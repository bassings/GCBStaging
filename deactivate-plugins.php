<?php
/**
 * Helper script to deactivate plugins without WP-CLI
 * Run this from command line: php deactivate-plugins.php
 */

// Plugins to deactivate
$plugins_to_remove = [
    'polldaddy/polldaddy.php',
    'duplicate-page/duplicatepage.php',
    'classic-editor/classic-editor.php',
    'layout-grid/index.php',
    'coblocks/class-coblocks.php',
    'gutenberg/gutenberg.php',
    'amp/amp.php',
    'pwa/pwa.php',
];

// Path to WordPress
define('WP_USE_THEMES', false);
require_once('./wp-load.php');

// Get current active plugins
$active_plugins = get_option('active_plugins', []);

echo "Current active plugins: " . count($active_plugins) . "\n";

// Remove the specified plugins
$updated_plugins = array_diff($active_plugins, $plugins_to_remove);
$updated_plugins = array_values($updated_plugins); // Re-index array

// Update the option
update_option('active_plugins', $updated_plugins);

echo "Updated active plugins: " . count($updated_plugins) . "\n";
echo "Deactivated " . (count($active_plugins) - count($updated_plugins)) . " plugins\n";

// Show which plugins were deactivated
$deactivated = array_intersect($active_plugins, $plugins_to_remove);
if (!empty($deactivated)) {
    echo "\nDeactivated plugins:\n";
    foreach ($deactivated as $plugin) {
        echo "  - $plugin\n";
    }
}

echo "\nDone!\n";
