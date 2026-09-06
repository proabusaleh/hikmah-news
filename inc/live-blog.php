<?php
/**
 * Live Blog / Live Coverage System
 * - Real-time updates for events (CPT + entries)
 * - Auto-refresh via AJAX
 * - Timestamped entries
 * - Live status: upcoming / live / ended
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// CPT: Live Blog
function hikmahnews_live_blog_cpt() {
    register_post_type('hikmahnews_live_blog', [
        'labels'       => ['name' => 'Live Blogs', 'singular_name' => 'Live Blog', 'add_new_item' => 'New Live Coverage'],
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-rss',
        'menu_position'=> 26,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest' => true,
    ]);

    register_post_type('hikmahnews_live_blog_entry', [
        'labels'       => ['name' => 'Live Entries', 'singular_name' => 'Live Entry'],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'edit.php?post_type=hikmahnews_live_blog',
        'supports'     => ['editor'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'hikmahnews_live_blog_cpt');

// Meta Box: Live Status
function hikmahnews_live_blog_meta() {
    add_meta_box('hikmahnews_live_status', '🔴 Live Status', function($post) {
        wp_nonce_field('hikmahnews_live_nonce', 'hikmahnews_live_nonce_field');
        $status = get_post_meta($post->ID, '_hikmahnews_live_status', true) ?: 'upcoming';
        echo '<select name="hikmahnews_live_status" style="width:100%;">';
        echo '<option value="upcoming" ' . selected($status, 'upcoming', false) . '>⏳ Upcoming</option>';
        echo '<option value="live" ' . selected($status, 'live', false) . '>🔴 LIVE</option>';
        echo '<option value="ended" ' . selected($status, 'ended', false) . '>✅ Ended</option>';
        echo '</select>';
    }, 'hikmahnews_live_blog', 'side', 'high');
}
add_action('add_meta_boxes', 'hikmahnews_live_blog_meta');

function hikmahnews_save_live_blog($post_id) {
    if (!isset($_POST['hikmahnews_live_nonce_field']) || !wp_verify_nonce($_POST['hikmahnews_live_nonce_field'], 'hikmahnews_live_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    update_post_meta($post_id, '_hikmahnews_live_status', sanitize_text_field($_POST['hikmahnews_live_status'] ?? 'upcoming'));
}
add_action('save_post_hikmahnews_live_blog', 'hikmahnews_save_live_blog');

// AJAX: Fetch live updates
function hikmahnews_live_blog_updates() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');
    $post_id = absint($_POST['post_id'] ?? 0);
    $after = sanitize_text_field($_POST['after'] ?? '');

    $args = [
        'post_parent'    => $post_id,
        'post_type'      => 'hikmahnews_live_blog_entry',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    if ($after) $args['date_query'] = [['after' => $after]];

    $entries = get_posts($args);
    $html = '';
    foreach ($entries as $entry) {
        $html .= '<div class="live-entry" data-time="' . esc_attr($entry->post_date) . '">';
        $html .= '<time class="live-entry__time">' . esc_html(get_the_date('g:i A', $entry)) . '</time>';
        $html .= '<div class="live-entry__content">' . wp_kses_post($entry->post_content) . '</div>';
        $html .= '</div>';
    }
    wp_send_json_success(['html' => $html, 'count' => count($entries)]);
}
add_action('wp_ajax_hikmahnews_live_updates', 'hikmahnews_live_blog_updates');
add_action('wp_ajax_nopriv_hikmahnews_live_updates', 'hikmahnews_live_blog_updates');

// Frontend: Live Blog Widget
function hikmahnews_live_blog_widget($post_id) {
    if (!$post_id) $post_id = get_the_ID();
    $status = get_post_meta($post_id, '_hikmahnews_live_status', true) ?: 'upcoming';
    $entries = get_posts([
        'post_parent'    => $post_id,
        'post_type'      => 'hikmahnews_live_blog_entry',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    ?>
    <div class="live-blog" id="liveBlog" data-post-id="<?php echo absint($post_id); ?>" data-status="<?php echo esc_attr($status); ?>">
        <div class="live-blog__header">
            <span class="live-blog__status live-blog__status--<?php echo esc_attr($status); ?>">
                <?php echo $status === 'live' ? '🔴 LIVE' : ($status === 'ended' ? '✅ Ended' : '⏳ Upcoming'); ?>
            </span>
            <h2 class="live-blog__title"><?php echo esc_html(get_the_title($post_id)); ?></h2>
        </div>
        <div class="live-blog__entries" id="liveEntries">
            <?php foreach ($entries as $entry) : ?>
                <div class="live-entry" data-time="<?php echo esc_attr($entry->post_date); ?>">
                    <time class="live-entry__time"><?php echo esc_html(get_the_date('g:i A', $entry)); ?></time>
                    <div class="live-entry__content"><?php echo wp_kses_post($entry->post_content); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($status === 'live') : ?>
    <script>
    (function() {
        var blog = document.getElementById('liveBlog');
        if (!blog || blog.dataset.status !== 'live') return;
        if (typeof hikmahnews_ajax === 'undefined') return;
        setInterval(function() {
            var entries = document.getElementById('liveEntries');
            var latest = entries.querySelector('.live-entry');
            var after = latest ? latest.dataset.time : '';
            fetch(hikmahnews_ajax.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=hikmahnews_live_updates&nonce=' + hikmahnews_ajax.nonce + '&post_id=' + blog.dataset.postId + '&after=' + encodeURIComponent(after)
            }).then(r => r.json()).then(data => {
                if (data.success && data.data.html && entries) {
                    entries.insertAdjacentHTML('afterbegin', data.data.html);
                }
            }).catch(function() {});
        }, 30000); // Refresh every 30s
    })();
    </script>
    <?php endif;
}