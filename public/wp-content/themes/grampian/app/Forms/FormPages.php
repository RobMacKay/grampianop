<?php

namespace App\Forms;

/**
 * Answers one question for the whole theme: does this request render an ACF
 * front-end form? acf_form_head() has to run before any output, and the
 * Turnstile script should only load where a form exists, so both ask here.
 */
class FormPages
{
    public static function current(): bool
    {
        if (is_page_template(['template-referral.blade.php', 'template-contact.blade.php'])) {
            return true;
        }

        // The contact and booking forms are blocks, so there is no template to key
        // off. Note has_block() only inspects post_content — a block nested inside a
        // reusable block or the flexible-content template will not be found here.
        $post = get_queried_object();

        // Note the leading backslash: this file is namespaced, so an unqualified
        // WP_Post would resolve to App\Forms\WP_Post and never match.
        if (! $post instanceof \WP_Post) {
            return false;
        }

        if (has_block('acf/contact-form', $post) || has_block('acf/booking-form', $post)) {
            return true;
        }

        // Bookable events and activities show their form automatically.
        return is_singular(['event', 'activity']) && BookingForm::isBookable($post->ID);
    }
}
