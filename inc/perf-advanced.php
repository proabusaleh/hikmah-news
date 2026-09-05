<?php
/**
 * Performance Advanced
 * - Critical CSS inline
 * - Cache compatibility headers
 * - CDN URL rewriting
 * - Core Web Vitals optimization
 * - Minification pipeline
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. CRITICAL CSS (Inline above-fold styles)
// ============================================
function hikmahnews_critical_css() {
    $critical = get_option('hikmahnews_critical_css', '');

    if ($critical) {
        echo '<style id="hikmahnews-critical">' . $critical . '</style>' . "\n";
    } else {
        // Default critical CSS (minimal above-fold)
        ?>
        <style id="hikmahnews-critical">
            /* Critical: Reset + Typography + Header + Hero */
            *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
            html{font-size:16px;-webkit-text-size-adjust:100%}
            body{font-family:'Inter',-apple-system,sans-serif;color:#111827;background:#f3f4f6;
                 -webkit-font-smoothing:antialiased}
            h1,h2,h3,h4{font-family:'Merriweather',Georgia,serif;font-weight:700;line-height:1.2}
            a{color:#dc2626;text-decoration:none}
            img{max-width:100%;height:auto;display:block}
            .container{max-width:1280px;margin:0 auto;padding:0 1rem}
            .topbar{background:#1e3a5f;color:#fff;font-size:.75rem;height:40px}
            .site-header{background:#fff;border-bottom:1px solid #e5e7eb}
            .site-header__inner{display:flex;align-items:center;height:70px}
            .main-nav{background:#dc2626;position:sticky;top:0;z-index:999}
            .hero-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:1.5rem;padding:2rem 0}
            .hero-main{border-radius:12px;overflow:hidden;position:relative;min-height:500px}
            .badge{display:inline-flex;padding:4px 10px;font-size:11px;font-weight:700;
                   border-radius:4px;text-transform:uppercase}
            .badge--primary{background:#dc2626;color:#fff}
            .reading-progress{position:fixed;top:0;left:0;right:0;height:3px;z-index:10000}
            .reading-progress__bar{height:100%;background:linear-gradient(90deg,#dc2626,#f59e0b);width:0}
        </style>
        <?php
    }

    // Defer non-critical CSS
    add_filter('style_loader_tag', 'hikmahnews_defer_css', 10, 4);
}
add_action('wp_head', 'hikmahnews_critical_css', 2);

function hikmahnews_defer_css($html, $handle, $href, $media) {
    // Don't defer critical styles
    $no_defer = ['hikmahnews-main']; // Main CSS loads normally
    if (in_array($handle, $no_defer)) return $html;

    // Defer dark and responsive CSS
    $defer_handles = ['hikmahnews-dark', 'hikmahnews-responsive'];
    if (in_array($handle, $defer_handles)) {
        $html = '<link rel="preload" href="' . esc_url($href) . '" as="style"
                       onload="this.onload=null;this.rel=\'stylesheet\'">';
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url($href) . '"></noscript>';
    }

    return $html;
}

// ============================================
// 2. CACHE COMPATIBILITY
// ============================================
class HikmahNews_Cache_Compat {

    public function __construct() {
        add_action('send_headers', [$this, 'cache_headers']);
        add_action('save_post', [$this, 'purge_cache'], 100);
        add_action('wp_update_nav_menu', [$this, 'purge_cache']);
        add_action('customize_save_after', [$this, 'purge_cache']);
        add_filter('wp_cache_set_last_changed', [$this, 'bump_last_changed']);
    }

    /**
     * Set proper cache headers
     */
    public function cache_headers() {
        if (is_admin() || is_user_logged_in()) return;

        $options = get_option('hikmahnews_theme_options', []);
        $cache_ttl = $options['performance']['cache_ttl'] ?? 3600;

        if (!headers_sent()) {
            header('Cache-Control: public, max-age=' . intval($cache_ttl));
            header('X-HikmahNews-Cache: theme-v' . HIKMAHNEWS_VERSION);
        }
    }

    /**
     * Purge cache on content change
     */
    public function purge_cache() {
        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        // LiteSpeed
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }

        // WP Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        // SiteGround Optimizer
        if (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        }

        // Cloudflare (via API)
        $cf_email = get_option('hikmahnews_cf_email');
        $cf_key = get_option('hikmahnews_cf_api_key');
        $cf_zone = get_option('hikmahnews_cf_zone_id');

        if ($cf_email && $cf_key && $cf_zone) {
            wp_remote_post("https://api.cloudflare.com/client/v4/zones/{$cf_zone}/purge_cache", [
                'headers' => [
                    'X-Auth-Email' => $cf_email,
                    'X-Auth-Key'   => $cf_key,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(['purge_everything' => true]),
                'timeout' => 5,
            ]);
        }

        // Clear our transients
        HikmahNews_Cache::clear_on_save(0);
    }

    public function bump_last_changed($last_changed) {
        return microtime();
    }
}
new HikmahNews_Cache_Compat();

// ============================================
// 3. CDN URL REWRITING
// ============================================
class HikmahNews_CDN {

    public function __construct() {
        $options = get_option('hikmahnews_theme_options', []);
        $cdn_url = $options['performance']['cdn_url'] ?? '';

        if ($cdn_url) {
            add_filter('wp_get_attachment_url', [$this, 'rewrite_url']);
            add_filter('the_content', [$this, 'rewrite_content'], 200);
            add_filter('style_loader_src', [$this, 'rewrite_url']);
            add_filter('script_loader_src', [$this, 'rewrite_url']);
            add_filter('wp_calculate_image_srcset', [$this, 'rewrite_srcset']);
        }
    }

    public function rewrite_url($url) {
        $cdn_url = $this->get_cdn_url();
        if (!$cdn_url) return $url;

        $site_url = site_url();
        return str_replace($site_url . '/wp-content', $cdn_url . '/wp-content', $url);
    }

    public function rewrite_content($content) {
        $cdn_url = $this->get_cdn_url();
        if (!$cdn_url) return $content;

        $site_url = site_url();
        return str_replace(
            $site_url . '/wp-content/uploads',
            $cdn_url . '/wp-content/uploads',
            $content
        );
    }

    public function rewrite_srcset($sources) {
        foreach ($sources as &$source) {
            $source['url'] = $this->rewrite_url($source['url']);
        }
        return $sources;
    }

    private function get_cdn_url() {
        $options = get_option('hikmahnews_theme_options', []);
        return rtrim($options['performance']['cdn_url'] ?? '', '/');
    }
}
new HikmahNews_CDN();

// ============================================
// 4. CORE WEB VITALS OPTIMIZATION
// ============================================
class HikmahNews_WebVitals {

    public function __construct() {
        add_filter('wp_get_attachment_image_attributes', [$this, 'lcp_attributes'], 20, 3);
        add_action('wp_head', [$this, 'font_display_swap'], 0);
        add_filter('the_content', [$this, 'cls_prevention'], 50);
        add_action('wp_footer', [$this, 'web_vitals_report']);
    }

    /**
     * LCP Image Optimization
     */
    public function lcp_attributes($attr, $attachment, $size) {
        if (is_single() && $attachment->ID === get_post_thumbnail_id()) {
            $attr['fetchpriority'] = 'high';
            $attr['loading'] = 'eager';
            $attr['decoding'] = 'sync';
        }
        return $attr;
    }

    /**
     * Font Display Swap (prevent FOUT/FOIT)
     */
    public function font_display_swap() {
        ?>
        <style>
            @font-face {
                font-family: 'Inter';
                font-display: swap;
            }
            @font-face {
                font-family: 'Merriweather';
                font-display: swap;
            }
        </style>
        <?php
    }

    /**
     * CLS Prevention: Add aspect-ratio to images
     */
    public function cls_prevention($content) {
        return preg_replace_callback(
            '/<img([^>]*)width=["\'](\d+)["\']([^>]*)height=["\'](\d+)["\']([^>]*)>/i',
            function ($matches) {
                $w = $matches[2];
                $h = $matches[4];
                $before = $matches[1] . $matches[3] . $matches[5];

                // Add aspect-ratio style if not present
                if (strpos($before, 'aspect-ratio') === false) {
                    $ratio = $w . '/' . $h;
                    if (strpos($before, 'style=') !== false) {
                        $before = preg_replace(
                            '/style=["\']([^"\']*)["\']/',
                            'style="$1;aspect-ratio:' . $ratio . ';"',
                            $before
                        );
                    } else {
                        $before .= ' style="aspect-ratio:' . $ratio . ';"';
                    }
                }

                return '<img' . $before . ' width="' . $w . '" height="' . $h . '">';
            },
            $content
        );
    }

    /**
     * Report Web Vitals to console (dev mode)
     */
    public function web_vitals_report() {
        $options = get_option('hikmahnews_theme_options', []);
        if (empty($options['performance']['debug_vitals'])) return;
        ?>
        <script type="module">
            import {onCLS, onFID, onLCP, onFCP, onTTFB} from
                'https://unpkg.com/web-vitals@3/dist/web-vitals.attribution.js?module';

            function sendToConsole(metric) {
                console.log(`[HikmahNews Vitals] ${metric.name}: ${metric.value.toFixed(2)}`, metric);
            }

            onCLS(sendToConsole);
            onFID(sendToConsole);
            onLCP(sendToConsole);
            onFCP(sendToConsole);
            onTTFB(sendToConsole);
        </script>
        <?php
    }
}
new HikmahNews_WebVitals();

// ============================================
// 5. CSS/JS MINIFICATION HELPER
// ============================================
function hikmahnews_minify_css($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = str_replace([' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; '],
                        ['{', '{', '}', '}', ':', ':', ';', ';'], $css);
    return trim($css);
}

function hikmahnews_minify_js($js) {
    // Basic minification (production should use build tools)
    $js = preg_replace('/\/\/.*$/m', '', $js);
    $js = preg_replace('/\s+/', ' ', $js);
    return trim($js);
}