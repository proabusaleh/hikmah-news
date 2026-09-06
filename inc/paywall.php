<?php
/**
 * Content Paywall / Article Gating
 * - Free article limit per month
 * - Premium content flag (admin meta box)
 * - Teaser + CTA overlay
 * - Defers to WooCommerce Subscriptions (STEP 67 premium.php) when active
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

class HikmahNews_Paywall {
    private $free_limit = 3;

    public function __construct() {
        add_action('wp', [$this, 'check_paywall']);
        add_filter('the_content', [$this, 'gate_content'], 50);
        add_action('wp_ajax_hikmahnews_track_article', [$this, 'track_article']);
        add_action('wp_ajax_nopriv_hikmahnews_track_article', [$this, 'track_article']);
    }

    public function check_paywall() {
        if (!is_single() || is_user_logged_in()) return;
        // WooCommerce Subscriptions owns premium gating when installed
        if (class_exists('WC_Subscriptions')) return;

        $post_id = get_the_ID();
        $is_premium = get_post_meta($post_id, '_hikmahnews_premium', true);
        if (!$is_premium) return;

        $read = $this->get_read_count();
        if ($read >= $this->free_limit) {
            add_filter('hikmahnews_paywall_active', '__return_true');
        }
    }

    private function get_read_count() {
        $key = 'hikmahnews_read_' . gmdate('Y-m');
        return (int) ($_COOKIE[$key] ?? 0);
    }

    public function gate_content($content) {
        if (!is_single() || is_user_logged_in()) return $content;
        if (class_exists('WC_Subscriptions')) return $content;

        if (!apply_filters('hikmahnews_paywall_active', false)) {
            // Track free article
            $this->increment_read();
            return $content;
        }

        // Show teaser (first 2 paragraphs)
        $paras = explode('</p>', $content);
        $teaser = implode('</p>', array_slice($paras, 0, 2)) . '</p>';

        $teaser .= '
        <div class="paywall-overlay">
            <div class="paywall-overlay__inner">
                <div class="paywall-overlay__blur"></div>
                <div class="paywall-overlay__content">
                    <span style="font-size:40px;">🔒</span>
                    <h3>Premium Content</h3>
                    <p>You\'ve read your ' . $this->free_limit . ' free articles this month. Subscribe for unlimited access.</p>
                    <a href="' . home_url('/subscribe') . '" class="modern-btn modern-btn--primary" style="margin-top:12px;">
                        Subscribe Now — $4.99/mo
                    </a>
                    <a href="' . wp_login_url(get_permalink()) . '" style="display:block;margin-top:8px;font-size:13px;color:var(--modern-text-2, #525252);">
                        Already a subscriber? Log in
                    </a>
                </div>
            </div>
        </div>';

        return $teaser;
    }

    private function increment_read() {
        $key = 'hikmahnews_read_' . gmdate('Y-m');
        $count = $this->get_read_count() + 1;
        setcookie($key, $count, strtotime('+1 month'), COOKIEPATH, COOKIE_DOMAIN);
    }

    public function track_article() {
        check_ajax_referer('hikmahnews_nonce', 'nonce');
        if (is_user_logged_in()) wp_send_json_error('Logged in');
        $this->increment_read();
        wp_send_json_success(['count' => $this->get_read_count()]);
    }
}
new HikmahNews_Paywall();

// Admin Meta Box for Premium flag
function hikmahnews_paywall_meta_box() {
    add_meta_box('hikmahnews_paywall', '🔒 Premium Content', function($post) {
        wp_nonce_field('hikmahnews_paywall_nonce', 'hikmahnews_paywall_nonce_field');
        $premium = get_post_meta($post->ID, '_hikmahnews_premium', true);
        echo '<label><input type="checkbox" name="hikmahnews_is_premium" value="1" ' . checked($premium, '1', false) . '> Mark as Premium (paywalled)</label>';
    }, 'post', 'side');
}
add_action('add_meta_boxes', 'hikmahnews_paywall_meta_box');

function hikmahnews_save_paywall($post_id) {
    if (!isset($_POST['hikmahnews_paywall_nonce_field']) || !wp_verify_nonce($_POST['hikmahnews_paywall_nonce_field'], 'hikmahnews_paywall_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    update_post_meta($post_id, '_hikmahnews_premium', isset($_POST['hikmahnews_is_premium']) ? '1' : '0');
}
add_action('save_post', 'hikmahnews_save_paywall');