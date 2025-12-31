<?php
/**
 * GCB Content Classifier
 *
 * Scans post content for YouTube videos and classifies based on word count:
 * - video-quick: Has video + < 300 words
 * - video-feature: Has video + >= 300 words
 * - standard: No video
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_Content_Classifier
 *
 * Handles automatic content classification.
 */
class GCB_Content_Classifier {

	/**
	 * Word count threshold for video-feature classification
	 */
	private const WORD_COUNT_THRESHOLD = 300;

	/**
	 * Hook: Classify post when saved
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an update.
	 * @return void
	 */
	public static function classify_post_on_save( int $post_id, WP_Post $post, bool $update ): void {
		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Only classify published posts.
		if ( 'post' !== $post->post_type ) {
			return;
		}

		self::classify_post( $post_id );
	}

	/**
	 * Classify a single post
	 *
	 * @param int $post_id Post ID.
	 * @return string The assigned term slug.
	 */
	public static function classify_post( int $post_id ): string {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$content       = $post->post_content;
		$has_video     = self::has_video_content( $content );
		$word_count    = self::get_word_count( $content );
		$assigned_term = '';

		if ( $has_video ) {
			if ( $word_count < self::WORD_COUNT_THRESHOLD ) {
				$assigned_term = 'video-quick';
			} else {
				$assigned_term = 'video-feature';
			}
		} else {
			$assigned_term = 'standard';
		}

		// Assign taxonomy term.
		$term = get_term_by( 'slug', $assigned_term, 'content_format' );
		if ( $term ) {
			wp_set_object_terms( $post_id, $term->term_id, 'content_format', false );
		}

		return $assigned_term;
	}

	/**
	 * Check if content contains video
	 *
	 * Detects:
	 * - YouTube URLs (youtube.com/watch, youtu.be)
	 * - WordPress embed blocks (wp:embed)
	 *
	 * @param string $content Post content.
	 * @return bool True if video found.
	 */
	private static function has_video_content( string $content ): bool {
		// Check for YouTube URLs.
		$youtube_patterns = array(
			'/youtube\.com\/watch\?v=/',
			'/youtu\.be\//',
			'/youtube\.com\/embed\//',
		);

		foreach ( $youtube_patterns as $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		// Check for WordPress embed blocks.
		if ( preg_match( '/<!-- wp:embed.*?"providerNameSlug":"youtube"/', $content ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get word count from content
	 *
	 * Strips HTML tags and counts words.
	 *
	 * @param string $content Post content.
	 * @return int Word count.
	 */
	private static function get_word_count( string $content ): int {
		// Remove HTML tags.
		$text = wp_strip_all_tags( $content );

		// Remove extra whitespace.
		$text = preg_replace( '/\s+/', ' ', $text );

		// Count words.
		$words = str_word_count( trim( $text ) );

		return $words;
	}

	/**
	 * Classify all posts
	 *
	 * Bulk classification for CLI command or backfill.
	 *
	 * @param array $args Query arguments.
	 * @return array Classification results.
	 */
	public static function classify_all_posts( array $args = array() ): array {
		$defaults = array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$query_args = wp_parse_args( $args, $defaults );
		$post_ids   = get_posts( $query_args );

		$results = array(
			'total'      => count( $post_ids ),
			'classified' => 0,
			'breakdown'  => array(
				'video-quick'   => 0,
				'video-feature' => 0,
				'standard'      => 0,
			),
		);

		foreach ( $post_ids as $post_id ) {
			$term = self::classify_post( $post_id );

			if ( $term ) {
				++$results['classified'];
				if ( isset( $results['breakdown'][ $term ] ) ) {
					++$results['breakdown'][ $term ];
				}
			}
		}

		return $results;
	}
}
