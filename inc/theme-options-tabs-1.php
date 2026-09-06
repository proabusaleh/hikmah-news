<?php
/**
 * Theme Options Tabs — Part 1
 * General, Header, Homepage, Breaking, Typography, Colors, Layout
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// GENERAL
// ============================================
function hikmahnews_options_tab_general($options) {
    $o = $options['general'] ?? [];
    ?>
    <tr>
        <th>Site Tagline</th>
        <td>
            <input type="text" name="hikmahnews_general_site_tagline" class="regular-text"
                   value="<?php echo esc_attr($o['site_tagline'] ?? ''); ?>"
                   placeholder="Your trusted news source">
            <p class="description">Displayed in header and SEO meta tags.</p>
        </td>
    </tr>
    <tr>
        <th>Logo Width (px)</th>
        <td>
            <input type="number" name="hikmahnews_general_logo_width" style="width:80px;"
                   value="<?php echo esc_attr($o['logo_width'] ?? 200); ?>" min="100" max="500">
        </td>
    </tr>
    <tr>
        <th>Date Format</th>
        <td>
            <select name="hikmahnews_general_date_format">
                <option value="relative" <?php selected($o['date_format'] ?? '', 'relative'); ?>>
                    Relative (2 hours ago)
                </option>
                <option value="absolute" <?php selected($o['date_format'] ?? '', 'absolute'); ?>>
                    Absolute (Jan 15, 2025)
                </option>
                <option value="both" <?php selected($o['date_format'] ?? '', 'both'); ?>>
                    Both (Jan 15 · 2h ago)
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Show Reading Time</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_general_reading_time" value="1"
                   <?php checked($o['reading_time'] ?? '1', '1'); ?>>
                   Display reading time on posts</label>
        </td>
    </tr>
    <tr>
        <th>Show Post Views</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_general_post_views" value="1"
                   <?php checked($o['post_views'] ?? '1', '1'); ?>>
                   Display view count on posts</label>
        </td>
    </tr>
    <tr>
        <th>Show Breadcrumb</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_general_breadcrumb" value="1"
                   <?php checked($o['breadcrumb'] ?? '1', '1'); ?>>
                   Show breadcrumb on single posts</label>
        </td>
    </tr>
    <tr>
        <th>Back to Top Button</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_general_back_to_top" value="1"
                   <?php checked($o['back_to_top'] ?? '1', '1'); ?>>
                   Show scroll-to-top button</label>
        </td>
    </tr>
    <tr>
        <th>Design Style</th>
        <td>
            <select name="hikmahnews_general_design_style">
                <option value="modern" <?php selected($o['design_style'] ?? 'modern', 'modern'); ?>>
                    ✨ Modern (Bento + Glassmorphism)
                </option>
                <option value="classic" <?php selected($o['design_style'] ?? '', 'classic'); ?>>
                    📰 Classic (Traditional News)
                </option>
                <option value="minimal" <?php selected($o['design_style'] ?? '', 'minimal'); ?>>
                    ◻️ Minimal (Clean Editorial)
                </option>
            </select>
            <p class="description">Choose the overall design language for your site.</p>
        </td>
    </tr>
    <?php
}

// ============================================
// HEADER
// ============================================
function hikmahnews_options_tab_header($options) {
    $o = $options['header'] ?? [];
    ?>
    <tr>
        <th>Top Bar</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_header_topbar_enabled" value="1"
                   <?php checked($o['topbar_enabled'] ?? '1', '1'); ?>>
                   Enable top bar (date, ticker, social)</label>
        </td>
    </tr>
    <tr>
        <th>Show Date in Top Bar</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_header_topbar_date" value="1"
                   <?php checked($o['topbar_date'] ?? '1', '1'); ?>>
                   Display current date</label>
        </td>
    </tr>
    <tr>
        <th>Show Social in Top Bar</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_header_topbar_social" value="1"
                   <?php checked($o['topbar_social'] ?? '1', '1'); ?>>
                   Display social icons</label>
        </td>
    </tr>
    <tr>
        <th>Sticky Navigation</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_header_sticky_nav" value="1"
                   <?php checked($o['sticky_nav'] ?? '1', '1'); ?>>
                   Make main nav sticky on scroll</label>
        </td>
    </tr>
    <tr>
        <th>Search Style</th>
        <td>
            <select name="hikmahnews_header_search_style">
                <option value="overlay" <?php selected($o['search_style'] ?? '', 'overlay'); ?>>
                    Full-screen Overlay
                </option>
                <option value="dropdown" <?php selected($o['search_style'] ?? '', 'dropdown'); ?>>
                    Dropdown
                </option>
                <option value="inline" <?php selected($o['search_style'] ?? '', 'inline'); ?>>
                    Inline (always visible)
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Header Ad Space</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_header_header_ad" value="1"
                   <?php checked($o['header_ad'] ?? '0', '1'); ?>>
                   Show ad banner in header</label>
        </td>
    </tr>
    <tr>
        <th>Extra Navbar Links</th>
        <td>
            <textarea name="hikmahnews_header_extra_nav_links" class="large-text" rows="5"
                      placeholder="About | https://example.com/about&#10;Contact | https://example.com/contact"><?php
                echo esc_textarea($o['extra_nav_links'] ?? '');
            ?></textarea>
            <p class="description">
                Dynamically add links to the navbar. One per line, format:
                <code>Label | URL</code>. Links are appended to the primary menu
                (desktop + mobile).
            </p>
        </td>
    </tr>
    <?php
}

// ============================================
// HOMEPAGE
// ============================================
function hikmahnews_options_tab_homepage($options) {
    $o = $options['homepage'] ?? [];
    ?>
    <tr>
        <th>Section Order</th>
        <td>
            <input type="text" name="hikmahnews_homepage_sections" class="large-text"
                   value="<?php echo esc_attr($o['sections'] ?? ''); ?>">
            <p class="description">
                Comma-separated. Available: hero, latest, politics, business, sports,
                technology, entertainment, health, opinion, trending, video, gallery,
                spotlight, newsletter
            </p>
        </td>
    </tr>
    <tr>
        <th>Posts Per Section</th>
        <td>
            <input type="number" name="hikmahnews_homepage_posts_per_section" style="width:80px;"
                   value="<?php echo esc_attr($o['posts_per_section'] ?? 6); ?>" min="3" max="12">
        </td>
    </tr>
    <tr>
        <th>Hero Style</th>
        <td>
            <select name="hikmahnews_homepage_hero_style">
                <option value="split" <?php selected($o['hero_style'] ?? '', 'split'); ?>>
                    Split (Large + Sidebar)
                </option>
                <option value="full" <?php selected($o['hero_style'] ?? '', 'full'); ?>>
                    Full Width
                </option>
                <option value="slider" <?php selected($o['hero_style'] ?? '', 'slider'); ?>>
                    Slider
                </option>
                <option value="grid" <?php selected($o['hero_style'] ?? '', 'grid'); ?>>
                    2-Column Grid
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Show Spotlight</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_homepage_show_spotlight" value="1"
                   <?php checked($o['show_spotlight'] ?? '1', '1'); ?>>
                   Enable spotlight section</label>
        </td>
    </tr>
    <?php
}

// ============================================
// BREAKING NEWS
// ============================================
function hikmahnews_options_tab_breaking($options) {
    $o = $options['breaking'] ?? [];
    ?>
    <tr>
        <th>Breaking Ticker</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_breaking_ticker_enabled" value="1"
                   <?php checked($o['ticker_enabled'] ?? '1', '1'); ?>>
                   Show scrolling breaking news ticker</label>
        </td>
    </tr>
    <tr>
        <th>Breaking Alert Bar</th>
        <td>
            <label><input type="checkbox" name="hikmahnews_breaking_alert_enabled" value="1"
                   <?php checked($o['alert_enabled'] ?? '1', '1'); ?>>
                   Show alert bar for urgent breaking news</label>
        </td>
    </tr>
    <tr>
        <th>Ticker Speed (seconds)</th>
        <td>
            <input type="number" name="hikmahnews_breaking_ticker_speed" style="width:80px;"
                   value="<?php echo esc_attr($o['ticker_speed'] ?? 40); ?>" min="10" max="120">
            <p class="description">Lower = faster. Default: 40s.</p>
        </td>
    </tr>
    <tr>
        <th>Auto-Expiry (hours)</th>
        <td>
            <input type="number" name="hikmahnews_breaking_auto_expiry" style="width:80px;"
                   value="<?php echo esc_attr($o['auto_expiry'] ?? 24); ?>" min="1" max="168">
            <p class="description">Breaking posts auto-expire after this many hours. 0 = manual only.</p>
        </td>
    </tr>
    <?php
}

// ============================================
// TYPOGRAPHY
// ============================================
function hikmahnews_options_tab_typography($options) {
    $o = $options['typography'] ?? [];
    $fonts = ['Inter', 'Merriweather', 'Roboto', 'Open Sans', 'Lato', 'Nunito',
              'Playfair Display', 'Source Serif Pro', 'Poppins', 'Montserrat'];
    ?>
    <tr>
        <th>Heading Font</th>
        <td>
            <select name="hikmahnews_typography_heading_font">
                <?php foreach ($fonts as $font) : ?>
                    <option value="<?php echo $font; ?>"
                            <?php selected($o['heading_font'] ?? '', $font); ?>>
                        <?php echo $font; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>Body Font</th>
        <td>
            <select name="hikmahnews_typography_body_font">
                <?php foreach ($fonts as $font) : ?>
                    <option value="<?php echo $font; ?>"
                            <?php selected($o['body_font'] ?? '', $font); ?>>
                        <?php echo $font; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>Base Font Size (px)</th>
        <td>
            <input type="number" name="hikmahnews_typography_base_size" style="width:80px;"
                   value="<?php echo esc_attr($o['base_size'] ?? 16); ?>" min="12" max="22">
        </td>
    </tr>
    <tr>
        <th>Heading Weight</th>
        <td>
            <select name="hikmahnews_typography_heading_weight">
                <option value="600" <?php selected($o['heading_weight'] ?? '', '600'); ?>>Semi Bold (600)</option>
                <option value="700" <?php selected($o['heading_weight'] ?? '', '700'); ?>>Bold (700)</option>
                <option value="800" <?php selected($o['heading_weight'] ?? '', '800'); ?>>Extra Bold (800)</option>
                <option value="900" <?php selected($o['heading_weight'] ?? '', '900'); ?>>Black (900)</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Line Height</th>
        <td>
            <input type="number" name="hikmahnews_typography_line_height" style="width:80px;" step="0.1"
                   value="<?php echo esc_attr($o['line_height'] ?? 1.6); ?>" min="1.2" max="2.0">
        </td>
    </tr>
    <?php
}

// ============================================
// COLORS
// ============================================
function hikmahnews_options_tab_colors($options) {
    $o = $options['colors'] ?? [];
    ?>
    <tr>
        <th>Primary Color</th>
        <td>
            <input type="color" name="hikmahnews_colors_primary"
                   value="<?php echo esc_attr($o['primary'] ?? '#DC2626'); ?>">
            <code><?php echo esc_html($o['primary'] ?? '#DC2626'); ?></code>
        </td>
    </tr>
    <tr>
        <th>Secondary Color</th>
        <td>
            <input type="color" name="hikmahnews_colors_secondary"
                   value="<?php echo esc_attr($o['secondary'] ?? '#1E3A5F'); ?>">
            <code><?php echo esc_html($o['secondary'] ?? '#1E3A5F'); ?></code>
        </td>
    </tr>
    <tr>
        <th>Accent Color</th>
        <td>
            <input type="color" name="hikmahnews_colors_accent"
                   value="<?php echo esc_attr($o['accent'] ?? '#F59E0B'); ?>">
            <code><?php echo esc_html($o['accent'] ?? '#F59E0B'); ?></code>
        </td>
    </tr>
    <tr>
        <th>Dark Mode</th>
        <td>
            <select name="hikmahnews_colors_dark_mode">
                <option value="auto" <?php selected($o['dark_mode'] ?? '', 'auto'); ?>>
                    Auto (System Preference)
                </option>
                <option value="manual" <?php selected($o['dark_mode'] ?? '', 'manual'); ?>>
                    Manual (Toggle Button)
                </option>
                <option value="always" <?php selected($o['dark_mode'] ?? '', 'always'); ?>>
                    Always Dark
                </option>
                <option value="never" <?php selected($o['dark_mode'] ?? '', 'never'); ?>>
                    Disabled
                </option>
            </select>
        </td>
    </tr>
    <?php
}

// ============================================
// LAYOUT
// ============================================
function hikmahnews_options_tab_layout($options) {
    $o = $options['layout'] ?? [];
    ?>
    <tr>
        <th>Container Width (px)</th>
        <td>
            <input type="number" name="hikmahnews_layout_container_width" style="width:100px;"
                   value="<?php echo esc_attr($o['container_width'] ?? 1280); ?>" min="960" max="1600">
        </td>
    </tr>
    <tr>
        <th>Grid Columns</th>
        <td>
            <select name="hikmahnews_layout_grid_columns">
                <option value="2" <?php selected($o['grid_columns'] ?? '', '2'); ?>>2 Columns</option>
                <option value="3" <?php selected($o['grid_columns'] ?? '', '3'); ?>>3 Columns</option>
                <option value="4" <?php selected($o['grid_columns'] ?? '', '4'); ?>>4 Columns</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Sidebar Position</th>
        <td>
            <select name="hikmahnews_layout_sidebar_position">
                <option value="right" <?php selected($o['sidebar_position'] ?? '', 'right'); ?>>Right</option>
                <option value="left" <?php selected($o['sidebar_position'] ?? '', 'left'); ?>>Left</option>
                <option value="none" <?php selected($o['sidebar_position'] ?? '', 'none'); ?>>No Sidebar</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>Card Style</th>
        <td>
            <select name="hikmahnews_layout_card_style">
                <option value="default" <?php selected($o['card_style'] ?? '', 'default'); ?>>
                    Default (Rounded)
                </option>
                <option value="flat" <?php selected($o['card_style'] ?? '', 'flat'); ?>>
                    Flat (No Shadow)
                </option>
                <option value="bordered" <?php selected($o['card_style'] ?? '', 'bordered'); ?>>
                    Bordered
                </option>
            </select>
        </td>
    </tr>
    <?php
}