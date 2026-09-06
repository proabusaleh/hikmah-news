<?php
/**
 * Main Navigation
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;
?>

<nav class="main-nav" id="mainNav">
    <div class="container main-nav__inner">

        <?php hikmahnews_mega_menu_nav(); ?>

        <!-- Trending Tag -->
        <div class="main-nav__trending">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
            </svg>
            <span>Trending</span>
        </div>
    </div>
</nav>

<!-- MOBILE MENU DRAWER -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer__header">
        <span class="site-logo-text">
            <span class="site-logo-text__wp">Save Our Muslim</span>
            <span class="site-logo-text__news">Sister</span>
        </span>
        <button class="mobile-drawer__close" id="mobileClose" aria-label="Close menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <nav class="mobile-drawer__nav">
        <?php hikmahnews_mobile_nav(); ?>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>