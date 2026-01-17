CLAUDE.md - Project Context & Rules
⚠️ CRITICAL PROTOCOL: TEST-DRIVEN DEVELOPMENT (TDD)
You are the Lead Engineer. I am the Architect.
You are strictly prohibited from writing implementation code (PHP/JS/CSS) until a corresponding End-to-End (E2E) test has been written and confirmed to FAIL.
The Workflow Loop:
RED: Write a Playwright test (tests/e2e/*.spec.ts) for the requested feature. Run it. Confirm it fails.
GREEN: Write the minimum WordPress code to make the test pass. Run the test. Confirm it passes.
REFACTOR: Optimize the code (clean up CSS, improve PHP efficiency) while ensuring the test stays green.
State Management Rule:
Before every test, you must reset the database state to prevent "flaky" tests. Use the gcb-test-utils REST endpoint: DELETE /wp-json/gcb-testing/v1/reset.

Testing Requirements:
EVERY feature test MUST include:
1. Mobile Viewport Tests: Test at 375px width (iPhone), 768px (tablet), 1920px (desktop)
2. Accessibility Tests:
   - Tab navigation works (all interactive elements reachable via keyboard)
   - Focus indicators visible (outline present and visible)
   - ARIA labels present on icon-only buttons
   - Touch targets ≥ 44px (use page.locator().boundingBox())
   - Color contrast verified (text readability)
3. Responsive Layout Tests:
   - Grid columns change at breakpoints (1 col mobile, 2 tablet, 4 desktop)
   - Text sizes scale appropriately (no text smaller than 16px on mobile)
   - Images scale/stack correctly
   - No horizontal scroll on mobile (viewport width check)

⚠️ CRITICAL PROTOCOL: DESIGN ALIGNMENT
North Star Design Reference: north-star-prototype.html (root directory)
This HTML prototype is the canonical design specification for all patterns.

Design Alignment Plan: ~/.claude/plans/snug-pondering-dove.md (OUTDATED)
This plan previously documented differences between the north star design and WordPress implementation.
- Created: 2026-01-01
- Status: ⚠️ OUTDATED as of January 2, 2026 - gaps no longer applicable
- Note: Retained for historical reference only

⚠️ CRITICAL PROTOCOL: IMPLEMENTATION PLAN & GIT WORKFLOW
Project Plan Location: IMPLEMENTATION-PLAN.md (root directory)
This is our single source of truth for project progress, status, and next steps.

Plan Update Protocol:
AFTER EVERY COMPLETED TASK: Update IMPLEMENTATION-PLAN.md with:
  - Current test results (passing/flaky/failing counts)
  - Completed implementation details
  - Files created/modified
  - Updated architecture overview
  - New recommendations for next steps

Git Commit Protocol:
ALL CHANGES MUST BE COMMITTED TO GIT after completion:
  - Pattern implementations (PHP files)
  - Test files (TypeScript)
  - Template modifications (HTML)
  - Theme configuration (functions.php, theme.json)
  - Documentation updates (IMPLEMENTATION-PLAN.md, CLAUDE.md)

Commit Message Format:
  - feat(pattern): add hero section with TDD tests
  - test(hero): add 7 E2E tests for hero section pattern
  - docs(plan): update implementation plan with hero section completion
  - refactor(hero): extract read time calculation to helper function

Git Workflow:
1. Complete TDD cycle (RED → GREEN → REFACTOR)
2. Update IMPLEMENTATION-PLAN.md
3. Commit all changes with descriptive message
4. Continue to next task

⚠️ CURRENT SITE STATUS (Updated: January 17, 2026)
=================================================

PHASE 5 COMPLETE: Fusion Builder Legacy Code Removed ✅

Staging Test Results (https://staging-9ba2-gaycarboys.wpcomstaging.com/):
- 144 tests passing on staging
- Performance: Excellent (TTFB: 17-18ms, LCP/FCP/CLS all passing)
- Security: All 11 security header tests passing
- Accessibility: All 12 axe-core tests passing

Recent Updates (January 17, 2026):
✅ Fusion Builder Fallbacks Removed: All legacy code cleaned up
   - Removed ~320 lines from functions.php (9 Fusion-related functions)
   - Removed ~320 lines from single.html (lite-youtube CSS/JS, gallery detection)
   - jQuery conditional loading removed (theme uses vanilla JS only)
   - All content migrated to Gutenberg blocks (verified via database query)
   - Functions removed: gcb_add_fusion_builder_support(), gcb_enqueue_jquery(),
     gcb_dequeue_fusion_scripts(), gcb_sanitize_youtube_id(),
     gcb_process_fusion_video_fallback(), gcb_register_fusion_fallback_shortcodes(),
     gcb_fusion_youtube_shortcode_fallback(), gcb_fusion_code_shortcode_fallback(),
     gcb_disable_jetpack_lazy_load_for_fusion_galleries()
   - Kept: Table styling for WordPress tables, video responsive CSS for embeds

Previous Updates (January 15, 2026):
✅ Bento Grid Hero Layout: Hero tile now spans full row (3 columns)
   - Changed from span 2 to grid-column: 1 / -1 (full width)
   - Hero image uses 16:9 aspect ratio (640px height on desktop)
   - Hero shows full excerpt, standard cards show 55 words
   - Reduced from 8 to 7 posts (hero + 6 standard cards)
✅ Standardized Excerpt Lengths: All card excerpts now 55 words
   - Based on analysis of 100 posts: 70% of excerpts fit without truncation
   - Updated: bento-grid.php, culture-grid.php, search-results.php, hero-section.php, functions.php
   - Culture grid offset updated from 8 to 7 (starts at article 8)
✅ Culture Grid CSS Fix: Equal column widths enforced
   - Changed grid-template-columns to repeat(4, minmax(0, 1fr))
   - Added word-break: break-word to prevent long words stretching columns

Previous Updates (January 2, 2026):
✅ CRITICAL FIX: Removed aspect-ratio CSS causing site-wide JavaScript failure
   - aspect-ratio: 16/9 property caused CSS syntax error in WordPress rendering
   - `/` character mangled during HTML generation, breaking JavaScript parser
   - Single CSS error prevented: YouTube thumbnails, lazy load, gallery layout
   - Added CSS to hide play button when video activates (.lyt-activated)
   - Fix resolves 4 separate reported issues with one root cause
   - Lesson: Test CSS properties in WordPress context, not just browser
✅ Brutalist Table Styling: Override legacy inline styles for dark theme readability
   - Legacy tables had light backgrounds (blue/white) with white text (unreadable)
   - Force void-black backgrounds, off-white text on ALL table cells (!important)
   - Headers: Acid-lime 2px borders, uppercase monospace typography
   - Body cells: Brutal-border 1px borders, alternating row striping (#050505 / #0a0a0a)
   - Mobile responsive with horizontal scroll
   - 19.8:1 contrast ratio (exceeds WCAG AAA)
   - single.html template (lines 50-130)
✅ Gutenberg Migration Complete: All content converted from Fusion Builder to Gutenberg blocks
   - Removed Fusion Builder fallback functions from functions.php (~320 lines)
   - Removed lite-youtube CSS/JS and Fusion gallery detection from single.html (~320 lines)
   - Removed jQuery conditional loading (theme uses vanilla JS only)
   - Table styling retained for general WordPress tables
   - Video responsive CSS retained for WordPress embeds

Previous Updates (January 1, 2026):
✅ Bento Grid Heading: Changed to "FEATURED STORIES" (uppercase, off-white)
✅ Bento Grid Hover: Fixed acid-lime border on hover (!important added)
✅ Bento Grid Metadata: Ensured consistent #AAAAAA brutal-grey color
✅ Footer Social Icons: Added Bluesky and Facebook (now 5 total: YouTube, Instagram, Twitter/X, Bluesky, Facebook)
✅ Index Template: Removed "Read More" text from post excerpt cards

Pattern Status:
✅ Hero Section - 7 tests passing
✅ Video Rail - 16 tests passing (horizontal scrolling, YouTube thumbnails)
✅ Bento Grid - 8 tests passing (mixed layout, featured/standard cards)
✅ Culture Grid - 10 tests passing (4-column text-only cards)
✅ Navigation - 16 tests passing (sticky header, mobile menu)
✅ Footer - 8 tests passing (5 social icons with WCAG compliance)

Design System Compliance:
✅ All section headings: Uppercase, Off-White (#FAFAFA)
✅ All hover states: Electric Blue (#0084FF) borders
✅ All metadata: Brutal Grey (#AAAAAA)
✅ All social icons: 44px touch targets, ARIA labels
✅ All interactive elements: Keyboard accessible, focus indicators

Known Issues:
- Some tests fail on staging due to environment differences (admin auth, REST API)
- Visual regression tests need baseline regeneration for staging
- Hero section pattern exists but not currently in index.html

Next Steps:
- Monitor production deployment for any issues
- Consider FAQ schema generation for Q&A content
- Implement grayscale image filters for brutalist aesthetic
- Custom scrollbar styling for video rail
- Clean up test suite for staging compatibility

=================================================

1. Project Identity
Name: Gay Car Boys (GCB) Modernization
Platform: High-End Digital Magazine (News & Culture)
Aesthetic: "Editorial Brutalism" // High Fashion x Automotive x Queer Culture.
Content Mix:
News: Industry updates, sales figures, manufacturer announcements.
Retrospectives: Deep dives into history (e.g., Porsche legacy).
Videos: YouTube-first content integrated seamlessly.
Voice: Authoritative, Witty, Unapologetic, "Vogue Man meets Top Gear."

2. Technical Stack
Core: WordPress 6.7+ (Full Site Editing).
Environment: WordPress Studio (WASM / SQLite).
Frontend: Custom Block Theme. Vanilla JS (ES6+). NO jQuery.
Testing: Playwright (E2E).
Schema: JSON-LD NewsArticle and VideoObject (Primary).

3. Design Tokens (Editorial Brutalism)
Do not invent colors. Use this system.
Palette "Neon Noir"
Name Hex Usage Contrast Ratio
Void Black #050505 Main Background (OLED Black)
Off-White #FAFAFA Body Text 19.8:1 (Max readability)
Electric Blue #0084FF Primary CTA, Play Buttons, Accents 5.6:1
Brutal Border #333333 Borders, Dividers (darker than Chrome)
Brutal Grey #AAAAAA Secondary Text, Meta Data ~10:1 (WCAG AAA)

Typography
Headings: Playfair Display (Serif). Usage: Massive sizes, italicized for "Feature" stories.
Body: Inter or System Sans. Usage: Clean, legible, high contrast.
Meta/UI: Space Mono or JetBrains Mono. Usage: Dates, Author names, Video timestamps.

Accessibility (WCAG 2.2 Level AA Compliance)
All text contrast ratios meet or exceed 4.5:1 (AA standard for normal text, 3:1 for large text).
Our implementation exceeds AA requirements:
- Off-White on Void Black: 19.8:1 (exceeds AAA 7:1)
- Brutal Grey on Void Black: ~10:1 (exceeds AAA 7:1)
- Electric Blue on Void Black: 5.6:1 (exceeds AA 4.5:1)

Touch Targets (WCAG 2.2 Success Criterion 2.5.8 - Level AA):
- Minimum 44px × 44px for ALL interactive elements (exceeds 24px AA requirement)
- 24px minimum spacing between targets on mobile
- Applies to buttons, links, form controls, custom controls

Focus States (WCAG 2.2 Success Criterion 2.4.13 - Level AA):
- 2px solid Electric Blue outline with 2px offset
- Visible on ALL interactive elements (no outline: none)
- Focus indicator contrast ratio ≥ 3:1
- Keyboard navigation: Tab order follows visual order

Screen Reader Support:
- Semantic HTML5 elements (nav, main, article, aside)
- ARIA labels for icon-only buttons (e.g., hamburger menu)
- ARIA-live regions for dynamic content updates
- Skip navigation link for keyboard users

Color Independence:
- Never rely solely on color to convey information
- Underlines on text links (not just color)
- Icon + text labels for buttons where possible
- Pattern/texture differentiation where needed

Mobile-First Responsive Design:
- Design for mobile (320px) first, then scale up
- Breakpoints: Mobile (<768px), Tablet (768-1024px), Desktop (>1024px)
- Touch-friendly interactions (no hover-dependent critical features)
- Viewport meta tag: width=device-width, initial-scale=1
- Test on real devices (iOS Safari, Android Chrome)

UI Patterns
Borders: 1px solid Brutal Border (#333) or Electric Blue (#0084FF). Visible grid lines.
No Ratings: We do not score cars. We discuss them.
No Sales: No "Buy Now" buttons. Focus on "Read" and "Watch".
Grayscale Images: filter: grayscale(100%) contrast(1.3) for brutalist aesthetic.
No Transitions: Instant state changes (transition: none) except mobile menu.
Sticky Navigation: Top bar remains visible on scroll with 2px shadow.
Mobile Menu: Slide-out drawer (256px) with overlay and body scroll lock.

4. Coding Standards & GEO (Generative Engine Optimization)
PHP / WordPress
Prefix all functions/classes with gcb_.
Use theme.json for all global styles. Do not hardcode colors in CSS files.
Security: Escape ALL output (esc_html, esc_url, esc_attr).
GEO / AI Optimization
Entity-First: Use specific nouns (e.g., "Porsche 911 GT3") in headers, not generic terms ("The Car").
Structure:
News Posts: Must use NewsArticle schema.
Video Posts: Must use VideoObject schema with uploadDate and duration.
Q&A Injection: Automatically detect questions in H2s and generate FAQ Schema for AI answer engines.

5. Directory Structuretext
/wp-content
/themes
/gcb-brutalist
/parts (Headers, Footers)
/templates
- index.html (Home)
- single.html (Standard News)
/patterns (Video Rail, Hero Section, Culture Grid)
theme.json
/plugins
/gcb-test-utils (REQUIRED: Handles DB reset)

⚠️ CRITICAL PROTOCOL: MULTI-ENVIRONMENT TESTING
Environment Differences:
The site operates across three environments with different configurations:
1. LOCAL (WordPress Studio): Full plugin access, WASM/SQLite
2. STAGING (WP.com): Managed WordPress, limited plugins, may differ from local
3. PRODUCTION: Final deployment environment

Note: All content has been migrated to Gutenberg blocks (January 2026).
Legacy Fusion Builder fallback code has been removed from the theme.

Cross-Environment Testing Protocol:
NEVER assume features work the same across environments. Test on BOTH local AND staging:
1. Develop and test locally using WordPress Studio
2. Commit changes to Git
3. Deploy to staging
4. Clear ALL caches (browser, WordPress, object cache, CDN)
5. Test identical functionality on staging
6. Document any environment-specific differences

Debugging Environment Differences:
When features work locally but fail on staging:
1. Check plugin activation status (class_exists(), shortcode_exists())
2. Verify theme files deployed correctly (check functions.php timestamps)
3. Clear multiple cache layers (browser, WP transients, object cache, page cache)
4. Use diagnostic tools to compare environments (see staging-diagnostic.php template)
5. Check for CDN/proxy interference (WP.com Photon, Jetpack modules)
6. Verify database content matches (post_content field for legacy shortcodes)

Cache Clearing Checklist:
When deploying fixes to staging/production:
- ✓ Browser cache (Cmd+Shift+R / Ctrl+Shift+F5)
- ✓ WordPress transients (wp_cache_flush())
- ✓ Object cache (Redis/Memcached flush)
- ✓ Page cache (WP Super Cache, W3 Total Cache, WP Rocket)
- ✓ CDN cache (Cloudflare, WP.com CDN)
- ✓ OPcache (PHP opcache_reset() if available)

Common Environment-Specific Issues:
- Plugins active locally but not on staging
- File permissions differ (wp-content/uploads writability)
- PHP version differences (8.3 local vs 8.1 staging)
- Database differences (SQLite local vs MySQL staging)
- URL rewriting rules (.htaccess vs nginx)
- SSL/HTTPS enforcement differences

⚠️ CRITICAL: CSS Property Compatibility with WordPress
Some modern CSS properties can cause syntax errors when WordPress renders templates:
- aspect-ratio: 16/9 - `/` character gets mangled, breaks JavaScript parser
- Use padding-bottom trick instead: padding-bottom: 56.25% for 16:9 ratio
- Always test new CSS properties on staging, not just in browser
- A single CSS syntax error can break ALL JavaScript site-wide
- Check browser console for "Uncaught SyntaxError" after CSS changes

⚠️ CRITICAL PROTOCOL: FRONTEND ERROR DETECTION & QUALITY GATES
Added: January 12, 2026

Console Error Capture:
Tests using `@utils/fixtures` automatically fail on JavaScript console errors.
- Console errors (console.error)
- Uncaught exceptions (pageerror)
- Failed network requests (requestfailed)
- Ignores analytics/third-party failures automatically

To disable for specific test:
```typescript
import { test } from '@utils/fixtures';
test.use({ failOnConsoleError: false });
```

Required Quality Checks for ALL Changes:
BEFORE marking any task complete, verify:
1. Console Errors: Run `npm run debug:ui` and check browser console
2. Performance: Run `npm run test:perf` - all Core Web Vitals must pass
3. Security: Run `npm run test:security` - headers and CSP validated
4. Accessibility: Run `npm run test:a11y` - axe-core WCAG 2.2 AA audit

Debug Commands:
| Command | Use Case |
|---------|----------|
| `npm run debug` | Open Playwright Inspector for step-through debugging |
| `npm run debug:ui` | Interactive test runner with live browser preview |
| `npm run debug:slow` | Slowed execution (500ms delay) for visual debugging |
| `npm run debug:trace` | Full trace capture for post-mortem analysis |

Visual Regression Testing:
Baselines stored in `tests/visual-baselines/`.
- Update baselines: `npm run test:visual:update`
- Compare current: `npm run test:visual`
- Mask dynamic content (dates, view counts) to prevent false failures

Performance Thresholds (Core Web Vitals):
| Metric | Threshold | Description |
|--------|-----------|-------------|
| LCP | < 2500ms | Largest Contentful Paint |
| FCP | < 1800ms | First Contentful Paint |
| CLS | < 0.1 | Cumulative Layout Shift |
| TTFB | < 800ms | Time to First Byte |

Security Headers Required:
| Header | Valid Values |
|--------|--------------|
| X-Frame-Options | DENY, SAMEORIGIN |
| X-Content-Type-Options | nosniff |
| X-XSS-Protection | 1; mode=block |
| Referrer-Policy | strict-origin-when-cross-origin |

Test Categories:
| Category | Command | Test Count |
|----------|---------|------------|
| Performance | `npm run test:perf` | 8 tests |
| Security | `npm run test:security` | 11 tests |
| Accessibility | `npm run test:a11y` | 12 tests |
| Visual Regression | `npm run test:visual` | 12 tests |
| All New Tests | Combined | 43 tests |

Utility Files (tests/utils/):
- fixtures.ts: Console error capture with auto-fail
- performance.ts: Core Web Vitals measurement
- security.ts: Header validation and XSS checks
- accessibility.ts: axe-core WCAG 2.2 AA wrapper
- visual-regression.ts: Screenshot comparison
- debug-helpers.ts: Interactive debugging tools

6. Pattern Specifications

Video Rail Pattern:
- Horizontal scroll with scroll-snap behavior
- Grayscale thumbnails with high contrast (filter: grayscale(100%) contrast(1.3))
- Massive acid lime play triangle (64-80px responsive)
- Metadata display: Duration + View count (e.g., "12:45 • 245K Views")
- "View All →" link in section header (44px touch target)
- Custom scrollbar: 6px height with acid lime accent
- Query: Filters by taxonomy content_format = video
- Accessibility: Keyboard navigation with arrow keys, ARIA labels on play buttons

Bento Grid Pattern (Featured Stories):
- 7 posts total: 1 hero (full row) + 6 standard cards
- Hero card: Spans full width (grid-column: 1 / -1), 16:9 image ratio (640px desktop)
- Hero excerpt: Full excerpt (no truncation)
- Standard cards: 3-column grid on desktop, 240px image height, 55-word excerpts
- Mobile-First Responsive:
  - <768px: Single column, all cards full width
  - 768-1024px: Two columns, hero spans full width
  - >1024px: Three columns, hero spans full row
- Image heights: Desktop 640px hero / 240px standard, Tablet 430px / 220px, Mobile 220px / 200px
- Metadata: Date + "Article" badge
- Border: 2px solid Brutal Border (#333), hover changes to Highlight
- Query: Latest 7 posts
- Accessibility: Focus states on all links, semantic HTML5

Hero Section Pattern (Not Currently in Use):
⚠️ NOTE: This pattern exists in the codebase but is **not currently included in index.html**. Pattern retained for potential future use.
- Two-column layout using 3-column CSS Grid (feature card spans 2 columns)
- Category badge: Acid lime border for FEATURE, brutal-border for OPINION
- Dark gradient overlay: from-void-black via-void-black/70 to-transparent
- Metadata: Author + Date + Read Time (e.g., "Read Time: 8 min")
- Opinion card: 256px total height (includes image + content)
- Mobile-First Responsive:
  - <768px: Single column, feature card full width
  - 768-1024px: Two columns, reduced heights
  - >1024px: Full desktop layout
- Query: Latest post or manually featured post via meta
- Accessibility: Focus states on all links, semantic HTML5 (article elements)

Culture Grid Pattern (Latest Reviews & News):
- Mobile-First Grid: 1 col mobile (<768px), 2 cols tablet (768-1024px), 4 cols desktop (>1024px)
- Text-only cards (NO images) for high information density
- Category label: Acid lime text (Technology, Safety, News, Lifestyle), uppercase mono
- Playfair headline: text-2xl desktop, text-xl mobile (never smaller than 20px)
- Mono font excerpt: Brutal Grey color, 55 words max, 16px minimum size
- Date only (no author displayed on these cards)
- Card padding: 24px (adequate touch target spacing)
- Border: 1px solid Brutal Border (#333), hover/focus changes to Electric Blue
- Grid columns use minmax(0, 1fr) to ensure equal widths regardless of content
- Query: Standard posts, offset by 7 (starts at article 8), exclude videos
- Accessibility: Full keyboard navigation, focus indicators on all cards, semantic article tags
