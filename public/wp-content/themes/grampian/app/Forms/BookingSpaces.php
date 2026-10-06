<?php

namespace App\Forms;

/**
 * Places available on a bookable event or activity.
 *
 * A limit is optional and applies per session: a one-off event has one session,
 * and a weekly group or an activity has one for every date a visitor can pick.
 * Every booking therefore carries a date — for a one-off event the form does not
 * ask for one, so BookingNotifier stamps the event's own date on it.
 *
 * Cancelled bookings and the waiting list do not use up places.
 */
class BookingSpaces
{
    /** Same cap as the attendees field on the form. */
    public const MAX_PER_BOOKING = 10;

    public static function init(): void
    {
        // Public and read-only: it returns counts, never any booking detail.
        add_action('wp_ajax_go_booking_spaces', [self::class, 'ajax']);
        add_action('wp_ajax_nopriv_go_booking_spaces', [self::class, 'ajax']);
    }

    /** Places per session, or null when the item takes unlimited bookings. */
    public static function limit(int $item): ?int
    {
        if (! get_field('limit_spaces', $item)) {
            return null;
        }

        $total = (int) get_field('spaces_total', $item);

        return $total > 0 ? $total : null;
    }

    public static function isOneOff(int $item): bool
    {
        return get_post_type($item) === 'event'
            && (get_field('event_type', $item) ?: 'one_off') === 'one_off';
    }

    /**
     * The session a booking belongs to, as Ymd. One-off events are always their
     * own date; anything else uses the date the visitor chose ('' if none yet).
     */
    public static function sessionDate(int $item, string $chosen = ''): string
    {
        if (self::isOneOff($item)) {
            return substr(preg_replace('/\D/', '', (string) get_post_meta($item, 'start_date', true)), 0, 8);
        }

        $digits = preg_replace('/\D/', '', $chosen);

        return strlen($digits) === 8 ? $digits : '';
    }

    /** Places already booked for a session. $exclude leaves one booking out of the count. */
    public static function taken(int $item, string $ymd, int $exclude = 0): int
    {
        global $wpdb;

        $sql = "SELECT COALESCE(SUM(CAST(a.meta_value AS UNSIGNED)), 0)
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} i ON i.post_id = p.ID AND i.meta_key = 'booked_item' AND i.meta_value = %s
                JOIN {$wpdb->postmeta} a ON a.post_id = p.ID AND a.meta_key = 'attendees'
                LEFT JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = 'booking_date'
                LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = 'booking_status'
                WHERE p.post_type = 'booking'
                  AND p.post_status IN ('publish', 'pending', 'draft', 'private')
                  AND p.ID <> %d
                  AND COALESCE(d.meta_value, '') = %s
                  AND (s.meta_value IS NULL OR s.meta_value NOT IN ('cancelled', 'waitlist'))";

        return (int) $wpdb->get_var($wpdb->prepare($sql, (string) $item, $exclude, $ymd));
    }

    /**
     * Places still free for a session; null means unlimited.
     * Never negative — a session that went over (a race, or staff adding people
     * by hand) simply reads as full.
     */
    public static function left(int $item, string $ymd, int $exclude = 0): ?int
    {
        $limit = self::limit($item);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - self::taken($item, $ymd, $exclude));
    }

    /** One-off events can be called full without asking for a date. */
    public static function isFull(int $item): bool
    {
        return self::isOneOff($item) && self::left($item, self::sessionDate($item)) === 0;
    }

    /** "7 November" — used in messages. */
    public static function dateLabel(string $ymd): string
    {
        return strlen($ymd) === 8 ? wp_date('j F', strtotime($ymd . ' 12:00:00')) : '';
    }

    /** GET ?action=go_booking_spaces&item=12&date=20261107 */
    public static function ajax(): void
    {
        $item = absint($_GET['item'] ?? 0);

        if (! BookingForm::isBookable($item)) {
            wp_send_json_error(['message' => 'closed'], 404);
        }

        $ymd   = self::sessionDate($item, sanitize_text_field(wp_unslash($_GET['date'] ?? '')));
        $limit = self::limit($item);

        wp_send_json_success([
            'limited' => $limit !== null,
            'total'   => $limit,
            'left'    => $limit === null || $ymd === '' ? null : self::left($item, $ymd),
            'date'    => $ymd,
        ]);
    }
}
