<?php
/**
 * Gutenberg Blocks — Server-Side Render Callbacks
 * All 11 custom blocks
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. FEATURED NEWS
// ============================================
function hikmahnews_render_featured_news($attributes) {
    $count = $attributes['postCount'] ?? 4;
    $posts = hikmahnews_get_featured_posts('hero', $count);

    if (empty($posts)) {
        return '<div class="hikmahnews-block hikmahnews-block--empty">No featured posts found.</div>';
    }

    $html = '<div class="hikmahnews-block hikmahnews-block--featured">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Featured News', '⭐',
        '', 'var(--color-accent)'
    );
    $html .= '<div class="grid grid--' . ($attributes['columns'] ?? 3) . '">';
    foreach ($posts as $post) {
        $html .= hikmahnews_block_render_card($post, 'hikmahnews-grid', $attributes['showExcerpt'] ?? true);
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 2. LATEST NEWS
// ============================================
function hikmahnews_render_latest_news($attributes) {
    $count = $attributes['postCount'] ?? 6;
    $cat = $attributes['category'] ?? '';

    $args = [
        'posts_per_page'      => $count,
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ];
    if ($cat) $args['category_name'] = $cat;

    $posts = get_posts($args);
    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--latest">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Latest News', '🕒',
        get_post_type_archive_link('post')
    );
    $html .= '<div class="grid grid--' . ($attributes['columns'] ?? 3) . '">';
    foreach ($posts as $post) {
        $html .= hikmahnews_block_render_card($post, 'hikmahnews-grid', $attributes['showExcerpt'] ?? true);
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 3. POPULAR NEWS
// ============================================
function hikmahnews_render_popular_news($attributes) {
    $count = $attributes['postCount'] ?? 5;
    $cat = $attributes['category'] ?? '';

    $args = [
        'posts_per_page' => $count,
        'orderby'        => 'meta_value_num',
        'meta_key'       => '_hikmahnews_views',
        'no_found_rows'  => true,
    ];
    if ($cat) $args['category_name'] = $cat;

    $posts = get_posts($args);
    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--popular">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Most Popular', '🏆'
    );
    $html .= '<div class="numbered-list">';
    $num = 1;
    foreach ($posts as $post) {
        $cats = get_the_category($post->ID);
        $html .= '<article class="numbered-item">';
        $html .= '<span class="numbered-item__num ' . ($num <= 3 ? 'numbered-item__num--highlight' : '') . '">';
        $html .= str_pad($num, 2, '0', STR_PAD_LEFT) . '</span>';
        $html .= '<div class="numbered-item__content">';
        if ($cats) {
            $html .= '<span class="numbered-item__cat">' . esc_html($cats[0]->name) . '</span>';
        }
        $html .= '<h4 class="numbered-item__title"><a href="' . get_permalink($post) . '">'
                 . esc_html($post->post_title) . '</a></h4>';
        $html .= '<div class="numbered-item__meta">';
        $html .= '<span>' . get_the_author_meta('display_name', $post->post_author) . '</span>';
        $html .= '<span class="dot"></span>';
        $html .= '<span>👁 ' . hikmahnews_get_formatted_views($post->ID) . '</span>';
        $html .= '</div></div></article>';
        $num++;
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 4. TRENDING NEWS
// ============================================
function hikmahnews_render_trending_news($attributes) {
    $count = $attributes['postCount'] ?? 6;
    $posts = hikmahnews_get_trending_posts($count, 48);

    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--trending">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Trending Now', '🔥', '', '#F59E0B'
    );
    $html .= '<div class="trending-grid">';
    $rank = 1;
    foreach ($posts as $post) {
        $score = get_post_meta($post->ID, '_hikmahnews_trending_score', true);
        $cats = get_the_category($post->ID);
        $html .= '<article class="trending-card ' . ($rank <= 3 ? 'trending-card--top' : '') . '">';
        $html .= '<a href="' . get_permalink($post) . '" class="trending-card__link">';
        $html .= '<span class="trending-card__rank">' . str_pad($rank, 2, '0', STR_PAD_LEFT) . '</span>';
        $html .= '<div class="trending-card__image">';
        if (has_post_thumbnail($post)) $html .= get_the_post_thumbnail($post, 'hikmahnews-thumb');
        $html .= '</div>';
        $html .= '<div class="trending-card__content">';
        if ($cats) $html .= '<span class="trending-card__cat">' . esc_html($cats[0]->name) . '</span>';
        $html .= '<h3 class="trending-card__title">' . esc_html($post->post_title) . '</h3>';
        $html .= '<div class="trending-card__meta">';
        $html .= '<span>👁 ' . hikmahnews_get_formatted_views($post->ID) . '</span>';
        $html .= '<span class="dot"></span>';
        $html .= '<span>💬 ' . $post->comment_count . '</span>';
        $html .= '</div></div></a></article>';
        $rank++;
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 5. CATEGORY NEWS
// ============================================
function hikmahnews_render_category_news($attributes) {
    $cat = $attributes['category'] ?? '';
    $count = $attributes['postCount'] ?? 6;

    if (!$cat) {
        return '<div class="hikmahnews-block hikmahnews-block--empty">Select a category in block settings.</div>';
    }

    $category = get_category_by_slug($cat);
    if (!$category) return '';

    $color = hikmahnews_get_category_color($category->term_id);
    $icon = hikmahnews_get_category_icon($category->term_id);

    $posts = get_posts([
        'category_name'  => $cat,
        'posts_per_page' => $count,
        'no_found_rows'  => true,
    ]);

    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--category" style="--cat-color:' . esc_attr($color) . ';">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?: $category->name,
        $icon,
        get_category_link($category),
        $color
    );
    $html .= '<div class="grid grid--' . ($attributes['columns'] ?? 3) . '">';
    foreach ($posts as $post) {
        $html .= hikmahnews_block_render_card($post, 'hikmahnews-grid', $attributes['showExcerpt'] ?? true);
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 6. NEWS SLIDER
// ============================================
function hikmahnews_render_news_slider($attributes) {
    $count = $attributes['postCount'] ?? 6;
    $cat = $attributes['category'] ?? '';

    $args = ['posts_per_page' => $count, 'no_found_rows' => true];
    if ($cat) $args['category_name'] = $cat;

    $posts = get_posts($args);
    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--slider">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Top Stories', '📰'
    );
    $html .= '<div class="home-carousel hikmahnews-slider">';
    foreach ($posts as $post) {
        $html .= '<article class="news-card home-carousel__card">';
        $html .= '<div class="news-card__image"><a href="' . get_permalink($post) . '">';
        if (has_post_thumbnail($post)) $html .= get_the_post_thumbnail($post, 'hikmahnews-grid');
        $html .= '</a></div>';
        $html .= '<div class="news-card__body">';
        $html .= '<h3 class="news-card__title"><a href="' . get_permalink($post) . '">'
                 . esc_html($post->post_title) . '</a></h3>';
        $html .= '<div class="news-card__meta"><time>'
                 . human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' ago</time></div>';
        $html .= '</div></article>';
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 7. NEWS GRID
// ============================================
function hikmahnews_render_news_grid($attributes) {
    $count = $attributes['postCount'] ?? 6;
    $cat = $attributes['category'] ?? '';
    $cols = $attributes['columns'] ?? 3;

    $args = ['posts_per_page' => $count, 'no_found_rows' => true];
    if ($cat) $args['category_name'] = $cat;

    $posts = get_posts($args);
    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--grid">';
    if (!empty($attributes['title'])) {
        $html .= hikmahnews_block_section_title($attributes['title'], '📰');
    }
    $html .= '<div class="grid grid--' . $cols . '">';
    foreach ($posts as $post) {
        $html .= hikmahnews_block_render_card($post, 'hikmahnews-grid', $attributes['showExcerpt'] ?? true);
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 8. NEWS LIST
// ============================================
function hikmahnews_render_news_list($attributes) {
    $count = $attributes['postCount'] ?? 5;
    $cat = $attributes['category'] ?? '';

    $args = ['posts_per_page' => $count, 'no_found_rows' => true];
    if ($cat) $args['category_name'] = $cat;

    $posts = get_posts($args);
    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--list">';
    if (!empty($attributes['title'])) {
        $html .= hikmahnews_block_section_title($attributes['title'], '📋');
    }
    $html .= '<div class="cat-block-list">';
    foreach ($posts as $post) {
        $html .= hikmahnews_block_render_list_item($post);
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 9. VIDEO NEWS
// ============================================
function hikmahnews_render_video_news($attributes) {
    $count = $attributes['postCount'] ?? 4;

    $posts = get_posts([
        'category_name'  => 'video',
        'posts_per_page' => $count,
        'no_found_rows'  => true,
    ]);

    if (empty($posts)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--video">';
    $html .= hikmahnews_block_section_title(
        $attributes['title'] ?? 'Video News', '🎬', '', '#EF4444'
    );
    $html .= '<div class="video-grid">';
    foreach ($posts as $post) {
        $html .= '<article class="video-card">';
        $html .= '<a href="' . get_permalink($post) . '" class="video-card__link">';
        $html .= '<div class="video-card__thumbnail">';
        if (has_post_thumbnail($post)) $html .= get_the_post_thumbnail($post, 'hikmahnews-grid');
        $html .= '<div class="video-card__play"><svg width="48" height="48" viewBox="0 0 24 24" fill="white">
                  <polygon points="5 3 19 12 5 21 5 3"/></svg></div>';
        $html .= '</div>';
        $html .= '<div class="video-card__body">';
        $html .= '<h3 class="video-card__title">' . esc_html($post->post_title) . '</h3>';
        $html .= '</div></a></article>';
    }
    $html .= '</div></div>';
    return $html;
}

// ============================================
// 10. BREAKING NEWS
// ============================================
function hikmahnews_render_breaking_news($attributes) {
    $breaking = hikmahnews_get_breaking_posts(6);
    if (empty($breaking)) return '';

    $html = '<div class="hikmahnews-block hikmahnews-block--breaking">';
    $html .= '<div class="breaking-ticker" style="position:relative;">';
    $html .= '<div class="container breaking-ticker__inner">';
    $html .= '<span class="breaking-ticker__label"><span class="breaking-ticker__dot"></span>LIVE</span>';
    $html .= '<div class="breaking-ticker__track-wrapper"><div class="breaking-ticker__track">';
    foreach ($breaking as $post) {
        $html .= '<a href="' . get_permalink($post) . '" class="breaking-ticker__item">'
                 . esc_html($post->post_title) . '</a>';
    }
    // Duplicate for seamless loop
    foreach ($breaking as $post) {
        $html .= '<a href="' . get_permalink($post) . '" class="breaking-ticker__item">'
                 . esc_html($post->post_title) . '</a>';
    }
    $html .= '</div></div></div></div></div>';
    return $html;
}

// ============================================
// 11. ADVERTISEMENT
// ============================================
function hikmahnews_render_advertisement($attributes) {
    $position = $attributes['adPosition'] ?? 'homepage_top';
    ob_start();
    hikmahnews_render_ad($position);
    return ob_get_clean();
}