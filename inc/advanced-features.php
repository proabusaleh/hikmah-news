<?php
/**
 * Multi-Author, Reading History, Dark Mode Schedule
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. MULTI-AUTHOR / CO-AUTHORS
// ============================================
function hikmahnews_coauthors_meta_box() {
    add_meta_box('hikmahnews_coauthors', '👥 Co-Authors', function($post) {
        wp_nonce_field('hikmahnews_coauthors_nonce', 'hikmahnews_coauthors_nonce_field');
        $coauthors = get_post_meta($post->ID, '_hikmahnews_coauthors', true);
        $coauthors = is_array($coauthors) ? $coauthors : [];
        $users = get_users(['role__in' => ['author', 'editor', 'administrator']]);
        echo '<select name="hikmahnews_coauthors[]" multiple style="width:100%;height:100px;">';
        foreach ($users as $u) {
            if ((int) $u->ID === (int) $post->post_author) continue;
            $sel = in_array($u->ID, $coauthors) ? ' selected' : '';
            echo '<option value="' . (int) $u->ID . '"' . $sel . '>' . esc_html($u->display_name) . '</option>';
        }
        echo '</select><p class="description">Hold Ctrl/Cmd to select multiple.</p>';
    }, 'post', 'side');
}
add_action('add_meta_boxes', 'hikmahnews_coauthors_meta_box');

function hikmahnews_save_coauthors($post_id) {
    if (!isset($_POST['hikmahnews_coauthors_nonce_field']) || !wp_verify_nonce($_POST['hikmahnews_coauthors_nonce_field'], 'hikmahnews_coauthors_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    $coauthors = array_map('absint', $_POST['hikmahnews_coauthors'] ?? []);
    update_post_meta($post_id, '_hikmahnews_coauthors', $coauthors);
}
add_action('save_post', 'hikmahnews_save_coauthors');

function hikmahnews_display_coauthors() {
    if (!is_single()) return;
    $coauthors = get_post_meta(get_the_ID(), '_hikmahnews_coauthors', true);
    if (empty($coauthors)) return;
    echo '<div class="coauthors">';
    echo '<span class="coauthors__label">Co-authors:</span> ';
    $names = [];
    foreach ($coauthors as $uid) {
        $u = get_userdata((int) $uid);
        if ($u) $names[] = '<a href="' . esc_url(get_author_posts_url($u->ID)) . '">' . esc_html($u->display_name) . '</a>';
    }
    echo implode(', ', $names);
    echo '</div>';
}
add_action('hikmahnews_after_author_box', 'hikmahnews_display_coauthors');

// ============================================
// 2. READING HISTORY (Logged-in users)
// ============================================
function hikmahnews_track_reading_history() {
    if (!is_single() || !is_user_logged_in()) return;
    $user_id = get_current_user_id();
    $history = get_user_meta($user_id, '_hikmahnews_reading_history', true);
    $history = is_array($history) ? $history : [];
    $post_id = get_the_ID();

    // Remove if exists (to move to top)
    $history = array_filter($history, function($h) use ($post_id) { return (int) $h['id'] !== (int) $post_id; });
    array_unshift($history, ['id' => $post_id, 'time' => current_time('mysql')]);
    $history = array_slice($history, 0, 50); // Keep last 50

    update_user_meta($user_id, '_hikmahnews_reading_history', $history);
}
add_action('wp_head', 'hikmahnews_track_reading_history');

// ============================================
// 3. DARK MODE SCHEDULE (8PM – 7AM)
// ============================================
function hikmahnews_dark_mode_schedule() {
    $mode = hikmahnews_option('colors', 'dark_mode', 'auto');
    if ($mode !== 'auto') return;
    ?>
    <script>
    (function() {
        var start = 20, end = 7; // 8PM to 7AM
        var hour = new Date().getHours();
        var isNight = hour >= start || hour < end;
        if (isNight && !localStorage.getItem('hikmahnews-dark')) {
            document.documentElement.classList.add('dark-mode');
        }
    })();
    </script>
    <?php
}
add_action('wp_head', 'hikmahnews_dark_mode_schedule', 0);