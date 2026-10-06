<?php

namespace App;

/**
 * Query helpers shared by the blocks that list activities and events, so a block
 * never carries its own hand-made cards — the post types are the source of truth.
 */
class Content
{
    /**
     * Activities shaped for card/grid blocks.
     *
     * @param  'all'|'selected'  $mode      'selected' keeps the editor's order.
     * @param  array<int, int|\WP_Post>  $selected
     * @return array<int, array{id:int,title:string,url:string,text:string,cta:string,image:?string,image_alt:string}>
     */
    public static function activityCards(string $mode = 'all', array $selected = [], int $limit = 0): array
    {
        $ids = self::activityIds($mode, $selected, $limit);

        return array_map([self::class, 'activityCard'], $ids);
    }

    /**
     * Published activity IDs, ordered. Also used by blocks that render the
     * full activity-card component, which reads its own fields from the global post.
     *
     * @param  array<int, int|\WP_Post>  $selected
     * @return array<int, int>
     */
    public static function activityIds(string $mode = 'all', array $selected = [], int $limit = 0): array
    {
        if ($mode === 'selected') {
            $ids = array_map(
                fn ($item) => $item instanceof \WP_Post ? $item->ID : (int) $item,
                $selected
            );

            return array_values(array_filter(
                $ids,
                fn ($id) => $id && get_post_status($id) === 'publish' && get_post_type($id) === 'activity'
            ));
        }

        return array_map('intval', get_posts([
            'post_type'      => 'activity',
            'post_status'    => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]));
    }

    /**
     * Blocks saved before the "source" field existed only had a hand-picked list,
     * so treat a missing source with a non-empty list as 'selected'.
     */
    public static function resolveSource(?string $source, array $selected): string
    {
        if ($source === 'all' || $source === 'selected') {
            return $source;
        }

        return empty($selected) ? 'all' : 'selected';
    }

    /**
     * @return array{id:int,title:string,url:string,text:string,cta:string,image:?string,image_alt:string}
     */
    public static function activityCard(int $id): array
    {
        $icon  = get_field('icon', $id);
        $image = null;
        $alt   = '';

        if (is_array($icon) && ! empty($icon['url'])) {
            $image = $icon['sizes']['medium'] ?? $icon['url'];
            $alt   = (string) ($icon['alt'] ?? '');
        } elseif ($thumb = get_the_post_thumbnail_url($id, 'medium')) {
            $image = $thumb;
        }

        $text = trim((string) get_field('summary', $id));

        if ($text === '') {
            $text = trim((string) get_field('intro', $id));
        }

        if ($text === '') {
            $text = wp_trim_words(wp_strip_all_tags(strip_shortcodes((string) get_post_field('post_content', $id))), 25);
        }

        return [
            'id'        => $id,
            'title'     => get_the_title($id),
            'url'       => (string) get_permalink($id),
            'text'      => $text,
            'cta'       => trim((string) get_field('cta_text', $id)) ?: 'Find out more',
            'image'     => $image,
            'image_alt' => $alt,
        ];
    }

    /**
     * Next occurrences of published events, soonest first, in the
     * EventRecurrence::nextCard() shape (weekday, day, title, detail, sort_ts, url).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function upcomingEvents(int $limit = 3): array
    {
        $cards = array_column(self::eventSchedule(), 'card');

        usort($cards, fn ($a, $b) => $a['sort_ts'] <=> $b['sort_ts']);

        return array_slice($cards, 0, max(1, $limit));
    }

    /**
     * Every published event that still has a next occurrence, split the way the
     * events archive shows them: regular (recurring) events by title, and
     * one-off events soonest first.
     *
     * Deliberately loads all events and asks EventRecurrence about each, rather
     * than filtering on meta in SQL: whether an event repeats depends on Event
     * Type with a fallback for old events, which a meta query cannot express
     * without listing some events twice or not at all. A small charity site has
     * dozens of events, not thousands.
     *
     * @return array{recurring: array<int, int>, one_off: array<int, int>}
     */
    public static function eventGroups(): array
    {
        $recurring = [];
        $oneOff    = [];

        foreach (self::eventSchedule() as $id => $row) {
            if ($row['recurring']) {
                $recurring[$id] = get_the_title($id);
            } else {
                $oneOff[$id] = $row['card']['sort_ts'];
            }
        }

        natcasesort($recurring);
        asort($oneOff);

        return ['recurring' => array_keys($recurring), 'one_off' => array_keys($oneOff)];
    }

    /** @return array<int, array{recurring: bool, card: array<string, mixed>}> */
    private static function eventSchedule(): array
    {
        $ids = get_posts([
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        $rows = [];

        foreach ($ids as $id) {
            $id = (int) $id;

            if ($card = EventRecurrence::nextCard($id)) {
                $rows[$id] = ['recurring' => EventRecurrence::isRecurring($id), 'card' => $card];
            }
        }

        return $rows;
    }
}
