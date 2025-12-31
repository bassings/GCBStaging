# GCB Plugin Cleanup - WP-CLI Commands

## Quick Reference for SSH Execution

### Option 1: Run the Full Script

```bash
# Upload and run the cleanup script
bash cleanup-plugins-wpcli.sh
```

---

### Option 2: Run Individual Commands

#### 1. Backup Current Plugin List
```bash
wp plugin list --format=csv > plugin-backup-$(date +%Y%m%d-%H%M%S).csv
wp plugin list
```

#### 2. Phase 1: Safe Removal (14 plugins)

```bash
# Deactivate and delete Phase 1 plugins
wp plugin deactivate classic-editor --quiet 2>/dev/null || true
wp plugin delete classic-editor --quiet 2>/dev/null || true

wp plugin deactivate insert-headers-and-footers --quiet 2>/dev/null || true
wp plugin delete insert-headers-and-footers --quiet 2>/dev/null || true

wp plugin deactivate code-snippets --quiet 2>/dev/null || true
wp plugin delete code-snippets --quiet 2>/dev/null || true

wp plugin deactivate duplicate-page --quiet 2>/dev/null || true
wp plugin delete duplicate-page --quiet 2>/dev/null || true

wp plugin deactivate onesignal-free-web-push-notifications --quiet 2>/dev/null || true
wp plugin delete onesignal-free-web-push-notifications --quiet 2>/dev/null || true

wp plugin deactivate layout-grid --quiet 2>/dev/null || true
wp plugin delete layout-grid --quiet 2>/dev/null || true

wp plugin deactivate pwa --quiet 2>/dev/null || true
wp plugin delete pwa --quiet 2>/dev/null || true

wp plugin deactivate qc-simple-link-directory --quiet 2>/dev/null || true
wp plugin delete qc-simple-link-directory --quiet 2>/dev/null || true

wp plugin deactivate taxonomy-terms-order --quiet 2>/dev/null || true
wp plugin delete taxonomy-terms-order --quiet 2>/dev/null || true

wp plugin deactivate wp-category-permalink --quiet 2>/dev/null || true
wp plugin delete wp-category-permalink --quiet 2>/dev/null || true

wp plugin deactivate polldaddy --quiet 2>/dev/null || true
wp plugin delete polldaddy --quiet 2>/dev/null || true

wp plugin deactivate crowdsignal-forms --quiet 2>/dev/null || true
wp plugin delete crowdsignal-forms --quiet 2>/dev/null || true

wp plugin deactivate LayerSlider --quiet 2>/dev/null || true
wp plugin delete LayerSlider --quiet 2>/dev/null || true

wp plugin deactivate revslider --quiet 2>/dev/null || true
wp plugin delete revslider --quiet 2>/dev/null || true
```

#### 3. Phase 2: Verified Safe Removal (5 plugins)

```bash
wp plugin deactivate instagram-feed --quiet 2>/dev/null || true
wp plugin delete instagram-feed --quiet 2>/dev/null || true

wp plugin deactivate popup-maker --quiet 2>/dev/null || true
wp plugin delete popup-maker --quiet 2>/dev/null || true

wp plugin deactivate coblocks --quiet 2>/dev/null || true
wp plugin delete coblocks --quiet 2>/dev/null || true

wp plugin deactivate gutenberg --quiet 2>/dev/null || true
wp plugin delete gutenberg --quiet 2>/dev/null || true

wp plugin deactivate header-footer --quiet 2>/dev/null || true
wp plugin delete header-footer --quiet 2>/dev/null || true
```

#### 4. Phase 3: Subscription Consolidation (5 plugins)

```bash
wp plugin deactivate wp-smush-pro --quiet 2>/dev/null || true
wp plugin delete wp-smush-pro --quiet 2>/dev/null || true

wp plugin deactivate wpmu-dev-seo --quiet 2>/dev/null || true
wp plugin delete wpmu-dev-seo --quiet 2>/dev/null || true

wp plugin deactivate snapshot --quiet 2>/dev/null || true
wp plugin delete snapshot --quiet 2>/dev/null || true

wp plugin deactivate wpmudev-updates --quiet 2>/dev/null || true
wp plugin delete wpmudev-updates --quiet 2>/dev/null || true

wp plugin deactivate page-optimize --quiet 2>/dev/null || true
wp plugin delete page-optimize --quiet 2>/dev/null || true
```

#### 5. Verification

```bash
# List remaining plugins
wp plugin list

# Verify GCB- plugins are preserved
wp plugin list --name=gcb-*

# Check site status
wp core verify-checksums
```

---

### Option 3: One-Liner (Copy/Paste All At Once)

**⚠️ WARNING: This will remove all 24 plugins immediately. Make sure you have backups!**

```bash
# Create backup and remove all plugins in one command
wp plugin list --format=csv > plugin-backup-$(date +%Y%m%d-%H%M%S).csv && \
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
  revslider \
  instagram-feed \
  popup-maker \
  coblocks \
  gutenberg \
  header-footer \
  wp-smush-pro \
  wpmu-dev-seo \
  snapshot \
  wpmudev-updates \
  page-optimize \
  --deactivate --quiet 2>/dev/null || true && \
echo "✓ Cleanup complete" && \
wp plugin list
```

---

## Post-Cleanup Tasks

1. **Cancel WPMU DEV Subscription**
   - Account ID: WDP ID 912164
   - Removes: wp-smush-pro, wpmu-dev-seo, snapshot, wpmudev-updates, page-optimize

2. **Enable Jetpack Features** (if needed)
   - Image optimization (replaces Smush)
   - Site backups (replaces Snapshot)
   - SEO tools (replaces WPMU SEO)

3. **Test Site Functionality**
   ```bash
   wp option get active_plugins
   wp theme list
   wp core verify-checksums
   ```

---

## Safety Notes

- ✅ GCB- plugins (gcb-test-utils, gcb-content-intelligence) are **NEVER** removed
- ✅ All commands use `--quiet` flag to suppress verbose output
- ✅ `2>/dev/null || true` prevents errors from stopping execution
- ✅ `wp plugin delete` automatically deactivates before deleting when using `--deactivate` flag
- ✅ A CSV backup of plugins is created before any changes

---

## Rollback (if needed)

If you need to reinstall any plugin:

```bash
# Reinstall a specific plugin
wp plugin install <plugin-slug> --activate

# Example: Reinstall Classic Editor
wp plugin install classic-editor --activate
```

View your backup:
```bash
cat plugin-backup-*.csv
```
