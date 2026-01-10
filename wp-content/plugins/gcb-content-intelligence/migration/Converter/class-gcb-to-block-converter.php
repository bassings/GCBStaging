<?php
/**
 * AST to Gutenberg Block Converter.
 *
 * Traverses the shortcode AST and converts nodes to Gutenberg block markup
 * using registered transformers.
 *
 * @package GCB_Content_Intelligence\Migration\Converter
 */

declare(strict_types=1);

/**
 * Class GCB_To_Block_Converter
 *
 * Main converter engine for AST to Gutenberg blocks.
 */
final class GCB_To_Block_Converter {

	/**
	 * Registered transformers indexed by shortcode tag.
	 *
	 * @var array<string, GCB_Transformer_Interface>
	 */
	private array $transformers = [];

	/**
	 * Register a transformer for its supported tags.
	 *
	 * @param GCB_Transformer_Interface $transformer Transformer instance.
	 * @return void
	 */
	public function registerTransformer( GCB_Transformer_Interface $transformer ): void {
		foreach ( $transformer->getSupportedTags() as $tag ) {
			$this->transformers[ $tag ] = $transformer;
		}
	}

	/**
	 * Convert AST nodes to Gutenberg block markup.
	 *
	 * @param array<GCB_Shortcode_Node> $nodes AST nodes from parser.
	 * @return string Gutenberg block HTML.
	 */
	public function convert( array $nodes ): string {
		$output = '';

		foreach ( $nodes as $node ) {
			$output .= $this->convertNode( $node );
		}

		return $output;
	}

	/**
	 * Convert a single AST node to block markup.
	 *
	 * @param GCB_Shortcode_Node $node AST node.
	 * @return string Block markup.
	 */
	private function convertNode( GCB_Shortcode_Node $node ): string {
		// Text nodes pass through unchanged.
		if ( $node->isText() ) {
			return $node->content;
		}

		// Convert children first (depth-first traversal).
		$childContent = '';
		foreach ( $node->children as $child ) {
			$childContent .= $this->convertNode( $child );
		}

		// Find transformer for this shortcode.
		$transformer = $this->transformers[ $node->tag ] ?? null;

		if ( null === $transformer ) {
			return $this->handleUnknownShortcode( $node, $childContent );
		}

		return $transformer->transform( $node, $childContent );
	}

	/**
	 * Handle shortcodes without registered transformers.
	 *
	 * Wraps content in HTML comments for visibility and debugging.
	 *
	 * @param GCB_Shortcode_Node $node         Unknown shortcode node.
	 * @param string             $childContent Converted child content.
	 * @return string Content wrapped in comments.
	 */
	private function handleUnknownShortcode( GCB_Shortcode_Node $node, string $childContent ): string {
		$tag = esc_html( $node->tag );

		return sprintf(
			"<!-- gcb-migration: unknown shortcode [%s] -->\n%s\n<!-- /gcb-migration: [%s] -->",
			$tag,
			$childContent,
			$tag
		);
	}
}

/**
 * Escape HTML for safe output.
 *
 * Standalone function for non-WordPress environments (testing).
 *
 * @param string $text Text to escape.
 * @return string Escaped text.
 */
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
