<?php
/**
 * Stack-Based Shortcode Parser.
 *
 * Parses Avada Fusion Builder shortcodes into an Abstract Syntax Tree (AST).
 * Uses character-by-character scanning with a stack for nested structures.
 * Does NOT use regex for structure parsing (only for attribute extraction).
 *
 * @package GCB_Content_Intelligence\Migration\Parser
 */

declare(strict_types=1);

/**
 * Class GCB_Shortcode_Parser
 *
 * Stack-based parser for Avada Fusion Builder shortcodes.
 */
final class GCB_Shortcode_Parser {

	/**
	 * Known self-closing shortcode tags (no closing tag expected).
	 *
	 * NOTE: fusion_imageframe is NOT self-closing because it often contains
	 * the image URL as inner content: [fusion_imageframe ...]URL[/fusion_imageframe]
	 *
	 * @var array<string, bool>
	 */
	private const SELF_CLOSING_TAGS = [
		'fusion_youtube'       => true,
		'fusion_vimeo'         => true,
		'fusion_separator'     => true,
		'fusion_button'        => true,
		'fusion_fontawesome'   => true,
		'fusion_social_links'  => true,
		'fusion_sharing'       => true,
		'fusion_person'        => true,
	];

	/**
	 * Parse content into an AST.
	 *
	 * @param string $content Post content with Fusion Builder shortcodes.
	 * @return array<GCB_Shortcode_Node> Array of root-level AST nodes.
	 */
	public function parse( string $content ): array {
		if ( '' === $content ) {
			return [];
		}

		$nodes    = [];
		$stack    = [];
		$position = 0;
		$length   = strlen( $content );
		$buffer   = '';

		while ( $position < $length ) {
			$char = $content[ $position ];

			// Check for shortcode start.
			if ( '[' === $char ) {
				// Look ahead to see if this is a valid shortcode.
				$shortcodeData = $this->extractShortcode( $content, $position );

				if ( null !== $shortcodeData ) {
					// Flush text buffer before processing shortcode.
					if ( '' !== $buffer ) {
						$textNode = GCB_Shortcode_Node::createText(
							$buffer,
							$position - strlen( $buffer )
						);
						$this->appendNode( $nodes, $stack, $textNode );
						$buffer = '';
					}

					if ( $shortcodeData['isClosing'] ) {
						// Closing tag: Pop from stack and finalize.
						$this->closeShortcode( $nodes, $stack, $shortcodeData['tag'] );
					} else {
						// Opening or self-closing tag.
						$node = GCB_Shortcode_Node::createShortcode(
							$shortcodeData['tag'],
							$shortcodeData['attributes'],
							[],
							$shortcodeData['startPosition']
						);

						if ( $this->isSelfClosing( $shortcodeData['tag'] ) || $shortcodeData['isSelfClosingBySyntax'] ) {
							// Self-closing: Add directly, don't push to stack.
							$this->appendNode( $nodes, $stack, $node );
						} else {
							// Container: Push to stack for nesting.
							$stack[] = [
								'node'     => $node,
								'children' => [],
							];
						}
					}

					// Move position past the shortcode.
					$position = $shortcodeData['endPosition'];
					continue;
				}
			}

			// Regular character: Add to buffer.
			$buffer .= $char;
			++$position;
		}

		// Flush remaining text buffer.
		if ( '' !== $buffer ) {
			$textNode = GCB_Shortcode_Node::createText(
				$buffer,
				$position - strlen( $buffer )
			);
			$this->appendNode( $nodes, $stack, $textNode );
		}

		// Close any unclosed shortcodes (graceful degradation).
		while ( ! empty( $stack ) ) {
			$this->closeShortcode( $nodes, $stack, null );
		}

		return $nodes;
	}

	/**
	 * Extract shortcode data starting at position.
	 *
	 * Scans character-by-character to find the complete shortcode.
	 *
	 * @param string $content  Full content string.
	 * @param int    $position Starting position (at '[').
	 * @return array|null Shortcode data or null if not a valid shortcode.
	 */
	private function extractShortcode( string $content, int $position ): ?array {
		$length        = strlen( $content );
		$startPosition = $position;

		// Must start with '['.
		if ( '[' !== ( $content[ $position ] ?? '' ) ) {
			return null;
		}

		// Find the closing ']'.
		$bracketEnd = strpos( $content, ']', $position );
		if ( false === $bracketEnd ) {
			return null;
		}

		// Extract the shortcode content between brackets.
		$shortcodeContent = substr( $content, $position + 1, $bracketEnd - $position - 1 );

		// Check for closing tag.
		$isClosing = false;
		if ( str_starts_with( $shortcodeContent, '/' ) ) {
			$isClosing        = true;
			$shortcodeContent = substr( $shortcodeContent, 1 );
		}

		// Check for self-closing syntax: [tag ... /] (ends with space and /).
		$isSelfClosingBySyntax = false;
		if ( ! $isClosing && str_ends_with( $shortcodeContent, ' /' ) ) {
			$isSelfClosingBySyntax = true;
			$shortcodeContent      = substr( $shortcodeContent, 0, -2 );
		}

		// Parse tag name (first word).
		$spacePos = strpos( $shortcodeContent, ' ' );
		if ( false !== $spacePos ) {
			$tag            = substr( $shortcodeContent, 0, $spacePos );
			$attributesPart = substr( $shortcodeContent, $spacePos + 1 );
		} else {
			$tag            = $shortcodeContent;
			$attributesPart = '';
		}

		// Validate tag name (must be alphanumeric with underscores).
		$tag = trim( $tag );
		if ( '' === $tag || ! preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tag ) ) {
			return null;
		}

