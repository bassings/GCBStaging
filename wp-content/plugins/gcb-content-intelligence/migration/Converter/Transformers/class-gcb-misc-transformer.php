<?php
/**
 * Misc Transformer.
 *
 * Handles miscellaneous Fusion shortcodes like fusion_global and fusion_audio.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Misc_Transformer
 *
 * Transforms miscellaneous Fusion shortcodes to appropriate Gutenberg blocks.
 */
final class GCB_Misc_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [
			'fusion_global',
			'fusion_audio',
			'fusion_soundcloud',
			'fusion_alert',
			'fusion_accordion',
			'fusion_toggle',
			'fusion_tabs',
			'fusion_tab',
			'fusion_pricing_table',
			'fusion_testimonial',
			'fusion_testimonials',
			'fusion_person',
			'fusion_social_links',
			'fusion_sharing',
			'fusion_title',
			'fusion_breadcrumbs',
			'fusion_menu',
			'fusion_checklist',
			'fusion_li_item',
			'fusion_countdown',
			'fusion_counters_circle',
			'fusion_counter_circle',
			'fusion_counters_box',
			'fusion_counter_box',
			'fusion_progress',
			'fusion_modal',
			'fusion_modal_text_link',
			'fusion_popover',
			'fusion_tooltip',
			'fusion_highlight',
			'fusion_dropcap',
			'fusion_flip_box',
			'fusion_content_box',
			'fusion_content_boxes',
			'fusion_fontawesome',
			'fusion_map',
			'fusion_section_separator',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		switch ( $node->tag ) {
			case 'fusion_audio':
			case 'fusion_soundcloud':
				return $this->transformAudio( $node, $childContent );

			case 'fusion_global':
				// Global elements are reusable blocks - pass through content.
				return $childContent;

			case 'fusion_alert':
				return $this->transformAlert( $node, $childContent );

			case 'fusion_title':
				return $this->transformTitle( $node, $childContent );

			case 'fusion_checklist':
				return $this->transformChecklist( $node, $childContent );

			case 'fusion_li_item':
				return $this->transformListItem( $node, $childContent );

			case 'fusion_accordion':
			case 'fusion_toggle':
			case 'fusion_tabs':
			case 'fusion_tab':
				// Complex interactive elements - wrap in group with content.
				return $this->wrapInGroup( $node, $childContent );

			default:
				// For other elements, just pass through child content.
				if ( '' !== trim( $childContent ) ) {
					return $childContent;
				}
				// Empty - return nothing.
				return '';
		}
	}

	/**
	 * Transform audio shortcode to embed block.
	 *
	 * @param GCB_Shortcode_Node $node         The audio node.
	 * @param string             $childContent Child content.
	 * @return string Audio/embed block.
	 */
	private function transformAudio( GCB_Shortcode_Node $node, string $childContent ): string {
		$url = $node->getAttribute( 'url' );
		$src = $node->getAttribute( 'src' );

		$audioUrl = '' !== $url ? $url : $src;

		if ( '' === $audioUrl ) {
			return "<!-- gcb-migration: audio missing url -->\n";
		}

		// Check if it's SoundCloud.
		if ( str_contains( $audioUrl, 'soundcloud.com' ) ) {
			$attributes = [
				'url'              => $audioUrl,
				'type'             => 'rich',
				'providerNameSlug' => 'soundcloud',
			];

			$attrJson = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

			return sprintf(
				"<!-- wp:embed %s -->\n" .
				"<figure class=\"wp-block-embed is-type-rich is-provider-soundcloud wp-block-embed-soundcloud\">\n" .
				"<div class=\"wp-block-embed__wrapper\">\n%s\n</div>\n" .
				"</figure>\n" .
				"<!-- /wp:embed -->\n",
				$attrJson,
				htmlspecialchars( $audioUrl, ENT_QUOTES, 'UTF-8' )
			);
		}

		// Regular audio file.
		return sprintf(
			"<!-- wp:audio -->\n" .
			"<figure class=\"wp-block-audio\"><audio controls src=\"%s\"></audio></figure>\n" .
			"<!-- /wp:audio -->\n",
			htmlspecialchars( $audioUrl, ENT_QUOTES, 'UTF-8' )
		);
	}

	/**
	 * Transform alert to paragraph with styling.
	 *
	 * @param GCB_Shortcode_Node $node         The alert node.
	 * @param string             $childContent Child content.
	 * @return string Paragraph block.
	 */
	private function transformAlert( GCB_Shortcode_Node $node, string $childContent ): string {
		$content = '' !== $childContent ? $childContent : $node->content;

		if ( '' === trim( $content ) ) {
			return '';
		}

		return sprintf(
			"<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->\n",
			$content
		);
	}

	/**
	 * Transform title to heading.
	 *
	 * @param GCB_Shortcode_Node $node         The title node.
	 * @param string             $childContent Child content.
	 * @return string Heading block.
	 */
	private function transformTitle( GCB_Shortcode_Node $node, string $childContent ): string {
		$content = '' !== $childContent ? $childContent : $node->getAttribute( 'title' );
		$size    = $node->getAttribute( 'size' );

		// Map size to heading level.
		$level = 2;
		if ( '' !== $size ) {
			$sizeNum = (int) filter_var( $size, FILTER_SANITIZE_NUMBER_INT );
			if ( $sizeNum >= 1 && $sizeNum <= 6 ) {
				$level = $sizeNum;
			}
		}

		if ( '' === trim( $content ) ) {
			return '';
		}

		return sprintf(
			"<!-- wp:heading {\"level\":%d} -->\n<h%d class=\"wp-block-heading\">%s</h%d>\n<!-- /wp:heading -->\n",
			$level,
			$level,
			$content,
			$level
		);
	}

	/**
	 * Transform checklist to list block.
	 *
	 * @param GCB_Shortcode_Node $node         The checklist node.
	 * @param string             $childContent Child content.
	 * @return string List block.
	 */
	private function transformChecklist( GCB_Shortcode_Node $node, string $childContent ): string {
		if ( '' === trim( $childContent ) ) {
			return '';
		}

		return sprintf(
			"<!-- wp:list -->\n<ul>%s</ul>\n<!-- /wp:list -->\n",
			$childContent
		);
	}

	/**
	 * Transform list item.
	 *
	 * @param GCB_Shortcode_Node $node         The list item node.
	 * @param string             $childContent Child content.
	 * @return string List item.
	 */
	private function transformListItem( GCB_Shortcode_Node $node, string $childContent ): string {
		$content = '' !== $childContent ? $childContent : $node->getAttribute( 'icon' );

		if ( '' === trim( $content ) ) {
			return '';
		}

		return sprintf( "<li>%s</li>\n", $content );
	}

	/**
	 * Wrap content in a group block.
	 *
	 * @param GCB_Shortcode_Node $node         The node.
	 * @param string             $childContent Child content.
	 * @return string Group block.
	 */
	private function wrapInGroup( GCB_Shortcode_Node $node, string $childContent ): string {
		if ( '' === trim( $childContent ) ) {
			return '';
		}

		return sprintf(
			"<!-- wp:group -->\n<div class=\"wp-block-group\">%s</div>\n<!-- /wp:group -->\n",
			$childContent
		);
	}
}
