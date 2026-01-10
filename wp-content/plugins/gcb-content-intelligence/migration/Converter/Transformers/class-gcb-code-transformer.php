<?php
/**
 * Code Transformer.
 *
 * Converts [fusion_code] to core/html blocks.
 * Handles base64 encoded content from legacy Avada.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Code_Transformer
 *
 * Transforms Fusion Code shortcodes to Gutenberg HTML blocks.
 */
final class GCB_Code_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_code',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$content = $childContent;

		// Try to decode as base64 if it looks like encoded content.
		if ( $this->isBase64( $content ) ) {
			$decoded = base64_decode( $content, true );
			if ( false !== $decoded ) {
				$content = $decoded;
			}
		}

		// Trim whitespace.
		$content = trim( $content );

		if ( '' === $content ) {
			return '';
		}

		return sprintf(
			"<!-- wp:html -->\n%s\n<!-- /wp:html -->\n",
			$content
		);
	}

	/**
	 * Check if content appears to be base64 encoded.
	 *
	 * @param string $content Content to check.
	 * @return bool True if content appears to be base64.
	 */
	private function isBase64( string $content ): bool {
		$content = trim( $content );

		// Empty or too short to be meaningful base64.
		if ( strlen( $content ) < 4 ) {
			return false;
		}

		// Check if it looks like HTML (starts with < and ends with >).
		if ( str_starts_with( $content, '<' ) && str_ends_with( $content, '>' ) ) {
			return false;
		}

		// Check if content only contains valid base64 characters.
		if ( ! preg_match( '/^[A-Za-z0-9+\/=]+$/', $content ) ) {
			return false;
		}

		// Try to decode and check if result looks like HTML/content.
		$decoded = base64_decode( $content, true );
		if ( false === $decoded ) {
			return false;
		}

		// If decoded contains common HTML characters, it's likely base64.
		return str_contains( $decoded, '<' ) || str_contains( $decoded, '>' );
	}
}
