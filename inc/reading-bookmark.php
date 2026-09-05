<?php
/**
 * Reading Time, Progress Bar & Bookmark System
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ENHANCED READING TIME
// ============================================
function hikmahnews_reading_time_detailed($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);

    // Strip shortcodes and HTML
    $clean = strip_tags(strip_shortcodes($content));
    $word_count = str_word_count($clean);

    // Average reading speed: 200-250 WPM
    $minutes = ceil($word_count / 225);
    $minutes = max(1, $minutes);

    return [
        'minutes'    => $minutes,
        'words'      => $word_count,
        'label'      => $minutes === 1 ? '1 min read' : $minutes . ' min read',
        'label_full' => sprintf('%d min read · %s words', $minutes, number_format($word_count)),
    ];
}

// ============================================
// 2. READING PROGRESS BAR (Frontend)
// ============================================
function hikmahnews_reading_progress_bar() {
    if (!is_single()) return;
    ?>
    <div class="reading-progress" id="readingProgress">
        <div class="reading-progress__bar" id="readingProgressBar"></div>
    </div>
    <?php
}
add_action('wp_body_open', 'hikmahnews_reading_progress_bar', 5);

function hikmahnews_reading_progress_script() {
    if (!is_single()) return;
    wp_add_inline_script('hikmahnews-main', '
        (function() {
            var bar = document.getElementById("readingProgressBar");
            var article = document.querySelector(".single-article");
            if (!bar || !article) return;

            window.addEventListener("scroll", function() {
                var rect = article.getBoundingClientRect();
                var total = article.scrollHeight - window.innerHeight;
                var scrolled = -rect.top;
                var progress = Math.min(Math.max(scrolled / total * 100, 0), 100);
                bar.style.width = progress + "%";
            });
        })();
    ');
}
add_action('wp_enqueue_scripts', 'hikmahnews_reading_progress_script');

// ============================================
// 3. BOOKMARK / SAVE ARTICLE (AJAX + localStorage)
// ============================================

// AJAX handler for logged-in users
function hikmahnews_toggle_bookmark() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id || !is_user_logged_in()) {
        wp_send_json_error('Invalid');
    }

    $user_id = get_current_user_id();
    $bookmarks = get_user_meta($user_id, '_hikmahnews_bookmarks', true) ?: [];

    if (in_array($post_id, $bookmarks)) {
        // Remove
        $bookmarks = array_diff($bookmarks, [$post_id]);
        $status = 'removed';
    } else {
        // Add
        $bookmarks[] = $post_id;
        $status = 'added';
    }

    update_user_meta($user_id, '_hikmahnews_bookmarks', array_values($bookmarks));

    wp_send_json_success([
        'status' => $status,
        'count'  => count($bookmarks),
    ]);
}
add_action('wp_ajax_hikmahnews_toggle_bookmark', 'hikmahnews_toggle_bookmark');

// Bookmark button HTML
function hikmahnews_bookmark_button($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $is_bookmarked = false;
    if (is_user_logged_in()) {
        $bookmarks = get_user_meta(get_current_user_id(), '_hikmahnews_bookmarks', true) ?: [];
        $is_bookmarked = in_array($post_id, $bookmarks);
    }
    ?>
    <button class="bookmark-btn <?php echo $is_bookmarked ? 'bookmark-btn--active' : ''; ?>"
            data-post-id="<?php echo esc_attr($post_id); ?>"
            aria-label="<?php echo $is_bookmarked ? 'Remove bookmark' : 'Save article'; ?>"
            title="<?php echo $is_bookmarked ? 'Remove bookmark' : 'Save article'; ?>">
        <svg class="bookmark-icon-empty" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
        </svg>
        <svg class="bookmark-icon-filled" width="18" height="18" viewBox="0 0 24 24"
             fill="currentColor" stroke="currentColor" stroke-width="2">
            <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
        </svg>
    </button>
    <?php
}

// Bookmark JS
function hikmahnews_bookmark_script() {
    if (!is_single() && !is_home() && !is_archive()) return;
    wp_add_inline_script('hikmahnews-main', '
        document.addEventListener("click", function(e) {
            var btn = e.target.closest(".bookmark-btn");
            if (!btn) return;

            var postId = btn.dataset.postId;
            if (typeof hikmahnews_ajax === "undefined") return;

            // For logged-out users: use localStorage
            if (!hikmahnews_ajax.nonce) {
                var saved = JSON.parse(localStorage.getItem("hikmahnews_bookmarks") || "[]");
                var idx = saved.indexOf(postId);
                if (idx > -1) {
                    saved.splice(idx, 1);
                    btn.classList.remove("bookmark-btn--active");
                } else {
                    saved.push(postId);
                    btn.classList.add("bookmark-btn--active");
                }
                localStorage.setItem("hikmahnews_bookmarks", JSON.stringify(saved));
                return;
            }

            // Logged-in: AJAX
            fetch(hikmahnews_ajax.ajax_url, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=hikmahnews_toggle_bookmark&nonce=" + hikmahnews_ajax.nonce + "&post_id=" + postId
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle("bookmark-btn--active", data.data.status === "added");
                }
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'hikmahnews_bookmark_script');