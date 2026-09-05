<?php
/**
 * Ad Placement Hooks
 * - Auto-insert ads at specific positions
 * - In-content paragraph injection
 * - Template hooks for all positions
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. HEADER AD (after nav, before content)
// ============================================
function hikmahnews_header_ad() {
    hikmahnews_render_ad('header');
}
add_action('wp_body_open', 'hikmahnews_header_ad', 15);

// ============================================
// 2. HOMEPAGE ADS
// ============================================
function hikmahnews_homepage_top_ad() {
    if (!is_front_page()) return;
    hikmahnews_render_ad('homepage_top');
}
add_action('hikmahnews_after_hero', 'hikmahnews_homepage_top_ad');

function hikmahnews_homepage_mid_ad() {
    if (!is_front_page()) return;
    hikmahnews_render_ad('homepage_mid');
}
// Called manually in front-page.php between sections

function hikmahnews_homepage_bottom_ad() {
    if (!is_front_page()) return;
    hikmahnews_render_ad('homepage_bottom');
}
add_action('hikmahnews_before_footer', 'hikmahnews_homepage_bottom_ad');

// ============================================
// 3. CATEGORY AD
// ============================================
function hikmahnews_category_ad() {
    if (!is_category()) return;
    hikmahnews_render_ad('category_top');
}
add_action('hikmahnews_after_category_header', 'hikmahnews_category_ad');

// ============================================
// 4. IN-CONTENT ADS (Paragraph Injection)
// ============================================
function hikmahnews_in_content_ads($content) {
    if (!is_single() || !is_main_query()) return $content;

    $global = get_option('hikmahnews_ads_enabled', '1');
    if ($global !== '1') return $content;

    $ads = get_option('hikmahnews_ads', []);

    // Get ad codes
    $ad1 = hikmahnews_get_ad_html('in_content_1', $ads);
    $ad2 = hikmahnews_get_ad_html('in_content_2', $ads);
    $ad3 = hikmahnews_get_ad_html('in_content_3', $ads);

    if (!$ad1 && !$ad2 && !$ad3) return $content;

    // Split content by paragraphs
    $paragraphs = explode('</p>', $content);
    $total_paras = count($paragraphs);

    if ($total_paras < 4) return $content; // Too short for ads

    $new_content = '';

    foreach ($paragraphs as $index => $para) {
        $para_num = $index + 1;
        $new_content .= $para . '</p>';

        // Ad after paragraph 2
        if ($para_num === 2 && $ad1) {
            $new_content .= $ad1;
        }

        // Ad after paragraph 5
        if ($para_num === 5 && $ad2) {
            $new_content .= $ad2;
        }

        // Ad before last paragraph
        if ($para_num === $total_paras - 1 && $ad3) {
            $new_content .= $ad3;
        }
    }

    return $new_content;
}
add_filter('the_content', 'hikmahnews_in_content_ads', 20);

// Helper: Get ad HTML for a position
function hikmahnews_get_ad_html($position, $ads = null) {
    if (!$ads) $ads = get_option('hikmahnews_ads', []);
    $ad = $ads[$position] ?? null;

    if (!$ad || $ad['enabled'] !== '1') return '';

    // Schedule check
    $now = current_time('Y-m-d');
    if ($ad['start_date'] && $ad['start_date'] > $now) return '';
    if ($ad['end_date'] && $ad['end_date'] < $now) return '';

    $device_class = '';
    if ($ad['device'] === 'desktop') $device_class = 'ad--desktop-only';
    if ($ad['device'] === 'mobile') $device_class = 'ad--mobile-only';

    $html = '<div class="hikmahnews-ad hikmahnews-ad--' . esc_attr($position) . ' ' . $device_class . '"
                  data-ad-position="' . esc_attr($position) . '">';
    $html .= '<span class="hikmahnews-ad__label">Advertisement</span>';

    switch ($ad['type']) {
        case 'image':
            if ($ad['image_url']) {
                $link = $ad['link_url'] ?: '#';
                $html .= '<a href="' . esc_url($link) . '" target="_blank" rel="noopener sponsored">';
                $html .= '<img src="' . esc_url($ad['image_url']) . '" alt="Ad" loading="lazy"
                               class="hikmahnews-ad__image">';
                $html .= '</a>';
            }
            break;
        case 'shortcode':
            $html .= do_shortcode($ad['code']);
            break;
        default:
            $html .= $ad['code'];
    }

    $html .= '</div>';
    return $html;
}

// ============================================
// 5. SIDEBAR ADS (Widget)
// ============================================
class HikmahNews_Ad_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'hikmahnews_ad_widget',
            '💰 Hikmah News: Advertisement',
            ['description' => 'Display an ad from the Ad Manager']
        );
    }

    public function widget($args, $instance) {
        $position = $instance['position'] ?? 'sidebar_top';
        echo $args['before_widget'];
        hikmahnews_render_ad($position);
        echo $args['after_widget'];
    }

    public function form($instance) {
        $position = $instance['position'] ?? 'sidebar_top';
        $positions = hikmahnews_ad_positions();
        ?>
        <p>
            <label>Ad Position:</label>
            <select class="widefat" name="<?php echo $this->get_field_name('position'); ?>">
                <?php foreach ($positions as $key => $data) : ?>
                    <option value="<?php echo $key; ?>" <?php selected($position, $key); ?>>
                        <?php echo $data['icon']; ?> <?php echo $data['label']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function update($new, $old) {
        return ['position' => sanitize_text_field($new['position'])];
    }
}

function hikmahnews_register_ad_widget() {
    register_widget('HikmahNews_Ad_Widget');
}
add_action('widgets_init', 'hikmahnews_register_ad_widget');

// ============================================
// 6. FOOTER AD
// ============================================
function hikmahnews_footer_ad() {
    hikmahnews_render_ad('footer');
}
add_action('hikmahnews_before_footer_widgets', 'hikmahnews_footer_ad');

// ============================================
// 7. MOBILE STICKY AD
// ============================================
function hikmahnews_mobile_sticky_ad() {
    hikmahnews_render_ad('mobile_sticky');
}
add_action('wp_footer', 'hikmahnews_mobile_sticky_ad', 5);

// ============================================
// 8. CUSTOM HOOKS (for theme templates)
// ============================================
// Add these hooks in your templates:
// do_action('hikmahnews_after_hero');
// do_action('hikmahnews_before_footer');
// do_action('hikmahnews_after_category_header');
// do_action('hikmahnews_before_footer_widgets');