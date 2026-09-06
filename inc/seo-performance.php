<?php
/**
 * Performance & Technical SEO
 * - Native lazy loading with exclusions
 * - Preload critical resources
 * - DNS prefetch
 * - Remove unnecessary WP head items
 * - Defer non-critical JS
 * - Image optimization hints
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. LAZY LOADING (Enhanced)
// ============================================
function hikmahnews_lazy_load_setup() {
    // WordPress 5.5+ native lazy loading
    add_filter('wp_lazy_loading_enabled', '__return_true');

    // Exclude hero images and above-fold from lazy loading
    add_filter('wp_img_tag_add_loading_attr', function($value, $image, $context) {
        // Don't lazy load the first image in single posts (LCP)
        if (is_single() && $context === 'the_content') {
            static $first_image = true;
            if ($first_image) {
                $first_image = false;
                return false; // Eager load first image
            }
        }

        // Don't lazy load featured images
        if (strpos($image, 'hikmahnews-hero') !== false) return false;
        if (strpos($image, 'attachment-full') !== false && is_single()) return false;

        return $value;
    }, 10, 3);

    // Add decoding="async" to all images
    add_filter('wp_img_tag_add_decoding_attr', function($value) {
        return 'async';
    });
}
add_action('init', 'hikmahnews_lazy_load_setup');

// ============================================
// 2. RESOURCE HINTS (Preload, Prefetch, Preconnect)
// ============================================
function hikmahnews_resource_hints() {
    // Preconnect for external resources
    $preconnect = [
        'https://fonts.googleapis.com',
        'https://fonts.gstatic.com',
        'https://www.googletagmanager.com',
    ];

    foreach ($preconnect as $url) {
        echo '<link rel="preconnect" href="' . esc_url($url) . '" crossorigin>' . "\n";
    }

    // DNS Prefetch
    $prefetch = [
        'https://www.google-analytics.com',
        'https://pagead2.googlesyndication.com',
        'https://www.googletagservices.com',
    ];

    foreach ($prefetch as $url) {
        echo '<link rel="dns-prefetch" href="' . esc_url($url) . '">' . "\n";
    }

    // Preload critical font
    echo '<link rel="preload" href="https://fonts.gstatic.com/s/inter/v13/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiJ-Ek-_EeA.woff2"
          as="font" type="font/woff2" crossorigin>' . "\n";

    // Preload hero image on single posts (LCP optimization)
    if (is_single() && has_post_thumbnail()) {
        $hero_url = get_the_post_thumbnail_url(get_the_ID(), 'hikmahnews-hero');
        if ($hero_url) {
            echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
        }
    }
}
add_action('wp_head', 'hikmahnews_resource_hints', 0);

// ============================================
// 3. CLEAN UP WP HEAD (Remove Bloat)
// ============================================
function hikmahnews_clean_head() {
    // Remove WP version
    remove_action('wp_head', 'wp_generator');

    // Remove RSD link
    remove_action('wp_head', 'rsd_link');

    // Remove WLW manifest
    remove_action('wp_head', 'wlwmanifest_link');

    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);

    // Remove REST API link (keep for Gutenberg)
    // remove_action('wp_head', 'rest_output_link_wp_head', 10);

    // Remove oEmbed links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    add_filter('emoji_svg_url', '__return_false');

    // Remove recent comments CSS
    add_filter('show_recent_comments_widget_style', '__return_false');
}
add_action('init', 'hikmahnews_clean_head');

// ============================================
// 4. DEFER NON-CRITICAL JAVASCRIPT
// ============================================
function hikmahnews_defer_scripts($tag, $handle, $src) {
    // Skip the admin area entirely — deferred scripts break core's
    // synchronous inline -js-after/-extra blocks (wp.i18n, wp.data, etc).
    if (is_admin()) return $tag;

    // Don't defer these
    $no_defer = ['jquery', 'jquery-core', 'jquery-migrate', 'hikmahnews-main'];

    // Core packages load with inline scripts that depend on the library
    // already being evaluated — leave them untouched on the frontend too.
    if (in_array($handle, $no_defer) || strpos($handle, 'wp-') === 0) return $tag;

    // Add defer to all other scripts
    if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
        $tag = str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'hikmahnews_defer_scripts', 10, 3);

// ============================================
// 5. IMAGE OPTIMIZATION
// ============================================
function hikmahnews_image_optimization() {
    // Add srcset and sizes for responsive images
    add_filter('wp_calculate_image_sizes', function($sizes, $size) {
        if (is_array($size)) {
            $width = $size[0];
        } else {
            $width = 800;
        }
        return '(max-width: 576px) 100vw, (max-width: 768px) 50vw, ' . $width . 'px';
    }, 10, 2);

    // Add WebP support check class to body
    add_filter('body_class', function($classes) {
        $classes[] = 'webp-check';
        return $classes;
    });

    // Fetch priority for LCP image
    add_filter('wp_img_tag_add_loading_attr', function($value, $image, $context) {
        if (strpos($image, 'fetchpriority') !== false) return $value;
        return $value;
    }, 20, 3);
}
add_action('init', 'hikmahnews_image_optimization');

// ============================================
// 6. ADD FETCHPRIORITY TO LCP IMAGE
// ============================================
function hikmahnews_lcp_fetchpriority($attr, $attachment, $size) {
    if (is_single() && has_post_thumbnail() && $attachment->ID === get_post_thumbnail_id()) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'hikmahnews_lcp_fetchpriority', 10, 3);

// ============================================
// 7. REMOVE QUERY STRINGS FROM STATIC RESOURCES
// ============================================
function hikmahnews_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
// Uncomment to enable (may break cache busting):
// add_filter('script_loader_src', 'hikmahnews_remove_query_strings', 15);
// add_filter('style_loader_src', 'hikmahnews_remove_query_strings', 15);

// ============================================
// 8. GZIP HINT IN HTACCESS (Admin Notice)
// ============================================
function hikmahnews_performance_notice() {
    if (!current_user_can('manage_options')) return;

    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard') return;

    echo '<div class="notice notice-info is-dismissible">
          <p><strong>💰 Hikmah News Performance Tips:</strong></p>
          <ul style="list-style:disc;margin-left:20px;">
              <li>Install a caching plugin (WP Rocket, LiteSpeed, W3 Total Cache)</li>
              <li>Enable GZIP/Brotli compression on your server</li>
              <li>Use a CDN (Cloudflare, BunnyCDN) for static assets</li>
              <li>Optimize images with ShortPixel or Imagify</li>
              <li>Enable HTTP/2 or HTTP/3 on your server</li>
          </ul>
          </div>';
}
add_action('admin_notices', 'hikmahnews_performance_notice');