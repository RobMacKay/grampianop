<?php

namespace app;

class Editor
{
    public static function init(): void
    {
        add_filter('use_block_editor_for_post', [self::class, 'maybeDisable'], 10, 2);
    }

    public static function maybeDisable($use, $post)
    {
        if (!$post) {
            return $use;
        }

        // Keep classic editor for data-entry CPTs that use ACF metaboxes only
        if (in_array($post->post_type, ['position', 'activity', 'event', 'contact_submission', 'referral'], true)) {
            return false;
        }

        return $use;
    }
}
