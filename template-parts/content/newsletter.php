<?php
/**
 * Newsletter CTA Section
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;
?>

<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-box">
            <div class="newsletter-box__content">
                <span class="newsletter-box__badge">📬 Newsletter</span>
                <h2 class="newsletter-box__title">
                    Stay Informed. Stay Ahead.
                </h2>
                <p class="newsletter-box__text">
                    Join 50,000+ readers who get the top stories delivered to their
                    inbox every morning. No spam, unsubscribe anytime.
                </p>
            </div>
            <form class="newsletter-box__form" action="#" method="POST">
                <div class="newsletter-box__input-group">
                    <input type="text" placeholder="Your name" class="newsletter-box__input" required>
                    <input type="email" placeholder="Your email address"
                           class="newsletter-box__input" required>
                </div>
                <button type="submit" class="btn btn--primary btn--lg newsletter-box__btn">
                    Subscribe Free →
                </button>
                <p class="newsletter-box__privacy">
                    🔒 We respect your privacy. Read our
                    <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>
</section>