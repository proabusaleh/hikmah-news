<?php
/**
 * External Integrations: WPML/Polylang, Facebook Instant Articles, Apple News
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. MULTILINGUAL COMPATIBILITY (WPML / Polylang)
// ============================================
function hikmahnews_multilingual_compat() {
    // WPML
    if (defined('ICL_SITEPRESS_VERSION')) {
        add_filter('hikmahnews_trending_posts_args', function($args) {
            $args['suppress_filters'] = false;
            return $args;
        });
    }
    // Polylang
    if (function_exists('pll_current_language')) {
        add_filter('hikmahnews_breaking_query', function($args) {
            $args['lang'] = pll_current_language();
            return $args;
        });
    }
}
add_action('init', 'hikmahnews_multilingual_compat');

// ============================================
// 2. FACEBOOK INSTANT ARTICLES RSS FEED
// Feed: /feed/instant-articles
// ============================================
function hikmahnews_fb_instant_articles_feed() {
    add_feed('instant-articles', 'hikmahnews_fb_ia_render');
}
add_action('init', 'hikmahnews_fb_instant_articles_feed');

function hikmahnews_fb_ia_render() {
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    $posts = get_posts(['posts_per_page' => 20, 'no_found_rows' => true]);
    echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?>';
    ?>
    <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?php bloginfo_rss('name'); ?> — Instant Articles</title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <?php foreach ($posts as $post) : setup_postdata($post); ?>
        <item>
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <guid><?php the_guid(); ?></guid>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $post->post_date_gmt, false); ?></pubDate>
            <content:encoded><![CDATA[
                <!doctype html>
                <html><head><meta charset="utf-8"><title><?php the_title(); ?></title>
                <link rel="canonical" href="<?php the_permalink(); ?>">
                <meta property="op:markup_version" content="v1.0"></head>
                <body><article><?php the_content(); ?></article></body></html>
            ]]></content:encoded>
        </item>
        <?php endforeach; wp_reset_postdata(); ?>
    </channel></rss>
    <?php
}

// ============================================
// 3. APPLE NEWS FORMAT FEED
// Feed: /feed/apple-news
// ============================================
function hikmahnews_apple_news_feed() {
    add_feed('apple-news', 'hikmahnews_apple_render');
}
add_action('init', 'hikmahnews_apple_news_feed');

function hikmahnews_apple_render() {
    header('Content-Type: application/json; charset=' . get_option('blog_charset'));
    $posts = get_posts(['posts_per_page' => 20, 'no_found_rows' => true]);
    $articles = [];
    foreach ($posts as $post) {
        $articles[] = [
            'identifier'    => 'hikmahnews-' . $post->ID,
            'language'      => get_locale(),
            'title'         => $post->post_title,
            'body'          => wp_strip_all_tags($post->post_content),
            'datePublished' => get_the_date('c', $post),
            'dateModified'  => get_the_modified_date('c', $post),
            'url'           => get_permalink($post),
        ];
    }
    echo wp_json_encode(['articles' => $articles], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}