<?php
/**
 * Column Transformer.
 *
 * Converts [fusion_builder_column] to core/column blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Column_Transformer
 *
 * Transforms Fusion Builder columns to Gutenberg Column blocks.
 */
final class GCB_Column_Transformer implements GCB_Transformer_Interface {

	/**
	 * Width mapping from Fusion type to percentage string.
	 *
	 * @var array<string, string>
	 */
	private const WIDTH_MAP = [
		'1_1' => '100%',
		'1_2' => '50%',
		'1_3' => '33.33%',
		'2_3' => '66.67%',
		'1_4' => '25%',
		'3_4' => '75%',
		'1_5' => '20%',
		'2_5' => '40%',
		'3_5' => '60%',
		'4_5' => '80%',
		'1_6' => '16.67%',
		'5_6' => '83.33%',
	];

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_builder_column',
			'fusion_builder_column_inner',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$type  = $node->getAttribute( 'type', '1_1' );
		$width = self::WIDTH_MAP[ $type ] ?? '100%';

		$attributes = [
			'width' => $width,
		];

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		return sprintf(
			"<!-- wp:column %s -->\n" .
			"<div class=\"wp-block-column\">\n" .
			"%s" .
			"</div>\n" .
			"<!-- /wp:column -->\n",
			$attrJson,
			$childContent
		);
	}
}
