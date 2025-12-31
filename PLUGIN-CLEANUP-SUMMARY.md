# Plugin Cleanup Script - Quick Reference

## Files Created

1. **`cleanup-plugins.sh`** - Main cleanup script (executable)
2. **`PLUGIN-CLEANUP-README.md`** - Complete documentation
3. **`PLUGIN-CLEANUP-SUMMARY.md`** - This quick reference

---

## Quick Commands

```bash
# Test run (recommended first)
./cleanup-plugins.sh --dry-run

# Execute cleanup
./cleanup-plugins.sh
```

---

## What Gets Removed (24 plugins)

### Phase 1: Safe Removal (14)
- classic-editor, insert-headers-and-footers, code-snippets, duplicate-page
- onesignal-free-web-push-notifications, layout-grid, pwa
- qc-simple-link-directory, taxonomy-terms-order, wp-category-permalink
- polldaddy, crowdsignal-forms, LayerSlider, revslider

### Phase 2: Verified Safe (5)
- instagram-feed, popup-maker, coblocks, gutenberg, header-footer

### Phase 3: WPMU DEV Consolidation (5)
- wp-smush-pro, wpmu-dev-seo, snapshot, wpmudev-updates, page-optimize

---

## Protected Plugins

**ALL GCB- plugins are PROTECTED:**
- gcb-test-utils
- gcb-content-intelligence
- Any other gcb-* plugins

The script will REFUSE to remove any plugin starting with `gcb-` or `GCB-`.

---

## Expected Outcome

- **Before:** 36 plugins (approx)
- **After:** 12 plugins
- **Removed:** 24 plugins (67% reduction)
- **Cost savings:** ~$200/year (cancel WPMU DEV)

---

## Safety Features

✅ Automatic database backup (timestamped)
✅ Plugin list backup
✅ GCB- plugin protection (hardcoded safety check)
✅ Database integrity verification
✅ Automatic rollback on errors
✅ Dry-run mode (test first)

---

## Usage Workflow

### 1. Redownload Site
```bash
# After redownloading site from source
cd /path/to/wordpress/root
```

### 2. Test First (Dry Run)
```bash
./cleanup-plugins.sh --dry-run
```

Review the output - it shows what WOULD be removed without actually removing anything.

### 3. Execute Cleanup
```bash
./cleanup-plugins.sh
```

Press `y` when prompted to confirm.

### 4. Verify Results
Script automatically verifies:
- Database integrity
- Theme status
- GCB- plugins preserved

### 5. Post-Cleanup Actions
- Cancel WPMU DEV subscription (WDP ID: 912164)
- Enable Jetpack features (image optimization, backups, SEO)
- Test site functionality

---

## Backup Files

Each run creates timestamped backups:

```
wp-content/database/.ht.sqlite.backup-YYYYMMDD-HHMMSS
active-plugins-backup-YYYYMMDD-HHMMSS.txt
```

Keep these for 30 days, then delete after confirming stability.

---

## Troubleshooting

### Permission Error
```bash
chmod +x cleanup-plugins.sh
```

### Not in WordPress Root
```bash
cd /Volumes/Storage/home/scott.b/repos/GCBStaging
./cleanup-plugins.sh
```

### Database Locked
Wait 10 seconds, close any DB tools, try again.

---

## Rollback (If Needed)

```bash
# Find backup
ls -lh wp-content/database/.ht.sqlite.backup-*

# Restore
cp wp-content/database/.ht.sqlite.backup-YYYYMMDD-HHMMSS wp-content/database/.ht.sqlite
```

---

## Final Plugin List (12)

After cleanup:

**CORE (2):** gcb-test-utils, gcb-content-intelligence
**JETPACK (3):** jetpack, jetpack-boost, akismet
**LEGACY (3):** fusion-builder, fusion-core, fusion-white-label-branding
**OPTIONAL (4):** advanced-custom-fields-pro, amp, health-check, rss-importer

---

**For full documentation, see:** `PLUGIN-CLEANUP-README.md`
