<?php
/**
 * Hero Section — Large Featured + Side Stories
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

// Main hero post (latest sticky or most recent)
$hero_query = new WP_Query([
    'posts_per_page'      => 1,
    'post__in'            => get_option('sticky_posts'),
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => true,
]);

// If no sticky, just get latest
if (!$hero_query->have_posts()) {
    $hero_query = new WP_Query([
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ]);
}

// Side stories
$side_query = new WP_Query([
    'posts_per_page'      => 4,
    'offset'              => 1,
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => true,
]);
?>

<section class="hero-section">
    <div class="container">
        <div class="hero-grid">

            <!-- MAIN HERO -->
            <?php if ($hero_query->have_posts()) : while ($hero_query->have_posts()) : $hero_query->the_post(); ?>
                <article class="hero-main">
                    <a href="<?php the_permalink(); ?>" class="hero-main__link">
                        <div class="hero-main__image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('wpnews-hero'); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(WPNEWS_URI . '/assets/images/placeholder.jpg'); ?>"
                                     alt="<?php the_title_attribute(); ?>">
                            <?php endif; ?>
                            <div class="hero-main__overlay"></div>
                        </div>
                        <div class="hero-main__content">
                            <?php
                            $categories = get_the_category();
                            if ($categories) :
                            ?>
                                <span class="badge badge--primary">
                                    <?php echo esc_html($categories[0]->name); ?>
                                </span>
                            <?php endif; ?>
                            <h1 class="hero-main__title">
                                <?php the_title(); ?>
                            </h1>
                            <p class="hero-main__excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                            </p>
                            <div class="hero-main__meta">
                                <span class="hero-main__author">
                                    By <?php the_author(); ?>
                                </span>
                                <span class="dot"></span>
                                <time datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                                </time>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endwhile; wp_reset_postdata(); endif; ?>

            <!-- SIDE STORIES -->
            <div class="hero-sidebar">
                <?php if ($side_query->have_posts()) : while ($side_query->have_posts()) : $side_query->the_post(); ?>
                    <article class="hero-side">
                        <a href="<?php the_permalink(); ?>" class="hero-side__link">
                            <div class="hero-side__image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('wpnews-thumb'); ?>
                                <?php endif; ?>
                            </div>
                            <div class="hero-side__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="hero-side__cat">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="hero-side__title">
                                    <?php the_title(); ?>
                                </h3>
                                <time class="hero-side__time" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                                </time>
                            </div>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>

        </div>
    </div>
</section>