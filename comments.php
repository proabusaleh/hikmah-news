<?php
/**
 * Comments Template
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

if (post_password_required()) return;
?>

<section class="comments-section" id="comments">
    <div class="container container--narrow">

        <div class="section-title">
            <h2 class="section-title__text">
                Comments
                <span class="badge badge--outline">
                    <?php echo get_comments_number(); ?>
                </span>
            </h2>
            <div class="section-title__line"></div>
        </div>

        <?php if (have_comments()) : ?>
            <ol class="comments-list">
                <?php
                wp_list_comments([
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 48,
                    'callback'    => 'hikmahnews_comment_callback',
                ]);
                ?>
            </ol>

            <?php if (get_comment_pages_count() > 1) : ?>
                <nav class="comments-nav">
                    <?php
                    paginate_comments_links([
                        'prev_text' => '← Older',
                        'next_text' => 'Newer →',
                    ]);
                    ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        comment_form([
            'title_reply'          => 'Leave a Comment',
            'title_reply_before'   => '<h3 class="comments-form__title">',
            'title_reply_after'    => '</h3>',
            'class_form'           => 'comments-form',
            'class_submit'         => 'btn btn--primary',
            'comment_field'        => '<p class="comments-form__field">
                <textarea id="comment" name="comment" rows="5"
                          placeholder="Write your comment..." required></textarea>
            </p>',
            'submit_field'         => '<p class="comments-form__submit">%1$s %2$s</p>',
        ]);
        ?>

    </div>
</section>