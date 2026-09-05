/**
 * Hikmah News Theme — Main JavaScript
 * @package HikmahNews
 */
document.addEventListener('DOMContentLoaded', function () {

    // ===== DARK MODE TOGGLE =====
    const darkToggle = document.getElementById('darkToggle');
    const html = document.documentElement;

    // Check saved preference
    if (localStorage.getItem('hikmahnews-dark') === 'true') {
        html.classList.add('dark-mode');
    }

    if (darkToggle) {
        darkToggle.addEventListener('click', () => {
            html.classList.toggle('dark-mode');
            localStorage.setItem(
                'hikmahnews-dark',
                html.classList.contains('dark-mode')
            );
        });
    }

    // ===== SEARCH OVERLAY =====
    const searchToggle = document.getElementById('searchToggle');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');

    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', () => {
            searchOverlay.classList.add('active');
            searchOverlay.querySelector('input').focus();
        });

        searchClose.addEventListener('click', () => {
            searchOverlay.classList.remove('active');
        });

        searchOverlay.addEventListener('click', (e) => {
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('active');
            }
        });
    }

    // ===== MOBILE MENU =====
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const mobileClose = document.getElementById('mobileClose');
    const mobileOverlay = document.getElementById('mobileOverlay');

    function openMobile() {
        mobileDrawer.classList.add('active');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobile() {
        mobileDrawer.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openMobile);
    if (mobileClose) mobileClose.addEventListener('click', closeMobile);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobile);

    // ===== STICKY NAV SHADOW =====
    const mainNav = document.getElementById('mainNav');
    if (mainNav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 150) {
                mainNav.classList.add('main-nav--scrolled');
            } else {
                mainNav.classList.remove('main-nav--scrolled');
            }
        });
    }

    // ===== ESCAPE KEY =====
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (searchOverlay) searchOverlay.classList.remove('active');
            closeMobile();
        }
    });
});