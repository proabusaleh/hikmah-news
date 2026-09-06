<?php
/**
 * Font Size Toggle (A- A A+)
 * - Increases/decreases root font size
 * - Persisted in localStorage
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

function hikmahnews_font_size_toggle() {
    ?>
    <div class="font-size-toggle" id="fontSizeToggle">
        <button class="font-size-toggle__btn" data-size="decrease" aria-label="Decrease font size">A-</button>
        <button class="font-size-toggle__btn font-size-toggle__btn--reset" data-size="reset" aria-label="Reset font size">A</button>
        <button class="font-size-toggle__btn" data-size="increase" aria-label="Increase font size">A+</button>
    </div>
    <style>
        .font-size-toggle {
            position: fixed; bottom: 90px; right: 20px; z-index: 997;
            display: flex; flex-direction: column; gap: 2px;
            background: var(--modern-surface, #fff); border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1); border: 1px solid var(--modern-border, #e5e5e5);
            overflow: hidden;
        }
        .font-size-toggle__btn {
            width: 40px; height: 36px; border: none; background: transparent;
            font-size: 14px; font-weight: 700; color: var(--modern-text-2, #525252);
            cursor: pointer; transition: all 0.2s;
        }
        .font-size-toggle__btn:hover { background: var(--color-primary); color: white; }
        .font-size-toggle__btn--reset { font-size: 16px; border-top: 1px solid var(--modern-border, #e5e5e5); border-bottom: 1px solid var(--modern-border, #e5e5e5); }
        @media (max-width: 768px) { .font-size-toggle { bottom: 130px; right: 12px; } }
    </style>
    <script>
    (function() {
        var toggle = document.getElementById('fontSizeToggle');
        if (!toggle) return;
        var sizes = [14, 16, 18, 20, 22];
        var current = parseInt(localStorage.getItem('hikmahnews_font_size')) || 1;
        function applySize() {
            document.documentElement.style.fontSize = sizes[current] + 'px';
            localStorage.setItem('hikmahnews_font_size', current);
        }
        applySize();
        toggle.addEventListener('click', function(e) {
            var btn = e.target.closest('.font-size-toggle__btn');
            if (!btn) return;
            var action = btn.dataset.size;
            if (action === 'increase' && current < sizes.length - 1) current++;
            else if (action === 'decrease' && current > 0) current--;
            else if (action === 'reset') current = 1;
            applySize();
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_font_size_toggle', 5);