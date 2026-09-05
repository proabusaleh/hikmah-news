<?php
/**
 * Category Tabs Widget
 * - AJAX-powered tab switching
 * - Shows Latest / Popular / Trending per category
 * - Perfect for sidebar or homepage sections
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. WIDGET CLASS
// ============================================
class HikmahNews_Category_Tabs_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'hikmahnews_category_tabs',
            '📰 Hikmah News: Category Tabs',
            ['description' => 'Tabbed category news widget (Latest / Popular / Trending)']
        );
    }

    public function widget($args, $instance) {
        $title = $instance['title'] ?? 'Top Stories';
        $categories = $instance['categories'] ?? 'politics,sports,technology';
        $cat_array = array_map('trim', explode(',', $categories));

        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        ?>
        <div class="cat-tabs-widget" data-categories="<?php echo esc_attr($categories); ?>">
            <!-- Tabs -->
            <div class="cat-tabs-widget__nav">
                <?php
                $first = true;
                foreach ($cat_array as $slug) :
                    $cat = get_category_by_slug($slug);
                    if (!$cat) continue;
                ?>
                    <button class="cat-tabs-widget__tab <?php echo $first ? 'cat-tabs-widget__tab--active' : ''; ?>"
                            data-slug="<?php echo esc_attr($slug); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </button>
                <?php
                    $first = false;
                endforeach;
                ?>
            </div>

            <!-- Sub-tabs: Latest / Popular / Trending -->
            <div class="cat-tabs-widget__subnav">
                <button class="cat-tabs-widget__subtab cat-tabs-widget__subtab--active" data-sort="latest">
                    🕒 Latest
                </button>
                <button class="cat-tabs-widget__subtab" data-sort="popular">
                    👁 Popular
                </button>
                <button class="cat-tabs-widget__subtab" data-sort="trending">
                    🔥 Trending
                </button>
            </div>

            <!-- Content -->
            <div class="cat-tabs-widget__content" id="catTabsContent">
                <?php echo hikmahnews_cat_tabs_content($cat_array[0] ?? 'politics', 'latest'); ?>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = $instance['title'] ?? 'Top Stories';
        $categories = $instance['categories'] ?? 'politics,sports,technology';
        ?>
        <p>
            <label>Title:</label>
            <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label>Categories (comma-separated slugs):</label>
            <input class="widefat" name="<?php echo $this->get_field_name('categories'); ?>"
                   value="<?php echo esc_attr($categories); ?>">
            <small>Example: politics,sports,technology</small>
        </p>
        <?php
    }

    public function update($new, $old) {
        return [
            'title'      => sanitize_text_field($new['title']),
            'categories' => sanitize_text_field($new['categories']),
        ];
    }
}

// ============================================
// 2. AJAX TAB CONTENT
// ============================================
function hikmahnews_cat_tabs_content($slug, $sort = 'latest') {
    $args = [
        'category_name'  => $slug,
        'posts_per_page' => 5,
        'no_found_rows'  => true,
    ];

    switch ($sort) {
        case 'popular':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_hikmahnews_views';
            break;
        case 'trending':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_hikmahnews_trending_score';
            $args['date_query'] = [['after' => '3 days ago']];
            break;
        default:
            $args['orderby'] = 'date';
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) :
        echo '<div class="cat-tabs-widget__list">';
        while ($query->have_posts()) : $query->the_post();
        ?>
            <article class="cat-tab-item">
                <div class="cat-tab-item__image">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-thumb'); ?>
                    </a>
                </div>
                <div class="cat-tab-item__content">
                    <h4 class="cat-tab-item__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h4>
                    <div class="cat-tab-item__meta">
                        <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                        <span class="dot"></span>
                        <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                    </div>
                </div>
            </article>
        <?php
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    else :
        echo '<p class="cat-tabs-widget__empty">No posts found.</p>';
    endif;

    return ob_get_clean();
}

function hikmahnews_cat_tabs_ajax() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $slug = sanitize_text_field($_POST['slug'] ?? '');
    $sort = sanitize_text_field($_POST['sort'] ?? 'latest');

    if (!$slug) wp_send_json_error('No category');

    wp_send_json_success([
        'html' => hikmahnews_cat_tabs_content($slug, $sort),
    ]);
}
add_action('wp_ajax_hikmahnews_cat_tabs', 'hikmahnews_cat_tabs_ajax');
add_action('wp_ajax_nopriv_hikmahnews_cat_tabs', 'hikmahnews_cat_tabs_ajax');

// ============================================
// 3. TAB JAVASCRIPT
// ============================================
function hikmahnews_cat_tabs_script() {
    wp_add_inline_script('hikmahnews-main', '
    (function() {
        document.addEventListener("click", function(e) {
            var tab = e.target.closest(".cat-tabs-widget__tab, .cat-tabs-widget__subtab");
            if (!tab) return;

            var widget = tab.closest(".cat-tabs-widget");
            var content = widget.querySelector(".cat-tabs-widget__content");
            var isMain = tab.classList.contains("cat-tabs-widget__tab");

            // Update active state
            var siblings = tab.parentElement.querySelectorAll(
                isMain ? ".cat-tabs-widget__tab" : ".cat-tabs-widget__subtab"
            );
            siblings.forEach(function(s) {
                s.classList.remove(
                    isMain ? "cat-tabs-widget__tab--active" : "cat-tabs-widget__subtab--active"
                );
            });
            tab.classList.add(
                isMain ? "cat-tabs-widget__tab--active" : "cat-tabs-widget__subtab--active"
            );

            // Get current state
            var activeMain = widget.querySelector(".cat-tabs-widget__tab--active");
            var activeSub = widget.querySelector(".cat-tabs-widget__subtab--active");
            var slug = activeMain ? activeMain.dataset.slug : "";
            var sort = activeSub ? activeSub.dataset.sort : "latest";

            if (!slug) return;

            // Loading state
            content.style.opacity = "0.4";

            fetch(hikmahnews_ajax.ajax_url, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=hikmahnews_cat_tabs&nonce=" + hikmahnews_ajax.nonce + "&slug=" + slug + "&sort=" + sort
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    content.innerHTML = data.data.html;
                    content.style.opacity = "1";
                }
            });
        });
    })();
    ');
}
add_action('wp_enqueue_scripts', 'hikmahnews_cat_tabs_script');

// ============================================
// 4. REGISTER WIDGET
// ============================================
function hikmahnews_register_widgets() {
    register_widget('HikmahNews_Category_Tabs_Widget');
}
add_action('widgets_init', 'hikmahnews_register_widgets');