<?php
/**
 * GCB Taxonomy Manager
 *
 * Manages the content_format taxonomy for classifying posts (video/standard/gallery).
 * This is a hidden taxonomy - not shown in admin UI but available via REST API.
 *
 * @package GCB_Content_Intelligence
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Taxonomy_Manager
 *
 * Handles registration and management of the content_format taxonomy.
 */
class GCB_Taxonomy_Manager {

    /**
     * Taxonomy name
     *
     * @var string
     */
    private const TAXONOMY = 'content_format';

    /**
     * Default taxonomy terms
     *
     * @var array<string>
     */
    private const DEFAULT_TERMS = array(
        'video',
        'standard',
        'gallery',
    );

    /**
     * Register the content_format taxonomy
     *
     * This taxonomy is hidden from the public and admin UI but exposed via REST API.
     * It's used internally to classify post content types for automated routing.
     *
     * @return void
     */
    public static function register_taxonomy(): void {
        $args = array(
            'label'             => __( 'Content Format', 'gcb-content-intelligence' ),
            'labels'            => array(
                'name'          => __( 'Content Formats', 'gcb-content-intelligence' ),
                'singular_name' => __( 'Content Format', 'gcb-content-intelligence' ),
            ),
            'hierarchical'      => true,
            'public'            => false,       // Hidden from public
            'show_ui'           => false,       // Hidden from admin UI
            'show_in_rest'      => true,        // Available via REST API
            'show_admin_column' => true,        // Visible in posts list
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'rewrite'           => false,
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ),
        );

        register_taxonomy( self::TAXONOMY, 'post', $args );
    }

    /**
     * Create default taxonomy terms
     *
     * Ensures video, standard, and gallery terms exist.
     * Safe to run multiple times - only creates if terms don't exist.
     *
     * @return void
     */
    public static function create_default_terms(): void {
        foreach ( self::DEFAULT_TERMS as $term ) {
            // Check if term already exists
            if ( ! term_exists( $term, self::TAXONOMY ) ) {
                wp_insert_term(
                    $term,
                    self::TAXONOMY,
                    array(
                        'description' => self::get_term_description( $term ),
                        'slug'        => $term,
                    )
                );
            }
        }
    }

    /**
     * Get description for a term
     *
     * @param string $term Term slug
     * @return string Term description
     */
    private static function get_term_description( string $term ): string {
        $descriptions = array(
            'video'    => 'Posts containing video content (YouTube embeds)',
            'standard' => 'Standard text-based articles without video',
            'gallery'  => 'Posts with image galleries',
        );

        return $descriptions[ $term ] ?? '';
    }

    /**
     * Get the taxonomy name
     *
     * @return string Taxonomy name
     */
    public static function get_taxonomy(): string {
        return self::TAXONOMY;
    }

    /**
     * Get default terms
     *
     * @return array<string> Default term slugs
     */
    public static function get_default_terms(): array {
        return self::DEFAULT_TERMS;
    }
}
