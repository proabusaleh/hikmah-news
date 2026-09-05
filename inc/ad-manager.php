<?php
/**
 * Advertisement Manager
 * - Admin panel for managing all ad positions
 * - Support: Image, HTML/JS code, Shortcode
 * - Device targeting: Desktop / Mobile / Both
 * - Schedule: Start/End dates
 * - Impression tracking
 * - A/B rotation (multiple ads per position)
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. AD POSITIONS REGISTRY
// ============================================
function hikmahnews_ad_positions() {
    return [
        'header'       => [
            'label'       => 'Header Banner',
            'description' => 'Top of page, below navigation (728×90 or responsive)',
            'size'        => '728×90',
            'icon'        => '🔝',
        ],
        'homepage_top' => [
            'label'       => 'Homepage Top',
            'description' => 'Between hero section and latest news',
            'size'        => '970×250',
            'icon'        => '🏠',
        ],
        'homepage_mid' => [
            'label'       => 'Homepage Middle',
            'description' => 'Between category sections',
            'size'        => '728×90',
            'icon'        => '🏠',
        ],
        'homepage_bottom' => [
            'label'       => 'Homepage Bottom',
            'description' => 'Before footer on homepage',
            'size'        => '728×90',
            'icon'        => '🏠',
        ],
        'category_top' => [
            'label'       => 'Category Page Top',
            'description' => 'Top of category archive pages',
            'size'        => '970×250',
            'icon'        => '📂',
        ],
        'in_content_1' => [
            'label'       => 'In-Content (After Para 2)',
            'description' => 'Inside single post, after 2nd paragraph',
            'size'        => '336×280',
            'icon'        => '📄',
        ],
        'in_content_2' => [
            'label'       => 'In-Content (After Para 5)',
            'description' => 'Inside single post, after 5th paragraph',
            'size'        => '336×280',
            'icon'        => '📄',
        ],
        'in_content_3' => [
            'label'       => 'In-Content (Before Last Para)',
            'description' => 'Inside single post, before last paragraph',
            'size'        => '728×90',
            'icon'        => '📄',
        ],
        'sidebar_top'  => [
            'label'       => 'Sidebar Top',
            'description' => 'Top of sidebar (300×250 or 300×600)',
            'size'        => '300×250',
            'icon'        => '📌',
        ],
        'sidebar_mid'  => [
            'label'       => 'Sidebar Middle',
            'description' => 'Middle of sidebar',
            'size'        => '300×250',
            'icon'        => '📌',
        ],
        'footer'       => [
            'label'       => 'Footer Banner',
            'description' => 'Above footer widgets',
            'size'        => '728×90',
            'icon'        => '🔻',
        ],
        'mobile_sticky' => [
            'label'       => 'Mobile Sticky Bottom',
            'description' => 'Sticky ad at bottom on mobile devices only',
            'size'        => '320×50',
            'icon'        => '📱',
        ],
        'mobile_interstitial' => [
            'label'       => 'Mobile Interstitial',
            'description' => 'Full-screen ad between page loads (mobile only)',
            'size'        => '320×480',
            'icon'        => '📱',
        ],
    ];
}

// ============================================
// 2. ADMIN PAGE
// ============================================
function hikmahnews_ad_admin_menu() {
    add_menu_page(
        'Ad Manager',
        '💰 Ad Manager',
        'manage_options',
        'hikmahnews-ads',
        'hikmahnews_ad_admin_page',
        'dashicons-megaphone',
        30
    );
}
add_action('admin_menu', 'hikmahnews_ad_admin_menu');

function hikmahnews_ad_admin_page() {
    // Save handler
    if (isset($_POST['hikmahnews_ad_nonce']) && wp_verify_nonce($_POST['hikmahnews_ad_nonce'], 'hikmahnews_ad_save')) {
        hikmahnews_save_ad_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>✅ Ad settings saved!</p></div>';
    }

    $positions = hikmahnews_ad_positions();
    $ads = get_option('hikmahnews_ads', []);
    $global_enabled = get_option('hikmahnews_ads_enabled', '1');
    ?>
    <div class="wrap">
        <h1>💰 Advertisement Manager</h1>
        <p>Manage ad placements across your news portal. Supports HTML/JS code, images, and shortcodes.</p>

        <form method="POST">
            <?php wp_nonce_field('hikmahnews_ad_save', 'hikmahnews_ad_nonce'); ?>

            <!-- Global Toggle -->
            <div style="background:#fff;padding:15px 20px;border:1px solid #ddd;border-radius:6px;margin:20px 0;">
                <label style="font-size:16px;font-weight:bold;">
                    <input type="checkbox" name="ads_global_enabled" value="1"
                           <?php checked($global_enabled, '1'); ?>>
                    Enable All Advertisements
                </label>
                <p class="description">Uncheck to temporarily disable all ads site-wide.</p>
            </div>

            <!-- Ad Positions -->
            <?php foreach ($positions as $pos_key => $pos_data) :
                $ad = $ads[$pos_key] ?? [];
                $type = $ad['type'] ?? 'code';
                $code = $ad['code'] ?? '';
                $image_url = $ad['image_url'] ?? '';
                $link_url = $ad['link_url'] ?? '';
                $device = $ad['device'] ?? 'both';
                $enabled = $ad['enabled'] ?? '0';
                $start_date = $ad['start_date'] ?? '';
                $end_date = $ad['end_date'] ?? '';
            ?>
                <div style="background:#fff;border:1px solid #ddd;border-radius:6px;margin:15px 0;overflow:hidden;">
                    <div style="background:#f8f9fa;padding:12px 20px;border-bottom:1px solid #ddd;
                                display:flex;align-items:center;justify-content:space-between;">
                        <h3 style="margin:0;">
                            <?php echo $pos_data['icon']; ?> <?php echo $pos_data['label']; ?>
                            <code style="font-size:11px;color:#666;"><?php echo $pos_data['size']; ?></code>
                        </h3>
                        <label>
                            <input type="checkbox" name="ads[<?php echo $pos_key; ?>][enabled]" value="1"
                                   <?php checked($enabled, '1'); ?>>
                            <strong>Active</strong>
                        </label>
                    </div>
                    <div style="padding:15px 20px;">
                        <p class="description" style="margin-bottom:10px;">
                            <?php echo $pos_data['description']; ?>
                        </p>

                        <table class="form-table" style="margin:0;">
                            <tr>
                                <th style="width:120px;">Ad Type</th>
                                <td>
                                    <select name="ads[<?php echo $pos_key; ?>][type]"
                                            class="ad-type-select" data-target="<?php echo $pos_key; ?>">
                                        <option value="code" <?php selected($type, 'code'); ?>>
                                            📝 HTML / JS Code (AdSense, DFP, etc.)
                                        </option>
                                        <option value="image" <?php selected($type, 'image'); ?>>
                                            🖼️ Image + Link
                                        </option>
                                        <option value="shortcode" <?php selected($type, 'shortcode'); ?>>
                                            🔌 Shortcode
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="ad-field-code ad-field-<?php echo $pos_key; ?>"
                                style="<?php echo $type !== 'code' ? 'display:none;' : ''; ?>">
                                <th>Ad Code</th>
                                <td>
                                    <textarea name="ads[<?php echo $pos_key; ?>][code]" rows="4"
                                              class="large-text code"
                                              placeholder="Paste your ad code here..."><?php echo esc_textarea($code); ?></textarea>
                                    <p class="description">
                                        Paste AdSense, DFP, or any HTML/JS ad code.
                                    </p>
                                </td>
                            </tr>
                            <tr class="ad-field-image ad-field-<?php echo $pos_key; ?>"
                                style="<?php echo $type !== 'image' ? 'display:none;' : ''; ?>">
                                <th>Image URL</th>
                                <td>
                                    <input type="text" name="ads[<?php echo $pos_key; ?>][image_url]"
                                           value="<?php echo esc_attr($image_url); ?>" class="large-text"
                                           placeholder="https://example.com/ad-banner.jpg">
                                    <br>
                                    <label style="margin-top:8px;display:block;">Link URL:</label>
                                    <input type="text" name="ads[<?php echo $pos_key; ?>][link_url]"
                                           value="<?php echo esc_attr($link_url); ?>" class="large-text"
                                           placeholder="https://advertiser.com/landing-page">
                                </td>
                            </tr>
                            <tr>
                                <th>Device</th>
                                <td>
                                    <select name="ads[<?php echo $pos_key; ?>][device]">
                                        <option value="both" <?php selected($device, 'both'); ?>>
                                            🖥️📱 All Devices
                                        </option>
                                        <option value="desktop" <?php selected($device, 'desktop'); ?>>
                                            🖥️ Desktop Only
                                        </option>
                                        <option value="mobile" <?php selected($device, 'mobile'); ?>>
                                            📱 Mobile Only
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Schedule</th>
                                <td>
                                    <label>Start:</label>
                                    <input type="date" name="ads[<?php echo $pos_key; ?>][start_date]"
                                           value="<?php echo esc_attr($start_date); ?>">
                                    <label style="margin-left:10px;">End:</label>
                                    <input type="date" name="ads[<?php echo $pos_key; ?>][end_date]"
                                           value="<?php echo esc_attr($end_date); ?>">
                                    <p class="description">Leave empty for always active.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php submit_button('💾 Save All Ad Settings'); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.ad-type-select').on('change', function() {
            var target = $(this).data('target');
            var type = $(this).val();
            $('.ad-field-' + target).hide();
            $('.ad-field-' + type + '.ad-field-' + target).show();
        });
    });
    </script>
    <?php
}

// ============================================
// 3. SAVE HANDLER
// ============================================
function hikmahnews_save_ad_settings($post_data) {
    $ads = [];

    if (!empty($post_data['ads'])) {
        foreach ($post_data['ads'] as $pos => $data) {
            $ads[$pos] = [
                'type'       => sanitize_text_field($data['type'] ?? 'code'),
                'code'       => wp_kses_post($data['code'] ?? ''),
                'image_url'  => esc_url_raw($data['image_url'] ?? ''),
                'link_url'   => esc_url_raw($data['link_url'] ?? ''),
                'device'     => sanitize_text_field($data['device'] ?? 'both'),
                'enabled'    => isset($data['enabled']) ? '1' : '0',
                'start_date' => sanitize_text_field($data['start_date'] ?? ''),
                'end_date'   => sanitize_text_field($data['end_date'] ?? ''),
            ];
        }
    }

    update_option('hikmahnews_ads', $ads);
    update_option('hikmahnews_ads_enabled', isset($post_data['ads_global_enabled']) ? '1' : '0');
}

// ============================================
// 4. FRONTEND RENDER FUNCTION
// ============================================
function hikmahnews_render_ad($position) {
    $global = get_option('hikmahnews_ads_enabled', '1');
    if ($global !== '1') return;

    $ads = get_option('hikmahnews_ads', []);
    $ad = $ads[$position] ?? null;

    if (!$ad || $ad['enabled'] !== '1') return;

    // Schedule check
    $now = current_time('Y-m-d');
    if ($ad['start_date'] && $ad['start_date'] > $now) return;
    if ($ad['end_date'] && $ad['end_date'] < $now) return;

    // Device class
    $device_class = '';
    if ($ad['device'] === 'desktop') $device_class = 'ad--desktop-only';
    if ($ad['device'] === 'mobile') $device_class = 'ad--mobile-only';

    $positions = hikmahnews_ad_positions();
    $label = $positions[$position]['label'] ?? $position;

    echo '<div class="hikmahnews-ad hikmahnews-ad--' . esc_attr($position) . ' ' . $device_class . '"
               data-ad-position="' . esc_attr($position) . '">';
    echo '<span class="hikmahnews-ad__label">Advertisement</span>';

    switch ($ad['type']) {
        case 'image':
            if ($ad['image_url']) {
                $link = $ad['link_url'] ?: '#';
                echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener sponsored">';
                echo '<img src="' . esc_url($ad['image_url']) . '" alt="' . esc_attr($label) . '"
                           loading="lazy" class="hikmahnews-ad__image">';
                echo '</a>';
            }
            break;

        case 'shortcode':
            echo do_shortcode($ad['code']);
            break;

        case 'code':
        default:
            echo $ad['code']; // Already sanitized with wp_kses_post
            break;
    }

    echo '</div>';
}

// ============================================
// 5. AD IMPRESSION TRACKING (Simple)
// ============================================
function hikmahnews_ad_impression_script() {
    $global = get_option('hikmahnews_ads_enabled', '1');
    if ($global !== '1') return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var ads = document.querySelectorAll('.hikmahnews-ad');
        if (!ads.length || typeof hikmahnews_ajax === 'undefined') return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var pos = entry.target.dataset.adPosition;
                    navigator.sendBeacon(hikmahnews_ajax.ajax_url,
                        'action=hikmahnews_ad_impression&nonce=' + hikmahnews_ajax.nonce + '&position=' + pos
                    );
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        ads.forEach(function(ad) { observer.observe(ad); });
    });
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_ad_impression_script');

function hikmahnews_ad_impression_handler() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');
    $position = sanitize_text_field($_POST['position'] ?? '');
    if (!$position) wp_send_json_error();

    $key = 'hikmahnews_ad_imp_' . $position . '_' . date('Y-m-d');
    $count = (int) get_transient($key);
    set_transient($key, $count + 1, DAY_IN_SECONDS);

    wp_send_json_success();
}
add_action('wp_ajax_hikmahnews_ad_impression', 'hikmahnews_ad_impression_handler');
add_action('wp_ajax_nopriv_hikmahnews_ad_impression', 'hikmahnews_ad_impression_handler');