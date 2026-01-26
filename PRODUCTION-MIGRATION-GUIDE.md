# Production Migration Guide: Fusion Builder to Gutenberg

**Date Created:** January 27, 2026
**Purpose:** Migrate all Fusion Builder shortcodes to Gutenberg blocks on production
**Expected Duration:** ~10 minutes for 3,894 posts

---

## ⚠️ CRITICAL: Pre-Migration Checklist

- [ ] **BACKUP DATABASE** - This is NOT optional!
- [ ] **Test on staging first** - Verify migration works correctly
- [ ] **Schedule during off-peak hours** - Minimize user impact
- [ ] **Verify GCB Content Intelligence plugin is active**
- [ ] **Clear your schedule** - Monitor the migration process

---

## Step 1: Backup Database (REQUIRED)

```bash
# Create timestamped backup
wp db export backup-before-migration-$(date +%Y%m%d-%H%M%S).sql

# Verify backup was created
ls -lh backup-before-migration-*.sql
```

**Expected output:** File showing size (should be several hundred MB)

---

## Step 2: Deactivate Fusion Builder Plugins

```bash
wp plugin deactivate fusion-builder fusion-core fusion-white-label-branding
```

**Expected output:**
```
Plugin 'fusion-builder' deactivated.
Plugin 'fusion-core' deactivated.
Plugin 'fusion-white-label-branding' deactivated.
Success: Deactivated 3 of 3 plugins.
```

---

## Step 3: Test Migration (Dry Run - RECOMMENDED)

```bash
# Test with 10 posts first (no changes to database)
wp gcb migrate_posts --dry-run --limit=10
```

**What to check:**
- No errors or PHP warnings
- "Success: Dry run complete!" message
- Review converted content preview

---

## Step 4: Run Full Migration

### Option A: Run with monitoring (Recommended)

```bash
wp gcb migrate_posts --batch-size=50 | tee migration-production-$(date +%Y%m%d-%H%M%S).log
```

### Option B: Run in background

```bash
wp gcb migrate_posts --batch-size=50 > migration-production-$(date +%Y%m%d-%H%M%S).log 2>&1 &

# Get the process ID
echo $!

# Monitor progress
tail -f migration-production-*.log
```

**Expected output:**
```
🚀 Starting Avada to Gutenberg migration...

📊 Found 3894 posts with Fusion Builder shortcodes
   Post Type: post
   Status: publish
   Batch Size: 50

[Progress bar...]

Success: ✅ Migration complete!

📈 Results:
   ✅ Migrated: 3894
   ⏭️  Skipped: 0
   ❌ Failed: 0
   ⚠️  Warnings: 0
   🔄 Shortcodes converted: 109883
```

---

## Step 5: Verify Migration Success

```bash
# Create verification script
cat > verify-migration.php << 'EOF'
<?php
$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids'
);

$all_posts = get_posts($args);
$fusion_count = 0;

foreach ($all_posts as $post_id) {
    $content = get_post_field('post_content', $post_id);
    if (strpos($content, '[fusion_') !== false) {
        $fusion_count++;
    }
}

echo "Total posts: " . count($all_posts) . PHP_EOL;
echo "Posts with Fusion shortcodes: " . $fusion_count . PHP_EOL;
echo "Posts without Fusion: " . (count($all_posts) - $fusion_count) . PHP_EOL;
echo "Migration complete: " . ($fusion_count === 0 ? "✅ YES" : "❌ NO") . PHP_EOL;
EOF

# Run verification
wp eval-file verify-migration.php
```

**Expected output:**
```
Total posts: 5653
Posts with Fusion shortcodes: 0
Posts without Fusion: 5653
Migration complete: ✅ YES
```

---

## Step 6: Clear All Caches

```bash
# Clear WordPress caches
wp cache flush
wp rewrite flush

# If you have object cache (Redis/Memcached)
wp cache flush --all

# Clear transients
wp transient delete --all

# Jetpack cache (if applicable)
wp jetpack module deactivate photon
wp jetpack module activate photon
```

**Additional cache clearing:**
- **Browser cache:** Cmd+Shift+R (Mac) or Ctrl+Shift+F5 (Windows)
- **CDN cache:** Purge via Cloudflare/Fastly/CloudFront dashboard
- **WP Super Cache/W3 Total Cache:** Purge from WordPress admin

---

## Step 7: Test Posts on Frontend

**Test these scenarios:**

1. **YouTube video posts:**
   - Videos load with lite-youtube facade (thumbnail + play button)
   - Click play button loads full video
   - No console errors in browser dev tools

2. **Gallery posts:**
   - Images display correctly
   - Lightbox still works (if applicable)
   - No broken layouts

3. **Standard posts:**
   - Text formatting preserved
   - Tables display correctly
   - No raw shortcode text visible

---

## Batch Size Recommendations

| Environment | Batch Size | Notes |
|-------------|------------|-------|
| Local Development | `--batch-size=100` | Fastest |
| Staging (WP.com) | `--batch-size=50` | Recommended |
| Production (WP.com) | `--batch-size=50` | Stable, moderate memory |
| Shared Hosting | `--batch-size=25` | Safer for limited resources |
| Large sites (>10k posts) | `--batch-size=25` | Prevents timeouts |

