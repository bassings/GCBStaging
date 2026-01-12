<?php
/**
 * Batch Migration Script
 *
 * Upload this file to your WordPress root directory and run via:
 * - CLI: php migrate-all-posts.php
 * - Browser: https://your-staging-site.com/migrate-all-posts.php?key=YOUR_SECRET_KEY
 *
 * IMPORTANT: Delete this file after migration is complete!
 */

// Security key - change this to something unique!
$security_key = 'gcb-migrate-2026';

// Check if running from browser
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== $security_key) {
        die('Unauthorized. Add ?key=YOUR_SECRET_KEY to the URL.');
    }
    // Output as plain text for browser
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
$batch_size = 50;
$dry_run = (php_sapi_name() === 'cli' && in_array('--dry-run', $argv)) ||
           (php_sapi_name() !== 'cli' && isset($_GET['dry-run']));

echo "========================================\n";
echo "  FULL MIGRATION - ALL POSTS\n";
echo "========================================\n\n";
echo "Mode: " . ($dry_run ? "DRY RUN (preview only)" : "LIVE (will update database)") . "\n\n";

// Get total count
$total = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts}
    WHERE post_content LIKE '%[fusion_%'
    AND post_status = 'publish'
    AND post_type = 'post'
");

if ($total == 0) {
    echo "No posts need migration. All done!\n";
    exit(0);
}

echo "Total posts to process: $total\n";
echo "Batch size: $batch_size\n\n";

$migration_service = new GCB_Migration_Service();

$stats = [
    'success' => 0,
    'skipped' => 0,
    'failed' => 0,
    'shortcodes_converted' => 0,
];

$failed_posts = [];
$offset = 0;
$batch_num = 0;
$start_time = microtime(true);

while (true) {
    $batch_num++;

    $post_ids = $wpdb->get_col($wpdb->prepare("
        SELECT ID FROM {$wpdb->posts}
        WHERE post_content LIKE '%s'
        AND post_status = 'publish'
        AND post_type = 'post'
        ORDER BY ID ASC
        LIMIT %d OFFSET %d
    ", '%[fusion_%', $batch_size, $offset));

    if (empty($post_ids)) {
        break;
    }

    echo "Batch $batch_num: Processing " . count($post_ids) . " posts...\n";
    flush();

    foreach ($post_ids as $post_id) {
        $post = get_post($post_id);

        if (!$post) {
            $stats['failed']++;
            continue;
        }

        if (strpos($post->post_content, '<!-- wp:') !== false &&
            strpos($post->post_content, '[fusion_') === false) {
            $stats['skipped']++;
            continue;
        }

        if (!$migration_service->needsMigration($post->post_content)) {
            $stats['skipped']++;
            continue;
        }

        $result = $migration_service->migrateContent($post->post_content, $dry_run);

        if (!$result->success) {
            $stats['failed']++;
            $failed_posts[] = $post_id;
            continue;
        }

        if (!$result->hasChanges) {
            $stats['skipped']++;
            continue;
        }

        $stats['shortcodes_converted'] += $result->stats['shortcodes_converted'] ?? 0;

        if (!$dry_run) {
            update_post_meta($post_id, '_gcb_original_avada_content', $post->post_content);
            update_post_meta($post_id, '_gcb_migrated_at', current_time('mysql'));

            wp_update_post([
                'ID' => $post_id,
                'post_content' => $result->content,
            ]);
        }

        $stats['success']++;
    }

    $progress = round(($offset + count($post_ids)) / $total * 100, 1);
    echo "  Progress: {$progress}% | Success: {$stats['success']} | Failed: {$stats['failed']}\n";
    flush();

    wp_cache_flush();
    $offset += $batch_size;
}

$total_time = round(microtime(true) - $start_time, 1);

echo "\n========================================\n";
echo "  MIGRATION COMPLETE\n";
echo "========================================\n";
echo "Total time: {$total_time}s\n";
echo "Posts migrated: {$stats['success']}\n";
echo "Posts skipped: {$stats['skipped']}\n";
echo "Posts failed: {$stats['failed']}\n";
echo "Shortcodes converted: {$stats['shortcodes_converted']}\n";

if (!empty($failed_posts)) {
    echo "\nFailed post IDs: " . implode(', ', array_slice($failed_posts, 0, 20)) . "\n";
}

echo "\n⚠️  IMPORTANT: Delete this file after migration!\n";
