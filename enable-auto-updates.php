<?php
/**
 * Enable auto-updates for safe, stable plugins
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "Enabling auto-updates for stable plugins...\n\n";

// Plugins safe for auto-updates (WordPress.org, stable)
$auto_update_plugins = [
    'akismet/akismet.php',
    'code-snippets/code-snippets.php',
    'crowdsignal-forms/crowdsignal-forms.php',
    'health-check/health-check.php',
    'instagram-feed/instagram-feed.php',
    'jetpack/jetpack.php',
    'jetpack-boost/jetpack-boost.php',
    'onesignal-free-web-push-notifications/onesignal.php',
    'popup-maker/popup-maker.php',
    'taxonomy-terms-order/taxonomy-terms-order.php',
    'wp-category-permalink/wp-category-permalink.php',
    'insert-headers-and-footers/ihaf.php',
];

// Get current auto-update settings
$auto_updates = (array) get_site_option( 'auto_update_plugins', array() );
$initial_count = count($auto_updates);

// Add each plugin to auto-updates
$added = 0;
foreach ( $auto_update_plugins as $plugin ) {
    if ( ! in_array( $plugin, $auto_updates, true ) ) {
        $auto_updates[] = $plugin;
        $added++;
        echo "  ✓ Enabled: " . $plugin . "\n";
    } else {
        echo "  - Already enabled: " . $plugin . "\n";
    }
}

// Update the option
update_site_option( 'auto_update_plugins', $auto_updates );

echo "\n" . str_repeat("=", 60) . "\n";
echo "Summary:\n";
echo "  Plugins with auto-updates: " . count($auto_updates) . "\n";
echo "  Newly enabled: " . $added . "\n";

echo "\nPlugins requiring manual updates (premium/theme-dependent):\n";
$manual_plugins = [
    'advanced-custom-fields-pro/acf.php' => 'Premium',
    'fusion-builder/fusion-builder.php' => 'Avada dependency',
    'fusion-core/fusion-core.php' => 'Avada dependency',
    'fusion-white-label-branding/fusion-white-label-branding.php' => 'Avada dependency',
    'LayerSlider/layerslider.php' => 'Premium',
    'snapshot/snapshot.php' => 'WPMU DEV',
    'wp-smush-pro/wp-smush-pro.php' => 'WPMU DEV',
    'wpmu-dev-seo/wpmu-dev-seo.php' => 'WPMU DEV',
    'wpmudev-updates/update-notifications.php' => 'WPMU DEV',
];

foreach ( $manual_plugins as $plugin => $reason ) {
    echo "  - " . $plugin . " (" . $reason . ")\n";
}

echo "\nDone!\n";
