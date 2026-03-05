#!/usr/bin/env php
<?php
/**
 * GCB Fix Attachment Metadata
 * Updates _wp_attachment_metadata for WebP-converted images:
 * 1. Changes 'file' from .jpg/.png to .webp
 * 2. Populates 'sizes' array from physical thumbnail files on disk
 * 
 * Usage: wp eval-file /tmp/gcb-fix-attachment-meta.php
 * Dry run: DRY_RUN=1 wp eval-file /tmp/gcb-fix-attachment-meta.php
 * 
 * Results (2026-03-05 staging):
 *   41,716 attachments processed
 *   11,488 file extensions fixed (.jpg/.png → .webp)
 *   21,288 sizes arrays populated (enabling srcset generation)
 *   83 skipped, 0 errors
 *   Mobile Lighthouse: 81 → 89 (+8), LCP: ~5s → 3.7s
 */

$dry_run = !empty(getenv("DRY_RUN"));
$upload_dir = wp_upload_dir();
$upload_base = $upload_dir['basedir'];

$sizes_to_register = [
    'thumbnail'    => ['w' => 150, 'h' => 150, 'crop' => true],
    'medium'       => ['w' => 300, 'h' => 300, 'crop' => false],
    'medium_large' => ['w' => 768, 'h' => 0,   'crop' => false],
    'large'        => ['w' => 1200,'h' => 0,   'crop' => false],
    'newspack-article-block-landscape-large' => ['w' => 1200, 'h' => 900, 'crop' => true],
    'newspack-article-block-uncropped'       => ['w' => 1200, 'h' => 0,   'crop' => false],
];

global $wpdb;

// Get all attachments with image mime types
$attachments = $wpdb->get_results("
    SELECT p.ID, pm.meta_value 
    FROM {$wpdb->posts} p 
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
    WHERE p.post_type = 'attachment' 
    AND p.post_mime_type LIKE 'image/%'
    ORDER BY p.ID
");

$fixed_file = 0;
$fixed_sizes = 0;
$skipped = 0;
$errors = 0;
$total = count($attachments);

echo ($dry_run ? "[DRY RUN] " : "") . "Processing $total attachments...\n";

foreach ($attachments as $att) {
    $meta = maybe_unserialize($att->meta_value);
    if (!is_array($meta) || empty($meta['file'])) {
        $skipped++;
        continue;
    }

    $changed = false;
    $file = $meta['file'];
    $dir = dirname($file);
    $full_path = $upload_base . '/' . $file;

    // 1. Fix file extension: .jpg/.jpeg/.png -> .webp
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $webp_file = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
        $webp_full = $upload_base . '/' . $webp_file;
        
        if (file_exists($webp_full)) {
            $meta['file'] = $webp_file;
            $full_path = $webp_full;
            $file = $webp_file;
            $changed = true;
            $fixed_file++;
        } else {
            // No WebP version exists, skip
            $skipped++;
            continue;
        }
    }

    // 2. Populate sizes from physical thumbnail files
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'webp') {
        $skipped++;
        continue;
    }

    $basename = pathinfo(basename($file), PATHINFO_FILENAME);
    $file_dir = $upload_base . '/' . $dir;
    
    // Get original dimensions
    $orig_w = $meta['width'] ?? 0;
    $orig_h = $meta['height'] ?? 0;
    if (!$orig_w || !$orig_h) {
        $info = @getimagesize($full_path);
        if ($info) {
            $orig_w = $info[0];
            $orig_h = $info[1];
            $meta['width'] = $orig_w;
            $meta['height'] = $orig_h;
        } else {
            $skipped++;
            continue;
        }
    }

    $sizes_changed = false;
    if (!isset($meta['sizes']) || !is_array($meta['sizes'])) {
        $meta['sizes'] = [];
    }

    foreach ($sizes_to_register as $size_name => $size_def) {
        $max_w = $size_def['w'];
        $max_h = $size_def['h'] ?: 9999;
        $crop = $size_def['crop'];

        if ($crop) {
            $ratio = max($max_w / $orig_w, $max_h / $orig_h);
            if ($ratio >= 1 && $orig_w < $max_w && $orig_h < $max_h) continue;
            $new_w = $max_w;
            $new_h = $max_h;
        } else {
            $ratio = min($max_w / $orig_w, $max_h / $orig_h);
            if ($ratio >= 1) continue; // Original smaller than target
            $new_w = (int) round($orig_w * $ratio);
            $new_h = (int) round($orig_h * $ratio);
        }

        $thumb_file = $basename . '-' . $new_w . 'x' . $new_h . '.webp';
        $thumb_path = $file_dir . '/' . $thumb_file;

        // Only register if physical file exists
        if (file_exists($thumb_path)) {
            $existing = $meta['sizes'][$size_name] ?? null;
            $needs_update = !$existing 
                || ($existing['file'] ?? '') !== $thumb_file
                || !empty($existing['virtual']);

            if ($needs_update) {
                $meta['sizes'][$size_name] = [
                    'file'      => $thumb_file,
                    'width'     => $new_w,
                    'height'    => $new_h,
                    'mime-type' => 'image/webp',
                ];
                $sizes_changed = true;
            }
        }
    }

    if ($sizes_changed) {
        $changed = true;
        $fixed_sizes++;
    }

    if ($changed && !$dry_run) {
        update_post_meta($att->ID, '_wp_attachment_metadata', $meta);
    }

    // Progress every 1000
    $processed = $fixed_file + $fixed_sizes + $skipped + $errors;
    if ($processed % 1000 === 0) {
        echo "  Progress: $processed / $total (file: $fixed_file, sizes: $fixed_sizes, skip: $skipped)\n";
        flush();
    }
}

$mode = $dry_run ? '[DRY RUN] ' : '';
echo "\n{$mode}Done:\n";
echo "  File ext fixed: $fixed_file\n";
echo "  Sizes populated: $fixed_sizes\n";
echo "  Skipped: $skipped\n";
echo "  Errors: $errors\n";
echo "  Total: $total\n";
