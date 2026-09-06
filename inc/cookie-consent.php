<?php
/**
 * Cookie Consent Banner (GDPR/CCPA Compliant)
 * - Accept all / essential only / custom preferences
 * - localStorage persistence + consent event
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

function hikmahnews_cookie_consent() {
    if (is_admin()) return;
    ?>
    <div class="cookie-consent" id="cookieConsent">
        <div class="cookie-consent__inner">
            <div class="cookie-consent__content">
                <span class="cookie-consent__icon">🍪</span>
                <div>
                    <h4 class="cookie-consent__title">We value your privacy</h4>
                    <p class="cookie-consent__text">
                        We use cookies to enhance your browsing experience, serve personalized
                        ads, and analyze our traffic. By clicking "Accept All", you consent to
                        our use of cookies.
                        <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Read our Privacy Policy</a>.
                    </p>
                </div>
            </div>
            <div class="cookie-consent__actions">
                <button class="modern-btn modern-btn--primary cookie-accept-all" id="cookieAcceptAll">
                    Accept All
                </button>
                <button class="modern-btn modern-btn--ghost cookie-accept-essential" id="cookieAcceptEssential">
                    Essential Only
                </button>
                <button class="modern-btn modern-btn--ghost" id="cookieCustomize">
                    Customize
                </button>
            </div>
        </div>
        <!-- Customize Panel -->
        <div class="cookie-consent__customize" id="cookieCustomizePanel" style="display:none;">
            <div class="cookie-consent__option">
                <label><input type="checkbox" checked disabled> <strong>Essential</strong> — Required for site functionality</label>
            </div>
            <div class="cookie-consent__option">
                <label><input type="checkbox" id="cookieAnalytics" checked> <strong>Analytics</strong> — Help us improve the site</label>
            </div>
            <div class="cookie-consent__option">
                <label><input type="checkbox" id="cookieAds" checked> <strong>Advertising</strong> — Personalized ads</label>
            </div>
            <button class="modern-btn modern-btn--primary" id="cookieSavePrefs" style="margin-top:12px;">
                Save Preferences
            </button>
        </div>
    </div>
    <script>
    (function() {
        var banner = document.getElementById('cookieConsent');
        if (!banner) return;
        var consent = localStorage.getItem('hikmahnews_cookie_consent');
        if (!consent) {
            setTimeout(function() { banner.classList.add('visible'); }, 1500);
        }
        function saveConsent(type) {
            var prefs = { type: type, time: Date.now() };
            if (type === 'custom') {
                prefs.analytics = document.getElementById('cookieAnalytics').checked;
                prefs.ads = document.getElementById('cookieAds').checked;
            }
            localStorage.setItem('hikmahnews_cookie_consent', JSON.stringify(prefs));
            banner.classList.remove('visible');
            document.dispatchEvent(new CustomEvent('hikmahnews:consent', { detail: prefs }));
        }
        var acceptAll = document.getElementById('cookieAcceptAll');
        var essential = document.getElementById('cookieAcceptEssential');
        var customize = document.getElementById('cookieCustomize');
        var savePrefs = document.getElementById('cookieSavePrefs');
        if (acceptAll) acceptAll.onclick = function() { saveConsent('all'); };
        if (essential) essential.onclick = function() { saveConsent('essential'); };
        if (customize) customize.onclick = function() {
            var p = document.getElementById('cookieCustomizePanel');
            if (p) p.style.display = p.style.display === 'none' ? 'block' : 'none';
        };
        if (savePrefs) savePrefs.onclick = function() { saveConsent('custom'); };
    })();
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_cookie_consent', 1);