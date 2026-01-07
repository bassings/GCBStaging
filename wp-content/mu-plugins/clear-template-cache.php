<?php
/**
 * Clear WordPress template cache
 *
 * This script forces WordPress to clear all template-related caches
 * and reload templates from theme files.
 */

// Clear all WordPress caches
wp_cache_flush();

// Delete all transients
delete_transient_cache();

// Force template reload by deleting relevant options
delete_option('_transient_dirsize_cache');
delete_option('_site_transient_update_themes');

// Clear Jetpack cache if active
if (class_exists('Jetpack')) {
    Jetpack::invalidate_module_cache();
}

// Output success message
if (isset($_GET['clear_cache']) && $_GET['clear_cache'] === 'true') {
    echo '<h1>✅ WordPress Caches Cleared</h1>';
    echo '<p>All template caches have been flushed. Please refresh your homepage.</p>';
    echo '<p><a href="/">Go to Homepage</a></p>';
    exit;
}

function delete_transient_cache() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
}
