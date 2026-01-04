<?php
/**
 * SQLite Performance Optimization for WordPress Studio
 * 
 * Reduces database locking and improves WASM performance
 */

// Increase SQLite timeout to reduce "database locked" errors
if ( defined( 'DB_TIMEOUT' ) ) {
	// Already defined
} else {
	define( 'DB_TIMEOUT', 10000 ); // 10 seconds
}

// Enable SQLite Write-Ahead Logging (WAL) mode for better concurrency
// Note: WordPress Studio may override this, but it's worth trying
add_action( 'plugins_loaded', function() {
	global $wpdb;
	if ( method_exists( $wpdb, 'query' ) ) {
		$wpdb->query( 'PRAGMA journal_mode=WAL;' );
		$wpdb->query( 'PRAGMA synchronous=NORMAL;' );
	}
}, 1 );
