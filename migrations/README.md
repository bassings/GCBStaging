# WebP Migration

## What happened
- Converted all JPG/JPEG/PNG images in `wp-content/uploads/` to WebP using `cwebp -q 82`
- Deleted original files after successful conversion
- Updated local SQLite database to reference .webp files
- Cleaned up corrupt/0-byte files and orphaned DB references

## Files
- `webp-migration.sql` — MySQL-compatible script to replay all DB changes on staging
- `webp-failures.txt` — List of files that failed conversion (with reasons)
- `webp-deleted-orphans.txt` — Files deleted that had no valid content

## How to apply to staging
1. Upload converted WebP files (rsync or Studio push)
2. Delete old JPG/PNG files on staging
3. Run `webp-migration.sql` against staging MySQL
4. Verify with the queries in Section 5 of the script
