<?php
/**
 * Migration Service.
 *
 * Core service that handles Avada to Gutenberg migration logic.
 * Used by the WP-CLI command and can be used programmatically.
 *
 * @package GCB_Content_Intelligence\Migration\CLI
 */

declare(strict_types=1);

/**
 * Class GCB_Migration_Service
 *
 * Handles the conversion of post content from Avada shortcodes to Gutenberg blocks.
 */
final class GCB_Migration_Service {

	/**
	 * Parser instance.
	 *
	 * @var GCB_Shortcode_Parser
	 */
	private GCB_Shortcode_Parser $parser;

	/**
	 * Converter instance.
	 *
	 * @var GCB_To_Block_Converter
	 */
	private GCB_To_Block_Converter $converter;

	/**
	 * Regex pattern to detect Fusion shortcodes.
	 *
	 * @var string
	 */
	private const FUSION_PATTERN = '/\[fusion_[a-z_]+/i';

	/**
	 * Constructor.
	 *
	 * Initializes parser and converter with all transformers.
	 */
	public function __construct() {
		$this->parser    = new GCB_Shortcode_Parser();
		$this->converter = new GCB_To_Block_Converter();

		// Register all transformers.
		$this->converter->registerTransformer( new GCB_Container_Transformer() );
		$this->converter->registerTransformer( new GCB_Row_Transformer() );
		$this->converter->registerTransformer( new GCB_Column_Transformer() );
		$this->converter->registerTransformer( new GCB_Text_Transformer() );
		$this->converter->registerTransformer( new GCB_YouTube_Transformer() );
		$this->converter->registerTransformer( new GCB_Separator_Transformer() );
		$this->converter->registerTransformer( new GCB_Code_Transformer() );
		$this->converter->registerTransformer( new GCB_Button_Transformer() );
		$this->converter->registerTransformer( new GCB_Image_Transformer() );
	}

	/**
	 * Check if content needs migration.
	 *
	 * @param string $content Post content to check.
	 * @return bool True if content contains Fusion shortcodes.
	 */
	public function needsMigration( string $content ): bool {
		if ( '' === $content ) {
			return false;
		}

		return (bool) preg_match( self::FUSION_PATTERN, $content );
	}

	/**
	 * Migrate content from Avada shortcodes to Gutenberg blocks.
	 *
	 * @param string $content  Post content to migrate.
	 * @param bool   $dryRun   If true, returns preview without marking as final.
	 * @return GCB_Migration_Result Migration result with converted content.
	 */
	public function migrateContent( string $content, bool $dryRun = false ): GCB_Migration_Result {
		$result                  = new GCB_Migration_Result();
		$result->originalContent = $content;
		$result->isDryRun        = $dryRun;

		// Check if migration is needed.
		if ( ! $this->needsMigration( $content ) ) {
			$result->success    = true;
			$result->hasChanges = false;
			$result->content    = $content;
			return $result;
		}

		try {
			// Parse content to AST.
			$ast = $this->parser->parse( $content );

			// Track unknown shortcodes.
			$unknownShortcodes = $this->findUnknownShortcodes( $ast );
			foreach ( $unknownShortcodes as $tag ) {
				$result->warnings[] = "Unknown shortcode: [{$tag}]";
			}

			// Convert AST to Gutenberg blocks.
			$converted = $this->converter->convert( $ast );

			$result->success    = true;
			$result->hasChanges = $converted !== $content;
			$result->content    = $converted;

			// Track statistics.
			$result->stats = $this->calculateStats( $ast );

		} catch ( Exception $e ) {
			$result->success  = false;
			$result->content  = $content; // Return original on failure.
			$result->errors[] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Get batch statistics for a set of posts.
	 *
	 * @param array $posts Array of posts with 'id' and 'content' keys.
	 * @return array Statistics array.
	 */
	public function getBatchStats( array $posts ): array {
		$stats = [
			'total'           => count( $posts ),
			'needs_migration' => 0,
			'already_clean'   => 0,
		];

		foreach ( $posts as $post ) {
			$content = $post['content'] ?? '';
			if ( $this->needsMigration( $content ) ) {
				$stats['needs_migration']++;
			} else {
				$stats['already_clean']++;
			}
		}

		return $stats;
	}

	/**
	 * Find unknown shortcodes in AST.
	 *
	 * @param array $nodes AST nodes.
	 * @return array List of unknown shortcode tags.
	 */
	private function findUnknownShortcodes( array $nodes ): array {
		$known = [
			'fusion_builder_container',
			'fusion_builder_container_inner',
			'fusion_builder_row',
			'fusion_builder_row_inner',
			'fusion_builder_column',
			'fusion_builder_column_inner',
			'fusion_text',
			'fusion_youtube',
			'fusion_vimeo',
			'fusion_separator',
			'fusion_section_separator',
			'fusion_code',
			'fusion_button',
			'fusion_tagline_box',
			'fusion_imageframe',
			'fusion_image',
		];

		$unknown = [];

		foreach ( $nodes as $node ) {
			if ( $node->isShortcode() && ! in_array( $node->tag, $known, true ) ) {
				if ( ! in_array( $node->tag, $unknown, true ) ) {
					$unknown[] = $node->tag;
				}
			}

			// Check children recursively.
			if ( ! empty( $node->children ) ) {
				$childUnknown = $this->findUnknownShortcodes( $node->children );
				foreach ( $childUnknown as $tag ) {
					if ( ! in_array( $tag, $unknown, true ) ) {
						$unknown[] = $tag;
					}
				}
			}
		}

		return $unknown;
	}

	/**
	 * Calculate statistics from AST.
	 *
	 * @param array $nodes AST nodes.
	 * @return array Statistics.
	 */
	private function calculateStats( array $nodes ): array {
		$count = 0;

		foreach ( $nodes as $node ) {
			if ( $node->isShortcode() ) {
				$count++;
			}
			if ( ! empty( $node->children ) ) {
				$childStats = $this->calculateStats( $node->children );
				$count     += $childStats['shortcodes_converted'];
			}
		}

		return [
			'shortcodes_converted' => $count,
		];
	}
}

/**
 * Class GCB_Migration_Result
 *
 * Value object containing migration results.
 */
final class GCB_Migration_Result {

	/**
	 * Whether migration succeeded.
	 *
	 * @var bool
	 */
	public bool $success = false;

	/**
	 * Whether content was changed.
	 *
	 * @var bool
	 */
	public bool $hasChanges = false;

	/**
	 * Whether this was a dry run.
	 *
	 * @var bool
	 */
	public bool $isDryRun = false;

	/**
	 * Converted content (or original if no changes).
	 *
	 * @var string
	 */
	public string $content = '';

	/**
	 * Original content before migration.
	 *
	 * @var string
	 */
	public string $originalContent = '';

	/**
	 * Error messages if migration failed.
	 *
	 * @var array<string>
	 */
	public array $errors = [];

	/**
	 * Warning messages (e.g., unknown shortcodes).
	 *
	 * @var array<string>
	 */
	public array $warnings = [];

	/**
	 * Statistics about the migration.
	 *
	 * @var array<string, int>
	 */
	public array $stats = [];
}
