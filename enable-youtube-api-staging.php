<?php
/**
 * Enable YouTube API on Staging Site
 *
 * Run this script ONCE on staging to:
 * 1. Disable test mode (GCB_TEST_KEY)
 * 2. Enable YouTube API key via WordPress option
 * 3. Clear cached mock videos
 *
 * DELETE THIS FILE after running!
 */

// Security: Only allow execution if WordPress is loaded
if (!defined('ABSPATH')) {
    require_once __DIR__ . '/wp-load.php';
}

echo "🚀 Enabling YouTube API on Staging\n";
echo "====================================\n\n";

// Step 1: Override test mode (in case GCB_TEST_KEY is defined in wp-config.php)
update_option('gcb_force_real_api', true);
echo "✅ Disabled test mode (forcing real API)\n";

// Step 2: Set YouTube API key via WordPress option
// (WordPress.com doesn't allow editing wp-config.php)
$api_key = 'AIzaSyDN1Bcu2C-YEPiJMP-kCATezfaE7cNKYK4';
update_option('gcb_youtube_api_key', $api_key);
echo "✅ Set YouTube API key via WordPress option\n";

// Step 3: Clear cached mock videos
delete_transient('gcb_youtube_channel_videos');
echo "✅ Cleared cached mock videos\n\n";

// Step 4: Test API and fetch real videos
echo "📺 Fetching real videos from YouTube channel...\n";
require_once __DIR__ . '/wp-content/plugins/gcb-content-intelligence/includes/class-gcb-youtube-channel-fetcher.php';

$videos = GCB_YouTube_Channel_Fetcher::get_videos();

if (empty($videos)) {
    echo "❌ Failed to fetch videos\n";
    echo "   Check error_log for details\n";
} else {
    echo "✅ Successfully fetched " . count($videos) . " videos:\n\n";

    foreach (array_slice($videos, 0, 3) as $index => $video) {
        echo ($index + 1) . ". " . ($video['title'] ?? 'Untitled') . "\n";
        echo "   Duration: " . ($video['duration'] ?? 'N/A') . "\n\n";
    }

    echo "   ...and " . (count($videos) - 3) . " more\n\n";
}

echo "====================================\n";
echo "✅ Setup complete!\n\n";
echo "⚠️  IMPORTANT: DELETE THIS FILE NOW for security\n";
echo "   Run: rm enable-youtube-api-staging.php\n";
