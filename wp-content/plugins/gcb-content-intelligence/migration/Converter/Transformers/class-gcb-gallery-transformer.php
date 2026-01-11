<?php
/**
 * Gallery Transformer.
 *
 * Converts [fusion_gallery] and [fusion_gallery_image] to Spectra uagb/image-gallery blocks.
 * Outputs carousel layout for enhanced user experience.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Gallery_Transformer
 *
 * Transforms Fusion Gallery shortcodes to Spectra Image Gallery blocks with carousel.
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
		// fusion_gallery_image is a child - collect image data for parent gallery.
		if ( 'fusion_gallery_image' === $node->tag ) {
			return $this->transformGalleryImage( $node, $childContent );
		}

		// fusion_gallery wraps the images - output Spectra carousel.
		return $this->transformGallery( $node, $childContent );
	}

	/**
	 * Transform a gallery container to core/gallery block.
	 *
	 * @param GCB_Shortcode_Node $node         The gallery node.
	 * @param string             $childContent Converted child content (contains image markers).
	 * @return string Gallery block markup.
	 */
	private function transformGallery( GCB_Shortcode_Node $node, string $childContent ): string {
		// Extract image data from child markers.
		$mediaGallery = $this->extractMediaGallery( $childContent );

		if ( empty( $mediaGallery ) ) {
			// No images found - return empty or comment.
			return "<!-- gcb-migration: empty gallery -->\n";
		}

		// Get columns from Fusion attributes.
		$columns = $node->getAttribute( 'columns' );
		$cols    = ( '' !== $columns && is_numeric( $columns ) ) ? (int) $columns : 3;

		// Build core/gallery block attributes.
		$attributes = [
			'linkTo'  => 'none',
			'columns' => $cols,
		];

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		// Build the inner HTML with image figures.
		$innerHtml = $this->buildCoreGalleryHtml( $mediaGallery );

		return sprintf(
			"<!-- wp:gallery %s -->\n" .
			"<figure class=\"wp-block-gallery has-nested-images columns-%d is-cropped\">\n" .
			"%s" .
			"</figure>\n" .
			"<!-- /wp:gallery -->\n",
			$attrJson,
			$cols,
			$innerHtml
		);
	}

	/**
	 * Transform a gallery image - outputs a marker for parent to collect.
	 *
	 * @param GCB_Shortcode_Node $node         The image node.
	 * @param string             $childContent Child content.
	 * @return string Image marker for gallery collection.
	 */
	private function transformGalleryImage( GCB_Shortcode_Node $node, string $childContent ): string {
		$imageId = $node->getAttribute( 'image_id' );
		$image   = $node->getAttribute( 'image' );
		$link    = $node->getAttribute( 'link' );
		$caption = $node->getAttribute( 'image_caption' );

		// Try to extract image URL from attribute or child content.
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

		// Output a marker that the parent gallery can parse.
		$imageData = [
			'id'      => '' !== $imageId ? (int) $imageId : 0,
			'url'     => $src,
			'alt'     => $alt,
			'caption' => $caption,
			'link'    => $link,
		];

		return sprintf(
			"<!-- gcb-gallery-image %s -->\n",
			base64_encode( json_encode( $imageData, JSON_UNESCAPED_SLASHES ) )
		);
	}

	/**
	 * Extract media gallery array from child content markers.
	 *
	 * @param string $childContent Content with image markers.
	 * @return array Array of media objects for Spectra.
	 */
	private function extractMediaGallery( string $childContent ): array {
		$mediaGallery = [];

		// Find all image markers.
		if ( preg_match_all( '/<!-- gcb-gallery-image ([A-Za-z0-9+\/=]+) -->/', $childContent, $matches ) ) {
			foreach ( $matches[1] as $encoded ) {
				$decoded = base64_decode( $encoded, true );
				if ( false !== $decoded ) {
					$imageData = json_decode( $decoded, true );
					if ( is_array( $imageData ) && ! empty( $imageData['url'] ) ) {
						$mediaGallery[] = [
							'id'      => $imageData['id'] ?? 0,
							'url'     => $imageData['url'],
							'alt'     => $imageData['alt'] ?? '',
							'caption' => $imageData['caption'] ?? '',
							'link'    => $imageData['link'] ?? '',
						];
					}
				}
			}
		}

		return $mediaGallery;
	}

	/**
	 * Build HTML for core/gallery images using nested wp:image blocks.
	 *
	 * @param array $mediaGallery Array of media objects.
	 * @return string HTML for gallery images.
	 */
	private function buildCoreGalleryHtml( array $mediaGallery ): string {
		$html = '';

		foreach ( $mediaGallery as $media ) {
			$id      = (int) ( $media['id'] ?? 0 );
			$src     = htmlspecialchars( $media['url'], ENT_QUOTES, 'UTF-8' );
			$alt     = htmlspecialchars( $media['alt'] ?? '', ENT_QUOTES, 'UTF-8' );
			$caption = htmlspecialchars( $media['caption'] ?? '', ENT_QUOTES, 'UTF-8' );

			// Build wp:image block attributes.
			$imageAttrs = [ 'sizeSlug' => 'large' ];
			if ( $id > 0 ) {
				$imageAttrs['id'] = $id;
			}
			$imageAttrJson = json_encode( $imageAttrs, JSON_UNESCAPED_SLASHES );

			$captionHtml = '' !== $caption
				? sprintf( "<figcaption class=\"wp-element-caption\">%s</figcaption>\n", $caption )
				: '';

			$html .= sprintf(
				"<!-- wp:image %s -->\n" .
				"<figure class=\"wp-block-image size-large\">" .
				"<img src=\"%s\" alt=\"%s\" class=\"%s\" />" .
				"%s" .
				"</figure>\n" .
				"<!-- /wp:image -->\n",
				$imageAttrJson,
				$src,
				$alt,
				$id > 0 ? "wp-image-{$id}" : '',
				$captionHtml
			);
		}

		return $html;
	}
}
