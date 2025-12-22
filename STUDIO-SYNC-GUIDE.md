# WordPress Studio Sync Guide

## Syncing to staging-b262-gaycarboys.wpcomstaging.com

**Local Site**: `/Volumes/Storage/home/scott.b/repos/GCBStaging`
**Remote Site**: `https://staging-b262-gaycarboys.wpcomstaging.com/`
**Authenticated as**: `sydscott76`

---

## Prerequisites ✓

- ✅ WordPress Studio GUI installed
- ✅ Authenticated to WordPress.com (`sydscott76`)
- ✅ Local changes committed to git
- ✅ Database backup created

---

## Step-by-Step Sync Instructions

### Step 1: Open WordPress Studio GUI

1. Launch **WordPress Studio** application (not the CLI)
2. You should see the Studio dashboard

### Step 2: Add Local Site to Studio

1. In Studio GUI, click **"Add Site"** or **File → Add Existing Site**
2. Browse to: `/Volumes/Storage/home/scott.b/repos/GCBStaging`
3. Click **"Add"** or **"Open"**
4. Studio will detect it's a WordPress site and add it to the dashboard

### Step 3: Configure Sync to WordPress.com Staging

1. **Select the site** from the Studio dashboard
2. Look for **"Sync"** button or **"Connect to WordPress.com"** option
3. Click **"Sync"** or **"Set Up Sync"**
4. When prompted, enter the staging URL:
   ```
   https://staging-b262-gaycarboys.wpcomstaging.com
   ```
