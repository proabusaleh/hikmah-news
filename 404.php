<?php
/**
 * 404 Error Page
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="site-main" id="main">
    <section class="error-404">
        <div class="container">
            <div class="error-404__inner">

                <div class="error-404__code">404</div>
                <h1 class="error-404__title">Page Not Found</h1>
                <p class="error-404__text">
                    The page you're looking for doesn't exist or has been moved.
                    Let's get you back on track.
                </p>

                <!-- Search -->
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
                      class="search-inline error-404__search">
                    <input type="search" name="s" class="search-inline__input"
                           placeholder="Search for articles...">
                    <button type="submit" class="btn btn--primary">Search</button>
                </form>

                <div class="error-404__actions">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary btn--lg">
                        ← Back to Home
                    </a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn--outline btn--lg">
                        Contact Us
                    </a>
                </div>

                <!-- Popular Posts -->
                <div class="error-404__popular">
                    <h3>Popular Articles</h3>
                    <div class="grid grid--3">
                        <?php
                        $popular = new WP_Query([
                            'posts_per_page' => 3,
                            'orderby'        => 'comment_count',
                            'order'          => 'DESC',
                            'no_found_rows'  => true,
                        ]);
                        while ($popular->have_posts()) : $popular->the_post();
                        ?>
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
                                </div>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>