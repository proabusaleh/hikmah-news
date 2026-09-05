<?php
/**
 * Theme Footer
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;
?>

    <!-- ========== FOOTER ========== -->
    <footer class="site-footer" id="siteFooter">

        <!-- Footer Widgets Area -->
        <div class="footer-widgets">
            <div class="container">
                <div class="footer-widgets__grid">

                    <!-- Column 1: About -->
                    <div class="footer-widget">
                        <h4 class="footer-widget__title">About WP News</h4>
                        <p class="footer-widget__text">
                            Your trusted source for breaking news, in-depth analysis,
                            and comprehensive coverage of the stories that matter most.
                            Delivering truth since 2024.
                        </p>
                        <div class="footer-social">
                            <a href="#" aria-label="Facebook" class="footer-social__link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                                </svg>
                            </a>
                            <a href="#" aria-label="Twitter" class="footer-social__link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                                </svg>
                            </a>
                            <a href="#" aria-label="Instagram" class="footer-social__link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                    <circle cx="12" cy="12" r="5"/>
                                    <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/>
                                </svg>
                            </a>
                            <a href="#" aria-label="YouTube" class="footer-social__link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.43z"/>
                                    <polygon points="9.75,15.02 15.5,11.75 9.75,8.48" fill="#111"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="footer-widget">
                        <h4 class="footer-widget__title">Quick Links</h4>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'container'      => false,
                            'menu_class'     => 'footer-widget__links',
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ]);
                        ?>
                        <?php if (!has_nav_menu('footer')) : ?>
                            <ul class="footer-widget__links">
                                <li><a href="<?php echo esc_url(home_url('/about')); ?>">About Us</a></li>
                                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
                                <li><a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a></li>
                                <li><a href="<?php echo esc_url(home_url('/terms')); ?>">Terms of Service</a></li>
                                <li><a href="<?php echo esc_url(home_url('/advertise')); ?>">Advertise</a></li>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Column 3: Categories -->
                    <div class="footer-widget">
                        <h4 class="footer-widget__title">Categories</h4>
                        <ul class="footer-widget__links">
                            <?php
                            $categories = get_categories([
                                'number'     => 6,
                                'orderby'    => 'count',
                                'order'      => 'DESC',
                                'hide_empty' => true,
                            ]);
                            foreach ($categories as $cat) :
                            ?>
                                <li>
                                    <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
                                        <?php echo esc_html($cat->name); ?>
                                        <span class="footer-cat-count">(<?php echo esc_html($cat->count); ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Column 4: Newsletter -->
                    <div class="footer-widget">
                        <h4 class="footer-widget__title">Newsletter</h4>
                        <p class="footer-widget__text">
                            Get the latest news delivered straight to your inbox every morning.
                        </p>
                        <form class="footer-newsletter" action="#" method="POST">
                            <input type="email" placeholder="Your email address"
                                   class="footer-newsletter__input" required>
                            <button type="submit" class="btn btn--primary btn--sm">
                                Subscribe
                            </button>
                        </form>
                        <p class="footer-newsletter__note">
                            No spam. Unsubscribe anytime.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container footer-bottom__inner">
                <p class="footer-bottom__copy">
                    &copy; <?php echo esc_html(date('Y')); ?>
                    <strong>WP News</strong>. All rights reserved.
                </p>
                <p class="footer-bottom__credits">
                    Designed with ❤️ for WordPress
                </p>
            </div>
        </div>

    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>