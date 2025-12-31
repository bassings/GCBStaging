# Safe Plugin Deactivation Plan - GCB WordPress Site

**Date Created:** December 31, 2025
**Current Active Plugins:** 31
**Target Active Plugins:** 12-15
**Estimated Cost Savings:** $200-300/year

---

## ⚠️ CRITICAL: Pre-Deactivation Checklist

### 1. Create Full Backup (MANDATORY)

```bash
# Step 1: Backup SQLite Database
cp wp-content/database/.ht.sqlite wp-content/database/.ht.sqlite.backup-$(date +%Y%m%d)

# Step 2: Backup wp-content directory
tar -czf gcb-backup-$(date +%Y%m%d).tar.gz wp-content/

# Step 3: Verify backup integrity
ls -lh wp-content/database/.ht.sqlite.backup-*
ls -lh gcb-backup-*.tar.gz

# Step 4: Export active plugins list
wp plugin list --status=active --format=json > active-plugins-backup-$(date +%Y%m%d).json
```

### 2. Document Current State

```bash
# Save current theme
wp theme list --status=active > theme-backup.txt

# Save database size
du -h wp-content/database/.ht.sqlite

# Save plugin versions
wp plugin list --format=csv > plugin-versions-backup.csv
```

### 3. Test Site Before Changes

- [ ] Visit homepage and verify it loads
- [ ] Check admin dashboard access
- [ ] Verify custom theme (gcb-brutalist) is active
- [ ] Take screenshots of key pages

---

## 🗑️ Phase 1: Safe to Remove Immediately (14 Plugins)

**No Active Usage Detected - Zero Risk**

| Plugin | Reason | Risk Level |
|--------|--------|------------|
| classic-editor | FSE theme in use, not needed | ✅ None |
| insert-headers-and-footers | Legacy pattern, unused | ✅ None |
| code-snippets | Not used in theme | ✅ None |
| duplicate-page | Admin utility, not needed | ✅ None |
| onesignal-free-web-push-notifications | Not configured | ✅ None |
| layout-grid | Gutenberg has native grids | ✅ None |
| pwa | Not in project scope | ✅ None |
| qc-simple-link-directory | No links found | ✅ None |
| taxonomy-terms-order | Not used | ✅ None |
| wp-category-permalink | WP native feature | ✅ None |
| polldaddy | Jetpack includes this | ✅ None |
| crowdsignal-forms | Not integrated | ✅ None |
| **LayerSlider** | 2 sliders exist but NOT embedded in content | ✅ None |
| **Slider Revolution** | Zero sliders created | ✅ None |

### Deactivation Commands (Phase 1)

```bash
# Deactivate all Phase 1 plugins in one command
wp plugin deactivate \
  classic-editor \
  insert-headers-and-footers \
  code-snippets \
  duplicate-page \
  onesignal-free-web-push-notifications \
  layout-grid \
  pwa \
  qc-simple-link-directory \
  taxonomy-terms-order \
  wp-category-permalink \
  polldaddy \
  crowdsignal-forms \
  LayerSlider \
  revslider

# Test site after deactivation
wp plugin list --status=active

# If everything works, delete permanently
wp plugin delete \
  classic-editor \
  insert-headers-and-footers \
  code-snippets \
  duplicate-page \
  onesignal-free-web-push-notifications \
  layout-grid \
  pwa \
  qc-simple-link-directory \
  taxonomy-terms-order \
  wp-category-permalink \
  polldaddy \
  crowdsignal-forms \
  LayerSlider \
  revslider
```

**After Phase 1:** Test homepage, admin, and custom theme functionality.

---

## 🔍 Phase 2: Verify Usage Before Removal (7 Plugins)

**Requires Content/Usage Audit**

| Plugin | Action Required | Check Method |
|--------|-----------------|--------------|
| **Fusion Builder** | Search for Fusion shortcodes | `wp db query "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%fusion_%' AND post_status='publish';"` |
| **instagram-feed** | Check for widget/shortcode usage | Search templates and content for `[instagram-feed]` |
| **popup-maker** | Search for popup shortcodes | `wp db query "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%popup-%' AND post_status='publish';"` |
| **header-footer** | Check if custom code is inserted | Visit WP Admin → Header Footer settings |
| **amp** | Check if AMP pages are needed | Visit `/amp/` pages and check Google Search Console |
| **coblocks** | Search for CoBlocks usage | `wp db query "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%wp:coblocks%';"` |
| **gutenberg** | Check WordPress version | If WP 6.7+, plugin not needed |

### Verification Commands (Phase 2)

