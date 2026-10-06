<?php

namespace App\Forms;

/**
 * Cloudflare Turnstile for every ACF front-end form (contact, referral, booking).
 *
 * Designed to cost nothing when it is not configured and almost nothing when it
 * is: no keys means no script, no markup and no checks; with keys the widget runs
 * in "interaction-only" mode, so visitors only ever see it if Cloudflare is
 * unsure about them.
 *
 * Keys come from wp-config.php constants first (GO_TURNSTILE_SITE_KEY /
 * GO_TURNSTILE_SECRET_KEY) so the secret never has to live in the database, and
 * fall back to Site Settings.
 */
class Turnstile
{
    private const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Verification result for this request — a token is single use. */
    private static ?bool $verified = null;

    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_filter('acf/validate_form', [self::class, 'addWidget']);
        add_action('acf/validate_save_post', [self::class, 'validate']);
    }

    public static function siteKey(): string
    {
        return trim((string) (defined('GO_TURNSTILE_SITE_KEY')
            ? GO_TURNSTILE_SITE_KEY
            : get_field('turnstile_site_key', 'option')));
    }

    private static function secretKey(): string
    {
        return trim((string) (defined('GO_TURNSTILE_SECRET_KEY')
            ? GO_TURNSTILE_SECRET_KEY
            : get_field('turnstile_secret_key', 'option')));
    }

    public static function enabled(): bool
    {
        return self::siteKey() !== '' && self::secretKey() !== '';
    }

    /** Load Cloudflare's script only on pages that actually have a form. */
    public static function enqueue(): void
    {
        if (! self::enabled() || ! FormPages::current()) {
            return;
        }

        // null version: Cloudflare asks that the script is never version-pinned or cached.
        wp_enqueue_script('go-turnstile', self::SCRIPT_URL, [], null, [
            'strategy'  => 'defer',
            'in_footer' => true,
        ]);

        wp_add_inline_script(
            'go-turnstile',
            'window.goTurnstile = ' . wp_json_encode(['sitekey' => self::siteKey()]) . ';',
            'before'
        );
    }

    /**
     * Put a placeholder in every acf_form(); resources/js/turnstile.js renders the
     * widget into it. Prepended, so on the referral form it sits above the
     * Back / Next / Submit row rather than below it.
     */
    public static function addWidget($args)
    {
        if (is_admin() || ! self::enabled() || ! is_array($args)) {
            return $args;
        }

        $action = substr(sanitize_key($args['id'] ?? 'form'), 0, 32);

        $args['html_after_fields'] = sprintf(
            '<div class="go-turnstile" data-action="%s"><noscript>Please turn on JavaScript to send this form.</noscript></div>',
            esc_attr($action)
        ) . ($args['html_after_fields'] ?? '');

        return $args;
    }

    /**
     * ACF validates twice: once over AJAX as the visitor presses submit, then
     * again on the real POST. Turnstile tokens are single use, so the AJAX pass
     * only checks a token exists, and Cloudflare is asked exactly once, on the
     * real submit.
     */
    public static function validate(): void
    {
        if (! self::enabled() || ($_POST['_acf_screen'] ?? '') !== 'acf_form') {
            return;
        }

        $token = sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'] ?? ''));

        if (wp_doing_ajax()) {
            if ($token === '') {
                acf_add_validation_error('', 'Please wait a moment for the security check to finish, then send again.');
            }

            return;
        }

        if (self::$verified === null) {
            self::$verified = $token !== '' && self::verify($token);
        }

        if (! self::$verified) {
            acf_add_validation_error('', 'We could not confirm you are not a robot. Please try again.');
        }
    }

    private static function verify(string $token): bool
    {
        $body = ['secret' => self::secretKey(), 'response' => $token];
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $body['remoteip'] = $ip;
        }

        $response = wp_remote_post(self::VERIFY_URL, ['timeout' => 8, 'body' => $body]);

        $result = is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200
            ? null
            : json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($result) && array_key_exists('success', $result)) {
            // An explicit "no" from Cloudflare is final.
            return $result['success'] === true;
        }

        // Cloudflare unreachable or answering nonsense. A Cloudflare outage should
        // not stop someone booking or referring — the honeypot is still in place.
        error_log('Turnstile: siteverify unavailable, letting the submission through.');

        return (bool) apply_filters('go_turnstile_fail_open', true);
    }
}
