<?php
/**
 * Separator Transformer.
 *
 * Converts [fusion_separator] to core/separator blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Separator_Transformer
 *
 * Transforms Fusion Separator shortcodes to Gutenberg Separator blocks.
 */
final class GCB_Separator_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_separator',
			'fusion_section_separator',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$styleType = $node->getAttribute( 'style_type' );
		$color     = $node->getAttribute( 'sep_color' );

		// Determine separator style.
		$className = 'wp-block-separator has-alpha-channel-opacity';
		$style     = '';

		// Add wide style if color is specified.
		if ( '' !== $color ) {
			$className .= ' is-style-wide';
			$style      = sprintf( ' style="border-color:%s"', htmlspecialchars( $color, ENT_QUOTES, 'UTF-8' ) );
		}

		$attributes = [];

		if ( '' !== $color ) {
			$attributes['className'] = 'is-style-wide';
		}

		$attrJson = ! empty( $attributes )
			? ' ' . json_encode( $attributes, JSON_UNESCAPED_SLASHES )
			: '';

		return sprintf(
			"<!-- wp:separator%s -->\n" .
			"<hr class=\"%s\"%s/>\n" .
			"<!-- /wp:separator -->\n",
			$attrJson,
			$className,
			$style
		);
	}
}
