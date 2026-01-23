<?php
/**
 * GCB Brand Dictionary
 *
 * Provides brand name mapping, variations, and model lookups for brand categorization auditing.
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_Brand_Dictionary
 *
 * Manages car brand data including variations, models, and brand families.
 */
class GCB_Brand_Dictionary {

	/**
	 * Brand mappings data loaded from JSON.
	 *
	 * @var array|null
	 */
	private static ?array $mappings = null;

	/**
	 * Load brand mappings from JSON file.
	 *
	 * @return array Brand mappings data.
	 */
	private static function load_mappings(): array {
		if ( null !== self::$mappings ) {
			return self::$mappings;
		}

		$json_path = GCB_CI_DIR . 'data/brand-mappings.json';

		if ( ! file_exists( $json_path ) ) {
			return array(
				'brands'            => array(),
				'brand_families'    => array(),
				'category_mappings' => array(),
				'comparison_keywords' => array(),
			);
		}

		$json_content = file_get_contents( $json_path );
		self::$mappings = json_decode( $json_content, true );

		return self::$mappings;
	}

	/**
	 * Get canonical brand name from category slug.
	 *
	 * Example: "bmw-reviews" → "BMW"
	 *
	 * @param string $category_slug Category slug.
	 * @return string|null Canonical brand name or null if not found.
	 */
	public static function get_brand_from_category( string $category_slug ): ?string {
		$mappings = self::load_mappings();
		return $mappings['category_mappings'][ $category_slug ] ?? null;
	}

	/**
	 * Get category slug from canonical brand name.
	 *
	 * Example: "BMW" → "bmw-reviews"
	 *
	 * @param string $brand_name Canonical brand name.
	 * @return string|null Category slug or null if not found.
	 */
	public static function get_category_from_brand( string $brand_name ): ?string {
		$mappings = self::load_mappings();
		$flipped  = array_flip( $mappings['category_mappings'] );
		return $flipped[ $brand_name ] ?? null;
	}

	/**
	 * Normalize brand name to canonical form.
	 *
	 * Handles variations like "VW" → "Volkswagen", "Merc" → "Mercedes-Benz"
	 *
	 * @param string $brand_text Brand name or variation.
	 * @return string|null Canonical brand name or null if not recognized.
	 */
	public static function normalize_brand( string $brand_text ): ?string {
		$brand_text = strtolower( trim( $brand_text ) );
		$mappings   = self::load_mappings();

		foreach ( $mappings['brands'] as $brand_data ) {
			// Check exact canonical match (case-insensitive).
			if ( strtolower( $brand_data['canonical'] ) === $brand_text ) {
				return $brand_data['canonical'];
			}

			// Check variations.
			foreach ( $brand_data['variations'] as $variation ) {
				if ( strtolower( $variation ) === $brand_text ) {
					return $brand_data['canonical'];
				}
			}
		}

		return null;
	}

	/**
	 * Get brand from model name.
	 *
	 * Example: "Corolla" → "Toyota", "Civic" → "Honda"
	 *
	 * @param string $model_name Model name.
	 * @return string|null Brand name or null if model not found.
	 */
	public static function get_brand_from_model( string $model_name ): ?string {
		$model_name = strtolower( trim( $model_name ) );
		$mappings   = self::load_mappings();

		foreach ( $mappings['brands'] as $brand_data ) {
			foreach ( $brand_data['models'] as $model ) {
				if ( strtolower( $model ) === $model_name ) {
					return $brand_data['canonical'];
				}
			}
		}

		return null;
	}

	/**
	 * Check if brands belong to the same family (acceptable combination).
	 *
	 * Example: Toyota + Lexus = same family, Jaguar + Land Rover = NOT same family
	 *
	 * @param string $brand1 First brand name.
	 * @param string $brand2 Second brand name.
	 * @return bool True if brands are in the same family.
	 */
	public static function are_brands_in_same_family( string $brand1, string $brand2 ): bool {
		// Same brand is always OK.
		if ( $brand1 === $brand2 ) {
			return true;
		}

		$mappings = self::load_mappings();

		foreach ( $mappings['brand_families'] as $family ) {
			if ( ! $family['allow_mixing'] ) {
				continue;
			}

			$brands_in_family = $family['brands'];

			if ( in_array( $brand1, $brands_in_family, true ) && in_array( $brand2, $brands_in_family, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all canonical brand names.
	 *
	 * @return array List of all canonical brand names.
	 */
	public static function get_all_brands(): array {
		$mappings = self::load_mappings();
		$brands   = array();

		foreach ( $mappings['brands'] as $brand_data ) {
			$brands[] = $brand_data['canonical'];
		}

		return $brands;
	}

	/**
	 * Get all brand variations for a canonical brand name.
	 *
	 * @param string $canonical_brand Canonical brand name.
	 * @return array List of variations (includes canonical name).
	 */
	public static function get_brand_variations( string $canonical_brand ): array {
		$mappings = self::load_mappings();

		foreach ( $mappings['brands'] as $brand_data ) {
			if ( $brand_data['canonical'] === $canonical_brand ) {
				return array_merge( array( $brand_data['canonical'] ), $brand_data['variations'] );
			}
		}

		return array();
	}

	/**
	 * Get comparison keywords used to detect multi-brand posts.
	 *
	 * @return array List of comparison keywords (e.g., "vs", "versus").
	 */
	public static function get_comparison_keywords(): array {
		$mappings = self::load_mappings();
		return $mappings['comparison_keywords'] ?? array();
	}

	/**
	 * Get all model names for a brand.
	 *
	 * @param string $canonical_brand Canonical brand name.
	 * @return array List of model names.
	 */
	public static function get_brand_models( string $canonical_brand ): array {
		$mappings = self::load_mappings();

		foreach ( $mappings['brands'] as $brand_data ) {
			if ( $brand_data['canonical'] === $canonical_brand ) {
				return $brand_data['models'];
			}
		}

		return array();
	}

	/**
	 * Clear cached mappings (useful for testing).
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$mappings = null;
	}
}
