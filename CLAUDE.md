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
Acid Lime #CCFF00 Primary CTA, Play Buttons, Accents 18.2:1
Brutal Border #333333 Borders, Dividers (darker than Chrome)
Brutal Grey #999999 Secondary Text, Meta Data 8.6:1 (WCAG AAA)

Typography
Headings: Playfair Display (Serif). Usage: Massive sizes, italicized for "Feature" stories.
Body: Inter or System Sans. Usage: Clean, legible, high contrast.
Meta/UI: Space Mono or JetBrains Mono. Usage: Dates, Author names, Video timestamps.

Accessibility (WCAG AAA)
All text contrast ratios meet or exceed 7:1 (AAA standard).
- Off-White on Void Black: 19.8:1 (maximum readability)
- Brutal Grey on Void Black: 8.6:1 (secondary text)
- Acid Lime on Void Black: 18.2:1 (accent text)
Touch Targets: Minimum 44px × 44px for mobile UX.
Focus States: 2px solid Acid Lime outline with 2px offset.
Color-blind Friendly: No red/green reliance in critical UI elements.

UI Patterns
Borders: 1px solid Brutal Border (#333) or Acid Lime (#CCFF00). Visible grid lines.
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

6. Pattern Specifications

Video Rail Pattern:
- Aspect ratio: 9:16 (portrait/vertical format for mobile-first)
- Horizontal scroll with scroll-snap behavior
- Grayscale thumbnails with high contrast (filter: grayscale(100%) contrast(1.3))
- Massive acid lime play triangle (64-80px)
- Metadata display: Duration + View count (e.g., "12:45 • 245K Views")
- "View All →" link in section header
- Custom scrollbar: 6px height with acid lime accent
- Query: Filters by taxonomy content_format = video

Hero Section Pattern:
- Two-column layout using 3-column CSS Grid (feature card spans 2 columns)
- Feature card: 500px height, massive headline (up to text-6xl on desktop)
- Category badge: Acid lime border, uppercase mono font
- Dark gradient overlay: from-void-black via-void-black/70 to-transparent
- Metadata: Author + Date + Read Time (e.g., "Read Time: 8 min")
- Opinion card: 256px height, smaller headline (text-3xl)
- Responsive: Stacks to single column on mobile (<768px)
- Query: Latest post or manually featured post via meta

Culture Grid Pattern:
- 4-column responsive grid (1 col mobile, 2 tablet, 4 desktop)
- Text-only cards (NO images) for high information density
- Category label: Acid lime text (Technology, Safety, News, Lifestyle)
- Large Playfair headline (text-2xl)
- Mono font excerpt (Brutal Grey color)
- Date only (no author displayed on these cards)
- Border: 1px solid Brutal Border (#333), hover changes to Acid Lime
- Query: Standard posts, exclude videos
