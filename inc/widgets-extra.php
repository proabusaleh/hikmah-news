<?php
/**
 * Extra Widgets: Podcast Player, Stock Ticker, Weather
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// 1. Podcast / Audio Player Shortcode
// Usage: [hikmahnews_podcast url="https://.../episode.mp3" title="Episode 12" duration="24:10"]
function hikmahnews_render_podcast_player($atts) {
    $atts = shortcode_atts(['url' => '', 'title' => 'Podcast Episode', 'duration' => ''], $atts);
    if (!$atts['url']) return '';
    return '
    <div class="podcast-player">
        <div class="podcast-player__info">
            <span class="podcast-player__icon">🎙️</span>
            <div>
                <h4 class="podcast-player__title">' . esc_html($atts['title']) . '</h4>
                <span class="podcast-player__duration">' . esc_html($atts['duration']) . '</span>
            </div>
        </div>
        <audio controls preload="metadata" class="podcast-player__audio">
            <source src="' . esc_url($atts['url']) . '" type="audio/mpeg">
        </audio>
    </div>';
}
add_shortcode('hikmahnews_podcast', 'hikmahnews_render_podcast_player');

// 2. Stock Ticker (top of page strip)
function hikmahnews_stock_ticker() {
    if (is_admin()) return;
    $symbols = hikmahnews_option('widgets', 'stock_symbols', 'DSE:BATBC,DSE:GP,NYSE:AAPL,NASDAQ:GOOGL');
    $items = array_values(array_filter(array_map('trim', explode(',', $symbols))));
    if (empty($items)) return;
    ?>
    <div class="stock-ticker" id="stockTicker">
        <div class="stock-ticker__track">
            <?php foreach (array_merge($items, $items) as $sym) : ?>
                <span class="stock-ticker__item">
                    <strong><?php echo esc_html($sym); ?></strong>
                    <span class="stock-ticker__price">—</span>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <style>
        .stock-ticker { background: var(--modern-surface-2, #f5f5f5); border-bottom: 1px solid var(--modern-border, #e5e5e5); overflow: hidden; height: 36px; }
        .stock-ticker__track { display: flex; gap: 32px; animation: modern-ticker 30s linear infinite; white-space: nowrap; padding: 8px 0; }
        .stock-ticker__item { font-size: 12px; color: var(--modern-text-2, #525252); flex-shrink: 0; }
        .stock-ticker__price { font-weight: 700; color: #059669; margin-left: 6px; }
    </style>
    <?php
}
add_action('wp_body_open', 'hikmahnews_stock_ticker', 8);

// 3. Weather Widget (sidebar/widget area)
class HikmahNews_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('hikmahnews_weather', '🌤️ Hikmah News: Weather');
    }

    public function widget($args, $instance) {
        $city = $instance['city'] ?? hikmahnews_option('widgets', 'weather_city', 'Dhaka');
        $api_key = $instance['api_key'] ?? hikmahnews_option('widgets', 'weather_api_key', '');

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . esc_html($instance['title']) . $args['after_title'];
        }
        ?>
        <div class="weather-widget" id="weatherWidget-<?php echo esc_attr($this->id); ?>" data-city="<?php echo esc_attr($city); ?>">
            <span class="weather-widget__icon">🌤️</span>
            <span class="weather-widget__temp">--°</span>
            <span class="weather-widget__city"><?php echo esc_html($city); ?></span>
        </div>
        <style>
            .weather-widget { display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--modern-text-2, #525252); }
            .weather-widget__temp { font-weight: 700; color: var(--modern-text, #0a0a0a); }
        </style>
        <?php if ($api_key) : ?>
        <script>
        (function() {
            var w = document.getElementById('weatherWidget-<?php echo esc_js($this->id); ?>');
            if (!w) return;
            var key = <?php echo wp_json_encode($api_key); ?>;
            fetch('https://api.openweathermap.org/data/2.5/weather?q=' + encodeURIComponent(w.dataset.city) + '&units=metric&appid=' + key)
                .then(r => r.json())
                .then(function(d) {
                    if (d.main) {
                        w.querySelector('.weather-widget__temp').textContent = Math.round(d.main.temp) + '°C';
                        w.querySelector('.weather-widget__icon').textContent = d.weather && d.weather[0] && d.weather[0].icon.includes('01') ? '☀️' : '⛅';
                    }
                }).catch(function() {});
        })();
        </script>
        <?php endif;
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = $instance['title'] ?? '';
        $city = $instance['city'] ?? 'Dhaka';
        $api_key = $instance['api_key'] ?? '';
        ?>
        <p><label>Title:</label><input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>"></p>
        <p><label>City:</label><input class="widefat" name="<?php echo $this->get_field_name('city'); ?>" value="<?php echo esc_attr($city); ?>"></p>
        <p><label>OpenWeatherMap API Key:</label><input class="widefat" name="<?php echo $this->get_field_name('api_key'); ?>" value="<?php echo esc_attr($api_key); ?>" placeholder="optional"></p>
        <?php
    }

    public function update($new, $old) {
        return [
            'title'   => sanitize_text_field($new['title'] ?? ''),
            'city'    => sanitize_text_field($new['city'] ?? 'Dhaka'),
            'api_key' => sanitize_text_field($new['api_key'] ?? ''),
        ];
    }
}

function hikmahnews_register_extra_widgets() {
    register_widget('HikmahNews_Weather_Widget');
}
add_action('widgets_init', 'hikmahnews_register_extra_widgets');