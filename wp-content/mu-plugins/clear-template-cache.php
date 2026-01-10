<?php
/**
 * Clear WordPress template cache
 *
 * This script forces WordPress to clear all template-related caches
 * and reload templates from theme files.
 *
 * Usage: Add ?clear_cache=true&_wpnonce=[nonce] to any admin URL
 * Nonce can be generated with: wp_create_nonce('gcb_clear_cache')
 */

/**
 * Handle cache clearing request with security checks.
 */
function gcb_handle_cache_clear_request() {
	// Only proceed if clear_cache parameter is present.
	if ( ! isset( $_GET['clear_cache'] ) || 'true' !== $_GET['clear_cache'] ) {
		return;
	}

	// Verify user has admin capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to clear caches.', 'gcb' ), 403 );
	}

	// Verify nonce for CSRF protection.
	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'gcb_clear_cache' ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'gcb' ), 403 );
	}

	// Clear all WordPress caches.
	wp_cache_flush();

	// Delete all transients using prepared statements.
	gcb_delete_transient_cache();

	// Force template reload by deleting relevant options.
	delete_option( '_transient_dirsize_cache' );
	delete_option( '_site_transient_update_themes' );

	// Clear Jetpack cache if active.
	if ( class_exists( 'Jetpack' ) ) {
		Jetpack::invalidate_module_cache();
	}

	// Output success message.
	echo '<h1>' . esc_html__( 'WordPress Caches Cleared', 'gcb' ) . '</h1>';
	echo '<p>' . esc_html__( 'All template caches have been flushed. Please refresh your homepage.', 'gcb' ) . '</p>';
	echo '<p><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Go to Homepage', 'gcb' ) . '</a></p>';
	exit;
}
add_action( 'admin_init', 'gcb_handle_cache_clear_request' );

/**
 * Delete all transients from the database using prepared statements.
 */
function gcb_delete_transient_cache() {
	global $wpdb;

	// Use prepare() with LIKE placeholders for security.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_' ) . '%'
		)
	);

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_site_transient_' ) . '%'
		)
	);
}