		// Only process fusion_* shortcodes.
		if ( ! str_starts_with( $tag, 'fusion_' ) ) {
			return null;
		}

		// Parse attributes for opening tags.
		$attributes = [];
		if ( ! $isClosing && '' !== $attributesPart ) {
			$attributes = $this->parseAttributes( $attributesPart );
		}

		return [
			'tag'                   => $tag,
			'attributes'            => $attributes,
			'isClosing'             => $isClosing,
			'isSelfClosingBySyntax' => $isSelfClosingBySyntax,
			'startPosition'         => $startPosition,
			'endPosition'           => $bracketEnd + 1,
		];
	}

	/**
	 * Parse shortcode attributes.
	 *
	 * Handles: key="value", key='value', key=value, key
	 *
	 * @param string $attributeString Attribute string to parse.
	 * @return array<string, string> Parsed attributes.
	 */
	private function parseAttributes( string $attributeString ): array {
		$attributes = [];
		$pattern    = '/
			(\w+)              # Attribute name
			\s*=\s*            # Equals sign with optional whitespace
			(?:
				"([^"]*)"      # Double-quoted value
				|
				\'([^\']*)\'   # Single-quoted value
				|
				(\S+)          # Unquoted value
			)
		/x';

		if ( preg_match_all( $pattern, $attributeString, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$name = $match[1];
				// Value is in one of: $match[2] (double), $match[3] (single), $match[4] (unquoted).
				// Check each group - use first non-empty match.
				$value = '';
				if ( isset( $match[2] ) && '' !== $match[2] ) {
					$value = $match[2];
				} elseif ( isset( $match[3] ) && '' !== $match[3] ) {
					$value = $match[3];
				} elseif ( isset( $match[4] ) && '' !== $match[4] ) {
					$value = $match[4];
				}
				$attributes[ $name ] = $value;
			}
		}

		return $attributes;
	}

	/**
	 * Check if a shortcode tag is self-closing.
	 *
	 * @param string $tag Shortcode tag name.
	 * @return bool True if self-closing.
	 */
	private function isSelfClosing( string $tag ): bool {
		return isset( self::SELF_CLOSING_TAGS[ $tag ] );
	}

	/**
	 * Append a node to the current context.
	 *
	 * If stack is empty, appends to root nodes.
	 * If stack has items, appends to the top item's children.
	 *
	 * @param array             $nodes Root nodes array (by reference).
	 * @param array             $stack Stack of open shortcodes (by reference).
	 * @param GCB_Shortcode_Node $node  Node to append.
	 * @return void
	 */
	private function appendNode( array &$nodes, array &$stack, GCB_Shortcode_Node $node ): void {
		if ( empty( $stack ) ) {
			$nodes[] = $node;
		} else {
			$stack[ count( $stack ) - 1 ]['children'][] = $node;
		}
	}

	/**
	 * Close a shortcode by popping from stack.
	 *
	 * Creates the final node with collected children and appends
	 * to parent context.
	 *
	 * @param array       $nodes Root nodes array (by reference).
	 * @param array       $stack Stack of open shortcodes (by reference).
	 * @param string|null $tag   Expected closing tag (null for forced close).
	 * @return void
	 */
	private function closeShortcode( array &$nodes, array &$stack, ?string $tag ): void {
		if ( empty( $stack ) ) {
			return;
		}

		// Pop the top of the stack.
		$stackItem = array_pop( $stack );
		$openNode  = $stackItem['node'];
		$children  = $stackItem['children'];

		// Validate tag match (if tag provided).
		if ( null !== $tag && $openNode->tag !== $tag ) {
			// Tag mismatch - could be malformed HTML.
			// For now, we still close but this could be logged.
		}

		// Create final node with children.
		$finalNode = $openNode->withChildren( $children );

		// Append to parent context.
		$this->appendNode( $nodes, $stack, $finalNode );
	}
}
