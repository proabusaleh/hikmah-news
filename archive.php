<?php
/**
 * Generic Archive Template (Date, Tag, Author, etc.)
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="site-main" id="main">

    <header class="archive-header">
        <div class="container">
            <span class="badge badge--secondary">
                <?php
                if (is_tag()) echo 'Tag';
                elseif (is_author()) echo 'Author';
                elseif (is_date()) echo 'Archive';
                else echo 'Archive';
                ?>
            </span>
            <h1 class="archive-header__title">
                <?php the_archive_title(); ?>
            </h1>
            <?php if (get_the_archive_description()) : ?>
                <p class="archive-header__desc">
                    <?php the_archive_description(); ?>
                </p>
            <?php endif; ?>
        </div>
    </header>

    <section class="archive-grid-section">
        <div class="container">
            <div class="grid grid--3">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <article class="news-card">
                        <div class="news-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
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
                    <p>No posts found.</p>
                <?php endif; ?>
            </div>

            <div class="archive-pagination">
                <?php
                the_posts_pagination([
                    'mid_size'  => 2,
                    'prev_text' => '← Previous',
                    'next_text' => 'Next →',
                ]);
                ?>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>