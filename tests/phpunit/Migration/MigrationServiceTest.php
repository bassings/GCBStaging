<?php
/**
 * Tests for GCB_Migration_Service.
 *
 * Tests the core migration logic that powers the WP-CLI command.
 *
 * @package GCB_Content_Intelligence\Tests\Migration
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Parser classes.
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-node.php';
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/Parser/class-gcb-shortcode-parser.php';

// Converter classes.
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

// Migration service.
require_once dirname( __DIR__, 3 ) . '/wp-content/plugins/gcb-content-intelligence/migration/CLI/class-gcb-migration-service.php';

/**
 * Class MigrationServiceTest
 *
 * Tests the migration service that converts post content.
 */
class MigrationServiceTest extends TestCase {

	/**
	 * Migration service instance.
	 *
	 * @var GCB_Migration_Service
	 */
	private GCB_Migration_Service $service;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->service = new GCB_Migration_Service();
	}

	/**
	 * Test migrating simple container content.
	 *
	 * @return void
	 */
	public function test_migrates_simple_container(): void {
		$content = '[fusion_builder_container]Hello World[/fusion_builder_container]';
		$result  = $this->service->migrateContent( $content );

		$this->assertTrue( $result->success );
		$this->assertStringContainsString( '<!-- wp:group', $result->content );
		$this->assertStringContainsString( 'Hello World', $result->content );
		$this->assertEmpty( $result->errors );
	}

	/**
	 * Test migrating nested Avada structure.
	 *
	 * @return void
	 */
	public function test_migrates_nested_structure(): void {
		$content = <<<'AVADA'
[fusion_builder_container]
[fusion_builder_row]
[fusion_builder_column type="1_2"]
[fusion_text]<h2>Title</h2><p>Content here.</p>[/fusion_text]
[/fusion_builder_column]
[fusion_builder_column type="1_2"]
[fusion_youtube id="dQw4w9WgXcQ"]
[/fusion_builder_column]
[/fusion_builder_row]
[/fusion_builder_container]
AVADA;

		$result = $this->service->migrateContent( $content );

		$this->assertTrue( $result->success );
		$this->assertStringContainsString( '<!-- wp:group', $result->content );
		$this->assertStringContainsString( '<!-- wp:columns', $result->content );
		$this->assertStringContainsString( '<!-- wp:column', $result->content );
		$this->assertStringContainsString( '<!-- wp:heading', $result->content );
		$this->assertStringContainsString( '<!-- wp:embed', $result->content );
		$this->assertStringContainsString( 'dQw4w9WgXcQ', $result->content );
	}

	/**
	 * Test content without shortcodes passes through unchanged.
	 *
	 * @return void
	 */
	public function test_content_without_shortcodes_unchanged(): void {
		$content = '<p>Just regular HTML content.</p>';
		$result  = $this->service->migrateContent( $content );

		$this->assertTrue( $result->success );
		$this->assertSame( $content, $result->content );
		$this->assertFalse( $result->hasChanges );
	}

	/**
	 * Test detecting content that needs migration.
	 *
	 * @return void
	 */
	public function test_detects_content_needing_migration(): void {
		$avadaContent = '[fusion_builder_container]Content[/fusion_builder_container]';
		$plainContent = '<p>No shortcodes here.</p>';

		$this->assertTrue( $this->service->needsMigration( $avadaContent ) );
		$this->assertFalse( $this->service->needsMigration( $plainContent ) );
	}

	/**
	 * Test detecting various Fusion shortcodes.
	 *
	 * @dataProvider fusionShortcodeProvider
	 * @param string $content Content to check.
	 * @param bool   $expected Expected result.
	 * @return void
	 */
	public function test_detects_fusion_shortcodes( string $content, bool $expected ): void {
		$this->assertSame( $expected, $this->service->needsMigration( $content ) );
	}

	/**
	 * Data provider for fusion shortcode detection.
	 *
	 * @return array Test cases.
	 */
	public static function fusionShortcodeProvider(): array {
		return [
			'container'    => [ '[fusion_builder_container]x[/fusion_builder_container]', true ],
			'row'          => [ '[fusion_builder_row]x[/fusion_builder_row]', true ],
			'column'       => [ '[fusion_builder_column]x[/fusion_builder_column]', true ],
			'text'         => [ '[fusion_text]x[/fusion_text]', true ],
			'youtube'      => [ '[fusion_youtube id="abc"]', true ],
			'gallery'      => [ '[fusion_gallery]', true ],
			'code'         => [ '[fusion_code]x[/fusion_code]', true ],
			'plain html'   => [ '<p>No shortcodes</p>', false ],
			'other shortcode' => [ '[gallery ids="1,2,3"]', false ],
			'empty'        => [ '', false ],
		];
	}

	/**
	 * Test migration result tracks changes.
	 *
	 * @return void
	 */
	public function test_result_tracks_changes(): void {
		$content = '[fusion_youtube id="test123"]';
		$result  = $this->service->migrateContent( $content );

		$this->assertTrue( $result->success );
		$this->assertTrue( $result->hasChanges );
		$this->assertNotSame( $content, $result->content );
	}

	/**
	 * Test migration handles malformed content gracefully.
	 *
	 * @return void
	 */
	public function test_handles_malformed_content(): void {
		$content = '[fusion_text]Unclosed shortcode without end tag';
		$result  = $this->service->migrateContent( $content );

		// Should still succeed with graceful degradation.
		$this->assertTrue( $result->success );
		$this->assertStringContainsString( 'Unclosed shortcode', $result->content );
	}

	/**
	 * Test migration result contains original content.
	 *
	 * @return void
	 */
	public function test_result_contains_original(): void {
		$content = '[fusion_youtube id="abc123"]';
		$result  = $this->service->migrateContent( $content );

		$this->assertSame( $content, $result->originalContent );
	}

	/**
	 * Test dry run mode returns preview without modifying.
	 *
	 * @return void
	 */
	public function test_dry_run_returns_preview(): void {
		$content = '[fusion_youtube id="test"]';
		$result  = $this->service->migrateContent( $content, true );

		$this->assertTrue( $result->success );
		$this->assertTrue( $result->isDryRun );
		$this->assertStringContainsString( '<!-- wp:embed', $result->content );
	}

	/**
	 * Test unknown shortcodes are logged.
	 *
	 * @return void
	 */
	public function test_unknown_shortcodes_logged(): void {
		$content = '[fusion_unknown_element]Content[/fusion_unknown_element]';
		$result  = $this->service->migrateContent( $content );

		$this->assertTrue( $result->success );
		$this->assertNotEmpty( $result->warnings );
		$this->assertStringContainsString( 'fusion_unknown_element', $result->warnings[0] );
	}

	/**
	 * Test statistics are tracked.
	 *
	 * @return void
	 */
	public function test_statistics_tracked(): void {
		$content = <<<'AVADA'
[fusion_builder_container]
[fusion_builder_row]
[fusion_builder_column type="1_2"][fusion_text]<h2>A</h2>[/fusion_text][/fusion_builder_column]
[fusion_builder_column type="1_2"][fusion_youtube id="x"][/fusion_builder_column]
[/fusion_builder_row]
[/fusion_builder_container]
AVADA;

		$result = $this->service->migrateContent( $content );

		$this->assertArrayHasKey( 'shortcodes_converted', $result->stats );
		$this->assertGreaterThan( 0, $result->stats['shortcodes_converted'] );
	}

	/**
	 * Test batch migration statistics.
	 *
	 * @return void
	 */
	public function test_batch_statistics(): void {
		$posts = [
			[ 'id' => 1, 'content' => '[fusion_youtube id="a"]' ],
			[ 'id' => 2, 'content' => '[fusion_youtube id="b"]' ],
			[ 'id' => 3, 'content' => '<p>No shortcodes</p>' ],
		];

		$stats = $this->service->getBatchStats( $posts );

		$this->assertSame( 3, $stats['total'] );
		$this->assertSame( 2, $stats['needs_migration'] );
		$this->assertSame( 1, $stats['already_clean'] );
	}
}
