# Design System Reference - GayCarBoys Editorial Brutalism

**Created from North Star Prototype (December 31, 2025)**

This section documents the complete design system established through the HTML prototype. All implementations must follow these specifications exactly.

---

## 6. Color Palette (Neon Noir) - Accessibility Compliant

**CRITICAL:** All color usage must meet WCAG AAA standards (7:1+ contrast ratio).

| Color Name | Hex | Usage | Contrast Ratio | WCAG Level |
|------------|-----|-------|----------------|------------|
| **Void Black** | `#050505` | Main background (OLED black) | - | - |
| **Hyper White** | `#FAFAFA` | Primary text, headlines | 19.8:1 | AAA ✓ |
| **Acid Lime** | `#CCFF00` | Primary CTA, play buttons, accents, hover states | 18.2:1 | AAA ✓ |
| **Brutal Grey** | `#999999` | Secondary text, metadata, timestamps, author names | 8.6:1 | AAA ✓ |
| **Chrome** | `#C0C0C0` | **Borders ONLY** (not for text) | - | - |

### Color Usage Rules:

✅ **DO:**
- Use Hyper White for all headlines and primary body text
- Use Brutal Grey for metadata, dates, author names, view counts
- Use Acid Lime for interactive elements (buttons, links on hover, play icons)
- Use Chrome for borders and dividers only

