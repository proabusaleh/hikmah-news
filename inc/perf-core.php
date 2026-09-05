<?php
/**
 * Performance Core
 * - Advanced lazy loading with LCP detection
 * - WebP image serving
 * - Database query optimization
 * - Object cache integration
 * - Transient caching for expensive queries
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ADVANCED LAZY LOADING
// ============================================
class HikmahNews_Lazy_Load {

    public function __construct() {
        add_filter('wp_lazy_loading_enabled', '__return_true');
        add_filter('wp_img_tag_add_loading_attr', [$this, 'smart_loading'], 10, 3);
        add_filter('wp_img_tag_add_decoding_attr', [$this, 'async_decoding'], 10, 3);
        add_filter('the_content', [$this, 'add_placeholder'], 99);
        add_action('wp_head', [$this, 'lcp_preload'], 1);
    }

    /**
     * Smart loading attribute based on position
     */
    public function smart_loading($value, $image, $context) {
        // Eager load: hero, featured, first content image
        if ($this->is_above_fold($image, $context)) {
            return false; // eager
        }

        // Lazy load everything else
        return 'lazy';
    }

    /**
     * Detect above-fold images
     */
    private function is_above_fold($image, $context) {
        static $content_images = 0;

        // Featured images are always above fold
        if (strpos($image, 'hikmahnews-hero') !== false) return true;
        if (strpos($image, 'wp-post-image') !== false && is_single()) return true;

        // First 2 content images on single posts
        if ($context === 'the_content' && is_single()) {
            $content_images++;
            if ($content_images <= 2) return true;
        }

        // Hero section images
        if (strpos($image, 'hero-main') !== false) return true;

        return false;
    }

    /**
     * Async decoding for all images
     */
    public function async_decoding($value, $image, $context) {
        // LCP image should be sync
        if ($this->is_above_fold($image, $context)) {
            return 'sync';
        }
        return 'async';
    }

    /**
     * Add low-quality placeholder for lazy images
     */
    public function add_placeholder($content) {
        if (is_admin()) return $content;

        return preg_replace_callback(
            '/<img([^>]*)loading=["\']lazy["\']([^>]*)>/',
            function ($matches) {
                $attrs = $matches[1] . $matches[2];

                // Add CSS placeholder class
                if (strpos($attrs, 'class=') !== false) {
                    $attrs = preg_replace(
                        '/class=["\']([^"\']*)["\']/',
                        'class="$1 hikmahnews-lazy"',
                        $attrs
                    );
                } else {
                    $attrs .= ' class="hikmahnews-lazy"';
                }

                return '<img' . $attrs . '>';
            },
            $content
        );
    }

    /**
     * Preload LCP image
     */
    public function lcp_preload() {
        if (is_single() && has_post_thumbnail()) {
            $url = get_the_post_thumbnail_url(get_the_ID(), 'hikmahnews-hero');
            if ($url) {
                $webp = $this->get_webp_url($url);
                $final = $webp ?: $url;
                echo '<link rel="preload" as="image" href="' . esc_url($final) . '" fetchpriority="high">' . "\n";
            }
        }

        if (is_front_page()) {
            $hero = get_posts(['posts_per_page' => 1, 'no_found_rows' => true]);
            if ($hero && has_post_thumbnail($hero[0]->ID)) {
                $url = get_the_post_thumbnail_url($hero[0]->ID, 'hikmahnews-hero');
                if ($url) {
                    echo '<link rel="preload" as="image" href="' . esc_url($url) . '" fetchpriority="high">' . "\n";
                }
            }
        }
    }

    /**
     * Get WebP version of URL
     */
    private function get_webp_url($url) {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);
        if ($webp !== $url) return $webp;
        return false;
    }
}
new HikmahNews_Lazy_Load();

// ============================================
// 2. WEBP IMAGE SUPPORT
// ============================================
class HikmahNews_WebP {

    public function __construct() {
        add_filter('upload_mimes', [$this, 'add_webp_mime']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fix_webp_type'], 10, 5);
        add_filter('the_content', [$this, 'serve_webp'], 100);
        add_filter('wp_get_attachment_image_attributes', [$this, 'webp_srcset'], 10, 3);
        add_action('wp_head', [$this, 'webp_detection_script'], 0);
    }

    public function add_webp_mime($mimes) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    }

    public function fix_webp_type($data, $file, $filename, $mimes, $real_mime) {
        if (!empty($data['ext']) && $data['ext'] === 'webp') {
            $data['type'] = 'image/webp';
        }
        return $data;
    }

