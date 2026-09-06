<?php
/**
 * Accessibility (a11y) Enhancements
 * - Skip links
 * - Visible focus states
 * - Reduced motion support
 * - High contrast mode
 * - Focus trap for modals
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

function hikmahnews_a11y_features() {
    ?>
    <!-- Skip Links -->
    <a class="skip-link" href="#main">Skip to main content</a>
    <a class="skip-link" href="#siteFooter">Skip to footer</a>
    <style>
        .skip-link {
            position: absolute; top: -100px; left: 16px; z-index: 100000;
            background: var(--color-primary); color: white; padding: 12px 24px;
            border-radius: 0 0 8px 8px; font-weight: 700; font-size: 14px;
            transition: top 0.2s;
        }
        .skip-link:focus { top: 0; color: white; outline: 3px solid var(--color-accent); }

        /* Focus Visible */
        :focus-visible {
            outline: 3px solid var(--color-primary);
            outline-offset: 2px;
        }

        /* Screen Reader Only */
        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* High Contrast Mode */
        @media (forced-colors: active) {
            .news-card, .modern-card, .bento-card {
                border: 2px solid ButtonText;
            }
        }
    </style>
    <script>
    (function() {
        // Trap focus in modals
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;
            var modal = document.querySelector('.search-overlay.active, .live-search-overlay.active, .mobile-drawer.active');
            if (!modal) return;
            var focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (!focusable.length) return;
            var first = focusable[0], last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });
    })();
    </script>
    <?php
}
add_action('wp_body_open', 'hikmahnews_a11y_features', 1);