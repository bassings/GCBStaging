<?php
/**
 * GCB Classification REST API
 *
 * Provides REST endpoint for triggering bulk classification.
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_Classification_REST_API
 *
 * Handles REST API routes for content classification.
 */
class GCB_Classification_REST_API {

	/**
	 * API namespace
	 */
	private const API_NAMESPACE = 'gcb-content-intelligence/v1';

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::API_NAMESPACE,
			'/classify-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_classify_all' ),
				'permission_callback' => array( __CLASS__, 'verify_permission' ),
			)
		);
	}

	/**
	 * Verify permission for classification endpoint
	 *
	 * Allows test key OR admin capability.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if authorized.
	 */
	public static function verify_permission( WP_REST_Request $request ): bool {
		// Check for test key (testing environment).
		if ( defined( 'GCB_TEST_KEY' ) ) {
			$test_key = $request->get_header( 'GCB-Test-Key' );
			if ( ! empty( $test_key ) && hash_equals( GCB_TEST_KEY, $test_key ) ) {
				return true;
			}
		}

		// Check admin capability.
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle classify-all endpoint
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function handle_classify_all( WP_REST_Request $request ): WP_REST_Response {
		$results = GCB_Content_Classifier::classify_all_posts();

		return new WP_REST_Response(
			array(
				'success'    => true,
				'classified' => $results['classified'],
				'total'      => $results['total'],
				'breakdown'  => $results['breakdown'],
				'message'    => sprintf(
					'Classified %d of %d posts',
					$results['classified'],
					$results['total']
				),
			),
			200
		);
	}
}
