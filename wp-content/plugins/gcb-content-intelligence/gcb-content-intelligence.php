<?php
/**
 * Plugin Name: GCB Content Intelligence
 * Description: Automated video detection and schema.org generation
 * Version: 1.0.0
 * Requires PHP: 8.3
 * Author: Gay Car Boys
 * Text Domain: gcb-content-intelligence
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GCB_CI_VERSION', '1.0.0' );
define( 'GCB_CI_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCB_CI_URL', plugin_dir_url( __FILE__ ) );

// Load classes
require_once GCB_CI_DIR . 'includes/class-gcb-taxonomy-manager.php';
require_once GCB_CI_DIR . 'includes/class-gcb-content-detector.php';

// Initialize hooks
add_action( 'init', 'gcb_ci_init' );
add_action( 'save_post', 'gcb_ci_process_post', 20, 2 );
add_action( 'rest_after_insert_post', 'gcb_ci_process_post_rest', 10, 2 );

/**
 * Initialize plugin on WordPress init hook
 *
 * Registers taxonomy, creates default terms, and registers post meta.
 *
 * @return void
 */
function gcb_ci_init(): void {
    GCB_Taxonomy_Manager::register_taxonomy();
    GCB_Taxonomy_Manager::create_default_terms();
    GCB_Content_Detector::register_post_meta();
}

/**
 * Process post on save to detect content format
 *
 * Automatically detects content format when posts are saved and assigns
 * the appropriate taxonomy term (video/standard/gallery).
 *
 * @param int     $post_id Post ID
 * @param WP_Post $post    Post object
 * @return void
 */
function gcb_ci_process_post( int $post_id, WP_Post $post ): void {
    // Skip autosaves and non-post types
    if ( wp_is_post_autosave( $post_id ) || 'post' !== $post->post_type ) {
        return;
    }

    // Detect content format
    $detector = new GCB_Content_Detector();
    $format   = $detector->detect_content_format( $post_id );

    // Assign taxonomy term
    wp_set_object_terms( $post_id, $format, 'content_format' );

    // Cache format in post meta for quick lookups
    update_post_meta( $post_id, '_gcb_content_format', $format );
}

/**
 * Process post created/updated via REST API
 *
 * Handles content detection for posts created through the WordPress REST API.
 * This runs after the post is fully inserted, ensuring all content is available.
 *
 * @param WP_Post         $post     Inserted or updated post object
 * @param WP_REST_Request $request  Request object
 * @return void
 */
function gcb_ci_process_post_rest( WP_Post $post, WP_REST_Request $request ): void {
    // Only process standard posts
    if ( 'post' !== $post->post_type ) {
        return;
    }

    // Process the post
    gcb_ci_process_post( $post->ID, $post );
}
