<?php
/**
 * Gutenberg Blocks — Initialization & Registration
 * - Registers all custom blocks
 * - Server-side rendering for dynamic content
 * - Block assets (editor + frontend CSS/JS)
 * - Block categories
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. BLOCK CATEGORY
// ============================================
function hikmahnews_block_categories($categories, $post) {
    return array_merge(
        [
            [
                'slug'  => 'hikmahnews-blocks',
                'title' => '📰 Hikmah News Blocks',
                'icon'  => 'admin-site-alt3',
            ],
        ],
        $categories
    );
}
add_filter('block_categories_all', 'hikmahnews_block_categories', 10, 2);

// ============================================
// 2. REGISTER ALL BLOCKS
// ============================================
function hikmahnews_register_blocks() {
    $blocks = [
        'featured-news',
        'latest-news',
        'popular-news',
        'trending-news',
        'category-news',
        'news-slider',
        'news-grid',
        'news-list',
        'video-news',
        'breaking-news',
        'advertisement',
    ];

    foreach ($blocks as $block) {
        $block_dir = HIKMAHNEWS_DIR . "/inc/gutenberg/blocks/{$block}";

        if (file_exists("{$block_dir}/block.json")) {
            register_block_type("{$block_dir}/block.json", [
                'render_callback' => "hikmahnews_render_{$block}",
            ]);
        }
    }
}
add_action('init', 'hikmahnews_register_blocks');

// ============================================
// 3. BLOCK EDITOR ASSETS
// ============================================
function hikmahnews_block_editor_assets() {
    // Editor CSS
    wp_enqueue_style(
        'hikmahnews-blocks-editor',
        HIKMAHNEWS_URI . '/inc/gutenberg/editor.css',
        ['wp-edit-blocks'],
        HIKMAHNEWS_VERSION
    );

    // Editor JS (block previews, inspector controls)
    wp_enqueue_script(
        'hikmahnews-blocks-editor',
        HIKMAHNEWS_URI . '/inc/gutenberg/editor.js',
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render'],
        HIKMAHNEWS_VERSION,
        true
    );

    // Pass data to editor
    $categories = get_categories(['hide_empty' => false, 'parent' => 0]);
    $cat_options = array_map(function($c) {
        return ['value' => $c->slug, 'label' => $c->name];
    }, $categories);

    wp_localize_script('hikmahnews-blocks-editor', 'hikmahnewsBlockData', [
        'categories' => $cat_options,
        'ajax_url'   => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('hikmahnews_nonce'),
    ]);
}
add_action('enqueue_block_editor_assets', 'hikmahnews_block_editor_assets');

// ============================================
// 4. BLOCK FRONTEND ASSETS
// ============================================
function hikmahnews_block_frontend_assets() {
    if (has_block('hikmahnews/news-slider')) {
        wp_enqueue_script(
            'hikmahnews-slider',
            HIKMAHNEWS_URI . '/inc/gutenberg/blocks/news-slider/slider.js',
            [],
            HIKMAHNEWS_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'hikmahnews_block_frontend_assets');

// ============================================
// 5. SHARED BLOCK RENDER HELPERS
// ============================================
function hikmahnews_block_wrapper($attributes, $content) {
    $classes = ['hikmahnews-block'];
    if (!empty($attributes['className'])) {
        $classes[] = $attributes['className'];
    }
    if (!empty($attributes['align'])) {
        $classes[] = 'align' . $attributes['align'];
    }

    $style = '';
    if (!empty($attributes['backgroundColor'])) {
        $style .= 'background-color:' . $attributes['backgroundColor'] . ';';
    }

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        esc_attr(implode(' ', $classes)),
        esc_attr($style),
        $content
    );
}

function hikmahnews_block_section_title($title, $icon = '', $link = '', $color = '') {
    $border = $color ? "border-bottom-color:{$color};" : '';
    $html = '<div class="section-title" style="' . esc_attr($border) . '">';
    $html .= '<h2 class="section-title__text">';
    if ($icon) $html .= '<span class="section-title__icon">' . esc_html($icon) . '</span> ';
    $html .= esc_html($title);
    $html .= '</h2>';
    $html .= '<div class="section-title__line"></div>';
    if ($link) {
        $html .= '<a href="' . esc_url($link) . '" class="btn btn--outline btn--sm">View All →</a>';
    }
    $html .= '</div>';
    return $html;
}

function hikmahnews_block_render_card($post, $size = 'hikmahnews-grid', $show_excerpt = true) {
    $cats = get_the_category($post->ID);
    $color = $cats ? hikmahnews_get_category_color($cats[0]->term_id) : 'var(--color-primary)';

    $html = '<article class="news-card">';
    $html .= '<div class="news-card__image">';
    $html .= '<a href="' . get_permalink($post) . '">';
    if (has_post_thumbnail($post)) {
        $html .= get_the_post_thumbnail($post, $size);
    }
    $html .= '</a>';
    if ($cats) {
        $html .= '<span class="badge news-card__badge" style="background:' . esc_attr($color) . ';">'
                  . esc_html($cats[0]->name) . '</span>';
    }
    $html .= '</div>';
    $html .= '<div class="news-card__body">';
    $html .= '<h3 class="news-card__title"><a href="' . get_permalink($post) . '">'
             . esc_html($post->post_title) . '</a></h3>';
    if ($show_excerpt) {
        $html .= '<p class="news-card__excerpt">'
                 . wp_trim_words(wp_strip_all_tags($post->post_excerpt ?: $post->post_content), 15, '...')
                 . '</p>';
    }
    $html .= '<div class="news-card__meta">';
    $html .= '<span class="author">' . get_the_author_meta('display_name', $post->post_author) . '</span>';
    $html .= '<span class="dot"></span>';
    $html .= '<time>' . human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' ago</time>';
    if (function_exists('hikmahnews_get_formatted_views')) {
        $html .= '<span class="dot"></span>';
        $html .= '<span>👁 ' . hikmahnews_get_formatted_views($post->ID) . '</span>';
    }
    $html .= '</div></div></article>';
    return $html;
}

function hikmahnews_block_render_list_item($post) {
    $cats = get_the_category($post->ID);

    $html = '<article class="news-list-card">';
    $html .= '<div class="news-list-card__image">';
    $html .= '<a href="' . get_permalink($post) . '">';
    if (has_post_thumbnail($post)) {
        $html .= get_the_post_thumbnail($post, 'hikmahnews-thumb');
    }
    $html .= '</a></div>';
    $html .= '<div class="news-list-card__body">';
    if ($cats) {
        $html .= '<span class="hero-side__cat">' . esc_html($cats[0]->name) . '</span>';
    }
    $html .= '<h4 class="news-list-card__title"><a href="' . get_permalink($post) . '">'
             . esc_html($post->post_title) . '</a></h4>';
    $html .= '<div class="news-card__meta">';
    $html .= '<time>' . human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' ago</time>';
    $html .= '</div></div></article>';
    return $html;
}