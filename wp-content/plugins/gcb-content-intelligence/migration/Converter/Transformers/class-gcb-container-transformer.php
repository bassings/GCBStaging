<?php
/**
 * Container Transformer.
 *
 * Converts [fusion_builder_container] to core/group blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Container_Transformer
 *
 * Transforms Fusion Builder containers to Gutenberg Group blocks.
 */
final class GCB_Container_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_builder_container',
			'fusion_builder_container_inner',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$attributes = [
			'layout' => 'constrained',
		];

		// Check for full-width container.
		if ( 'yes' === $node->getAttribute( 'hundred_percent' ) ) {
			$attributes['layout'] = 'full';
		}

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		return sprintf(
			"<!-- wp:group %s -->\n" .
			"<div class=\"wp-block-group\">\n" .
			"%s" .
			"</div>\n" .
			"<!-- /wp:group -->\n",
			$attrJson,
			$childContent
		);
	}
}
