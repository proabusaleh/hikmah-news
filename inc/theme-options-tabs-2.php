<?php
/**
 * Theme Options Tabs — Part 2
 * Sidebar, Advertisement, Social, Footer, Newsletter, SEO, Performance, Import/Export
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// SIDEBAR
// ============================================
function hikmahnews_options_tab_sidebar($options) {
    $o = $options['sidebar'] ?? [];
    ?>
    <tr>
        <th>Show on Homepage</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_sidebar_show_on_home" value="1"
                   <?php checked($o['show_on_home'] ?? '0', '1'); ?>>
                   Show sidebar on homepage</label>
        </td>
    </tr>
    <tr>
        <th>Show on Single Posts</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_sidebar_show_on_single" value="1"
                   <?php checked($o['show_on_single'] ?? '1', '1'); ?>>
                   Show sidebar on article pages</label>
        </td>
    </tr>
    <tr>
        <th>Show on Category</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_sidebar_show_on_category" value="1"
                   <?php checked($o['show_on_category'] ?? '1', '1'); ?>>
                   Show sidebar on category archives</label>
        </td>
    </tr>
    <tr>
        <th>Sticky Sidebar</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_sidebar_sticky_sidebar" value="1"
                   <?php checked($o['sticky_sidebar'] ?? '1', '1'); ?>>
                   Make sidebar cards sticky on scroll</label>
        </td>
    </tr>
    <?php
}

// ============================================
// ADVERTISEMENT
// ============================================
function hikmahnews_options_tab_advertisement($options) {
    $o = $options['advertisement'] ?? [];
    ?>
    <tr>
        <th>Enable Ads</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_advertisement_enabled" value="1"
                   <?php checked($o['enabled'] ?? '1', '1'); ?>>
                   Enable advertisement system</label>
        </td>
    </tr>
    <tr>
        <th>AdSense Publisher ID</th>
        <td>
            <input type="text" name="hikmahnews_advertisement_adsense_id" class="regular-text"
                   value="<?php echo esc_attr($o['adsense_id'] ?? ''); ?>"
                   placeholder="ca-pub-XXXXXXXXXXXXXXXX">
            <p class="description">Used for auto ads. Leave empty to use managed ad slots.</p>
        </td>
    </tr>
    <tr>
        <th>In-Content Ads</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_advertisement_in_content" value="1"
                   <?php checked($o['in_content'] ?? '1', '1'); ?>>
                   Insert ads inside article content</label>
        </td>
    </tr>
    <tr>
        <th>Ad Frequency</th>
        <td>
            <select name="hikmahnews_advertisement_ad_frequency">
                <option value="2" <?php selected($o['ad_frequency'] ?? '', '2'); ?>>Every 2 paragraphs</option>
                <option value="3" <?php selected($o['ad_frequency'] ?? '', '3'); ?>>Every 3 paragraphs</option>
                <option value="5" <?php selected($o['ad_frequency'] ?? '', '5'); ?>>Every 5 paragraphs</option>
            </select>
            <p class="description">How often to insert in-content ads.</p>
        </td>
    </tr>
    <?php
}

// ============================================
// SOCIAL MEDIA
// ============================================
function hikmahnews_options_tab_social($options) {
    $o = $options['social'] ?? [];
    ?>
    <tr>
        <th>Facebook URL</th>
        <td>
            <input type="url" name="hikmahnews_social_facebook" class="regular-text"
                   value="<?php echo esc_attr($o['facebook'] ?? ''); ?>" placeholder="https://facebook.com/...">
        </td>
    </tr>
    <tr>
        <th>Twitter / X URL</th>
        <td>
            <input type="url" name="hikmahnews_social_twitter" class="regular-text"
                   value="<?php echo esc_attr($o['twitter'] ?? ''); ?>" placeholder="https://twitter.com/...">
        </td>
    </tr>
    <tr>
        <th>YouTube URL</th>
        <td>
            <input type="url" name="hikmahnews_social_youtube" class="regular-text"
                   value="<?php echo esc_attr($o['youtube'] ?? ''); ?>" placeholder="https://youtube.com/...">
        </td>
    </tr>
    <tr>
        <th>Instagram URL</th>
        <td>
            <input type="url" name="hikmahnews_social_instagram" class="regular-text"
                   value="<?php echo esc_attr($o['instagram'] ?? ''); ?>" placeholder="https://instagram.com/...">
        </td>
    </tr>
    <tr>
        <th>LinkedIn URL</th>
        <td>
            <input type="url" name="hikmahnews_social_linkedin" class="regular-text"
                   value="<?php echo esc_attr($o['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/...">
        </td>
    </tr>
    <tr>
        <th>Share Buttons</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_social_share_buttons" value="1"
                   <?php checked($o['share_buttons'] ?? '1', '1'); ?>>
                   Show share buttons on single posts</label>
        </td>
    </tr>
    <tr>
        <th>Share Button Style</th>
        <td>
            <select name="hikmahnews_social_share_style">
                <option value="icons" <?php selected($o['share_style'] ?? '', 'icons'); ?>>Icons</option>
                <option value="buttons" <?php selected($o['share_style'] ?? '', 'buttons'); ?>>Labeled Buttons</option>
                <option value="floating" <?php selected($o['share_style'] ?? '', 'floating'); ?>>Floating Sidebar</option>
            </select>
        </td>
    </tr>
    <?php
}

// ============================================
// FOOTER
// ============================================
function hikmahnews_options_tab_footer($options) {
    $o = $options['footer'] ?? [];
    ?>
    <tr>
        <th>Columns</th>
        <td>
            <select name="hikmahnews_footer_columns">
                <option value="3" <?php selected($o['columns'] ?? '', '3'); ?>>3 Columns</option>
                <option value="4" <?php selected($o['columns'] ?? '', '4'); ?>>4 Columns</option>
                <option value="2" <?php selected($o['columns'] ?? '', '2'); ?>>2 Columns</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Copyright Text</th>
        <td>
            <input type="text" name="hikmahnews_footer_copyright" class="regular-text"
                   value="<?php echo esc_attr($o['copyright'] ?? ''); ?>"
                   placeholder="© 2025 All rights reserved. {{year}} {{sitename}}">
            <p class="description">Available placeholders: {{year}}, {{sitename}}, {{sitedescription}}.</p>
        </td>
    </tr>
    <tr>
        <th>Footer Back to Top</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_footer_back_to_top" value="1"
                   <?php checked($o['back_to_top'] ?? '1', '1'); ?>>
                   Show back-to-top in footer</label>
        </td>
    </tr>
    <tr>
        <th>Footer Ad Space</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_footer_footer_ad" value="1"
                   <?php checked($o['footer_ad'] ?? '0', '1'); ?>>
                   Show ad banner above footer</label>
        </td>
    </tr>
    <?php
}

// ============================================
// NEWSLETTER
// ============================================
function hikmahnews_options_tab_newsletter($options) {
    $o = $options['newsletter'] ?? [];
    ?>
    <tr>
        <th>Provider</th>
        <td>
            <select name="hikmahnews_newsletter_provider">
                <option value="mailchimp" <?php selected($o['provider'] ?? '', 'mailchimp'); ?>>
                    Mailchimp
                </option>
                <option value="mailpoet" <?php selected($o['provider'] ?? '', 'mailpoet'); ?>>
                    MailPoet (Plugin)
                </option>
                <option value="mailerlite" <?php selected($o['provider'] ?? '', 'mailerlite'); ?>>
                    MailerLite
                </option>
                <option value="custom" <?php selected($o['provider'] ?? '', 'custom'); ?>>
                    Custom Form HTML
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th>API Key</th>
        <td>
            <input type="text" name="hikmahnews_newsletter_api_key" class="regular-text"
                   value="<?php echo esc_attr($o['api_key'] ?? ''); ?>">
            <p class="description">For Mailchimp/MailerLite API integration.</p>
        </td>
    </tr>
    <tr>
        <th>List / Audience ID</th>
        <td>
            <input type="text" name="hikmahnews_newsletter_list_id" class="regular-text"
                   value="<?php echo esc_attr($o['list_id'] ?? ''); ?>">
        </td>
    </tr>
    <tr>
        <th>Popup Subscription</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_newsletter_popup_enabled" value="1"
                   <?php checked($o['popup_enabled'] ?? '0', '1'); ?>>
                   Show newsletter popup</label>
        </td>
    </tr>
    <tr>
        <th>Popup Delay (seconds)</th>
        <td>
            <input type="number" name="hikmahnews_newsletter_popup_delay" style="width:80px;"
                   value="<?php echo esc_attr($o['popup_delay'] ?? 30); ?>" min="5" max="120">
        </td>
    </tr>
    <?php
}

// ============================================
// SEO
// ============================================
function hikmahnews_options_tab_seo($options) {
    $o = $options['seo'] ?? [];
    ?>
    <tr>
        <th>Meta Title</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_meta_title" value="1"
                   <?php checked($o['meta_title'] ?? '1', '1'); ?>>
                   Auto-generate meta titles</label>
        </td>
    </tr>
    <tr>
        <th>Meta Description</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_meta_description" value="1"
                   <?php checked($o['meta_description'] ?? '1', '1'); ?>>
                   Auto-generate meta descriptions</label>
        </td>
    </tr>
    <tr>
        <th>Schema Markup</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_schema" value="1"
                   <?php checked($o['schema'] ?? '1', '1'); ?>>
                   Enable JSON-LD schema</label>
        </td>
    </tr>
    <tr>
        <th>Open Graph</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_open_graph" value="1"
                   <?php checked($o['open_graph'] ?? '1', '1'); ?>>
                   Enable Open Graph tags</label>
        </td>
    </tr>
    <tr>
        <th>Twitter Cards</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_twitter_cards" value="1"
                   <?php checked($o['twitter_cards'] ?? '1', '1'); ?>>
                   Enable Twitter card tags</label>
        </td>
    </tr>
    <tr>
        <th>Canonical URLs</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_canonical" value="1"
                   <?php checked($o['canonical'] ?? '1', '1'); ?>>
                   Output canonical URLs</label>
        </td>
    </tr>
    <tr>
        <th>Noindex Archived</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_seo_noindex_archives" value="1"
                   <?php checked($o['noindex_archives'] ?? '1', '1'); ?>>
                   Noindex date/tag/taxonomy archives</label>
        </td>
    </tr>
    <?php
}

// ============================================
// PERFORMANCE
// ============================================
function hikmahnews_options_tab_performance($options) {
    $o = $options['performance'] ?? [];
    ?>
    <tr>
        <th>Lazy Loading</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_lazy_load" value="1"
                   <?php checked($o['lazy_load'] ?? '1', '1'); ?>>
                   Enable smart lazy loading</label>
        </td>
    </tr>
    <tr>
        <th>WebP Serving</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_webp" value="1"
                   <?php checked($o['webp'] ?? '1', '1'); ?>>
                   Serve WebP images when supported</label>
        </td>
    </tr>
    <tr>
        <th>Critical CSS</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_critical_css" value="1"
                   <?php checked($o['critical_css'] ?? '1', '1'); ?>>
                   Inline critical CSS</label>
        </td>
    </tr>
    <tr>
        <th>Defer Javascript</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_defer_js" value="1"
                   <?php checked($o['defer_js'] ?? '1', '1'); ?>>
                   Defer non-critical JavaScript</label>
        </td>
    </tr>
    <tr>
        <th>Cache TTL (seconds)</th>
        <td>
            <input type="number" name="hikmahnews_performance_cache_ttl" style="width:100px;"
                   value="<?php echo esc_attr($o['cache_ttl'] ?? 3600); ?>" min="300" max="86400">
        </td>
    </tr>
    <tr>
        <th>CDN URL</th>
        <td>
            <input type="url" name="hikmahnews_performance_cdn_url" class="regular-text"
                   value="<?php echo esc_attr($o['cdn_url'] ?? ''); ?>"
                   placeholder="https://cdn.example.com">
            <p class="description">Rewrite site assets through your CDN. Leave empty to disable.</p>
        </td>
    </tr>
    <tr>
        <th>Debug Web Vitals</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_debug_vitals" value="1"
                   <?php checked($o['debug_vitals'] ?? '0', '1'); ?>>
                   Log Core Web Vitals to console (dev only)</label>
        </td>
    </tr>
    <tr>
        <th>Minify HTML</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_performance_minify_html" value="1"
                   <?php checked($o['minify_html'] ?? '0', '1'); ?>>
                   Minify HTML output</label>
        </td>
    </tr>
    <?php
}

// ============================================
// IMPORT / EXPORT
// ============================================
function hikmahnews_options_tab_import_export($options) {
    ?>
    <tr>
        <th>Export Settings</th>
        <td>
            <p class="description">Download all theme settings as JSON backup.</p>
            <textarea class="large-text hikmahnews-export-area" rows="6" readonly
                      onclick="this.select();"><?php echo esc_textarea(wp_json_encode($options, JSON_PRETTY_PRINT)); ?></textarea>
            <p>
                <button type="button" class="button" onclick="hikmahnewsDownloadSettings();">
                    ⬇️ Download JSON
                </button>
            </p>
        </td>
    </tr>
    <tr>
        <th>Import Settings</th>
        <td>
            <p class="description">Paste JSON backup to restore settings.</p>
            <form method="POST" action="">
                <?php wp_nonce_field('hikmahnews_import', 'hikmahnews_import_nonce'); ?>
                <textarea name="hikmahnews_import_data" class="large-text" rows="6"
                          placeholder='{"general": {...}, "colors": {...}}'></textarea>
                <p>
                    <button type="submit" class="button button-primary">📦 Import Settings</button>
                </p>
            </form>
        </td>
    </tr>
    <tr>
        <th>Reset Options</th>
        <td>
            <p class="description">Restore all settings to theme defaults.</p>
            <form method="POST" action="">
                <?php wp_nonce_field('hikmahnews_reset', 'hikmahnews_reset_nonce'); ?>
                <button type="submit" class="button button-link-delete"
                        onclick="return confirm('This will reset ALL theme settings. Continue?');">
                    🗑️ Reset to Defaults
                </button>
            </form>
        </td>
    </tr>
    <?php
}

// ============================================
// IMPORT / RESET HANDLERS
// ============================================
function hikmahnews_import_options($json_data) {
    $data = json_decode(stripslashes($json_data), true);
    if (!is_array($data)) return;

    $defaults = hikmahnews_default_options();
    $clean = [];

    foreach ($defaults as $section => $fields) {
        foreach ($fields as $key => $default) {
            if (isset($data[$section][$key])) {
                $clean[$section][$key] = sanitize_text_field($data[$section][$key]);
            } else {
                $clean[$section][$key] = $default;
            }
        }
    }

    update_option('hikmahnews_theme_options', $clean);
}

function hikmahnews_reset_options() {
    update_option('hikmahnews_theme_options', hikmahnews_default_options());
}

// Handle reset
if (isset($_POST['hikmahnews_reset_nonce']) &&
    wp_verify_nonce($_POST['hikmahnews_reset_nonce'], 'hikmahnews_reset')) {
    hikmahnews_reset_options();
}

// ============================================
// DYNAMIC CSS FROM OPTIONS
// ============================================
function hikmahnews_dynamic_css_from_options() {
    $options = get_option('hikmahnews_theme_options', hikmahnews_default_options());

    $primary = esc_html($options['colors']['primary'] ?? '#DC2626');
    $secondary = esc_html($options['colors']['secondary'] ?? '#1E3A5F');
    $accent = esc_html($options['colors']['accent'] ?? '#F59E0B');
    $container = esc_html($options['layout']['container_width'] ?? '1280');
    $heading_font = esc_html($options['typography']['heading_font'] ?? 'Merriweather');
    $body_font = esc_html($options['typography']['body_font'] ?? 'Inter');
    $base_size = esc_html($options['typography']['base_size'] ?? '16');
    $line_height = esc_html($options['typography']['line_height'] ?? '1.6');
    $heading_weight = esc_html($options['typography']['heading_weight'] ?? '700');

    $css = "
        :root {
            --hikmahnews-primary: {$primary};
            --hikmahnews-secondary: {$secondary};
            --hikmahnews-accent: {$accent};
            --hikmahnews-container: {$container}px;
            --hikmahnews-heading-font: '{$heading_font}', Georgia, serif;
            --hikmahnews-body-font: '{$body_font}', -apple-system, sans-serif;
            --hikmahnews-base-size: {$base_size}px;
            --hikmahnews-line-height: {$line_height};
            --hikmahnews-heading-weight: {$heading_weight};
        }
    ";

    echo '<style id="hikmahnews-dynamic-css">' . $css . '</style>' . "\n";
}
add_action('wp_head', 'hikmahnews_dynamic_css_from_options', 3);