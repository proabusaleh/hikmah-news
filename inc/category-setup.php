<?php
/**
 * Category System — Hierarchical Setup
 * - Auto-creates category tree on theme activation
 * - Parent → Child structure
 * - Category metadata (color, icon, layout, order)
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. CATEGORY STRUCTURE DEFINITION
// ============================================
function wpnews_category_structure() {
    return [
        'politics' => [
            'name'        => 'Politics',
            'description' => 'National and international political news, elections, and policy analysis.',
            'color'       => '#DC2626',
            'icon'        => '🏛️',
            'layout'      => 'standard',
            'children'    => [
                'bangladesh' => [
                    'name'        => 'Bangladesh',
                    'description' => 'Bangladesh political news and updates.',
                    'color'       => '#006A4E',
                    'icon'        => '🇧🇩',
                ],
                'international' => [
                    'name'        => 'International',
                    'description' => 'Global political developments and diplomacy.',
                    'color'       => '#1E40AF',
                    'icon'        => '🌍',
                ],
                'election' => [
                    'name'        => 'Election',
                    'description' => 'Election coverage, results, and analysis.',
                    'color'       => '#7C3AED',
                    'icon'        => '🗳️',
                ],
            ],
        ],
        'business' => [
            'name'        => 'Business',
            'description' => 'Economy, markets, banking, and corporate news.',
            'color'       => '#D97706',
            'icon'        => '💼',
            'layout'      => 'finance',
            'children'    => [
                'economy' => [
                    'name'        => 'Economy',
                    'description' => 'Economic indicators, GDP, and fiscal policy.',
                    'color'       => '#059669',
                    'icon'        => '📊',
                ],
                'banking' => [
                    'name'        => 'Banking',
                    'description' => 'Banking sector news and financial institutions.',
                    'color'       => '#0891B2',
                    'icon'        => '🏦',
                ],
                'markets' => [
                    'name'        => 'Markets',
                    'description' => 'Stock market, commodities, and trading.',
                    'color'       => '#4F46E5',
                    'icon'        => '📈',
                ],
            ],
        ],
        'sports' => [
            'name'        => 'Sports',
            'description' => 'Cricket, football, and all sports coverage.',
            'color'       => '#059669',
            'icon'        => '⚽',
            'layout'      => 'sports',
            'children'    => [
                'football' => [
                    'name'        => 'Football',
                    'description' => 'Football news, leagues, and tournaments.',
                    'color'       => '#16A34A',
                    'icon'        => '⚽',
                ],
                'cricket' => [
                    'name'        => 'Cricket',
                    'description' => 'Cricket matches, scores, and player updates.',
                    'color'       => '#EA580C',
                    'icon'        => '🏏',
                ],
                'other-sports' => [
                    'name'        => 'Other Sports',
                    'description' => 'Tennis, basketball, athletics, and more.',
                    'color'       => '#8B5CF6',
                    'icon'        => '🏅',
                ],
            ],
        ],
        'technology' => [
            'name'        => 'Technology',
            'description' => 'AI, gadgets, software, and digital innovation.',
            'color'       => '#7C3AED',
            'icon'        => '💻',
            'layout'      => 'tech',
            'children'    => [
                'ai' => [
                    'name'        => 'AI',
                    'description' => 'Artificial intelligence and machine learning.',
                    'color'       => '#6D28D9',
                    'icon'        => '🤖',
                ],
                'gadgets' => [
                    'name'        => 'Gadgets',
                    'description' => 'Smartphones, laptops, and tech reviews.',
                    'color'       => '#2563EB',
                    'icon'        => '📱',
                ],
                'software' => [
                    'name'        => 'Software',
                    'description' => 'Apps, platforms, and software development.',
                    'color'       => '#0891B2',
                    'icon'        => '⚙️',
                ],
            ],
        ],
        'entertainment' => [
            'name'        => 'Entertainment',
            'description' => 'Movies, music, celebrity news, and culture.',
            'color'       => '#EC4899',
            'icon'        => '🎬',
            'layout'      => 'standard',
            'children'    => [
                'movies' => [
                    'name'        => 'Movies',
                    'description' => 'Film reviews, releases, and box office.',
                    'color'       => '#BE185D',
                    'icon'        => '🎥',
                ],
                'music' => [
                    'name'        => 'Music',
                    'description' => 'Music news, albums, and concerts.',
                    'color'       => '#9333EA',
                    'icon'        => '🎵',
                ],
            ],
        ],
        'health' => [
            'name'        => 'Health',
            'description' => 'Medical news, wellness, and public health.',
            'color'       => '#0D9488',
            'icon'        => '🏥',
            'layout'      => 'standard',
            'children'    => [],
        ],
        'opinion' => [
            'name'        => 'Opinion',
            'description' => 'Editorials, columns, and expert analysis.',
            'color'       => '#64748B',
            'icon'        => '💬',
            'layout'      => 'opinion',
            'children'    => [],
        ],
    ];
}

// ============================================
// 2. AUTO-CREATE CATEGORIES ON ACTIVATION
// ============================================
function wpnews_create_categories() {
    $structure = wpnews_category_structure();
    $created = get_option('wpnews_categories_created', false);

    if ($created) return;

    foreach ($structure as $parent_slug => $parent_data) {
        // Check if parent exists
        $parent_term = term_exists($parent_slug, 'category');

        if (!$parent_term) {
            $parent_term = wp_insert_term(
                $parent_data['name'],
                'category',
                [
                    'slug'        => $parent_slug,
                    'description' => $parent_data['description'],
                ]
            );
        }

        if (is_wp_error($parent_term)) continue;

        $parent_id = is_array($parent_term) ? $parent_term['term_id'] : $parent_term;

        // Save parent meta
        wpnews_save_category_meta($parent_id, $parent_data);

        // Create children
        if (!empty($parent_data['children'])) {
            foreach ($parent_data['children'] as $child_slug => $child_data) {
                $child_term = term_exists($child_slug, 'category');

                if (!$child_term) {
                    $child_term = wp_insert_term(
                        $child_data['name'],
                        'category',
                        [
                            'slug'        => $child_slug,
                            'description' => $child_data['description'],
                            'parent'      => $parent_id,
                        ]
                    );
                }

                if (is_wp_error($child_term)) continue;

                $child_id = is_array($child_term) ? $child_term['term_id'] : $child_term;
                wpnews_save_category_meta($child_id, $child_data);
            }
        }
    }

    update_option('wpnews_categories_created', true);
}
add_action('after_switch_theme', 'wpnews_create_categories');

// Also provide manual trigger via admin
function wpnews_manual_category_setup() {
    if (isset($_GET['wpnews_setup_cats']) && current_user_can('manage_options')) {
        check_admin_referer('wpnews_setup');
        delete_option('wpnews_categories_created');
        wpnews_create_categories();
        wp_redirect(admin_url('edit-tags.php?taxonomy=category&setup=done'));
        exit;
    }
}
add_action('admin_init', 'wpnews_manual_category_setup');

// ============================================
// 3. SAVE CATEGORY META HELPER
// ============================================
function wpnews_save_category_meta($term_id, $data) {
    if (isset($data['color'])) update_term_meta($term_id, 'wpnews_color', $data['color']);
    if (isset($data['icon'])) update_term_meta($term_id, 'wpnews_icon', $data['icon']);
    if (isset($data['layout'])) update_term_meta($term_id, 'wpnews_layout', $data['layout']);
}

// ============================================
// 4. GET CATEGORY META HELPERS
// ============================================
function wpnews_get_category_color($term_id = null) {
    if (!$term_id) {
        $cats = get_the_category();
        if (!$cats) return 'var(--color-primary)';
        $term_id = $cats[0]->term_id;
    }
    $color = get_term_meta($term_id, 'wpnews_color', true);
    return $color ?: 'var(--color-primary)';
}

function wpnews_get_category_icon($term_id = null) {
    if (!$term_id) {
        $cats = get_the_category();
        if (!$cats) return '📰';
        $term_id = $cats[0]->term_id;
    }
    $icon = get_term_meta($term_id, 'wpnews_icon', true);
    return $icon ?: '📰';
}

function wpnews_get_category_layout($term_id) {
    $layout = get_term_meta($term_id, 'wpnews_layout', true);
    return $layout ?: 'standard';
}

// ============================================
// 5. ADMIN NOTICE
// ============================================
function wpnews_category_setup_notice() {
    if (isset($_GET['setup']) && $_GET['setup'] === 'done') {
        echo '<div class="notice notice-success is-dismissible">
              <p>✅ WP News categories created successfully!</p></div>';
    }
}
add_action('admin_notices', 'wpnews_category_setup_notice');