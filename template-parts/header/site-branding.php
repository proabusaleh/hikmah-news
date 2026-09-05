<?php
/**
 * Site Branding — Logo + Search + Mobile Toggle
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;
?>

<div class="site-header">
    <div class="container site-header__inner">

        <!-- LOGO -->
        <div class="site-branding">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-text">
                    <span class="site-logo-text__wp">WP</span>
                    <span class="site-logo-text__news">News</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- HEADER AD SPACE (optional) -->
        <div class="site-header__ad">
            <?php if (is_active_sidebar('header-ad')) : ?>
                <?php dynamic_sidebar('header-ad'); ?>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Search + Mobile Menu -->
        <div class="site-header__actions">

            <!-- Search Toggle -->
            <button class="search-toggle" id="searchToggle" aria-label="Open search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>

            <!-- Mobile Hamburger -->
            <button class="mobile-toggle" id="mobileToggle" aria-label="Open menu">
                <span class="hamburger"></span>
            </button>
        </div>
    </div>

    <!-- SEARCH OVERLAY -->
    <div class="search-overlay" id="searchOverlay">
        <div class="container">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
                  class="search-overlay__form">
                <input type="search" name="s" placeholder="Search news, topics, authors..."
                       class="search-overlay__input" autocomplete="off" required>
                <button type="submit" class="btn btn--primary">Search</button>
                <button type="button" class="search-overlay__close" id="searchClose"
                        aria-label="Close search">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>