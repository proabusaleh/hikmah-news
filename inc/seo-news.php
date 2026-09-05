<?php
/**
 * News-Specific SEO
 * - Google News compatibility
 * - NewsArticle structured data enhancements
 * - RSS feed optimization for news
 * - AMP-ready markup
 * - Publication date prominence
 * - Author byline optimization
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. GOOGLE NEWS COMPATIBILITY
// ============================================
function hikmahnews_google_news_compat() {
    // Ensure proper date formatting for Google News
    add_filter('get_the_date', function($date, $format, $post) {
        if (is_single() && $post && $post->post_type === 'post') {
            // Google News prefers: "Month Day, Year"
            if ($format === '' || $format === get_option('date_format')) {
                return date_i18n('F j, Y', strtotime($post->post_date));
            }
        }
        return $date;
    }, 10, 3);

    // Add publication name to article schema
    add_filter('hikmahnews_schema_article', function($schema) {
        $schema['publisher'] = [
            '@type' => 'NewsMediaOrganization',
            'name'  => get_bloginfo('name'),
            'logo'  => [
                '@type'  => 'ImageObject',
                'url'    => hikmahnews_get_logo_url(),
                'width'  => 600,
                'height' => 60,
            ],
            'foundingDate' => get_option('hikmahnews_founding_date', '2024-01-01'),
        ];
        return $schema;
    });
}
add_action('init', 'hikmahnews_google_news_compat');

// ============================================
// 2. ENHANCED NEWS ARTICLE SCHEMA
// ============================================
function hikmahnews_news_article_schema() {
    if (!is_single()) return;

    $post = get_queried_object();
    if ($post->post_type !== 'post') return;

    $cats = get_the_category($post->ID);
    $tags = get_the_tags($post->ID);
    $author = get_userdata($post->post_author);
    $is_breaking = get_post_meta($post->ID, '_hikmahnews_breaking', true);
    $reading = hikmahnews_reading_time_detailed($post->ID);

    // Determine article type
    $article_type = 'NewsArticle';
    if ($is_breaking === '1') $article_type = 'ReportageNewsArticle';
    if ($cats) {
        $cat_slug = $cats[0]->slug;
        if ($cat_slug === 'opinion') $article_type = 'OpinionNewsArticle';
        if ($cat_slug === 'analysis') $article_type = 'AnalysisNewsArticle';
        if ($cat_slug === 'review') $article_type = 'ReviewNewsArticle';
    }

    $schema = [
        '@context'         => 'https://schema.org',
        '@type'            => $article_type,
        'headline'         => get_the_title(),
        'description'      => wp_strip_all_tags(get_the_excerpt()),
        'datePublished'    => get_the_date('c'),
        'dateModified'     => get_the_modified_date('c'),
        'author'           => [
            '@type' => 'Person',
            'name'  => $author->display_name,
            'url'   => get_author_posts_url($author->ID),
            'jobTitle' => get_the_author_meta('job_title', $author->ID) ?: 'Journalist',
        ],
        'publisher'        => [
            '@type' => 'NewsMediaOrganization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => hikmahnews_get_logo_url(),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ],
        'wordCount'        => $reading['words'],
        'isAccessibleForFree' => true,
    ];

    // Image
    if (has_post_thumbnail()) {
        $img_data = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        $schema['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $img_data[0],
            'width'  => $img_data[1],
            'height' => $img_data[2],
        ];
        $schema['thumbnailUrl'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
    }

    // Category
    if ($cats) {
        $schema['articleSection'] = $cats[0]->name;
    }

    // Keywords from tags
    if ($tags) {
        $schema['keywords'] = implode(', ', wp_list_pluck($tags, 'name'));
    }

    // Apply filter for extensibility
    $schema = apply_filters('hikmahnews_schema_article', $schema);

    echo '<script type="application/ld+json">'
         . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
         . '</script>' . "\n";

    // Also output LiveBlogPosting for breaking news
    if ($is_breaking === '1') {
        $live_schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'LiveBlogPosting',
            'headline'      => get_the_title(),
            'datePublished' => get_the_date('c'),
            'dateModified'  => get_the_modified_date('c'),
            'coverageStartTime' => get_the_date('c'),
            'author'        => [
                '@type' => 'Person',
                'name'  => $author->display_name,
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
            ],
        ];
        echo '<script type="application/ld+json">'
             . wp_json_encode($live_schema, JSON_UNESCAPED_SLASHES)
             . '</script>' . "\n";
    }
}
// The enhanced schema above replaces the basic NewsArticle from Step 29.
// Site-level schemas (WebSite + SearchAction, NewsMediaOrganization,
// BreadcrumbList, CollectionPage) are preserved: schema-markup.php skips its
// own article block when this handler is active (see hikmahnews_schema_output()).
add_action('wp_head', 'hikmahnews_news_article_schema', 5);

// ============================================
// 3. RSS FEED OPTIMIZATION FOR NEWS
// ============================================
function hikmahnews_rss_optimization() {
    // Add featured image to RSS
    add_filter('the_excerpt_rss', function($content) {
        global $post;
        if (has_post_thumbnail($post->ID)) {
            $img = get_the_post_thumbnail_url($post->ID, 'hikmahnews-grid');
            $content = '<img src="' . esc_url($img) . '" alt="" style="max-width:100%;">' . $content;
        }
        return $content;
    });

    add_filter('the_content_feed', function($content) {
        global $post;
        if (has_post_thumbnail($post->ID)) {
            $img = get_the_post_thumbnail_url($post->ID, 'hikmahnews-grid');
            $content = '<figure><img src="' . esc_url($img) . '" alt=""></figure>' . $content;
        }
        return $content;
    });

    // Add category to RSS
    add_filter('the_category_rss', function($output) {
        return $output;
    });

    // Custom RSS footer
    add_filter('the_content_feed', function($content) {
        $content .= '<hr><p><em>This article was originally published on '
                    . get_bloginfo('name') . '. '
                    . '<a href="' . get_permalink() . '">Read the full article</a>.</em></p>';
        return $content;
    });
}
add_action('init', 'hikmahnews_rss_optimization');

// ============================================
// 4. AMP-READY MARKUP
// ============================================
function hikmahnews_amp_compat() {
    // Only if AMP plugin is active
    if (!function_exists('is_amp_endpoint')) return;

    // Add AMP-specific styles
    add_action('amp_post_template_css', function() {
        ?>
        .hikmahnews-ad { display: none; }
        .breaking-alert { display: none; }
        .reading-progress { display: none; }
        .notification-prompt { display: none; }
        .hikmahnews-ad--mobile_sticky { display: none; }
        .single-header__title { font-size: 28px; }
        .single-content__body { font-size: 16px; line-height: 1.7; }
        <?php
    });

    // Add AMP analytics
    add_action('amp_post_template_footer', function() {
        ?>
        <amp-analytics type="gtag" data-credentials="include">
            <script type="application/json">
            {
                "vars": {
                    "gtag_id": "<?php echo esc_js(get_option('hikmahnews_ga_id', '')); ?>",
                    "config": {
                        "<?php echo esc_js(get_option('hikmahnews_ga_id', '')); ?>": {
                            "groups": "default"
                        }
                    }
                }
            }
            </script>
        </amp-analytics>
        <?php
    });
}
add_action('init', 'hikmahnews_amp_compat');

// ============================================
// 5. PUBLICATION DATE PROMINENCE (SEO)
// ============================================
function hikmahnews_date_prominence() {
    if (!is_single()) return;

    // Add time element with datetime attribute (already in single.php)
    // This ensures Google can properly parse the publication date

    // Add "Last Updated" to modified posts
    $published = get_the_date('U');
    $modified = get_the_modified_date('U');
    $diff = $modified - $published;

    if ($diff > 86400) { // More than 1 day difference
        add_filter('the_content', function($content) use ($modified) {
            $updated = '<p class="single-updated-notice">
                <strong>📅 Last Updated:</strong> '
                . date_i18n('F j, Y \a\t g:i A', $modified) . '</p>';
            return $updated . $content;
        }, 5);
    }
}
add_action('wp', 'hikmahnews_date_prominence');