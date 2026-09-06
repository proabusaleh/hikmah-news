<?php
/**
 * Hikmah News Theme - Main Functions
 * @package HikmahNews
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// 1. THEME CONSTANTS
// ============================================
define('HIKMAHNEWS_VERSION', '3.0.0');
define('HIKMAHNEWS_DIR', dirname(__FILE__));
define('HIKMAHNEWS_URI', get_stylesheet_directory_uri());

// ============================================
// 2. THEME SETUP
// ============================================
function hikmahnews_theme_setup() {
    // Title tag support
    add_theme_support('title-tag');

    // Featured images
    add_theme_support('post-thumbnails');
    add_image_size('hikmahnews-hero', 1200, 675, true);
    add_image_size('hikmahnews-grid', 600, 400, true);
    add_image_size('hikmahnews-list', 350, 230, true);
    add_image_size('hikmahnews-thumb', 150, 100, true);

    // HTML5 support
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script'
    ]);

    // Custom logo
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Menus
    register_nav_menus([
        'primary'   => __('Primary Menu', 'hikmahnews'),
        'top-bar'   => __('Top Bar Menu', 'hikmahnews'),
        'footer'    => __('Footer Menu', 'hikmahnews'),
    ]);
}
add_action('after_setup_theme', 'hikmahnews_theme_setup');

// ============================================
// 3. ENQUEUE STYLES & SCRIPTS
// ============================================
function hikmahnews_enqueue_assets() {
    // Google Fonts — from theme options
    $heading_font = hikmahnews_option('typography', 'heading_font', 'Merriweather');
    $body_font    = hikmahnews_option('typography', 'body_font', 'Inter');

    $font_family_str = '';
    if ($heading_font === $body_font) {
        $font_family_str = $heading_font . ':wght@400;500;600;700;800;900';
    } else {
        $font_family_str = $body_font . ':wght@400;500;600;700;800;900&family=' .
                           $heading_font . ':wght@400;700;900';
    }

    wp_enqueue_style(
        'hikmahnews-fonts',
        'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font_family_str) .
            '&display=swap',
        [],
        null
    );

    // Main CSS
    wp_enqueue_style(
        'hikmahnews-main',
        HIKMAHNEWS_URI . '/assets/css/main.css',
        ['hikmahnews-fonts'],
        HIKMAHNEWS_VERSION
    );

    // Dark Mode CSS
    wp_enqueue_style(
        'hikmahnews-dark',
        HIKMAHNEWS_URI . '/assets/css/dark.css',
        ['hikmahnews-main'],
        HIKMAHNEWS_VERSION
    );

    // Responsive CSS
    wp_enqueue_style(
        'hikmahnews-responsive',
        HIKMAHNEWS_URI . '/assets/css/responsive.css',
        ['hikmahnews-main'],
        HIKMAHNEWS_VERSION
    );

    // Main JS
    wp_enqueue_script(
        'hikmahnews-main',
        HIKMAHNEWS_URI . '/assets/js/main.js',
        [],
        HIKMAHNEWS_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('hikmahnews-main', 'hikmahnews_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('hikmahnews_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'hikmahnews_enqueue_assets');

// Include helper functions
require_once HIKMAHNEWS_DIR . '/inc/helpers.php';
// Include theme options (Customizer panel)
require_once HIKMAHNEWS_DIR . '/inc/theme-options.php';

// ============================================
// FOUNDATION (Phases 1-5)
// ============================================

// ============================================
// NEWS FEATURES (Phase 6)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/breaking-news.php';
require_once HIKMAHNEWS_DIR . '/inc/featured-news.php';
require_once HIKMAHNEWS_DIR . '/inc/post-views.php';
require_once HIKMAHNEWS_DIR . '/inc/reading-bookmark.php';
require_once HIKMAHNEWS_DIR . '/inc/live-search.php';
require_once HIKMAHNEWS_DIR . '/inc/ajax-load-more.php';
require_once HIKMAHNEWS_DIR . '/inc/trending-algorithm.php';
require_once HIKMAHNEWS_DIR . '/inc/push-notifications.php';
require_once HIKMAHNEWS_DIR . '/inc/schema-markup.php';
require_once HIKMAHNEWS_DIR . '/inc/category-tabs-widget.php';

// ============================================
// CATEGORY SYSTEM (Phase 7)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/category-setup.php';
require_once HIKMAHNEWS_DIR . '/inc/category-meta-admin.php';
require_once HIKMAHNEWS_DIR . '/inc/mega-menu.php';
require_once HIKMAHNEWS_DIR . '/inc/homepage-builder.php';

// ============================================
// ADVERTISEMENT SYSTEM (Phase 8)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/ad-manager.php';
require_once HIKMAHNEWS_DIR . '/inc/ad-placements.php';

// ============================================
// SEO SYSTEM (Phase 9)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/seo-meta.php';
require_once HIKMAHNEWS_DIR . '/inc/seo-performance.php';
require_once HIKMAHNEWS_DIR . '/inc/seo-news.php';

// ============================================
// ADVANCED PERFORMANCE (Phase 10)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/perf-core.php';
require_once HIKMAHNEWS_DIR . '/inc/perf-advanced.php';

// ============================================
// FULL THEME OPTIONS (Phase 11)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/theme-options-panel.php';
require_once HIKMAHNEWS_DIR . '/inc/theme-options-tabs-1.php';
require_once HIKMAHNEWS_DIR . '/inc/theme-options-tabs-2.php';
require_once HIKMAHNEWS_DIR . '/inc/theme-options-updates.php';
require_once HIKMAHNEWS_DIR . '/inc/theme-updater.php';
require_once HIKMAHNEWS_DIR . '/inc/admin-enhancements.php';

// ============================================
// 4. WIDGET AREAS
// ============================================
function hikmahnews_widgets_init() {
    register_sidebar([
        'name'          => __('Main Sidebar', 'hikmahnews'),
        'id'            => 'sidebar-main',
        'description'   => __('Add widgets to the main sidebar.', 'hikmahnews'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="sidebar-widget__title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Ad', 'hikmahnews'),
        'id'            => 'header-ad',
        'description'   => __('Ad space in the header.', 'hikmahnews'),
        'before_widget' => '<div class="header-ad-widget">',
        'after_widget'  => '</div>',
    ]);
}
add_action('widgets_init', 'hikmahnews_widgets_init');

// ============================================
// GUTENBERG BLOCKS (Phase 12)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/gutenberg/blocks-init.php';
require_once HIKMAHNEWS_DIR . '/inc/gutenberg/block-renders.php';
require_once HIKMAHNEWS_DIR . '/inc/gutenberg/block-patterns.php';

// ============================================
// SECURITY (Phase 13)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/security.php';

// ============================================
// ADVANCED FEATURES (Phase 14)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/cookie-consent.php';
require_once HIKMAHNEWS_DIR . '/inc/accessibility.php';
require_once HIKMAHNEWS_DIR . '/inc/font-size-toggle.php';
require_once HIKMAHNEWS_DIR . '/inc/pwa.php';
require_once HIKMAHNEWS_DIR . '/inc/paywall.php';
require_once HIKMAHNEWS_DIR . '/inc/live-blog.php';
require_once HIKMAHNEWS_DIR . '/inc/widgets-extra.php';
require_once HIKMAHNEWS_DIR . '/inc/engagement.php';
require_once HIKMAHNEWS_DIR . '/inc/advanced-features.php';
require_once HIKMAHNEWS_DIR . '/inc/community.php';
require_once HIKMAHNEWS_DIR . '/inc/integrations.php';
require_once HIKMAHNEWS_DIR . '/inc/premium.php';

// ============================================
// ANALYTICS DASHBOARD (Phase 15)
// ============================================
require_once HIKMAHNEWS_DIR . '/inc/analytics-dashboard.php';

// ============================================
// FEATURE ASSETS (Print + Component CSS)
// ============================================
function hikmahnews_enqueue_feature_assets() {
    // Feature component styles (paywall, live blog, TOC, polls, etc.)
    wp_enqueue_style(
        'hikmahnews-features',
        HIKMAHNEWS_URI . '/assets/css/features.css',
        ['hikmahnews-main'],
        HIKMAHNEWS_VERSION
    );

    // Print stylesheet (only emitted for print media)
    wp_enqueue_style(
        'hikmahnews-print',
        HIKMAHNEWS_URI . '/assets/css/print.css',
        [],
        HIKMAHNEWS_VERSION,
        'print'
    );
}
add_action('wp_enqueue_scripts', 'hikmahnews_enqueue_feature_assets');

// ============================================
// TRANSLATION (Phase 13)
// ============================================
function hikmahnews_load_textdomain() {
    load_theme_textdomain('hikmahnews', HIKMAHNEWS_DIR . '/languages');
}
add_action('after_setup_theme', 'hikmahnews_load_textdomain');

// ============================================
// MODERN TEMPLATE SUPPORT
// ============================================
function hikmahnews_enqueue_modern_assets() {
    $style = hikmahnews_option('general', 'design_style', 'modern');

    if ($style === 'modern') {
        wp_enqueue_style(
            'hikmahnews-modern',
            HIKMAHNEWS_URI . '/assets/css/modern.css',
            ['hikmahnews-main'],
            HIKMAHNEWS_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'hikmahnews_enqueue_modern_assets');

// Auto-select modern template for front page
function hikmahnews_modern_front_page($template) {
    if (is_front_page() && hikmahnews_option('general', 'design_style', 'modern') === 'modern') {
        $modern = locate_template('front-page-modern.php');
        if ($modern) return $modern;
    }
    return $template;
}
add_filter('template_include', 'hikmahnews_modern_front_page');

// Auto-select modern single template
function hikmahnews_modern_single($template) {
    if (is_single() && hikmahnews_option('general', 'design_style', 'modern') === 'modern') {
        $modern = locate_template('single-modern.php');
        if ($modern) return $modern;
    }
    return $template;
}
add_filter('template_include', 'hikmahnews_modern_single');

// Disable classic progress bar (single-modern.php has its own)
function hikmahnews_modern_disable_classic_progress() {
    if (hikmahnews_option('general', 'design_style', 'modern') === 'modern') {
        remove_action('wp_body_open', 'hikmahnews_reading_progress_bar', 5);
        remove_action('wp_enqueue_scripts', 'hikmahnews_reading_progress_script');
    }
}
add_action('init', 'hikmahnews_modern_disable_classic_progress');