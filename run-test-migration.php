<?php
/**
 * Test Migration Script
 *
 * Migrates the 20 test posts from Fusion shortcodes to Gutenberg blocks.
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Load migration classes
require_once WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-node.php';
require_once WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-parser.php';
require_once WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/Converter/interface-gcb-transformer.php';
require_once WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/Converter/class-gcb-to-block-converter.php';

// Load all transformers
$transformer_path = WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/Converter/Transformers/';
require_once $transformer_path . 'class-gcb-container-transformer.php';
require_once $transformer_path . 'class-gcb-row-transformer.php';
require_once $transformer_path . 'class-gcb-column-transformer.php';
require_once $transformer_path . 'class-gcb-text-transformer.php';
require_once $transformer_path . 'class-gcb-youtube-transformer.php';
require_once $transformer_path . 'class-gcb-separator-transformer.php';
require_once $transformer_path . 'class-gcb-code-transformer.php';
require_once $transformer_path . 'class-gcb-button-transformer.php';
require_once $transformer_path . 'class-gcb-image-transformer.php';
require_once $transformer_path . 'class-gcb-gallery-transformer.php';
require_once $transformer_path . 'class-gcb-misc-transformer.php';

// Load migration service
require_once WP_PLUGIN_DIR . '/gcb-content-intelligence/migration/CLI/class-gcb-migration-service.php';

// Test posts from MIGRATION-TEST-PLAN.md
$test_posts = [
    3049,   // video|multi-col (1,542 posts)
    26909,  // mixed|multi-col (1,168 posts)
    6,      // text-only|columned (671 posts)
    16188,  // mixed|complex (377 posts)
    729,    // standard|multi-col (44 posts)
    91845,  // table|multi-col (28 posts)
    27615,  // text-only|multi-col (16 posts)
    32659,  // video-gallery|complex (13 posts)
    17475,  // video|columned (10 posts)
    28667,  // video|complex (8 posts)
    30756,  // multi-image|complex (4 posts)
    31474,  // mixed-heavy|complex (4 posts)
    33420,  // text-only|complex (3 posts)
    28597,  // text-only|single (1 post)
    28612,  // multi-image|single (1 post)
    28629,  // table|single (1 post)
    28632,  // standard|single (1 post)
    31092,  // multi-image|multi-col (1 post)
    33073,  // standard|complex (1 post)
    94615,  // table|complex (1 post)
];

// Check for specific post ID argument
$specific_post = isset($argv[1]) ? (int)$argv[1] : null;
if ($specific_post) {
    $test_posts = [$specific_post];
}

// Check for dry-run flag
$dry_run = in_array('--dry-run', $argv);

echo "========================================\n";
echo "  MIGRATION TEST RUNNER\n";
echo "========================================\n\n";
echo "Mode: " . ($dry_run ? "DRY RUN (preview only)" : "LIVE (will update database)") . "\n";
echo "Posts to migrate: " . count($test_posts) . "\n\n";

$migration_service = new GCB_Migration_Service();

$results = [
    'success' => 0,
    'no_change' => 0,
    'failed' => 0,
    'warnings' => 0,
];

foreach ($test_posts as $post_id) {
    $post = get_post($post_id);

    if (!$post) {
        echo "❌ Post $post_id: NOT FOUND\n";
        $results['failed']++;
        continue;
    }

    // Check if already migrated
    if (strpos($post->post_content, '<!-- wp:') !== false) {
        echo "⏭️  Post $post_id: Already has Gutenberg blocks (skipped)\n";
        $results['no_change']++;
        continue;
    }

    // Check if needs migration
    if (!$migration_service->needsMigration($post->post_content)) {
        echo "⏭️  Post $post_id: No Fusion shortcodes found (skipped)\n";
        $results['no_change']++;
        continue;
    }

    // Run migration
    $result = $migration_service->migrateContent($post->post_content, $dry_run);

    if (!$result->success) {
        echo "❌ Post $post_id: Migration FAILED\n";
        foreach ($result->errors as $error) {
            echo "   Error: $error\n";
        }
        $results['failed']++;
        continue;
    }

    if (!$result->hasChanges) {
        echo "⏭️  Post $post_id: No changes needed\n";
        $results['no_change']++;
        continue;
    }

    // Show warnings if any
    if (!empty($result->warnings)) {
        foreach ($result->warnings as $warning) {
            echo "   ⚠️  $warning\n";
        }
        $results['warnings']++;
    }

    $shortcodes_converted = $result->stats['shortcodes_converted'] ?? 0;

    if (!$dry_run) {
        // Backup original content
        update_post_meta($post_id, '_gcb_original_avada_content', $post->post_content);
        update_post_meta($post_id, '_gcb_migrated_at', current_time('mysql'));

        // Update post content
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $result->content,
        ]);

        echo "✅ Post $post_id: Migrated ($shortcodes_converted shortcodes converted)\n";
    } else {
        echo "🔍 Post $post_id: Would migrate ($shortcodes_converted shortcodes)\n";
    }

    $results['success']++;
}

echo "\n========================================\n";
echo "  SUMMARY\n";
echo "========================================\n";
echo "Successful: " . $results['success'] . "\n";
echo "Skipped (no change): " . $results['no_change'] . "\n";
echo "Failed: " . $results['failed'] . "\n";
echo "With warnings: " . $results['warnings'] . "\n";

if ($dry_run) {
    echo "\n💡 Run without --dry-run to apply changes.\n";
}
