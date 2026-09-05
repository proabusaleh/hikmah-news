<?php
/**
 * Post Views Counter
 * - AJAX-based (no caching issues)
 * - Cookie-based duplicate prevention
 * - Bot detection
 * - Admin dashboard column + sortable
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. AJAX VIEW COUNTER
// ============================================
function hikmahnews_track_post_view() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id) wp_send_json_error('Invalid post');

    // Bot detection
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $bots = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'facebook', 'twitter', 'whatsapp'];
    foreach ($bots as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            wp_send_json_success('Bot ignored');
        }
    }

    // Cookie-based duplicate check (24hr window)
    $cookie_name = 'hikmahnews_viewed_' . $post_id;
    if (isset($_COOKIE[$cookie_name])) {
        wp_send_json_success('Already counted');
    }

    // Increment
    $current = (int) get_post_meta($post_id, '_hikmahnews_views', true);
    update_post_meta($post_id, '_hikmahnews_views', $current + 1);

    // Set cookie (24 hours)
    setcookie($cookie_name, '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);

    // Also update today's count for trending
    $today = date('Y-m-d');
    $daily_key = '_hikmahnews_views_' . $today;
    $daily_count = (int) get_post_meta($post_id, $daily_key, true);
    update_post_meta($post_id, $daily_key, $daily_count + 1);

    wp_send_json_success([
        'views' => $current + 1,
    ]);
}
add_action('wp_ajax_hikmahnews_track_view', 'hikmahnews_track_post_view');
add_action('wp_ajax_nopriv_hikmahnews_track_view', 'hikmahnews_track_post_view');

// ============================================
// 2. ENQUEUE VIEW TRACKER ON SINGLE POSTS
// ============================================
function hikmahnews_enqueue_view_tracker() {
    if (is_single()) {
        wp_add_inline_script('hikmahnews-main', '
            document.addEventListener("DOMContentLoaded", function() {
                var postId = ' . get_the_ID() . ';
                if (typeof hikmahnews_ajax !== "undefined") {
                    fetch(hikmahnews_ajax.ajax_url, {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: "action=hikmahnews_track_view&nonce=" + hikmahnews_ajax.nonce + "&post_id=" + postId
                    });
                }
            });
        ');
    }
}
add_action('wp_enqueue_scripts', 'hikmahnews_enqueue_view_tracker');

// ============================================
// 3. HELPER: Get Formatted Views
// ============================================
function hikmahnews_get_formatted_views($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $views = (int) get_post_meta($post_id, '_hikmahnews_views', true);

    if ($views >= 1000000) return round($views / 1000000, 1) . 'M';
    if ($views >= 1000) return round($views / 1000, 1) . 'K';
    return (string) $views;
}

// ============================================
// 4. ADMIN COLUMN (Sortable)
// ============================================
function hikmahnews_views_admin_columns($columns) {
    $new = [];
    foreach ($columns as $key => $val) {
        $new[$key] = $val;
        if ($key === 'featured') {
            $new['views'] = '👁 Views';
        }
    }
    return $new;
}
add_filter('manage_posts_columns', 'hikmahnews_views_admin_columns');

function hikmahnews_views_admin_column_data($column, $post_id) {
    if ($column === 'views') {
        $views = (int) get_post_meta($post_id, '_hikmahnews_views', true);
        echo '<strong>' . number_format($views) . '</strong>';
    }
}
add_action('manage_posts_custom_column', 'hikmahnews_views_admin_column_data', 10, 2);

function hikmahnews_views_sortable_columns($columns) {
    $columns['views'] = 'views';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'hikmahnews_views_sortable_columns');

function hikmahnews_views_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('orderby') === 'views') {
        $query->set('meta_key', '_hikmahnews_views');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'hikmahnews_views_orderby');

// ============================================
// 5. CLEANUP: Remove old daily view meta (cron)
// ============================================
function hikmahnews_cleanup_daily_views() {
    global $wpdb;
    $old_date = date('Y-m-d', strtotime('-30 days'));
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_key < %s",
        '_hikmahnews_views_%',
        '_hikmahnews_views_' . $old_date
    ));
}

if (!wp_next_scheduled('hikmahnews_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'hikmahnews_daily_cleanup');
}
add_action('hikmahnews_daily_cleanup', 'hikmahnews_cleanup_daily_views');