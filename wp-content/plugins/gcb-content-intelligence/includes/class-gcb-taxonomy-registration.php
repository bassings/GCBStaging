<?php
/**
 * GCB Taxonomy Registration
 *
 * Registers the content_format taxonomy with terms:
 * - video-quick: Video posts with < 300 words
 * - video-feature: Video posts with > 300 words (hybrid content)
 * - standard: Text-only posts
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_Taxonomy_Registration
 *
 * Handles registration of content_format taxonomy.
 */
class GCB_Taxonomy_Registration {

	/**
	 * Register content_format taxonomy
	 *
	 * @return void
	 */
	public static function register_content_format_taxonomy(): void {
		$labels = array(
			'name'              => _x( 'Content Formats', 'taxonomy general name', 'gcb-content-intelligence' ),
			'singular_name'     => _x( 'Content Format', 'taxonomy singular name', 'gcb-content-intelligence' ),
			'search_items'      => __( 'Search Content Formats', 'gcb-content-intelligence' ),
			'all_items'         => __( 'All Content Formats', 'gcb-content-intelligence' ),
			'edit_item'         => __( 'Edit Content Format', 'gcb-content-intelligence' ),
			'update_item'       => __( 'Update Content Format', 'gcb-content-intelligence' ),
			'add_new_item'      => __( 'Add New Content Format', 'gcb-content-intelligence' ),
			'new_item_name'     => __( 'New Content Format Name', 'gcb-content-intelligence' ),
			'menu_name'         => __( 'Content Format', 'gcb-content-intelligence' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'content-format' ),
			'public'            => true,
			'description'       => __( 'Automatically assigned based on video content and word count.', 'gcb-content-intelligence' ),
		);

		register_taxonomy( 'content_format', array( 'post' ), $args );

		// Create default terms on registration.
		self::create_default_terms();
	}

	/**
	 * Create default taxonomy terms
	 *
	 * @return void
	 */
	private static function create_default_terms(): void {
		$terms = array(
			'video-quick'   => array(
				'name'        => 'Video Quick',
				'slug'        => 'video-quick',
				'description' => 'Video posts with less than 300 words.',
			),
			'video-feature' => array(
				'name'        => 'Video Feature',
				'slug'        => 'video-feature',
				'description' => 'Video posts with more than 300 words (hybrid content).',
			),
			'standard'      => array(
				'name'        => 'Standard',
				'slug'        => 'standard',
				'description' => 'Text-only posts without video content.',
			),
		);

		foreach ( $terms as $term_data ) {
			if ( ! term_exists( $term_data['slug'], 'content_format' ) ) {
				wp_insert_term(
					$term_data['name'],
					'content_format',
					array(
						'slug'        => $term_data['slug'],
						'description' => $term_data['description'],
					)
				);
			}
		}
	}
}
