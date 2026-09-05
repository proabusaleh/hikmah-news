<?php
/**
 * Template Name: Contact Page
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();

// Handle form submission
$form_success = false;
$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wpnews_contact_nonce'])) {
    if (wp_verify_nonce($_POST['wpnews_contact_nonce'], 'wpnews_contact')) {
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $subject = sanitize_text_field($_POST['contact_subject']);
        $message = sanitize_textarea_field($_POST['contact_message']);

        if ($name && $email && $message) {
            $to = get_option('admin_email');
            $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];
            $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";

            if (wp_mail($to, '[WP News] ' . $subject, $body, $headers)) {
                $form_success = true;
            } else {
                $form_error = 'Failed to send. Please try again.';
            }
        } else {
            $form_error = 'Please fill in all required fields.';
        }
    }
}
?>

<main class="site-main" id="main">

    <header class="archive-header">
        <div class="container">
            <h1 class="archive-header__title"><?php the_title(); ?></h1>
            <?php if (has_excerpt()) : ?>
                <p class="archive-header__desc"><?php echo wp_strip_all_tags(get_the_excerpt()); ?></p>
            <?php endif; ?>
        </div>
    </header>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">

                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    <p>Have a news tip, question, or feedback? We'd love to hear from you.</p>

                    <div class="contact-info__items">
                        <div class="contact-info__item">
                            <span class="contact-info__icon">📧</span>
                            <div>
                                <strong>Email</strong>
                                <a href="mailto:editor@wpnews.com">editor@wpnews.com</a>
                            </div>
                        </div>
                        <div class="contact-info__item">
                            <span class="contact-info__icon">📍</span>
                            <div>
                                <strong>Address</strong>
                                <span>123 News Street, Media City</span>
                            </div>
                        </div>
                        <div class="contact-info__item">
                            <span class="contact-info__icon">📞</span>
                            <div>
                                <strong>Phone</strong>
                                <a href="tel:+1234567890">+1 (234) 567-890</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-wrapper">
                    <?php if ($form_success) : ?>
                        <div class="contact-alert contact-alert--success">
                            ✅ Your message has been sent successfully! We'll get back to you soon.
                        </div>
                    <?php endif; ?>

                    <?php if ($form_error) : ?>
                        <div class="contact-alert contact-alert--error">
                            ❌ <?php echo esc_html($form_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form">
                        <?php wp_nonce_field('wpnews_contact', 'wpnews_contact_nonce'); ?>

                        <div class="contact-form__row">
                            <div class="contact-form__field">
                                <label for="contact_name">Full Name *</label>
                                <input type="text" id="contact_name" name="contact_name"
                                       required placeholder="John Doe">
                            </div>
                            <div class="contact-form__field">
                                <label for="contact_email">Email *</label>
                                <input type="email" id="contact_email" name="contact_email"
                                       required placeholder="john@example.com">
                            </div>
                        </div>

                        <div class="contact-form__field">
                            <label for="contact_subject">Subject</label>
                            <input type="text" id="contact_subject" name="contact_subject"
                                   placeholder="News tip, feedback, etc.">
                        </div>

                        <div class="contact-form__field">
                            <label for="contact_message">Message *</label>
                            <textarea id="contact_message" name="contact_message" rows="6"
                                      required placeholder="Write your message here..."></textarea>
                        </div>

                        <button type="submit" class="btn btn--primary btn--lg">
                            Send Message →
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>