<?php
/**
 * Top Bar — Date, Breaking News Ticker, Social Icons
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;
?>

<div class="topbar">
    <div class="container topbar__inner">

        <!-- LEFT: Date -->
        <div class="topbar__left">
            <time class="topbar__date" datetime="<?php echo esc_attr(date('Y-m-d')); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <?php echo esc_html(date_i18n('l, F j, Y')); ?>
            </time>
        </div>

        <!-- CENTER: Breaking News Ticker -->
        <div class="topbar__center">
            <span class="badge badge--breaking">BREAKING</span>
            <div class="ticker">
                <div class="ticker__track" id="newsTicker">
                    <?php
                    $breaking = new WP_Query([
                        'posts_per_page' => 5,
                        'tag'            => 'breaking',
                        'no_found_rows'  => true,
                    ]);

                    if ($breaking->have_posts()) :
                        while ($breaking->have_posts()) : $breaking->the_post();
                    ?>
                        <a href="<?php the_permalink(); ?>" class="ticker__item">
                            <?php the_title(); ?>
                        </a>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <span class="ticker__item">
                            Welcome to Hikmah News — Your trusted news source.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Social Icons + Dark Mode Toggle -->
        <div class="topbar__right">
            <div class="topbar__social">
                <a href="#" aria-label="Facebook" class="social-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                    </svg>
                </a>
                <a href="#" aria-label="Twitter" class="social-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                    </svg>
                </a>
                <a href="#" aria-label="YouTube" class="social-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.43z"/>
                        <polygon points="9.75,15.02 15.5,11.75 9.75,8.48" fill="#fff"/>
                    </svg>
                </a>
            </div>

            <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode">
                <svg class="icon-sun" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>
        </div>

    </div>
</div>