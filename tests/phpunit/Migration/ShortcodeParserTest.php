<?php
/**
 * Tests for GCB_Shortcode_Parser.
 *
 * TDD Phase 1: RED - These tests will fail until the parser is implemented.
 *
 * @package GCB_Content_Intelligence\Tests\Migration
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// These classes don't exist yet - this test will FAIL (RED phase).
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-node.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-parser.php';

/**
 * Class ShortcodeParserTest
 *
 * Unit tests for the stack-based Avada shortcode parser.
 */
class ShortcodeParserTest extends TestCase {

	/**
	 * Parser instance.
	 *
	 * @var GCB_Shortcode_Parser
	 */
	private GCB_Shortcode_Parser $parser;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->parser = new GCB_Shortcode_Parser();
	}

	/**
	 * Test parsing deeply nested Avada structure.
	 *
	 * This is the canonical test case: Container > Row > Column > Element.
	 *
	 * @return void
	 */
	public function test_parses_nested_avada_structure(): void {
		$content = <<<'AVADA'
[fusion_builder_container type="flex"]
[fusion_builder_row]
[fusion_builder_column type="1_2"]
[fusion_text]
<h2>Heading</h2>
<p>Paragraph text.</p>
[/fusion_text]
[/fusion_builder_column]
[fusion_builder_column type="1_2"]
[fusion_youtube id="dQw4w9WgXcQ" width="600" height="350"]
[/fusion_builder_column]
[/fusion_builder_row]
[/fusion_builder_container]
AVADA;

		$ast = $this->parser->parse( $content );

		// Root should have one container node.
		$this->assertCount( 1, $ast, 'AST should have exactly one root container' );

		$container = $ast[0];
		$this->assertInstanceOf( GCB_Shortcode_Node::class, $container );
		$this->assertSame( GCB_Shortcode_Node::TYPE_SHORTCODE, $container->type );
		$this->assertSame( 'fusion_builder_container', $container->tag );
		$this->assertSame( 'flex', $container->getAttribute( 'type' ) );

		// Container should have one row child (plus whitespace text nodes).
		$rowNodes = $this->filterShortcodeNodes( $container->children, 'fusion_builder_row' );
		$this->assertCount( 1, $rowNodes, 'Container should have one row' );

		$row = $rowNodes[0];
		$this->assertSame( 'fusion_builder_row', $row->tag );

		// Row should have two column children.
		$columnNodes = $this->filterShortcodeNodes( $row->children, 'fusion_builder_column' );
		$this->assertCount( 2, $columnNodes, 'Row should have two columns' );

		// First column: 1_2 width with fusion_text child.
		$col1 = $columnNodes[0];
		$this->assertSame( '1_2', $col1->getAttribute( 'type' ) );

		$textNodes = $this->filterShortcodeNodes( $col1->children, 'fusion_text' );
		$this->assertCount( 1, $textNodes, 'First column should have fusion_text' );

		$textNode = $textNodes[0];
		// Text node should have HTML content as children or content.
		$this->assertNotEmpty( $textNode->children, 'fusion_text should have content' );

		// Second column: 1_2 width with fusion_youtube (self-closing).
		$col2 = $columnNodes[1];
		$this->assertSame( '1_2', $col2->getAttribute( 'type' ) );

		$youtubeNodes = $this->filterShortcodeNodes( $col2->children, 'fusion_youtube' );
		$this->assertCount( 1, $youtubeNodes, 'Second column should have fusion_youtube' );

		$youtube = $youtubeNodes[0];
		$this->assertSame( 'dQw4w9WgXcQ', $youtube->getAttribute( 'id' ) );
		$this->assertSame( '600', $youtube->getAttribute( 'width' ) );
		$this->assertSame( '350', $youtube->getAttribute( 'height' ) );
	}

	/**
	 * Test parsing self-closing shortcodes.
	 *
	 * @return void
	 */
	public function test_parses_self_closing_shortcode(): void {
		$content = '[fusion_youtube id="abc123XYZ" width="800" height="450" autoplay="false"]';

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );

		$node = $ast[0];
		$this->assertSame( 'fusion_youtube', $node->tag );
		$this->assertSame( 'abc123XYZ', $node->getAttribute( 'id' ) );
		$this->assertSame( '800', $node->getAttribute( 'width' ) );
		$this->assertSame( '450', $node->getAttribute( 'height' ) );
		$this->assertSame( 'false', $node->getAttribute( 'autoplay' ) );
		$this->assertEmpty( $node->children, 'Self-closing shortcode should have no children' );
	}

	/**
	 * Test parsing plain text without shortcodes.
	 *
	 * @return void
	 */
	public function test_parses_plain_text(): void {
		$content = '<p>Just HTML, no shortcodes here.</p>';

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );

		$node = $ast[0];
		$this->assertSame( GCB_Shortcode_Node::TYPE_TEXT, $node->type );
		$this->assertSame( $content, $node->content );
	}

	/**
	 * Test parsing mixed content (text + shortcodes).
	 *
	 * @return void
	 */
	public function test_parses_mixed_content(): void {
		$content = '<p>Before</p>[fusion_youtube id="test123"]<p>After</p>';

		$ast = $this->parser->parse( $content );

		$this->assertCount( 3, $ast, 'Should have 3 nodes: text, shortcode, text' );

		$this->assertSame( GCB_Shortcode_Node::TYPE_TEXT, $ast[0]->type );
		$this->assertSame( '<p>Before</p>', $ast[0]->content );

		$this->assertSame( GCB_Shortcode_Node::TYPE_SHORTCODE, $ast[1]->type );
		$this->assertSame( 'fusion_youtube', $ast[1]->tag );

		$this->assertSame( GCB_Shortcode_Node::TYPE_TEXT, $ast[2]->type );
		$this->assertSame( '<p>After</p>', $ast[2]->content );
	}

	/**
	 * Test handling unclosed shortcodes gracefully.
	 *
	 * @return void
	 */
	public function test_handles_unclosed_shortcodes(): void {
		$content = '[fusion_text]Some text without closing tag';

		$ast = $this->parser->parse( $content );

		// Should still produce valid AST (parser closes unclosed tags at end).
		$this->assertCount( 1, $ast );
		$this->assertSame( 'fusion_text', $ast[0]->tag );

		// The text content should be captured.
		$textChildren = array_filter(
			$ast[0]->children,
			fn( GCB_Shortcode_Node $child ) => GCB_Shortcode_Node::TYPE_TEXT === $child->type
		);
		$this->assertNotEmpty( $textChildren, 'Unclosed shortcode should capture inner text' );
	}

	/**
	 * Test parsing fusion_gallery with carousel layout.
	 *
	 * This is critical for Phase 3: carousel conversion.
	 *
	 * @return void
	 */
	public function test_parses_fusion_gallery_carousel(): void {
		$content = '[fusion_gallery layout="carousel" image_ids="1,2,3,4" columns="4" lightbox="yes"]';

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );

		$gallery = $ast[0];
		$this->assertSame( 'fusion_gallery', $gallery->tag );
		$this->assertSame( 'carousel', $gallery->getAttribute( 'layout' ) );
		$this->assertSame( '1,2,3,4', $gallery->getAttribute( 'image_ids' ) );
		$this->assertSame( '4', $gallery->getAttribute( 'columns' ) );
		$this->assertSame( 'yes', $gallery->getAttribute( 'lightbox' ) );
	}

	/**
	 * Test parsing fusion_code with base64 content.
	 *
	 * @return void
	 */
	public function test_parses_fusion_code_block(): void {
		$htmlContent  = '<table><tr><td>Data</td></tr></table>';
		$base64       = base64_encode( $htmlContent );
		$content      = "[fusion_code]{$base64}[/fusion_code]";

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );

		$codeNode = $ast[0];
		$this->assertSame( 'fusion_code', $codeNode->tag );

		// Content should be captured (will be decoded by transformer).
		$textChildren = array_filter(
			$codeNode->children,
			fn( GCB_Shortcode_Node $child ) => GCB_Shortcode_Node::TYPE_TEXT === $child->type
		);
		$this->assertNotEmpty( $textChildren, 'fusion_code should capture base64 content' );
	}

	/**
	 * Test parsing attributes with special characters.
	 *
	 * @return void
	 */
	public function test_parses_attributes_with_special_chars(): void {
		$content = '[fusion_text class="my-class another-class" id="section-1" style="color: red;"]Content[/fusion_text]';

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );

		$node = $ast[0];
		$this->assertSame( 'my-class another-class', $node->getAttribute( 'class' ) );
		$this->assertSame( 'section-1', $node->getAttribute( 'id' ) );
		$this->assertSame( 'color: red;', $node->getAttribute( 'style' ) );
	}

	/**
	 * Test parsing attributes with single quotes.
	 *
	 * @return void
	 */
	public function test_parses_single_quoted_attributes(): void {
		$content = "[fusion_youtube id='abc123' width='640']";

		$ast = $this->parser->parse( $content );

		$this->assertCount( 1, $ast );
		$this->assertSame( 'abc123', $ast[0]->getAttribute( 'id' ) );
		$this->assertSame( '640', $ast[0]->getAttribute( 'width' ) );
	}

	/**
	 * Test parsing empty content.
	 *
	 * @return void
	 */
	public function test_parses_empty_content(): void {
		$ast = $this->parser->parse( '' );

		$this->assertIsArray( $ast );
		$this->assertEmpty( $ast );
	}

	/**
	 * Test default attribute value.
	 *
	 * @return void
	 */
	public function test_get_attribute_returns_default(): void {
		$content = '[fusion_youtube id="test"]';

		$ast = $this->parser->parse( $content );

		$node = $ast[0];
		$this->assertSame( '', $node->getAttribute( 'nonexistent' ) );
		$this->assertSame( 'default', $node->getAttribute( 'nonexistent', 'default' ) );
	}

	/**
	 * Helper: Filter children to only shortcode nodes with specific tag.
	 *
	 * @param array  $children Child nodes.
	 * @param string $tag      Tag name to filter.
	 * @return array Filtered nodes.
	 */
	private function filterShortcodeNodes( array $children, string $tag ): array {
		return array_values(
			array_filter(
				$children,
				fn( GCB_Shortcode_Node $child ) =>
					$child->isShortcode() && $tag === $child->tag
			)
		);
	}
}
