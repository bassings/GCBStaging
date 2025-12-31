<?php
/**
 * Deactivate LayerSlider Plugin
 *
 * Quick utility to deactivate LayerSlider which is causing memory issues.
 * Also activates gcb-test-utils if not already active.
 *
 * Usage: php deactivate-layerslider.php
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

echo "Current active plugins:\n";
$active_plugins = get_option('active_plugins', array());
print_r($active_plugins);
echo "\n";

// Deactivate LayerSlider
$layerslider_plugin = 'LayerSlider/layerslider.php';
if (in_array($layerslider_plugin, $active_plugins, true)) {
    $active_plugins = array_diff($active_plugins, array($layerslider_plugin));
    $active_plugins = array_values($active_plugins); // Re-index array
    update_option('active_plugins', $active_plugins);
    echo "✅ LayerSlider deactivated\n";
} else {
    echo "ℹ️  LayerSlider is not active\n";
}

// Activate gcb-test-utils
$gcb_test_utils = 'gcb-test-utils/gcb-test-utils.php';
if (!in_array($gcb_test_utils, $active_plugins, true)) {
    $active_plugins[] = $gcb_test_utils;
    update_option('active_plugins', $active_plugins);
    echo "✅ gcb-test-utils activated\n";
} else {
    echo "ℹ️  gcb-test-utils is already active\n";
}

echo "\nUpdated active plugins:\n";
print_r(get_option('active_plugins'));
echo "\n✅ Done! Try loading the site now.\n";
