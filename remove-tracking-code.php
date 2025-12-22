<?php
/**
 * Remove tracking code from header-footer plugin and deactivate it
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "Removing all advertising and GTM code...\n\n";

// Delete the header-footer plugin settings (contains GTM, AdSense, etc.)
$deleted = delete_option('hefo');
echo ($deleted ? "✓" : "✗") . " Deleted 'hefo' option (header-footer settings)\n";

// Deactivate the plugin
$active_plugins = get_option('active_plugins', []);
$plugin_to_remove = 'header-footer/plugin.php';
$updated_plugins = array_diff($active_plugins, [$plugin_to_remove]);
$updated_plugins = array_values($updated_plugins);
update_option('active_plugins', $updated_plugins);

echo "✓ Deactivated header-footer plugin\n";
echo "\nRemoved tracking code:\n";
echo "  - Google Tag Manager (GTM-5T5NDMG)\n";
echo "  - Google AdSense (ca-pub-3464605412590456)\n";
echo "  - Mailchimp tracking script\n";
echo "  - Pinterest domain verification\n";
echo "  - Microsoft validation\n";
echo "\nDone!\n";
