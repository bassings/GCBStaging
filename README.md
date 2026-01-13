# Gay Car Boys - Editorial Brutalism Redesign

WordPress block theme implementation for [gaycarboys.com](https://gaycarboys.com) featuring an "Editorial Brutalism" design system (High Fashion x Automotive x Queer Culture).

**Status:** Migration Complete | 81+ E2E Tests Passing | WCAG 2.2 AA Compliant

---

## Project Overview

This project modernizes the Gay Car Boys digital magazine from Avada/Fusion Builder to native WordPress Gutenberg blocks, eliminating jQuery dependencies and implementing a custom brutalist design system.

### Key Achievements

- **3,891 posts** successfully migrated to Gutenberg blocks
- **164,204 shortcodes** converted to native blocks
- **100% WCAG 2.2 Level AA** accessibility compliance
- **5 custom patterns** fully implemented with TDD
- **YouTube Channel API** integration for video content

---

## Development Environment

### Prerequisites

- [WordPress Studio](https://developer.wordpress.com/studio/) installed and running
- Node.js (LTS 18+) + npm
- Composer (for PHP tooling)

### Getting Started

1. **Start WordPress Studio** and ensure the site is accessible at `http://localhost:8881`

2. **Install dependencies:**

```bash
npm install
composer install
```

3. **Run E2E tests:**

```bash
npm test
```

### Environment Details

| Environment | Database | PHP Runtime |
|-------------|----------|-------------|
| Local (WordPress Studio) | SQLite | WebAssembly (WASM) |
| Staging (WP.com) | MySQL | Native PHP |
| Production (WP.com Atomic) | MariaDB | Native PHP |

---

## Testing

This project uses **Playwright** for end-to-end testing with a strict **Test-Driven Development (TDD)** approach.

### Test Commands

| Command | Description |
|---------|-------------|
| `npm test` | Run all Playwright tests |
| `npm run test:ui` | Run tests with Playwright UI |
| `npm run test:headed` | Run tests with visible browser |
| `npm run debug` | Open Playwright Inspector |
| `npm run debug:ui` | Interactive test runner with live preview |
| `npm run test:perf` | Run performance tests (Core Web Vitals) |
| `npm run test:a11y` | Run accessibility tests (WCAG 2.2 AA) |
| `npm run test:security` | Run security header tests |
| `npm run test:visual` | Run visual regression tests |

### Test Coverage

| Category | Tests | Status |
|----------|-------|--------|
| Pattern Tests | 67+ | Passing |
| WCAG Compliance | 14 | Passing |
| Performance | 8 | Environment-dependent |
| Security | 11 | Environment-dependent |
| Accessibility | 12 | Passing |

### TDD Workflow

1. **RED:** Write a Playwright test for the feature, confirm it fails
2. **GREEN:** Write minimum code to make the test pass
3. **REFACTOR:** Optimize code while keeping tests green

---

## Design System: Editorial Brutalism

### Color Palette "Neon Noir"

| Name | Hex | Usage | Contrast |
|------|-----|-------|----------|
| Void Black | `#050505` | Main Background | - |
| Off-White | `#FAFAFA` | Body Text | 19.8:1 |
| Acid Lime | `#CCFF00` | CTAs, Accents | 18.2:1 |
| Brutal Border | `#333333` | Borders, Dividers | - |
| Brutal Grey | `#999999` | Secondary Text | 8.6:1 (AAA) |

### Typography

- **Headings:** Playfair Display (Serif)
- **Body:** Inter or System Sans
- **Meta/UI:** Space Mono or JetBrains Mono

### UI Patterns

- Borders: 1px solid with high contrast
- Images: `filter: grayscale(100%) contrast(1.3)`
- Transitions: Instant state changes (`transition: none`)
- Touch targets: 44px minimum (exceeds WCAG 24px requirement)

---

## Theme Patterns

### Implemented Patterns

| Pattern | Description | Tests |
|---------|-------------|-------|
| **Video Rail** | Horizontal scrolling YouTube thumbnails via API | 16 |
| **Bento Grid** | Mixed layout grid with featured cards | 8 |
| **Culture Grid** | 4-column text-only editorial cards | 10 |
| **Navigation** | Sticky header + mobile slide-out menu | 16 |
| **Footer** | 5 social icons with WCAG compliance | 8 |

### Pattern Files

```
wp-content/themes/gcb-brutalist/
├── patterns/
│   ├── video-rail.php          # YouTube channel integration
│   ├── bento-grid.php          # Featured stories grid
│   ├── culture-grid.php        # Text-only editorial cards
│   ├── header-navigation.php   # Sticky nav + mobile menu
│   └── hero-section.php        # Feature/opinion cards (optional)
├── templates/
│   ├── index.html              # Homepage layout
│   ├── single.html             # Article template
│   ├── search.html             # Search results
│   └── date.html               # Year archive
└── parts/
    ├── header.html
    └── footer.html
```

---

## Plugins

### Custom Plugins

| Plugin | Purpose |
|--------|---------|
| `gcb-content-intelligence` | Content classification, YouTube API, Schema.org |
| `gcb-test-utils` | E2E testing utilities, database reset endpoint |

### Content Intelligence Features

- YouTube URL detection (3 regex patterns)
- `content_format` taxonomy (video, standard, gallery)
- YouTube Data API v3 integration with caching
- Schema.org JSON-LD generation (VideoObject, NewsArticle)
- Avada shortcode converter (fusion_* to Gutenberg blocks)

---

## Deployment

### Syncing to Staging

Use **WordPress Studio Sync** to deploy changes:

1. Open WordPress Studio
2. Select sync direction (Local → Staging)
3. **UNCHECK Database** - only sync files (themes, plugins, uploads)
4. Push changes

### Manual Testing on Staging

After syncing, always:

1. Clear all caches (browser, WordPress, CDN)
2. Test functionality matches local environment
3. Check browser console for JavaScript errors
4. Verify responsive behavior on mobile

---

## Project Structure

```
GCBStaging/
├── wp-content/
│   ├── themes/gcb-brutalist/    # Custom block theme
│   ├── plugins/                  # Custom + third-party plugins
│   └── mu-plugins/               # Must-use plugins
├── tests/
│   ├── e2e/                      # Playwright E2E tests
│   ├── utils/                    # Test utilities
│   └── visual-baselines/         # Visual regression baselines
├── CLAUDE.md                     # AI assistant instructions
├── IMPLEMENTATION-PLAN.md        # Project progress tracking
├── DESIGN-SYSTEM-REFERENCE.md    # Design tokens documentation
└── north-star-prototype.html     # Reference design
```

---

## Documentation

| Document | Purpose |
|----------|---------|
| [CLAUDE.md](./CLAUDE.md) | Development guidelines, TDD protocol, design tokens |
| [IMPLEMENTATION-PLAN.md](./IMPLEMENTATION-PLAN.md) | Project progress, migration status, test results |
| [DESIGN-SYSTEM-REFERENCE.md](./DESIGN-SYSTEM-REFERENCE.md) | Complete design system specification |
| [TESTING.md](./TESTING.md) | Testing setup and commands |

---

## SQLite Compatibility

The local environment uses SQLite via WordPress Studio. When writing code:

- Use `WP_Query` or `$wpdb` methods (they abstract database differences)
- Avoid raw SQL with MySQL-specific functions
- Always use WordPress APIs for database queries

---

## Accessibility (WCAG 2.2 Level AA)

All patterns meet or exceed WCAG 2.2 Level AA requirements:

- **Skip Navigation:** "Skip to main content" link
- **Keyboard Navigation:** Full Tab navigation, ESC to close modals
- **Focus Indicators:** 2px Acid Lime outline on all interactive elements
- **Color Contrast:** All text exceeds 4.5:1 (most achieve AAA 7:1+)
- **Touch Targets:** 44px minimum on all interactive elements
- **ARIA Support:** Labels, roles, states on all components
- **Semantic HTML:** Proper landmarks, heading hierarchy

---

## Legacy Content Support

The theme includes fallback systems for Fusion Builder content:

- `[fusion_youtube]` → WordPress oEmbed
- `[fusion_gallery]` → CSS grid layout with responsive columns
- `[fusion_code]` → Base64 decode with brutalist table styling
- `<lite-youtube>` → CSS/JS for custom YouTube elements

---

## License

Private repository for Gay Car Boys Pty Ltd.

---

**Last Updated:** January 13, 2026
