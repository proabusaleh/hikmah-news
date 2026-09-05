<?php
/**
 * AJAX Load More Posts
 * - Infinite scroll OR "Load More" button
 * - Works on homepage, category, archive, search
 * - Respects all query parameters
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. AJAX HANDLER
// ============================================
function hikmahnews_load_more_posts() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $page      = absint($_POST['page'] ?? 1);
    $per_page  = absint($_POST['per_page'] ?? 6);
    $category  = sanitize_text_field($_POST['category'] ?? '');
    $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');
    $orderby   = sanitize_text_field($_POST['orderby'] ?? 'date');
    $search    = sanitize_text_field($_POST['search'] ?? '');
    $layout    = sanitize_text_field($_POST['layout'] ?? 'grid');
    $offset    = absint($_POST['offset'] ?? 0);

    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => $orderby,
        'order'          => 'DESC',
        'post_status'    => 'publish',
        'ignore_sticky_posts' => 1,
    ];

    if ($offset) {
        $args['offset'] = $offset;
    }

    if ($category) {
        $args['category_name'] = $category;
    }

    if ($search) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();

            if ($layout === 'list') :
        ?>
                <article class="news-list-card">
                    <div class="news-list-card__image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-thumb'); ?>
                        </a>
                    </div>
                    <div class="news-list-card__body">
                        <?php
                        $cats = get_the_category();
                        if ($cats) :
                        ?>
                            <span class="hero-side__cat"><?php echo esc_html($cats[0]->name); ?></span>
                        <?php endif; ?>
                        <h4 class="news-list-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <div class="news-card__meta">
                            <span class="author"><?php the_author(); ?></span>
                            <span class="dot"></span>
                            <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                            <span class="dot"></span>
                            <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                        </div>
                    </div>
                </article>
        <?php
            else :
        ?>
                <article class="news-card">
                    <div class="news-card__image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
                        </a>
                        <?php
                        $cats = get_the_category();
                        if ($cats) :
                        ?>
                            <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>"
                               class="badge badge--primary news-card__badge">
                                <?php echo esc_html($cats[0]->name); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="news-card__body">
                        <h3 class="news-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="news-card__excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                        </p>
                        <div class="news-card__meta">
                            <span class="author"><?php the_author(); ?></span>
                            <span class="dot"></span>
                            <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                            <span class="dot"></span>
                            <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                            <?php hikmahnews_bookmark_button(); ?>
                        </div>
                    </div>
                </article>
        <?php
            endif;

        endwhile;
        wp_reset_postdata();
    endif;

    $html = ob_get_clean();

    wp_send_json_success([
        'html'      => $html,
        'max_pages' => $query->max_num_pages,
        'current'   => $page,
        'has_more'  => $page < $query->max_num_pages,
    ]);
}
add_action('wp_ajax_hikmahnews_load_more', 'hikmahnews_load_more_posts');
add_action('wp_ajax_nopriv_hikmahnews_load_more', 'hikmahnews_load_more_posts');

// ============================================
// 2. LOAD MORE BUTTON COMPONENT
// ============================================
function hikmahnews_load_more_button($args = []) {
    $defaults = [
        'max_pages'  => 1,
        'per_page'   => 6,
        'category'   => '',
        'layout'     => 'grid',
        'container'  => '.load-more-container',
        'label'      => 'Load More Articles',
        'loading'    => 'Loading...',
        'offset'     => 0,
    ];
    $args = wp_parse_args($args, $defaults);

    if ($args['max_pages'] <= 1) return;
    ?>
    <div class="load-more-wrapper" data-container="<?php echo esc_attr($args['container']); ?>">
        <button class="btn btn--outline btn--lg load-more-btn"
                data-page="2"
                data-max="<?php echo esc_attr($args['max_pages']); ?>"
                data-per-page="<?php echo esc_attr($args['per_page']); ?>"
                data-category="<?php echo esc_attr($args['category']); ?>"
                data-layout="<?php echo esc_attr($args['layout']); ?>"
                data-offset="<?php echo esc_attr($args['offset']); ?>">
            <span class="load-more-btn__text"><?php echo esc_html($args['label']); ?></span>
            <span class="load-more-btn__loading" style="display:none;">
                <span class="live-search__spinner"></span>
                <?php echo esc_html($args['loading']); ?>
            </span>
        </button>
        <p class="load-more-progress">
            Page <span class="load-more-current">1</span> of
            <span class="load-more-total"><?php echo esc_html($args['max_pages']); ?></span>
        </p>
    </div>
    <?php
}

// ============================================
// 3. LOAD MORE JAVASCRIPT
// ============================================
function hikmahnews_load_more_script() {
    wp_add_inline_script('hikmahnews-main', '
    (function() {
        document.addEventListener("click", function(e) {
            var btn = e.target.closest(".load-more-btn");
            if (!btn || btn.disabled) return;

            var wrapper = btn.closest(".load-more-wrapper");
            var container = document.querySelector(wrapper.dataset.container);
            if (!container) return;

            var page = parseInt(btn.dataset.page);
            var max = parseInt(btn.dataset.max);
            var textEl = btn.querySelector(".load-more-btn__text");
            var loadEl = btn.querySelector(".load-more-btn__loading");

            // Show loading
            btn.disabled = true;
            textEl.style.display = "none";
            loadEl.style.display = "inline-flex";

            var formData = new URLSearchParams({
                action: "hikmahnews_load_more",
                nonce: hikmahnews_ajax.nonce,
                page: page,
                per_page: btn.dataset.perPage,
                category: btn.dataset.category,
                layout: btn.dataset.layout,
                offset: btn.dataset.offset || "0"
            });

            fetch(hikmahnews_ajax.ajax_url, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: formData.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.data.html) {
                    // Animate new items
                    var temp = document.createElement("div");
                    temp.innerHTML = data.data.html;
                    var items = temp.children;

                    Array.from(items).forEach(function(item, i) {
                        item.style.opacity = "0";
                        item.style.transform = "translateY(20px)";
                        container.appendChild(item);

                        setTimeout(function() {
                            item.style.transition = "opacity 0.4s ease, transform 0.4s ease";
                            item.style.opacity = "1";
                            item.style.transform = "translateY(0)";
                        }, i * 80);
                    });

                    // Update state
                    page++;
                    btn.dataset.page = page;
                    var currentEl = wrapper.querySelector(".load-more-current");
                    if (currentEl) currentEl.textContent = page - 1;

                    if (!data.data.has_more) {
                        btn.style.display = "none";
                        var progress = wrapper.querySelector(".load-more-progress");
                        if (progress) progress.textContent = "All articles loaded ✓";
                    }
                }
            })
            .catch(function(err) {
                console.error("Load more error:", err);
            })
            .finally(function() {
                btn.disabled = false;
                textEl.style.display = "";
                loadEl.style.display = "none";
            });
        });
    })();
    ');
}
add_action('wp_enqueue_scripts', 'hikmahnews_load_more_script');