<?php

namespace App\Forms;

/**
 * wp-admin list screen for bookings: useful columns, filters by what was booked
 * and by status, sortable date, and a CSV download for staff.
 */
class BookingAdmin
{
    private const TYPE = 'booking';

    private const STATUSES = [
        'new'       => 'New',
        'confirmed' => 'Confirmed',
        'waitlist'  => 'Waiting list',
        'cancelled' => 'Cancelled',
    ];

    public static function init(): void
    {
        if (! is_admin()) {
            return;
        }

        add_filter('manage_' . self::TYPE . '_posts_columns', [self::class, 'columns']);
        add_action('manage_' . self::TYPE . '_posts_custom_column', [self::class, 'renderColumn'], 10, 2);
        add_filter('manage_edit-' . self::TYPE . '_sortable_columns', [self::class, 'sortable']);
        add_action('restrict_manage_posts', [self::class, 'filters']);
        add_action('pre_get_posts', [self::class, 'applyFilters']);
        add_action('admin_post_go_export_bookings', [self::class, 'exportCsv']);
    }

    /** @param array<string, string> $columns */
    public static function columns(array $columns): array
    {
        return [
            'cb'             => $columns['cb'] ?? '',
            'title'          => 'Booking',
            'booked_for'     => 'Booked for',
            'booking_date'   => 'Date',
            'people'         => 'People',
            'booking_status' => 'Status',
            'date'           => $columns['date'] ?? 'Submitted',
        ];
    }

    public static function renderColumn(string $column, int $post_id): void
    {
        switch ($column) {
            case 'booked_for':
                $item = (int) get_post_meta($post_id, 'booked_item', true);

                if ($item && $link = get_edit_post_link($item)) {
                    printf('<a href="%s">%s</a>', esc_url($link), esc_html(get_the_title($item)));
                } else {
                    echo '—';
                }
                break;

            case 'booking_date':
                $date = (string) get_post_meta($post_id, 'booking_date', true);
                echo strlen($date) === 8 ? esc_html(wp_date('d/m/Y', strtotime($date))) : '—';
                break;

            case 'people':
                echo esc_html((string) get_post_meta($post_id, 'attendees', true) ?: '—');
                break;

            case 'booking_status':
                $status = (string) get_post_meta($post_id, 'booking_status', true);
                echo esc_html(self::STATUSES[$status] ?? '—');
                break;
        }
    }

    /** @param array<string, string> $columns */
    public static function sortable(array $columns): array
    {
        $columns['booking_date'] = 'booking_date';

        return $columns;
    }

    public static function filters(): void
    {
        if (! self::onBookingScreen()) {
            return;
        }

        $item   = absint($_GET['go_booked_item'] ?? 0);
        $status = sanitize_key($_GET['go_status'] ?? '');

        echo '<label class="screen-reader-text" for="go_booked_item">Filter by what was booked</label>';
        echo '<select name="go_booked_item" id="go_booked_item"><option value="">All events and activities</option>';

        foreach (self::bookableItems() as $post) {
            printf(
                '<option value="%d"%s>%s</option>',
                $post->ID,
                selected($item, $post->ID, false),
                esc_html($post->post_title)
            );
        }

        echo '</select>';

        echo '<label class="screen-reader-text" for="go_status">Filter by status</label>';
        echo '<select name="go_status" id="go_status"><option value="">All statuses</option>';

        foreach (self::STATUSES as $value => $label) {
            printf('<option value="%s"%s>%s</option>', esc_attr($value), selected($status, $value, false), esc_html($label));
        }

        echo '</select>';

        $export = wp_nonce_url(
            add_query_arg(
                array_filter(['action' => 'go_export_bookings', 'go_booked_item' => $item, 'go_status' => $status]),
                admin_url('admin-post.php')
            ),
            'go_export_bookings'
        );

        printf('<a class="button" href="%s">Download CSV</a>', esc_url($export));
    }

    public static function applyFilters(\WP_Query $query): void
    {
        if (! is_admin() || ! $query->is_main_query() || $query->get('post_type') !== self::TYPE) {
            return;
        }

        $meta = self::metaQuery(
            absint($_GET['go_booked_item'] ?? 0),
            sanitize_key($_GET['go_status'] ?? '')
        );

        // Order by booking date without dropping bookings that have none (one-off
        // events have no date field) — a bare meta_key would exclude them.
        if ($query->get('orderby') === 'booking_date') {
            $meta[] = [
                'relation' => 'OR',
                'go_booking_date' => ['key' => 'booking_date', 'compare' => 'EXISTS'],
                ['key' => 'booking_date', 'compare' => 'NOT EXISTS'],
            ];
            $query->set('orderby', 'go_booking_date');
        }

        if ($meta) {
            $query->set('meta_query', array_merge(['relation' => 'AND'], $meta));
        }
    }

    /** @return array<int, array<string, mixed>> */
    private static function metaQuery(int $item, string $status): array
    {
        $meta = [];

        if ($item) {
            $meta[] = ['key' => 'booked_item', 'value' => $item, 'compare' => '='];
        }

        if (isset(self::STATUSES[$status])) {
            $meta[] = ['key' => 'booking_status', 'value' => $status, 'compare' => '='];
        }

        return $meta;
    }

    /**
     * Staff are signed in, so unlike the emails the CSV carries the full detail.
     */
    public static function exportCsv(): void
    {
        if (! current_user_can('edit_others_posts')) {
            wp_die('You do not have permission to export bookings.', 403);
        }

        check_admin_referer('go_export_bookings');

        $meta = self::metaQuery(absint($_GET['go_booked_item'] ?? 0), sanitize_key($_GET['go_status'] ?? ''));

        $ids = get_posts([
            'post_type'      => self::TYPE,
            'post_status'    => ['publish', 'pending', 'draft', 'private'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => $meta ? array_merge(['relation' => 'AND'], $meta) : [],
            'no_found_rows'  => true,
        ]);

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bookings-' . wp_date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8
        fputcsv($out, [
            'Submitted', 'Booked for', 'Booking date', 'Status', 'First name', 'Last name', 'Phone', 'Email',
            'People', 'Support or access needs', 'Support contact name', 'Support contact role',
            'Support contact phone', 'Support contact email', 'Staff notes',
        ]);

        foreach ($ids as $id) {
            $get  = fn (string $key) => (string) get_post_meta($id, $key, true);
            $date = $get('booking_date');
            $item = (int) $get('booked_item');
            $status = $get('booking_status');

            $row = [
                get_the_date('Y-m-d H:i', $id),
                $item ? get_the_title($item) : '',
                strlen($date) === 8 ? wp_date('Y-m-d', strtotime($date)) : '',
                self::STATUSES[$status] ?? '',
                $get('first_name'), $get('last_name'), $get('phone'), $get('email'),
                $get('attendees'), $get('requirements'), $get('support_name'), $get('support_role'),
                $get('support_phone'), $get('support_email'), $get('staff_notes'),
            ];

            // Stop spreadsheet apps treating user text as a formula.
            fputcsv($out, array_map(
                fn ($cell) => preg_match('/^[=+\-@\t\r]/', $cell) ? "'" . $cell : $cell,
                $row
            ));
        }

        fclose($out);
        exit;
    }

    /** @return array<int, \WP_Post> */
    private static function bookableItems(): array
    {
        return get_posts([
            'post_type'      => ['event', 'activity'],
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'meta_key'       => 'bookings_enabled',
            'meta_value'     => '1',
            'no_found_rows'  => true,
        ]);
    }

    private static function onBookingScreen(): bool
    {
        return ($GLOBALS['typenow'] ?? '') === self::TYPE;
    }
}
