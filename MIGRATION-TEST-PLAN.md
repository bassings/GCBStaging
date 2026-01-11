# Migration Validation Test Plan

**Created:** January 12, 2026
**Purpose:** Validate Avada → Gutenberg migration across all unique design patterns
**Total Posts:** 3,895 | **Unique Patterns:** 20 | **Posts to Validate:** 20

---

## Quick Reference: Test URLs

```
1.  http://localhost:8881/?p=3049    video|multi-col (1,542 posts)
2.  http://localhost:8881/?p=26909   mixed|multi-col (1,168 posts)
3.  http://localhost:8881/?p=6       text-only|columned (671 posts)
4.  http://localhost:8881/?p=16188   mixed|complex (377 posts)
5.  http://localhost:8881/?p=729     standard|multi-col (44 posts)
6.  http://localhost:8881/?p=91845   table|multi-col (28 posts)
7.  http://localhost:8881/?p=27615   text-only|multi-col (16 posts)
8.  http://localhost:8881/?p=32659   video-gallery|complex (13 posts)
9.  http://localhost:8881/?p=17475   video|columned (10 posts)
10. http://localhost:8881/?p=28667   video|complex (8 posts)
11. http://localhost:8881/?p=30756   multi-image|complex (4 posts)
12. http://localhost:8881/?p=31474   mixed-heavy|complex (4 posts)
13. http://localhost:8881/?p=33420   text-only|complex (3 posts)
14. http://localhost:8881/?p=28597   text-only|single (1 post)
15. http://localhost:8881/?p=28612   multi-image|single (1 post)
16. http://localhost:8881/?p=28629   table|single (1 post)
17. http://localhost:8881/?p=28632   standard|single (1 post)
18. http://localhost:8881/?p=31092   multi-image|multi-col (1 post)
19. http://localhost:8881/?p=33073   standard|complex (1 post)
20. http://localhost:8881/?p=94615   table|complex (1 post)
```

---

## Pattern Definitions

| Content Type | Description |
|--------------|-------------|
| `video` | Contains YouTube/Vimeo embeds (1-3) |
| `video-gallery` | Contains 10+ video embeds |
| `mixed` | Contains both videos AND images |
| `mixed-heavy` | Contains 3+ videos AND 3+ images |
| `standard` | Contains images only (1-3) |
| `multi-image` | Contains 4+ images |
| `table` | Contains data tables |
| `gallery` | Contains image gallery blocks |
| `text-only` | No media, text and lists only |

| Layout Type | Description |
|-------------|-------------|
| `single` | No column structure |
| `columned` | 1-2 column sections |
| `multi-col` | 3-5 column sections |
| `complex` | 6+ column sections |

---

## Validation Checklist

### Frontend Checks (View Post)

For each post, verify:

- [ ] **Page loads** without errors (no white screen, no PHP errors)
- [ ] **Images display** correctly (not broken, correct size)
- [ ] **Videos embed** and play (YouTube thumbnail visible, plays on click)
- [ ] **Layout intact** (columns display side-by-side on desktop)
- [ ] **Text readable** (no HTML entities like `&rsquo;`, proper formatting)
- [ ] **Links work** (internal and external links clickable)
- [ ] **Tables render** (if applicable - rows/columns visible, styled correctly)
- [ ] **Mobile responsive** (check at 375px width)

### Editor Checks (Edit Post in Gutenberg)

For each post, verify:

- [ ] **No block errors** ("Block contains unexpected or invalid content" warnings)
- [ ] **Blocks editable** (can click into text, modify content)
- [ ] **Block structure visible** (correct hierarchy in List View)
- [ ] **Images have src** (not broken in editor)
- [ ] **Save works** (can update without errors)

---

## Detailed Test Cases

### TEST 1: video|multi-col (1,542 posts - 40%)
**URL:** http://localhost:8881/?p=3049
**Title:** "A brief history of Saab"
**Priority:** CRITICAL (largest pattern)

**Expected Elements:**
- [ ] YouTube video embed(s) with thumbnail
- [ ] Multiple column sections (3-5)
- [ ] Text paragraphs
- [ ] Possibly images

**Frontend:**
- [ ] Video thumbnail displays
- [ ] Video plays when clicked
- [ ] Columns display correctly
- [ ] No layout breaking

**Editor:**
- [ ] `core/embed` blocks present
- [ ] `core/columns` with nested `core/column`
- [ ] No validation errors

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 2: mixed|multi-col (1,168 posts - 30%)
**URL:** http://localhost:8881/?p=26909
**Title:** "Helpful Hints: etag placement"
**Priority:** CRITICAL (second largest pattern)

**Expected Elements:**
- [ ] YouTube video embed(s)
- [ ] Image(s) with src attribute
- [ ] Multiple column sections

**Frontend:**
- [ ] Videos and images both display
- [ ] Mixed media layout intact
- [ ] No broken images

