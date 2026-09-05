<?php
/**
 * WP News Theme - Main Functions
 * @package WPNews
 * @version 1.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// 1. THEME CONSTANTS
// ============================================
define('WPNEWS_VERSION', '1.0.0');
define('WPNEWS_DIR', get_template_directory());
define('WPNEWS_URI', get_template_directory_uri());

// ============================================
// 2. THEME SETUP
// ============================================
function wpnews_theme_setup() {
    // Title tag support
    add_theme_support('title-tag');

    // Featured images
    add_theme_support('post-thumbnails');
    add_image_size('wpnews-hero', 1200, 675, true);
    add_image_size('wpnews-grid', 600, 400, true);
    add_image_size('wpnews-list', 350, 230, true);
    add_image_size('wpnews-thumb', 150, 100, true);

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
        'primary'   => __('Primary Menu', 'wpnews'),
        'top-bar'   => __('Top Bar Menu', 'wpnews'),
        'footer'    => __('Footer Menu', 'wpnews'),
    ]);
}
add_action('after_setup_theme', 'wpnews_theme_setup');

// ============================================
// 3. ENQUEUE STYLES & SCRIPTS
// ============================================
function wpnews_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'wpnews-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Merriweather:wght@400;700;900&display=swap',
        [],
        null
    );

    // Main CSS
    wp_enqueue_style(
        'wpnews-main',
        WPNEWS_URI . '/assets/css/main.css',
        ['wpnews-fonts'],
        WPNEWS_VERSION
    );

    // Dark Mode CSS
    wp_enqueue_style(
        'wpnews-dark',
        WPNEWS_URI . '/assets/css/dark.css',
        ['wpnews-main'],
        WPNEWS_VERSION
    );

    // Responsive CSS
    wp_enqueue_style(
        'wpnews-responsive',
        WPNEWS_URI . '/assets/css/responsive.css',
        ['wpnews-main'],
        WPNEWS_VERSION
    );

    // Main JS
    wp_enqueue_script(
        'wpnews-main',
        WPNEWS_URI . '/assets/js/main.js',
        [],
        WPNEWS_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('wpnews-main', 'wpnews_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wpnews_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'wpnews_enqueue_assets');

// Include helper functions
require_once WPNEWS_DIR . '/inc/helpers.php';
// Include theme options
require_once WPNEWS_DIR . '/inc/theme-options.php';

// Phase 6 — News Features
require_once WPNEWS_DIR . '/inc/breaking-news.php';
require_once WPNEWS_DIR . '/inc/featured-news.php';
require_once WPNEWS_DIR . '/inc/post-views.php';
require_once WPNEWS_DIR . '/inc/reading-bookmark.php';
require_once WPNEWS_DIR . '/inc/live-search.php';

// Phase 6 — Final Features
require_once WPNEWS_DIR . '/inc/ajax-load-more.php';
require_once WPNEWS_DIR . '/inc/trending-algorithm.php';
require_once WPNEWS_DIR . '/inc/push-notifications.php';
require_once WPNEWS_DIR . '/inc/schema-markup.php';
require_once WPNEWS_DIR . '/inc/category-tabs-widget.php';

// Phase 7 — Category System
require_once WPNEWS_DIR . '/inc/category-setup.php';
require_once WPNEWS_DIR . '/inc/category-meta-admin.php';
require_once WPNEWS_DIR . '/inc/mega-menu.php';
require_once WPNEWS_DIR . '/inc/homepage-builder.php';
// ============================================
// 4. WIDGET AREAS
// ============================================
function wpnews_widgets_init() {
    register_sidebar([
        'name'          => __('Main Sidebar', 'wpnews'),
        'id'            => 'sidebar-main',
        'description'   => __('Add widgets to the main sidebar.', 'wpnews'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="sidebar-widget__title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Ad', 'wpnews'),
        'id'            => 'header-ad',
        'description'   => __('Ad space in the header.', 'wpnews'),
        'before_widget' => '<div class="header-ad-widget">',
        'after_widget'  => '</div>',
    ]);

    register_sidebar([
        'name'          => __('Footer Widgets', 'wpnews'),
        'id'            => 'footer-widgets',
        'description'   => __('Footer widget area.', 'wpnews'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget__title">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'wpnews_widgets_init');