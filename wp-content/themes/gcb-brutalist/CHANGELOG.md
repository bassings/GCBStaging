# Changelog

All notable changes to the GCB Brutalist theme will be documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-03-08

### Performance
- **Fix LCP render delay** — Hero preload was fetching `medium_large` (768px) while the bento grid rendered the hero at `large` (1000px), causing a wasted preload and ~1.7s element render delay. Now both match. Render delay dropped from 1,759ms to 17ms.
- **Override WP.com font-display:fallback → swap** — WP.com's wp-fonts-local system was ignoring theme.json `fontDisplay: "swap"` and forcing `fallback`. Output buffer now rewrites all instances to `swap` for immediate text rendering.
- **Correct bento card `sizes` attribute** — Standard cards display at ~400px on desktop but `sizes` said `280px`, causing the browser to skip appropriately-sized srcset entries. Updated to `400px` (standard) and `900px` (hero). Saves ~92KiB per page load.
- **Downsize YouTube rail thumbnails** — Video rail cards (320-450px wide) were loading `hqdefault.jpg` (480x360, ~45KB). Now uses `mqdefault.jpg` (320x180, ~23KB) — 48% smaller per thumbnail.
- **Preload LCP hero image** (Photon-aware, no double download)
- **Delay gtag.js** until user interaction (scroll/click/touch) or 5s timeout

### Added
- **Consistent 16:9 aspect ratio** for all bento grid image containers (hero and standard cards) using CSS `aspect-ratio` instead of fixed pixel heights
- Privacy Policy and Terms of Service links in footer
- WebP safety net — serves `.webp` when `.jpg`/`.png` is requested (3-layer: attachment URL, srcset, output buffer)
- Auto-convert new uploads to WebP (quality 82, 1200px cap)
- Physical WebP thumbnail generation at upload time (thumbnail, medium, medium_large, large, newspack sizes)
- Review schema (JSON-LD) for car review articles
- `ai.txt` served at `/ai.txt`
- Category breadcrumbs on single posts (uses Yoast primary category)
- Author box injected before Jetpack sharing/related posts
- Lite-youtube facade for YouTube embeds (defers ~800KB+ per video)
- Email-safe converters for Spectra galleries and lite-youtube in newsletters
- Video archive page at `/video/` with YouTube API grid
- Category children grid ("Browse by Brand") on parent category pages
- Year selector for date archives
- Legacy Fusion YouTube shortcode handler (`[fusion_youtube]` → lite-youtube)
- Old permalink redirect (`/%postname%/%category%/` → `/%postname%/`)

### Fixed
- Lite-youtube thumbnail fallback chain (maxres → sd → hq)
- Review schema — removed `in_the_loop()` check (wp_head fires before loop)
- Breadcrumbs pick deepest category when no Yoast primary is set
- Contact email and social links updated (Instagram → @gcbcarreviews, Twitter → x.com)

### Removed
- Emoji detection script (wp-emoji-release.min.js)
- devicepx-jetpack.js (redundant with srcset)
- Open Sans font (not in design system)
- Jetpack Likes/Sharing/Related Posts assets from homepage (only needed on single posts)

## [1.0.0] - 2026-02-01

### Added
- Initial release of GCB Brutalist theme
- Editorial Brutalism "Neon Noir" design system
- FSE block theme with custom patterns (bento grid, video rail, hero section, culture grid)
- Custom header navigation with mobile slide-out menu
- Terminal-style search modal
- Search results grid with pagination
- Responsive video embeds
- Critical CSS inlining
- Resource hints for Google Fonts, YouTube, Photon CDN
