<?php
/**
 * Activate GCB Plugins
 *
 * Activates gcb-test-utils and gcb-content-intelligence plugins.
 *
 * Usage: php activate-gcb-plugins.php
 */

require_once __DIR__ . '/wp-load.php';

echo "🔌 Activating GCB Plugins...\n\n";

// Plugins to activate
$plugins = array(
    'gcb-test-utils/gcb-test-utils.php',
    'gcb-content-intelligence/gcb-content-intelligence.php',
);

// Get current active plugins (ensure it's an array)
$active_plugins = get_option('active_plugins', array());
if (!is_array($active_plugins)) {
    $active_plugins = array();
}

$updated = false;

foreach ($plugins as $plugin) {
    // Check if plugin exists
    $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
    if (!file_exists($plugin_file)) {
        echo "❌ ERROR: $plugin not found!\n";
        echo "   Location: $plugin_file\n";
        continue;
    }

    // Check if already active
    if (in_array($plugin, $active_plugins)) {
        echo "✓  $plugin is already active\n";
        continue;
    }

    // Add to active plugins
    $active_plugins[] = $plugin;
    $updated = true;
    echo "✅ Added $plugin to active plugins\n";
}

// Update the option if changes were made
if ($updated) {
    update_option('active_plugins', $active_plugins);
    echo "\n✅ Updated active_plugins option\n";
}

echo "\n✅ Done! GCB plugins activated.\n";
echo "🧪 Test endpoint: http://localhost:8881/wp-json/gcb-testing/v1/reset\n";
