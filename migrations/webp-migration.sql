-- GCB WebP Migration Script
-- Generated: 2026-03-04
-- Purpose: Update all image references from JPG/JPEG/PNG to WebP
-- Target: Staging MySQL (replay of local SQLite changes)
-- Run AFTER uploading WebP files and deleting old JPG/PNG files on staging.

-- ============================================================
-- SECTION 1: Update post_content — /uploads/ paths only
-- ============================================================
-- .jpg → .webp (within upload paths)
UPDATE wp_posts SET post_content = REPLACE(post_content, '.jpg"', '.webp"')
WHERE post_content LIKE '%/uploads/%.jpg"%';

UPDATE wp_posts SET post_content = REPLACE(post_content, ".jpg'", ".webp'")
WHERE post_content LIKE "%/uploads/%.jpg'%";

UPDATE wp_posts SET post_content = REPLACE(post_content, '.jpg ', '.webp ')
WHERE post_content LIKE '%/uploads/%.jpg %';

UPDATE wp_posts SET post_content = REPLACE(post_content, '.jpg)', '.webp)')
WHERE post_content LIKE '%/uploads/%.jpg)%';

-- .jpeg → .webp
UPDATE wp_posts SET post_content = REPLACE(post_content, '.jpeg"', '.webp"')
WHERE post_content LIKE '%/uploads/%.jpeg"%';

UPDATE wp_posts SET post_content = REPLACE(post_content, ".jpeg'", ".webp'")
WHERE post_content LIKE "%/uploads/%.jpeg'%";

-- .png → .webp
UPDATE wp_posts SET post_content = REPLACE(post_content, '.png"', '.webp"')
WHERE post_content LIKE '%/uploads/%.png"%';

UPDATE wp_posts SET post_content = REPLACE(post_content, ".png'", ".webp'")
WHERE post_content LIKE "%/uploads/%.png'%";

UPDATE wp_posts SET post_content = REPLACE(post_content, '.png ', '.webp ')
WHERE post_content LIKE '%/uploads/%.png %';

UPDATE wp_posts SET post_content = REPLACE(post_content, '.png)', '.webp)')
WHERE post_content LIKE '%/uploads/%.png)%';

-- ============================================================
-- SECTION 2: Update wp_postmeta
-- ============================================================
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.jpg"', '.webp"')
WHERE meta_value LIKE '%/uploads/%.jpg"%';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.jpeg"', '.webp"')
WHERE meta_value LIKE '%/uploads/%.jpeg"%';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.png"', '.webp"')
WHERE meta_value LIKE '%/uploads/%.png"%';

-- Serialized metadata (bare filenames without /uploads/ prefix)
-- These use s:NN:"filename.jpg" format
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.jpg";', '.webp";')
WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%.jpg";%';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.jpeg";', '.webp";')
WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%.jpeg";%';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '.png";', '.webp";')
WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%.png";%';

-- ============================================================
-- SECTION 3: Update attachment records
-- ============================================================
UPDATE wp_posts SET guid = REPLACE(guid, '.jpg', '.webp')
WHERE post_type = 'attachment' AND guid LIKE '%/uploads/%.jpg';

UPDATE wp_posts SET guid = REPLACE(guid, '.jpeg', '.webp')
WHERE post_type = 'attachment' AND guid LIKE '%/uploads/%.jpeg';

UPDATE wp_posts SET guid = REPLACE(guid, '.png', '.webp')
WHERE post_type = 'attachment' AND guid LIKE '%/uploads/%.png';

UPDATE wp_posts SET post_mime_type = 'image/webp'
WHERE post_type = 'attachment'
AND post_mime_type IN ('image/jpeg', 'image/png')
AND guid LIKE '%.webp';

-- ============================================================
-- SECTION 4: Fix double-extension files (WordPress.com CDN legacy)
-- Pattern: .webp(stuff).jpg should be .jpg(stuff).webp
-- ============================================================
-- These exist as filename.jpg.webp or filename.jpg-NxN.webp on disk
-- but may have been incorrectly rewritten. Swap back:
UPDATE wp_posts SET post_content = REPLACE(post_content, '.webp.jpg', '.jpg.webp')
WHERE post_content LIKE '%/uploads/%.webp.jpg%';

UPDATE wp_posts SET post_content = REPLACE(post_content, '.webp-', '.jpg-')
WHERE post_content LIKE '%/uploads/%.webp-%' AND post_content LIKE '%.jpg%';
-- Note: Section 4 may need manual review on staging. Only ~50 refs affected.

-- ============================================================
-- SECTION 5: Serialized string length fix
-- ============================================================
-- IMPORTANT: MySQL REPLACE changes string content but NOT PHP serialized
-- string length prefixes (s:NN:"..."). For staging, run the Python script
-- migrations/fix-serialized-lengths.py against the MySQL DB after these
-- SQL updates, OR use WP-CLI:
--   wp search-replace '.jpg' '.webp' --precise --all-tables
-- which handles serialized data correctly.

-- ============================================================
-- SECTION 6: Verification (run after migration)
-- ============================================================
-- SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%/uploads/%.jpg"%';
-- SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%/uploads/%.png"%';
-- SELECT COUNT(*) FROM wp_postmeta WHERE meta_value LIKE '%/uploads/%.jpg"%' AND meta_value NOT LIKE '%files.wordpress.com%';
-- SELECT COUNT(*) FROM wp_posts WHERE post_type='attachment' AND post_mime_type IN ('image/jpeg','image/png');

-- ============================================================
-- SECTION 7: Clean orphaned thumbnail metadata
-- ============================================================
-- After deleting orphaned thumbnail files, the serialized
-- _wp_attachment_metadata still references sizes that no longer exist.
-- 
-- MySQL can't easily parse PHP serialized data in SQL.
-- Run this Python script on staging after file cleanup:
--   python3 migrations/clean-orphan-metadata-staging.py
--
-- Or use WP-CLI on staging:
--   wp eval-file migrations/clean-orphan-metadata.php
--
-- Registered sizes to KEEP: thumbnail(150x150), medium(300xN),
--   medium_large(768xN), large(1200xN), 1536x1536, 2048x2048, scaled
-- Everything else in the sizes array should be removed.

-- ============================================================
-- SECTION 8: Delete orphaned thumbnail FILES on staging
-- ============================================================
-- Run on staging SSH:
-- cd ~/htdocs/wp-content/uploads
-- find . -name "*-[0-9]*x[0-9]*.webp" \
--   ! -name "*-150x150.*" \
--   ! -name "*-300x[0-9]*.*" \
--   ! -name "*-768x[0-9]*.*" \
--   ! -name "*-1200x[0-9]*.*" \
--   ! -name "*-1536x[0-9]*.*" \
--   ! -name "*-2048x[0-9]*.*" \
--   -delete
-- find . -name "*@2x*.webp" -delete
