<?php
/**
 * Generic Page Template
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="site-main" id="main">

    <header class="archive-header">
        <div class="container">
            <h1 class="archive-header__title"><?php the_title(); ?></h1>
        </div>
    </header>

    <section class="page-content">
        <div class="container container--narrow">
            <?php while (have_posts()) : the_post(); ?>
                <article class="single-content__body">
                    <?php
                    if (has_post_thumbnail()) :
                    ?>
                        <figure class="single-featured-image" style="padding: 0 0 var(--space-6);">
                            <?php the_post_thumbnail('hikmahnews-hero'); ?>
                        </figure>
                    <?php endif; ?>

                    <?php the_content(); ?>
                </article>
            <?php endwhile; ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>