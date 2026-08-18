<?php

namespace App\Forms;

class ReferralFormHandler
{
    public static function handle(): void
    {
        if (! isset($_POST['referral_nonce']) || ! wp_verify_nonce($_POST['referral_nonce'], 'submit_referral_nonce')) {
            wp_die('Security check failed.');
        }

        $return_url = self::safeReturnUrl($_POST['return_url'] ?? '');

        // Step 1
        $referral_type = sanitize_text_field($_POST['referral_type'] ?? '');
        if (! in_array($referral_type, ['myself', 'someone_else'], true)) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        // Step 2 — only required if someone_else
        $referrer_first_name    = sanitize_text_field($_POST['referrer_first_name'] ?? '');
        $referrer_last_name     = sanitize_text_field($_POST['referrer_last_name'] ?? '');
        $referrer_relationship  = sanitize_text_field($_POST['referrer_relationship'] ?? '');
        $referrer_organisation  = sanitize_text_field($_POST['referrer_organisation'] ?? '');
        $referrer_phone         = sanitize_text_field($_POST['referrer_phone'] ?? '');
        $referrer_email         = sanitize_email($_POST['referrer_email'] ?? '');

        if ($referral_type === 'someone_else') {
            if (empty($referrer_first_name) || empty($referrer_last_name) || empty($referrer_phone)) {
                wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
                exit;
            }
        }

        // Step 3
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name  = sanitize_text_field($_POST['last_name'] ?? '');
        $dob        = sanitize_text_field($_POST['date_of_birth'] ?? '');
        $address    = sanitize_text_field($_POST['address'] ?? '');
        $town       = sanitize_text_field($_POST['town'] ?? '');
        $postcode   = sanitize_text_field($_POST['postcode'] ?? '');
        $phone      = sanitize_text_field($_POST['phone'] ?? '');
        $email      = sanitize_email($_POST['email'] ?? '');

        if (empty($first_name) || empty($last_name) || empty($dob) || empty($address) || empty($town) || empty($postcode)) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        // Step 4
        $reason          = sanitize_textarea_field($_POST['referral_reason'] ?? '');
        $additional_info = sanitize_textarea_field($_POST['additional_info'] ?? '');
        $consent_data    = isset($_POST['consent_data']) ? 1 : 0;
        $consent_referral = isset($_POST['consent_referral']) ? 1 : 0;

        if (empty($reason) || ! $consent_data) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        $title = $referral_type === 'myself'
            ? "Self-referral: {$first_name} {$last_name} — " . wp_date('d/m/Y H:i')
            : "Referral for {$first_name} {$last_name} by {$referrer_first_name} {$referrer_last_name} — " . wp_date('d/m/Y H:i');

        $post_id = wp_insert_post([
            'post_type'   => 'referral',
            'post_status' => 'pending',
            'post_title'  => $title,
        ]);

        if (is_wp_error($post_id)) {
            wp_safe_redirect(add_query_arg('form_error', '1', $return_url));
            exit;
        }

        // Step 1
        update_field('field_grampian_ref_type', $referral_type, $post_id);

        // Step 2
        update_field('field_grampian_ref_referrer_first_name', $referrer_first_name, $post_id);
        update_field('field_grampian_ref_referrer_last_name', $referrer_last_name, $post_id);
        update_field('field_grampian_ref_referrer_relationship', $referrer_relationship, $post_id);
        update_field('field_grampian_ref_referrer_org', $referrer_organisation, $post_id);
        update_field('field_grampian_ref_referrer_phone', $referrer_phone, $post_id);
        update_field('field_grampian_ref_referrer_email', $referrer_email, $post_id);

        // Step 3
        update_field('field_grampian_ref_first_name', $first_name, $post_id);
        update_field('field_grampian_ref_last_name', $last_name, $post_id);
        update_field('field_grampian_ref_dob', $dob, $post_id);
        update_field('field_grampian_ref_address', $address, $post_id);
        update_field('field_grampian_ref_town', $town, $post_id);
        update_field('field_grampian_ref_postcode', $postcode, $post_id);
        update_field('field_grampian_ref_phone', $phone, $post_id);
        update_field('field_grampian_ref_email', $email, $post_id);

        // Step 4
        update_field('field_grampian_ref_reason', $reason, $post_id);
        update_field('field_grampian_ref_additional_info', $additional_info, $post_id);
        update_field('field_grampian_ref_consent_referral', $consent_referral, $post_id);
        update_field('field_grampian_ref_consent_data', $consent_data, $post_id);

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