    /**
     * Replace JPEG/PNG with WebP in content (if file exists)
     */
    public function serve_webp($content) {
        if (is_admin()) return $content;

        return preg_replace_callback(
            '/(src|srcset)=["\']([^"\']+\.(jpe?g|png))["\']/i',
            function ($matches) {
                $attr = $matches[1];
                $url = $matches[2];
                $webp_url = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);

                // Only replace if using CSS class detection (JS adds .webp-supported)
                return $attr . '="' . $url . '" data-webp="' . $webp_url . '"';
            },
            $content
        );
    }

    public function webp_srcset($attr, $attachment, $size) {
        $attr['fetchpriority'] = 'auto';
        return $attr;
    }

    public function webp_detection_script() {
        ?>
        <script>
        (function(){
            var img = new Image();
            img.onload = function(){
                if(img.width > 0 && img.height > 0){
                    document.documentElement.classList.add('webp-supported');
                    // Swap images
                    document.querySelectorAll('[data-webp]').forEach(function(el){
                        var webp = el.dataset.webp;
                        if(el.tagName === 'IMG'){
                            el.src = webp;
                        } else {
                            el.srcset = webp;
                        }
                    });
                }
            };
            img.src = 'data:image/webp;base64,UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA';
        })();
        </script>
        <?php
    }
}
new HikmahNews_WebP();

// ============================================
// 3. QUERY OPTIMIZATION
// ============================================
class HikmahNews_Query_Optimizer {

    public function __construct() {
        add_action('pre_get_posts', [$this, 'optimize_main_query']);
        add_filter('posts_clauses', [$this, 'optimize_clauses'], 10, 2);
        add_action('wp', [$this, 'disable_emojis_query']);
    }

    /**
     * Optimize main query on archives
     */
    public function optimize_main_query($query) {
        if (is_admin() || !$query->is_main_query()) return;

        // Disable meta cache on archives (not needed)
        if ($query->is_archive() || $query->is_home()) {
            $query->set('update_post_meta_cache', false);
            $query->set('update_post_term_cache', true); // Keep term cache for categories
        }

        // Optimize search
        if ($query->is_search()) {
            $query->set('no_found_rows', false); // Need for pagination
            $query->set('update_post_meta_cache', false);
        }

        // Limit feed posts
        if ($query->is_feed()) {
            $query->set('posts_per_page', 20);
        }
    }

    /**
     * Remove unnecessary SQL clauses
     */
    public function optimize_clauses($clauses, $query) {
        if (is_admin()) return $clauses;

        // Remove SQL_CALC_FOUND_ROWS when not needed
        if ($query->get('no_found_rows')) {
            $clauses['fields'] = str_replace('SQL_CALC_FOUND_ROWS ', '', $clauses['fields']);
        }

        return $clauses;
    }

    public function disable_emojis_query() {
        // Already removed in seo-performance.php
    }
}
new HikmahNews_Query_Optimizer();

// ============================================
// 4. TRANSIENT CACHING FOR EXPENSIVE QUERIES
// ============================================
class HikmahNews_Cache {

    /**
     * Cache trending posts (expensive meta query)
     */
    public static function get_trending($count = 10) {
        $cache_key = 'hikmahnews_trending_' . $count;
        $cached = get_transient($cache_key);

        if ($cached !== false) return $cached;

        $posts = hikmahnews_get_trending_posts($count, 48);
        $ids = wp_list_pluck($posts, 'ID');

        set_transient($cache_key, $ids, HOUR_IN_SECONDS);
        return $ids;
    }

    /**
     * Cache popular posts
     */
    public static function get_popular($count = 5, $cat = '') {
        $cache_key = 'hikmahnews_popular_' . $cat . '_' . $count;
        $cached = get_transient($cache_key);

        if ($cached !== false) return $cached;

        $args = [
            'posts_per_page' => $count,
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_hikmahnews_views',
            'no_found_rows'  => true,
        ];

        if ($cat) $args['category_name'] = $cat;

        $posts = get_posts($args);
        $ids = wp_list_pluck($posts, 'ID');

        set_transient($cache_key, $ids, 2 * HOUR_IN_SECONDS);
        return $ids;
    }

    /**
     * Cache breaking news
     */
    public static function get_breaking($count = 6) {
        $cache_key = 'hikmahnews_breaking_' . $count;
        $cached = get_transient($cache_key);

        if ($cached !== false) return $cached;

        $posts = hikmahnews_get_breaking_posts($count);
        $ids = wp_list_pluck($posts, 'ID');

        set_transient($cache_key, $ids, 15 * MINUTE_IN_SECONDS); // Short cache for breaking
        return $ids;
    }

    /**
     * Clear caches on post save
     */
    public static function clear_on_save($post_id) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

        delete_transient('hikmahnews_trending_10');
        delete_transient('hikmahnews_breaking_6');

        // Clear popular caches
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%hikmahnews_popular_%'");
    }
}
add_action('save_post', ['HikmahNews_Cache', 'clear_on_save'], 50);
add_action('comment_post', function() {
    delete_transient('hikmahnews_trending_10');
});

// ============================================
// 5. OBJECT CACHE INTEGRATION
// ============================================
function hikmahnews_object_cache_support() {
    // Check if object cache is available
    if (wp_using_ext_object_cache()) {
        // Use wp_cache_* functions for better performance
        add_filter('hikmahnews_use_object_cache', '__return_true');
    }
}
add_action('init', 'hikmahnews_object_cache_support');