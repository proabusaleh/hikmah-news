<?php
/**
 * Schema.org JSON-LD Markup
 * - NewsArticle (single posts)
 * - BreadcrumbList
 * - WebSite + SearchAction
 * - Organization
 * - FAQ (if FAQ blocks detected)
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. MAIN SCHEMA OUTPUT
// ============================================
function wpnews_schema_output() {
    $schemas = [];

    // --- WebSite Schema (all pages) ---
    $schemas[] = [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => get_bloginfo('name'),
        'url'             => home_url('/'),
        'description'     => get_bloginfo('description'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => home_url('/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];

    // --- Organization Schema (homepage) ---
    if (is_front_page()) {
        $schemas[] = [
            '@context'    => 'https://schema.org',
            '@type'       => 'NewsMediaOrganization',
            'name'        => get_bloginfo('name'),
            'url'         => home_url('/'),
            'logo'        => wpnews_get_logo_url(),
            'description' => get_bloginfo('description'),
            'sameAs'      => wpnews_get_social_urls(),
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'contactType' => 'editorial',
                'email'       => get_option('admin_email'),
            ],
        ];
    }

    // --- NewsArticle Schema (single posts) ---
    if (is_single()) {
        $post = get_queried_object();
        $author = get_userdata($post->post_author);
        $cats = get_the_category($post->ID);
        $reading = wpnews_reading_time_detailed($post->ID);

        $article_schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => get_the_title(),
            'description'      => wp_strip_all_tags(get_the_excerpt()),
            'datePublished'    => get_the_date('c'),
            'dateModified'     => get_the_modified_date('c'),
            'author'           => [
                '@type' => 'Person',
                'name'  => $author->display_name,
                'url'   => get_author_posts_url($author->ID),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => wpnews_get_logo_url(),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => get_permalink(),
            ],
            'wordCount'        => $reading['words'],
        ];

        // Image
        if (has_post_thumbnail()) {
            $img_url = get_the_post_thumbnail_url($post->ID, 'full');
            $img_data = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
            $article_schema['image'] = [
                '@type'  => 'ImageObject',
                'url'    => $img_url,
                'width'  => $img_data[1] ?? 1200,
                'height' => $img_data[2] ?? 675,
            ];
        }

        // Category / Article Section
        if ($cats) {
            $article_schema['articleSection'] = $cats[0]->name;
        }

        // Breaking news flag
        $is_breaking = get_post_meta($post->ID, '_wpnews_breaking', true);
        if ($is_breaking === '1') {
            $article_schema['@type'] = 'ReportageNewsArticle';
        }

        $schemas[] = $article_schema;

        // --- BreadcrumbList ---
        $breadcrumbs = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'name'     => 'Home',
                    'item'     => home_url('/'),
                ],
            ],
        ];

        if ($cats) {
            $breadcrumbs['itemListElement'][] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $cats[0]->name,
                'item'     => get_category_link($cats[0]->term_id),
            ];
        }

        $breadcrumbs['itemListElement'][] = [
            '@type'    => 'ListItem',
            'position' => $cats ? 3 : 2,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        ];

        $schemas[] = $breadcrumbs;
    }

    // --- Category Page Schema ---
    if (is_category()) {
        $cat = get_queried_object();
        $schemas[] = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $cat->name,
            'description' => $cat->description ?: $cat->name . ' news and articles',
            'url'         => get_category_link($cat->term_id),
        ];
    }

    // Output all schemas
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}
add_action('wp_head', 'wpnews_schema_output', 5);

// ============================================
// 2. HELPER FUNCTIONS
// ============================================
function wpnews_get_logo_url() {
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo) return $logo;
    }
    return get_template_directory_uri() . '/assets/images/logo.png';
}

function wpnews_get_social_urls() {
    $socials = [];
    $networks = ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin'];
    foreach ($networks as $network) {
        $url = get_theme_mod("wpnews_social_{$network}");
        if ($url) $socials[] = $url;
    }
    return $socials;
}

// ============================================
// 3. OPEN GRAPH META TAGS
// ============================================
function wpnews_open_graph_tags() {
    if (is_single()) {
        $post = get_queried_object();
        $cats = get_the_category($post->ID);
        ?>
        <meta property="og:type" content="article">
        <meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>">
        <meta property="og:description" content="<?php echo esc_attr(wp_strip_all_tags(get_the_excerpt())); ?>">
        <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
        <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <meta property="article:published_time" content="<?php echo get_the_date('c'); ?>">
        <meta property="article:modified_time" content="<?php echo get_the_modified_date('c'); ?>">
        <?php if ($cats) : ?>
            <meta property="article:section" content="<?php echo esc_attr($cats[0]->name); ?>">
        <?php endif; ?>
        <?php
        $tags = get_the_tags();
        if ($tags) :
            foreach (array_slice($tags, 0, 5) as $tag) :
        ?>
            <meta property="article:tag" content="<?php echo esc_attr($tag->name); ?>">
        <?php
            endforeach;
        endif;
        ?>
        <?php if (has_post_thumbnail()) : ?>
            <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'wpnews-hero')); ?>">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="675">
        <?php endif; ?>

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr(get_the_title()); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr(wp_strip_all_tags(get_the_excerpt())); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <meta name="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'wpnews-hero')); ?>">
        <?php endif; ?>
        <?php
    }
}
add_action('wp_head', 'wpnews_open_graph_tags', 10);