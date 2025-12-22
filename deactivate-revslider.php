<?php
/**
 * Deactivate Revolution Slider (not in use)
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "Deactivating Revolution Slider...\n\n";

// Deactivate the plugin
$active_plugins = get_option('active_plugins', []);
$plugin_to_remove = 'revslider/revslider.php';
$updated_plugins = array_diff($active_plugins, [$plugin_to_remove]);
$updated_plugins = array_values($updated_plugins);
update_option('active_plugins', $updated_plugins);

echo "✓ Deactivated Revolution Slider\n";
echo "  (LayerSlider is kept - actively used on 2 pages)\n";
echo "\nDone!\n";
