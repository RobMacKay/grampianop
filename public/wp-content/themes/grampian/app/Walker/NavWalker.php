<?php

namespace App\Walker;

/**
 * Nav walker that emits Alpine.js-compatible dropdown markup for the GO redesign.
 * Desktop: button/ul with x-data per top-level item; mobile drawer handled separately.
 */
class NavWalker extends \Walker_Nav_Menu
{
    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0): void
    {
        $item = $data_object;

        if ($depth === 0) {
            $hasChildren = in_array('menu-item-has-children', $item->classes, true);
            $isCurrent   = in_array('current-menu-item', $item->classes, true)
                        || in_array('current-menu-ancestor', $item->classes, true);
            $currentAttr = $isCurrent ? ' aria-current="page"' : '';

            if ($hasChildren) {
                $output .= '<li x-data="{ open: false }" class="relative">';
                $output .= '<button'
                    . ' @click="open = !open"'
                    . ' @keydown.escape.window="open = false"'
                    . ' :aria-expanded="open.toString()"'
                    . ' class="flex items-center gap-1 px-[11px] py-[11px] rounded-lg font-heading text-[16px] font-semibold text-go-ink whitespace-nowrap hover:bg-go-mint hover:text-go-green-deep transition-colors"'
                    . '>'
                    . esc_html($item->title)
                    . '<svg class="w-3 h-3 mt-0.5" aria-hidden="true" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    . '</button>';

                $output .= '<ul'
                    . ' x-show="open"'
                    . ' @click.outside="open = false"'
                    . ' x-transition:enter="transition ease-out duration-100"'
                    . ' x-transition:enter-start="opacity-0 -translate-y-1"'
                    . ' x-transition:enter-end="opacity-100 translate-y-0"'
                    . ' x-cloak'
                    . ' class="absolute top-full left-0 mt-[6px] min-w-[270px] bg-white border border-go-line rounded-[14px] shadow-[0_18px_40px_rgba(20,24,26,0.14)] p-[10px] z-50"'
                    . '>';
            } else {
                $url = esc_url($item->url ?: '#');
                $output .= '<li>';
                $output .= '<a href="' . $url . '"'
                    . $currentAttr
                    . ' class="flex items-center px-[11px] py-[11px] rounded-lg font-heading text-[16px] font-semibold text-go-ink whitespace-nowrap hover:bg-go-mint hover:text-go-green-deep transition-colors"'
                    . '>'
                    . esc_html($item->title)
                    . '</a>';
            }
        } elseif ($depth === 1) {
            $url     = esc_url($item->url ?: '#');
            $isCurrent = in_array('current-menu-item', $item->classes, true);
            $currentAttr = $isCurrent ? ' aria-current="page"' : '';

            $output .= '<li>';
            $output .= '<a href="' . $url . '"'
                . $currentAttr
                . ' class="block px-[14px] py-[11px] rounded-[9px] font-body text-[17px] text-go-ink hover:bg-go-mint hover:text-go-green-deep transition-colors"'
                . '>'
                . esc_html($item->title)
                . '</a>';
        }
    }

    public function end_el(&$output, $data_object, $depth = 0, $args = null): void
    {
        $item = $data_object;

        if ($depth === 0 && in_array('menu-item-has-children', $item->classes, true)) {
            $output .= '</ul>';
        }

        $output .= '</li>';
    }
}
