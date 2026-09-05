<?php
if (!defined('ABSPATH')) exit;
function hikmah_news_setup() {
 load_theme_textdomain('hikmah-news', get_template_directory().'/languages');
 add_theme_support('title-tag'); add_theme_support('post-thumbnails'); add_theme_support('automatic-feed-links');
 add_theme_support('custom-logo'); add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
 add_theme_support('editor-styles'); add_theme_support('responsive-embeds');
 register_nav_menus(['primary'=>__('Primary Menu','hikmah-news'),'footer'=>__('Footer Menu','hikmah-news')]);
}
add_action('after_setup_theme','hikmah_news_setup');
