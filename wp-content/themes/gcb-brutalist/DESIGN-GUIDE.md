# GCB Brutalist Theme Design Guide

**Version:** 1.0
**Last Updated:** January 2026
**Aesthetic:** Editorial Brutalism - High Fashion x Automotive x Queer Culture

---

## Table of Contents

1. [Design Tokens](#design-tokens)
2. [Typography](#typography)
3. [Spacing System](#spacing-system)
4. [Components](#components)
   - [Header Navigation](#header-navigation)
   - [Bento Grid](#bento-grid)
   - [Video Rail](#video-rail)
   - [Culture Grid](#culture-grid)
   - [Category Children Grid](#category-children-grid)
   - [Footer](#footer)
5. [Interactive States](#interactive-states)
6. [Responsive Breakpoints](#responsive-breakpoints)
7. [Accessibility](#accessibility)
8. [CSS Variables Reference](#css-variables-reference)

---

## Design Tokens

### Color Palette: "Neon Noir"

| Name | Hex | CSS Variable | Usage | Contrast Ratio |
|------|-----|--------------|-------|----------------|
| **Void Black** | `#050505` | `--wp--preset--color--void-black` | Main background (OLED Black) | - |
| **Off-White** | `#FAFAFA` | `--wp--preset--color--off-white` | Body text, headings | 19.8:1 |
| **Acid Lime** | `#CCFF00` | `--wp--preset--color--acid-lime` | Primary CTA, play buttons, accents, hover states | 18.2:1 |
| **Brutal Border** | `#333333` | `--wp--preset--color--brutal-border` | Borders, dividers | - |
| **Brutal Grey** | `#999999` | `--wp--preset--color--brutal-grey` | Secondary text, metadata | 8.6:1 (AAA) |

### Usage Rules

- **Never invent colors** - Only use tokens from this palette
- **Background:** Always Void Black
- **Primary Text:** Off-White
- **Metadata/Secondary:** Brutal Grey
- **Interactive Accents:** Acid Lime
- **Borders:** Brutal Border (default), Acid Lime (hover/focus)

---

## Typography

### Font Families

| Role | Font | CSS Variable | Usage |
|------|------|--------------|-------|
| **Headings** | Playfair Display | `--wp--preset--font-family--playfair` | Section titles, card headlines, hero text |
| **Body** | System Sans | `--wp--preset--font-family--system-sans` | Body copy, excerpts |
| **Mono/UI** | Space Mono | `--wp--preset--font-family--mono` | Metadata, dates, labels, navigation |

### Font Sizes

| Name | Size | CSS Variable | Usage |
|------|------|--------------|-------|
| Small | `0.875rem` (14px) | `--wp--preset--font-size--small` | Metadata, labels |
| Medium | `1rem` (16px) | `--wp--preset--font-size--medium` | Body text |
| Large | `1.25rem` (20px) | `--wp--preset--font-size--large` | Card titles |
| X-Large | `2rem` (32px) | `--wp--preset--font-size--x-large` | Featured card titles |
| XX-Large | `3rem` (48px) | `--wp--preset--font-size--xx-large` | Hero headings |

### Typography Specs by Element

| Element | Font | Size | Weight | Line Height | Letter Spacing |
|---------|------|------|--------|-------------|----------------|
| Section Title | Playfair | 2.5rem | 700 | 1.2 | -0.02em |
| Card Headline (Featured) | Playfair | 2rem | 700 | 1.3 | - |
| Card Headline (Standard) | Playfair | 1.25rem-1.5rem | 700 | 1.3 | -0.01em |
| Body/Excerpt | System Sans | 1rem | 400 | 1.6 | - |
| Metadata | Space Mono | 0.75rem | 400 | 1.4 | 0.05em |
| Category Label | Space Mono | 0.75rem | 700 | 1.4 | 0.1em |
| Navigation | Space Mono | 0.875rem-1rem | 400 | 1.4 | 0.05em |

---

## Spacing System

### Preset Spacing Scale

| Preset | Value | CSS Variable | Usage |
|--------|-------|--------------|-------|
| 20 | 0.5rem (8px) | `--wp--preset--spacing--20` | Tight gaps |
| 30 | 1rem (16px) | `--wp--preset--spacing--30` | Card padding, small gaps |
| 40 | 1.5rem (24px) | `--wp--preset--spacing--40` | Section margins |
| 50 | 2rem (32px) | `--wp--preset--spacing--50` | Major section padding |
| 60 | 3rem (48px) | `--wp--preset--spacing--60` | Large vertical spacing |

### Layout Widths

| Type | Width | Usage |
|------|-------|-------|
| Content Size | 1200px | Main content container |
| Wide Size | 1400px | Full-bleed sections |

---

## Components

### Header Navigation

**File:** `patterns/header-navigation.php`

#### Structure
```
header.site-header
├── .header-wrapper (max-width: 1200px)
│   ├── .site-logo
│   │   └── h1/span.logo-text ("GCB")
│   ├── nav.main-nav (desktop only)
│   │   └── ul.menu > li.menu-item > a.nav-link
│   ├── button.search-toggle (desktop only)
│   └── button.menu-toggle (mobile only)
├── .menu-overlay
└── nav.mobile-menu
    └── .mobile-menu-content
```

#### Specifications

| Property | Value |
|----------|-------|
| Position | `sticky`, top: 0 |
| Background | Void Black |
| Border | 2px solid Brutal Border (bottom) |
| Padding | 1rem 1.5rem (mobile), 1.25rem 2rem (desktop) |
| Z-index | 100 (header), 90 (overlay), 95 (menu) |

#### Logo
| Property | Mobile | Desktop |
|----------|--------|---------|
| Font | Playfair Display | Playfair Display |
| Size | 2rem | 2.5rem |
| Weight | 700 | 700 |
| Color | Off-White | Off-White |
| Min-height | 44px | 44px |

#### Navigation Links
| Property | Value |
|----------|-------|
| Font | Space Mono |
| Size | 0.875rem (tablet), 1rem (desktop) |
| Transform | uppercase |
| Letter-spacing | 0.05em |
| Color | Off-White |
| Hover | Acid Lime |

#### Mobile Menu
| Property | Value |
|----------|-------|
| Width | 256px |
| Position | Fixed, left slide-in |
| Transition | 0.3s ease (transform, visibility) |
| Background | Void Black |
| Border-right | 2px solid Brutal Border |

---

### Bento Grid

**File:** `patterns/bento-grid.php`

#### Structure
```
.gcb-bento-grid
├── h2 ("FEATURED STORIES")
├── hr.wp-block-separator
└── .gcb-bento-grid__container
    └── .bento-item.gcb-bento-card (×8)
        ├── a > img.gcb-bento-card__image
        └── div (content)
            ├── h2/h3.gcb-bento-card__title
            ├── p (excerpt - featured only)
            └── .gcb-bento-card__meta
```

#### Section Header
| Property | Value |
|----------|-------|
| Title Font | Playfair Display |
| Title Size | 2.5rem |
| Title Color | Off-White |
| Title Transform | uppercase |
| Separator | 1px solid Brutal Border |

#### Grid Layout
| Breakpoint | Columns | Gap |
|------------|---------|-----|
| Mobile (<768px) | 1 | 2rem |
| Tablet (768-1024px) | 2 | 2rem |
| Desktop (>1024px) | 3 | 2rem |

#### Card Specifications

| Property | Featured | Standard |
|----------|----------|----------|
| Grid Span | 2 columns | 1 column |
| Border | 2px solid Brutal Border | 2px solid Brutal Border |
| Background | Void Black | Void Black |
| Hover Border | Acid Lime | Acid Lime |

#### Card Image Heights
| Breakpoint | Featured | Standard |
|------------|----------|----------|
| Mobile | 280px | 200px |
| Tablet | 320px | 210px |
| Desktop | 350px | 220px |

#### Card Typography
| Element | Featured | Standard |
|---------|----------|----------|
| Title Font | Playfair | Playfair |
| Title Size | 2rem | 1.25rem |
| Title Color | Off-White | Off-White |
| Excerpt | 1rem, Off-White | - |
| Metadata | 0.75rem, Brutal Grey | 0.75rem, Brutal Grey |

---

### Video Rail

**File:** `patterns/video-rail.php`

#### Structure
```
.gcb-video-rail
├── div (header)
│   ├── h2 ("LATEST VIDEOS")
│   └── a ("View All →")
└── .gcb-video-rail__container
    └── .gcb-video-card (×n)
        └── a
            └── .gcb-video-card__aspect
                ├── img (thumbnail)
                ├── div (dark overlay)
                ├── div > svg.video-play-button
                └── div (content overlay)
                    ├── h3.gcb-video-card__title
                    └── p.gcb-video-card__meta
```

#### Section Header
| Property | Value |
|----------|-------|
| Display | flex, justify-content: space-between |
| Title Font | Playfair Display |
| Title Size | 2.5rem |
| "View All" Font | Space Mono, 0.75rem, uppercase |
| "View All" Color | Brutal Grey |

#### Scroll Container
| Property | Value |
|----------|-------|
| Display | flex |
| Gap | 1rem |
| Overflow | scroll-snap-type: x mandatory |
| Scrollbar Height | 6px |
| Scrollbar Track | Void Black |
| Scrollbar Thumb | Brutal Border with Acid Lime border |

#### Card Specifications
| Breakpoint | Width | Aspect Ratio |
|------------|-------|--------------|
| Mobile | 320px | 16:9 (landscape) |
| Tablet (640px+) | 380px | 16:9 (landscape) |
| Desktop (768px+) | 450px | 16:9 (landscape) |

#### Play Button
| Property | Mobile | Desktop (640px+) |
|----------|--------|------------------|
| Size | 4rem (64px) | 5rem (80px) |
| Color | Acid Lime | Acid Lime |
| Shape | Triangle polygon |
| Filter | drop-shadow(0 4px 8px rgba(0,0,0,0.5)) |

#### Content Overlay
| Property | Value |
|----------|-------|
| Position | absolute, bottom: 0 |
| Background | linear-gradient(to top, Void Black, transparent) |
| Padding | 1rem |
| Title Font | Playfair, 1rem, bold |
| Metadata Font | Space Mono, 0.75rem, uppercase |

---

### Culture Grid

**File:** `patterns/culture-grid.php`

#### Structure
```
.culture-grid-section
└── .culture-grid-wrapper
    ├── .culture-grid-header
    │   └── h2.culture-grid-title
    └── .culture-grid-container
        └── article.culture-card (×8)
            └── a.culture-card-link
                ├── .culture-card-category
                ├── h3.culture-card-title
                ├── p.culture-card-excerpt
                └── .culture-card-meta > time
```

#### Section Specifications
| Property | Value |
|----------|-------|
| Background | Void Black |
| Padding | spacing-60 spacing-30 |
| Max-width | 1200px |

#### Header
| Property | Value |
|----------|-------|
| Title | "LATEST REVIEWS & NEWS" |
| Font | Playfair Display, 2.5rem |
| Color | Off-White |
| Transform | uppercase |
| Border-bottom | 1px solid Brutal Border |
| Margin-bottom | spacing-40 |

#### Grid Layout
| Breakpoint | Columns |
|------------|---------|
| Mobile (<768px) | 1 |
| Tablet (768-1024px) | 2 |
| Desktop (>1024px) | 4 |

#### Card Specifications (Text-Only)
| Property | Value |
|----------|-------|
| Background | Void Black |
| Border | 1px solid Brutal Border |
| Padding | spacing-30 |
| Min-height | 200px |
| Hover Border | Acid Lime |
| **NO IMAGES** | Images hidden |

#### Card Typography
| Element | Font | Size | Color |
|---------|------|------|-------|
| Category | Space Mono | 0.75rem | Acid Lime |
| Title | Playfair | 1.5rem | Off-White |
| Excerpt | Space Mono | 0.875rem | Brutal Grey |
| Date | Space Mono | 0.75rem | Brutal Grey |

---

### Category Children Grid

**File:** `patterns/category-children.php`

#### Structure
```
.category-children-grid
├── div (header)
│   └── h2 ("Browse by Brand")
└── .brands-grid
    └── a.brand-card (×n)
        ├── div (category name)
        └── div (count)
```

#### Header
| Property | Value |
|----------|-------|
| Title | "Browse by Brand" |
| Font | Playfair Display, 2rem (should be 2.5rem) |
| Color | Off-White |
| Transform | uppercase |
| Border-bottom | 2px solid Acid Lime |

#### Grid Layout
| Breakpoint | Columns |
|------------|---------|
| Mobile (<480px) | 2 |
| Tablet (<768px) | auto-fill, minmax(150px, 1fr) |
| Desktop | auto-fill, minmax(200px, 1fr) |

#### Brand Card
| Property | Value |
|----------|-------|
| Padding | 1.5rem 1rem |
| Border | 2px solid Brutal Border |
| Background | transparent |
| Hover Border | Acid Lime |
| Hover Background | rgba(204, 255, 0, 0.05) |

#### Card Typography
| Element | Font | Size | Color |
|---------|------|------|-------|
| Name | Space Mono | 0.875rem | Off-White |
| Count | Space Mono | 0.75rem | Brutal Grey |

---

### Footer

**File:** `parts/footer.html`

#### Structure
```
.gcb-footer
├── .footer-top
│   ├── .footer-branding
│   │   ├── h2 ("GCB")
│   │   ├── p (tagline)
│   │   └── p (subtitle)
│   └── .footer-social
│       └── a.social-icon (×5)
└── .footer-bottom
    └── p (copyright)
```

#### Section Specifications
| Property | Value |
|----------|-------|
| Border-top | 2px solid Brutal Border |
| Padding | spacing-50 (vertical), spacing-30 (horizontal) |

#### Logo
| Property | Value |
|----------|-------|
| Font | Playfair Display |
| Size | 3rem |
| Weight | 900 |
| Color | Off-White |

#### Taglines
| Element | Font | Size | Color |
|---------|------|------|-------|
| Primary | Space Mono | 0.75rem | Off-White |
| Secondary | Space Mono | 0.75rem | Brutal Grey |

#### Social Icons
| Property | Value |
|----------|-------|
| Count | 5 (YouTube, Instagram, Twitter/X, Bluesky, Facebook) |
| Icon Size | 2rem |
| Touch Target | 44px × 44px |
| Color | Brutal Grey |
| Hover | Acid Lime |

#### Copyright
| Property | Value |
|----------|-------|
| Font | Space Mono |
| Size | 0.75rem |
| Color | Brutal Grey |
| Border-top | 1px solid Brutal Border |

---

## Interactive States

### Hover States

| Element | Effect |
|---------|--------|
| Cards | Border changes to Acid Lime |
| Links | Color changes to Acid Lime |
| Buttons | Border changes to Acid Lime |
| Social Icons | Color changes to Acid Lime |

**Important:** Hover effects should ONLY change borders/colors. No background color changes except where explicitly specified.

### Focus States

All interactive elements must have visible focus indicators:

```css
:focus-visible {
    outline: 2px solid var(--wp--preset--color--acid-lime);
    outline-offset: 2px;
}
```

| Property | Value |
|----------|-------|
| Outline Width | 2px |
| Outline Style | solid |
| Outline Color | Acid Lime |
| Outline Offset | 2px |

### Transitions

**No transitions** except for mobile menu:

```css
/* Global rule */
* {
    transition: none !important;
}

/* Exception: Mobile menu */
.mobile-menu,
.menu-overlay {
    transition: transform 0.3s ease, visibility 0.3s ease;
}
```

---

## Responsive Breakpoints

### Standard Breakpoints

| Name | Width | Usage |
|------|-------|-------|
| Mobile | < 768px | Single column, stacked layouts |
| Tablet | 768px - 1024px | 2-column grids, show desktop nav |
| Desktop | > 1024px | Full layouts, 3-4 column grids |

### Media Query Patterns

```css
/* Mobile First */
@media (max-width: 767px) { }

/* Tablet */
@media (min-width: 768px) and (max-width: 1024px) { }

/* Desktop */
@media (min-width: 1025px) { }
```

### Grid Column Breakpoints

| Component | Mobile | Tablet | Desktop |
|-----------|--------|--------|---------|
| Bento Grid | 1 | 2 | 3 |
| Culture Grid | 1 | 2 | 4 |
| Brands Grid | 2 | auto-fill | auto-fill |

---

## Accessibility

### WCAG 2.2 AA Compliance

#### Color Contrast
All text combinations exceed WCAG AA requirements:
- Off-White on Void Black: 19.8:1 (exceeds AAA)
- Brutal Grey on Void Black: 8.6:1 (exceeds AAA)
- Acid Lime on Void Black: 18.2:1 (exceeds AAA)

#### Touch Targets
Minimum 44px × 44px for all interactive elements (exceeds 24px AA requirement)

```css
min-width: 44px;
min-height: 44px;
```

#### Focus Indicators
All interactive elements have visible focus indicators with 2px offset.

#### Skip Navigation
Header includes skip link for keyboard users:
```html
<a href="#main-content" class="skip-link">Skip to main content</a>
```

#### ARIA Labels
- Hamburger menu: `aria-label="Open Menu"`, `aria-expanded`
- Social icons: `aria-label="YouTube"`, etc.
- Play buttons: `aria-label="Play video: [title]"`

---

## CSS Variables Reference

### Colors
```css
--wp--preset--color--void-black: #050505;
--wp--preset--color--off-white: #FAFAFA;
--wp--preset--color--acid-lime: #CCFF00;
--wp--preset--color--brutal-border: #333333;
--wp--preset--color--brutal-grey: #999999;
```

### Typography
```css
--wp--preset--font-family--system-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
--wp--preset--font-family--playfair: 'Playfair Display', Georgia, serif;
--wp--preset--font-family--mono: 'Space Mono', 'Courier New', monospace;

--wp--preset--font-size--small: 0.875rem;
--wp--preset--font-size--medium: 1rem;
--wp--preset--font-size--large: 1.25rem;
--wp--preset--font-size--x-large: 2rem;
--wp--preset--font-size--xx-large: 3rem;
```

### Spacing
```css
--wp--preset--spacing--20: 0.5rem;
--wp--preset--spacing--30: 1rem;
--wp--preset--spacing--40: 1.5rem;
--wp--preset--spacing--50: 2rem;
--wp--preset--spacing--60: 3rem;
```

---

## Quick Reference Card

### Section Titles
- Font: Playfair Display, 2.5rem, 700
- Color: Off-White
- Transform: uppercase
- Separator: 1px solid Brutal Border

### Card Pattern
- Border: 1px-2px solid Brutal Border
- Background: Void Black
- Hover: Border → Acid Lime
- Padding: 1.5rem (spacing-30)

### Metadata Pattern
- Font: Space Mono, 0.75rem
- Color: Brutal Grey
- Transform: uppercase
- Letter-spacing: 0.05em

### Interactive Elements
- Touch target: 44px minimum
- Focus: 2px solid Acid Lime, 2px offset
- Hover: Acid Lime border/color
- Transitions: none (except mobile menu)
