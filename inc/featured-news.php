<?php
/**
 * Featured News System
 * - Admin meta box for "Featured" flag
 * - Featured position: hero, sidebar, section-top
 * - Auto-expiry for featured slots
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ADMIN META BOX
// ============================================
function wpnews_featured_meta_box() {
    add_meta_box(
        'wpnews_featured_box',
        '⭐ Featured News',
        'wpnews_featured_meta_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wpnews_featured_meta_box');

function wpnews_featured_meta_callback($post) {
    wp_nonce_field('wpnews_featured_nonce', 'wpnews_featured_nonce_field');
    $is_featured = get_post_meta($post->ID, '_wpnews_featured', true);
    $featured_position = get_post_meta($post->ID, '_wpnews_featured_position', true) ?: 'hero';
    $featured_order = get_post_meta($post->ID, '_wpnews_featured_order', true) ?: '0';
    ?>
    <div class="wpnews-featured-admin">
        <p>
            <label>
                <input type="checkbox" name="wpnews_is_featured" value="1"
                       <?php checked($is_featured, '1'); ?>>
                <strong>⭐ Mark as Featured</strong>
            </label>
        </p>
        <p>
            <label>Position:</label>
            <select name="wpnews_featured_position" style="width:100%;">
                <option value="hero" <?php selected($featured_position, 'hero'); ?>>🏠 Hero Section</option>
                <option value="sidebar" <?php selected($featured_position, 'sidebar'); ?>>📌 Sidebar</option>
                <option value="section" <?php selected($featured_position, 'section'); ?>>📰 Section Top</option>
                <option value="spotlight" <?php selected($featured_position, 'spotlight'); ?>>💡 Spotlight</option>
            </select>
        </p>
        <p>
            <label>Order (lower = first):</label>
            <input type="number" name="wpnews_featured_order"
                   value="<?php echo esc_attr($featured_order); ?>"
                   min="0" max="99" style="width:100%;">
        </p>
    </div>
    <?php
}

function wpnews_save_featured_meta($post_id) {
    if (!isset($_POST['wpnews_featured_nonce_field']) ||
        !wp_verify_nonce($_POST['wpnews_featured_nonce_field'], 'wpnews_featured_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $is_featured = isset($_POST['wpnews_is_featured']) ? '1' : '0';
    update_post_meta($post_id, '_wpnews_featured', $is_featured);

    $position = sanitize_text_field($_POST['wpnews_featured_position'] ?? 'hero');
    update_post_meta($post_id, '_wpnews_featured_position', $position);

    $order = absint($_POST['wpnews_featured_order'] ?? 0);
    update_post_meta($post_id, '_wpnews_featured_order', $order);
}
add_action('save_post', 'wpnews_save_featured_meta');

// ============================================
// 2. HELPER: Get Featured Posts by Position
// ============================================
function wpnews_get_featured_posts($position = 'hero', $count = 5) {
    return get_posts([
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'   => '_wpnews_featured',
                'value' => '1',
            ],
            [
                'key'   => '_wpnews_featured_position',
                'value' => $position,
            ],
        ],
        'meta_key'       => '_wpnews_featured_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ]);
}

// ============================================
// 3. ADMIN COLUMN
// ============================================
function wpnews_featured_admin_columns($columns) {
    $new = [];
    foreach ($columns as $key => $val) {
        $new[$key] = $val;
        if ($key === 'breaking') {
            $new['featured'] = '⭐ Featured';
        }
    }
    return $new;
}
add_filter('manage_posts_columns', 'wpnews_featured_admin_columns');

function wpnews_featured_admin_column_data($column, $post_id) {
    if ($column === 'featured') {
        $is_featured = get_post_meta($post_id, '_wpnews_featured', true);
        if ($is_featured === '1') {
            $position = get_post_meta($post_id, '_wpnews_featured_position', true);
            $icons = [
                'hero' => '🏠', 'sidebar' => '📌',
                'section' => '📰', 'spotlight' => '💡',
            ];
            $icon = $icons[$position] ?? '⭐';
            echo '<span style="color:#F59E0B;font-weight:bold;">' . $icon . ' '
                 . ucfirst($position) . '</span>';
        } else {
            echo '<span style="color:#ccc;">—</span>';
        }
    }
}
add_action('manage_posts_custom_column', 'wpnews_featured_admin_column_data', 10, 2);

// ============================================
// 4. SPOTLIGHT SECTION (Frontend)
// ============================================
function wpnews_spotlight_section() {
    $spotlight = wpnews_get_featured_posts('spotlight', 3);
    if (empty($spotlight)) return;
    ?>
    <section class="spotlight-section">
        <div class="container">
            <div class="section-title" style="border-bottom-color: var(--color-accent);">
                <h2 class="section-title__text">
                    <span class="section-title__icon">💡</span>
                    Spotlight
                </h2>
                <div class="section-title__line"></div>
            </div>
            <div class="spotlight-grid">
                <?php foreach ($spotlight as $post) : setup_postdata($post); ?>
                    <article class="spotlight-card">
                        <a href="<?php the_permalink(); ?>" class="spotlight-card__link">
                            <div class="spotlight-card__image">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('wpnews-hero'); ?>
                                <div class="spotlight-card__overlay"></div>
                                <span class="spotlight-card__badge">SPOTLIGHT</span>
                            </div>
                            <div class="spotlight-card__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="badge badge--accent">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="spotlight-card__title"><?php the_title(); ?></h3>
                                <p class="spotlight-card__excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </p>
                            </div>
                        </a>
                    </article>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php
}