<?php
/**
 * Premium Features: WooCommerce Subscriptions, AI Suggestions
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. WOOCOMMERCE SUBSCRIPTION CHECK + GATE
// ============================================
function hikmahnews_is_subscriber($user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return false;
    if (!class_exists('WC_Subscriptions')) return false;
    if (!function_exists('wcs_user_has_subscription')) return false;
    return wcs_user_has_subscription($user_id, '', 'active');
}

function hikmahnews_subscription_gate($content) {
    if (!is_single() || is_admin()) return $content;
    // Only when WooCommerce Subscriptions is actually installed
    if (!class_exists('WC_Subscriptions')) return $content;

    $is_premium = get_post_meta(get_the_ID(), '_hikmahnews_premium', true);
    if (!$is_premium) return $content;
    if (hikmahnews_is_subscriber()) return $content;

    $paras = explode('</p>', $content);
    $teaser = implode('</p>', array_slice($paras, 0, 2)) . '</p>';
    $teaser .= '<div class="paywall-overlay"><div class="paywall-overlay__inner"><div class="paywall-overlay__content">
        <span style="font-size:40px;">🔒</span><h3>Subscriber Only</h3>
        <p>This article is available to subscribers only.</p>
        <a href="' . esc_url(home_url('/product/premium-subscription')) . '" class="modern-btn modern-btn--primary">Subscribe Now</a>
    </div></div></div>';
    return $teaser;
}
add_filter('the_content', 'hikmahnews_subscription_gate', 40);

// ============================================
// 2. AI CONTENT SUGGESTIONS ("You might also like")
// ============================================
function hikmahnews_ai_suggestions($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $cats = get_the_category($post_id);
    $tags = get_the_tags($post_id);

    $args = [
        'post__not_in'   => [$post_id],
        'posts_per_page' => 4,
        'orderby'        => 'rand',
        'no_found_rows'  => true,
    ];

    if ($tags) {
        $args['tag__in'] = wp_list_pluck($tags, 'term_id');
    } elseif ($cats) {
        $args['category__in'] = wp_list_pluck($cats, 'term_id');
    }

    $suggestions = get_posts($args);
    if (empty($suggestions)) return '';

    $html = '<div class="ai-suggestions"><h3 class="ai-suggestions__title">🤖 You Might Also Like</h3><div class="modern-grid" style="grid-template-columns:repeat(4,1fr);">';
    foreach ($suggestions as $s) {
        $html .= hikmahnews_block_render_card($s, 'hikmahnews-grid', false);
    }
    $html .= '</div></div>';
    return $html;
}

// Append suggestions to modern single posts
function hikmahnews_ai_suggestions_append($content) {
    if (!is_single() || is_admin()) return $content;
    if (hikmahnews_option('general', 'design_style', 'modern') !== 'modern') return $content;
    $content .= '<div class="ai-suggestions-wrap">' . hikmahnews_ai_suggestions() . '</div>';
    return $content;
}
add_filter('the_content', 'hikmahnews_ai_suggestions_append', 30);