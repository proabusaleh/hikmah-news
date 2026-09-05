<?php
/**
 * Author Archive Template
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();

$author = get_queried_object();
$author_bio = get_the_author_meta('description', $author->ID);
$author_url = get_the_author_meta('user_url', $author->ID);
$post_count = count_user_posts($author->ID);
?>

<main class="site-main" id="main">

    <!-- Author Header -->
    <header class="author-header">
        <div class="container">
            <div class="author-header__inner">
                <div class="author-header__avatar">
                    <?php echo get_avatar($author->ID, 120); ?>
                </div>
                <div class="author-header__info">
                    <span class="badge badge--secondary">Author</span>
                    <h1 class="author-header__name">
                        <?php echo esc_html($author->display_name); ?>
                    </h1>
                    <?php if ($author_bio) : ?>
                        <p class="author-header__bio">
                            <?php echo esc_html($author_bio); ?>
                        </p>
                    <?php endif; ?>
                    <div class="author-header__stats">
                        <span>📝 <?php echo esc_html($post_count); ?> Articles</span>
                        <?php if ($author_url) : ?>
                            <span>🌐 <a href="<?php echo esc_url($author_url); ?>"
                                       target="_blank" rel="noopener">Website</a></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Author Posts -->
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
                                <time><?php echo get_the_date(); ?></time>
                                <span class="dot"></span>
                                <span><?php echo wpnews_reading_time(); ?> min read</span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; endif; ?>
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