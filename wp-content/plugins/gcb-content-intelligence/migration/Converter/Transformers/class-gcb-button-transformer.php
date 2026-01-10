<?php
/**
 * Button Transformer.
 *
 * Converts [fusion_button] to core/buttons blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Button_Transformer
 *
 * Transforms Fusion Button shortcodes to Gutenberg Button blocks.
 */
final class GCB_Button_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_button',
			'fusion_tagline_box', // Often contains CTA buttons.
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$link   = $node->getAttribute( 'link' );
		$target = $node->getAttribute( 'target' );
		$title  = $node->getAttribute( 'title' );
		$text   = '' !== $childContent ? $childContent : ( '' !== $title ? $title : 'Button' );

		// Build link attributes.
		$linkAttrs = '';
		if ( '' !== $link ) {
			$linkAttrs .= sprintf( ' href="%s"', htmlspecialchars( $link, ENT_QUOTES, 'UTF-8' ) );
		}

		if ( '_blank' === $target ) {
			$linkAttrs .= ' target="_blank" rel="noopener"';
		}

		// Button block attributes.
		$buttonAttrs = [];

		$buttonAttrJson = ! empty( $buttonAttrs )
			? ' ' . json_encode( $buttonAttrs, JSON_UNESCAPED_SLASHES )
			: '';

		return sprintf(
			"<!-- wp:buttons -->\n" .
			"<div class=\"wp-block-buttons\">\n" .
			"<!-- wp:button%s -->\n" .
			"<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\"%s>%s</a></div>\n" .
			"<!-- /wp:button -->\n" .
			"</div>\n" .
			"<!-- /wp:buttons -->\n",
			$buttonAttrJson,
			$linkAttrs,
			htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' )
		);
	}
}