❌ **DON'T:**
- Use Chrome (#C0C0C0) for text (fails WCAG - only 1.9:1 contrast)
- Invent new colors outside this palette
- Use semi-transparent colors for text

### Accessibility Requirements:

- **WCAG AAA Compliance:** All text must have 7:1+ contrast ratio
- **Color-blind friendly:** Don't rely on color alone to convey information
- **Focus states:** 2px solid Acid Lime outline, 2px offset
- **Interactive elements:** Must pass color contrast for both normal and hover states

---

## 7. Typography System

### Font Families:

```css
/* theme.json mapping */
"fontFamily": {
  "playfair": ["Playfair Display", "serif"],
  "mono": ["Space Mono", "monospace"],
  "sans": ["Inter", "system-ui", "sans-serif"]
}
```

### Typography Scale (Responsive):

| Element | Mobile (< 640px) | Tablet (640-768px) | Desktop (768px+) | Usage |
|---------|------------------|-------------------|------------------|-------|
| **Feature Headlines** | text-2xl (24px) | text-3xl → text-4xl | text-5xl → text-6xl (60px) | Playfair Display, font-bold |
| **Section Titles** | text-3xl (30px) | text-4xl (36px) | text-5xl (48px) | Playfair Display, font-bold |
| **Card Headlines** | text-2xl (24px) | text-2xl | text-3xl (30px) | Playfair Display, font-bold |
| **Video Titles** | text-base (16px) | text-lg (18px) | text-lg | Playfair Display, font-bold |
| **Body Text** | text-sm (14px) | text-base (16px) | text-base | Inter/System Sans |
| **Metadata/UI** | text-xs (12px) | text-xs | text-xs (fixed) | Space Mono, uppercase, tracking-wider |

### Typography Rules:

✅ **Playfair Display (Serif):**
- All headlines and article titles
- Font weights: 400 (regular), 700 (bold), 900 (black)
- Italicize for "Feature" story headlines
- Massive sizes encouraged (Editorial Brutalism principle)

✅ **Space Mono (Monospace):**
- Author names, dates, timestamps
- Video metadata (duration, view counts)
- Navigation links
- Category labels
- Always: `uppercase tracking-wider text-xs`

✅ **Inter/System Sans:**
- Body copy only
- Article excerpts
- Description text

### Font Rendering (Critical):

**REQUIRED CSS for all implementations:**

```css
body {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
}
```

**Why this matters:**
- Ensures consistent font rendering across browsers (especially Safari/macOS)
- Prevents jagged/pixelated text on dark backgrounds
- Critical for Editorial Brutalism aesthetic with Void Black (#050505) background
- Mobile browsers render fonts smoothly by default, but desktop browsers need explicit hints

**WordPress Implementation:**
- Add to `style.css` in theme
- Apply globally to `body` element
- Test on Chrome, Safari, Firefox, Edge to verify crisp rendering

---

## 8. Core Layout Components (Required Patterns)

### Pattern 1: Ticker Nav (Sticky)

**Location:** Top of all pages, sticky positioned

**Structure:**
```
[GCB Logo] [Car Reviews] [Car News] [Brands] [>_ SEARCH] [≡ Menu]
```

**Specifications:**
- Height: 64px (h-16)
- Background: Void Black
- Border bottom: 2px solid Chrome
- Box shadow: `0 2px 0 #333333`
- Z-index: 50 (always on top)
- Mobile: Hide nav links, show hamburger menu

**Navigation Links:**
- Car Reviews
- Car News
- Brands
- (No separate "Electric Cars" menu - integrate into Car Reviews)

**Search Button:**
- Terminal-style: `>_ SEARCH`
- Border: 1px solid Chrome
- Hover: Border changes to Acid Lime
- Hidden on mobile < 640px (moved to mobile menu)

**Mobile Menu:**
- Slide-out drawer from left (256px wide)
- Dark overlay on open (opacity: 50%)
- Closes on: link click, overlay tap, ESC key
- Body scroll disabled when open
- Touch targets: 44px minimum height

---

### Pattern 2: Video Rail (Horizontal Scroll)

**Critical:** This is PRIMARY content placement, not secondary.

**Specifications:**
- Section title: "LATEST VIDEOS" (Playfair Display, text-3xl → text-5xl)
- Layout: Horizontal scroll (overflow-x-auto)
- Gap: 16px (gap-4)
- Custom scrollbar: 6px height, Chrome with Acid Lime accent

**Video Cards:**
- **Aspect ratio:** 9:16 (TikTok-style vertical)
- **Width:**
  - Mobile: w-56 (224px)
  - Tablet: w-64 (256px)
  - Desktop: w-72 (288px)
- **Border:** 1px solid Chrome
- **Hover:** Border changes to Acid Lime
- **Image:** Grayscale filter (100%) with 1.3x contrast

**Play Button (CRITICAL):**
- SVG triangle, filled Acid Lime (#CCFF00)
- Size: w-16 h-16 (mobile) → w-20 h-20 (desktop)
- Position: Absolute centered
- Drop shadow for visibility

**Video Metadata:**
- Title: Playfair Display, text-base → text-lg
- Duration + Views: Space Mono, text-xs, Brutal Grey
- Format: `12:45 • 245K Views`
- Position: Bottom overlay with gradient

---

### Pattern 3: News Hero (Mixed Media Grid)

**Layout:** 3-column grid on desktop, stacked on mobile

**Feature Card (2 columns):**
- Height: 384px (mobile) → 500px (desktop)
- Image: Full coverage with gradient overlay
- Category badge: Border 1px Acid Lime, text Acid Lime
- Headline: text-2xl → text-6xl (Playfair, bold)
- Meta: Author, Date, **Read Time** (Acid Lime)
- Separators: 1px vertical lines (Brutal Grey)

**Opinion/Culture Card (1 column):**
- Smaller format (complementary content)
- Image: 256px height
- Category badge: Border Chrome, hover Acid Lime
- Headline: text-2xl → text-3xl
- Excerpt: font-mono, Brutal Grey

---

### Pattern 4: Culture Grid (Dense Layout)

**Grid Configuration:**
- Mobile: 1 column
- Tablet: 2 columns (640px+)
- Desktop: 4 columns (1024px+)
- Gap: 24px (gap-6)

**Card Structure:**
- Border: 1px solid Chrome
- Hover: Border changes to Acid Lime
- Padding: 24px (p-6)
- Min-height: Auto (content-driven)

**Card Content:**
- Category label: text-xs uppercase, Acid Lime
- Headline: text-2xl, Playfair Display, bold
- Body text: text-sm, font-mono, Brutal Grey
- Meta: Border top Chrome, text-xs, Brutal Grey

**Content Categories:**
- Technology
- Safety
- News
- Lifestyle
- Culture

---

### Pattern 5: Footer (Minimalist)

**Structure:**
- Border top: 2px solid Chrome
- Padding: py-12
- Layout: Flexbox (stacked on mobile, row on desktop)

**Elements:**
- Logo: "GCB" (Playfair Display, text-5xl, font-black)
- Tagline: "Gay Car Reviews & Gay Lifestyle" (Hyper White, text-xs, uppercase)
- Subtitle: "LGBTQ+ Car Reviews, News & Culture Since 2008" (Brutal Grey)
- Social icons: YouTube, Instagram, Twitter/X (32px, hover to Acid Lime)
- Copyright: Brutal Grey, text-xs

---

## 9. Mobile Optimization Requirements

### Touch Targets (44px Minimum):

**Apple Human Interface Guidelines compliance:**
- All navigation links: min-height 44px
- Mobile menu links: py-3, pb-4 (extra padding)
- Buttons: touch-target class
- Hamburger menu: 44px × 44px
- Video cards: Full card is touch target

### Responsive Breakpoints:

```css
/* Tailwind breakpoints */
sm:  640px  /* Tablet start */
md:  768px  /* Desktop start */
lg:  1024px /* Large desktop */
xl:  1280px /* Extra large */
max-w-7xl: 1400px /* Content width cap */
```

### Mobile Menu Pattern:

**Required features:**
- Slide-out drawer (256px width)
- Overlay backdrop (50% opacity Void Black)
- Hamburger → X icon transition
- Close triggers: link click, overlay tap, ESC key, desktop resize
- Prevent body scroll when open
- 44px touch targets for all links

### Responsive Images:

- Always apply grayscale filter: `filter: grayscale(100%) contrast(1.3)`
- Use object-cover for all images
- Lazy loading for performance

---

## 10. Brutalist Principles (Hard Rules)

### NO Smooth Transitions:
```css
* {
  transition: none !important;
}
```
Exception: Mobile menu slide-out (0.3s ease) - UX requirement

### NO Rounded Corners:
- All borders are sharp 90° angles
- No border-radius anywhere
- Cards, buttons, images: all square

### Visible Grid Lines:
- 1-2px solid borders on ALL grid items
- Chrome (#C0C0C0) for default borders
- Acid Lime (#CCFF00) for hover/active states
- Borders define visual hierarchy

### Offset Rhythm:
- Archive grids: Every 2nd card offset 50px vertically
- Creates visual tension (Brutalist aesthetic)
- Tablet: Every 2nd card (2 columns)
- Desktop: Every 2nd card (3 columns)

### Typography Brutalism:
- Massive headline sizes (text-6xl, text-9xl)
- High contrast (19.8:1 ratio)
- Monospace for all UI elements
- No text shadows or gradients

### Image Treatment:
- All images: grayscale(100%) + contrast(1.3)
- No color photography (except in original media embeds)
- High contrast black and white aesthetic

---

## 11. Content Strategy & Types

### Primary Content Categories:

**Reviews (Featured):**
- "Top 13 LGBTQ+ Electric Cars"
- In-depth vehicle reviews
- Comparative analysis
- Read time: 5-15 minutes

**Culture:**
- "What Makes a Car an LGBTQ+ Gay Car?"
- Community identity pieces
- Design philosophy
- Lifestyle connections

**Technology:**
- "Top 10 Favourite Gay Car Boys Technologies"
- Feature deep-dives
- Innovation coverage
- Accessibility tech

**Safety:**
- "ANCAP 5-Star Safety Ratings"
- Crash test results
- Family-friendly reviews
- Safety-first analysis

**Lifestyle:**
- "What Cars Do Gay Boys Drive?"
- Community surveys
- Trend reports
- Cultural observations

### Video-First Strategy:

- Videos are **primary content**, not supplementary
- YouTube integration mandatory
- 9:16 aspect ratio preferred (mobile-first)
- Metadata required: duration, view count, upload date
- VideoObject schema (JSON-LD) on all video content

### Content Voice:

From original spec: "Authoritative, Witty, Unapologetic. Vogue Man meets Top Gear."

**Specific guidelines:**
- Use specific entity names: "Porsche 911 GT3" not "the car"
- No ratings/scores (we discuss, not score)
- No "Buy Now" buttons (editorial, not commerce)
- Question-based H2s for FAQ schema generation
- Community-focused language

---

## 12. WordPress Implementation Notes

### theme.json Structure:

```json
{
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        {"slug": "void-black", "color": "#050505", "name": "Void Black"},
        {"slug": "hyper-white", "color": "#FAFAFA", "name": "Hyper White"},
        {"slug": "acid-lime", "color": "#CCFF00", "name": "Acid Lime"},
        {"slug": "brutal-grey", "color": "#999999", "name": "Brutal Grey"},
        {"slug": "chrome", "color": "#C0C0C0", "name": "Chrome"}
      ]
    },
    "typography": {
      "fontFamilies": [
        {"fontFamily": "\"Playfair Display\", serif", "slug": "playfair"},
        {"fontFamily": "\"Space Mono\", monospace", "slug": "mono"},
        {"fontFamily": "\"Inter\", system-ui, sans-serif", "slug": "sans"}
      ]
    },
    "layout": {
      "contentSize": "1200px",
      "wideSize": "1400px"
    }
  }
}
```

### Block Patterns Directory:

```
/wp-content/themes/gcb-magazine/patterns/
├── ticker-nav.php
├── video-rail.php
├── news-hero.php
├── culture-grid.php
└── footer.php
```

### Custom CSS (style.css):

**Only for Brutalist effects that theme.json can't handle:**
- Grayscale image filters
- Grid offset rhythm
- Custom scrollbar styling
- Focus state outlines

**DO NOT:** Hardcode colors in CSS (use theme.json)

---

## 13. Schema.org Requirements

### NewsArticle (Standard Posts):

```json
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "Specific Entity Name (e.g., Tesla Model 3)",
  "author": {"@type": "Person", "name": "Author Name"},
  "datePublished": "ISO 8601 format",
  "image": "Featured image URL",
  "publisher": {
    "@type": "Organization",
    "name": "Gay Car Boys",
    "logo": "Logo URL"
  }
}
```

### VideoObject (Video Posts):

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Video title",
  "description": "Video description",
  "uploadDate": "ISO 8601 format",
  "duration": "PT12M45S",
  "thumbnailUrl": "Thumbnail URL",
  "embedUrl": "YouTube embed URL"
}
```

### FAQ Schema (Auto-generated):

- Detect question-based H2 headings
- Generate FAQPage schema automatically
- Optimizes for AI answer engines (ChatGPT, Perplexity, etc.)

---

## 14. Performance & Optimization

### Image Optimization:

- Lazy loading: `loading="lazy"` on all images
- WebP format preferred
- Responsive images: srcset for mobile/tablet/desktop
- Grayscale filter: CSS (not image processing)

### CSS Delivery:

- Critical CSS inlined (navigation, above-fold content)
- Defer non-critical CSS
- No external CSS frameworks (except Google Fonts)
- Minimal custom CSS (Brutalism = minimal styling)

### JavaScript:

- Vanilla ES6+ only (NO jQuery)
- Mobile menu: ~50 lines (vanilla)
- Defer all non-critical scripts
- No animation libraries (instant state changes)

### WordPress Performance:

- Object caching recommended
- Lazy load embeds (YouTube iframes)
- Disable unused WordPress features
- Limit post revisions (3 max)

---

## 15. Testing & Quality Assurance

### Accessibility Testing:

- **Color contrast:** All text must pass WCAG AAA (7:1+)
- **Keyboard navigation:** Tab through all interactive elements
- **Screen reader:** Test with VoiceOver/NVDA
- **Focus indicators:** Visible 2px Acid Lime outlines

### Responsive Testing:

**Required viewports:**
- iPhone SE: 375px
- iPhone 14 Pro: 390px
- iPhone 14 Pro Max: 414px
- iPad Mini: 768px
- Desktop: 1400px

### Browser Testing:

- Chrome (primary)
- Safari (iOS critical)
- Firefox
- Edge

### Performance Targets:

- Lighthouse score: 90+ (mobile & desktop)
- First Contentful Paint: < 1.5s
- Largest Contentful Paint: < 2.5s
- Cumulative Layout Shift: < 0.1

---

## 16. Design System Governance

### When to Deviate:

**NEVER deviate from:**
- Color palette (accessibility requirement)
- Touch target sizes (44px minimum)
- WCAG AAA compliance
- Brutalist principles (no transitions, no rounded corners)

**Architect approval required for:**
- New UI patterns
- Typography changes
- Navigation structure changes
- New content types

### Design System Updates:

- All updates documented in this file
- Prototype changes first (HTML)
- Test accessibility before implementation
- Update CLAUDE.md when patterns change

---

**End of Design System Reference**

*This design system was established through the North Star HTML prototype and represents the complete visual and technical specification for the GayCarBoys modernization project.*
