<?php
/**
 * Main Index Template (Ultimate Fallback)
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="site-main" id="main">

    <header class="archive-header">
        <div class="container">
            <h1 class="archive-header__title">
                <?php is_home() ? esc_html_e('Latest News', 'wpnews') : the_archive_title(); ?>
            </h1>
        </div>
    </header>

    <section class="archive-grid-section">
        <div class="container">
            <div class="grid grid--3">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <article class="news-card">
                        <div class="news-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('wpnews-grid'); ?>
                            </a>
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
                                <time><?php echo get_the_date(); ?></time>
                            </div>
                        </div>
                    </article>
                <?php endwhile; else : ?>
                    <p><?php esc_html_e('No posts found.', 'wpnews'); ?></p>
                <?php endif; ?>
            </div>

            <div class="archive-pagination">
                <?php the_posts_pagination([
                    'mid_size'  => 2,
                    'prev_text' => '← Previous',
                    'next_text' => 'Next →',
                ]); ?>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>