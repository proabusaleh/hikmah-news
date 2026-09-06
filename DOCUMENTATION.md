# Hikmah News Theme — Documentation

## Quick Start
1. Install & activate the theme
2. Go to **Appearance → ⚙️ Theme Options**
3. Choose your design in **⚙️ General → Design Style** (Modern / Classic / Minimal)
4. Categories are auto-created on activation
5. Set up menus in **Appearance → Menus**
6. Add widgets in **Appearance → Widgets**

## Theme Options (15 Tabs)
| Tab | Description |
|-----|-------------|
| ⚙️ General | Logo, date format, design style, reading time |
| 🔝 Header | Top bar, sticky nav, search style |
| 🏠 Homepage | Section order, hero style, posts per section |
| 🔴 Breaking | Ticker, alert bar, auto-expiry |
| 🔤 Typography | Fonts, sizes, weights |
| 🎨 Colors | Primary, secondary, accent, dark mode |
| 📐 Layout | Container width, grid, sidebar |
| 📌 Sidebar | Visibility per page type |
| 💰 Advertisement | Global toggle, AdSense, frequency |
| 🌐 Social | Profile links, share buttons |
| 🔻 Footer | Columns, copyright |
| 📬 Newsletter | Provider, API key, popup |
| 🔍 SEO | Meta, schema, OG, Twitter |
| ⚡ Performance | Cache, CDN, lazy load, vitals |
| 🔄 Updates | Source, auto-update, backup |

## Gutenberg Blocks (11)
Search for "Hikmah News Blocks" in the block inserter:
- Featured News, Latest News, Popular News
- Trending News, Category News, News Slider
- News Grid, News List, Video News
- Breaking News, Advertisement

## Shortcodes
| Shortcode | Usage |
|-----------|-------|
| `[hikmahnews_podcast url="..." title="..."]` | Audio player |
| `[hikmahnews_poll question="..." options="Yes\|No\|Maybe"]` | Interactive poll |
| `[hikmahnews_reading_history]` | Logged-in user's reading history |

## Admin Panels
- **📊 Analytics** — Dashboard: total views, 7-day chart, top articles/categories/authors, ad impressions, estimated revenue
- **💰 Ad Manager** — 13 ad positions with scheduling
- **⚙️ Theme Options** — Full configuration (15 tabs)
- **🔔 Push Notifications** — VAPID keys, log

## Custom Post Types
- **Live Blog (hikmahnews_live_blog)** — Real-time event coverage with AJAX auto-refresh

## PWA
- Manifest: `/hikmahnews-manifest.json`
- Service Worker: `/hikmahnews-sw.js`
- Offline page: `/offline`
- App icons: `assets/images/icon-192.png`, `assets/images/icon-512.png`

## Performance Tips
1. Install a caching plugin (WP Rocket recommended)
2. Enable CDN in Theme Options → Performance
3. Use WebP images (auto-detected)
4. Enable object cache (Redis/Memcached)
5. In 2.1.0 the theme self-updates via GitHub/Custom API

## Compliance
- GDPR/CCPA cookie consent
- Accessibility (a11y): skip links, focus visible, reduced motion, forced-colors, focus trap
- PHPCS: WordPress Coding Standards (`phpcs.xml` provided)
- PWA (installable, offline-capable), SEO schema

## Support
- Documentation: https://example.com/hikmahnews
- Support Forum: https://example.com/support
- GitHub: https://github.com/yourusername/hikmah-news