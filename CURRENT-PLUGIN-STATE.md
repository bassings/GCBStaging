# Current Plugin State - Pre-Deactivation Snapshot

**Date:** December 31, 2025
**Total Active Plugins:** 31

---

## Active Plugins (Serialized from Database)

```
advanced-custom-fields-pro/acf.php
akismet/akismet.php
amp/amp.php
classic-editor/classic-editor.php
coblocks/class-coblocks.php
code-snippets/code-snippets.php
crowdsignal-forms/crowdsignal-forms.php
duplicate-page/duplicatepage.php
fusion-builder/fusion-builder.php
fusion-core/fusion-core.php
fusion-white-label-branding/fusion-white-label-branding.php
gutenberg/gutenberg.php
header-footer/plugin.php
health-check/health-check.php
insert-headers-and-footers/ihaf.php
instagram-feed/instagram-feed.php
jetpack/jetpack.php
layout-grid/index.php
onesignal-free-web-push-notifications/onesignal.php
page-optimize/page-optimize.php
polldaddy/polldaddy.php
popup-maker/popup-maker.php
pwa/pwa.php
qc-simple-link-directory/qc-op-directory-main.php
revslider/revslider.php
snapshot/snapshot.php
taxonomy-terms-order/taxonomy-terms-order.php
wp-category-permalink/wp-category-permalink.php
wpmu-dev-seo/wpmu-dev-seo.php
wpmudev-updates/update-notifications.php
gcb-test-utils/gcb-test-utils.php
```

---

## Plugin Categories

### Premium/Licensed (7)
1. advanced-custom-fields-pro/acf.php
2. jetpack/jetpack.php
3. fusion-builder/fusion-builder.php
4. revslider/revslider.php
5. LayerSlider (missing from list but active - check wp-admin)
6. wpmu-dev-seo/wpmu-dev-seo.php
7. Smush Pro (missing from list but active - check wp-admin)

### Free/Open Source (23)
- akismet, amp, classic-editor, coblocks, code-snippets
- crowdsignal-forms, duplicate-page, fusion-core
- fusion-white-label-branding, gutenberg, header-footer
- health-check, insert-headers-and-footers, instagram-feed
- layout-grid, onesignal-free-web-push-notifications
- page-optimize, polldaddy, popup-maker, pwa
- qc-simple-link-directory, snapshot, taxonomy-terms-order
- wp-category-permalink, wpmudev-updates

### Custom Development (1)
- gcb-test-utils/gcb-test-utils.php

---

## Recommendations (From Analysis)

### Phase 1: Safe to Remove Immediately (14 plugins)
- classic-editor
- insert-headers-and-footers
- code-snippets
- duplicate-page
- onesignal-free-web-push-notifications
- layout-grid
- pwa
- qc-simple-link-directory
- taxonomy-terms-order
- wp-category-permalink
- polldaddy
- crowdsignal-forms
- LayerSlider (2 sliders exist but NOT used in content)
- revslider (0 sliders, completely unused)

### Phase 2: Verify Before Removal (7 plugins)
- fusion-builder (check for Fusion shortcodes)
- instagram-feed (check for widget/shortcode)
- popup-maker (check for popup post type)
- header-footer (check admin settings)
- amp (check if AMP needed)
- coblocks (check for CoBlocks usage)
- gutenberg (if WP 6.7+, not needed)

### Phase 3: Subscription Consolidation
**Choose One:**
- Option A: Keep Jetpack Business, remove WPMU DEV plugins
- Option B: Keep WPMU DEV, downgrade Jetpack to free

### Phase 4: Performance Plugin Consolidation
- Remove: page-optimize (redundant with Jetpack Boost)

---

## Database Snapshot

**See:** `active-plugins-backup-20251231.txt` for raw serialized data

**Restore Command (if needed):**
```bash
# Restore from this snapshot
wp option update active_plugins --format=json < active-plugins-backup-20251231.txt
```

---

## Notes

- **LayerSlider** appears to be active but not in serialized list (check wp-admin)
- **Smush Pro** appears to be active but not in serialized list (check wp-admin)
- Current theme: gcb-brutalist (FSE block theme)
- WordPress Studio environment (WASM/SQLite)
- Database: wp-content/database/.ht.sqlite
- Total posts: 5,674
- Total pages: 80

---

**For deactivation instructions, see:** `PLUGIN-DEACTIVATION-PLAN.md`
