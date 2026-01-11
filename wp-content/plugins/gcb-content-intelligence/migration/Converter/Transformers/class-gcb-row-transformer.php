<?php
/**
 * Row Transformer.
 *
 * Converts [fusion_builder_row] to core/columns blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Row_Transformer
 *
 * Transforms Fusion Builder rows to Gutenberg Columns blocks.
 */
final class GCB_Row_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_builder_row',
			'fusion_builder_row_inner',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$attributes = [];

		$attrJson = ! empty( $attributes )
			? json_encode( $attributes, JSON_UNESCAPED_SLASHES ) . ' '
			: '';

		return sprintf(
			"<!-- wp:columns %s-->\n" .
			"<div class=\"wp-block-columns\">\n" .
			"%s" .
			"</div>\n" .
			"<!-- /wp:columns -->\n",
			$attrJson,
			$childContent
		);
	}
}
