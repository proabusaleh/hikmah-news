<?php
/**
 * Live Search — AJAX Instant Results
 * - Real-time search as you type (300ms debounce)
 * - Shows title, category, thumbnail, excerpt
 * - Keyboard navigation (↑↓ Enter Esc)
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. AJAX SEARCH HANDLER
// ============================================
function hikmahnews_live_search() {
    check_ajax_referer('hikmahnews_nonce', 'nonce');

    $query = sanitize_text_field($_POST['query'] ?? '');
    if (strlen($query) < 2) {
        wp_send_json_success(['results' => [], 'message' => 'Type at least 2 characters']);
    }

    $search = new WP_Query([
        's'              => $query,
        'posts_per_page' => 8,
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
        'orderby'        => 'relevance',
    ]);

    $results = [];

    if ($search->have_posts()) {
        while ($search->have_posts()) {
            $search->the_post();
            $cats = get_the_category();
            $reading = hikmahnews_reading_time_detailed(get_the_ID());

            $results[] = [
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'url'       => get_permalink(),
                'excerpt'   => wp_trim_words(get_the_excerpt(), 12, '...'),
                'category'  => $cats ? $cats[0]->name : '',
                'cat_url'   => $cats ? get_category_link($cats[0]->term_id) : '',
                'date'      => get_the_date('M j, Y'),
                'reading'   => $reading['label'],
                'thumbnail' => has_post_thumbnail()
                    ? get_the_post_thumbnail_url(get_the_ID(), 'hikmahnews-thumb')
                    : '',
                'views'     => hikmahnews_get_formatted_views(get_the_ID()),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success([
        'results' => $results,
        'total'   => count($results),
        'query'   => $query,
        'search_url' => add_query_arg('s', urlencode($query), home_url('/')),
    ]);
}
add_action('wp_ajax_hikmahnews_live_search', 'hikmahnews_live_search');
add_action('wp_ajax_nopriv_hikmahnews_live_search', 'hikmahnews_live_search');

// ============================================
// 2. LIVE SEARCH HTML (Replaces old search overlay)
// ============================================
function hikmahnews_live_search_overlay() {
    ?>
    <div class="live-search-overlay" id="liveSearchOverlay">
        <div class="container">
            <div class="live-search-box">
                <!-- Search Input -->
                <div class="live-search__input-wrap">
                    <svg class="live-search__icon" width="22" height="22" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="liveSearchInput" class="live-search__input"
                           placeholder="Search news, topics, authors..."
                           autocomplete="off" spellcheck="false">
                    <span class="live-search__shortcut">ESC</span>
                    <button class="live-search__close" id="liveSearchClose" aria-label="Close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- Results Container -->
                <div class="live-search__results" id="liveSearchResults">
                    <div class="live-search__empty">
                        <p>Start typing to search...</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="live-search__footer" id="liveSearchFooter" style="display:none;">
                    <a href="#" class="live-search__view-all" id="liveSearchViewAll">
                        View all results →
                    </a>
                    <span class="live-search__count" id="liveSearchCount"></span>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'hikmahnews_live_search_overlay');

// ============================================
// 3. LIVE SEARCH JAVASCRIPT
// ============================================
function hikmahnews_live_search_script() {
    wp_add_inline_script('hikmahnews-main', '
    (function() {
        var overlay = document.getElementById("liveSearchOverlay");
        var input = document.getElementById("liveSearchInput");
        var results = document.getElementById("liveSearchResults");
        var footer = document.getElementById("liveSearchFooter");
        var viewAll = document.getElementById("liveSearchViewAll");
        var countEl = document.getElementById("liveSearchCount");
        var closeBtn = document.getElementById("liveSearchClose");
        var searchToggle = document.getElementById("searchToggle");
        var debounceTimer;
        var activeIndex = -1;

        if (!overlay || !input) return;

        // Open
        function openSearch() {
            overlay.classList.add("active");
            setTimeout(function() { input.focus(); }, 100);
            document.body.style.overflow = "hidden";
        }

        // Close
        function closeSearch() {
            overlay.classList.remove("active");
            input.value = "";
            results.innerHTML = \'<div class="live-search__empty"><p>Start typing to search...</p></div>\';
            footer.style.display = "none";
            document.body.style.overflow = "";
            activeIndex = -1;
        }

        if (searchToggle) searchToggle.addEventListener("click", openSearch);
        if (closeBtn) closeBtn.addEventListener("click", closeSearch);

        overlay.addEventListener("click", function(e) {
            if (e.target === overlay) closeSearch();
        });

        // Keyboard shortcut: Ctrl+K or /
        document.addEventListener("keydown", function(e) {
            if ((e.ctrlKey && e.key === "k") || (e.key === "/" && document.activeElement.tagName !== "INPUT")) {
                e.preventDefault();
                openSearch();
            }
            if (e.key === "Escape") closeSearch();
        });

        // Search with debounce
        input.addEventListener("input", function() {
            var query = this.value.trim();
            clearTimeout(debounceTimer);
            activeIndex = -1;

            if (query.length < 2) {
                results.innerHTML = \'<div class="live-search__empty"><p>Start typing to search...</p></div>\';
                footer.style.display = "none";
                return;
            }

            results.innerHTML = \'<div class="live-search__loading"><div class="live-search__spinner"></div> Searching...</div>\';

            debounceTimer = setTimeout(function() {
                if (typeof hikmahnews_ajax === "undefined") return;

                fetch(hikmahnews_ajax.ajax_url, {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: "action=hikmahnews_live_search&nonce=" + hikmahnews_ajax.nonce + "&query=" + encodeURIComponent(query)
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) return;
                    renderResults(data.data);
                });
            }, 300);
        });

        // Render results
        function renderResults(data) {
            if (!data.results || data.results.length === 0) {
                results.innerHTML = \'<div class="live-search__empty"><p>No results found for "\' + data.query + \'"</p></div>\';
                footer.style.display = "none";
                return;
            }

            var html = "";
            data.results.forEach(function(item, i) {
                html += \'<a href="\' + item.url + \'" class="live-search__item" data-index="\' + i + \'">\';
                if (item.thumbnail) {
                    html += \'<div class="live-search__item-img"><img src="\' + item.thumbnail + \'" alt=""></div>\';
                }
                html += \'<div class="live-search__item-content">\';
                if (item.category) {
                    html += \'<span class="live-search__item-cat">\' + item.category + \'</span>\';
                }
                html += \'<h4 class="live-search__item-title">\' + item.title + \'</h4>\';
                html += \'<p class="live-search__item-excerpt">\' + item.excerpt + \'</p>\';
                html += \'<div class="live-search__item-meta">\';
                html += \'<span>\' + item.date + \'</span>\';
                html += \'<span class="dot"></span>\';
                html += \'<span>\' + item.reading + \'</span>\';
                html += \'<span class="dot"></span>\';
                html += \'<span>👁 \' + item.views + \'</span>\';
                html += \'</div></div></a>\';
            });

            results.innerHTML = html;
            footer.style.display = "flex";
            viewAll.href = data.search_url;
            countEl.textContent = data.total + " result(s) found";
        }

        // Keyboard navigation
        input.addEventListener("keydown", function(e) {
            var items = results.querySelectorAll(".live-search__item");
            if (!items.length) return;

            if (e.key === "ArrowDown") {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                updateActive(items);
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                updateActive(items);
            } else if (e.key === "Enter" && activeIndex >= 0) {
                e.preventDefault();
                items[activeIndex].click();
            }
        });

        function updateActive(items) {
            items.forEach(function(item, i) {
                item.classList.toggle("live-search__item--active", i === activeIndex);
            });
            if (items[activeIndex]) {
                items[activeIndex].scrollIntoView({ block: "nearest" });
            }
        }
    })();
    ');
}
add_action('wp_enqueue_scripts', 'hikmahnews_live_search_script');