<?php
/**
 * YouTube Transformer.
 *
 * Converts [fusion_youtube] to core/embed blocks.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_YouTube_Transformer
 *
 * Transforms Fusion YouTube shortcodes to Gutenberg Embed blocks.
 */
final class GCB_YouTube_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_youtube',
			'fusion_vimeo',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		$videoId = $node->getAttribute( 'id' );

		if ( '' === $videoId ) {
			// No video ID: return empty or comment.
			return "<!-- gcb-migration: fusion_youtube missing id attribute -->\n";
		}

		// Determine provider based on tag.
		$isVimeo = 'fusion_vimeo' === $node->tag;

		if ( $isVimeo ) {
			return $this->createVimeoEmbed( $videoId );
		}

		return $this->createYouTubeEmbed( $videoId );
	}

	/**
	 * Create a YouTube embed block.
	 *
	 * @param string $videoId YouTube video ID.
	 * @return string Embed block markup.
	 */
	private function createYouTubeEmbed( string $videoId ): string {
		$url = 'https://www.youtube.com/watch?v=' . $videoId;

		$attributes = [
			'url'              => $url,
			'type'             => 'video',
			'providerNameSlug' => 'youtube',
			'responsive'       => true,
			'className'        => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
		];

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		return sprintf(
			"<!-- wp:embed %s -->\n" .
			"<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\">\n" .
			"<div class=\"wp-block-embed__wrapper\">\n" .
			"%s\n" .
			"</div>\n" .
			"</figure>\n" .
			"<!-- /wp:embed -->\n",
			$attrJson,
			$url
		);
	}

	/**
	 * Create a Vimeo embed block.
	 *
	 * @param string $videoId Vimeo video ID.
	 * @return string Embed block markup.
	 */
	private function createVimeoEmbed( string $videoId ): string {
		$url = 'https://vimeo.com/' . $videoId;

		$attributes = [
			'url'              => $url,
			'type'             => 'video',
			'providerNameSlug' => 'vimeo',
			'responsive'       => true,
			'className'        => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
		];

		$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		return sprintf(
			"<!-- wp:embed %s -->\n" .
			"<figure class=\"wp-block-embed is-type-video is-provider-vimeo wp-block-embed-vimeo wp-embed-aspect-16-9 wp-has-aspect-ratio\">\n" .
			"<div class=\"wp-block-embed__wrapper\">\n" .
			"%s\n" .
			"</div>\n" .
			"</figure>\n" .
			"<!-- /wp:embed -->\n",
			$attrJson,
			$url
		);
	}
}
