<?php
/**
 * Plugin Name: GCB Content Intelligence
 * Plugin URI: https://gaycarboys.com
 * Description: Automatically classifies posts by content type (video-quick, video-feature, standard) based on video presence and word count. Integrates with Editorial Brutalism design system.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.3
 * Author: Gay Car Boys Dev Team
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gcb-content-intelligence
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin constants.
define( 'GCB_CI_VERSION', '1.0.0' );
define( 'GCB_CI_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCB_CI_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize Plugin
 *
 * Load taxonomy registration, content classifier, and CLI commands.
 */
function gcb_ci_init(): void {
	require_once GCB_CI_DIR . 'includes/class-gcb-taxonomy-registration.php';
	require_once GCB_CI_DIR . 'includes/class-gcb-content-detector.php';
	require_once GCB_CI_DIR . 'includes/class-gcb-content-classifier.php';
	require_once GCB_CI_DIR . 'includes/class-gcb-classification-rest-api.php';

	// Register taxonomy.
	add_action( 'init', array( 'GCB_Taxonomy_Registration', 'register_content_format_taxonomy' ) );

	// Register post meta fields for REST API access.
	add_action( 'init', array( 'GCB_Content_Detector', 'register_post_meta' ) );

	// Hook video metadata extraction (priority 5 - runs before classifier).
	add_action( 'save_post', array( 'GCB_Content_Detector', 'extract_video_metadata' ), 5, 3 );

	// Hook classifier into save_post (priority 10 - runs after video detection).
	add_action( 'save_post', array( 'GCB_Content_Classifier', 'classify_post_on_save' ), 10, 3 );

	// Register REST API endpoint for bulk classification.
	add_action( 'rest_api_init', array( 'GCB_Classification_REST_API', 'register_routes' ) );

	// Load WP-CLI commands if available.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once GCB_CI_DIR . 'includes/class-gcb-cli-commands.php';
		WP_CLI::add_command( 'gcb', 'GCB_CLI_Commands' );
	}
}
add_action( 'plugins_loaded', 'gcb_ci_init' );

/**
 * Activation Hook
 *
 * Register taxonomy and flush rewrite rules.
 */
function gcb_ci_activate(): void {
	require_once GCB_CI_DIR . 'includes/class-gcb-taxonomy-registration.php';
	GCB_Taxonomy_Registration::register_content_format_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'gcb_ci_activate' );

/**
 * Deactivation Hook
 *
 * Flush rewrite rules.
 */
function gcb_ci_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'gcb_ci_deactivate' );
