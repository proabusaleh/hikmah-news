<?php
function hikmah_news_assets() {
 wp_enqueue_style('hikmah-news-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
 wp_enqueue_style('hikmah-news-main', get_template_directory_uri().'/assets/css/main.css', ['hikmah-news-style'], '1.0.0');
 wp_enqueue_style('hikmah-news-responsive', get_template_directory_uri().'/assets/css/responsive.css', ['hikmah-news-main'], '1.0.0');
 wp_enqueue_script('hikmah-news-main', get_template_directory_uri().'/assets/js/main.js', [], '1.0.0', true);
 wp_enqueue_script('hikmah-news-navigation', get_template_directory_uri().'/assets/js/navigation.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts','hikmah_news_assets');
