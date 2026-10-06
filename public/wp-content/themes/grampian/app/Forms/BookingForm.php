<?php

namespace App\Forms;

use App\EventRecurrence;

/**
 * Rules for the booking form: what can be booked, and what a valid submission is.
 *
 * The item being booked travels as a hidden input rather than an ACF field, so it
 * is never editable by the person filling in the form — and is re-checked here on
 * every submit, because a hidden input is still user input.
 */
class BookingForm
{
    /** Set by the view just before acf_form() renders, read by the field filters. */
    public static ?int $currentItem = null;

    private const DATE_KEY = 'field_grampian_bk_date';

    private const ATTENDEES_KEY = 'field_grampian_bk_attendees';

    public static function init(): void
    {
        add_filter('acf/prepare_field/key=' . self::DATE_KEY, [self::class, 'prepareDate']);
        add_filter('acf/prepare_field/key=' . self::ATTENDEES_KEY, [self::class, 'prepareAttendees']);
        add_action('acf/validate_save_post', [self::class, 'validate']);
    }

    /** True on the page the visitor lands on after a successful booking. */
    public static function submitted(): bool
    {
        return ($_GET['booked'] ?? '') === '1';
    }

    /**
     * An event or activity is bookable when staff have switched bookings on, it is
     * published, and — for a one-off event — it has not already happened.
     */
    public static function isBookable(int $id): bool
    {
        if (! $id || get_post_status($id) !== 'publish') {
            return false;
        }

        $type = get_post_type($id);

        if (! in_array($type, ['event', 'activity'], true) || ! get_field('bookings_enabled', $id)) {
            return false;
        }

        if ($type === 'event' && ! EventRecurrence::isRecurring($id)) {
            return EventRecurrence::nextCard($id) !== null;
        }

        return true;
    }

    /**
     * A one-off event has exactly one date, so asking for one is noise. The field
     * stays on the wp-admin screen (currentItem is only ever set on the front end).
     */
    public static function prepareDate($field)
    {
        $id = self::$currentItem;

        if ($id && BookingSpaces::isOneOff($id)) {
            return false;
        }

        return $field;
    }

    /**
     * On a limited one-off event nobody can book more people than there are
     * places. (Sessions chosen by date are capped live in booking-spaces.js, and
     * both cases are enforced again server-side in validate().)
     */
    public static function prepareAttendees($field)
    {
        $id = self::$currentItem;

        if ($id && BookingSpaces::isOneOff($id)) {
            $left = BookingSpaces::left($id, BookingSpaces::sessionDate($id));

            if ($left !== null) {
                $field['max'] = max(1, min(BookingSpaces::MAX_PER_BOOKING, $left));
            }
        }

        return $field;
    }

    /**
     * Runs on ACF's AJAX validation and again on the real submit. Does nothing
     * unless the request is this theme's booking form.
     */
    public static function validate(): void
    {
        if (! self::isBookingSubmission()) {
            return;
        }

        $item = (int) ($_POST['go_booking_item'] ?? 0);

        if (! self::isBookable($item)) {
            acf_add_validation_error('', 'Sorry, bookings for this are closed. Please call us.');

            return;
        }

        // ACF's date picker posts Ymd in its hidden input, and has no minimum-date setting.
        $chosen = preg_replace('/\D/', '', (string) ($_POST['acf'][self::DATE_KEY] ?? ''));

        if (strlen($chosen) === 8 && $chosen < wp_date('Ymd')) {
            acf_add_validation_error('acf[' . self::DATE_KEY . ']', 'Please choose a date that has not passed.');

            return;
        }

        self::validateSpaces($item, $chosen);
    }

    /** Refuse a booking that would put the session over its limit. */
    private static function validateSpaces(int $item, string $chosen): void
    {
        $session = BookingSpaces::sessionDate($item, $chosen);
        $left    = $session === '' ? null : BookingSpaces::left($item, $session);

        if ($left === null) {
            return; // unlimited, or no date picked yet (ACF's required check covers that)
        }

        $when     = BookingSpaces::dateLabel($session);
        $oneOff   = BookingSpaces::isOneOff($item);
        $people   = (int) ($_POST['acf'][self::ATTENDEES_KEY] ?? 1);
        $dateName = $oneOff ? '' : 'acf[' . self::DATE_KEY . ']';

        if ($left === 0) {
            acf_add_validation_error(
                $dateName,
                $oneOff
                    ? 'Sorry, this is now fully booked. Please call us to ask about the waiting list.'
                    : sprintf('Sorry, %s is fully booked. Please choose another date or call us.', $when)
            );

            return;
        }

        if ($people > $left) {
            acf_add_validation_error(
                'acf[' . self::ATTENDEES_KEY . ']',
                sprintf('Only %d %s left%s.', $left, $left === 1 ? 'place' : 'places', $oneOff ? '' : ' on ' . $when)
            );
        }
    }

    public static function isBookingSubmission(): bool
    {
        return ($_POST['_acf_screen'] ?? '') === 'acf_form'
            && ($_POST['_acf_post_id'] ?? '') === 'new_post'
            && isset($_POST['acf']['field_grampian_bk_first_name']);
    }
}
