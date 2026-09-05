<?php
/**
 * Related Posts Section
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

$categories = get_the_category();
if (!$categories) return;

$cat_ids = wp_list_pluck($categories, 'term_id');

$related_query = new WP_Query([
    'category__in'        => $cat_ids,
    'post__not_in'        => [get_the_ID()],
    'posts_per_page'      => 3,
    'orderby'             => 'rand',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => 1,
]);

if (!$related_query->have_posts()) return;
?>

<section class="related-section">
    <div class="container container--narrow">

        <div class="section-title">
            <h2 class="section-title__text">Related Articles</h2>
            <div class="section-title__line"></div>
        </div>

        <div class="grid grid--3">
            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <article class="news-card">
                    <div class="news-card__image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('wpnews-grid'); ?>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="news-card__body">
                        <h3 class="news-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="news-card__meta">
                            <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    </div>
</section>