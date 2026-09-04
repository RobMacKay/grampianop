<?php

namespace App\Forms;

class ContactFormHandler
{
    public static function handle(): void
    {
        if (! isset($_POST['contact_nonce']) || ! wp_verify_nonce($_POST['contact_nonce'], 'submit_contact_nonce')) {
            wp_die('Security check failed.');
        }

        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $phone   = sanitize_text_field($_POST['phone'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        $return_url = self::safeReturnUrl($_POST['return_url'] ?? '');

        if (empty($name) || empty($email) || ! is_email($email) || empty($subject) || empty($message)) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        $post_id = wp_insert_post([
            'post_type'   => 'contact_submission',
            'post_status' => 'pending',
            'post_title'  => "Contact from {$name} — " . wp_date('d/m/Y H:i'),
        ]);

        if (is_wp_error($post_id)) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        update_field('field_grampian_ct_name', $name, $post_id);
        update_field('field_grampian_ct_email', $email, $post_id);
        update_field('field_grampian_ct_phone', $phone, $post_id);
        update_field('field_grampian_ct_subject', $subject, $post_id);
        update_field('field_grampian_ct_message', $message, $post_id);

        wp_safe_redirect(add_query_arg('submitted', '1', $return_url));
        exit;
    }

    private static function safeReturnUrl(string $url): string
    {
        $url = esc_url_raw($url);

        if (empty($url) || ! str_starts_with($url, home_url())) {
            return home_url();
        }

        return $url;
    }
}
