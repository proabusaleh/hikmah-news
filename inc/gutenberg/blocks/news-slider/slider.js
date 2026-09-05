/**
 * Hikmah News Slider — Frontend Carousel
 * Horizontal snap-scroll carousel used by the News Slider block.
 * Degrades gracefully: without JS the track is a native overflow scroll.
 * @package HikmahNews
 */
(function() {
    'use strict';

    function initSlider(slider) {
        var track = slider.querySelector('.home-carousel');
        if (!track) return;

        var prevBtn = slider.querySelector('.ws-prev');
        var nextBtn = slider.querySelector('.ws-next');

        function scrollByCard(dir) {
            var first = track.querySelector('.home-carousel__card');
            var step = first ? first.offsetWidth + 16 : 320;
            var target = track.scrollLeft + dir * step;

            track.scrollTo({ left: target, behavior: 'smooth' });
        }

        if (prevBtn) prevBtn.addEventListener('click', function() { scrollByCard(-1); });
        if (nextBtn) nextBtn.addEventListener('click', function() { scrollByCard(1); });

        // Keyboard support
        slider.setAttribute('tabindex', '0');
        slider.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') { scrollByCard(-1); e.preventDefault(); }
            if (e.key === 'ArrowRight') { scrollByCard(1); e.preventDefault(); }
        });

        // Build native arrows if the theme hasn't rendered any
        if (!prevBtn && !nextBtn) {
            prevBtn = document.createElement('button');
            prevBtn.className = 'ws-arrow ws-prev';
            prevBtn.type = 'button';
            prevBtn.setAttribute('aria-label', 'Previous');
            prevBtn.textContent = '‹';

            nextBtn = document.createElement('button');
            nextBtn.className = 'ws-arrow ws-next';
            nextBtn.type = 'button';
            nextBtn.setAttribute('aria-label', 'Next');
            nextBtn.textContent = '›';

            slider.appendChild(prevBtn);
            slider.appendChild(nextBtn);
            slider.classList.add('hikmahnews-slider-has-arrows');

            prevBtn.addEventListener('click', function() { scrollByCard(-1); });
            nextBtn.addEventListener('click', function() { scrollByCard(1); });
        }
    }

    function init() {
        var sliders = document.querySelectorAll('.hikmahnews-slider');
        Array.prototype.forEach.call(sliders, initSlider);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init after AJAX load-more / infinite scroll replaces content
    if (window.hikmahnews_ajax) {
        document.addEventListener('hikmahnews:content-updated', init);
    }
})();