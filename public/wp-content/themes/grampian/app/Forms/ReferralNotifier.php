<?php

namespace App\Forms;

/**
 * Post-processing for referrals submitted through acf_form().
 *
 * ACF creates the post and writes the fields; everything that has to happen
 * afterwards — generating a title and notifying people — hangs off acf/save_post
 * at a late priority so the field values are already committed.
 */
class ReferralNotifier
{
    private const POST_TYPE = 'referral';

    /** Marks a referral as already notified, so admin edits never re-send. */
    private const NOTIFIED_META = '_go_referral_notified';

    public static function init(): void
    {
        add_action('acf/save_post', [self::class, 'handle'], 20);
    }

    public static function handle($post_id): void
    {
        if (! is_numeric($post_id) || get_post_type($post_id) !== self::POST_TYPE) {
            return;
        }

        // Only ever fires once per referral. Staff editing a referral in wp-admin
        // must not trigger another round of emails.
        if (get_post_meta($post_id, self::NOTIFIED_META, true)) {
            return;
        }

        update_post_meta($post_id, self::NOTIFIED_META, current_time('mysql'));

        self::setTitle($post_id);
        self::notifyStaff($post_id);
        self::acknowledge($post_id);
    }

    /**
     * The referral post type only supports 'title', so build one that is
     * scannable in the admin list without exposing anything sensitive.
     */
    private static function setTitle(int $post_id): void
    {
        $first = (string) get_field('first_name', $post_id);
        $last  = (string) get_field('last_name', $post_id);
        $when  = wp_date('d/m/Y H:i');

        if (get_field('referral_type', $post_id) === 'myself') {
            $title = sprintf('Self-referral: %s %s — %s', $first, $last, $when);
        } else {
            $title = sprintf(
                'Referral for %s %s by %s %s — %s',
                $first,
                $last,
                (string) get_field('referrer_first_name', $post_id),
                (string) get_field('referrer_last_name', $post_id),
                $when
            );
        }

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => wp_strip_all_tags($title),
        ]);
    }

    /**
     * Referrals carry health and disability information — special category data
     * under UK GDPR. The notification deliberately carries only enough to triage
     * (who, what kind, when) plus a link; the detail stays behind a login rather
     * than being copied into every recipient's mailbox and mail relay.
     */
    private static function notifyStaff(int $post_id): void
    {
        $recipients = self::recipients();

        if (empty($recipients)) {
            return;
        }

        $is_self = get_field('referral_type', $post_id) === 'myself';
        $name    = trim(get_field('first_name', $post_id) . ' ' . get_field('last_name', $post_id));

        $rows = [
            'Type'     => $is_self ? 'Self-referral' : 'Referred by a third party',
            'Person'   => $name,
            'Received' => wp_date('j F Y \a\t H:i'),
        ];

        if (! $is_self) {
            $rows['Referrer'] = trim(
                get_field('referrer_first_name', $post_id) . ' ' . get_field('referrer_last_name', $post_id)
            );
            $rows['Relationship'] = (string) get_field('referrer_relationship', $post_id);
        }

        $body = '<p>A new referral has been submitted through the website.</p><table cellpadding="6">';

        foreach ($rows as $label => $value) {
            $body .= sprintf(
                '<tr><td><strong>%s</strong></td><td>%s</td></tr>',
                esc_html($label),
                esc_html($value)
            );
        }

        $body .= '</table>';
        $body .= sprintf(
            '<p><a href="%s">View the full referral in the website admin</a></p>',
            esc_url(admin_url('post.php?post=' . $post_id . '&action=edit'))
        );
        $body .= '<p style="color:#555">The referral details are not included in this email. '
            . 'Sign in to view them.</p>';

        $subject = $is_self
            ? sprintf('New self-referral: %s', $name)
            : sprintf('New referral: %s', $name);

        wp_mail($recipients, $subject, $body, Mail::headers());
    }

    /**
     * Confirm receipt to whoever submitted the form, when they gave an address.
     * Carries no referral detail — it is reassurance, not a copy of the record.
     */
    private static function acknowledge(int $post_id): void
    {
        $is_self = get_field('referral_type', $post_id) === 'myself';

        $email = $is_self
            ? (string) get_field('email', $post_id)
            : (string) get_field('referrer_email', $post_id);

        if (! is_email($email)) {
            return;
        }

        $phone = get_field('phone', 'option') ?: '01467 629675';

        $body = '<p>Thank you — we have received your referral.</p>'
            . '<p>Someone from the Grampian Opportunities team will review it and be in touch. '
            . 'If you need to speak to us in the meantime, call us on '
            . '<strong>' . esc_html($phone) . '</strong>.</p>'
            . '<p>This message confirms receipt only. For your privacy it does not repeat '
            . 'anything you submitted.</p>';

        wp_mail(
            $email,
            'We have received your referral — Grampian Opportunities',
            $body,
            Mail::headers()
        );
    }

    /**
     * Recipients come from the referral page's own field, falling back to the
     * address on the site settings options page.
     *
     * @return array<int, string>
     */
    private static function recipients(): array
    {
        $configured = '';

        if ($page_id = self::referralPageId()) {
            $configured = (string) get_field('referral_notify_emails', $page_id);
        }

        if (trim($configured) === '') {
            $configured = (string) get_field('email', 'option');
        }

        if (trim($configured) === '') {
            $configured = (string) get_option('admin_email');
        }

        $emails = array_map('trim', explode(',', $configured));

        return array_values(array_filter($emails, 'is_email'));
    }

    /** The page using the referral template, if one exists. */
    private static function referralPageId(): ?int
    {
        $pages = get_posts([
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'template-referral.blade.php',
        ]);

        return $pages ? (int) $pages[0] : null;
    }
}
