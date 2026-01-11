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
	 * Transform a gallery container to Spectra uagb/image-gallery carousel block.
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

		// Get columns from Fusion attributes.
		$columns = $node->getAttribute( 'columns' );
		$cols    = ( '' !== $columns && is_numeric( $columns ) ) ? (int) $columns : 3;

		// Generate unique block ID.
		$blockId = 'gcb-' . substr( md5( uniqid( '', true ) ), 0, 8 );

		// Build mediaGallery array for Spectra (requires sizes property).
		$spectraMedia = [];
		foreach ( $mediaGallery as $index => $media ) {
			$spectraMedia[] = [
				'id'      => $media['id'] ?: $index,
				'url'     => $media['url'],
				'alt'     => $media['alt'] ?? '',
				'caption' => $media['caption'] ?? '',
				'sizes'   => [
					'large'     => [ 'url' => $media['url'] ],
					'medium'    => [ 'url' => $media['url'] ],
					'thumbnail' => [ 'url' => $media['url'] ],
				],
			];
		}

		// Build Spectra block attributes for carousel layout.
		$attributes = [
			'block_id'                => $blockId,
			'readyToRender'           => true,
			'feedLayout'              => 'carousel',
			'mediaGallery'            => $spectraMedia,
			'columnsDesk'             => min( $cols, 4 ),
			'columnsTab'              => min( $cols, 3 ),
			'columnsMob'              => 1,
			'gridImageGap'            => 16,
			'carouselLoop'            => true,
			'carouselAutoplay'        => false,
			'carouselPauseOnHover'    => true,
			'carouselTransitionSpeed' => 500,
			'imageDisplayCaption'     => false,
			'imageClickEvent'         => 'lightbox',
		];

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		// Spectra uses server-side rendering, so output self-closing block.
		return sprintf(
			"<!-- wp:uagb/image-gallery %s /-->\n",
			$attrJson
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

}
