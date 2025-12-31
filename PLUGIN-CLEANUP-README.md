# GCB Plugin Cleanup Script

Automated script to remove 24 unused/redundant WordPress plugins while preserving all GCB- custom plugins.

## Quick Start

```bash
# Test run (no changes made)
./cleanup-plugins.sh --dry-run

# Execute cleanup
./cleanup-plugins.sh
```

## What It Does

### Removes 24 Plugins Across 3 Phases

**Phase 1: Safe Removal (14 plugins)**
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
- LayerSlider (premium - unused)
- revslider (premium - unused)

**Phase 2: Verified Safe Removal (5 plugins)**
- instagram-feed (0 usage)
- popup-maker (0 popups)
- coblocks (0 usage)
- gutenberg (redundant - WP 6.9+)
- header-footer (no custom code)

**Phase 3: Subscription Consolidation (5 plugins)**
- wp-smush-pro → Use Jetpack image optimization
- wpmu-dev-seo → Use Jetpack SEO
- snapshot → Use Jetpack backups
- wpmudev-updates → Not needed
- page-optimize → Redundant with Jetpack Boost

### Protected Plugins

**The script will NEVER remove:**
- Any plugin starting with `gcb-` or `GCB-`
- Custom GCB plugins (gcb-test-utils, gcb-content-intelligence, etc.)

## Expected Results

- **Plugin count:** 36 → 12 (67% reduction)
- **Cost savings:** ~$200/year (WPMU DEV subscription)
- **Site impact:** Zero functionality loss

## Features

✅ **Safety First**
- Automatic database backup before changes
- Plugin list backup
- GCB- plugin protection
- Database integrity verification
- Rollback capability on errors

✅ **Dry Run Mode**
- Test the script without making changes
- See what would be removed
- Verify safety before execution

✅ **Detailed Logging**
- Color-coded output
- Phase-by-phase progress
- Comprehensive final report

## Usage Examples

### Test Without Making Changes

```bash
./cleanup-plugins.sh --dry-run
```

Shows what would be removed without actually removing anything.

### Execute Cleanup

```bash
./cleanup-plugins.sh
```

Prompts for confirmation before proceeding.

### Check Script Permissions

```bash
ls -lh cleanup-plugins.sh
```

Should show: `-rwx--x--x` (executable)

If not executable:
```bash
chmod +x cleanup-plugins.sh
```

## Output Example

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
          GCB PLUGIN CLEANUP SCRIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[INFO] Running pre-flight checks...
[SUCCESS] WordPress installation detected
[SUCCESS] Database found: wp-content/database/.ht.sqlite
[SUCCESS] Plugins directory found
[SUCCESS] SQLite3 available

[INFO] Initial plugin count: 36

Continue with plugin cleanup? (y/N): y

[INFO] Creating backups...
[SUCCESS] Database backup created: wp-content/database/.ht.sqlite.backup-20251231-181500
[SUCCESS] Plugin list saved: active-plugins-backup-20251231-181500.txt

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[INFO] PHASE 1: Safe Removal (14 plugins)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[SUCCESS] Removed: classic-editor
[SUCCESS] Removed: insert-headers-and-footers
...

              PLUGIN CLEANUP COMPLETE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

RESULTS:
  Initial plugins:  36
  Final plugins:    12
  Removed:          24 plugins
  Reduction:        67%

GCB- PLUGINS (PRESERVED):
  ✓ gcb-test-utils
  ✓ gcb-content-intelligence
```

## Backup Files Created

Each run creates timestamped backups:

```
wp-content/database/.ht.sqlite.backup-YYYYMMDD-HHMMSS
active-plugins-backup-YYYYMMDD-HHMMSS.txt
```

### Rollback Instructions

If you need to restore:

```bash
# Find your backup
ls -lh wp-content/database/.ht.sqlite.backup-*

# Restore database
cp wp-content/database/.ht.sqlite.backup-YYYYMMDD-HHMMSS wp-content/database/.ht.sqlite

# Verify restoration
sqlite3 wp-content/database/.ht.sqlite "PRAGMA integrity_check;"
```

## Post-Cleanup Actions

After successful cleanup:

### 1. Cancel WPMU DEV Subscription
- Login to WPMU DEV dashboard
- Account WDP ID: 912164
- Cancel membership to save $200/year

### 2. Enable Jetpack Features
- Image optimization (replaces Smush Pro)
- Site backups (replaces Snapshot)
- SEO tools (replaces WPMU DEV SEO)
- Login to Jetpack dashboard to activate

### 3. Test Site Functionality
- Visit homepage
- Check admin dashboard
- Verify theme (gcb-brutalist) is active
- Test any custom functionality

## Troubleshooting

### "Permission denied" Error

```bash
chmod +x cleanup-plugins.sh
```

### "wp-config.php not found"

Run the script from WordPress root directory:

```bash
cd /path/to/wordpress/root
./cleanup-plugins.sh
```

### "Database locked" Error

Wait 10 seconds and try again. Close any database connections (phpMyAdmin, DB Browser, etc.)

### Database Integrity Check Fails

The script automatically rolls back changes if integrity check fails. Your backup is safe at:
```
wp-content/database/.ht.sqlite.backup-YYYYMMDD-HHMMSS
```

## Technical Details

### Requirements
- Bash shell (macOS, Linux, WSL on Windows)
- SQLite3 command-line tool
- WordPress installation with SQLite database

### Safety Mechanisms
1. **Pre-flight checks** - Verifies environment before proceeding
2. **GCB- plugin protection** - Refuses to remove any GCB- plugins
3. **Automatic backups** - Creates database and plugin list backups
4. **Integrity verification** - Checks database health after removal
5. **Automatic rollback** - Restores backup if verification fails
6. **Dry run mode** - Test without making changes

### Exit Codes
- `0` - Success
- `1` - Error (pre-flight check failed, database error, user cancelled)

## Final Plugin List (12)

After cleanup, you'll have:

**ESSENTIAL CORE (2)**
1. gcb-test-utils
2. gcb-content-intelligence

**JETPACK ECOSYSTEM (3)**
3. jetpack
4. jetpack-boost
5. akismet

**LEGACY CONTENT (3)**
6. fusion-builder (4,022 posts may depend on it)
7. fusion-core
8. fusion-white-label-branding

**OPTIONAL/UTILITIES (4)**
9. advanced-custom-fields-pro
10. amp
11. health-check
12. rss-importer

## Cost Savings

- **Immediate:** ~$200/year (cancel WPMU DEV)
- **Ongoing:** Simplified plugin management, faster site performance

## Support

Questions or issues? Check:
1. Run with `--dry-run` first
2. Check backup files exist before running
3. Verify you're in WordPress root directory
4. Ensure SQLite3 is installed: `sqlite3 --version`

## License

Part of the GCB (Gay Car Boys) WordPress modernization project.

---

**Last Updated:** December 31, 2025
**Script Version:** 1.0
