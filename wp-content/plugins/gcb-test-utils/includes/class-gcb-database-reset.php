<?php
/**
 * Database Reset Functionality
 *
 * Provides a REST API endpoint to reset the WordPress database
 * for E2E testing purposes. This class handles:
 * - Deleting all posts, pages, and custom post types
 * - Deleting all terms and taxonomies
 * - Deleting all comments
 * - Cleaning up orphaned metadata
 *
 * @package GCB_Test_Utils
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Database Reset Handler
 */
class GCB_Database_Reset {

	/**
	 * API namespace for the reset endpoint.
	 */
	private const API_NAMESPACE = 'gcb-testing/v1';

	/**
	 * API route for the reset endpoint.
	 */
	private const API_ROUTE = '/reset';

	/**
	 * Register REST API Routes
	 *
	 * Registers the DELETE /gcb-testing/v1/reset endpoint.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::API_NAMESPACE,
			self::API_ROUTE,
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'handle_reset_request' ),
				'permission_callback' => array( __CLASS__, 'verify_test_key' ),
			)
		);
	}

	/**
	 * Verify Test Key
	 *
	 * Permission callback that validates the GCB-Test-Key header
	 * matches the GCB_TEST_KEY constant.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return bool True if test key is valid, false otherwise.
	 */
	public static function verify_test_key( WP_REST_Request $request ): bool {
		if ( ! defined( 'GCB_TEST_KEY' ) ) {
			return false;
		}

		$provided_key = $request->get_header( 'GCB-Test-Key' );

		if ( empty( $provided_key ) ) {
			return false;
		}

		// Use hash_equals to prevent timing attacks.
		return hash_equals( GCB_TEST_KEY, $provided_key );
	}

	/**
	 * Handle Reset Request
	 *
	 * Main handler for the database reset endpoint.
	 * Deletes all content and returns statistics.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return WP_REST_Response The response object with reset statistics.
	 */
	public static function handle_reset_request( WP_REST_Request $request ): WP_REST_Response {
		try {
			$deleted_counts = self::reset_database();

			return new WP_REST_Response(
				array(
					'success'        => true,
					'message'        => 'Database reset successfully',
					'deleted_posts'  => $deleted_counts['posts'],
					'deleted_pages'  => $deleted_counts['pages'],
					'deleted_media'  => $deleted_counts['media'],
					'deleted_terms'  => $deleted_counts['terms'],
					'timestamp'      => current_time( 'mysql' ),
				),
				200
			);

		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Database reset failed: ' . $e->getMessage(),
				),
				500
			);
		}
	}

	/**
	 * Reset Database
	 *
	 * Performs the actual database reset by deleting all posts,
	 * pages, media, terms, and comments using direct SQL queries for speed.
	 * This is optimized for testing environments.
	 *
	 * @return array<string, int> Counts of deleted items by type.
	 * @throws Exception If database operations fail.
	 */
	private static function reset_database(): array {
		global $wpdb;

		// Get counts before deletion for reporting.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post'" );
		$page_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'page'" );
		$media_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'" );
		$term_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms} WHERE term_id != 1" );

		$counts = array(
			'posts' => (int) $post_count,
			'pages' => (int) $page_count,
			'media' => (int) $media_count,
			'terms' => (int) $term_count,
		);

		// Delete all posts, pages, and custom post types using SQL.
		$wpdb->query( "DELETE FROM {$wpdb->posts}" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta}" );

		// Delete all terms except the default "Uncategorized" category (ID 1).
		$wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE term_id != 1" );
		$wpdb->query( "DELETE FROM {$wpdb->term_relationships}" );
		$wpdb->query( "DELETE FROM {$wpdb->terms} WHERE term_id != 1" );
		$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE term_id != 1" );

		// Delete all comments and comment meta.
		$wpdb->query( "DELETE FROM {$wpdb->comments}" );
		$wpdb->query( "DELETE FROM {$wpdb->commentmeta}" );

		// Clear WordPress object cache.
		wp_cache_flush();
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		return $counts;
	}
}
