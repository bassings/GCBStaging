<?php
/**
 * Transformer Interface.
 *
 * Contract for all shortcode-to-block transformers.
 *
 * @package GCB_Content_Intelligence\Migration\Converter
 */

declare(strict_types=1);

/**
 * Interface GCB_Transformer_Interface
 *
 * All transformers must implement this interface.
 */
interface GCB_Transformer_Interface {

	/**
	 * Get the shortcode tags this transformer handles.
	 *
	 * @return array<string> List of supported shortcode tags.
	 */
	public function getSupportedTags(): array;

	/**
	 * Transform a shortcode node to Gutenberg block markup.
	 *
	 * @param GCB_Shortcode_Node $node         The AST node to transform.
	 * @param string             $childContent Already-converted child content.
	 * @return string Gutenberg block markup.
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string;
}
