<?php
/**
 * Activate GCB Brutalist Theme
 *
 * Switches from Avada to gcb-brutalist theme and displays sample posts.
 *
 * Usage: php activate-gcb-theme.php
 */

require_once __DIR__ . '/wp-load.php';

echo "🎨 Activating GCB Brutalist Theme...\n\n";

// Check if theme exists
$theme = wp_get_theme('gcb-brutalist');
if (!$theme->exists()) {
    echo "❌ ERROR: gcb-brutalist theme not found!\n";
    echo "   Location: wp-content/themes/gcb-brutalist/\n";
    exit(1);
}

// Switch theme
switch_theme('gcb-brutalist');

// Verify activation
$active_theme = get_option('stylesheet');
if ($active_theme !== 'gcb-brutalist') {
    echo "❌ ERROR: Theme activation failed\n";
    exit(1);
}

echo "✅ GCB Brutalist theme activated!\n\n";

// Display info about existing content
$post_count = wp_count_posts('post');
echo "📊 Your Content:\n";
echo "   Published posts: " . number_format($post_count->publish) . "\n";
echo "   Draft posts: " . number_format($post_count->draft) . "\n\n";

// Show 5 most recent posts
$recent_posts = get_posts(array(
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
));

echo "🚗 Recent Articles:\n";
foreach ($recent_posts as $post) {
    echo "   • " . $post->post_title . "\n";
    echo "     └─ " . get_permalink($post->ID) . "\n";
}

echo "\n✅ Done! Visit http://localhost:8881/ to see your articles with:\n";
echo "   - 3-column broken grid layout\n";
echo "   - Editorial Brutalism design\n";
echo "   - Video Rail and Bento Grid patterns\n\n";
echo "💡 TIP: Enjoy your " . number_format($post_count->publish) . " posts with clean, brutalist styling!\n";