**Editor:**
- [ ] `core/embed` blocks present
- [ ] `core/image` blocks with src
- [ ] `core/columns` structure valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 3: text-only|columned (671 posts - 17%)
**URL:** http://localhost:8881/?p=6
**Title:** "Could Sexy Peugeot RCZ be the new GAY Icon?"
**Priority:** HIGH

**Expected Elements:**
- [ ] Text paragraphs only
- [ ] 1-2 column sections
- [ ] Possibly lists
- [ ] No media

**Frontend:**
- [ ] Text displays correctly
- [ ] No broken layout
- [ ] Links work

**Editor:**
- [ ] `core/paragraph` blocks
- [ ] `core/columns` if present
- [ ] `core/list` if present

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 4: mixed|complex (377 posts - 10%)
**URL:** http://localhost:8881/?p=16188
**Title:** "VW Golf Cabriolet: The Joy of Taking Your Top Off in Public"
**Priority:** HIGH

**Expected Elements:**
- [ ] Multiple videos and images
- [ ] 6+ column sections
- [ ] Complex nested layout

**Frontend:**
- [ ] All media displays
- [ ] Complex layout renders
- [ ] No horizontal scroll issues

**Editor:**
- [ ] Deep nesting in List View
- [ ] All blocks editable
- [ ] No validation errors

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 5: standard|multi-col (44 posts)
**URL:** http://localhost:8881/?p=729
**Title:** "SAAB 9-5 Aero. The sexy car that nearly wasn't."
**Priority:** MEDIUM

**Expected Elements:**
- [ ] 1-3 images (no video)
- [ ] Multiple column sections

**Frontend:**
- [ ] Images display with correct src
- [ ] Column layout intact

**Editor:**
- [ ] `core/image` blocks valid
- [ ] Image src attribute present

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 6: table|multi-col (28 posts)
**URL:** http://localhost:8881/?p=91845
**Title:** "2025 Kia K4 - Good Car Spoilt by its Looks"
**Priority:** MEDIUM

**Expected Elements:**
- [ ] Data table(s)
- [ ] Multiple column sections
- [ ] Possibly spec sheets

**Frontend:**
- [ ] Table renders with borders
- [ ] Table text readable (not light-on-light)
- [ ] Mobile horizontal scroll works

**Editor:**
- [ ] `core/table` or `core/html` block
- [ ] Table structure intact

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 7: text-only|multi-col (16 posts)
**URL:** http://localhost:8881/?p=27615
**Title:** "2018 Tesla Model S P100D Review"
**Priority:** MEDIUM

**Expected Elements:**
- [ ] Text only, no media
- [ ] Multiple column sections

**Frontend:**
- [ ] Text displays
- [ ] Columns work

**Editor:**
- [ ] Clean paragraph blocks

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 8: video-gallery|complex (13 posts)
**URL:** http://localhost:8881/?p=32659
**Title:** "What are the Top 10 Australian Cars"
**Priority:** MEDIUM

**Expected Elements:**
- [ ] 10+ YouTube embeds
- [ ] Complex column structure
- [ ] Likely a "listicle" format

**Frontend:**
- [ ] All videos have thumbnails
- [ ] All videos play
- [ ] Layout doesn't break

**Editor:**
- [ ] Many `core/embed` blocks
- [ ] Deep nesting

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 9: video|columned (10 posts)
**URL:** http://localhost:8881/?p=17475
**Title:** "Honda's new HR-V: Good looking, but is it any good?"
**Priority:** LOW

**Expected Elements:**
- [ ] Video embed(s)
- [ ] Simple 1-2 column layout

**Frontend:**
- [ ] Video plays
- [ ] Simple layout intact

**Editor:**
- [ ] Basic structure

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 10: video|complex (8 posts)
**URL:** http://localhost:8881/?p=28667
**Title:** "Range Rover Sport HSE P400 Hybrid Climbs 999 45° Stairs"
**Priority:** LOW

**Expected Elements:**
- [ ] Video embed(s)
- [ ] 6+ column sections

**Frontend:**
- [ ] Video works
- [ ] Complex layout renders

**Editor:**
- [ ] No errors

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 11: multi-image|complex (4 posts)
**URL:** http://localhost:8881/?p=30756
**Title:** "Lotus Evija production limited 130 cars"
**Priority:** LOW

**Expected Elements:**
- [ ] 4+ images
- [ ] Complex layout

**Frontend:**
- [ ] All images display
- [ ] Layout intact

**Editor:**
- [ ] Image blocks valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 12: mixed-heavy|complex (4 posts)
**URL:** http://localhost:8881/?p=31474
**Title:** "Top 10 Gay Lesbian LGBT Cars"
**Priority:** LOW

**Expected Elements:**
- [ ] 3+ videos AND 3+ images
- [ ] Complex layout

**Frontend:**
- [ ] All media displays
- [ ] Heavy content loads

**Editor:**
- [ ] Many blocks, all valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 13: text-only|complex (3 posts)
**URL:** http://localhost:8881/?p=33420
**Title:** "2020 Mitsubishi Pajero Sport"
**Priority:** LOW

