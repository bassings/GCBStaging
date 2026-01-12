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
	 * Inline element tags that should be combined into paragraphs.
	 *
	 * @var array
	 */
	private const INLINE_TAGS = [
		'strong', 'b', 'em', 'i', 'a', 'span', 'code', 'mark', 'small',
		'sub', 'sup', 'u', 's', 'strike', 'del', 'ins', 'abbr', 'cite', 'br',
	];

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

		// Collect and process nodes, combining inline content.
		$output = $this->processChildNodes( $wrapper, $doc );

		return $output;
	}

	/**
	 * Process child nodes, combining consecutive inline content into paragraphs.
	 *
	 * @param DOMNode     $parent Parent node.
	 * @param DOMDocument $doc    Parent document.
	 * @return string Block markup.
	 */
	private function processChildNodes( DOMNode $parent, DOMDocument $doc ): string {
		$output        = '';
		$inlineBuffer  = '';

		foreach ( $parent->childNodes as $childNode ) {
			if ( $this->isInlineContent( $childNode ) ) {
				// Accumulate inline content.
				$inlineBuffer .= $doc->saveHTML( $childNode );
			} else {
				// Flush inline buffer before processing block element.
				if ( '' !== trim( $inlineBuffer ) ) {
					$output       .= $this->createParagraphsFromText( $inlineBuffer );
					$inlineBuffer  = '';
				}

				// Process block-level node.
				$output .= $this->processBlockNode( $childNode, $doc );
			}
		}

		// Flush any remaining inline content.
		if ( '' !== trim( $inlineBuffer ) ) {
			$output .= $this->createParagraphsFromText( $inlineBuffer );
		}

		return $output;
	}

	/**
	 * Check if a node is inline content (text or inline element).
	 *
	 * @param DOMNode $node Node to check.
	 * @return bool True if inline content.
	 */
	private function isInlineContent( DOMNode $node ): bool {
		// Text nodes are inline.
		if ( XML_TEXT_NODE === $node->nodeType ) {
			return true;
		}

		// Check if element is an inline tag.
		if ( XML_ELEMENT_NODE === $node->nodeType ) {
			$tagName = strtolower( $node->nodeName );
			return in_array( $tagName, self::INLINE_TAGS, true );
		}

		return false;
	}

	/**
	 * Create paragraph blocks from text that may contain line breaks.
	 *
	 * @param string $text Text content (may contain inline HTML).
	 * @return string Paragraph block(s).
	 */
	private function createParagraphsFromText( string $text ): string {
		// Split by double line breaks (paragraph separator).
		$paragraphs = preg_split( '/\n\s*\n/', $text );
		$output     = '';

		foreach ( $paragraphs as $para ) {
			$para = trim( $para );
			if ( '' !== $para ) {
				// Replace single newlines with spaces.
				$para    = preg_replace( '/\s*\n\s*/', ' ', $para );
				$output .= $this->createParagraphBlock( $para );
			}
		}

		return $output;
	}

	/**
	 * Process a block-level DOM node.
	 *
	 * @param DOMNode     $domNode DOM node to process.
	 * @param DOMDocument $doc     Parent document.
	 * @return string Block markup.
	 */
	private function processBlockNode( DOMNode $domNode, DOMDocument $doc ): string {
		// Skip non-element nodes (text is handled by inline processing).
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
			return $this->createListBlock( $domNode, $doc, false );
		}

		// Handle ordered lists.
		if ( 'ol' === $tagName ) {
			return $this->createListBlock( $domNode, $doc, true );
		}

		// Handle blockquotes.
		if ( 'blockquote' === $tagName ) {
			$content = $this->getInnerHtml( $domNode, $doc );
			return $this->createQuoteBlock( $content );
		}

		// Handle divs: recursively process children using inline-aware method.
		if ( 'div' === $tagName ) {
			return $this->processChildNodes( $domNode, $doc );
		}

		// Handle br tags: they're part of inline content, skip here.
		if ( 'br' === $tagName ) {
			return '';
		}

		// Handle tables: convert to proper wp:table block.
		if ( 'table' === $tagName ) {
			return $this->createTableBlock( $domNode, $doc );
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
	 * Create a list block with proper wp:list-item inner blocks.
	 *
	 * WordPress 6.x requires each list item to be wrapped in wp:list-item blocks.
	 *
	 * @param DOMNode     $listNode DOM node of the list (ul/ol).
	 * @param DOMDocument $doc      Parent document.
	 * @param bool        $ordered  Whether list is ordered.
	 * @return string List block markup.
	 */
	private function createListBlock( DOMNode $listNode, DOMDocument $doc, bool $ordered ): string {
		$attributes = $ordered ? [ 'ordered' => true ] : [];
		$attrJson   = ! empty( $attributes )
			? json_encode( $attributes, JSON_UNESCAPED_SLASHES ) . ' '
			: '';

		$listTag = $ordered ? 'ol' : 'ul';

		// Build list items with wp:list-item blocks.
		$itemsHtml = '';
		foreach ( $listNode->childNodes as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType && 'li' === strtolower( $child->nodeName ) ) {
				$itemContent = $this->getInnerHtml( $child, $doc );
				$itemsHtml  .= sprintf(
					"<!-- wp:list-item -->\n<li>%s</li>\n<!-- /wp:list-item -->\n",
					$itemContent
				);
			}
		}

		if ( '' === $itemsHtml ) {
			return '';
		}

		return sprintf(
			"<!-- wp:list %s-->\n" .
			"<%s class=\"wp-block-list\">%s</%s>\n" .
			"<!-- /wp:list -->\n",
			$attrJson,
			$listTag,
			$itemsHtml,
			$listTag
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

	/**
	 * Create a proper Gutenberg table block from a DOM table element.
	 *
	 * @param DOMNode     $tableNode DOM node of the table.
	 * @param DOMDocument $doc       Parent document.
	 * @return string Table block markup.
	 */
	private function createTableBlock( DOMNode $tableNode, DOMDocument $doc ): string {
		$thead = '';
		$tbody = '';
		$isFirstRow = true;

		// Find tbody or direct tr children.
		$tbodyNode = null;
		foreach ( $tableNode->childNodes as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType && 'tbody' === strtolower( $child->nodeName ) ) {
				$tbodyNode = $child;
				break;
			}
		}

		$rowsContainer = $tbodyNode ?? $tableNode;

		foreach ( $rowsContainer->childNodes as $row ) {
			if ( XML_ELEMENT_NODE !== $row->nodeType || 'tr' !== strtolower( $row->nodeName ) ) {
				continue;
			}

			$cells = '';
			foreach ( $row->childNodes as $cell ) {
				if ( XML_ELEMENT_NODE !== $cell->nodeType ) {
					continue;
				}

				$cellTag = strtolower( $cell->nodeName );
				if ( 'td' !== $cellTag && 'th' !== $cellTag ) {
					continue;
				}

				$cellContent = trim( $this->getInnerHtml( $cell, $doc ) );
				// Clean up whitespace.
				$cellContent = preg_replace( '/\s+/', ' ', $cellContent );

				if ( $isFirstRow ) {
					// First row becomes header with <th> tags.
					$cells .= '<th>' . $cellContent . '</th>';
				} else {
					$cells .= '<td>' . $cellContent . '</td>';
				}
			}

			if ( $isFirstRow ) {
				$thead = '<tr>' . $cells . '</tr>';
				$isFirstRow = false;
			} else {
				$tbody .= '<tr>' . $cells . '</tr>';
			}
		}

		// Build the table block.
		$tableHtml = '<figure class="wp-block-table"><table class="has-fixed-layout">';

		if ( '' !== $thead ) {
			$tableHtml .= '<thead>' . $thead . '</thead>';
		}

		if ( '' !== $tbody ) {
			$tableHtml .= '<tbody>' . $tbody . '</tbody>';
		}

		$tableHtml .= '</table></figure>';

		return "<!-- wp:table -->\n" . $tableHtml . "\n<!-- /wp:table -->\n";
	}

	/**
	 * Create an HTML block for content that can't be in paragraphs.
	 *
	 * @param string $content HTML content.
	 * @return string HTML block markup.
	 */
	private function createHtmlBlock( string $content ): string {
		return sprintf(
			"<!-- wp:html -->\n" .
			"%s\n" .
			"<!-- /wp:html -->\n",
			$content
		);
	}
}
