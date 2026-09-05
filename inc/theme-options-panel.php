<?php
/**
 * Hikmah News Theme Options Panel
 * - Full admin page (not just Customizer)
 * - Tabbed interface with 15 sections
 * - Save/Load with options API
 * - Import/Export settings
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. REGISTER ADMIN PAGE
// ============================================
function hikmahnews_options_admin_menu() {
    add_theme_page(
        'Hikmah News Options',
        '⚙️ Theme Options',
        'manage_options',
        'hikmahnews-options',
        'hikmahnews_options_page_render'
    );
}
add_action('admin_menu', 'hikmahnews_options_admin_menu');

// ============================================
// 2. OPTIONS PAGE RENDER
// ============================================
function hikmahnews_options_page_render() {
    // Save handler
    if (isset($_POST['hikmahnews_options_nonce']) &&
        wp_verify_nonce($_POST['hikmahnews_options_nonce'], 'hikmahnews_options_save')) {
        hikmahnews_save_options($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>✅ Settings saved successfully!</p></div>';
    }

    // Import handler
    if (isset($_POST['hikmahnews_import_nonce']) &&
        wp_verify_nonce($_POST['hikmahnews_import_nonce'], 'hikmahnews_import')) {
        hikmahnews_import_options($_POST['hikmahnews_import_data']);
        echo '<div class="notice notice-success is-dismissible"><p>✅ Settings imported!</p></div>';
    }

    $options = get_option('hikmahnews_theme_options', hikmahnews_default_options());
    $tabs = hikmahnews_options_tabs();
    $active_tab = $_GET['tab'] ?? 'general';
    if (!isset($tabs[$active_tab])) $active_tab = 'general';
    $is_special_tab = ($active_tab === 'import_export');
    ?>
    <div class="wrap hikmahnews-options-wrap">
        <h1>⚙️ Hikmah News Theme Options</h1>
        <p class="description">Configure your news portal. Changes are saved per section.</p>

        <div class="hikmahnews-options-container">
            <!-- Sidebar Tabs -->
            <nav class="hikmahnews-options-nav">
                <?php foreach ($tabs as $tab_key => $tab_data) : ?>
                    <a href="?page=hikmahnews-options&tab=<?php echo $tab_key; ?>"
                       class="hikmahnews-options-nav__item <?php echo $active_tab === $tab_key ? 'hikmahnews-options-nav__item--active' : ''; ?>">
                        <span class="hikmahnews-options-nav__icon"><?php echo $tab_data['icon']; ?></span>
                        <span class="hikmahnews-options-nav__label"><?php echo $tab_data['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Content Area -->
            <div class="hikmahnews-options-content">
                <?php if (!$is_special_tab) : ?>
                <form method="POST" action="">
                    <?php wp_nonce_field('hikmahnews_options_save', 'hikmahnews_options_nonce'); ?>
                    <input type="hidden" name="hikmahnews_active_tab" value="<?php echo esc_attr($active_tab); ?>">

                    <h2><?php echo $tabs[$active_tab]['icon']; ?> <?php echo $tabs[$active_tab]['label']; ?></h2>
                    <p class="description"><?php echo $tabs[$active_tab]['description']; ?></p>

                    <table class="form-table hikmahnews-form-table">
                        <?php
                        $callback = 'hikmahnews_options_tab_' . $active_tab;
                        if (function_exists($callback)) {
                            $callback($options);
                        } else {
                            echo '<tr><td>Settings coming soon...</td></tr>';
                        }
                        ?>
                    </table>

                    <?php submit_button('💾 Save ' . $tabs[$active_tab]['label'] . ' Settings'); ?>
                </form>
                <?php else : ?>
                    <h2><?php echo $tabs[$active_tab]['icon']; ?> <?php echo $tabs[$active_tab]['label']; ?></h2>
                    <p class="description"><?php echo $tabs[$active_tab]['description']; ?></p>

                    <table class="form-table hikmahnews-form-table">
                        <?php
                        $callback = 'hikmahnews_options_tab_' . $active_tab;
                        if (function_exists($callback)) {
                            $callback($options);
                        }
                        ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .hikmahnews-options-wrap { max-width: 1200px; }
        .hikmahnews-options-container { display: flex; gap: 20px; margin-top: 20px; }
        .hikmahnews-options-nav {
            width: 220px; flex-shrink: 0;
            background: #fff; border: 1px solid #ddd; border-radius: 6px;
            overflow: hidden; position: sticky; top: 40px; align-self: flex-start;
        }
        .hikmahnews-options-nav__item {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 15px; color: #555; text-decoration: none;
            border-bottom: 1px solid #f0f0f0; font-size: 13px;
            transition: all 0.15s;
        }
        .hikmahnews-options-nav__item:hover { background: #f8f9fa; color: #222; }
        .hikmahnews-options-nav__item--active {
            background: #dc2626; color: #fff; font-weight: 600;
        }
        .hikmahnews-options-nav__icon { font-size: 16px; }
        .hikmahnews-options-content {
            flex: 1; background: #fff; border: 1px solid #ddd;
            border-radius: 6px; padding: 20px 25px;
        }
        .hikmahnews-form-table th { width: 200px; font-weight: 600; }
        .hikmahnews-form-table td { max-width: 600px; }
        .hikmahnews-section-divider {
            border: none; border-top: 2px solid #f0f0f0; margin: 20px 0;
        }
        .hikmahnews-field-group {
            background: #f9f9f9; padding: 15px; border-radius: 6px;
            margin-bottom: 15px; border: 1px solid #eee;
        }
        .hikmahnews-field-group h4 { margin: 0 0 10px; color: #333; }
    </style>
    <?php
}

// ============================================
// 3. TABS DEFINITION
// ============================================
function hikmahnews_options_tabs() {
    return [
        'general'      => ['icon' => '⚙️', 'label' => 'General',          'description' => 'Site-wide settings, logo, favicon, and basic configuration.'],
        'header'       => ['icon' => '🔝', 'label' => 'Header',           'description' => 'Top bar, navigation, sticky header, and search settings.'],
        'homepage'     => ['icon' => '🏠', 'label' => 'Homepage',         'description' => 'Homepage layout, sections order, and content display.'],
        'breaking'     => ['icon' => '🔴', 'label' => 'Breaking News',    'description' => 'Breaking news ticker, alert bar, and auto-expiry.'],
        'typography'   => ['icon' => '🔤', 'label' => 'Typography',       'description' => 'Font families, sizes, weights, and line heights.'],
        'colors'       => ['icon' => '🎨', 'label' => 'Colors',           'description' => 'Primary, secondary, accent colors and dark mode.'],
        'layout'       => ['icon' => '📐', 'label' => 'Layout',           'description' => 'Container width, grid columns, sidebar position.'],
        'sidebar'      => ['icon' => '📌', 'label' => 'Sidebar',          'description' => 'Sidebar visibility, widgets, and sticky behavior.'],
        'advertisement'=> ['icon' => '💰', 'label' => 'Advertisement',    'description' => 'Ad positions, codes, and display rules.'],
        'social'       => ['icon' => '🌐', 'label' => 'Social Media',     'description' => 'Social profile links and share button settings.'],
        'footer'       => ['icon' => '🔻', 'label' => 'Footer',           'description' => 'Footer layout, widgets, copyright, and back-to-top.'],
        'newsletter'   => ['icon' => '📬', 'label' => 'Newsletter',       'description' => 'Newsletter integration and popup settings.'],
        'seo'          => ['icon' => '🔍', 'label' => 'SEO',              'description' => 'Meta tags, schema, Open Graph, and sitemap.'],
        'performance'  => ['icon' => '⚡', 'label' => 'Performance',      'description' => 'Caching, CDN, lazy loading, and Core Web Vitals.'],
        'updates'      => ['icon' => '🔄', 'label' => 'Updates',          'description' => 'Theme update source, auto-update, backup, and version management.'],
        'import_export'=> ['icon' => '📦', 'label' => 'Import / Export',  'description' => 'Backup and restore theme settings.'],
    ];
}

// ============================================
// 4. DEFAULT OPTIONS
// ============================================
function hikmahnews_default_options() {
    return [
        'general' => [
            'site_tagline'     => '',
            'logo_width'       => 200,
            'favicon'          => '',
            'date_format'      => 'relative',
            'reading_time'     => '1',
            'post_views'       => '1',
            'breadcrumb'       => '1',
            'back_to_top'      => '1',
        ],
        'header' => [
            'topbar_enabled'   => '1',
            'topbar_date'      => '1',
            'topbar_social'    => '1',
            'sticky_nav'       => '1',
            'search_style'     => 'overlay',
            'header_ad'        => '0',
        ],
        'homepage' => [
            'sections'         => 'hero,latest,politics,business,sports,technology,trending,newsletter',
            'posts_per_section'=> 6,
            'hero_style'       => 'split',
            'show_spotlight'   => '1',
        ],
        'breaking' => [
            'ticker_enabled'   => '1',
            'alert_enabled'    => '1',
            'ticker_speed'     => '40',
            'auto_expiry'      => '24',
        ],
        'typography' => [
            'heading_font'     => 'Merriweather',
            'body_font'        => 'Inter',
            'base_size'        => '16',
            'heading_weight'   => '700',
            'line_height'      => '1.6',
        ],
        'colors' => [
            'primary'          => '#DC2626',
            'secondary'        => '#1E3A5F',
            'accent'           => '#F59E0B',
            'dark_mode'        => 'auto',
            'dark_default'     => '0',
        ],
        'layout' => [
            'container_width'  => '1280',
            'grid_columns'     => '3',
            'sidebar_position' => 'right',
            'card_style'       => 'default',
        ],
        'sidebar' => [
            'show_on_home'     => '0',
            'show_on_single'   => '1',
            'show_on_category' => '1',
            'sticky_sidebar'   => '1',
        ],
        'advertisement' => [
            'enabled'          => '1',
            'adsense_id'       => '',
            'in_content'       => '1',
            'ad_frequency'     => '3',
        ],
        'social' => [
            'facebook'         => '',
            'twitter'          => '',
            'youtube'          => '',
            'instagram'        => '',
            'linkedin'         => '',
            'share_buttons'    => '1',
            'share_style'      => 'icons',
        ],
        'footer' => [
            'columns'          => '4',
            'copyright'        => 'All rights reserved.',
            'back_to_top'      => '1',
            'footer_ad'        => '0',
        ],
        'newsletter' => [
            'provider'         => 'mailchimp',
            'api_key'          => '',
            'list_id'          => '',
            'popup_enabled'    => '0',
            'popup_delay'      => '30',
        ],
        'seo' => [
            'meta_title'       => '1',
            'meta_description' => '1',
            'schema'           => '1',
            'open_graph'       => '1',
            'twitter_cards'    => '1',
            'canonical'        => '1',
            'noindex_archives' => '1',
        ],
        'performance' => [
            'lazy_load'        => '1',
            'webp'             => '1',
            'critical_css'     => '1',
            'defer_js'         => '1',
            'cache_ttl'        => '3600',
            'cdn_url'          => '',
            'debug_vitals'     => '0',
            'minify_html'      => '0',
        ],
        'updates' => [
            'source'         => 'github',
            'github_repo'    => '',
            'api_url'        => '',
            'check_interval' => '12',
            'auto_update'    => '0',
            'backup_before'  => '1',
            'email_notify'   => '0',
        ],
    ];
}

// ============================================
// 5. SAVE OPTIONS
// ============================================
function hikmahnews_save_options($post_data) {
    $options = get_option('hikmahnews_theme_options', hikmahnews_default_options());
    $tab = sanitize_text_field($post_data['hikmahnews_active_tab'] ?? 'general');

    if ($tab === 'import_export') return;

    $defaults = hikmahnews_default_options();
    if (!isset($defaults[$tab])) return;

    foreach ($defaults[$tab] as $key => $default) {
        $field_name = "hikmahnews_{$tab}_{$key}";
        if (isset($post_data[$field_name])) {
            $options[$tab][$key] = sanitize_text_field($post_data[$field_name]);
        } else {
            // Checkbox: if not in POST, it's unchecked
            if ($default === '1' || $default === '0') {
                $options[$tab][$key] = '0';
            }
        }
    }

    update_option('hikmahnews_theme_options', $options);

    // Save separate GitHub token (stored independently, not in options array)
    if ($tab === 'updates' && isset($post_data['hikmahnews_github_token'])) {
        update_option('hikmahnews_github_token', sanitize_text_field($post_data['hikmahnews_github_token']));
    }

    // Regenerate critical CSS if performance settings changed
    if ($tab === 'performance') {
        delete_option('hikmahnews_critical_css');
    }

    // Clear caches
    if (class_exists('HikmahNews_Cache_Compat')) {
        $cache = new HikmahNews_Cache_Compat();
        $cache->purge_cache();
    }
}

// ============================================
// 6. HELPER: Get Option Value
// ============================================
function hikmahnews_option($section, $key, $default = '') {
    $options = get_option('hikmahnews_theme_options', []);
    return $options[$section][$key] ?? $default;
}

function hikmahnews_checkbox($section, $key) {
    return hikmahnews_option($section, $key) === '1';
}