**Expected Elements:**
- [ ] Text only
- [ ] 6+ column sections

**Frontend:**
- [ ] Text readable
- [ ] Complex columns work

**Editor:**
- [ ] Clean structure

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 14: text-only|single (1 post)
**URL:** http://localhost:8881/?p=28597
**Title:** "How to Use Smart Entry and Start"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] Text only
- [ ] No columns

**Frontend:**
- [ ] Simple text post

**Editor:**
- [ ] Minimal blocks

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 15: multi-image|single (1 post)
**URL:** http://localhost:8881/?p=28612
**Title:** "Citroën 19_19 Concept"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] 4+ images
- [ ] No columns

**Frontend:**
- [ ] Images display
- [ ] Simple layout

**Editor:**
- [ ] Image blocks valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 16: table|single (1 post)
**URL:** http://localhost:8881/?p=28629
**Title:** "Next-Gen Mazda3 Sedan Arrives in Australia, at Last"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] Table(s)
- [ ] No columns

**Frontend:**
- [ ] Table renders correctly

**Editor:**
- [ ] Table block valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 17: standard|single (1 post)
**URL:** http://localhost:8881/?p=28632
**Title:** "New Audi A4 for 2020"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] 1-3 images
- [ ] No columns

**Frontend:**
- [ ] Images display

**Editor:**
- [ ] Simple structure

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 18: multi-image|multi-col (1 post)
**URL:** http://localhost:8881/?p=31092
**Title:** "2019 Max Verstappen Wins Grand Prix Hockenheim"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] 4+ images
- [ ] Multiple columns

**Frontend:**
- [ ] All images display
- [ ] Columns work

**Editor:**
- [ ] All blocks valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 19: standard|complex (1 post)
**URL:** http://localhost:8881/?p=33073
**Title:** "The Last Overland Adventure"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] 1-3 images
- [ ] 6+ column sections

**Frontend:**
- [ ] Images display
- [ ] Complex layout works

**Editor:**
- [ ] Deep nesting valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

### TEST 20: table|complex (1 post)
**URL:** http://localhost:8881/?p=94615
**Title:** "Elegance Recharged: Classic Mercedes, MONCEAU Magic"
**Priority:** LOW (edge case)

**Expected Elements:**
- [ ] Table(s)
- [ ] 6+ column sections

**Frontend:**
- [ ] Table renders
- [ ] Complex layout works

**Editor:**
- [ ] All blocks valid

**Status:** ⬜ Not Tested | ✅ Pass | ❌ Fail | ⚠️ Issues

**Notes:**
```
[Add notes here]
```

---

## Summary Results

| Test | Pattern | Posts Covered | Status |
|------|---------|---------------|--------|
| 1 | video\|multi-col | 1,542 | ⬜ |
| 2 | mixed\|multi-col | 1,168 | ⬜ |
| 3 | text-only\|columned | 671 | ⬜ |
| 4 | mixed\|complex | 377 | ⬜ |
| 5 | standard\|multi-col | 44 | ⬜ |
| 6 | table\|multi-col | 28 | ⬜ |
| 7 | text-only\|multi-col | 16 | ⬜ |
| 8 | video-gallery\|complex | 13 | ⬜ |
| 9 | video\|columned | 10 | ⬜ |
| 10 | video\|complex | 8 | ⬜ |
| 11 | multi-image\|complex | 4 | ⬜ |
| 12 | mixed-heavy\|complex | 4 | ⬜ |
| 13 | text-only\|complex | 3 | ⬜ |
| 14 | text-only\|single | 1 | ⬜ |
| 15 | multi-image\|single | 1 | ⬜ |
| 16 | table\|single | 1 | ⬜ |
| 17 | standard\|single | 1 | ⬜ |
| 18 | multi-image\|multi-col | 1 | ⬜ |
| 19 | standard\|complex | 1 | ⬜ |
| 20 | table\|complex | 1 | ⬜ |

**Total Passed:** ___ / 20
**Total Failed:** ___ / 20
**Coverage:** 3,895 posts validated through pattern sampling

---

## Issue Tracking

### Known Issues Found

| Test # | Issue Description | Severity | Fixed? |
|--------|-------------------|----------|--------|
| | | | |

### Fixes Applied

| Date | Issue | Fix Description | Commit |
|------|-------|-----------------|--------|
| 2026-01-12 | Images missing src | Removed imageframe from self-closing, added URL extraction | 1da06fc7 |
| 2026-01-12 | Block validation errors | Added flex-basis style, fixed list blocks, etc. | 1af2c351 |

---

## Re-test After Fixes

After applying fixes, re-run failed tests:

1. Restore original content: `wp eval 'wp_update_post(["ID" => POST_ID, "post_content" => get_post_meta(POST_ID, "_gcb_original_avada_content", true)]);'`
2. Re-migrate: `wp gcb migrate_posts --post-id=POST_ID`
3. Re-test frontend and editor
4. Update status in this document
