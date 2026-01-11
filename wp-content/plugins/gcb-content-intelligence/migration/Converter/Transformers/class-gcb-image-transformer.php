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

		// Parse image_id which may contain size suffix (e.g., "32670|medium").
		if ( '' !== $imageId && str_contains( $imageId, '|' ) ) {
			$parts   = explode( '|', $imageId );
			$imageId = $parts[0];
		}

		// Extract image details from inner content.
		$src = '';
		$alt = '';

		// Method 1: Look for src= in an img tag.
		if ( preg_match( '/src=["\']([^"\']+)["\']/', $childContent, $srcMatch ) ) {
			$src = $srcMatch[1];
		}

		// Method 2: Fusion Builder stores URL as plain text content.
		if ( '' === $src ) {
			$trimmed = trim( $childContent );
			// Check if content looks like a URL (starts with http or /).
			if ( preg_match( '/^(https?:\/\/|\/)[^\s<>]+\.(jpg|jpeg|png|gif|webp|svg)(\?[^\s]*)?$/i', $trimmed ) ) {
				$src = $trimmed;
			}
		}

		// Method 3: Extract URL from anywhere in content.
		if ( '' === $src && preg_match( '/(https?:\/\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg)(\?[^\s]*)?)/i', $childContent, $urlMatch ) ) {
			$src = $urlMatch[1];
		}

		if ( preg_match( '/alt=["\']([^"\']*)["\']/', $childContent, $altMatch ) ) {
			$alt = $altMatch[1];
		}

		// Build block attributes.
		$attributes = [];
		$sizeSlug   = 'full'; // Default size.

		if ( '' !== $imageId ) {
			$attributes['id'] = (int) $imageId;
		}

		$attributes['sizeSlug'] = $sizeSlug;

		if ( '' !== $link ) {
			$attributes['linkDestination'] = 'custom';
		}

		$attrJson = ' ' . json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		// Build figure classes - must include size class.
		$figureClass = 'wp-block-image size-' . $sizeSlug;

		// Build image tag with proper classes.
		$imgAttrs = '';
		if ( '' !== $src ) {
			$imgAttrs .= sprintf( ' src="%s"', htmlspecialchars( $src, ENT_QUOTES, 'UTF-8' ) );
		}
		if ( '' !== $alt ) {
			$imgAttrs .= sprintf( ' alt="%s"', htmlspecialchars( $alt, ENT_QUOTES, 'UTF-8' ) );
		} else {
			$imgAttrs .= ' alt=""'; // Always include alt attribute.
		}

		// Add wp-image-{id} class when image ID is known.
		if ( '' !== $imageId ) {
			$imgAttrs .= sprintf( ' class="wp-image-%d"', (int) $imageId );
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
			"<figure class=\"%s\">%s</figure>\n" .
			"<!-- /wp:image -->\n",
			$attrJson,
			$figureClass,
			$imgTag
		);
	}
}
