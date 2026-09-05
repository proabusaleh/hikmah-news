<?php
/**
 * Video News Section
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

$video_query = new WP_Query([
    'posts_per_page' => 4,
    'category_name'  => 'video',
    'no_found_rows'  => true,
]);

if (!$video_query->have_posts()) return;
?>

<section class="video-section">
    <div class="container">

        <div class="section-title" style="border-bottom-color: #EF4444;">
            <h2 class="section-title__text">
                <span class="section-title__icon">🎬</span>
                Video News
            </h2>
            <div class="section-title__line"></div>
            <a href="<?php echo esc_url(get_category_link(get_category_by_slug('video'))); ?>"
               class="btn btn--outline btn--sm" style="border-color: #EF4444; color: #EF4444;">
                All Videos →
            </a>
        </div>

        <div class="video-grid">
            <?php while ($video_query->have_posts()) : $video_query->the_post(); ?>
                <article class="video-card">
                    <a href="<?php the_permalink(); ?>" class="video-card__link">
                        <div class="video-card__thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('wpnews-grid'); ?>
                            <?php endif; ?>
                            <div class="video-card__play">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="white">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                            <span class="video-card__duration">
                                <?php
                                $duration = get_post_meta(get_the_ID(), 'video_duration', true);
                                echo $duration ? esc_html($duration) : '3:45';
                                ?>
                            </span>
                        </div>
                        <div class="video-card__body">
                            <h3 class="video-card__title">
                                <?php the_title(); ?>
                            </h3>
                            <div class="video-card__meta">
                                <span><?php the_author(); ?></span>
                                <span class="dot"></span>
                                <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    </div>
</section>