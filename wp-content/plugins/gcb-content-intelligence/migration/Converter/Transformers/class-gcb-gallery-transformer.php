<?php
/**
 * Gallery Transformer.
 *
 * Converts [fusion_gallery] and [fusion_gallery_image] to core/gallery blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Gallery_Transformer
 *
 * Transforms Fusion Gallery shortcodes to Gutenberg Gallery blocks.
 */
final class GCB_Gallery_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_gallery',
			'fusion_gallery_image',
			'fusion_images',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		// fusion_gallery_image is a child - just pass through the image content.
		if ( 'fusion_gallery_image' === $node->tag ) {
			return $this->transformGalleryImage( $node, $childContent );
		}

		// fusion_gallery wraps the images.
		return $this->transformGallery( $node, $childContent );
	}

	/**
	 * Transform a gallery container.
	 *
	 * @param GCB_Shortcode_Node $node         The gallery node.
	 * @param string             $childContent Converted child content.
	 * @return string Gallery block markup.
	 */
	private function transformGallery( GCB_Shortcode_Node $node, string $childContent ): string {
		// Extract image IDs from child content if available.
		$imageIds = [];
		if ( preg_match_all( '/<!-- wp:image \{"id":(\d+)/', $childContent, $matches ) ) {
			$imageIds = array_map( 'intval', $matches[1] );
		}

		// Get layout from attributes.
		$layout  = $node->getAttribute( 'layout' );
		$columns = $node->getAttribute( 'columns' );

		// Build gallery attributes.
		$attributes = [];

		if ( ! empty( $imageIds ) ) {
			$attributes['ids'] = $imageIds;
		}

		if ( '' !== $columns && is_numeric( $columns ) ) {
			$attributes['columns'] = (int) $columns;
		}

		$attrJson = ! empty( $attributes )
			? ' ' . json_encode( $attributes, JSON_UNESCAPED_SLASHES )
			: '';

		// If we have child content with images, wrap in gallery.
		if ( '' !== trim( $childContent ) ) {
			return sprintf(
				"<!-- wp:gallery%s -->\n" .
				"<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\">\n" .
				"%s" .
				"</figure>\n" .
				"<!-- /wp:gallery -->\n",
				$attrJson,
				$childContent
			);
		}

		// Empty gallery.
		return sprintf(
			"<!-- wp:gallery%s -->\n" .
			"<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\"></figure>\n" .
			"<!-- /wp:gallery -->\n",
			$attrJson
		);
	}

	/**
	 * Transform a gallery image.
	 *
	 * @param GCB_Shortcode_Node $node         The image node.
	 * @param string             $childContent Child content (usually empty or image tag).
	 * @return string Image block markup.
	 */
	private function transformGalleryImage( GCB_Shortcode_Node $node, string $childContent ): string {
		$imageId = $node->getAttribute( 'image_id' );
		$image   = $node->getAttribute( 'image' );
		$link    = $node->getAttribute( 'link' );

		// Try to extract image URL.
		$src = '';
		$alt = '';

		if ( '' !== $image ) {
			$src = $image;
		}

		// Try to get from child content.
		if ( '' === $src && preg_match( '/src=["\']([^"\']+)["\']/', $childContent, $srcMatch ) ) {
			$src = $srcMatch[1];
		}

		if ( preg_match( '/alt=["\']([^"\']*)["\']/', $childContent, $altMatch ) ) {
			$alt = $altMatch[1];
		}

		// Build image attributes.
		$attributes = [];
		if ( '' !== $imageId && is_numeric( $imageId ) ) {
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
		if ( '' !== $imageId ) {
			$imgAttrs .= sprintf( ' class="wp-image-%s"', htmlspecialchars( $imageId, ENT_QUOTES, 'UTF-8' ) );
		}

		$imgTag = sprintf( '<img%s/>', $imgAttrs );

		// Wrap in link if specified.
		if ( '' !== $link ) {
			$imgTag = sprintf(
				'<a href="%s">%s</a>',
				htmlspecialchars( $link, ENT_QUOTES, 'UTF-8' ),
				$imgTag
			);
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
