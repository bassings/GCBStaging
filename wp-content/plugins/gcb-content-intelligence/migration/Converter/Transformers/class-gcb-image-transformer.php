<?php
/**
 * Image Transformer.
 *
 * Converts [fusion_imageframe] to core/image blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Image_Transformer
 *
 * Transforms Fusion Image Frame shortcodes to Gutenberg Image blocks.
 */
final class GCB_Image_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_imageframe',
			'fusion_image',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$link       = $node->getAttribute( 'link' );
		$linkTarget = $node->getAttribute( 'linktarget' );
		$imageId    = $node->getAttribute( 'image_id' );

		// Extract image details from inner content.
		$src = '';
		$alt = '';

		if ( preg_match( '/src=["\']([^"\']+)["\']/', $childContent, $srcMatch ) ) {
			$src = $srcMatch[1];
		}

		if ( preg_match( '/alt=["\']([^"\']*)["\']/', $childContent, $altMatch ) ) {
			$alt = $altMatch[1];
		}

		// Build block attributes.
		$attributes = [];

		if ( '' !== $imageId ) {
			$attributes['id'] = (int) $imageId;
		}

		if ( '' !== $link ) {
			$attributes['linkDestination'] = 'custom';
		}

		$attrJson = ! empty( $attributes )
			? ' ' . json_encode( $attributes, JSON_UNESCAPED_SLASHES )
			: '';

		// Build image tag.
		$imgAttrs = '';
		if ( '' !== $src ) {
			$imgAttrs .= sprintf( ' src="%s"', htmlspecialchars( $src, ENT_QUOTES, 'UTF-8' ) );
		}
		if ( '' !== $alt ) {
			$imgAttrs .= sprintf( ' alt="%s"', htmlspecialchars( $alt, ENT_QUOTES, 'UTF-8' ) );
		}

		$imgTag = sprintf( '<img%s/>', $imgAttrs );

		// Wrap in link if specified.
		if ( '' !== $link ) {
			$linkAttrs = sprintf( 'href="%s"', htmlspecialchars( $link, ENT_QUOTES, 'UTF-8' ) );
			if ( '_blank' === $linkTarget ) {
				$linkAttrs .= ' target="_blank" rel="noopener"';
			}
			$imgTag = sprintf( '<a %s>%s</a>', $linkAttrs, $imgTag );
		}

		return sprintf(
			"<!-- wp:image%s -->\n" .
			"<figure class=\"wp-block-image\">%s</figure>\n" .
			"<!-- /wp:image -->\n",
			$attrJson,
			$imgTag
		);
	}
}
