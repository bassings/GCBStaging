<?php
/**
 * GCB Schema Generator
 *
 * Generates schema.org JSON-LD markup for posts.
 * Outputs VideoObject for video posts, NewsArticle for standard posts.
 *
 * @package GCB_Content_Intelligence
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Schema_Generator
 *
 * Handles schema.org JSON-LD generation and output.
 */
class GCB_Schema_Generator {

	/**
	 * Output schema.org JSON-LD for current post
	 *
	 * Hooked to wp_footer to inject schema markup in page footer.
	 *
	 * @return void
	 */
	public static function output_schema(): void {
		if ( ! is_single() ) {
			return;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// Determine content format
		$format = get_post_meta( $post->ID, '_gcb_content_format', true );

		// Generate appropriate schema
		$schema = 'video' === $format
			? self::generate_video_schema( $post )
			: self::generate_article_schema( $post );

		if ( empty( $schema ) ) {
			return;
		}

		// Output JSON-LD
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		echo '</script>' . "\n";
	}

	/**
	 * Generate VideoObject schema
	 *
	 * @param WP_Post $post Post object
	 * @return array Schema data
	 */
	private static function generate_video_schema( WP_Post $post ): array {
		// Get video metadata
		$video_id       = get_post_meta( $post->ID, '_gcb_video_id', true );
		$metadata_json  = get_post_meta( $post->ID, '_gcb_video_metadata', true );
		$metadata       = json_decode( $metadata_json, true );

		if ( empty( $video_id ) ) {
			return array();
		}

		// Base schema structure
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'VideoObject',
			'name'        => $metadata['title'] ?? get_the_title( $post ),
			'description' => $metadata['description'] ?? wp_strip_all_tags( get_the_excerpt( $post ) ),
			'contentUrl'  => "https://www.youtube.com/watch?v={$video_id}",
			'embedUrl'    => "https://www.youtube.com/embed/{$video_id}",
			'uploadDate'  => $metadata['uploadDate'] ?? mysql2date( 'c', $post->post_date, false ),
			'publisher'   => self::get_publisher_schema(),
		);

		// Add optional properties if available
		if ( ! empty( $metadata['thumbnailUrl'] ) ) {
			$schema['thumbnailUrl'] = $metadata['thumbnailUrl'];
		}

		if ( ! empty( $metadata['duration'] ) ) {
			$schema['duration'] = $metadata['duration'];
		}

		return $schema;
	}

	/**
	 * Generate NewsArticle schema
	 *
	 * @param WP_Post $post Post object
	 * @return array Schema data
	 */
	private static function generate_article_schema( WP_Post $post ): array {
		return array(
			'@context'      => 'https://schema.org',
			'@type'         => 'NewsArticle',
			'headline'      => get_the_title( $post ),
			'articleBody'   => wp_strip_all_tags( $post->post_content ),
			'datePublished' => mysql2date( 'c', $post->post_date, false ),
			'dateModified'  => mysql2date( 'c', $post->post_modified, false ),
			'author'        => self::get_author_schema( $post ),
			'publisher'     => self::get_publisher_schema(),
		);
	}

	/**
	 * Get author schema
	 *
	 * @param WP_Post $post Post object
	 * @return array Author schema
	 */
	private static function get_author_schema( WP_Post $post ): array {
		$author = get_userdata( $post->post_author );

		return array(
			'@type' => 'Person',
			'name'  => $author ? $author->display_name : 'Unknown',
		);
	}

	/**
	 * Get publisher schema
	 *
	 * @return array Publisher schema
	 */
	private static function get_publisher_schema(): array {
		return array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url(),
		);
	}
}
