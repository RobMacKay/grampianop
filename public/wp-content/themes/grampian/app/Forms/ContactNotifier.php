<?php

namespace App\Forms;

/**
 * Post-processing for contact messages submitted through the contact form block.
 *
 * Same shape as ReferralNotifier, with one deliberate difference: a contact
 * message is ordinary correspondence rather than special category data, so the
 * full message goes in the email body. Staff can read and reply without signing
 * in, which is the point of a contact form.
 */
class ContactNotifier
{
    private const POST_TYPE = 'contact_submission';

    /** Marks a submission as already notified, so admin edits never re-send. */
    private const NOTIFIED_META = '_go_contact_notified';

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

        self::setTitle($post_id);
        self::notifyStaff($post_id);
    }

    private static function setTitle(int $post_id): void
    {
        $name    = (string) get_field('name', $post_id);
        $subject = (string) get_field('subject', $post_id);

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => wp_strip_all_tags(
                sprintf('%s — %s (%s)', $name, $subject, wp_date('d/m/Y H:i'))
            ),
        ]);
    }

    private static function notifyStaff(int $post_id): void
    {
        $recipients = self::recipients($post_id);

        if (empty($recipients)) {
            return;
        }

        $name    = (string) get_field('name', $post_id);
        $email   = (string) get_field('email', $post_id);
        $phone   = (string) get_field('phone', $post_id);
        $subject = (string) get_field('subject', $post_id);
        $message = (string) get_field('message', $post_id);

        $body = '<p>A message was sent through the website contact form.</p><table cellpadding="6">';

        foreach (['From' => $name, 'Email' => $email, 'Phone' => $phone, 'Subject' => $subject] as $label => $value) {
            if ($value === '') {
                continue;
            }

            $body .= sprintf(
                '<tr><td><strong>%s</strong></td><td>%s</td></tr>',
                esc_html($label),
                esc_html($value)
            );
        }

        $body .= '</table><hr><p>' . nl2br(esc_html($message)) . '</p>';
        $body .= sprintf(
            '<p><a href="%s">View this message in the website admin</a></p>',
            esc_url(admin_url('post.php?post=' . $post_id . '&action=edit'))
        );

        $headers = self::headers();

        // Replying in a mail client should go to the sender, not the site.
        if (is_email($email)) {
            $headers[] = sprintf('Reply-To: %s <%s>', $name, $email);
        }

        wp_mail(
            $recipients,
            sprintf('Website enquiry: %s', $subject ?: 'No subject'),
            $body,
            $headers
        );
    }

    /**
     * The address on the site settings options page.
     *
     * Deliberately not a per-block setting: by the time this runs we only have
     * the submission, not the block that produced it, so a block-level field
     * would look configurable and silently do nothing. Use the filter to route
     * elsewhere.
     *
     * @return array<int, string>
     */
    private static function recipients(int $post_id): array
    {
        $configured = (string) get_field('email', 'option');

        if (trim($configured) === '') {
            $configured = (string) get_option('admin_email');
        }

        $emails = array_values(array_filter(array_map('trim', explode(',', $configured)), 'is_email'));

        return (array) apply_filters('go_contact_recipients', $emails, $post_id);
    }

    /** @return array<int, string> */
    private static function headers(): array
    {
        $domain = wp_parse_url(home_url(), PHP_URL_HOST) ?: 'localhost';
        $domain = preg_replace('/^www\./', '', $domain);

        return [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <no-reply@%s>', get_bloginfo('name'), $domain),
        ];
    }
}
