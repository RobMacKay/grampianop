<?php

namespace App\Forms;

/**
 * Post-processing for bookings submitted through acf_form().
 *
 * Same shape as ReferralNotifier, and for the same reason: the requirements box
 * and the carer's details are personal and often health-related, so staff emails
 * carry a summary and a wp-admin link, never the detail.
 */
class BookingNotifier
{
    private const POST_TYPE = 'booking';

    /** Marks a booking as already notified, so admin edits never re-send. */
    private const NOTIFIED_META = '_go_booking_notified';

    public static function init(): void
    {
        add_action('acf/save_post', [self::class, 'handle'], 20);
    }

    public static function handle($post_id): void
    {
        if (! is_numeric($post_id) || get_post_type($post_id) !== self::POST_TYPE) {
            return;
        }

        if (get_post_meta($post_id, self::NOTIFIED_META, true)) {
            return;
        }

        update_post_meta($post_id, self::NOTIFIED_META, current_time('mysql'));

        self::attachItem($post_id);
        self::setTitle($post_id);
        self::notifyStaff($post_id);
        self::acknowledge($post_id);
    }

    /**
     * The booked item is not an editable field on the public form, so write it
     * here from the hidden input — re-validated, never trusted.
     */
    private static function attachItem(int $post_id): void
    {
        $item = (int) ($_POST['go_booking_item'] ?? 0);

        if (! $item || ! BookingForm::isBookable($item)) {
            return;
        }

        update_field('field_grampian_bk_booked_item', $item, $post_id);

        // Every booking carries its session date, so places can be counted per
        // session. One-off events do not ask for one, so stamp the event's own.
        $chosen  = (string) get_post_meta($post_id, 'booking_date', true);
        $session = BookingSpaces::sessionDate($item, $chosen);

        if ($session !== '' && $chosen === '') {
            update_post_meta($post_id, 'booking_date', $session);
            update_post_meta($post_id, '_booking_date', 'field_grampian_bk_date');
        }

        // The form refuses over-limit bookings, but two people can submit at the
        // same moment. Whoever lands second goes on the waiting list for staff
        // to sort out, rather than silently overselling.
        $left   = BookingSpaces::left($item, $session, $post_id);
        $people = (int) get_post_meta($post_id, 'attendees', true);

        update_field('field_grampian_bk_status', ($left !== null && $people > $left) ? 'waitlist' : 'new', $post_id);
    }

    private static function itemId(int $post_id): int
    {
        return (int) get_post_meta($post_id, 'booked_item', true);
    }

    private static function itemTitle(int $post_id): string
    {
        $item = self::itemId($post_id);

        return $item ? get_the_title($item) : 'Unknown activity';
    }

    private static function dateLabel(int $post_id): string
    {
        $date = (string) get_post_meta($post_id, 'booking_date', true); // Ymd

        if (strlen($date) === 8) {
            return wp_date('j F Y', strtotime($date . ' 12:00:00'));
        }

        return 'Next session';
    }

    private static function setTitle(int $post_id): void
    {
        $title = sprintf(
            '%s %s — %s (%s)',
            (string) get_post_meta($post_id, 'first_name', true),
            (string) get_post_meta($post_id, 'last_name', true),
            self::itemTitle($post_id),
            self::dateLabel($post_id)
        );

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => wp_strip_all_tags($title),
        ]);
    }

    private static function notifyStaff(int $post_id): void
    {
        $recipients = self::recipients($post_id);

        if (empty($recipients)) {
            return;
        }

        $item = self::itemTitle($post_id);
        $name = trim(
            get_post_meta($post_id, 'first_name', true) . ' ' . get_post_meta($post_id, 'last_name', true)
        );

        $rows = [
            'Booking for'     => $item,
            'Date'            => self::dateLabel($post_id),
            'People'          => (string) get_post_meta($post_id, 'attendees', true),
            'Name'            => $name,
            'Support contact' => get_post_meta($post_id, 'has_support_contact', true) ? 'Yes' : 'No',
            'Support needs'   => trim((string) get_post_meta($post_id, 'requirements', true)) !== '' ? 'Yes' : 'No',
            'Received'        => wp_date('j F Y \a\t H:i'),
        ];

        // Limited places: say what is left, and flag a booking that went over.
        $itemId  = self::itemId($post_id);
        $session = BookingSpaces::sessionDate($itemId, (string) get_post_meta($post_id, 'booking_date', true));
        $left    = $itemId ? BookingSpaces::left($itemId, $session) : null;

        if ($left !== null) {
            $rows['Places left'] = (string) $left;
        }

        if (get_post_meta($post_id, 'booking_status', true) === 'waitlist') {
            $rows['Status'] = 'Waiting list — this booking went over the limit';
        }

        $body = '<p>A new booking request has been submitted through the website.</p><table cellpadding="6">';

        foreach ($rows as $label => $value) {
            $body .= sprintf(
                '<tr><td><strong>%s</strong></td><td>%s</td></tr>',
                esc_html($label),
                esc_html($value)
            );
        }

        $body .= '</table>';
        $body .= sprintf(
            '<p><a href="%s">View the full booking in the website admin</a></p>',
            esc_url(admin_url('post.php?post=' . $post_id . '&action=edit'))
        );
        $body .= '<p style="color:#555">The booking details are not included in this email. '
            . 'Sign in to view them.</p>';

        wp_mail($recipients, sprintf('New booking: %s', $item), $body, Mail::headers());
    }

    /**
     * Confirm receipt to the booker — or, when they gave no email, to their
     * carer. Carries no booking detail: it is reassurance, not a copy of the record.
     */
    private static function acknowledge(int $post_id): void
    {
        $email = (string) get_post_meta($post_id, 'email', true);

        if (! is_email($email)) {
            $email = (string) get_post_meta($post_id, 'support_email', true);
        }

        if (! is_email($email)) {
            return;
        }

        $phone = get_field('phone', 'option') ?: '01467 629675';
        $item  = self::itemTitle($post_id);

        $body = '<p>Thank you — we have received the booking request for <strong>'
            . esc_html($item) . '</strong>.</p>'
            . '<p>We will be in touch to confirm the place. '
            . 'If you need to speak to us in the meantime, call us on '
            . '<strong>' . esc_html($phone) . '</strong>.</p>'
            . '<p>This message confirms receipt only. For privacy it does not repeat '
            . 'anything that was submitted.</p>';

        wp_mail(
            $email,
            'We have received your booking request — Grampian Opportunities',
            $body,
            Mail::headers()
        );
    }

    /**
     * Item's own address, then the bookings address in Site Settings, then the
     * main contact email, then the site admin.
     *
     * @return array<int, string>
     */
    private static function recipients(int $post_id): array
    {
        $item       = self::itemId($post_id);
        $configured = $item ? (string) get_field('booking_notify_emails', $item) : '';

        foreach (['bookings_email', 'email'] as $option) {
            if (trim($configured) === '') {
                $configured = (string) get_field($option, 'option');
            }
        }

        if (trim($configured) === '') {
            $configured = (string) get_option('admin_email');
        }

        return (array) apply_filters('go_booking_recipients', Mail::parseList($configured), $post_id, $item);
    }
}
