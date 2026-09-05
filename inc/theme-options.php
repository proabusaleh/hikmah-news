<?php
/**
 * Theme Customizer Options
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

function hikmahnews_customize_register($wp_customize) {

    // ==========================================
    // PANEL: Theme Options
    // ==========================================
    $wp_customize->add_panel('hikmahnews_options', [
        'title'    => __('Hikmah News Options', 'hikmahnews'),
        'priority' => 30,
    ]);

    // ==========================================
    // SECTION: Colors
    // ==========================================
    $wp_customize->add_section('hikmahnews_colors', [
        'title' => __('Colors', 'hikmahnews'),
        'panel' => 'hikmahnews_options',
    ]);

    $wp_customize->add_setting('hikmahnews_primary_color', [
        'default'           => '#DC2626',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hikmahnews_primary_color', [
        'label'   => __('Primary Color', 'hikmahnews'),
        'section' => 'hikmahnews_colors',
    ]));

    $wp_customize->add_setting('hikmahnews_secondary_color', [
        'default'           => '#1E3A5F',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hikmahnews_secondary_color', [
        'label'   => __('Secondary Color', 'hikmahnews'),
        'section' => 'hikmahnews_colors',
    ]));

    // ==========================================
    // SECTION: Homepage
    // ==========================================
    $wp_customize->add_section('hikmahnews_homepage', [
        'title' => __('Homepage Settings', 'hikmahnews'),
        'panel' => 'hikmahnews_options',
    ]);

    $wp_customize->add_setting('hikmahnews_hero_posts_count', [
        'default'           => 4,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('hikmahnews_hero_posts_count', [
        'label'   => __('Hero Sidebar Posts Count', 'hikmahnews'),
        'section' => 'hikmahnews_homepage',
        'type'    => 'number',
        'input_attrs' => ['min' => 2, 'max' => 6],
    ]);

    $wp_customize->add_setting('hikmahnews_show_video_section', [
        'default'           => true,
        'sanitize_callback' => 'hikmahnews_sanitize_checkbox',
    ]);

    $wp_customize->add_control('hikmahnews_show_video_section', [
        'label'   => __('Show Video Section', 'hikmahnews'),
        'section' => 'hikmahnews_homepage',
        'type'    => 'checkbox',
    ]);

    $wp_customize->add_setting('hikmahnews_show_gallery_section', [
        'default'           => true,
        'sanitize_callback' => 'hikmahnews_sanitize_checkbox',
    ]);

    $wp_customize->add_control('hikmahnews_show_gallery_section', [
        'label'   => __('Show Gallery Section', 'hikmahnews'),
        'section' => 'hikmahnews_homepage',
        'type'    => 'checkbox',
    ]);

    // ==========================================
    // SECTION: Social Links
    // ==========================================
    $wp_customize->add_section('hikmahnews_social', [
        'title' => __('Social Links', 'hikmahnews'),
        'panel' => 'hikmahnews_options',
    ]);

    $socials = ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin'];

    foreach ($socials as $social) {
        $wp_customize->add_setting("hikmahnews_social_{$social}", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);

        $wp_customize->add_control("hikmahnews_social_{$social}", [
            'label'   => ucfirst($social) . ' URL',
            'section' => 'hikmahnews_social',
            'type'    => 'url',
        ]);
    }

    // ==========================================
    // SECTION: Footer
    // ==========================================
    $wp_customize->add_section('hikmahnews_footer', [
        'title' => __('Footer Settings', 'hikmahnews'),
        'panel' => 'hikmahnews_options',
    ]);

    $wp_customize->add_setting('hikmahnews_footer_text', [
        'default'           => 'All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('hikmahnews_footer_text', [
        'label'   => __('Footer Copyright Text', 'hikmahnews'),
        'section' => 'hikmahnews_footer',
        'type'    => 'text',
    ]);
}
add_action('customize_register', 'hikmahnews_customize_register');

// Sanitize checkbox
function hikmahnews_sanitize_checkbox($checked) {
    return (bool) $checked;
}

// ==========================================
// OUTPUT CUSTOM CSS
// ==========================================
function hikmahnews_custom_css_output() {
    $primary = get_theme_mod('hikmahnews_primary_color', '#DC2626');
    $secondary = get_theme_mod('hikmahnews_secondary_color', '#1E3A5F');

    if ($primary !== '#DC2626' || $secondary !== '#1E3A5F') :
?>
    <style id="hikmahnews-custom-css">
        :root {
            --color-primary: <?php echo esc_attr($primary); ?>;
            --color-secondary: <?php echo esc_attr($secondary); ?>;
        }
    </style>
<?php
    endif;
}
add_action('wp_head', 'hikmahnews_custom_css_output', 50);