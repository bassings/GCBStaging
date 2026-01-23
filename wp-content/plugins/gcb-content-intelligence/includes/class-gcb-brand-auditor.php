<?php
/**
 * GCB Brand Auditor
 *
 * Audits posts for brand categorization accuracy by comparing detected brands
 * in content against assigned brand categories.
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_Brand_Auditor
 *
 * Core audit logic for brand categorization accuracy.
 */
class GCB_Brand_Auditor {

	/**
	 * Audit a single post for brand categorization accuracy.
	 *
	 * @param WP_Post $post Post object to audit.
	 * @return array Audit result with detected brands, assigned categories, and mismatches.
	 */
	public static function audit_post( WP_Post $post ): array {
		// Extract brands from content.
		$detected_brands = self::extract_brands_from_post( $post );

		// Get assigned brand categories.
		$assigned_brands = self::get_assigned_brand_categories( $post->ID );

		// Determine if this is a comparison post.
		$is_comparison = self::is_comparison_post( $post );

		// Analyze mismatches.
		$mismatches = self::analyze_mismatches( $detected_brands, $assigned_brands, $is_comparison );

		return array(
			'post_id'         => $post->ID,
			'post_title'      => $post->post_title,
			'detected_brands' => $detected_brands,
			'assigned_brands' => $assigned_brands,
			'is_comparison'   => $is_comparison,
			'mismatches'      => $mismatches,
		);
	}

	/**
	 * Extract brands from post title and content.
	 *
	 * Returns array of brands with confidence scores:
	 * - 0.9: Brand in title
	 * - 0.8: Model name detected
	 * - 0.7: Brand in first paragraph
	 * - 0.6: Brand mentioned 3+ times
	 *
	 * @param WP_Post $post Post object.
	 * @return array Associative array of brand => confidence score.
	 */
	private static function extract_brands_from_post( WP_Post $post ): array {
		$brands = array();

		// Get all known brands and their variations.
		$all_brands = GCB_Brand_Dictionary::get_all_brands();

		// Extract text from title and content.
		$title_text     = strtolower( $post->post_title );
		$content_text   = strtolower( wp_strip_all_tags( $post->post_content ) );
		$first_para     = self::get_first_paragraph( $content_text );

		// Remove common phrases that contain brand-like words.
		$exclude_phrases = array(
			'mini review',
			'mini-review',
			'quick review',
			'short review',
		);
		foreach ( $exclude_phrases as $phrase ) {
			$title_text   = str_replace( $phrase, '', $title_text );
			$content_text = str_replace( $phrase, '', $content_text );
			$first_para   = str_replace( $phrase, '', $first_para );
		}

		// Check each brand.
		foreach ( $all_brands as $brand ) {
			$confidence = 0.0;
			$variations = GCB_Brand_Dictionary::get_brand_variations( $brand );
			$models     = GCB_Brand_Dictionary::get_brand_models( $brand );

			// Build regex patterns for brand variations.
			$brand_patterns = array_map(
				function( $var ) {
					return preg_quote( strtolower( $var ), '/' );
				},
				$variations
			);
			$brand_regex = '/\b(' . implode( '|', $brand_patterns ) . ')\b/i';

			// Check title (highest confidence).
			if ( preg_match( $brand_regex, $title_text ) ) {
				$confidence = max( $confidence, 0.9 );
			}

			// Check for model names in title or content.
			// Filter out very short model names to reduce false positives.
			$brand_mentioned = $confidence > 0; // Track if brand name was already detected.

			foreach ( $models as $model ) {
				// Skip single-letter models (too generic: S, X, E, C, etc.).
				if ( strlen( $model ) <= 1 ) {
					continue;
				}

				// Skip very short models (<=3 chars) unless brand is already mentioned.
				// This prevents "GT" from matching every post, but allows "911 GT3" when Porsche is mentioned.
				if ( strlen( $model ) <= 3 && ! $brand_mentioned ) {
					continue;
				}

				$model_pattern = '/\b' . preg_quote( strtolower( $model ), '/' ) . '\b/i';

				// Model in title gives higher confidence if brand is also mentioned.
				if ( preg_match( $model_pattern, $title_text ) ) {
					if ( $brand_mentioned ) {
						$confidence = max( $confidence, 0.85 );
					} else {
						$confidence = max( $confidence, 0.70 );
					}
					break;
				}

				// Model in content only counts if brand is also mentioned.
				if ( $brand_mentioned && preg_match( $model_pattern, $content_text ) ) {
					$confidence = max( $confidence, 0.75 );
					break;
				}
			}

			// Check first paragraph.
			if ( preg_match( $brand_regex, $first_para ) ) {
				$confidence = max( $confidence, 0.7 );
			}

			// Check mention frequency in content.
			$mention_count = preg_match_all( $brand_regex, $content_text );
			if ( $mention_count >= 3 ) {
				$confidence = max( $confidence, 0.6 );
			}

			// Only include brands with some confidence.
			if ( $confidence > 0 ) {
				$brands[ $brand ] = $confidence;
			}
		}

		// Sort by confidence (highest first).
		arsort( $brands );

		return $brands;
	}

