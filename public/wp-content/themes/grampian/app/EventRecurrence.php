<?php

namespace App;

use DateTimeImmutable;

/**
 * Computes the next occurrence of a recurring or one-off event post.
 *
 * Returns an array shaped for the go-events-strip card:
 *   weekday, day, title, detail, sort_ts, url
 *
 * Returns null if the event has expired or has no computable date.
 */
class EventRecurrence
{
    /** Map our stored day names to PHP day-of-week strings */
    private const DAY_NAMES = [
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
        'sunday'    => 'Sunday',
    ];

    /** Map our ordinal values to PHP relative date strings */
    private const ORDINALS = [
        '1st'  => 'first',
        '2nd'  => 'second',
        '3rd'  => 'third',
        '4th'  => 'fourth',
        'last' => 'last',
    ];

    public static function nextCard(int $post_id): ?array
    {
        $today = new DateTimeImmutable('today');

        return self::isRecurring($post_id)
            ? self::recurringCard($post_id, $today)
            : self::oneOffCard($post_id, $today);
    }

    /**
     * The one place that decides whether an event repeats. The Event Type radio
     * is the source of truth; the retired "Repeating Event" tick box is only
     * consulted for old events saved before Event Type existed, so a stale tick
     * can never turn a one-off event into a recurring one.
     */
    public static function isRecurring(int $post_id): bool
    {
        $type = get_post_meta($post_id, 'event_type', true);

        if ($type === 'recurring' || $type === 'one_off') {
            return $type === 'recurring';
        }

        return (bool) get_post_meta($post_id, 'recurring', true);
    }

    /**
     * Plain-English schedule for a recurring event, e.g. "Every Wednesday · 10:30–12:00"
     * or "3rd Thursday of the month". The editor's own summary wins when given.
     */
    public static function scheduleLabel(int $post_id): string
    {
        $note = trim((string) get_field('recurring_note', $post_id));

        if ($note !== '') {
            return $note;
        }

        $days = array_map(
            fn ($d) => self::DAY_NAMES[$d] ?? ucfirst((string) $d),
            (array) (get_field('recurrence_days', $post_id) ?: [])
        );
        $list = count($days) > 1
            ? implode(', ', array_slice($days, 0, -1)) . ' and ' . end($days)
            : (string) ($days[0] ?? '');

        $label = match (get_field('recurrence_type', $post_id) ?: 'weekly') {
            'fortnightly'     => $list ? 'Every other ' . $list : 'Fortnightly',
            'monthly_ordinal' => sprintf(
                '%s %s of the month',
                get_field('recurrence_ordinal', $post_id) === 'last' ? 'Last' : (get_field('recurrence_ordinal', $post_id) ?: '1st'),
                self::DAY_NAMES[get_field('recurrence_day', $post_id) ?: 'monday'] ?? 'Monday'
            ),
            default           => $list ? 'Every ' . $list : 'Weekly',
        };

        $time = trim((string) get_field('recurrence_time', $post_id));

        return $time !== '' ? $label . ' · ' . $time : $label;
    }

    // ── One-off ──────────────────────────────────────────────────────────────

    private static function oneOffCard(int $post_id, DateTimeImmutable $today): ?array
    {
        $start = get_field('start_date', $post_id);
        if (!$start) return null;

        $dt = new DateTimeImmutable($start);
        if ($dt < $today) return null;

        $all_day  = get_field('all_day', $post_id);
        $end      = get_field('end_date', $post_id);
        $location = get_field('location', $post_id);

        if ($all_day) {
            $detail = 'All day';
        } else {
            $detail = $dt->format('g:ia');
            if ($end) {
                $detail .= '–' . (new DateTimeImmutable($end))->format('g:ia');
            }
        }
        if ($location) {
            $detail .= ' · ' . $location;
        }

        return self::shape($dt, $post_id, $detail);
    }

    // ── Recurring ────────────────────────────────────────────────────────────

