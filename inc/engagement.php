<?php
/**
 * Engagement Features: TOC, Polls, Social Proof
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. AUTO TABLE OF CONTENTS
// ============================================
function hikmahnews_toc($content) {
    if (!is_single() || is_admin()) return $content;
    if (strpos($content, '<h2') === false) return $content;

    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches);
    if (count($matches[1]) < 3) return $content; // Only show if 3+ headings

    $toc = '<nav class="hikmahnews-toc" aria-label="Table of Contents">';
    $toc .= '<h4 class="hikmahnews-toc__title">📋 Table of Contents</h4>';
    $toc .= '<ol class="hikmahnews-toc__list">';

    foreach ($matches[1] as $i => $heading) {
        $id = 'section-' . ($i + 1);
        $clean = wp_strip_all_tags($heading);
        $toc .= '<li><a href="#' . $id . '">' . esc_html($clean) . '</a></li>';
        $content = preg_replace('/' . preg_quote($matches[0][$i], '/') . '/', '<h2 id="' . $id . '">' . $heading . '</h2>', $content, 1);
    }

    $toc .= '</ol></nav>';

    // Insert after first paragraph
    $pos = strpos($content, '</p>');
    if ($pos !== false) {
        $content = substr($content, 0, $pos + 4) . $toc . substr($content, $pos + 4);
    }

    return $content;
}
add_filter('the_content', 'hikmahnews_toc', 15);

// ============================================
// 2. POLL SHORTCODE (with AJAX vote persistence)
// Usage: [hikmahnews_poll question="Best striker?" options="Messi|Ronaldo|Haaland|Mbappé"]
// ============================================
function hikmahnews_poll_user_key() {
    if (is_user_logged_in()) return 'u' . get_current_user_id();
    $hash = md5((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
    return 'g' . substr($hash, 0, 16);
}

function hikmahnews_poll_shortcode($atts) {
    $atts = shortcode_atts(['question' => '', 'options' => '', 'id' => ''], $atts);
    if (!$atts['question'] || !$atts['options']) return '';

    $post_id = get_the_ID();
    $options = array_map('trim', explode('|', $atts['options']));
    $poll_id = $atts['id'] ?: 'hx' . substr(md5($post_id . $atts['question'] . implode(',', $options)), 0, 8);
    $poll_key = '_hikmahnews_poll_' . $poll_id;

    $votes = get_post_meta($post_id, $poll_key, true);
    $votes = is_array($votes) ? $votes : [];

    $user_key = hikmahnews_poll_user_key();
    $voted = isset($_COOKIE['hikmahnews_poll_' . $poll_id]) || isset($votes[$user_key]);

    $counts = array_fill(0, count($options), 0);
    foreach ($votes as $opt) {
        $opt = (int) $opt;
        if (isset($counts[$opt])) $counts[$opt]++;
    }
    $total = array_sum($counts);

    $html = '<div class="hikmahnews-poll" data-id="' . esc_attr($poll_id) . '" data-post-id="' . esc_attr($post_id) . '" data-voted="' . ($voted ? '1' : '0') . '">';
    $html .= '<h4 class="hikmahnews-poll__question">' . esc_html($atts['question']) . '</h4>';

    foreach ($options as $i => $opt) {
        $pct = $total > 0 ? round($counts[$i] / $total * 100) : 0;
        $html .= '<div class="hikmahnews-poll__option" data-option="' . $i . '">';
        $html .= '<span class="hikmahnews-poll__bar" style="width:' . ($voted ? esc_attr($pct) . '%' : '0%') . '"></span>';
        $html .= '<span class="hikmahnews-poll__label">' . esc_html($opt) . '</span>';
        $html .= '<span class="hikmahnews-poll__pct">' . ($voted ? esc_html($pct) . '%' : '') . '</span>';
        $html .= '</div>';
    }

    $html .= '<div class="hikmahnews-poll__meta">' .
             ($voted ? 'Voted · ' : '') .
             esc_html($total) . ' votes</div>';
    $html .= '</div>';

    if (!$voted) {
        $html .= '<script>
        (function() {
            var polls = document.querySelectorAll(".hikmahnews-poll[data-voted=\"0\"]");
            if (!polls.length || typeof hikmahnews_ajax === "undefined") return;
            polls.forEach(function(poll) {
                poll.addEventListener("click", function(e) {
                    var opt = e.target.closest(".hikmahnews-poll__option");
                    if (!opt || opt.classList.contains("voted")) return;
                    var idx = opt.dataset.option;
                    opt.classList.add("voted");
                    fetch(hikmahnews_ajax.ajax_url, {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "action=hikmahnews_poll_vote&nonce=" + hikmahnews_ajax.nonce +
                              "&post_id=" + poll.dataset.postId + "&poll=" + poll.dataset.id + "&option=" + idx
                    }).then(function(r) { return r.json(); }).then(function(d) {
                        if (d.success && d.data.counts) {
                            poll.dataset.voted = "1";
                            var total = d.data.counts.reduce(function(a, b) { return a + b; }, 0) || 1;
                            poll.querySelectorAll(".hikmahnews-poll__option").forEach(function(op, i) {
                                var pct = Math.round((d.data.counts[i] || 0) / total * 100);
                                op.querySelector(".hikmahnews-poll__bar").style.width = pct + "%";
                                op.querySelector(".hikmahnews-poll__pct").textContent = pct + "%";
                            });
                            var meta = poll.querySelector(".hikmahnews-poll__meta");
                            if (meta) meta.textContent = "Voted · " + (total || 0) + " votes";
                        }
                    }).catch(function() {
                        opt.classList.remove("voted");
                    });
                });
            });
        })();
        </script>';
    }

    return $html;
}
add_shortcode('hikmahnews_poll', 'hikmahnews_poll_shortcode');

function hikmahnews_poll_vote() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    $poll_id = sanitize_key($_POST['poll'] ?? '');
    $option = absint($_POST['option'] ?? -1);
    if (!$post_id || !$poll_id || $option < 0) wp_send_json_error('Invalid');

    $poll_key = '_hikmahnews_poll_' . $poll_id;
    $votes = get_post_meta($post_id, $poll_key, true);
    $votes = is_array($votes) ? $votes : [];

    // Already voted? Reject duplicate
    $user_key = hikmahnews_poll_user_key();
    if (isset($votes[$user_key]) || isset($_COOKIE['hikmahnews_poll_' . $poll_id])) {
        wp_send_json_success(['counts' => hikmahnews_poll_counts($post_id, $poll_key, $votes)]);
    }

    $votes[$user_key] = $option;
    update_post_meta($post_id, $poll_key, $votes);
    setcookie('hikmahnews_poll_' . $poll_id, '1', time() + 60 * 60 * 24 * 365, COOKIEPATH, COOKIE_DOMAIN);

    wp_send_json_success(['counts' => hikmahnews_poll_counts($post_id, $poll_key, $votes)]);
}
add_action('wp_ajax_hikmahnews_poll_vote', 'hikmahnews_poll_vote');
add_action('wp_ajax_nopriv_hikmahnews_poll_vote', 'hikmahnews_poll_vote');

function hikmahnews_poll_counts($post_id, $poll_key, $votes = null) {
    if ($votes === null) {
        $votes = get_post_meta($post_id, $poll_key, true);
        $votes = is_array($votes) ? $votes : [];
    }
    $counts = [];
    foreach ($votes as $opt) {
        $opt = (int) $opt;
        $counts[$opt] = ($counts[$opt] ?? 0) + 1;
    }
    ksort($counts);
    return $counts;
}

// ============================================
// 3. SOCIAL PROOF ("X people reading now")
// ============================================
function hikmahnews_social_proof() {
    if (!is_single() || is_admin()) return;
    ?>
    <div class="social-proof" id="socialProof">
        <span class="social-proof__dot"></span>
        <span><strong id="socialProofCount"><?php echo esc_html(rand(50, 300)); ?></strong> people reading this now</span>
    </div>
    <style>
        .social-proof { position: fixed; bottom: 20px; left: 20px; z-index: 996;
            background: var(--modern-surface, #fff); padding: 10px 16px; border-radius: 100px;
            box-shadow: var(--modern-shadow, 0 1px 3px rgba(0,0,0,0.1)); font-size: 12px; color: var(--modern-text-2, #525252);
            display: flex; align-items: center; gap: 8px; animation: slack-slide-up 0.5s ease 5s both; }
        .social-proof__dot { width: 8px; height: 8px; background: #10B981; border-radius: 50%; animation: modern-pulse 2s infinite; }
        @keyframes slack-slide-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .social-proof { display: none; } }
    </style>
    <script>
    setInterval(function() {
        var el = document.getElementById('socialProofCount');
        if (el) el.textContent = Math.floor(Math.random() * 250) + 50;
    }, 15000);
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_social_proof', 8);