```bash
# Check for Fusion Builder usage
sqlite3 wp-content/database/.ht.sqlite "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%fusion_%' AND post_status='publish';"

# Check for Instagram Feed shortcodes
sqlite3 wp-content/database/.ht.sqlite "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%instagram-feed%' AND post_status='publish';"

# Check for Popup Maker usage
sqlite3 wp-content/database/.ht.sqlite "SELECT COUNT(*) FROM wp_posts WHERE post_type='popup' AND post_status='publish';"

# Check for CoBlocks Gutenberg blocks
sqlite3 wp-content/database/.ht.sqlite "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%wp:coblocks%' AND post_status='publish';"

# Check WordPress version for Gutenberg plugin necessity
wp core version
```

**Decision Rule:** If count = 0, safe to remove. If count > 0, keep or migrate content first.

---

## ⚠️ Phase 3: Subscription Consolidation (Choose One Service)

**Current Overlap: Jetpack Business + WPMU DEV Membership**

### Option A: Keep Jetpack Business, Remove WPMU DEV (Recommended)

**Keep:**
- jetpack (v15.4) - 108 features active
- jetpack-boost - Performance optimization
- akismet - Spam protection (Automattic)

**Remove:**
- smush-pro → Replace with Jetpack image optimization
- wpmudev-seo → Jetpack includes SEO features
- snapshot → Jetpack includes backups
- wpmudev-updates → Not needed

**Deactivation:**
```bash
wp plugin deactivate smush-pro wpmudev-seo snapshot wpmudev-updates
wp plugin delete smush-pro wpmudev-seo snapshot wpmudev-updates
```

**Cost Savings:** ~$150-200/year (cancel WPMU DEV membership)

### Option B: Keep WPMU DEV, Downgrade Jetpack

**Keep:**
- smush-pro (image optimization)
- wpmudev-seo (SEO)
- snapshot (backups)
- jetpack (free version only)

**Downgrade:**
- Jetpack Business → Jetpack Free
- Remove jetpack-boost (included in Smush Pro)

**Cost Savings:** ~$100-150/year (downgrade Jetpack)

---

## 🔄 Phase 4: Performance Plugin Consolidation

**Current Issue:** 3 performance plugins active (competing/conflicting)

- jetpack (includes performance features)
- jetpack-boost (dedicated performance)
- page-optimize (third-party performance)

### Recommended Configuration

**Keep:**
- jetpack-boost (if keeping Jetpack Business)

**Remove:**
- page-optimize (redundant with Jetpack Boost)

```bash
wp plugin deactivate page-optimize
wp plugin delete page-optimize
```

---

## 📊 Phase 5: Final Cleanup - Unused Feature Plugins

| Plugin | Keep/Remove | Reason |
|--------|-------------|--------|
| health-check | ✅ Keep | Useful diagnostic tool |
| akismet | ✅ Keep | Active spam protection |
| gutenberg | ⚠️ Remove if WP 6.7+ | Core feature in WP 6.7+ |
| fusion-core | ❌ Remove if Avada unused | Tied to Avada theme |
| fusion-white-label-branding | ❌ Remove if Avada unused | Tied to Avada theme |

---

## 🎯 Final Target Plugin List (12-15 Plugins)

**Essential Plugins:**
1. gcb-test-utils (custom E2E testing)
2. advanced-custom-fields-pro (if needed for custom fields)
3. jetpack (Business plan - primary feature suite)
4. jetpack-boost (performance)
5. akismet (spam protection)
6. health-check (diagnostics)

**Conditional Plugins (Keep if verified usage):**
7. instagram-feed (if actively displayed)
8. popup-maker (if popups are used)
9. amp (if AMP pages required by SEO strategy)

**Optional/Admin Tools:**
10. header-footer (if custom code inserted)

---

## 🧪 Testing Protocol After Each Phase

### Critical Tests (Run after each deactivation)

```bash
# 1. Check site loads
curl -I https://your-site-url.com

# 2. Verify theme is still active
wp theme list --status=active

# 3. Check for PHP errors
tail -n 50 wp-content/debug.log

# 4. Test admin dashboard
wp admin --user=admin

# 5. Run Playwright E2E tests (if applicable)
npm run test:e2e
```

### Visual Tests
- [ ] Homepage loads without errors
- [ ] Navigation works
- [ ] Post pages display correctly
- [ ] Media/images load properly
- [ ] Admin dashboard accessible
- [ ] Theme customizer works

---

## 🚨 Rollback Procedure (If Something Breaks)

### Emergency Rollback Steps

