<?php

namespace App;

class Blocks
{
    public static function init(): void
    {
        add_action('acf/init', [self::class, 'register']);
        add_filter('block_categories_all', [self::class, 'categories'], 10, 1);
    }

    public static function categories(array $categories): array
    {
        return array_merge([
            [
                'slug'  => 'go-blocks',
                'title' => 'Grampian Opportunities',
                'icon'  => null,
            ],
        ], $categories);
    }

    public static function register(): void
    {
        // Full-width section blocks — these manage their own max-width internally
        // and need to span the full editor canvas width
        $fullWidthBlocks = [
            ['slug' => 'go-hero',            'view' => 'blocks.go-hero',            'title' => 'GO: Hero',            'icon' => 'cover-image'],
            ['slug' => 'go-audience-router', 'view' => 'blocks.go-audience-router', 'title' => 'GO: Audience Router', 'icon' => 'grid-view'],
            ['slug' => 'go-service-grid',    'view' => 'blocks.go-service-grid',    'title' => 'GO: Service Grid',    'icon' => 'layout'],
            ['slug' => 'go-impact-band',     'view' => 'blocks.go-impact-band',     'title' => 'GO: Impact Band',     'icon' => 'chart-bar'],
            ['slug' => 'go-events-strip',    'view' => 'blocks.go-events-strip',    'title' => 'GO: Events Strip',    'icon' => 'calendar-alt'],
            ['slug' => 'go-get-involved',    'view' => 'blocks.go-get-involved',    'title' => 'GO: Get Involved',    'icon' => 'heart'],
            ['slug' => 'go-logo-wall',       'view' => 'blocks.go-logo-wall',       'title' => 'GO: Logo Wall',       'icon' => 'images-alt2'],
            ['slug' => 'go-visit',           'view' => 'blocks.go-visit',           'title' => 'GO: Visit',           'icon' => 'location'],
            ['slug' => 'hero',               'view' => 'blocks.hero',               'title' => 'Hero',                'icon' => 'cover-image'],
            ['slug' => 'overview',           'view' => 'blocks.overview',           'title' => 'Overview / Cards',    'icon' => 'screenoptions'],
            ['slug' => 'call-to-action',     'view' => 'blocks.call-to-action',     'title' => 'Call to Action',      'icon' => 'megaphone'],
            ['slug' => 'activities',         'view' => 'blocks.activities',         'title' => 'Activities',          'icon' => 'list-view'],
        ];

        // Content-width blocks — sit within the normal content column
        $contentBlocks = [
            ['slug' => 'text',       'view' => 'blocks.text',        'title' => 'Text',          'icon' => 'editor-paragraph'],
            ['slug' => 'text-image', 'view' => 'blocks.text-image',  'title' => 'Text + Image',  'icon' => 'align-pull-left'],
            ['slug' => 'image',      'view' => 'blocks.image',        'title' => 'Image',         'icon' => 'format-image'],
            ['slug' => 'video',      'view' => 'blocks.video',        'title' => 'Video',         'icon' => 'video-alt3'],
            ['slug' => 'html',       'view' => 'blocks.html',         'title' => 'Custom HTML',   'icon' => 'editor-code'],
            ['slug' => 'quote',      'view' => 'blocks.quote',        'title' => 'Quote',         'icon' => 'format-quote'],
            ['slug' => 'cards',      'view' => 'blocks.cards',        'title' => 'Cards',         'icon' => 'columns'],
            ['slug' => 'contact-form', 'view' => 'blocks.contact-form', 'title' => 'Contact Form', 'icon' => 'email'],
            ['slug' => 'booking-form', 'view' => 'blocks.booking-form', 'title' => 'Booking Form', 'icon' => 'tickets-alt'],
        ];

        foreach ($fullWidthBlocks as $block) {
            acf_register_block_type([
                'name'            => $block['slug'],
                'title'           => $block['title'],
                'category'        => 'go-blocks',
                'icon'            => $block['icon'],
                'render_callback' => self::renderer($block['view']),
                'supports'        => [
                    'align'  => ['full', 'wide'],
                    'anchor' => true,
                    'jsx'    => false,
                ],
                'align'           => 'full',
                'mode'            => 'preview',
            ]);
        }

        foreach ($contentBlocks as $block) {
            acf_register_block_type([
                'name'            => $block['slug'],
                'title'           => $block['title'],
                'category'        => 'go-blocks',
                'icon'            => $block['icon'],
                'render_callback' => self::renderer($block['view']),
                'supports'        => [
                    'align'  => false,
                    'anchor' => true,
                    'jsx'    => false,
                ],
                'mode'            => 'preview',
            ]);
        }
    }

    private static function renderer(string $view): callable
    {
        return function ($block, $content, $is_preview) use ($view) {
            $fields = get_fields() ?: [];
            echo \Roots\view($view, [
                'block'      => $fields,
                'is_preview' => $is_preview,
                // Blocks that render a form need a unique id per instance,
                // since the same block can appear twice on one page.
                'block_id'   => $block['id'] ?? uniqid('block-'),
            ])->render();
        };
    }
}
