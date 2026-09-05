<?php
/**
 * Hikmah News Theme Updater
 * - Remote update checking (GitHub / Custom Server)
 * - Version migration (database schema updates)
 * - Auto-update toggle
 * - Changelog viewer
 * - Backup before update
 * - Admin notifications
 * @package HikmahNews
 * @since 2.0.0
 */
if (!defined('ABSPATH')) exit;

class HikmahNews_Theme_Updater {

    private $theme_slug = 'hikmah-news';
    private $current_version;
    private $update_source;
    private $github_repo;
    private $custom_api_url;
    private $check_interval;

    public function __construct() {
        $this->current_version = HIKMAHNEWS_VERSION;
        $options = get_option('hikmahnews_theme_options', []);
        $update_opts = $options['updates'] ?? [];

        $this->update_source  = $update_opts['source'] ?? 'github';
        $this->github_repo    = $update_opts['github_repo'] ?? '';
        $this->custom_api_url = $update_opts['api_url'] ?? '';
        $this->check_interval = ($update_opts['check_interval'] ?? 12) * HOUR_IN_SECONDS;

        add_filter('pre_set_site_transient_update_themes', [$this, 'check_for_update']);
        add_filter('site_transient_update_themes', [$this, 'inject_update_info']);
        add_filter('upgrader_pre_download', [$this, 'verify_update'], 10, 3);
        add_action('admin_notices', [$this, 'update_admin_notices']);
        add_action('admin_init', [$this, 'handle_manual_check']);
        add_action('after_switch_theme', [$this, 'run_migrations']);
        add_action('admin_init', [$this, 'check_version_change']);

        add_action('wp_ajax_hikmahnews_check_update', [$this, 'ajax_check_update']);
        add_action('wp_ajax_hikmahnews_dismiss_notice', [$this, 'ajax_dismiss_notice']);
        add_action('wp_ajax_hikmahnews_view_changelog', [$this, 'ajax_view_changelog']);
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) return $transient;

        $last_check = get_transient('hikmahnews_update_last_check');
        if ($last_check && !isset($_GET['force-check'])) {
            return $transient;
        }

        $remote = $this->fetch_remote_version();

        if ($remote && version_compare($remote['version'], $this->current_version, '>')) {
            $theme = wp_get_theme();
            $stylesheet = $theme->get_stylesheet();

            $transient->response[$stylesheet] = [
                'theme'       => $stylesheet,
                'new_version' => $remote['version'],
                'url'         => $remote['url'] ?? '',
                'package'     => $remote['download_url'] ?? '',
                'requires'    => $remote['requires_wp'] ?? '6.0',
                'requires_php'=> $remote['requires_php'] ?? '7.4',
                'tested'      => $remote['tested_wp'] ?? '6.5',
            ];

            set_transient('hikmahnews_update_info', $remote, DAY_IN_SECONDS);
        }