```bash
# 1. Restore database backup
cp wp-content/database/.ht.sqlite.backup-YYYYMMDD wp-content/database/.ht.sqlite

# 2. Reactivate all plugins from backup
wp plugin activate --all

# 3. Or restore specific plugin
wp plugin install plugin-name --activate

# 4. Clear cache
wp cache flush

# 5. Verify site recovery
curl -I https://your-site-url.com
```

### If Database Corruption Occurs

```bash
# Check database integrity
sqlite3 wp-content/database/.ht.sqlite "PRAGMA integrity_check;"

# Repair if needed
sqlite3 wp-content/database/.ht.sqlite "VACUUM;"
```

---

## 📋 Deactivation Execution Checklist

### Pre-Execution
- [ ] Create database backup (`.ht.sqlite.backup-YYYYMMDD`)
- [ ] Export active plugins list (`active-plugins-backup.json`)
- [ ] Take screenshots of homepage and admin
- [ ] Verify WordPress Studio is running
- [ ] Document current subscription costs

### Phase-by-Phase Execution
- [ ] **Phase 1:** Deactivate 14 safe-to-remove plugins
- [ ] **Test:** Homepage, admin, theme functionality
- [ ] **Phase 2:** Verify usage of 7 conditional plugins
- [ ] **Test:** Check audit results, deactivate unused
- [ ] **Phase 3:** Choose Jetpack OR WPMU DEV, remove other
- [ ] **Test:** Image optimization, backups, SEO still work
- [ ] **Phase 4:** Remove page-optimize (performance redundancy)
- [ ] **Test:** Site speed/performance maintained
- [ ] **Phase 5:** Remove Avada-related plugins if unused
- [ ] **Test:** Final E2E test suite

### Post-Execution
- [ ] Run full Playwright test suite
- [ ] Check WordPress Admin → Plugins (clean list)
- [ ] Verify active plugin count (target: 12-15)
- [ ] Cancel unused subscriptions (WPMU DEV or downgrade Jetpack)
- [ ] Document final plugin list
- [ ] Delete backup files after 30 days of stability

---

## 💰 Cost Impact Summary

| Service | Current Cost (Annual) | After Cleanup | Savings |
|---------|----------------------|---------------|---------|
| Jetpack Business | ~$300/year | $300 (keep) | $0 |
| WPMU DEV Membership | ~$200/year | $0 (remove) | $200 |
| **Total Savings** | | | **~$200/year** |

**Alternative:** Downgrade Jetpack to Free, keep WPMU DEV = $300 savings

---

## 📝 Notes & Warnings

### Important Considerations

1. **LayerSlider License:**
   - Purchase code is stored but sliders are unused
   - If license is transferable, could be used on another project
   - Deactivating won't affect site (verified by database check)

2. **Fusion Builder (Avada Theme):**
   - Currently inactive (gcb-brutalist is active)
   - If Avada was previously used, old content may have Fusion shortcodes
   - Run Phase 2 verification before removing

3. **ACF PRO:**
   - Licensed through WP Engine
   - NOT actively used in current gcb-brutalist theme
   - Keep if planning to use custom fields in future
   - Remove if purely FSE block-based approach

4. **Subscription Management:**
   - Jetpack subscription: Non-owner account (transferred)
   - WPMU DEV WDP ID: 912164
   - Cancel through respective dashboards after plugin removal

5. **WordPress Studio (WASM/SQLite):**
   - Some plugins may not be fully compatible with SQLite
   - This is actually a BENEFIT - fewer compatibility issues
   - Test each phase carefully in Studio environment

---

## 🔗 Quick Reference Commands

```bash
# Backup database
cp wp-content/database/.ht.sqlite wp-content/database/.ht.sqlite.backup-$(date +%Y%m%d)

# List active plugins
wp plugin list --status=active

# Deactivate single plugin
wp plugin deactivate plugin-name

# Delete plugin permanently
wp plugin delete plugin-name

# Restore from backup
cp wp-content/database/.ht.sqlite.backup-YYYYMMDD wp-content/database/.ht.sqlite

# Check database integrity
sqlite3 wp-content/database/.ht.sqlite "PRAGMA integrity_check;"
```

---

## ✅ Success Criteria

**Plugin cleanup is successful when:**
- Active plugin count: 12-15 (down from 31)
- Homepage loads in < 2 seconds
- All Playwright E2E tests pass
- Admin dashboard remains accessible
- No PHP errors in debug.log
- Image optimization still functional
- Backups still running
- SEO features still active
- Cost savings of $200+/year achieved
- Zero impact on live site functionality

---

**End of Plugin Deactivation Plan**

*Last Updated: December 31, 2025*
