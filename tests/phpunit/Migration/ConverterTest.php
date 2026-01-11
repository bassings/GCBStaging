<?php
/**
 * Tests for GCB_To_Block_Converter.
 *
 * TDD Phase 2: RED - These tests will fail until the converter is implemented.
 *
 * @package GCB_Content_Intelligence\Tests\Migration
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Parser classes (already implemented).
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-node.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-parser.php';

// Converter classes (to be implemented).
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/interface-gcb-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/class-gcb-to-block-converter.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-container-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-row-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-column-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-text-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-youtube-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-separator-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-code-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-button-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-image-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-gallery-transformer.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Converter/Transformers/class-gcb-misc-transformer.php';

/**
 * Class ConverterTest
 *
 * Unit tests for the AST to Gutenberg block converter.
 */
class ConverterTest extends TestCase {

	/**
	 * Parser instance.
	 *
	 * @var GCB_Shortcode_Parser
	 */
	private GCB_Shortcode_Parser $parser;

	/**
	 * Converter instance.
	 *
	 * @var GCB_To_Block_Converter
	 */
	private GCB_To_Block_Converter $converter;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->parser    = new GCB_Shortcode_Parser();
		$this->converter = new GCB_To_Block_Converter();

		// Register all transformers.
		$this->converter->registerTransformer( new GCB_Container_Transformer() );
		$this->converter->registerTransformer( new GCB_Row_Transformer() );
		$this->converter->registerTransformer( new GCB_Column_Transformer() );
		$this->converter->registerTransformer( new GCB_Text_Transformer() );
		$this->converter->registerTransformer( new GCB_YouTube_Transformer() );
		$this->converter->registerTransformer( new GCB_Separator_Transformer() );
		$this->converter->registerTransformer( new GCB_Code_Transformer() );
		$this->converter->registerTransformer( new GCB_Button_Transformer() );
		$this->converter->registerTransformer( new GCB_Image_Transformer() );
		$this->converter->registerTransformer( new GCB_Gallery_Transformer() );
		$this->converter->registerTransformer( new GCB_Misc_Transformer() );
	}

	/**
	 * Test converting a simple container to core/group.
	 *
	 * @return void
	 */
	public function test_converts_container_to_group(): void {
		$content = '[fusion_builder_container]Hello World[/fusion_builder_container]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:group', $output );
		$this->assertStringContainsString( '<!-- /wp:group -->', $output );
		$this->assertStringContainsString( 'Hello World', $output );
	}

	/**
	 * Test converting column with width mapping.
	 *
	 * @return void
	 */
	public function test_converts_column_with_width(): void {
		$content = '[fusion_builder_column type="1_2"]Content[/fusion_builder_column]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:column', $output );
		$this->assertStringContainsString( '"width":"50%"', $output );
		$this->assertStringContainsString( '<!-- /wp:column -->', $output );
	}

	/**
	 * Test column width mappings.
	 *
	 * @dataProvider columnWidthProvider
	 * @param string $type     Fusion column type.
	 * @param string $expected Expected Gutenberg width.
	 * @return void
	 */
	public function test_column_width_mappings( string $type, string $expected ): void {
		$content = "[fusion_builder_column type=\"{$type}\"]Content[/fusion_builder_column]";
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( "\"width\":\"{$expected}\"", $output );
	}

	/**
	 * Data provider for column width tests.
	 *
	 * @return array Test cases.
	 */
	public static function columnWidthProvider(): array {
		return [
			'full width'    => [ '1_1', '100%' ],
			'half width'    => [ '1_2', '50%' ],
			'one third'     => [ '1_3', '33.33%' ],
			'two thirds'    => [ '2_3', '66.67%' ],
			'one quarter'   => [ '1_4', '25%' ],
			'three quarter' => [ '3_4', '75%' ],
			'one fifth'     => [ '1_5', '20%' ],
			'two fifths'    => [ '2_5', '40%' ],
			'three fifths'  => [ '3_5', '60%' ],
			'four fifths'   => [ '4_5', '80%' ],
			'one sixth'     => [ '1_6', '16.67%' ],
			'five sixths'   => [ '5_6', '83.33%' ],
		];
	}

	/**
	 * Test converting fusion_text with heading to core/heading.
	 *
	 * @return void
	 */
	public function test_converts_text_heading_to_block(): void {
		$content = '[fusion_text]<h2>My Heading</h2>[/fusion_text]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:heading {"level":2}', $output );
		$this->assertStringContainsString( '<h2 class="wp-block-heading">My Heading</h2>', $output );
		$this->assertStringContainsString( '<!-- /wp:heading -->', $output );
	}

	/**
	 * Test converting fusion_text with paragraph to core/paragraph.
	 *
	 * @return void
	 */
	public function test_converts_text_paragraph_to_block(): void {
		$content = '[fusion_text]<p>This is a paragraph.</p>[/fusion_text]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:paragraph -->', $output );
		$this->assertStringContainsString( '<p>This is a paragraph.</p>', $output );
		$this->assertStringContainsString( '<!-- /wp:paragraph -->', $output );
	}

	/**
	 * Test converting fusion_text with multiple elements.
	 *
	 * @return void
	 */
	public function test_converts_text_with_multiple_elements(): void {
		$content = '[fusion_text]<h2>Heading</h2><p>Paragraph one.</p><p>Paragraph two.</p>[/fusion_text]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:heading {"level":2}', $output );
		$this->assertStringContainsString( 'Heading', $output );

		// Count paragraphs.
		$paragraphCount = substr_count( $output, '<!-- wp:paragraph -->' );
		$this->assertSame( 2, $paragraphCount, 'Should have two paragraph blocks' );
	}

	/**
	 * Test converting fusion_youtube to core/embed.
	 *
	 * @return void
	 */
	public function test_converts_youtube_to_embed(): void {
		$content = '[fusion_youtube id="dQw4w9WgXcQ" width="600" height="350"]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:embed', $output );
		$this->assertStringContainsString( '"providerNameSlug":"youtube"', $output );
		$this->assertStringContainsString( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', $output );
		$this->assertStringContainsString( '<!-- /wp:embed -->', $output );
	}

	/**
	 * Test converting nested structure (container > row > columns).
	 *
	 * @return void
	 */
	public function test_converts_nested_structure(): void {
		$content = <<<'AVADA'
[fusion_builder_container]
[fusion_builder_row]
[fusion_builder_column type="1_2"]
[fusion_text]<h2>Left Column</h2>[/fusion_text]
[/fusion_builder_column]
[fusion_builder_column type="1_2"]
[fusion_youtube id="abc123XYZ00"]
[/fusion_builder_column]
[/fusion_builder_row]
[/fusion_builder_container]
AVADA;

		$ast    = $this->parser->parse( $content );
		$output = $this->converter->convert( $ast );

		// Verify structure.
		$this->assertStringContainsString( '<!-- wp:group', $output );
		$this->assertStringContainsString( '<!-- wp:columns', $output );
		$this->assertStringContainsString( '<!-- wp:column', $output );
		$this->assertStringContainsString( '<!-- wp:heading', $output );
		$this->assertStringContainsString( '<!-- wp:embed', $output );

		// Verify content.
		$this->assertStringContainsString( 'Left Column', $output );
		$this->assertStringContainsString( 'abc123XYZ00', $output );

		// Verify proper nesting (group closes last).
		$groupClose   = strrpos( $output, '<!-- /wp:group -->' );
		$columnsClose = strrpos( $output, '<!-- /wp:columns -->' );
		$this->assertGreaterThan( $columnsClose, $groupClose, 'Group should close after columns' );
	}

	/**
	 * Test plain text passes through unchanged.
	 *
	 * @return void
	 */
	public function test_plain_text_passes_through(): void {
		$content = 'Just plain text, no shortcodes.';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertSame( $content, $output );
	}

	/**
	 * Test mixed content (text before and after shortcodes).
	 *
	 * @return void
	 */
	public function test_mixed_content_preserved(): void {
		$content = '<p>Before</p>[fusion_youtube id="test123"]<p>After</p>';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<p>Before</p>', $output );
		$this->assertStringContainsString( '<!-- wp:embed', $output );
		$this->assertStringContainsString( '<p>After</p>', $output );

		// Verify order.
		$beforePos = strpos( $output, '<p>Before</p>' );
		$embedPos  = strpos( $output, '<!-- wp:embed' );
		$afterPos  = strpos( $output, '<p>After</p>' );

		$this->assertLessThan( $embedPos, $beforePos, 'Before should come before embed' );
		$this->assertLessThan( $afterPos, $embedPos, 'Embed should come before after' );
	}

	/**
	 * Test unknown shortcodes are wrapped in comments.
	 *
	 * @return void
	 */
	public function test_unknown_shortcodes_commented(): void {
		$content = '[fusion_unknown_element param="value"]Some content[/fusion_unknown_element]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- gcb-migration: unknown shortcode', $output );
		$this->assertStringContainsString( 'fusion_unknown_element', $output );
		$this->assertStringContainsString( 'Some content', $output );
	}

	/**
	 * Test heading levels are preserved.
	 *
	 * @dataProvider headingLevelProvider
	 * @param string $tag      HTML heading tag.
	 * @param int    $level    Expected heading level.
	 * @return void
	 */
	public function test_heading_levels_preserved( string $tag, int $level ): void {
		$content = "[fusion_text]<{$tag}>Heading</{$tag}>[/fusion_text]";
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( "\"level\":{$level}", $output );
		$this->assertStringContainsString( "<{$tag} class=\"wp-block-heading\">Heading</{$tag}>", $output );
	}

	/**
	 * Data provider for heading level tests.
	 *
	 * @return array Test cases.
	 */
	public static function headingLevelProvider(): array {
		return [
			'h1' => [ 'h1', 1 ],
			'h2' => [ 'h2', 2 ],
			'h3' => [ 'h3', 3 ],
			'h4' => [ 'h4', 4 ],
			'h5' => [ 'h5', 5 ],
			'h6' => [ 'h6', 6 ],
		];
	}

	/**
	 * Test that inline formatting is preserved in paragraphs.
	 *
	 * @return void
	 */
	public function test_inline_formatting_preserved(): void {
		$content = '[fusion_text]<p>This has <strong>bold</strong> and <em>italic</em> text.</p>[/fusion_text]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<strong>bold</strong>', $output );
		$this->assertStringContainsString( '<em>italic</em>', $output );
	}

	/**
	 * Test links are preserved in text.
	 *
	 * @return void
	 */
	public function test_links_preserved(): void {
		$content = '[fusion_text]<p>Click <a href="https://example.com">here</a> for more.</p>[/fusion_text]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<a href="https://example.com">here</a>', $output );
	}

	/**
	 * Test row converts to columns wrapper.
	 *
	 * @return void
	 */
	public function test_row_converts_to_columns(): void {
		$content = '[fusion_builder_row][fusion_builder_column type="1_2"]A[/fusion_builder_column][fusion_builder_column type="1_2"]B[/fusion_builder_column][/fusion_builder_row]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:columns', $output );
		$this->assertStringContainsString( '<!-- /wp:columns -->', $output );

		// Verify two columns (use space after 'column' to not match 'columns').
		$columnCount = substr_count( $output, '<!-- wp:column {' );
		$this->assertSame( 2, $columnCount, 'Should have two column blocks' );
	}

	/**
	 * Test empty content returns empty string.
	 *
	 * @return void
	 */
	public function test_empty_content(): void {
		$ast    = [];
		$output = $this->converter->convert( $ast );

		$this->assertSame( '', $output );
	}

	/**
	 * Test converting fusion_separator to core/separator.
	 *
	 * @return void
	 */
	public function test_converts_separator_to_block(): void {
		$content = '[fusion_separator style_type="single solid" /]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:separator', $output );
		$this->assertStringContainsString( '<hr class="wp-block-separator', $output );
		$this->assertStringContainsString( '<!-- /wp:separator -->', $output );
	}

	/**
	 * Test separator with different styles.
	 *
	 * @return void
	 */
	public function test_separator_wide_style(): void {
		$content = '[fusion_separator style_type="single solid" sep_color="#333333" /]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:separator', $output );
		$this->assertStringContainsString( 'is-style-wide', $output );
	}

	/**
	 * Test converting fusion_code with base64 content.
	 *
	 * @return void
	 */
	public function test_converts_code_with_base64(): void {
		// Base64 encoded "<p>Hello World</p>"
		$encoded = base64_encode( '<p>Hello World</p>' );
		$content = "[fusion_code]{$encoded}[/fusion_code]";
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:html -->', $output );
		$this->assertStringContainsString( '<p>Hello World</p>', $output );
		$this->assertStringContainsString( '<!-- /wp:html -->', $output );
	}

	/**
	 * Test converting fusion_code with plain HTML.
	 *
	 * @return void
	 */
	public function test_converts_code_with_plain_html(): void {
		$content = '[fusion_code]<div class="custom">Content</div>[/fusion_code]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:html -->', $output );
		$this->assertStringContainsString( '<div class="custom">Content</div>', $output );
	}

	/**
	 * Test converting fusion_button to core/buttons.
	 *
	 * @return void
	 */
	public function test_converts_button_to_block(): void {
		$content = '[fusion_button link="https://example.com" title="Click Me"]Click Me[/fusion_button]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:buttons -->', $output );
		$this->assertStringContainsString( '<!-- wp:button -->', $output );
		$this->assertStringContainsString( 'href="https://example.com"', $output );
		$this->assertStringContainsString( 'Click Me', $output );
		$this->assertStringContainsString( '<!-- /wp:button -->', $output );
		$this->assertStringContainsString( '<!-- /wp:buttons -->', $output );
	}

	/**
	 * Test button with target attribute.
	 *
	 * @return void
	 */
	public function test_button_with_target(): void {
		$content = '[fusion_button link="https://example.com" target="_blank"]External[/fusion_button]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( 'target="_blank"', $output );
		$this->assertStringContainsString( 'rel="noopener"', $output );
	}

	/**
	 * Test converting fusion_imageframe to core/image.
	 *
	 * @return void
	 */
	public function test_converts_imageframe_to_block(): void {
		$content = '[fusion_imageframe image_id="123" style_type="none"]<img src="https://example.com/image.jpg" alt="Test Image" />[/fusion_imageframe]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:image', $output );
		$this->assertStringContainsString( 'src="https://example.com/image.jpg"', $output );
		$this->assertStringContainsString( 'alt="Test Image"', $output );
		$this->assertStringContainsString( '<!-- /wp:image -->', $output );
	}

	/**
	 * Test image with link.
	 *
	 * @return void
	 */
	public function test_image_with_link(): void {
		$content = '[fusion_imageframe link="https://example.com" linktarget="_blank"]<img src="https://example.com/image.jpg" />[/fusion_imageframe]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:image', $output );
		$this->assertStringContainsString( '"linkDestination":"custom"', $output );
		$this->assertStringContainsString( 'href="https://example.com"', $output );
	}

	/**
	 * Test converting fusion_vimeo to core/embed.
	 *
	 * @return void
	 */
	public function test_converts_vimeo_to_embed(): void {
		$content = '[fusion_vimeo id="123456789"]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:embed', $output );
		$this->assertStringContainsString( '"providerNameSlug":"vimeo"', $output );
		$this->assertStringContainsString( 'https://vimeo.com/123456789', $output );
		$this->assertStringContainsString( '<!-- /wp:embed -->', $output );
	}

	/**
	 * Test converting fusion_gallery to Spectra uagb/image-gallery carousel.
	 *
	 * @return void
	 */
	public function test_converts_gallery_to_spectra_carousel(): void {
		$content = '[fusion_gallery][fusion_gallery_image image="https://example.com/img1.jpg" image_id="1"][/fusion_gallery_image][fusion_gallery_image image="https://example.com/img2.jpg" image_id="2"][/fusion_gallery_image][/fusion_gallery]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- wp:uagb/image-gallery', $output );
		$this->assertStringContainsString( '"feedLayout":"carousel"', $output );
		$this->assertStringContainsString( '"mediaGallery":', $output );
		$this->assertStringContainsString( 'img1.jpg', $output );
		$this->assertStringContainsString( 'img2.jpg', $output );
		$this->assertStringContainsString( '/-->', $output ); // Self-closing block.
	}

	/**
	 * Test gallery with columns attribute maps to Spectra columnsDesk.
	 *
	 * @return void
	 */
	public function test_gallery_columns(): void {
		$content = '[fusion_gallery columns="4"][fusion_gallery_image image="https://example.com/img.jpg"][/fusion_gallery_image][/fusion_gallery]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '"columnsDesk":4', $output );
		$this->assertStringContainsString( '"columnsTab":3', $output ); // Capped at 3 for tablet.
		$this->assertStringContainsString( '"columnsMob":1', $output );
	}

	/**
	 * Test empty gallery returns comment.
	 *
	 * @return void
	 */
	public function test_empty_gallery_returns_comment(): void {
		$content = '[fusion_gallery][/fusion_gallery]';
		$ast     = $this->parser->parse( $content );
		$output  = $this->converter->convert( $ast );

		$this->assertStringContainsString( '<!-- gcb-migration: empty gallery -->', $output );
	}
}
