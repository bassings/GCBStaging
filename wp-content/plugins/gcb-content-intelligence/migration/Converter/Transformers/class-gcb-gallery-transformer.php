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
	 * Transform a gallery container to Spectra uagb/image-gallery.
	 *
	 * @param GCB_Shortcode_Node $node         The gallery node.
	 * @param string             $childContent Converted child content (contains image markers).
	 * @return string Spectra gallery block markup.
	 */
	private function transformGallery( GCB_Shortcode_Node $node, string $childContent ): string {
		// Extract image data from child markers.
		$mediaGallery = $this->extractMediaGallery( $childContent );

		if ( empty( $mediaGallery ) ) {
			// No images found - return empty or comment.
			return "<!-- gcb-migration: empty gallery -->\n";
		}

		// Generate unique block ID.
		$blockId = 'gcb-gallery-' . substr( md5( uniqid( '', true ) ), 0, 8 );

		// Get layout preferences from Fusion attributes.
		$layout       = $node->getAttribute( 'layout' );
		$columns      = $node->getAttribute( 'columns' );
		$isCarousel   = ( 'slider' === $layout || 'carousel' === $layout || '' === $layout );

		// Build Spectra block attributes.
		$attributes = [
			'block_id'         => $blockId,
			'feedLayout'       => $isCarousel ? 'carousel' : 'grid',
			'imageSize'        => 'large',
			'mediaGallery'     => $mediaGallery,
			'carouselArrows'   => true,
			'carouselDots'     => true,
			'carouselAutoplay' => true,
			'carouselSpeed'    => 3000,
			'focusOnSelect'    => true,
		];

		// Add columns for grid layout.
		if ( ! $isCarousel && '' !== $columns && is_numeric( $columns ) ) {
			$attributes['columnsDesk'] = (int) $columns;
			$attributes['columnsTab']  = min( (int) $columns, 3 );
			$attributes['columnsMob']  = 1;
		}

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

		// Build the inner HTML with image figures.
		$innerHtml = $this->buildGalleryInnerHtml( $mediaGallery, $blockId );

		return sprintf(
			"<!-- wp:uagb/image-gallery %s -->\n" .
			"<div class=\"wp-block-uagb-image-gallery uagb-block-%s\">\n" .
			"<div class=\"uagb-image-gallery\">\n" .
			"%s" .
			"</div>\n" .
			"</div>\n" .
			"<!-- /wp:uagb/image-gallery -->\n",
			$attrJson,
			$blockId,
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
	 * Build inner HTML for gallery images.
	 *
	 * @param array  $mediaGallery Array of media objects.
	 * @param string $blockId      Block ID for classes.
	 * @return string HTML for gallery images.
	 */
	private function buildGalleryInnerHtml( array $mediaGallery, string $blockId ): string {
		$html = '';

		foreach ( $mediaGallery as $index => $media ) {
			$src     = htmlspecialchars( $media['url'], ENT_QUOTES, 'UTF-8' );
			$alt     = htmlspecialchars( $media['alt'] ?? '', ENT_QUOTES, 'UTF-8' );
			$caption = htmlspecialchars( $media['caption'] ?? '', ENT_QUOTES, 'UTF-8' );

			$imgTag = sprintf( '<img src="%s" alt="%s" />', $src, $alt );

			// Wrap in link if specified.
			if ( ! empty( $media['link'] ) ) {
				$link   = htmlspecialchars( $media['link'], ENT_QUOTES, 'UTF-8' );
				$imgTag = sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', $link, $imgTag );
			}

			$html .= sprintf(
				"<figure class=\"uagb-image-gallery__media-wrapper\">\n" .
				"<div class=\"uagb-image-gallery__media\">\n" .
				"%s\n" .
				"</div>\n" .
				"%s" .
				"</figure>\n",
				$imgTag,
				'' !== $caption ? sprintf( "<figcaption>%s</figcaption>\n", $caption ) : ''
			);
		}

		return $html;
	}
}
