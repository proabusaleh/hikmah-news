<?php
/**
 * Theme Options — Updates Tab
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

function hikmahnews_options_tab_updates($options) {
    $o = $options['updates'] ?? [];
    $current = HIKMAHNEWS_VERSION;
    $remote = get_transient('hikmahnews_update_info');
    $has_update = $remote && version_compare($remote['version'], $current, '>');
    $installed_version = get_option('hikmahnews_installed_version', $current);

    $status_color = $has_update ? '#F59E0B' : '#10B981';
    $status_text = $has_update ? 'Update Available' : 'Up to Date';
    ?>

    <tr>
        <th>Current Version</th>
        <td>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:24px;font-weight:bold;color:#111;">
                    v<?php echo esc_html($current); ?>
                </span>
                <span style="background:<?php echo esc_attr($status_color); ?>;color:white;
                             padding:4px 12px;border-radius:20px;font-size:12px;font-weight:bold;">
                    <?php echo esc_html($status_text); ?>
                </span>
            </div>
            <?php if ($has_update) : ?>
                <p style="margin-top:8px;color:#D97706;font-weight:bold;">
                    Version <strong>v<?php echo esc_html($remote['version']); ?></strong> is available!
                    <?php if (!empty($remote['published'])) : ?>
                        <span style="color:#999;font-weight:normal;">
                            (Released <?php echo human_time_diff(strtotime($remote['published']), current_time('timestamp')); ?> ago)
                        </span>
                    <?php endif; ?>
                </p>
                <a href="<?php echo admin_url('update-core.php'); ?>" class="button button-primary"
                   style="margin-top:8px;">
                    Update to v<?php echo esc_html($remote['version']); ?>
                </a>
            <?php endif; ?>
            <p class="description" style="margin-top:8px;">
                Installed since: v<?php echo esc_html($installed_version); ?>
            </p>
        </td>
    </tr>

    <tr><th colspan="2"><hr class="hikmahnews-section-divider"></th></tr>

    <tr>
        <th>Update Source</th>
        <td>
            <select name="hikmahnews_updates_source" id="hikmahnewsUpdateSource">
                <option value="github" <?php selected($o['source'] ?? 'github', 'github'); ?>>
                    GitHub Releases
                </option>
                <option value="custom" <?php selected($o['source'] ?? '', 'custom'); ?>>
                    Custom API Server
                </option>
                <option value="wordpress" <?php selected($o['source'] ?? '', 'wordpress'); ?>>
                    WordPress.org (if listed)
                </option>
                <option value="manual" <?php selected($o['source'] ?? '', 'manual'); ?>>
                    Manual Only (no auto-check)
                </option>
            </select>
            <p class="description">Where to check for theme updates.</p>
        </td>
    </tr>

    <tr class="hikmahnews-update-field hikmahnews-update-field--github">
        <th>GitHub Repository</th>
        <td>
            <input type="text" name="hikmahnews_updates_github_repo" class="regular-text"
                   value="<?php echo esc_attr($o['github_repo'] ?? ''); ?>"
                   placeholder="username/repo-name">
            <p class="description">
                Format: <code>owner/repository</code>. Uses GitHub Releases API.
                <br>Create a release with a <code>.zip</code> asset for auto-update.
            </p>
            <?php if (!empty($o['github_repo']) && strpos($o['github_repo'], '/') !== false) : ?>
                <p style="margin-top:8px;">
                    <a href="<?php echo esc_url('https://github.com/' . $o['github_repo']); ?>"
                       class="button button-secondary" target="_blank" rel="noopener">
                        Open Repository
                    </a>
                    <a href="<?php echo esc_url('https://github.com/' . $o['github_repo'] . '/releases'); ?>"
                       class="button button-secondary" target="_blank" rel="noopener">
                        View Releases
                    </a>
                </p>
            <?php endif; ?>
        </td>
    </tr>

    <tr class="hikmahnews-update-field hikmahnews-update-field--github">
        <th>GitHub Token (Optional)</th>
        <td>
            <input type="password" name="hikmahnews_github_token" class="regular-text"
                   value="<?php echo esc_attr(get_option('hikmahnews_github_token', '')); ?>"
                   placeholder="ghp_xxxxxxxxxxxx">
            <p class="description">
                Personal Access Token for private repos or higher rate limits.
            </p>
        </td>
    </tr>

    <tr class="hikmahnews-update-field hikmahnews-update-field--custom" style="display:none;">
        <th>Custom API URL</th>
        <td>
            <input type="url" name="hikmahnews_updates_api_url" class="large-text"
                   value="<?php echo esc_attr($o['api_url'] ?? ''); ?>"
                   placeholder="https://api.yourserver.com/theme-update">
            <p class="description">
                Your server should return JSON:
                <code>{"version":"2.1.0","download_url":"...","changelog":"..."}</code>
            </p>
        </td>
    </tr>

    <tr><th colspan="2"><hr class="hikmahnews-section-divider"></th></tr>

    <tr>
        <th>Check Interval</th>
        <td>
            <select name="hikmahnews_updates_check_interval">
                <option value="6" <?php selected($o['check_interval'] ?? 12, '6'); ?>>
                    Every 6 hours
                </option>
                <option value="12" <?php selected($o['check_interval'] ?? 12, '12'); ?>>
                    Every 12 hours (Recommended)
                </option>
                <option value="24" <?php selected($o['check_interval'] ?? 12, '24'); ?>>
                    Once daily
                </option>
                <option value="72" <?php selected($o['check_interval'] ?? 12, '72'); ?>>
                    Every 3 days
                </option>
            </select>
        </td>
    </tr>

    <tr>
        <th>Auto Update</th>
        <td>
            <label>
                <input type="checkbox" name="hikmahnews_updates_auto_update" value="1"
                       <?php checked($o['auto_update'] ?? '0', '1'); ?>>
                Automatically install minor updates (e.g., 2.0.1 &rarr; 2.0.2)
            </label>
            <p class="description">
                Major updates (e.g., 2.0 &rarr; 3.0) always require manual confirmation.
            </p>
        </td>
    </tr>

    <tr>
        <th>Backup Before Update</th>
        <td>
            <label>
                <input type="checkbox" name="hikmahnews_updates_backup_before" value="1"
                       <?php checked($o['backup_before'] ?? '1', '1'); ?>>
                Create a settings backup before applying updates
            </label>
            <p class="description">
                Backs up theme options to database. Can be restored from Import/Export tab.
            </p>
        </td>
    </tr>

    <tr>
        <th>Email on Update</th>
        <td>
            <label>
                <input type="checkbox" name="hikmahnews_updates_email_notify" value="1"
                       <?php checked($o['email_notify'] ?? '0', '1'); ?>>
                Send email notification when an update is available
            </label>
            <p class="description">Sends to: <code><?php echo get_option('admin_email'); ?></code></p>
        </td>
    </tr>

    <tr><th colspan="2"><hr class="hikmahnews-section-divider"></th></tr>

    <tr>
        <th>Manual Check</th>
        <td>
            <a href="<?php echo wp_nonce_url(admin_url('themes.php?page=hikmahnews-options&tab=updates&hikmahnews_check_update=1'), 'hikmahnews_check_update'); ?>"
               class="button button-secondary">
                Check for Updates Now
            </a>
            <button type="button" class="button" id="hikmahnewsAjaxCheck" style="margin-left:8px;">
                Quick Check (AJAX)
            </button>
            <span id="hikmahnewsCheckResult" style="margin-left:12px;font-weight:bold;"></span>

            <?php if (isset($_GET['checked'])) : ?>
                <p style="color:#059669;font-weight:bold;margin-top:8px;">
                    &#x2705; Update check completed.
                </p>
            <?php endif; ?>
        </td>
    </tr>

    <tr>
        <th>Migration Status</th>
        <td>
            <div class="hikmahnews-field-group" style="background:#F0FDF4;border-color:#BBF7D0;">
                <h4>&#x2705; Database Migrations</h4>
                <p>Installed version: <strong>v<?php echo esc_html($installed_version); ?></strong></p>
                <p>Current version: <strong>v<?php echo esc_html($current); ?></strong></p>
                <?php if (version_compare($installed_version, $current, '<')) : ?>
                    <p style="color:#D97706;">
                        Migrations pending. They will run automatically on next page load.
                    </p>
                <?php else : ?>
                    <p style="color:#059669;">All migrations are up to date.</p>
                <?php endif; ?>
            </div>
        </td>
    </tr>

    <script>
    jQuery(document).ready(function($) {
        $('#hikmahnewsUpdateSource').on('change', function() {
            var val = $(this).val();
            $('.hikmahnews-update-field').hide();
            $('.hikmahnews-update-field--' + val).show();
        }).trigger('change');

        $('#hikmahnewsAjaxCheck').on('click', function() {
            var btn = $(this);
            var result = $('#hikmahnewsCheckResult');
            btn.prop('disabled', true).text('Checking...');
            result.text('');

            $.post(ajaxurl, {
                action: 'hikmahnews_check_update',
                nonce: '<?php echo wp_create_nonce("hikmahnews_nonce"); ?>'
            }, function(response) {
                btn.prop('disabled', false).text('Quick Check (AJAX)');
                if (response.success) {
                    var data = response.data;
                    if (data.update_available) {
                        result.html('<span style="color:#D97706;">v' + data.new_version + ' available!</span>');
                    } else {
                        result.html('<span style="color:#059669;">' + data.message + '</span>');
                    }
                } else {
                    result.html('<span style="color:#DC2626;">Check failed.</span>');
                }
            }).fail(function() {
                btn.prop('disabled', false).text('Quick Check (AJAX)');
                result.html('<span style="color:#DC2626;">Network error.</span>');
            });
        });
    });
    </script>
    <?php
}

function hikmahnews_auto_update_filter($update, $item) {
    if (!isset($item->type) || $item->type !== 'theme') return $update;

    $theme = wp_get_theme();
    if ($item->theme !== $theme->get_stylesheet()) return $update;

    $auto = hikmahnews_option('updates', 'auto_update', '0');
    if ($auto !== '1') return $update;

    $current_parts = explode('.', HIKMAHNEWS_VERSION);
    $new_parts = explode('.', $item->new_version);

    if ($current_parts[0] === $new_parts[0] && $current_parts[1] === $new_parts[1]) {
        return true;
    }

    return false;
}
add_filter('auto_update_theme', 'hikmahnews_auto_update_filter', 10, 2);

function hikmahnews_backup_before_update($reply, $package, $upgrader) {
    $backup = hikmahnews_option('updates', 'backup_before', '1');
    if ($backup !== '1') return $reply;

    $options = get_option('hikmahnews_theme_options', []);
    if (!empty($options)) {
        $backup_key = 'hikmahnews_backup_' . date('Y-m-d_H-i-s');
        update_option($backup_key, $options);

        global $wpdb;
        $backups = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name LIKE 'hikmahnews_backup_%'
             ORDER BY option_name DESC"
        );

        if (count($backups) > 3) {
            $to_delete = array_slice($backups, 3);
            foreach ($to_delete as $key) {
                delete_option($key);
            }
        }
    }

    return $reply;
}
add_filter('upgrader_pre_download', 'hikmahnews_backup_before_update', 5, 3);

function hikmahnews_update_email_notification() {
    $notify = hikmahnews_option('updates', 'email_notify', '0');
    if ($notify !== '1') return;

    $remote = get_transient('hikmahnews_update_info');
    if (!$remote || version_compare($remote['version'], HIKMAHNEWS_VERSION, '<=')) return;

    $notified = get_transient('hikmahnews_update_email_sent');
    if ($notified) return;

    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');

    wp_mail(
        $admin_email,
        "[{$site_name}] Hikmah News Theme Update Available — v{$remote['version']}",
        "Hello,\n\n" .
        "A new version of Hikmah News Theme is available.\n\n" .
        "Current: v" . HIKMAHNEWS_VERSION . "\n" .
        "New: v{$remote['version']}\n\n" .
        "Update now: " . admin_url('update-core.php') . "\n\n" .
        "— Hikmah News Theme"
    );

    set_transient('hikmahnews_update_email_sent', true, WEEK_IN_SECONDS);
}
add_action('admin_init', 'hikmahnews_update_email_notification');