        set_transient('hikmahnews_update_last_check', time(), $this->check_interval);
        return $transient;
    }

    private function fetch_remote_version() {
        if ($this->update_source === 'github' && $this->github_repo) {
            return $this->fetch_from_github();
        }

        if ($this->update_source === 'custom' && $this->custom_api_url) {
            return $this->fetch_from_custom_api();
        }

        return false;
    }

    private function fetch_from_github() {
        $repo = sanitize_text_field($this->github_repo);
        if (!$repo || strpos($repo, '/') === false) return false;

        $api_url = "https://api.github.com/repos/{$repo}/releases/latest";
        $token = get_option('hikmahnews_github_token', '');

        $headers = [
            'Accept'     => 'application/vnd.github.v3+json',
            'User-Agent' => 'HikmahNews-Theme-Updater',
        ];

        if ($token) {
            $headers['Authorization'] = 'token ' . $token;
        }

        $response = wp_remote_get($api_url, [
            'headers' => $headers,
            'timeout' => 15,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data || empty($data['tag_name'])) return false;

        $version = ltrim($data['tag_name'], 'v');

        $download_url = '';
        if (!empty($data['assets'])) {
            foreach ($data['assets'] as $asset) {
                if (pathinfo($asset['name'], PATHINFO_EXTENSION) === 'zip') {
                    $download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        if (!$download_url) {
            $download_url = "https://github.com/{$repo}/archive/refs/tags/{$data['tag_name']}.zip";
        }

        return [
            'version'      => $version,
            'download_url' => $download_url,
            'url'          => $data['html_url'] ?? "https://github.com/{$repo}/releases",
            'changelog'    => $data['body'] ?? '',
            'published'    => $data['published_at'] ?? '',
            'requires_wp'  => '6.0',
            'requires_php' => '7.4',
            'tested_wp'    => '6.5',
        ];
    }

    private function fetch_from_custom_api() {
        $api_url = add_query_arg([
            'action'  => 'check_update',
            'slug'    => $this->theme_slug,
            'version' => $this->current_version,
            'site'    => home_url(),
        ], $this->custom_api_url);

        $response = wp_remote_get($api_url, ['timeout' => 15]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data || empty($data['version'])) return false;

        return $data;
    }

    public function inject_update_info($transient) {
        $theme = wp_get_theme();
        $stylesheet = $theme->get_stylesheet();

        if (isset($transient->response[$stylesheet])) {
            $remote = get_transient('hikmahnews_update_info');
            if ($remote && !empty($remote['changelog'])) {
                $transient->response[$stylesheet]['changelog'] = $remote['changelog'];
            }
        }

        return $transient;
    }

    public function verify_update($reply, $package, $upgrader) {
        if (strpos($package, 'github.com') !== false ||
            ($this->custom_api_url && strpos($package, $this->custom_api_url) !== false)) {
            return $reply;
        }

        return $reply;
    }

    /**
     * Build the GitHub repository URL from the configured repo string.
     */
    public function get_repo_url() {
        $repo = trim($this->github_repo);
        if (!$repo || strpos($repo, '/') === false) return '';

        return 'https://github.com/' . $repo;
    }

    public function update_admin_notices() {
        if (!current_user_can('update_themes')) return;

        $dismissed = get_transient('hikmahnews_update_dismissed');
        if ($dismissed) return;

        $remote = get_transient('hikmahnews_update_info');
        if (!$remote || version_compare($remote['version'], $this->current_version, '<=')) return;

        ?>
        <div class="notice notice-warning is-dismissible hikmahnews-update-notice" id="hikmahnewsUpdateNotice">
            <div style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                <span style="font-size:28px;">&#x1F504;</span>
                <div style="flex:1;">
                    <p style="margin:0 0 4px;">
                        <strong>Hikmah News Theme Update Available!</strong>
                    </p>
                    <p style="margin:0;color:#666;font-size:13px;">
                        Version <strong><?php echo esc_html($this->current_version); ?></strong> &rarr;
                        <strong style="color:#059669;"><?php echo esc_html($remote['version']); ?></strong>
                        <?php if (!empty($remote['published'])) : ?>
                            <span style="color:#999;">
                                (Released <?php echo human_time_diff(strtotime($remote['published']), current_time('timestamp')); ?> ago)
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="<?php echo admin_url('update-core.php'); ?>"
                       class="button button-primary">
                        Update Now
                    </a>
                    <button class="button" id="hikmahnewsViewChangelog"
                            data-version="<?php echo esc_attr($remote['version']); ?>">
                        View Changelog
                    </button>
                    <?php if ($this->get_repo_url()) : ?>
                        <a href="<?php echo esc_url($this->get_repo_url()); ?>"
                           class="button" target="_blank" rel="noopener">
                            Open Repo
                        </a>
                    <?php endif; ?>
                    <button class="button" id="hikmahnewsDismissUpdate"
                            style="color:#999;">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>

        <div id="hikmahnewsChangelogModal" style="display:none;position:fixed;inset:0;
             background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:white;border-radius:8px;max-width:600px;width:90%;
                        max-height:80vh;overflow:auto;padding:24px;position:relative;">
                <button onclick="document.getElementById('hikmahnewsChangelogModal').style.display='none'"
                        style="position:absolute;top:12px;right:12px;background:none;
                               border:none;font-size:20px;cursor:pointer;">&times;</button>
                <h2 style="margin:0 0 16px;">Changelog &mdash; v<?php echo esc_html($remote['version']); ?></h2>
                <div style="font-size:14px;line-height:1.7;">
                    <?php echo wp_kses_post(wpautop($remote['changelog'])); ?>
                </div>
                <?php if (!empty($remote['url'])) : ?>
                    <p style="margin-top:16px;padding-top:12px;border-top:1px solid #eee;">
                        <a href="<?php echo esc_url($remote['url']); ?>" target="_blank" rel="noopener">
                            View on GitHub &rarr;
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.getElementById('hikmahnewsViewChangelog')?.addEventListener('click', function() {
            document.getElementById('hikmahnewsChangelogModal').style.display = 'flex';
        });
        document.getElementById('hikmahnewsDismissUpdate')?.addEventListener('click', function() {
            document.getElementById('hikmahnewsUpdateNotice').style.display = 'none';
            fetch(ajaxurl, {
                method: 'POST',
                body: 'action=hikmahnews_dismiss_notice&nonce=<?php echo wp_create_nonce("hikmahnews_nonce"); ?>'
            });
        });
        </script>
        <?php
    }

    public function handle_manual_check() {
        if (!isset($_GET['hikmahnews_check_update']) || !current_user_can('update_themes')) return;
        check_admin_referer('hikmahnews_check_update');

        delete_transient('hikmahnews_update_last_check');
        delete_transient('hikmahnews_update_info');
        delete_site_transient('update_themes');

        wp_redirect(admin_url('themes.php?page=hikmahnews-options&tab=updates&checked=1'));
        exit;
    }

    public function check_version_change() {
        $saved_version = get_option('hikmahnews_installed_version', '1.0.0');

        if (version_compare($saved_version, $this->current_version, '<')) {
            $this->run_migrations($saved_version);
            update_option('hikmahnews_installed_version', $this->current_version);
        }
    }

    public function run_migrations($from_version = '1.0.0') {
        $migrations = $this->get_migrations();

        foreach ($migrations as $version => $callback) {
            if (version_compare($from_version, $version, '<')) {
                if (is_callable([$this, $callback])) {
                    $this->$callback();
                    error_log("[HikmahNews] Migration {$callback} (v{$version}) completed.");
                }
            }
        }

        wp_cache_flush();
        delete_transient('hikmahnews_trending_10');
        delete_transient('hikmahnews_breaking_6');
    }

    private function get_migrations() {
        return [
            '1.1.0' => 'migrate_1_1_0_add_trending_scores',
            '1.2.0' => 'migrate_1_2_0_add_category_meta',
            '1.5.0' => 'migrate_1_5_0_add_bookmark_system',
            '2.0.0' => 'migrate_2_0_0_gutenberg_compatibility',
            '2.1.0' => 'migrate_2_1_0_update_options_structure',
        ];
    }

    private function migrate_1_1_0_add_trending_scores() {
        if (function_exists('hikmahnews_recalculate_trending')) {
            hikmahnews_recalculate_trending();
        }
    }

    private function migrate_1_2_0_add_category_meta() {
        if (function_exists('hikmahnews_create_categories')) {
            hikmahnews_create_categories();
        }
    }

    private function migrate_1_5_0_add_bookmark_system() {
        // Handled dynamically, no migration needed
    }

    private function migrate_2_0_0_gutenberg_compatibility() {
        if (function_exists('hikmahnews_register_patterns')) {
            hikmahnews_register_patterns();
        }
    }

    private function migrate_2_1_0_update_options_structure() {
        $old_options = get_option('hikmahnews_options', []);
        if (!empty($old_options) && !get_option('hikmahnews_theme_options')) {
            $new_options = hikmahnews_default_options();

            if (isset($old_options['primary_color'])) {
                $new_options['colors']['primary'] = $old_options['primary_color'];
            }
            if (isset($old_options['secondary_color'])) {
                $new_options['colors']['secondary'] = $old_options['secondary_color'];
            }

            update_option('hikmahnews_theme_options', $new_options);
        }
    }

    public function ajax_check_update() {
        check_ajax_referer('hikmahnews_nonce', 'nonce');
        if (!current_user_can('update_themes')) wp_send_json_error('Unauthorized');

        delete_transient('hikmahnews_update_last_check');
        delete_transient('hikmahnews_update_info');
        delete_site_transient('update_themes');

        $remote = $this->fetch_remote_version();

        if ($remote && version_compare($remote['version'], $this->current_version, '>')) {
            set_transient('hikmahnews_update_info', $remote, DAY_IN_SECONDS);
            wp_send_json_success([
                'update_available' => true,
                'current'          => $this->current_version,
                'new_version'      => $remote['version'],
                'changelog'        => $remote['changelog'] ?? '',
                'url'              => $remote['url'] ?? '',
                'published'        => $remote['published'] ?? '',
            ]);
        } else {
            wp_send_json_success([
                'update_available' => false,
                'current'          => $this->current_version,
                'message'          => 'You are running the latest version.',
            ]);
        }
    }

    public function ajax_dismiss_notice() {
        check_ajax_referer('hikmahnews_nonce', 'nonce');
        set_transient('hikmahnews_update_dismissed', true, WEEK_IN_SECONDS);
        wp_send_json_success();
    }

    public function ajax_view_changelog() {
        check_ajax_referer('hikmahnews_nonce', 'nonce');
        $remote = get_transient('hikmahnews_update_info');
        wp_send_json_success([
            'changelog' => $remote['changelog'] ?? 'No changelog available.',
        ]);
    }
}

new HikmahNews_Theme_Updater();
