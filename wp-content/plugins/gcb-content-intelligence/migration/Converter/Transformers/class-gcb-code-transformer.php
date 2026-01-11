<?php
/**
 * Code Transformer.
 *
 * Converts [fusion_code] to proper Gutenberg blocks.
 * Parses HTML content and converts to heading, paragraph, table blocks.
 * Handles base64 encoded content from legacy Avada.
 *
 * @package GCB_Content_Intelligence\Migration\Converter\Transformers
 */

declare(strict_types=1);

/**
 * Class GCB_Code_Transformer
 *
 * Transforms Fusion Code shortcodes to Gutenberg blocks.
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

		// Parse HTML and convert to proper Gutenberg blocks.
		return $this->parseHtmlToBlocks( $content );
	}

	/**
	 * Parse HTML content and convert to Gutenberg blocks.
	 *
	 * @param string $html HTML content to parse.
	 * @return string Gutenberg block markup.
	 */
	private function parseHtmlToBlocks( string $html ): string {
		// Remove style tags - they're not needed in block editor.
		$html = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $html );

		// Suppress DOMDocument warnings for HTML5 elements.
		libxml_use_internal_errors( true );

		$doc = new DOMDocument( '1.0', 'UTF-8' );

		// Wrap in container to handle multiple root elements.
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
			// Fallback: return as HTML block.
			return $this->createHtmlBlock( $html );
		}

		$output = '';

		foreach ( $wrapper->childNodes as $node ) {
			$output .= $this->convertNode( $node, $doc );
		}

		return $output;
	}

	/**
	 * Convert a DOM node to Gutenberg block.
	 *
	 * @param DOMNode     $node DOM node to convert.
	 * @param DOMDocument $doc  Parent document.
	 * @return string Block markup.
	 */
	private function convertNode( DOMNode $node, DOMDocument $doc ): string {
		// Skip text nodes that are just whitespace.
		if ( XML_TEXT_NODE === $node->nodeType ) {
			$text = trim( $node->textContent );
			if ( '' === $text ) {
				return '';
			}
			return $this->createParagraphBlock( $text );
		}

		// Skip non-element nodes.
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			return '';
		}

		$tagName = strtolower( $node->nodeName );

		// Handle headings.
		if ( preg_match( '/^h([1-6])$/', $tagName, $matches ) ) {
			$level   = (int) $matches[1];
			$content = $this->getInnerHtml( $node, $doc );
			return $this->createHeadingBlock( $level, $content );
		}

		// Handle paragraphs.
		if ( 'p' === $tagName ) {
			$content = $this->getInnerHtml( $node, $doc );
			return $this->createParagraphBlock( $content );
		}

		// Handle tables.
		if ( 'table' === $tagName ) {
			return $this->createTableBlock( $node, $doc );
		}

		// Handle lists.
		if ( 'ul' === $tagName ) {
			return $this->createListBlock( $node, $doc, false );
		}

		if ( 'ol' === $tagName ) {
			return $this->createListBlock( $node, $doc, true );
		}

		// Handle divs - recurse into children.
		if ( 'div' === $tagName ) {
			$output = '';
			foreach ( $node->childNodes as $child ) {
				$output .= $this->convertNode( $child, $doc );
			}
			return $output;
		}

		// Skip style, script, etc.
		if ( in_array( $tagName, [ 'style', 'script', 'meta', 'link' ], true ) ) {
			return '';
		}

		// Default: wrap unknown elements in HTML block.
		$content = $this->getOuterHtml( $node, $doc );
		if ( '' !== trim( $content ) ) {
			return $this->createHtmlBlock( $content );
		}

		return '';
	}

	/**
	 * Create a heading block.
	 *
	 * @param int    $level   Heading level (1-6).
	 * @param string $content Heading content.
	 * @return string Heading block markup.
	 */
	private function createHeadingBlock( int $level, string $content ): string {
		$content = trim( $content );
		if ( '' === $content ) {
			return '';
		}

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

		return sprintf(
			"<!-- wp:paragraph -->\n" .
			"<p>%s</p>\n" .
			"<!-- /wp:paragraph -->\n",
			$content
		);
	}

	/**
	 * Create a table block.
	 *
	 * @param DOMNode     $tableNode Table DOM node.
	 * @param DOMDocument $doc       Parent document.
	 * @return string Table block markup.
	 */
	private function createTableBlock( DOMNode $tableNode, DOMDocument $doc ): string {
		$hasHeader = false;
		$hasFooter = false;

		// Check for thead/tfoot.
		foreach ( $tableNode->childNodes as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType ) {
				$tag = strtolower( $child->nodeName );
				if ( 'thead' === $tag ) {
					$hasHeader = true;
				}
				if ( 'tfoot' === $tag ) {
					$hasFooter = true;
				}
			}
		}

		// Build clean table HTML without classes/styles.
		$tableHtml = $this->buildCleanTableHtml( $tableNode, $doc );

		$attributes = [];
		if ( $hasHeader ) {
			$attributes['hasFixedLayout'] = false;
		}

		$attrJson = ! empty( $attributes )
			? ' ' . json_encode( $attributes, JSON_UNESCAPED_SLASHES )
			: '';

		return sprintf(
			"<!-- wp:table%s -->\n" .
			"<figure class=\"wp-block-table\">%s</figure>\n" .
			"<!-- /wp:table -->\n",
			$attrJson,
			$tableHtml
		);
	}

	/**
	 * Build clean table HTML without inline styles.
	 *
	 * @param DOMNode     $tableNode Table node.
	 * @param DOMDocument $doc       Parent document.
	 * @return string Clean table HTML.
	 */
	private function buildCleanTableHtml( DOMNode $tableNode, DOMDocument $doc ): string {
		$output = '<table>';

		foreach ( $tableNode->childNodes as $child ) {
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			$tag = strtolower( $child->nodeName );

			if ( 'thead' === $tag ) {
				$output .= '<thead>';
				$output .= $this->buildTableRows( $child, $doc, 'th' );
				$output .= '</thead>';
			} elseif ( 'tbody' === $tag ) {
				$output .= '<tbody>';
				$output .= $this->buildTableRows( $child, $doc, 'td' );
				$output .= '</tbody>';
			} elseif ( 'tfoot' === $tag ) {
				$output .= '<tfoot>';
				$output .= $this->buildTableRows( $child, $doc, 'td' );
				$output .= '</tfoot>';
			} elseif ( 'tr' === $tag ) {
				// Direct tr children (no thead/tbody wrapper).
				$output .= '<tbody>';
				$output .= $this->buildTableRow( $child, $doc );
				$output .= '</tbody>';
			}
		}

		$output .= '</table>';
		return $output;
	}

	/**
	 * Build table rows HTML.
	 *
	 * @param DOMNode     $sectionNode Section node (thead/tbody/tfoot).
	 * @param DOMDocument $doc         Parent document.
	 * @param string      $defaultCell Default cell type (th/td).
	 * @return string Rows HTML.
	 */
	private function buildTableRows( DOMNode $sectionNode, DOMDocument $doc, string $defaultCell = 'td' ): string {
		$output = '';

		foreach ( $sectionNode->childNodes as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType && 'tr' === strtolower( $child->nodeName ) ) {
				$output .= $this->buildTableRow( $child, $doc );
			}
		}

		return $output;
	}

	/**
	 * Build single table row HTML.
	 *
	 * @param DOMNode     $rowNode Row node.
	 * @param DOMDocument $doc     Parent document.
	 * @return string Row HTML.
	 */
	private function buildTableRow( DOMNode $rowNode, DOMDocument $doc ): string {
		$output = '<tr>';

		foreach ( $rowNode->childNodes as $cell ) {
			if ( XML_ELEMENT_NODE !== $cell->nodeType ) {
				continue;
			}

			$cellTag = strtolower( $cell->nodeName );
			if ( in_array( $cellTag, [ 'th', 'td' ], true ) ) {
				$content = $this->getInnerHtml( $cell, $doc );
				$output .= sprintf( '<%s>%s</%s>', $cellTag, $content, $cellTag );
			}
		}

		$output .= '</tr>';
		return $output;
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
	 * Create an HTML block for content that can't be converted.
	 *
	 * @param string $content HTML content.
	 * @return string HTML block markup.
	 */
	private function createHtmlBlock( string $content ): string {
		return sprintf(
			"<!-- wp:html -->\n%s\n<!-- /wp:html -->\n",
			$content
		);
	}

	/**
	 * Get inner HTML of a DOM node.
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
	 * Get outer HTML of a DOM node.
	 *
	 * @param DOMNode     $node DOM node.
	 * @param DOMDocument $doc  Parent document.
	 * @return string Outer HTML.
	 */
	private function getOuterHtml( DOMNode $node, DOMDocument $doc ): string {
		return trim( $doc->saveHTML( $node ) );
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
