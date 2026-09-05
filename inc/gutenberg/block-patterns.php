<?php
/**
 * Gutenberg Block Patterns
 * Pre-built page layouts using Hikmah News blocks
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. REGISTER PATTERN CATEGORY
// ============================================
function hikmahnews_register_pattern_categories() {
    register_block_pattern_category('hikmahnews-patterns', [
        'label' => '📰 Hikmah News Patterns',
    ]);
}
add_action('init', 'hikmahnews_register_pattern_categories');

// ============================================
// 2. REGISTER PATTERNS
// ============================================
function hikmahnews_register_patterns() {

    // --- Homepage Full Layout ---
    register_block_pattern('hikmahnews/homepage-full', [
        'title'       => 'Full Homepage Layout',
        'description' => 'Complete news homepage with hero, latest, categories, and trending.',
        'categories'  => ['hikmahnews-patterns'],
        'content'     => '
            <!-- wp:hikmahnews/breaking-news /-->
            <!-- wp:hikmahnews/featured-news {"postCount":4,"title":"⭐ Featured Stories"} /-->
            <!-- wp:hikmahnews/latest-news {"postCount":6,"columns":3,"title":"🕒 Latest News"} /-->
            <!-- wp:hikmahnews/category-news {"category":"politics","postCount":5,"title":"🏛️ Politics"} /-->
            <!-- wp:hikmahnews/category-news {"category":"business","postCount":5,"title":"💼 Business"} /-->
            <!-- wp:hikmahnews/trending-news {"postCount":6,"title":"🔥 Trending Now"} /-->
            <!-- wp:hikmahnews/category-news {"category":"sports","postCount":5,"title":"⚽ Sports"} /-->
            <!-- wp:hikmahnews/category-news {"category":"technology","postCount":5,"title":"💻 Technology"} /-->
            <!-- wp:hikmahnews/video-news {"postCount":4,"title":"🎬 Video News"} /-->
            <!-- wp:hikmahnews/popular-news {"postCount":5,"title":"🏆 Most Popular"} /-->
        ',
    ]);

    // --- News Grid Section ---
    register_block_pattern('hikmahnews/news-grid-section', [
        'title'       => 'News Grid Section',
        'description' => 'Simple 3-column news grid with title.',
        'categories'  => ['hikmahnews-patterns'],
        'content'     => '
            <!-- wp:hikmahnews/news-grid {"postCount":6,"columns":3,"title":"Top Stories","showExcerpt":true} /-->
        ',
    ]);

    // --- Two Column: Grid + List ---
    register_block_pattern('hikmahnews/grid-and-list', [
        'title'       => 'Two Column: Grid + List',
        'description' => 'News grid on left, list on right.',
        'categories'  => ['hikmahnews-patterns'],
        'content'     => '
            <!-- wp:columns -->
            <div class="wp-block-columns">
                <!-- wp:column {"width":"66%"} -->
                <div class="wp-block-column" style="flex-basis:66%">
                    <!-- wp:hikmahnews/news-grid {"postCount":4,"columns":2,"title":"Latest"} /-->
                </div>
                <!-- /wp:column -->
                <!-- wp:column {"width":"33%"} -->
                <div class="wp-block-column" style="flex-basis:33%">
                    <!-- wp:hikmahnews/popular-news {"postCount":5,"title":"Popular"} /-->
                </div>
                <!-- /wp:column -->
            </div>
            <!-- /wp:columns -->
        ',
    ]);

    // --- Category Page Layout ---
    register_block_pattern('hikmahnews/category-page', [
        'title'       => 'Category Page Layout',
        'description' => 'Layout for a category landing page.',
        'categories'  => ['hikmahnews-patterns'],
        'content'     => '
            <!-- wp:hikmahnews/breaking-news /-->
            <!-- wp:hikmahnews/featured-news {"postCount":3,"title":"Featured"} /-->
            <!-- wp:hikmahnews/category-news {"category":"","postCount":9,"columns":3,"title":"All Stories"} /-->
            <!-- wp:hikmahnews/advertisement {"adPosition":"homepage_mid"} /-->
            <!-- wp:hikmahnews/trending-news {"postCount":4,"title":"Trending"} /-->
        ',
    ]);

    // --- Sidebar Layout ---
    register_block_pattern('hikmahnews/sidebar-layout', [
        'title'       => 'Content + Sidebar',
        'description' => 'Main content with sidebar containing popular and ad.',
        'categories'  => ['hikmahnews-patterns'],
        'content'     => '
            <!-- wp:columns -->
            <div class="wp-block-columns">
                <!-- wp:column {"width":"70%"} -->
                <div class="wp-block-column" style="flex-basis:70%">
                    <!-- wp:hikmahnews/latest-news {"postCount":6,"columns":2,"title":"Latest News"} /-->
                </div>
                <!-- /wp:column -->
                <!-- wp:column {"width":"30%"} -->
                <div class="wp-block-column" style="flex-basis:30%">
                    <!-- wp:hikmahnews/advertisement {"adPosition":"sidebar_top"} /-->
                    <!-- wp:hikmahnews/popular-news {"postCount":5,"title":"Most Read"} /-->
                    <!-- wp:hikmahnews/advertisement {"adPosition":"sidebar_mid"} /-->
                </div>
                <!-- /wp:column -->
            </div>
            <!-- /wp:columns -->
        ',
    ]);
}
add_action('init', 'hikmahnews_register_patterns');

// ============================================
// 3. BLOCK STYLES
// ============================================
function hikmahnews_register_block_styles() {
    // Card styles
    register_block_style('hikmahnews/news-grid', [
        'name'  => 'flat-cards',
        'label' => 'Flat Cards',
    ]);

    register_block_style('hikmahnews/news-grid', [
        'name'  => 'bordered-cards',
        'label' => 'Bordered Cards',
    ]);

    // List styles
    register_block_style('hikmahnews/news-list', [
        'name'  => 'numbered',
        'label' => 'Numbered List',
    ]);

    register_block_style('hikmahnews/news-list', [
        'name'  => 'compact',
        'label' => 'Compact',
    ]);
}
add_action('init', 'hikmahnews_register_block_styles');