---

## Troubleshooting

### Migration Stalls or Times Out

```bash
# Reduce batch size
wp gcb migrate_posts --batch-size=25

# Or process specific posts
wp gcb migrate_posts --post-id=12345
```

### Some Posts Failed to Migrate

```bash
# Check for specific errors in log
grep "Failed" migration-production-*.log

# Retry specific post
wp gcb migrate_posts --post-id=FAILED_POST_ID
```

### YouTube Videos Not Loading

1. Check browser console for JavaScript errors
2. Verify `lite-youtube-embed.js` is loading:
   ```bash
   curl -I https://yourdomain.com/wp-content/themes/gcb-brutalist/assets/js/lite-youtube-embed.js
   ```
3. Clear browser cache completely

### Posts Show Raw Shortcode Text

This means migration didn't convert that shortcode type. Check:
```bash
# Find posts with specific shortcode
wp post list --post_type=post --format=table --fields=ID,post_title | xargs -I {} wp post get {} --field=post_content | grep "\[fusion_"
```

---

## Rollback Procedure (If Needed)

### Option 1: Restore from Database Backup

```bash
# Import backup
wp db import backup-before-migration-TIMESTAMP.sql

# Reactivate Fusion Builder
wp plugin activate fusion-builder fusion-core fusion-white-label-branding
```

### Option 2: Restore from Post Meta Backup

The migration stores original content in post meta:
- `_gcb_original_avada_content` - Original Fusion content
- `_gcb_migrated_at` - Migration timestamp

```bash
# Create rollback script
cat > rollback-migration.php << 'EOF'
<?php
$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_gcb_original_avada_content',
    'fields' => 'ids'
);

$migrated_posts = get_posts($args);
$restored = 0;

foreach ($migrated_posts as $post_id) {
    $original = get_post_meta($post_id, '_gcb_original_avada_content', true);
    if (!empty($original)) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $original
        ));
        delete_post_meta($post_id, '_gcb_original_avada_content');
        delete_post_meta($post_id, '_gcb_migrated_at');
        $restored++;
    }
}

echo "Restored {$restored} posts to original Fusion content" . PHP_EOL;
EOF

# Run rollback
wp eval-file rollback-migration.php
```

---

## Post-Migration Performance Monitoring

### Test Core Web Vitals

1. **Run Lighthouse audit:**
   - Open Chrome DevTools (F12)
   - Go to Lighthouse tab
   - Run audit for "Performance" category
   - Check LCP score (should be under 2.5s)

2. **Test with Google PageSpeed Insights:**
   - https://pagespeed.web.dev/
   - Enter your site URL
   - Check mobile LCP score

3. **Monitor CrUX data:**
   - https://lookerstudio.google.com/reporting/bbc5698d-57bb-4969-9e07-68810b9fa348
   - Wait 28 days for data to update

### Expected Performance Improvements

| Metric | Before | Target | Impact |
|--------|--------|--------|--------|
| **LCP** | 4689ms | <2500ms | -2200ms |
| **Page Weight** | ~5MB | ~3MB | -40% |
| **YouTube Load** | 800KB+ | 50KB | -94% |
| **Fusion JS/CSS** | ~500KB | 0KB | -100% |

---

## Support & Documentation

**Migration Code Location:**
- `wp-content/plugins/gcb-content-intelligence/migration/`
- Main service: `class-gcb-migration-service.php`
- CLI commands: `includes/class-gcb-cli-commands.php`

**Logs & Backups:**
- Database backup: `backup-before-migration-*.sql`
- Migration log: `migration-production-*.log`
- Original content: Post meta `_gcb_original_avada_content`

**Related Files:**
- Theme functions: `wp-content/themes/gcb-brutalist/functions.php`
- Lite-YouTube: `wp-content/themes/gcb-brutalist/assets/js/lite-youtube-embed.js`
- LCP CSS: `wp-content/themes/gcb-brutalist/style.css`

---

## Quick Reference Commands

```bash
# Full migration workflow (copy/paste)
wp db export backup-before-migration-$(date +%Y%m%d-%H%M%S).sql && \
wp plugin deactivate fusion-builder fusion-core fusion-white-label-branding && \
wp gcb migrate_posts --batch-size=50 | tee migration-production-$(date +%Y%m%d-%H%M%S).log && \
wp eval-file verify-migration.php && \
wp cache flush && \
wp rewrite flush

# Verify migration
wp eval-file verify-migration.php

# Check single post
wp post get POST_ID --field=post_content | head -50

# Find posts with specific shortcode (if any remain)
wp post list --post_type=post --format=ids | xargs -I {} wp post get {} --field=post_content 2>/dev/null | grep -l "\[fusion_"
```

---

## Success Criteria

✅ Migration completed with 0 failures
✅ `verify-migration.php` shows 0 Fusion shortcodes
✅ Sample posts load correctly on frontend
✅ YouTube videos use lite-youtube facade
✅ No console errors in browser dev tools
✅ Lighthouse LCP score improved
✅ Database backup saved securely

---

**Last Updated:** January 27, 2026
**Migration Successfully Completed on Local:** ✅ Yes (3,894 posts, 109,883 shortcodes)
**Ready for Production:** ✅ Yes
