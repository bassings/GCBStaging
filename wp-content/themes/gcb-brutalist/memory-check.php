<?php
/**
 * WordPress Studio Memory Diagnostic
 *
 * Access via: http://localhost:8881/wp-content/themes/gcb-brutalist/memory-check.php
 *
 * Shows PHP memory limits, usage, and database size
 */

// Prevent direct access in production
if ( ! defined( 'ABSPATH' ) ) {
	// Load WordPress
	require_once '../../../wp-load.php';
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "=== WordPress Studio Memory Diagnostic ===\n\n";

// PHP Memory Settings
echo "PHP MEMORY SETTINGS:\n";
echo "-------------------\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Memory Limit: " . ini_get( 'memory_limit' ) . "\n";
echo "Max Execution Time: " . ini_get( 'max_execution_time' ) . "s\n";
echo "Post Max Size: " . ini_get( 'post_max_size' ) . "\n";
echo "Upload Max Filesize: " . ini_get( 'upload_max_filesize' ) . "\n";

if ( defined( 'WP_MEMORY_LIMIT' ) ) {
	echo "WP_MEMORY_LIMIT: " . WP_MEMORY_LIMIT . "\n";
} else {
	echo "WP_MEMORY_LIMIT: Not defined (using PHP default)\n";
}

if ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
	echo "WP_MAX_MEMORY_LIMIT: " . WP_MAX_MEMORY_LIMIT . "\n";
} else {
	echo "WP_MAX_MEMORY_LIMIT: Not defined\n";
}

echo "\n";

// Current Memory Usage
echo "CURRENT MEMORY USAGE:\n";
echo "--------------------\n";
$memory_usage = memory_get_usage();
$memory_usage_mb = round( $memory_usage / 1024 / 1024, 2 );
echo "Current Usage: {$memory_usage_mb} MB\n";

$memory_peak = memory_get_peak_usage();
$memory_peak_mb = round( $memory_peak / 1024 / 1024, 2 );
echo "Peak Usage: {$memory_peak_mb} MB\n";

$memory_limit_bytes = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
$memory_limit_mb = round( $memory_limit_bytes / 1024 / 1024, 2 );
$memory_available = $memory_limit_mb - $memory_usage_mb;
echo "Available: {$memory_available} MB\n";
echo "Utilization: " . round( ( $memory_usage_mb / $memory_limit_mb ) * 100, 1 ) . "%\n";

echo "\n";

// Database Information
echo "DATABASE INFORMATION:\n";
echo "--------------------\n";

global $wpdb;

// Database size (SQLite specific)
$db_path = WP_CONTENT_DIR . '/database/.ht.sqlite';
if ( file_exists( $db_path ) ) {
	$db_size_bytes = filesize( $db_path );
	$db_size_mb = round( $db_size_bytes / 1024 / 1024, 2 );
	echo "Database File: .ht.sqlite\n";
	echo "Database Size: {$db_size_mb} MB\n";

	// Warning thresholds for WordPress Studio
	if ( $db_size_mb > 300 ) {
		echo "⚠️  WARNING: Database is very large for WordPress Studio!\n";
		echo "   Recommended: < 150 MB for optimal performance\n";
	} elseif ( $db_size_mb > 150 ) {
		echo "⚠️  CAUTION: Database is getting large\n";
		echo "   Consider optimization if experiencing slowness\n";
	} else {
		echo "✅ Database size is acceptable\n";
	}
} else {
	echo "Database Type: Unknown (SQLite not detected)\n";
}

echo "\n";

// Table sizes
echo "LARGEST TABLES:\n";
echo "--------------\n";

// For SQLite, we can query the dbstat virtual table
$tables = $wpdb->get_results( "
	SELECT
		name as table_name,
		COUNT(*) as page_count
	FROM sqlite_master
	WHERE type = 'table'
	AND name LIKE 'wp_%'
	ORDER BY name
" );

if ( $tables ) {
	foreach ( $tables as $table ) {
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table->table_name}" );
		echo "- {$table->table_name}: " . number_format( $row_count ) . " rows\n";
	}
} else {
	echo "Unable to retrieve table information\n";
}

echo "\n";

// Optimization recommendations
echo "OPTIMIZATION OPPORTUNITIES:\n";
echo "--------------------------\n";

// Count revisions
$revisions = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
if ( $revisions > 100 ) {
	echo "⚠️  Post Revisions: " . number_format( $revisions ) . " (delete to save space)\n";
} else {
	echo "✅ Post Revisions: " . number_format( $revisions ) . " (acceptable)\n";
}

// Count auto-drafts
$autodrafts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );
if ( $autodrafts > 50 ) {
	echo "⚠️  Auto-Drafts: " . number_format( $autodrafts ) . " (delete to save space)\n";
} else {
	echo "✅ Auto-Drafts: " . number_format( $autodrafts ) . " (acceptable)\n";
}

// Count trash
$trash = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
if ( $trash > 0 ) {
	echo "⚠️  Trashed Posts: " . number_format( $trash ) . " (empty trash to save space)\n";
} else {
	echo "✅ Trashed Posts: 0 (clean)\n";
}

// Count transients
$transients = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );
if ( $transients > 100 ) {
	echo "⚠️  Transients: " . number_format( $transients ) . " (delete expired to save space)\n";
} else {
	echo "✅ Transients: " . number_format( $transients ) . " (acceptable)\n";
}

// Count spam comments
$spam = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
if ( $spam > 0 ) {
	echo "⚠️  Spam Comments: " . number_format( $spam ) . " (delete to save space)\n";
} else {
	echo "✅ Spam Comments: 0 (clean)\n";
}

echo "\n";

// WordPress Studio Specific Recommendations
echo "WORDPRESS STUDIO RECOMMENDATIONS:\n";
echo "--------------------------------\n";

if ( $db_size_mb > 200 ) {
	echo "1. ⚠️  URGENT: Reduce database size\n";
	echo "   - Install WP-Optimize plugin\n";
	echo "   - Delete post revisions\n";
	echo "   - Empty trash\n";
	echo "   - Clean transients\n\n";
}

if ( $memory_usage_mb > 100 ) {
	echo "2. ⚠️  High memory usage detected\n";
	echo "   - Deactivate unused plugins\n";
	echo "   - Disable debug mode\n";
	echo "   - Clear object cache\n\n";
}

echo "3. Browser Optimization:\n";
echo "   - Use Chrome/Edge (best WASM performance)\n";
echo "   - Close other tabs to free memory\n";
echo "   - Disable browser extensions in incognito mode\n";
echo "   - Clear browser cache regularly\n\n";

echo "4. Performance Tips:\n";
echo "   - Limit post revisions to 3-5\n";
echo "   - Keep database under 150MB\n";
echo "   - Avoid large image uploads\n";
echo "   - Use external CDN for media\n";

echo "\n=== End Diagnostic ===\n";
