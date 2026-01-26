<?php
/**
 * Migrate Fusion Template Posts to Regular Posts (Drafts)
 *
 * This script converts hidden fusion_template custom post types to regular posts:
 * - Converts fusion_template to post type 'post'
 * - Sets status to 'draft'
 * - Prepends "template - " to titles
 * - Migrates Fusion Builder content to Gutenberg blocks
 *
 * Upload this file to your WordPress root directory and run via:
 * - CLI: php migrate-template-posts.php [--dry-run]
 * - Browser: https://your-site.com/migrate-template-posts.php?key=YOUR_SECRET_KEY[&dry-run]
 *
 * IMPORTANT: Delete this file after migration is complete!
 */

// Security key - change this to something unique!
$security_key = 'gcb-migrate-templates-2026';

// Check if running from browser
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== $security_key) {
        die('Unauthorized. Add ?key=YOUR_SECRET_KEY to the URL.');
    }
    header('Content-Type: text/plain; charset=utf-8');
    ob_implicit_flush(true);
    ob_end_flush();
}

// Increase limits
ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/wp-load.php';

// Load migration classes
$plugin_dir = WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/';

if (!file_exists($plugin_dir . 'CLI/class-gcb-migration-service.php')) {
    die("ERROR: gcb-content-intelligence plugin not found. Please install the plugin first.\n");
}

require_once $plugin_dir . 'Parser/class-gcb-shortcode-node.php';
require_once $plugin_dir . 'Parser/class-gcb-shortcode-parser.php';
require_once $plugin_dir . 'Converter/interface-gcb-transformer.php';
require_once $plugin_dir . 'Converter/class-gcb-to-block-converter.php';

// Load all transformers
$transformer_path = $plugin_dir . 'Converter/Transformers/';
foreach (glob($transformer_path . 'class-gcb-*.php') as $file) {
    require_once $file;
}

require_once $plugin_dir . 'CLI/class-gcb-migration-service.php';

global $wpdb;

// Configuration
$dry_run = (php_sapi_name() === 'cli' && in_array('--dry-run', $argv)) ||
           (php_sapi_name() !== 'cli' && isset($_GET['dry-run']));

echo "========================================\n";
echo "  TEMPLATE POST MIGRATION\n";
echo "========================================\n\n";
echo "Mode: " . ($dry_run ? "DRY RUN (preview only)" : "LIVE (will update database)") . "\n\n";

// Get all fusion_template posts
echo "Fetching all fusion_template posts...\n";
flush();

$template_posts = $wpdb->get_results("
    SELECT ID, post_title, post_status, post_date
    FROM {$wpdb->posts}
    WHERE post_type = 'fusion_template'
    ORDER BY ID ASC
");

$total = count($template_posts);

if ($total == 0) {
    echo "\n✅ No fusion_template posts found. All done!\n";
    exit(0);
}

echo "Found $total fusion_template posts to migrate.\n\n";

$migration_service = new GCB_Migration_Service();

$stats = [
    'success' => 0,
    'skipped' => 0,
    'failed' => 0,
    'shortcodes_converted' => 0,
];

$failed_posts = [];
$start_time = microtime(true);

// Process each template post
foreach ($template_posts as $template) {
    $post = get_post($template->ID);

    if (!$post) {
        $stats['failed']++;
        $failed_posts[] = $template->ID;
        echo "❌ Failed to load post ID {$template->ID}\n";
        continue;
    }

    echo "Processing ID {$post->ID}: {$post->post_title}\n";

    // Check if title already starts with "template - " (case insensitive)
    $new_title = $post->post_title;
    if (stripos($new_title, 'template') !== 0) {
        $new_title = 'template - ' . $new_title;
    }

    // Migrate Fusion Builder content if needed
    $new_content = $post->post_content;
    if ($migration_service->needsMigration($post->post_content)) {
        echo "  → Migrating Fusion Builder content...\n";
        $result = $migration_service->migrateContent($post->post_content, $dry_run);

        if (!$result->success) {
            $stats['failed']++;
            $failed_posts[] = $post->ID;
            echo "  ❌ Migration failed\n";
            continue;
        }

        if ($result->hasChanges) {
            $new_content = $result->content;
            $stats['shortcodes_converted'] += $result->stats['shortcodes_converted'] ?? 0;
            echo "  → Converted {$result->stats['shortcodes_converted']} shortcodes\n";
        }
    } else {
        echo "  → No Fusion Builder content to migrate\n";
    }

    // Update the post
    if (!$dry_run) {
        // Store original data
        update_post_meta($post->ID, '_gcb_original_post_type', 'fusion_template');
        update_post_meta($post->ID, '_gcb_original_title', $post->post_title);
        update_post_meta($post->ID, '_gcb_original_status', $post->post_status);
        if ($new_content !== $post->post_content) {
            update_post_meta($post->ID, '_gcb_original_avada_content', $post->post_content);
        }
        update_post_meta($post->ID, '_gcb_migrated_at', current_time('mysql'));

        // Update post
        $updated = wp_update_post([
            'ID' => $post->ID,
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_title' => $new_title,
            'post_content' => $new_content,
        ], true);

        if (is_wp_error($updated)) {
            $stats['failed']++;
            $failed_posts[] = $post->ID;
            echo "  ❌ Failed to update post: " . $updated->get_error_message() . "\n";
            continue;
        }

        echo "  ✅ Converted to draft post\n";
        echo "  → New title: {$new_title}\n";
    } else {
        echo "  [DRY RUN] Would convert to:\n";
        echo "  → Type: post (draft)\n";
        echo "  → Title: {$new_title}\n";
    }

    $stats['success']++;
    echo "\n";
    flush();
}

$total_time = round(microtime(true) - $start_time, 1);

echo "========================================\n";
echo "  MIGRATION COMPLETE\n";
echo "========================================\n";
echo "Total time: {$total_time}s\n";
echo "Templates migrated: {$stats['success']}\n";
echo "Templates skipped: {$stats['skipped']}\n";
echo "Templates failed: {$stats['failed']}\n";
echo "Shortcodes converted: {$stats['shortcodes_converted']}\n";

if (!empty($failed_posts)) {
    echo "\nFailed post IDs: " . implode(', ', $failed_posts) . "\n";
}

if (!$dry_run) {
    echo "\n✅ All template posts have been:\n";
    echo "   - Converted to post type 'post'\n";
    echo "   - Set to 'draft' status\n";
    echo "   - Prefixed with 'template - ' in title\n";
    echo "   - Migrated from Fusion Builder to Gutenberg blocks\n";
    echo "   - Searchable in wp-admin Posts screen\n";
} else {
    echo "\nℹ️  This was a dry run. Run without --dry-run to apply changes.\n";
}

echo "\n⚠️  IMPORTANT: Delete this file after migration!\n";
