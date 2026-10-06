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

    public static function init(): void
    {
        add_filter('acf/prepare_field/key=' . self::DATE_KEY, [self::class, 'prepareDate']);
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

        if ($type === 'event' && (get_field('event_type', $id) ?: 'one_off') === 'one_off') {
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

        if ($id && get_post_type($id) === 'event' && (get_field('event_type', $id) ?: 'one_off') === 'one_off') {
            return false;
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

        if (! self::isBookable((int) ($_POST['go_booking_item'] ?? 0))) {
            acf_add_validation_error('', 'Sorry, bookings for this are closed. Please call us.');
        }

        // ACF's date picker posts Ymd in its hidden input, and has no minimum-date setting.
        $digits = preg_replace('/\D/', '', (string) ($_POST['acf'][self::DATE_KEY] ?? ''));

        if (strlen($digits) === 8 && $digits < wp_date('Ymd')) {
            acf_add_validation_error('acf[' . self::DATE_KEY . ']', 'Please choose a date that has not passed.');
        }
    }

    public static function isBookingSubmission(): bool
    {
        return ($_POST['_acf_screen'] ?? '') === 'acf_form'
            && ($_POST['_acf_post_id'] ?? '') === 'new_post'
            && isset($_POST['acf']['field_grampian_bk_first_name']);
    }
}
