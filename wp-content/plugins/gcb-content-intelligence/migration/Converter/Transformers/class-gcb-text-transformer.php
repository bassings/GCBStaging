<?php
/**
 * Text Transformer.
 *
 * Converts [fusion_text] to core/heading and core/paragraph blocks.
 * Parses inner HTML using DOMDocument for accurate block conversion.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Text_Transformer
 *
 * Transforms Fusion Text blocks to Gutenberg Heading and Paragraph blocks.
 */
final class GCB_Text_Transformer implements GCB_Transformer_Interface {

	/**
	 * @inheritDoc
	 */
	public function getSupportedTags(): array {
		return [ 'fusion_text' ];
	}

	/**
	 * @inheritDoc
	 */
	public function transform( GCB_Shortcode_Node $node, string $childContent ): string {
		// The childContent contains the text from inside the shortcode.
		$html = trim( $childContent );

		if ( '' === $html ) {
			return '';
		}

		return $this->parseHtmlToBlocks( $html );
	}

	/**
	 * Parse HTML content into Gutenberg blocks.
	 *
	 * @param string $html HTML content to parse.
	 * @return string Gutenberg block markup.
	 */
	private function parseHtmlToBlocks( string $html ): string {
		// Suppress DOMDocument warnings for HTML5 elements.
		libxml_use_internal_errors( true );

		$doc = new DOMDocument( '1.0', 'UTF-8' );

		// Wrap in a container to handle multiple root elements.
		$wrappedHtml = '<div id="gcb-wrapper">' . $html . '</div>';

		// Load HTML with UTF-8 encoding.
		$doc->loadHTML(
			'<?xml encoding="UTF-8">' . $wrappedHtml,
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		libxml_clear_errors();

		// Get the wrapper element.
		$wrapper = $doc->getElementById( 'gcb-wrapper' );
		if ( ! $wrapper ) {
			// Fallback: return as paragraph.
			return $this->createParagraphBlock( $html );
		}

		$output = '';

		// Process each child node.
		foreach ( $wrapper->childNodes as $childNode ) {
			$output .= $this->processNode( $childNode, $doc );
		}

		return $output;
	}

	/**
	 * Process a single DOM node.
	 *
	 * @param DOMNode     $domNode DOM node to process.
	 * @param DOMDocument $doc     Parent document.
	 * @return string Block markup.
	 */
	private function processNode( DOMNode $domNode, DOMDocument $doc ): string {
		// Skip whitespace-only text nodes.
		if ( XML_TEXT_NODE === $domNode->nodeType ) {
			$text = trim( $domNode->textContent );
			if ( '' === $text ) {
				return '';
			}
			// Standalone text: wrap in paragraph.
			return $this->createParagraphBlock( $domNode->textContent );
		}

		// Skip non-element nodes.
		if ( XML_ELEMENT_NODE !== $domNode->nodeType ) {
			return '';
		}

		$tagName = strtolower( $domNode->nodeName );

		// Handle headings.
		if ( preg_match( '/^h([1-6])$/', $tagName, $matches ) ) {
			$level   = (int) $matches[1];
			$content = $this->getInnerHtml( $domNode, $doc );
			return $this->createHeadingBlock( $level, $content );
		}

		// Handle paragraphs.
		if ( 'p' === $tagName ) {
			$content = $this->getInnerHtml( $domNode, $doc );
			return $this->createParagraphBlock( $content );
		}

		// Handle unordered lists.
		if ( 'ul' === $tagName ) {
			$content = $this->getOuterHtml( $domNode, $doc );
			return $this->createListBlock( $content, false );
		}

		// Handle ordered lists.
		if ( 'ol' === $tagName ) {
			$content = $this->getOuterHtml( $domNode, $doc );
			return $this->createListBlock( $content, true );
		}

		// Handle blockquotes.
		if ( 'blockquote' === $tagName ) {
			$content = $this->getInnerHtml( $domNode, $doc );
			return $this->createQuoteBlock( $content );
		}

		// Handle divs: recursively process children.
		if ( 'div' === $tagName ) {
			$output = '';
			foreach ( $domNode->childNodes as $child ) {
				$output .= $this->processNode( $child, $doc );
			}
			return $output;
		}

		// Default: wrap in paragraph.
		$content = $this->getOuterHtml( $domNode, $doc );
		return $this->createParagraphBlock( $content );
	}

	/**
	 * Get inner HTML of a DOM element.
	 *
	 * @param DOMNode     $node DOM node.
	 * @param DOMDocument $doc  Parent document.
	 * @return string Inner HTML.
	 */
	private function getInnerHtml( DOMNode $node, DOMDocument $doc ): string {
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $doc->saveHTML( $child );
		}
		return trim( $html );
	}

	/**
	 * Get outer HTML of a DOM element.
	 *
	 * @param DOMNode     $node DOM node.
	 * @param DOMDocument $doc  Parent document.
	 * @return string Outer HTML.
	 */
	private function getOuterHtml( DOMNode $node, DOMDocument $doc ): string {
		return trim( $doc->saveHTML( $node ) );
	}

	/**
	 * Create a heading block.
	 *
	 * @param int    $level   Heading level (1-6).
	 * @param string $content Heading content.
	 * @return string Heading block markup.
	 */
	private function createHeadingBlock( int $level, string $content ): string {
		$attributes = [ 'level' => $level ];
		$attrJson   = json_encode( $attributes, JSON_UNESCAPED_SLASHES );

		return sprintf(
			"<!-- wp:heading %s -->\n" .
			"<h%d class=\"wp-block-heading\">%s</h%d>\n" .
			"<!-- /wp:heading -->\n",
			$attrJson,
			$level,
			$content,
			$level
		);
	}

	/**
	 * Create a paragraph block.
	 *
	 * @param string $content Paragraph content.
	 * @return string Paragraph block markup.
	 */
	private function createParagraphBlock( string $content ): string {
		$content = trim( $content );
		if ( '' === $content ) {
			return '';
		}

		// Check if content is already wrapped in <p> tags.
		if ( preg_match( '/^<p\b[^>]*>.*<\/p>$/is', $content ) ) {
			return sprintf(
				"<!-- wp:paragraph -->\n" .
				"%s\n" .
				"<!-- /wp:paragraph -->\n",
				$content
			);
		}

		return sprintf(
			"<!-- wp:paragraph -->\n" .
			"<p>%s</p>\n" .
			"<!-- /wp:paragraph -->\n",
			$content
		);
	}

	/**
	 * Create a list block.
	 *
	 * @param string $content List HTML content.
	 * @param bool   $ordered Whether list is ordered.
	 * @return string List block markup.
	 */
	private function createListBlock( string $content, bool $ordered ): string {
		$attributes = $ordered ? [ 'ordered' => true ] : [];
		$attrJson   = ! empty( $attributes )
			? json_encode( $attributes, JSON_UNESCAPED_SLASHES ) . ' '
			: '';

		return sprintf(
			"<!-- wp:list %s-->\n" .
			"%s\n" .
			"<!-- /wp:list -->\n",
			$attrJson,
			$content
		);
	}

	/**
	 * Create a quote block.
	 *
	 * @param string $content Quote content.
	 * @return string Quote block markup.
	 */
	private function createQuoteBlock( string $content ): string {
		return sprintf(
			"<!-- wp:quote -->\n" .
			"<blockquote class=\"wp-block-quote\"><p>%s</p></blockquote>\n" .
			"<!-- /wp:quote -->\n",
			$content
		);
	}
}
