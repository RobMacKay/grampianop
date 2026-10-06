<?php

namespace App\Forms;

/** Small helpers shared by the form notifiers. */
class Mail
{
    /** @return array<int, string> */
    public static function headers(): array
    {
        $domain = wp_parse_url(home_url(), PHP_URL_HOST) ?: 'localhost';
        $domain = preg_replace('/^www\./', '', $domain);

        return [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <no-reply@%s>', get_bloginfo('name'), $domain),
        ];
    }

    /**
     * Valid addresses from a comma-separated setting.
     *
     * @return array<int, string>
     */
    public static function parseList(string $configured): array
    {
        $emails = array_map('trim', explode(',', $configured));

        return array_values(array_filter($emails, 'is_email'));
    }
}
