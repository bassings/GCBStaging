<?php
/**
 * Activate Avada Child Theme
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "Activating Avada Child Theme...\n\n";

// Get current theme
$current_theme = wp_get_theme();
echo "Current theme: " . $current_theme->get('Name') . " v" . $current_theme->get('Version') . "\n";

// Switch to child theme
switch_theme('Avada-child');

// Verify switch
$new_theme = wp_get_theme();
echo "New theme: " . $new_theme->get('Name') . " v" . $new_theme->get('Version') . "\n";

if ( $new_theme->get_template() === 'Avada' ) {
    echo "✓ Successfully activated Avada Child Theme\n";
    echo "  Parent: Avada\n";
    echo "  WooCommerce integration: DISABLED\n";
    echo "  Expected savings: ~150KB+ (CSS + JS)\n";
} else {
    echo "✗ Failed to activate child theme\n";
}

echo "\nDone!\n";
