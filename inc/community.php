<?php
/**
 * Community Features: Comment Reactions, Fact Check, Instagram Feed
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. COMMENT REACTIONS (👍 👎 ❤️) with AJAX persistence
// ============================================
function hikmahnews_comment_reaction_styles_scripts() {
    ?>
    <style>
        .comment-reactions { display: flex; gap: 8px; margin-top: 8px; }
        .comment-reaction { display: flex; align-items: center; gap: 4px; padding: 4px 10px;
            border: 1px solid var(--modern-border, #e5e5e5); border-radius: 100px; font-size: 12px;
            cursor: pointer; background: var(--modern-surface, #fff); transition: all 0.2s; }
        .comment-reaction:hover { border-color: var(--color-primary); }
        .comment-reaction.active { background: var(--color-primary-light, #fee2e2); border-color: var(--color-primary); }
        .comment-reaction .count { font-weight: 700; }
    </style>
    <script>
    (function() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.comment-reaction');
            if (!btn) return;
            if (typeof hikmahnews_ajax === 'undefined') return;
            var commentId = btn.dataset.comment;
            var type = btn.dataset.type;
            var togglingOn = !btn.classList.contains('active');
            btn.classList.toggle('active', togglingOn);
            var count = btn.querySelector('.count');
            count.textContent = Math.max(0, parseInt(count.textContent || '0') + (togglingOn ? 1 : -1));
            fetch(hikmahnews_ajax.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=hikmahnews_comment_reaction&nonce=' + hikmahnews_ajax.nonce +
                      '&comment_id=' + commentId + '&type=' + type + '&toggle=' + (togglingOn ? 1 : 0)
            }).then(function(r) { return r.json(); }).then(function(d) {
                if (d.success && d.data.counts) {
                    btn.closest('.comment-reactions').querySelectorAll('.comment-reaction').forEach(function(b) {
                        var c = b.querySelector('.count');
                        if (c && d.data.counts[b.dataset.type] !== undefined) c.textContent = d.data.counts[b.dataset.type];
                    });
                }
            }).catch(function() {
                btn.classList.toggle('active', !togglingOn);
                count.textContent = Math.max(0, parseInt(count.textContent || '0') + (togglingOn ? -1 : 1));
            });
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_comment_reaction_styles_scripts');

function hikmahnews_comment_vote_key() {
    if (is_user_logged_in()) return 'u' . get_current_user_id();
    $hash = md5((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
    return 'g' . substr($hash, 0, 16);
}

function hikmahnews_comment_reaction_save() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $comment_id = absint($_POST['comment_id'] ?? 0);
    $type = sanitize_key($_POST['type'] ?? '');
    $toggle = !empty($_POST['toggle']) ? 1 : 0;
    if (!$comment_id || !in_array($type, ['like', 'love', 'dislike'], true)) wp_send_json_error('Invalid');
    if (!get_comment($comment_id)) wp_send_json_error('Comment not found');

    $user_key = hikmahnews_comment_vote_key();
    $votes = get_comment_meta($comment_id, '_hikmahnews_reaction_votes', true);
    $votes = is_array($votes) ? $votes : [];

    if ($toggle) {
        $votes[$user_key] = $type;
    } else {
        if (isset($votes[$user_key]) && $votes[$user_key] === $type) unset($votes[$user_key]);
    }

    update_comment_meta($comment_id, '_hikmahnews_reaction_votes', $votes);

    $counts = ['like' => 0, 'love' => 0, 'dislike' => 0];
    foreach ($votes as $t) {
        if (isset($counts[$t])) $counts[$t]++;
    }
    update_comment_meta($comment_id, '_hikmahnews_reactions', $counts);

    wp_send_json_success(['counts' => $counts]);
}
add_action('wp_ajax_hikmahnews_comment_reaction', 'hikmahnews_comment_reaction_save');
add_action('wp_ajax_nopriv_hikmahnews_comment_reaction', 'hikmahnews_comment_reaction_save');

// Add reactions to comment output
function hikmahnews_add_reactions_to_comment($comment_text, $comment) {
    $reactions = get_comment_meta($comment->comment_ID, '_hikmahnews_reactions', true);
    $reactions = is_array($reactions) ? $reactions : ['like' => 0, 'love' => 0, 'dislike' => 0];
    $comment_text .= '<div class="comment-reactions">';
    $comment_text .= '<span class="comment-reaction" data-comment="' . (int) $comment->comment_ID . '" data-type="like">👍 <span class="count">' . (int) ($reactions['like'] ?? 0) . '</span></span>';
    $comment_text .= '<span class="comment-reaction" data-comment="' . (int) $comment->comment_ID . '" data-type="love">❤️ <span class="count">' . (int) ($reactions['love'] ?? 0) . '</span></span>';
    $comment_text .= '<span class="comment-reaction" data-comment="' . (int) $comment->comment_ID . '" data-type="dislike">👎 <span class="count">' . (int) ($reactions['dislike'] ?? 0) . '</span></span>';
    $comment_text .= '</div>';
    return $comment_text;
}
add_filter('comment_text', 'hikmahnews_add_reactions_to_comment', 20, 2);

// ============================================
// 2. FACT CHECK LABELS
// ============================================
function hikmahnews_fact_check_meta_box() {
    add_meta_box('hikmahnews_fact_check', '🏷️ Fact Check Label', function($post) {
        wp_nonce_field('hikmahnews_factcheck_nonce', 'hikmahnews_factcheck_nonce_field');
        $label = get_post_meta($post->ID, '_hikmahnews_factcheck', true) ?: 'none';
        $options = ['none' => '— No Label —', 'verified' => '✅ Verified', 'misleading' => '⚠️ Misleading',
                     'false' => '❌ False', 'unverified' => '❓ Unverified', 'satire' => '😄 Satire'];
        echo '<select name="hikmahnews_factcheck" style="width:100%;">';
        foreach ($options as $val => $lbl) {
            echo '<option value="' . esc_attr($val) . '" ' . selected($label, $val, false) . '>' . esc_html($lbl) . '</option>';
        }
        echo '</select>';
    }, 'post', 'side');
}
add_action('add_meta_boxes', 'hikmahnews_fact_check_meta_box');

function hikmahnews_save_factcheck($post_id) {
    if (!isset($_POST['hikmahnews_factcheck_nonce_field']) || !wp_verify_nonce($_POST['hikmahnews_factcheck_nonce_field'], 'hikmahnews_factcheck_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    update_post_meta($post_id, '_hikmahnews_factcheck', sanitize_text_field($_POST['hikmahnews_factcheck'] ?? 'none'));
}
add_action('save_post', 'hikmahnews_save_factcheck');

function hikmahnews_display_factcheck() {
    if (!is_single()) return;
    $label = get_post_meta(get_the_ID(), '_hikmahnews_factcheck', true);
    if (!$label || $label === 'none') return;
    $styles = [
        'verified'   => 'background:#D1FAE5;color:#065F46;border-color:#6EE7B7;',
        'misleading' => 'background:#FEF3C7;color:#92400E;border-color:#FCD34D;',
        'false'      => 'background:#FEE2E2;color:#991B1B;border-color:#FCA5A5;',
        'unverified' => 'background:#E0E7FF;color:#3730A3;border-color:#A5B4FC;',
        'satire'     => 'background:#F3E8FF;color:#6B21A8;border-color:#C4B5FD;',
    ];
    $icons = ['verified' => '✅', 'misleading' => '⚠️', 'false' => '❌', 'unverified' => '❓', 'satire' => '😄'];
    echo '<div class="fact-check-badge" style="' . ($styles[$label] ?? '') . 'border:1px solid;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:16px;">';
    echo esc_html(($icons[$label] ?? '') . ' ' . ucfirst($label) . ' Content');
    echo '</div>';
}
add_action('hikmahnews_before_content', 'hikmahnews_display_factcheck');

// ============================================
// 3. INSTAGRAM FEED WIDGET
// ============================================
class HikmahNews_Instagram_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('hikmahnews_instagram', '📸 Hikmah News: Instagram Feed');
    }

    public function widget($args, $instance) {
        $username = $instance['username'] ?? 'hikmahnews';
        $count = (int) ($instance['count'] ?? 6);
        $count = max(3, min(12, $count));
        echo $args['before_widget'];
        echo '<h3 class="sidebar-widget__title">📸 @' . esc_html($username) . '</h3>';
        echo '<div class="insta-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:4px;">';
        for ($i = 0; $i < $count; $i++) {
            echo '<a href="https://instagram.com/' . esc_attr($username) . '" target="_blank" rel="noopener" class="insta-grid__item" style="background:var(--modern-surface-2, #f5f5f5);aspect-ratio:1;display:block;border-radius:8px;overflow:hidden;"></a>';
        }
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        echo '<p><label>Username:</label><input class="widefat" name="' . $this->get_field_name('username') . '" value="' . esc_attr($instance['username'] ?? '') . '"></p>';
        echo '<p><label>Count (3-12):</label><input class="widefat" name="' . $this->get_field_name('count') . '" value="' . esc_attr($instance['count'] ?? 6) . '"></p>';
    }

    public function update($new, $old) {
        return [
            'username' => sanitize_text_field($new['username'] ?? ''),
            'count'    => min(12, max(3, (int) ($new['count'] ?? 6))),
        ];
    }
}

function hikmahnews_register_community_widgets() {
    register_widget('HikmahNews_Instagram_Widget');
}
add_action('widgets_init', 'hikmahnews_register_community_widgets');