	/**
	 * Get first paragraph from content (first 500 characters).
	 *
	 * @param string $content Content text.
	 * @return string First paragraph.
	 */
	private static function get_first_paragraph( string $content ): string {
		$paragraphs = explode( "\n", $content );
		$first_para = $paragraphs[0] ?? '';
		return substr( $first_para, 0, 500 );
	}

	/**
	 * Get assigned brand categories for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Associative array of category_slug => brand_name.
	 */
	private static function get_assigned_brand_categories( int $post_id ): array {
		$categories      = get_the_category( $post_id );
		$brand_categories = array();

		foreach ( $categories as $category ) {
			$brand = GCB_Brand_Dictionary::get_brand_from_category( $category->slug );
			if ( $brand ) {
				$brand_categories[ $category->slug ] = $brand;
			}
		}

		return $brand_categories;
	}

	/**
	 * Determine if post is a comparison post (mentions multiple brands).
	 *
	 * Checks for keywords like "vs", "versus", "compared to" in title/content.
	 *
	 * @param WP_Post $post Post object.
	 * @return bool True if post appears to be a comparison.
	 */
	private static function is_comparison_post( WP_Post $post ): bool {
		$title_text   = strtolower( $post->post_title );
		$content_text = strtolower( wp_strip_all_tags( $post->post_content ) );
		$keywords     = GCB_Brand_Dictionary::get_comparison_keywords();

		foreach ( $keywords as $keyword ) {
			$pattern = '/\b' . preg_quote( strtolower( $keyword ), '/' ) . '\b/i';
			if ( preg_match( $pattern, $title_text ) || preg_match( $pattern, $content_text ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Analyze mismatches between detected brands and assigned categories.
	 *
	 * Returns array with three types of issues:
	 * - wrong_brand: Post has Brand A category but content is about Brand B (critical)
	 * - missing_brand: Content mentions Brand A but no Brand A category (medium)
	 * - extra_brand: Has Brand A category but Brand A not mentioned (low)
	 *
	 * @param array $detected_brands Detected brands with confidence scores.
	 * @param array $assigned_brands Assigned brand categories (slug => brand).
	 * @param bool  $is_comparison   Whether this is a comparison post.
	 * @return array Mismatch analysis.
	 */
	private static function analyze_mismatches( array $detected_brands, array $assigned_brands, bool $is_comparison ): array {
		$mismatches = array(
			'wrong_brand'   => array(),
			'missing_brand' => array(),
			'extra_brand'   => array(),
		);

		$assigned_brand_names = array_values( $assigned_brands );
		$detected_brand_names = array_keys( $detected_brands );

		// Check for WRONG BRAND (critical): assigned brand doesn't match detected brands.
		foreach ( $assigned_brand_names as $assigned_brand ) {
			$has_match = false;

			// Check if assigned brand is detected.
			if ( in_array( $assigned_brand, $detected_brand_names, true ) ) {
				$has_match = true;
			}

			// Check if assigned brand is in same family as any detected brand.
			foreach ( $detected_brand_names as $detected_brand ) {
				if ( GCB_Brand_Dictionary::are_brands_in_same_family( $assigned_brand, $detected_brand ) ) {
					$has_match = true;
					break;
				}
			}

			// If no match and not a comparison post, flag as wrong brand.
			if ( ! $has_match && ! $is_comparison ) {
				$mismatches['wrong_brand'][] = array(
					'assigned' => $assigned_brand,
					'detected' => $detected_brand_names,
				);
			}
		}

		// Check for MISSING BRAND (medium): high-confidence brand detected but not assigned.
		foreach ( $detected_brands as $brand => $confidence ) {
			// Only flag if confidence is high (>= 0.7).
			if ( $confidence < 0.7 ) {
				continue;
			}

			$is_assigned = in_array( $brand, $assigned_brand_names, true );

			// Check if brand family is assigned instead.
			$family_assigned = false;
			foreach ( $assigned_brand_names as $assigned_brand ) {
				if ( GCB_Brand_Dictionary::are_brands_in_same_family( $brand, $assigned_brand ) ) {
					$family_assigned = true;
					break;
				}
			}

			if ( ! $is_assigned && ! $family_assigned ) {
				$mismatches['missing_brand'][] = array(
					'brand'      => $brand,
					'confidence' => $confidence,
				);
			}
		}

		// Check for EXTRA BRAND (low): assigned brand category but brand not detected.
		foreach ( $assigned_brand_names as $assigned_brand ) {
			$is_detected = in_array( $assigned_brand, $detected_brand_names, true );

			// Check if any detected brand is in same family.
			$family_detected = false;
			foreach ( $detected_brand_names as $detected_brand ) {
				if ( GCB_Brand_Dictionary::are_brands_in_same_family( $assigned_brand, $detected_brand ) ) {
					$family_detected = true;
					break;
				}
			}

			// Only flag if NO detected brands at all (empty post or generic content).
			if ( ! $is_detected && ! $family_detected && count( $detected_brand_names ) === 0 ) {
				$mismatches['extra_brand'][] = $assigned_brand;
			}
		}

		return $mismatches;
	}

	/**
	 * Check if post has any mismatches.
	 *
	 * @param array $audit_result Audit result from audit_post().
	 * @return bool True if post has any mismatches.
	 */
	public static function has_mismatches( array $audit_result ): bool {
		$mismatches = $audit_result['mismatches'];
		return ! empty( $mismatches['wrong_brand'] )
			|| ! empty( $mismatches['missing_brand'] )
			|| ! empty( $mismatches['extra_brand'] );
	}

	/**
	 * Generate fix recommendations for mismatches.
	 *
	 * @param array $audit_result Audit result from audit_post().
	 * @return array Array of recommended actions.
	 */
	public static function generate_recommendations( array $audit_result ): array {
		$recommendations = array();
		$mismatches      = $audit_result['mismatches'];

		// Wrong brand recommendations.
		foreach ( $mismatches['wrong_brand'] as $wrong ) {
			$assigned        = $wrong['assigned'];
			$category_slug   = GCB_Brand_Dictionary::get_category_from_brand( $assigned );
			$recommendations[] = "Remove category: {$category_slug}";
		}

		// Missing brand recommendations.
		foreach ( $mismatches['missing_brand'] as $missing ) {
			$brand           = $missing['brand'];
			$confidence      = $missing['confidence'];
			$category_slug   = GCB_Brand_Dictionary::get_category_from_brand( $brand );
			$conf_percent    = round( $confidence * 100 );
			$recommendations[] = "Add category: {$category_slug} (confidence: {$conf_percent}%)";
		}

		// Extra brand recommendations (usually low priority).
		foreach ( $mismatches['extra_brand'] as $extra_brand ) {
			$category_slug   = GCB_Brand_Dictionary::get_category_from_brand( $extra_brand );
			$recommendations[] = "Review category: {$category_slug} (brand not detected in content)";
		}

		return $recommendations;
	}

	/**
	 * Format audit result as text for CLI output.
	 *
	 * @param array $audit_result Audit result from audit_post().
	 * @return string Formatted text.
	 */
	public static function format_audit_result( array $audit_result ): string {
		$output = "Post ID: {$audit_result['post_id']}\n";
		$output .= "Title: {$audit_result['post_title']}\n";

		if ( $audit_result['is_comparison'] ) {
			$output .= "Type: COMPARISON POST\n";
		}

		$output .= "Detected Brands:\n";
		foreach ( $audit_result['detected_brands'] as $brand => $confidence ) {
			$conf_percent = round( $confidence * 100 );
			$output .= "  - {$brand} ({$conf_percent}%)\n";
		}

		$output .= "Assigned Categories:\n";
		foreach ( $audit_result['assigned_brands'] as $slug => $brand ) {
			$output .= "  - {$brand} ({$slug})\n";
		}

		if ( self::has_mismatches( $audit_result ) ) {
			$output .= "MISMATCHES FOUND:\n";
			$recommendations = self::generate_recommendations( $audit_result );
			foreach ( $recommendations as $rec ) {
				$output .= "  → {$rec}\n";
			}
		} else {
			$output .= "Status: ✅ OK\n";
		}

		return $output;
	}
}
