<?php
/**
 * GCB Post Creator
 *
 * Provides REST API endpoint for creating posts in E2E tests without authentication.
 * FOR TESTING ONLY - DO NOT USE IN PRODUCTION.
 *
 * @package GCB_Test_Utils
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Post_Creator
 *
 * Handles test post creation via REST API.
 */
class GCB_Post_Creator {

    /**
     * Register REST API routes
     *
     * @return void
     */
    public static function register_routes(): void {
        register_rest_route(
            'gcb-testing/v1',
            '/create-post',
            array(
                'methods'             => 'POST',
                'callback'            => array( __CLASS__, 'create_post' ),
                'permission_callback' => array( __CLASS__, 'verify_test_key' ),
                'args'                => array(
                    'title'   => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'content' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'wp_kses_post',
                    ),
                    'status'  => array(
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => 'publish',
                        'sanitize_callback' => 'sanitize_key',
                    ),
                ),
            )
        );
    }

    /**
     * Verify test key from request headers
     *
     * @param WP_REST_Request $request Request object
     * @return bool True if key is valid
     */
    public static function verify_test_key( WP_REST_Request $request ): bool {
        $test_key = $request->get_header( 'GCB-Test-Key' );

        if ( ! defined( 'GCB_TEST_KEY' ) ) {
            return false;
        }

        return hash_equals( GCB_TEST_KEY, (string) $test_key );
    }

    /**
     * Create test post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public static function create_post( WP_REST_Request $request ) {
        $title   = $request->get_param( 'title' );
        $content = $request->get_param( 'content' );
        $status  = $request->get_param( 'status' );

        $post_id = wp_insert_post(
            array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => $status,
                'post_type'    => 'post',
                'post_author'  => 1, // Default to admin user
            )
        );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error(
                'post_creation_failed',
                $post_id->get_error_message(),
                array( 'status' => 500 )
            );
        }

        // Get the post object
        $post = get_post( $post_id );

        if ( ! $post ) {
            return new WP_Error(
                'post_not_found',
                'Post was created but could not be retrieved',
                array( 'status' => 500 )
            );
        }

        // Prepare response data (similar to WordPress REST API format)
        $response_data = array(
            'id'              => $post->ID,
            'title'           => array(
                'raw'      => $post->post_title,
                'rendered' => get_the_title( $post->ID ),
            ),
            'content'         => array(
                'raw'      => $post->post_content,
                'rendered' => apply_filters( 'the_content', $post->post_content ),
            ),
            'status'          => $post->post_status,
            'type'            => $post->post_type,
            'link'            => get_permalink( $post->ID ),
            'date'            => mysql2date( 'c', $post->post_date, false ),
            'date_gmt'        => mysql2date( 'c', $post->post_date_gmt, false ),
            'modified'        => mysql2date( 'c', $post->post_modified, false ),
            'modified_gmt'    => mysql2date( 'c', $post->post_modified_gmt, false ),
            // Include taxonomy terms
            'content_format'  => wp_get_object_terms( $post->ID, 'content_format', array( 'fields' => 'ids' ) ),
            // Include meta fields
            'meta'            => array(
                '_gcb_video_id'        => get_post_meta( $post->ID, '_gcb_video_id', true ),
                '_gcb_content_format'  => get_post_meta( $post->ID, '_gcb_content_format', true ),
            ),
        );

        return new WP_REST_Response( $response_data, 201 );
    }
}