    private static function recurringCard(int $post_id, DateTimeImmutable $today): ?array
    {
        $type  = get_field('recurrence_type', $post_id) ?: 'weekly';
        $until = get_field('recurrence_end_date', $post_id);

        if ($until && new DateTimeImmutable($until) < $today) return null;

        $next = match ($type) {
            'weekly'          => self::nextWeekly($post_id, $today, 7),
            'fortnightly'     => self::nextFortnightly($post_id, $today),
            'monthly_ordinal' => self::nextMonthlyOrdinal($post_id, $today),
            default           => null,
        };

        if (!$next) return null;
        if ($until && $next > new DateTimeImmutable($until)) return null;

        $time   = get_field('recurrence_time', $post_id) ?: '';
        $note   = get_field('recurring_note', $post_id)  ?: '';
        $location = get_field('location', $post_id) ?: '';
        $detail = $time ?: $note;
        if ($location && $detail) {
            $detail .= ' · ' . $location;
        } elseif ($location) {
            $detail = $location;
        }

        return self::shape($next, $post_id, $detail);
    }

    private static function nextWeekly(int $post_id, DateTimeImmutable $today, int $window_days): ?DateTimeImmutable
    {
        $days = get_field('recurrence_days', $post_id) ?: [];
        if (empty($days)) return null;

        $candidates = [];
        foreach ($days as $day) {
            $name = self::DAY_NAMES[$day] ?? null;
            if (!$name) continue;

            $candidate = new DateTimeImmutable('this ' . $name);
            if ($candidate < $today) {
                $candidate = new DateTimeImmutable('next ' . $name);
            }
            $candidates[] = $candidate;
        }

        if (empty($candidates)) return null;

        usort($candidates, fn($a, $b) => $a->getTimestamp() <=> $b->getTimestamp());
        return $candidates[0];
    }

    private static function nextFortnightly(int $post_id, DateTimeImmutable $today): ?DateTimeImmutable
    {
        $days = get_field('recurrence_days', $post_id) ?: [];
        $ref  = get_field('fortnightly_ref_date', $post_id);
        $ref_dt = $ref ? new DateTimeImmutable($ref) : $today;

        if (empty($days)) return null;

        $candidates = [];
        foreach ($days as $day) {
            $name = self::DAY_NAMES[$day] ?? null;
            if (!$name) continue;

            // Find next occurrence of this weekday
            $candidate = new DateTimeImmutable('this ' . $name);
            if ($candidate < $today) {
                $candidate = new DateTimeImmutable('next ' . $name);
            }

            // Check alignment against the reference date (must be an even number of weeks away)
            $days_from_ref = (int) abs($candidate->diff($ref_dt)->days);
            if (($days_from_ref % 14) >= 7) {
                // Off week — push forward one week
                $candidate = $candidate->modify('+7 days');
            }

            $candidates[] = $candidate;
        }

        if (empty($candidates)) return null;

        usort($candidates, fn($a, $b) => $a->getTimestamp() <=> $b->getTimestamp());
        return $candidates[0];
    }

    private static function nextMonthlyOrdinal(int $post_id, DateTimeImmutable $today): ?DateTimeImmutable
    {
        $ordinal = get_field('recurrence_ordinal', $post_id) ?: '1st';
        $day     = get_field('recurrence_day', $post_id) ?: 'monday';

        $ordinal_str = self::ORDINALS[$ordinal] ?? 'first';
        $day_str     = self::DAY_NAMES[$day]    ?? 'Monday';

        // Try this month first, then next month
        foreach (['this month', 'next month'] as $month) {
            try {
                $candidate = new DateTimeImmutable($ordinal_str . ' ' . $day_str . ' of ' . $month);
                if ($candidate >= $today) return $candidate;
            } catch (\Exception $e) {
                // e.g. "last Sunday of this month" might land in previous month — skip
            }
        }

        return null;
    }

    // ── Shared ───────────────────────────────────────────────────────────────

    private static function shape(DateTimeImmutable $dt, int $post_id, string $detail): array
    {
        return [
            'weekday' => strtoupper($dt->format('D')),
            'day'     => $dt->format('j'),
            'title'   => get_the_title($post_id),
            'detail'  => $detail,
            'sort_ts' => $dt->getTimestamp(),
            'url'     => get_permalink($post_id),
        ];
    }
}
