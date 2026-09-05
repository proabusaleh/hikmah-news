<?php
/**
 * Breaking News System
 * - Admin meta box to mark posts as "Breaking"
 * - Custom admin column
 * - Frontend ticker + alert bar
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ADMIN META BOX
// ============================================
function hikmahnews_breaking_meta_box() {
    add_meta_box(
        'hikmahnews_breaking_box',
        '🔴 Breaking News',
        'hikmahnews_breaking_meta_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'hikmahnews_breaking_meta_box');

function hikmahnews_breaking_meta_callback($post) {
    wp_nonce_field('hikmahnews_breaking_nonce', 'hikmahnews_breaking_nonce_field');
    $is_breaking = get_post_meta($post->ID, '_hikmahnews_breaking', true);
    $breaking_priority = get_post_meta($post->ID, '_hikmahnews_breaking_priority', true) ?: 'normal';
    $breaking_expiry = get_post_meta($post->ID, '_hikmahnews_breaking_expiry', true);
    ?>
    <div class="hikmahnews-breaking-admin">
        <p>
            <label>
                <input type="checkbox" name="hikmahnews_is_breaking" value="1"
                       <?php checked($is_breaking, '1'); ?>>
                <strong>Mark as Breaking News</strong>
            </label>
        </p>
        <p>
            <label>Priority:</label>
            <select name="hikmahnews_breaking_priority" style="width:100%;">
                <option value="normal" <?php selected($breaking_priority, 'normal'); ?>>Normal</option>
                <option value="high" <?php selected($breaking_priority, 'high'); ?>>🔥 High</option>
                <option value="urgent" <?php selected($breaking_priority, 'urgent'); ?>>🚨 Urgent</option>
            </select>
        </p>
        <p>
            <label>Expiry Date (optional):</label>
            <input type="datetime-local" name="hikmahnews_breaking_expiry"
                   value="<?php echo esc_attr($breaking_expiry); ?>" style="width:100%;">
            <small>Leave empty for no expiry.</small>
        </p>
    </div>
    <?php
}

function hikmahnews_save_breaking_meta($post_id) {
    if (!isset($_POST['hikmahnews_breaking_nonce_field']) ||
        !wp_verify_nonce($_POST['hikmahnews_breaking_nonce_field'], 'hikmahnews_breaking_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Breaking status
    $is_breaking = isset($_POST['hikmahnews_is_breaking']) ? '1' : '0';
    update_post_meta($post_id, '_hikmahnews_breaking', $is_breaking);

    // Priority
    $priority = sanitize_text_field($_POST['hikmahnews_breaking_priority'] ?? 'normal');
    update_post_meta($post_id, '_hikmahnews_breaking_priority', $priority);

    // Expiry
    $expiry = sanitize_text_field($_POST['hikmahnews_breaking_expiry'] ?? '');
    update_post_meta($post_id, '_hikmahnews_breaking_expiry', $expiry);
}
add_action('save_post', 'hikmahnews_save_breaking_meta');

// ============================================
// 2. ADMIN COLUMN
// ============================================
function hikmahnews_breaking_admin_columns($columns) {
    $new = [];
    foreach ($columns as $key => $val) {
        $new[$key] = $val;
        if ($key === 'title') {
            $new['breaking'] = '🔴 Breaking';
        }
    }
    return $new;
}
add_filter('manage_posts_columns', 'hikmahnews_breaking_admin_columns');

function hikmahnews_breaking_admin_column_data($column, $post_id) {
    if ($column === 'breaking') {
        $is_breaking = get_post_meta($post_id, '_hikmahnews_breaking', true);
        $priority = get_post_meta($post_id, '_hikmahnews_breaking_priority', true);

        if ($is_breaking === '1') {
            $colors = ['normal' => '#F59E0B', 'high' => '#EF4444', 'urgent' => '#DC2626'];
            $labels = ['normal' => 'Breaking', 'high' => '🔥 High', 'urgent' => '🚨 Urgent'];
            $color = $colors[$priority] ?? '#EF4444';
            $label = $labels[$priority] ?? 'Breaking';
            echo '<span style="background:' . $color . ';color:#fff;padding:2px 8px;
                  border-radius:3px;font-size:11px;font-weight:bold;">' . $label . '</span>';
        } else {
            echo '<span style="color:#999;">—</span>';
        }
    }
}
add_action('manage_posts_custom_column', 'hikmahnews_breaking_admin_column_data', 10, 2);

// ============================================
// 3. HELPER: Get Active Breaking Posts
// ============================================
function hikmahnews_get_breaking_posts($count = 8) {
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'meta_query'     => [
            [
                'key'   => '_hikmahnews_breaking',
                'value' => '1',
            ],
        ],
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ];

    $posts = get_posts($args);

    // Filter out expired
    $active = [];
    foreach ($posts as $post) {
        $expiry = get_post_meta($post->ID, '_hikmahnews_breaking_expiry', true);
        if ($expiry && strtotime($expiry) < current_time('timestamp')) {
            continue; // Expired
        }
        $active[] = $post;
    }

    return $active;
}

// ============================================
// 4. FRONTEND: Breaking News Alert Bar
// ============================================
function hikmahnews_breaking_alert_bar() {
    $breaking = hikmahnews_get_breaking_posts(1);
    if (empty($breaking)) return;

    $post = $breaking[0];
    $priority = get_post_meta($post->ID, '_hikmahnews_breaking_priority', true);
    $bg_colors = [
        'normal' => '#DC2626',
        'high'   => '#B91C1C',
        'urgent' => '#7F1D1D',
    ];
    $bg = $bg_colors[$priority] ?? '#DC2626';
    ?>
    <div class="breaking-alert" style="--breaking-bg: <?php echo esc_attr($bg); ?>"
         id="breakingAlert">
        <div class="container breaking-alert__inner">
            <span class="breaking-alert__badge">
                <span class="breaking-alert__pulse"></span>
                <?php
                if ($priority === 'urgent') echo '🚨 URGENT';
                elseif ($priority === 'high') echo '🔥 BREAKING';
                else echo '⚡ BREAKING';
                ?>
            </span>
            <a href="<?php echo esc_url(get_permalink($post)); ?>" class="breaking-alert__text">
                <?php echo esc_html($post->post_title); ?>
            </a>
            <time class="breaking-alert__time">
                <?php echo human_time_diff(strtotime($post->post_date), current_time('timestamp')) . ' ago'; ?>
            </time>
            <button class="breaking-alert__close" onclick="this.closest('.breaking-alert').remove()"
                    aria-label="Close">✕</button>
        </div>
    </div>
    <?php
}
add_action('wp_body_open', 'hikmahnews_breaking_alert_bar');

// ============================================
// 5. FRONTEND: Breaking News Ticker (Enhanced)
// ============================================
function hikmahnews_breaking_ticker() {
    $breaking = hikmahnews_get_breaking_posts(6);
    if (empty($breaking)) return;
    ?>
    <div class="breaking-ticker">
        <div class="container breaking-ticker__inner">
            <span class="breaking-ticker__label">
                <span class="breaking-ticker__dot"></span>
                LIVE
            </span>
            <div class="breaking-ticker__track-wrapper">
                <div class="breaking-ticker__track">
                    <?php foreach ($breaking as $post) : ?>
                        <a href="<?php echo esc_url(get_permalink($post)); ?>"
                           class="breaking-ticker__item">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                    <?php endforeach; ?>
                    <?php // Duplicate for seamless loop ?>
                    <?php foreach ($breaking as $post) : ?>
                        <a href="<?php echo esc_url(get_permalink($post)); ?>"
                           class="breaking-ticker__item">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}