<?php
/**
 * Check if header-footer plugin has any active code
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "Checking header-footer plugin settings...\n\n";

// Get the plugin settings
$hefo_settings = get_option('hefo', []);

if (empty($hefo_settings)) {
    echo "✓ No settings found in header-footer plugin\n";
    echo "  Safe to remove without migration\n";
} else {
    echo "⚠ Found settings in header-footer plugin:\n";
    print_r($hefo_settings);
    echo "\nNeed to migrate this code before removing plugin!\n";
}

// Also check insert-headers-and-footers (WPCode) settings
echo "\n" . str_repeat("=", 50) . "\n";
echo "Checking insert-headers-and-footers (WPCode) settings...\n\n";

$ihaf_header = get_option('ihaf_insert_header', '');
$ihaf_body = get_option('ihaf_insert_body', '');
$ihaf_footer = get_option('ihaf_insert_footer', '');

echo "Header code: " . (empty($ihaf_header) ? "Empty" : "Has code") . "\n";
echo "Body code: " . (empty($ihaf_body) ? "Empty" : "Has code") . "\n";
echo "Footer code: " . (empty($ihaf_footer) ? "Empty" : "Has code") . "\n";
