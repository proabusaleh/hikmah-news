<?php
/**
 * Theme Customizer Options
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

function wpnews_customize_register($wp_customize) {

    // ==========================================
    // PANEL: Theme Options
    // ==========================================
    $wp_customize->add_panel('wpnews_options', [
        'title'    => __('WP News Options', 'wpnews'),
        'priority' => 30,
    ]);

    // ==========================================
    // SECTION: Colors
    // ==========================================
    $wp_customize->add_section('wpnews_colors', [
        'title' => __('Colors', 'wpnews'),
        'panel' => 'wpnews_options',
    ]);

    $wp_customize->add_setting('wpnews_primary_color', [
        'default'           => '#DC2626',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wpnews_primary_color', [
        'label'   => __('Primary Color', 'wpnews'),
        'section' => 'wpnews_colors',
    ]));

    $wp_customize->add_setting('wpnews_secondary_color', [
        'default'           => '#1E3A5F',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wpnews_secondary_color', [
        'label'   => __('Secondary Color', 'wpnews'),
        'section' => 'wpnews_colors',
    ]));

    // ==========================================
    // SECTION: Homepage
    // ==========================================
    $wp_customize->add_section('wpnews_homepage', [
        'title' => __('Homepage Settings', 'wpnews'),
        'panel' => 'wpnews_options',
    ]);

    $wp_customize->add_setting('wpnews_hero_posts_count', [
        'default'           => 4,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('wpnews_hero_posts_count', [
        'label'   => __('Hero Sidebar Posts Count', 'wpnews'),
        'section' => 'wpnews_homepage',
        'type'    => 'number',
        'input_attrs' => ['min' => 2, 'max' => 6],
    ]);

    $wp_customize->add_setting('wpnews_show_video_section', [
        'default'           => true,
        'sanitize_callback' => 'wpnews_sanitize_checkbox',
    ]);

    $wp_customize->add_control('wpnews_show_video_section', [
        'label'   => __('Show Video Section', 'wpnews'),
        'section' => 'wpnews_homepage',
        'type'    => 'checkbox',
    ]);

    $wp_customize->add_setting('wpnews_show_gallery_section', [
        'default'           => true,
        'sanitize_callback' => 'wpnews_sanitize_checkbox',
    ]);

    $wp_customize->add_control('wpnews_show_gallery_section', [
        'label'   => __('Show Gallery Section', 'wpnews'),
        'section' => 'wpnews_homepage',
        'type'    => 'checkbox',
    ]);

    // ==========================================
    // SECTION: Social Links
    // ==========================================
    $wp_customize->add_section('wpnews_social', [
        'title' => __('Social Links', 'wpnews'),
        'panel' => 'wpnews_options',
    ]);

    $socials = ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin'];

    foreach ($socials as $social) {
        $wp_customize->add_setting("wpnews_social_{$social}", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);

        $wp_customize->add_control("wpnews_social_{$social}", [
            'label'   => ucfirst($social) . ' URL',
            'section' => 'wpnews_social',
            'type'    => 'url',
        ]);
    }

    // ==========================================
    // SECTION: Footer
    // ==========================================
    $wp_customize->add_section('wpnews_footer', [
        'title' => __('Footer Settings', 'wpnews'),
        'panel' => 'wpnews_options',
    ]);

    $wp_customize->add_setting('wpnews_footer_text', [
        'default'           => 'All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('wpnews_footer_text', [
        'label'   => __('Footer Copyright Text', 'wpnews'),
        'section' => 'wpnews_footer',
        'type'    => 'text',
    ]);
}
add_action('customize_register', 'wpnews_customize_register');

// Sanitize checkbox
function wpnews_sanitize_checkbox($checked) {
    return (bool) $checked;
}

// ==========================================
// OUTPUT CUSTOM CSS
// ==========================================
function wpnews_custom_css_output() {
    $primary = get_theme_mod('wpnews_primary_color', '#DC2626');
    $secondary = get_theme_mod('wpnews_secondary_color', '#1E3A5F');

    if ($primary !== '#DC2626' || $secondary !== '#1E3A5F') :
?>
    <style id="wpnews-custom-css">
        :root {
            --color-primary: <?php echo esc_attr($primary); ?>;
            --color-secondary: <?php echo esc_attr($secondary); ?>;
        }
    </style>
<?php
    endif;
}
add_action('wp_head', 'wpnews_custom_css_output', 50);