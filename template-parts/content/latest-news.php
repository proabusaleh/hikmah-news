<?php
/**
 * Latest News Grid Section
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

$latest_query = new WP_Query([
    'posts_per_page'      => 6,
    'offset'              => 5, // Skip hero posts
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => false, // IMPORTANT: enable for pagination
]);

if (!$latest_query->have_posts()) return;
?>

<section class="latest-section">
    <div class="container">

        <!-- Section Title -->
        <div class="section-title">
            <h2 class="section-title__text">
                <span class="section-title__icon">🔥</span>
                Latest News
            </h2>
            <div class="section-title__line"></div>
            <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>"
               class="btn btn--outline btn--sm">
                View All
            </a>
        </div>

        <!-- News Grid -->
        <div class="grid grid--3 latest-grid">
            <?php while ($latest_query->have_posts()) : $latest_query->the_post(); ?>
                <article class="news-card">
                    <div class="news-card__image">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('wpnews-grid'); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(WPNEWS_URI . '/assets/images/placeholder.jpg'); ?>"
                                     alt="<?php the_title_attribute(); ?>">
                            <?php endif; ?>
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
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        <p class="news-card__excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 18, '...'); ?>
                        </p>
                        <div class="news-card__meta">
                            <span class="author"><?php the_author(); ?></span>
                            <span class="dot"></span>
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                            </time>
                            <span class="dot"></span>
                            <span><?php echo wpnews_reading_time(); ?> min read</span>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Load More -->
        <?php
        wpnews_load_more_button([
            'max_pages' => $latest_query->max_num_pages,
            'per_page'  => 6,
            'container' => '.latest-grid',
            'offset'    => 5,
        ]);
        ?>

    </div>
</section>