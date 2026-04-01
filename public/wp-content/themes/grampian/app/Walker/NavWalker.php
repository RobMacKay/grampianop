<?php

namespace App\Walker;

/**
 * Custom nav walker that outputs Flowbite-compatible dropdown markup.
 */
class NavWalker extends \Walker_Nav_Menu
{
    /** @var int */
    private int $dropdownCount = 0;

    /**
     * Start the element output (the <li> and its <a>/<button>).
     */
    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0): void
    {
        $item = $data_object;

        if ($depth === 0) {
            $hasChildren = in_array('menu-item-has-children', $item->classes, true);

            if ($hasChildren) {
                $this->dropdownCount++;
                $id    = 'dropdownNavbar' . $this->dropdownCount;
                $btnId = 'dropdownNavbarLink' . $this->dropdownCount;

                $output .= '<li>';
                $output .= '<button'
                    . ' id="' . esc_attr($btnId) . '"'
                    . ' data-dropdown-toggle="' . esc_attr($id) . '"'
                    . ' class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-white md:p-0 md:w-auto lg:border border-gray-200 lg:px-3 lg:py-2 rounded-md bg-white transition-colors"'
                    . ' aria-expanded="false"'
                    . '>'
                    . esc_html($item->title)
                    . '<svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/></svg>'
                    . '</button>';

                $output .= '<div id="' . esc_attr($id) . '" class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-lg w-48">';
                $output .= '<ul class="py-2 text-sm text-gray-700" aria-labelledby="' . esc_attr($btnId) . '">';
            } else {
                $url     = $item->url ?: '#';
                $current = in_array('current-menu-item', $item->classes, true) ? ' aria-current="page"' : '';

                $output .= '<li>';
                $output .= '<a href="' . esc_url($url) . '"'
                    . $current
                    . ' class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-white md:p-0 md:w-auto lg:border border-gray-200 lg:px-3 lg:py-2 rounded-md bg-white transition-colors"'
                    . '>'
                    . esc_html($item->title)
                    . '</a>';
            }
        } elseif ($depth === 1) {
            $url     = $item->url ?: '#';
            $current = in_array('current-menu-item', $item->classes, true) ? ' aria-current="page"' : '';

            $output .= '<li>';
            $output .= '<a href="' . esc_url($url) . '"'
                . $current
                . ' class="block px-4 py-2 hover:bg-gray-100"'
                . '>'
                . esc_html($item->title)
                . '</a>';
        }
    }

    /**
     * End element — close the dropdown wrappers if this item had children.
     */
    public function end_el(&$output, $data_object, $depth = 0, $args = null): void
    {
        $item = $data_object;

        if ($depth === 0 && in_array('menu-item-has-children', $item->classes, true)) {
            $output .= '</ul></div>';
        }

        $output .= '</li>';
    }
}
