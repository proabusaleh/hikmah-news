<?php
/**
 * Category Mega Menu
 * - Replaces standard dropdown with rich mega menu
 * - Shows category columns with featured post thumbnails
 * - Subcategory links
 * - Mobile: accordion style
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. MEGA MENU WALKER
// ============================================
class HikmahNews_Mega_Menu_Walker extends Walker_Nav_Menu {

    public function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            // Mega menu container
            $output .= '<div class="mega-menu">';
            $output .= '<div class="mega-menu__inner container">';
            $output .= '<div class="mega-menu__columns">';
        } else {
            $output .= '<ul class="mega-menu__sublist">';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</div>'; // columns
            $output .= '</div>'; // inner
            $output .= '</div>'; // mega-menu
        } else {
            $output .= '</ul>';
        }
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $has_children = in_array('menu-item-has-children', $classes);

        if ($depth === 0) {
            $class_str = implode(' ', array_filter($classes));
            $output .= '<li class="' . esc_attr($class_str) . '">';
            $output .= '<a href="' . esc_url($item->url) . '" class="mega-menu__trigger">';
            $output .= esc_html($item->title);
            if ($has_children) {
                $output .= ' <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>';
            }
            $output .= '</a>';
        } elseif ($depth === 1 && $has_children) {
            // Category column header
            $cat = get_category_by_slug($item->post_name);
            $color = $cat ? hikmahnews_get_category_color($cat->term_id) : '#DC2626';
            $icon = $cat ? hikmahnews_get_category_icon($cat->term_id) : '📰';

            $output .= '<div class="mega-menu__column">';
            $output .= '<div class="mega-menu__column-header" style="border-top: 3px solid ' . esc_attr($color) . ';">';
            $output .= '<span class="mega-menu__icon">' . $icon . '</span> ';
            $output .= '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
            $output .= '</div>';

            // Featured post for this category
            if ($cat) {
                $featured = get_posts([
                    'category'       => $cat->term_id,
                    'posts_per_page' => 1,
                    'no_found_rows'  => true,
                ]);
                if ($featured) {
                    $fp = $featured[0];
                    $output .= '<div class="mega-menu__featured">';
                    $output .= '<a href="' . get_permalink($fp) . '">';
                    if (has_post_thumbnail($fp)) {
                        $output .= get_the_post_thumbnail($fp, 'hikmahnews-thumb', ['class' => 'mega-menu__featured-img']);
                    }
                    $output .= '<span class="mega-menu__featured-title">' . esc_html($fp->post_title) . '</span>';
                    $output .= '</a>';
                    $output .= '</div>';
                }
            }
        } elseif ($depth >= 2) {
            // Subcategory link
            $output .= '<li class="mega-menu__subitem">';
            $output .= '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
        } else {
            $output .= '<li><a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</li>';
        } elseif ($depth === 1) {
            $output .= '</div>'; // column
        } else {
            $output .= '</li>';
        }
    }
}

// ============================================
// 2. REGISTER MEGA MENU NAV (replace primary)
// ============================================
function hikmahnews_mega_menu_nav() {
    wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'mega-nav__list',
        'walker'         => new HikmahNews_Mega_Menu_Walker(),
        'fallback_cb'    => 'hikmahnews_fallback_mega_menu',
        'depth'          => 3,
    ]);
}

// Fallback: auto-generate from categories
function hikmahnews_fallback_mega_menu() {
    $parents = get_categories([
        'parent'     => 0,
        'hide_empty' => false,
        'orderby'    => 'term_order',
        'number'     => 8,
    ]);

    echo '<ul class="mega-nav__list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';

    foreach ($parents as $cat) {
        $children = get_categories([
            'parent'     => $cat->term_id,
            'hide_empty' => false,
        ]);

        echo '<li class="menu-item-has-children">';
        echo '<a href="' . esc_url(get_category_link($cat)) . '" class="mega-menu__trigger">';
        echo hikmahnews_get_category_icon($cat->term_id) . ' ' . esc_html($cat->name);
        if ($children) echo ' <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>';
        echo '</a>';

        if ($children) {
            echo '<div class="mega-menu"><div class="mega-menu__inner container">';
            echo '<div class="mega-menu__columns">';
            echo '<div class="mega-menu__column">';
            $color = hikmahnews_get_category_color($cat->term_id);
            echo '<div class="mega-menu__column-header" style="border-top:3px solid ' . esc_attr($color) . ';">';
            echo hikmahnews_get_category_icon($cat->term_id) . ' ' . esc_html($cat->name);
            echo '</div>';
            echo '<ul class="mega-menu__sublist">';
            foreach ($children as $child) {
                echo '<li class="mega-menu__subitem">';
                echo '<a href="' . esc_url(get_category_link($child)) . '">' . esc_html($child->name) . '</a>';
                echo '</li>';
            }
            echo '</ul></div></div></div></div>';
        }

        echo '</li>';
    }

    echo '</ul>';
}