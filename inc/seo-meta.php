<?php
/**
 * Complete SEO System
 * - Meta title & description
 * - Canonical URLs
 * - Robots meta
 * - Pagination rel links
 * - SEO plugin compatibility
 * - Noindex for thin pages
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. SEO META TAGS
// ============================================
function hikmahnews_seo_meta_tags() {
    // Don't output if Yoast/RankMath is active
    if (hikmahnews_seo_plugin_active()) return;

    $title = hikmahnews_seo_title();
    $description = hikmahnews_seo_description();
    $canonical = hikmahnews_seo_canonical();
    $robots = hikmahnews_seo_robots();

    // Meta Description
    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }

    // Canonical
    if ($canonical) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }

    // Robots
    if ($robots) {
        echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
    }

    // Pagination
    hikmahnews_seo_pagination_links();

    // Hreflang (basic)
    $locale = get_locale();
    echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
}
add_action('wp_head', 'hikmahnews_seo_meta_tags', 1);

// ============================================
// 2. SEO TITLE
// ============================================
function hikmahnews_seo_title() {
    $site_name = get_bloginfo('name');
    $separator = '—';

    if (is_front_page()) {
        return $site_name . ' ' . $separator . ' ' . get_bloginfo('description');
    }

    if (is_single()) {
        $title = get_the_title();
        $cats = get_the_category();
        $cat_name = $cats ? $cats[0]->name : '';
        return $title . ' ' . $separator . ' ' . $cat_name . ' ' . $separator . ' ' . $site_name;
    }

    if (is_category()) {
        return single_cat_title('', false) . ' News ' . $separator . ' ' . $site_name;
    }

    if (is_tag()) {
        return single_tag_title('', false) . ' ' . $separator . ' ' . $site_name;
    }

    if (is_author()) {
        return get_the_author() . ' — Author ' . $separator . ' ' . $site_name;
    }

    if (is_search()) {
        return 'Search: ' . get_search_query() . ' ' . $separator . ' ' . $site_name;
    }

    if (is_404()) {
        return 'Page Not Found ' . $separator . ' ' . $site_name;
    }

    if (is_archive()) {
        return get_the_archive_title() . ' ' . $separator . ' ' . $site_name;
    }

    return wp_get_document_title();
}

// ============================================
// 3. SEO DESCRIPTION
// ============================================
function hikmahnews_seo_description() {
    if (is_front_page()) {
        return get_bloginfo('description');
    }

    if (is_single()) {
        $post = get_queried_object();
        if ($post->post_excerpt) {
            return wp_trim_words(wp_strip_all_tags($post->post_excerpt), 25, '...');
        }
        return wp_trim_words(wp_strip_all_tags($post->post_content), 25, '...');
    }

    if (is_category()) {
        $desc = category_description();
        if ($desc) return wp_strip_all_tags($desc);
        return single_cat_title('', false) . ' news, analysis, and latest updates.';
    }

    if (is_tag()) {
        return 'Latest news and articles tagged with ' . single_tag_title('', false) . '.';
    }

    if (is_author()) {
        $bio = get_the_author_meta('description');
        return $bio ?: 'Articles by ' . get_the_author() . '.';
    }

    if (is_search()) {
        global $wp_query;
        return 'Search results for "' . get_search_query() . '" — '
               . $wp_query->found_posts . ' articles found.';
    }

    return get_bloginfo('description');
}

// ============================================
// 4. CANONICAL URL
// ============================================
function hikmahnews_seo_canonical() {
    if (is_front_page()) return home_url('/');
    if (is_single() || is_page()) return get_permalink();
    if (is_category()) return get_category_link(get_queried_object_id());
    if (is_tag()) return get_tag_link(get_queried_object_id());
    if (is_author()) return get_author_posts_url(get_queried_object_id());
    if (is_search()) return get_search_link(get_search_query());

    // Other archives (tags/terms, dates, CPAs): resolve from queried object
    if (is_archive()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            return get_term_link($term);
        }
        if (is_date()) {
            $year  = get_query_var('year');
            $month = get_query_var('monthnum');
            $day   = get_query_var('day');
            if ($day) return get_day_link($year, $month, $day);
            if ($month) return get_month_link($year, $month);
            if ($year) return get_year_link($year);
        }
    }

    // Fallback: current request URI
    return home_url($_SERVER['REQUEST_URI']);
}

// ============================================
// 5. ROBOTS META
// ============================================
function hikmahnews_seo_robots() {
    // Noindex thin/low-value pages
    if (is_search() && !have_posts()) return 'noindex, follow';
    if (is_404()) return 'noindex, nofollow';

    // Noindex paginated pages beyond page 2
    $paged = get_query_var('paged') ?: 1;
    if ($paged > 2 && (is_category() || is_tag() || is_archive())) {
        return 'noindex, follow';
    }

    // Noindex date archives (thin content)
    if (is_date()) return 'noindex, follow';

    // Noindex author pages with few posts
    if (is_author()) {
        $count = count_user_posts(get_queried_object_id());
        if ($count < 3) return 'noindex, follow';
    }

    // Default: index, follow
    if (is_single() || is_front_page() || is_category()) {
        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }

    return 'index, follow';
}

// ============================================
// 6. PAGINATION REL LINKS
// ============================================
function hikmahnews_seo_pagination_links() {
    global $wp_query;

    $paged = get_query_var('paged') ?: 1;
    $max = $wp_query->max_num_pages;

    if ($max <= 1) return;

    $base = is_category() ? get_category_link(get_queried_object_id()) :
            (is_tag() ? get_tag_link(get_queried_object_id()) :
            (is_search() ? get_search_link(get_search_query()) : home_url('/')));

    if ($paged > 1) {
        $prev = $paged === 2 ? $base : add_query_arg('paged', $paged - 1, $base);
        echo '<link rel="prev" href="' . esc_url($prev) . '">' . "\n";
    }

    if ($paged < $max) {
        $next = add_query_arg('paged', $paged + 1, $base);
        echo '<link rel="next" href="' . esc_url($next) . '">' . "\n";
    }
}

// ============================================
// 7. SEO PLUGIN COMPATIBILITY
// ============================================
function hikmahnews_seo_plugin_active() {
    // Yoast SEO
    if (defined('WPSEO_VERSION')) return true;
    // Rank Math
    if (defined('RANK_MATH_VERSION')) return true;
    // All in One SEO
    if (defined('AIOSEO_VERSION')) return true;
    // SEOPress
    if (function_exists('seopress_activation')) return true;
    return false;
}

// Tell Yoast/RankMath we have proper schema
function hikmahnews_seo_plugin_compat() {
    if (!hikmahnews_seo_plugin_active()) return;

    // Disable our schema if plugin handles it
    remove_action('wp_head', 'hikmahnews_schema_output', 5);
    remove_action('wp_head', 'hikmahnews_open_graph_tags', 10);
}
add_action('init', 'hikmahnews_seo_plugin_compat');

// ============================================
// 8. XML SITEMAP COMPATIBILITY
// ============================================
function hikmahnews_sitemap_compat() {
    // WordPress 5.5+ built-in sitemap
    add_filter('wp_sitemaps_posts_query_args', function($args, $post_type) {
        if ($post_type === 'post') {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key'     => '_hikmahnews_breaking',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'   => '_hikmahnews_breaking',
                    'value' => '1',
                ],
            ];
        }
        return $args;
    }, 10, 2);

    // Add lastmod for news posts
    add_filter('wp_sitemaps_posts_entry', function($entry, $post) {
        if ($post->post_type === 'post') {
            $entry['lastmod'] = get_the_modified_date('c', $post);
        }
        return $entry;
    }, 10, 2);
}
add_action('init', 'hikmahnews_sitemap_compat');