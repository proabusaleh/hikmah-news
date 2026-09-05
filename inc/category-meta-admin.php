<?php
/**
 * Category Meta — Admin UI
 * - Color picker for each category
 * - Icon selector
 * - Layout selector
 * - Category thumbnail/image
 * - Display order
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ADD FIELDS TO CATEGORY ADD/EDIT FORMS
// ============================================

// --- Add New Category Form ---
function hikmahnews_category_add_fields() {
    ?>
    <div class="form-field">
        <label for="hikmahnews_cat_color">Category Color</label>
        <input type="color" id="hikmahnews_cat_color" name="hikmahnews_cat_color" value="#DC2626"
               style="height:40px;padding:2px;">
        <p class="description">Primary color for this category (badges, headers, borders).</p>
    </div>
    <div class="form-field">
        <label for="hikmahnews_cat_icon">Category Icon (Emoji)</label>
        <input type="text" id="hikmahnews_cat_icon" name="hikmahnews_cat_icon" value="📰" maxlength="4">
        <p class="description">Emoji icon: 🏛️ 💼 ⚽ 💻 🎬 🏥 💬</p>
    </div>
    <div class="form-field">
        <label for="hikmahnews_cat_layout">Category Layout</label>
        <select id="hikmahnews_cat_layout" name="hikmahnews_cat_layout">
            <option value="standard">📰 Standard Grid</option>
            <option value="finance">💰 Finance (with ticker)</option>
            <option value="sports">⚽ Sports (score cards)</option>
            <option value="tech">💻 Tech (product cards)</option>
            <option value="opinion">💬 Opinion (author focus)</option>
            <option value="magazine">📖 Magazine (mixed)</option>
        </select>
    </div>
    <div class="form-field">
        <label for="hikmahnews_cat_order">Display Order</label>
        <input type="number" id="hikmahnews_cat_order" name="hikmahnews_cat_order" value="0" min="0" max="99">
        <p class="description">Lower number = appears first on homepage.</p>
    </div>
    <div class="form-field">
        <label for="hikmahnews_cat_featured_image">Category Image URL</label>
        <input type="text" id="hikmahnews_cat_featured_image" name="hikmahnews_cat_featured_image" value="">
        <p class="description">Header image for category landing page.</p>
    </div>
    <?php
}
add_action('category_add_form_fields', 'hikmahnews_category_add_fields');

// --- Edit Category Form ---
function hikmahnews_category_edit_fields($term) {
    $color = get_term_meta($term->term_id, 'hikmahnews_color', true) ?: '#DC2626';
    $icon = get_term_meta($term->term_id, 'hikmahnews_icon', true) ?: '📰';
    $layout = get_term_meta($term->term_id, 'hikmahnews_layout', true) ?: 'standard';
    $order = get_term_meta($term->term_id, 'hikmahnews_order', true) ?: '0';
    $image = get_term_meta($term->term_id, 'hikmahnews_image', true) ?: '';
    ?>
    <tr class="form-field">
        <th><label for="hikmahnews_cat_color">Category Color</label></th>
        <td>
            <input type="color" id="hikmahnews_cat_color" name="hikmahnews_cat_color"
                   value="<?php echo esc_attr($color); ?>" style="height:40px;padding:2px;">
            <span style="display:inline-block;width:20px;height:20px;background:<?php echo esc_attr($color); ?>;
                  border-radius:3px;vertical-align:middle;margin-left:8px;"></span>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="hikmahnews_cat_icon">Icon (Emoji)</label></th>
        <td>
            <input type="text" id="hikmahnews_cat_icon" name="hikmahnews_cat_icon"
                   value="<?php echo esc_attr($icon); ?>" maxlength="4" style="width:60px;font-size:20px;">
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="hikmahnews_cat_layout">Layout</label></th>
        <td>
            <select id="hikmahnews_cat_layout" name="hikmahnews_cat_layout">
                <option value="standard" <?php selected($layout, 'standard'); ?>>📰 Standard Grid</option>
                <option value="finance" <?php selected($layout, 'finance'); ?>>💰 Finance</option>
                <option value="sports" <?php selected($layout, 'sports'); ?>>⚽ Sports</option>
                <option value="tech" <?php selected($layout, 'tech'); ?>>💻 Tech</option>
                <option value="opinion" <?php selected($layout, 'opinion'); ?>>💬 Opinion</option>
                <option value="magazine" <?php selected($layout, 'magazine'); ?>>📖 Magazine</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="hikmahnews_cat_order">Display Order</label></th>
        <td>
            <input type="number" id="hikmahnews_cat_order" name="hikmahnews_cat_order"
                   value="<?php echo esc_attr($order); ?>" min="0" max="99" style="width:80px;">
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="hikmahnews_cat_featured_image">Category Image</label></th>
        <td>
            <input type="text" id="hikmahnews_cat_featured_image" name="hikmahnews_cat_featured_image"
                   value="<?php echo esc_attr($image); ?>" class="large-text">
            <?php if ($image) : ?>
                <br><img src="<?php echo esc_url($image); ?>" style="max-width:200px;margin-top:8px;border-radius:4px;">
            <?php endif; ?>
        </td>
    </tr>
    <?php
}
add_action('category_edit_form_fields', 'hikmahnews_category_edit_fields');

// ============================================
// 2. SAVE CATEGORY META
// ============================================
function hikmahnews_save_category_fields($term_id) {
    if (!current_user_can('manage_categories')) return;

    $fields = [
        'hikmahnews_cat_color'          => 'hikmahnews_color',
        'hikmahnews_cat_icon'           => 'hikmahnews_icon',
        'hikmahnews_cat_layout'         => 'hikmahnews_layout',
        'hikmahnews_cat_order'          => 'hikmahnews_order',
        'hikmahnews_cat_featured_image' => 'hikmahnews_image',
    ];

    foreach ($fields as $post_key => $meta_key) {
        if (isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            update_term_meta($term_id, $meta_key, $value);
        }
    }
}
add_action('created_category', 'hikmahnews_save_category_fields');
add_action('edited_category', 'hikmahnews_save_category_fields');

// ============================================
// 3. ADMIN COLUMNS
// ============================================
function hikmahnews_category_admin_columns($columns) {
    $new = [];
    foreach ($columns as $key => $val) {
        $new[$key] = $val;
        if ($key === 'name') {
            $new['cat_color'] = 'Color';
            $new['cat_icon'] = 'Icon';
            $new['cat_layout'] = 'Layout';
        }
    }
    return $new;
}
add_filter('manage_edit-category_columns', 'hikmahnews_category_admin_columns');

function hikmahnews_category_admin_column_data($content, $column_name, $term_id) {
    switch ($column_name) {
        case 'cat_color':
            $color = get_term_meta($term_id, 'hikmahnews_color', true) ?: '#999';
            return '<span style="display:inline-block;width:24px;height:24px;background:'
                   . esc_attr($color) . ';border-radius:4px;vertical-align:middle;"
                   title="' . esc_attr($color) . '"></span> '
                   . '<code>' . esc_html($color) . '</code>';

        case 'cat_icon':
            $icon = get_term_meta($term_id, 'hikmahnews_icon', true) ?: '📰';
            return '<span style="font-size:20px;">' . esc_html($icon) . '</span>';

        case 'cat_layout':
            $layout = get_term_meta($term_id, 'hikmahnews_layout', true) ?: 'standard';
            return '<span class="badge" style="background:#f0f0f0;padding:2px 8px;
                    border-radius:3px;font-size:11px;">' . ucfirst($layout) . '</span>';
    }
    return $content;
}
add_filter('manage_category_custom_column', 'hikmahnews_category_admin_column_data', 10, 3);

// ============================================
// 4. FRONTEND: Dynamic Category CSS Variables
// ============================================
function hikmahnews_category_dynamic_css() {
    if (!is_category() && !is_single()) return;

    $term_id = null;

    if (is_category()) {
        $term_id = get_queried_object_id();
    } elseif (is_single()) {
        $cats = get_the_category();
        if ($cats) $term_id = $cats[0]->term_id;
    }

    if (!$term_id) return;

    $color = get_term_meta($term_id, 'hikmahnews_color', true);
    if (!$color) return;
    ?>
    <style id="hikmahnews-category-color">
        :root {
            --cat-color: <?php echo esc_attr($color); ?>;
            --cat-color-light: <?php echo esc_attr($color); ?>15;
            --cat-color-hover: <?php echo esc_attr($color); ?>CC;
        }

        .archive-header { border-bottom: 3px solid var(--cat-color); }
        .archive-header .badge { background: var(--cat-color); }
        .cat-nav__tab--active { color: var(--cat-color); border-bottom-color: var(--cat-color); }
        .section-title { border-bottom-color: var(--cat-color) !important; }
    </style>
    <?php
}
add_action('wp_head', 'hikmahnews_category_dynamic_css', 20);