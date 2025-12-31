<?php
/**
 * GCB Theme Manager
 *
 * Provides REST API endpoints for theme management in E2E tests.
 *
 * @package GCB_Test_Utils
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class GCB_Theme_Manager
 *
 * Handles theme activation and retrieval via REST API for testing.
 */
class GCB_Theme_Manager {

	/**
	 * REST API Namespace
	 */
	private const NAMESPACE = 'gcb-testing/v1';

	/**
	 * Register REST API Routes
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		// Activate theme endpoint
		register_rest_route(
			self::NAMESPACE,
			'/activate-theme',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'activate_theme' ),
				'permission_callback' => array( __CLASS__, 'verify_test_key' ),
			)
		);

		// Get active theme endpoint
		register_rest_route(
			self::NAMESPACE,
			'/active-theme',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_active_theme' ),
				'permission_callback' => array( __CLASS__, 'verify_test_key' ),
			)
		);
	}

	/**
	 * Verify Test Key
	 *
	 * Checks X-Test-Key header matches GCB_TEST_KEY constant.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if authorized, false otherwise.
	 */
	public static function verify_test_key( WP_REST_Request $request ): bool {
		if ( ! defined( 'GCB_TEST_KEY' ) ) {
			return false;
		}

		$test_key = $request->get_header( 'X-Test-Key' );

		return $test_key === GCB_TEST_KEY;
	}

	/**
	 * Activate Theme
	 *
	 * Switches the active WordPress theme.
	 *
	 * @param WP_REST_Request $request Request object containing theme slug.
	 * @return WP_REST_Response Response with activation status.
	 */
	public static function activate_theme( WP_REST_Request $request ): WP_REST_Response {
		$theme_slug = $request->get_param( 'theme' );

		if ( empty( $theme_slug ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Theme slug is required',
				),
				400
			);
		}

		// Check if theme exists
		$theme = wp_get_theme( $theme_slug );
		if ( ! $theme->exists() ) {
			return new WP_REST_Response(
				array(
					'success'    => false,
					'message'    => sprintf( 'Theme "%s" does not exist', $theme_slug ),
					'theme_slug' => $theme_slug,
				),
				404
			);
		}

		// Switch theme
		switch_theme( $theme_slug );

		// Verify theme was activated
		$active_theme = get_option( 'stylesheet' );
		if ( $active_theme !== $theme_slug ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Theme activation failed',
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success'       => true,
				'message'       => sprintf( 'Theme "%s" activated successfully', $theme->get( 'Name' ) ),
				'theme'         => $theme_slug,
				'theme_name'    => $theme->get( 'Name' ),
				'theme_version' => $theme->get( 'Version' ),
			),
			200
		);
	}

	/**
	 * Get Active Theme
	 *
	 * Retrieves the currently active theme information.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response with active theme details.
	 */
	public static function get_active_theme( WP_REST_Request $request ): WP_REST_Response {
		$active_theme_slug = get_option( 'stylesheet' );
		$theme             = wp_get_theme( $active_theme_slug );

		return new WP_REST_Response(
			array(
				'theme'         => $active_theme_slug,
				'theme_name'    => $theme->get( 'Name' ),
				'theme_version' => $theme->get( 'Version' ),
			),
			200
		);
	}
}
