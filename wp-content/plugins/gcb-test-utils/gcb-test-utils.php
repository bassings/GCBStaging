<?php
/**
 * Plugin Name: GCB Test Utils
 * Plugin URI: https://gaycarboys.com
 * Description: Testing utilities for GCB WordPress development. Provides database reset endpoint for E2E tests. <strong>DO NOT ACTIVATE IN PRODUCTION.</strong>
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.3
 * Author: Gay Car Boys Dev Team
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gcb-test-utils
 *
 * @package GCB_Test_Utils
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin constants.
define( 'GCB_TEST_UTILS_VERSION', '1.0.0' );
define( 'GCB_TEST_UTILS_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCB_TEST_UTILS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Environment Check on Activation
 *
 * Prevents activation in production environments.
 */
function gcb_test_utils_environment_check(): void {
	if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'production' ) {
		wp_die(
			esc_html__( 'GCB Test Utils cannot be activated in production environments.', 'gcb-test-utils' ),
			esc_html__( 'Plugin Activation Error', 'gcb-test-utils' ),
			array( 'back_link' => true )
		);
	}

	if ( ! defined( 'GCB_TEST_KEY' ) ) {
		add_action( 'admin_notices', 'gcb_test_utils_missing_key_notice' );
	}
}
register_activation_hook( __FILE__, 'gcb_test_utils_environment_check' );

/**
 * Display Admin Notice if GCB_TEST_KEY is Missing
 */
function gcb_test_utils_missing_key_notice(): void {
	?>
	<div class="notice notice-warning">
		<p>
			<strong>GCB Test Utils:</strong>
			<code>GCB_TEST_KEY</code> is not defined in <code>wp-config.php</code>.
			Add: <code>define('GCB_TEST_KEY', 'test-secret-key-local');</code>
		</p>
	</div>
	<?php
}

/**
 * Initialize Plugin
 *
 * Load the database reset functionality and theme manager, then register REST API routes.
 */
function gcb_test_utils_init(): void {
	require_once GCB_TEST_UTILS_DIR . 'includes/class-gcb-database-reset.php';
	require_once GCB_TEST_UTILS_DIR . 'includes/class-gcb-theme-manager.php';
	require_once GCB_TEST_UTILS_DIR . 'includes/class-gcb-post-creator.php';

	add_action( 'rest_api_init', array( 'GCB_Database_Reset', 'register_routes' ) );
	add_action( 'rest_api_init', array( 'GCB_Theme_Manager', 'register_routes' ) );
	add_action( 'rest_api_init', array( 'GCB_Post_Creator', 'register_routes' ) );
}
add_action( 'plugins_loaded', 'gcb_test_utils_init' );

/**
 * Display Warning Notice in Admin
 *
 * Shows a persistent warning that this plugin can delete all database content.
 */
function gcb_test_utils_admin_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && 'plugins' === $screen->id ) {
		?>
		<div class="notice notice-error">
			<p>
				<strong>⚠️ WARNING:</strong>
				GCB Test Utils is active. This plugin can <strong>DELETE ALL DATABASE CONTENT</strong>.
				DO NOT use in production.
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'gcb_test_utils_admin_notice' );
