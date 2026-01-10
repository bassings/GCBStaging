<?php
/**
 * Shortcode AST Node.
 *
 * Represents a node in the parsed shortcode Abstract Syntax Tree.
 * Immutable data class for safe traversal and transformation.
 *
 * @package GCB_Content_Intelligence\Migration\Parser
 */

declare(strict_types=1);

/**
 * Class GCB_Shortcode_Node
 *
 * Immutable data class representing a parsed shortcode or text node.
 */
final class GCB_Shortcode_Node {

	/**
	 * Node type: shortcode (has tag and attributes).
	 */
	public const TYPE_SHORTCODE = 'shortcode';

	/**
	 * Node type: text (raw text/HTML content).
	 */
	public const TYPE_TEXT = 'text';

	/**
	 * Node type (shortcode or text).
	 *
	 * @var string
	 */
	public readonly string $type;

	/**
	 * Shortcode tag name (e.g., 'fusion_builder_container').
	 * Empty string for text nodes.
	 *
	 * @var string
	 */
	public readonly string $tag;

	/**
	 * Shortcode attributes as key-value pairs.
	 * Empty array for text nodes.
	 *
	 * @var array<string, string>
	 */
	public readonly array $attributes;

	/**
	 * Child nodes (for container shortcodes).
	 * Empty array for self-closing shortcodes and text nodes.
	 *
	 * @var array<GCB_Shortcode_Node>
	 */
	public readonly array $children;

	/**
	 * Raw text content (for text nodes).
	 * Empty string for shortcode nodes.
	 *
	 * @var string
	 */
	public readonly string $content;

	/**
	 * Original source position for debugging.
	 *
	 * @var int
	 */
	public readonly int $sourcePosition;

	/**
	 * Constructor.
	 *
	 * @param string $type           Node type (TYPE_SHORTCODE or TYPE_TEXT).
	 * @param string $tag            Shortcode tag name (empty for text nodes).
	 * @param array  $attributes     Shortcode attributes (empty for text nodes).
	 * @param array  $children       Child nodes.
	 * @param string $content        Raw text content (empty for shortcode nodes).
	 * @param int    $sourcePosition Source position in original content.
	 */
	public function __construct(
		string $type,
		string $tag = '',
		array $attributes = [],
		array $children = [],
		string $content = '',
		int $sourcePosition = 0
	) {
		$this->type           = $type;
		$this->tag            = $tag;
		$this->attributes     = $attributes;
		$this->children       = $children;
		$this->content        = $content;
		$this->sourcePosition = $sourcePosition;
	}

	/**
	 * Check if this is a shortcode node.
	 *
	 * @return bool True if shortcode node.
	 */
	public function isShortcode(): bool {
		return self::TYPE_SHORTCODE === $this->type;
	}

	/**
	 * Check if this is a text node.
	 *
	 * @return bool True if text node.
	 */
	public function isText(): bool {
		return self::TYPE_TEXT === $this->type;
	}

	/**
	 * Get attribute value with optional default.
	 *
	 * @param string $key     Attribute key.
	 * @param string $default Default value if attribute doesn't exist.
	 * @return string Attribute value or default.
	 */
	public function getAttribute( string $key, string $default = '' ): string {
		return $this->attributes[ $key ] ?? $default;
	}

	/**
	 * Check if node has a specific attribute.
	 *
	 * @param string $key Attribute key.
	 * @return bool True if attribute exists.
	 */
	public function hasAttribute( string $key ): bool {
		return isset( $this->attributes[ $key ] );
	}

	/**
	 * Create a new node with additional children.
	 *
	 * Since nodes are immutable, this returns a new instance.
	 *
	 * @param array<GCB_Shortcode_Node> $children Children to add.
	 * @return GCB_Shortcode_Node New node with children.
	 */
	public function withChildren( array $children ): GCB_Shortcode_Node {
		return new self(
			$this->type,
			$this->tag,
			$this->attributes,
			$children,
			$this->content,
			$this->sourcePosition
		);
	}

	/**
	 * Create a text node.
	 *
	 * Factory method for convenience.
	 *
	 * @param string $content        Text content.
	 * @param int    $sourcePosition Source position.
	 * @return GCB_Shortcode_Node Text node.
	 */
	public static function createText( string $content, int $sourcePosition = 0 ): GCB_Shortcode_Node {
		return new self(
			self::TYPE_TEXT,
			'',
			[],
			[],
			$content,
			$sourcePosition
		);
	}

	/**
	 * Create a shortcode node.
	 *
	 * Factory method for convenience.
	 *
	 * @param string $tag            Shortcode tag.
	 * @param array  $attributes     Shortcode attributes.
	 * @param array  $children       Child nodes.
	 * @param int    $sourcePosition Source position.
	 * @return GCB_Shortcode_Node Shortcode node.
	 */
	public static function createShortcode(
		string $tag,
		array $attributes = [],
		array $children = [],
		int $sourcePosition = 0
	): GCB_Shortcode_Node {
		return new self(
			self::TYPE_SHORTCODE,
			$tag,
			$attributes,
			$children,
			'',
			$sourcePosition
		);
	}
}