5. Studio will authenticate (you're already logged in as `sydscott76`)

### Step 4: Configure Selective Sync

⚠️ **IMPORTANT**: Use **Selective Sync** to avoid syncing unnecessary files

**Recommended Sync Configuration**:

#### ✅ SYNC THESE:
- **Themes** (wp-content/themes/)
  - ✅ Avada-child (NEW - our performance optimizations)
  - ℹ️ Parent Avada theme should already be on staging

- **Plugins** (wp-content/plugins/)
  - ⚠️ **CAREFUL**: We removed 13 plugins locally
  - **Recommended**: Manually deactivate/delete plugins on staging (see list below)
  - **OR** Let Studio sync and remove them

- **wp-config.php**
  - ✅ Sync to apply memory limit changes
  - ⚠️ Studio may handle database credentials automatically

- **Database** (OPTIONAL - see warnings below)
  - ⚠️ Contains plugin deactivations and theme activation
  - ℹ️ May be easier to manually deactivate plugins via staging admin

#### ❌ DO NOT SYNC:
- wp-content/uploads/ (unless you want to overwrite staging media)
- wp-content/cache/
- wp-content/debug.log
- .git/ (not needed on staging)

### Step 5: Choose Sync Direction

**Direction**: **Local → Remote** (Push changes TO staging)

**Sync Mode**:
- **One-way sync**: Local overwrites remote (RECOMMENDED for this deployment)
- ⚠️ **Two-way sync**: Can cause conflicts - avoid for initial push

### Step 6: Review Changes Before Syncing

Studio should show you a **preview** of what will change:

**Expected Changes**:
- 13 plugin directories will be REMOVED
- 1 theme directory added (Avada-child)
- wp-config.php updated
- Database changes (if syncing DB):
  - active_plugins option updated
  - stylesheet/template options (child theme)
  - hefo option deleted (tracking code)
  - Plugin auto-update settings

### Step 7: Start the Sync

1. Review the changes carefully
2. Click **"Start Sync"** or **"Push to Staging"**
3. Studio will upload files and optionally sync database
4. Wait for completion (may take 5-10 minutes for first sync)

### Step 8: Verify on Staging

After sync completes:

1. Visit: `https://staging-b262-gaycarboys.wpcomstaging.com/wp-admin/`
2. Check **Appearance → Themes**: Should show "Avada Child" as active
3. Check **Plugins**: Should show 24 plugins (down from 37)
4. Check a few pages to ensure site works correctly

---

## Alternative: Manual Plugin Management

If you prefer NOT to sync the database (safer approach):

### Before Syncing:
1. Sync ONLY themes and wp-config.php
2. Do NOT sync database or plugins directory yet

### Then Manually on Staging Admin:
1. Login to: `https://staging-b262-gaycarboys.wpcomstaging.com/wp-admin/`
2. **Deactivate and delete these plugins**:
   - Hello Dolly
   - RSS Importer
   - AMP
   - Classic Editor
   - CoBlocks
   - Duplicate Page
   - Gutenberg
   - Layout Grid
   - Polldaddy
   - PWA
   - Head, Footer and Post Injections
   - Revolution Slider

3. **Activate Avada Child theme**:
   - Go to Appearance → Themes
   - Find "Avada Child"
   - Click "Activate"

4. **Enable auto-updates**:
   - Go to Plugins → Installed Plugins
   - Enable auto-updates for safe plugins (see list in OPTIMIZATION-SUMMARY.md)

---

## Plugins to Remove on Staging

```
1.  hello.php (Hello Dolly)
2.  rss-importer/
3.  amp/
4.  classic-editor/
5.  coblocks/
6.  duplicate-page/
7.  gutenberg/
8.  layout-grid/
9.  polldaddy/
10. pwa/
11. header-footer/ (Contains GTM & AdSense - MUST REMOVE)
12. revslider/ (Revolution Slider - unused)
13. (Count verification: should have removed 13 total)
```

---

## Post-Sync Checklist

After syncing, verify on staging:

### 1. Theme Check
- [ ] Avada Child theme is active
- [ ] Parent Avada theme is still present
- [ ] Site design looks correct

### 2. Plugin Check
- [ ] Plugin count: 24 (down from 37)
- [ ] All removed plugins are gone
- [ ] Remaining plugins are active and working

### 3. Configuration Check
- [ ] Memory limit: Check via Site Health (Tools → Site Health)
- [ ] No GTM/advertising code in source (View Page Source)
- [ ] Auto-updates enabled for stable plugins

### 4. Functionality Test
- [ ] Homepage loads
- [ ] bbPress forums work
- [ ] Contact forms work
- [ ] LayerSlider works on pages with sliders
- [ ] Admin dashboard accessible

### 5. Performance Check
- [ ] Run Google PageSpeed Insights
- [ ] Check page size (should be ~50% smaller)
- [ ] Verify no JavaScript errors in console

---

## Troubleshooting

### If Sync Fails:

**Error: "Permission denied"**
- Solution: Check WordPress.com permissions for `sydscott76`
- Ensure you have admin access to staging site

**Error: "Database connection error"**
- Solution: Don't sync database, Studio handles this automatically
- wp-config.php database credentials are managed by WordPress.com

**Error: "File already exists"**
- Solution: Use "Overwrite" option in sync settings
- This is expected for core WordPress files

### If Site Breaks After Sync:

1. **Check debug.log**:
   - Access via SFTP or WordPress.com dashboard
   - Look for plugin/theme errors

2. **Deactivate all plugins**:
   - Via WordPress.com dashboard or phpMyAdmin
   - Then reactivate one by one

3. **Switch back to parent Avada theme**:
   - Via Appearance → Themes in staging admin

4. **Rollback locally**:
   ```bash
   cd /Volumes/Storage/home/scott.b/repos/GCBStaging
   git log --oneline
   git reset --hard <commit-hash>
   ```
   Then re-sync

---

## Database-Only Changes (If Not Syncing Database)

If you choose NOT to sync the database, these changes need to be made manually on staging:

### 1. Remove Tracking Code

**Via phpMyAdmin or WP-CLI on staging**:
```sql
DELETE FROM wp_options WHERE option_name = 'hefo';
```

**OR manually**:
- Settings → Header and Footer (if plugin still exists)
- Delete all code in all fields

### 2. Deactivate Plugins

**Via phpMyAdmin**:
```sql
-- Get current active plugins
SELECT option_value FROM wp_options WHERE option_name = 'active_plugins';

-- Note: Will need to manually edit the serialized array
-- Easier to do via WordPress admin
```

**Via WordPress Admin** (Recommended):
- Plugins → Installed Plugins
- Deactivate and delete the 13 plugins listed above

### 3. Activate Child Theme

**Via phpMyAdmin**:
```sql
UPDATE wp_options SET option_value = 'Avada-child' WHERE option_name = 'stylesheet';
UPDATE wp_options SET option_value = 'Avada' WHERE option_name = 'template';
```

**Via WordPress Admin** (Recommended):
- Appearance → Themes
- Activate "Avada Child"

---

## Expected Sync Time

- **File sync**: 3-5 minutes (themes + plugins)
- **Database sync**: 2-3 minutes (if enabled)
- **Total**: 5-10 minutes

**File sizes**:
- Removed: ~1.1 million lines of code
- Added: Avada-child theme (~2KB)
- Modified: wp-config.php (~1KB)

---

## Backup Reminder

**Before syncing**, ensure you have:
- ✅ Local git backup (complete)
- ✅ Local database backup (complete)
- ⚠️ **CREATE STAGING BACKUP** via WordPress.com dashboard
  - Go to staging site dashboard
  - Create a restore point before syncing
  - This allows rollback if needed

---

## WordPress.com Staging Dashboard

Access your staging site controls:
1. Go to: https://wordpress.com/sites
2. Find: `staging-b262-gaycarboys.wpcomstaging.com`
3. Available options:
   - Backups/Restore points
   - SFTP access
   - phpMyAdmin access
   - Site Health
   - Activity log

---

## Next Steps After Successful Sync

Once sync is complete and verified:

1. **Complete admin configurations** (see OPTIMIZATION-SUMMARY.md):
   - Avada Performance Wizard
   - Jetpack Boost
   - WP Smush Pro
   - OneSignal

2. **Run performance tests**:
   - Google PageSpeed Insights
   - GTmetrix
   - WebPageTest

3. **Monitor for 24-48 hours**:
   - Check error logs
   - Monitor site performance
   - Test all functionality

4. **If all good, sync to production**:
   - Follow same process for production site
   - OR use WordPress.com to promote staging → production

---

## Questions or Issues?

If you encounter problems during sync:

1. Check WordPress Studio sync logs
2. Review WordPress.com activity log
3. Check staging site debug.log
4. Refer to OPTIMIZATION-SUMMARY.md for rollback instructions

**Remember**: All changes are tracked in git, so you can always rollback locally and re-sync!

---

*Created: December 22, 2025*
*Local Site: GCBStaging*
*Target: staging-b262-gaycarboys.wpcomstaging.com*
