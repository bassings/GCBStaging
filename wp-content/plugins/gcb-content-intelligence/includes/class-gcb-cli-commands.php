<?php
/**
 * GCB WP-CLI Commands
 *
 * Provides WP-CLI commands:
 * - wp gcb classify-all
 * - wp gcb migrate-posts
 *
 * @package GCB_Content_Intelligence
 */

declare(strict_types=1);

/**
 * Class GCB_CLI_Commands
 *
 * WP-CLI commands for content classification.
 */
class GCB_CLI_Commands {

	/**
	 * Classify all posts in the archive
	 *
	 * ## OPTIONS
	 *
	 * [--post-type=<post-type>]
	 * : The post type to classify. Default: post
	 *
	 * [--status=<status>]
	 * : Post status to classify. Default: publish
	 *
	 * [--batch-size=<size>]
	 * : Number of posts to process per batch. Default: 100
	 *
	 * ## EXAMPLES
	 *
	 *     # Classify all published posts
	 *     $ wp gcb classify-all
	 *
	 *     # Classify all posts including drafts
	 *     $ wp gcb classify-all --status=any
	 *
	 *     # Process in smaller batches
	 *     $ wp gcb classify-all --batch-size=50
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function classify_all( array $args, array $assoc_args ): void {
		$post_type  = $assoc_args['post-type'] ?? 'post';
		$status     = $assoc_args['status'] ?? 'publish';
		$batch_size = intval( $assoc_args['batch-size'] ?? 100 );

		WP_CLI::log( '🔍 Starting content classification...' );
		WP_CLI::log( "Post Type: {$post_type}" );
		WP_CLI::log( "Status: {$status}" );
		WP_CLI::log( "Batch Size: {$batch_size}" );
		WP_CLI::log( '' );

		// Get total count.
		$total_query = new WP_Query(
			array(
				'post_type'      => $post_type,
				'post_status'    => $status,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$total_posts = $total_query->found_posts;

		WP_CLI::log( "📊 Found {$total_posts} posts to classify" );
		WP_CLI::log( '' );

		// Process in batches.
		$progress = WP_CLI\Utils\make_progress_bar( 'Classifying posts', $total_posts );
		$offset   = 0;
		$results  = array(
			'total'      => 0,
			'classified' => 0,
			'breakdown'  => array(
				'video-quick'   => 0,
				'video-feature' => 0,
				'standard'      => 0,
			),
		);

		while ( $offset < $total_posts ) {
			$batch_results = GCB_Content_Classifier::classify_all_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => $status,
					'posts_per_page' => $batch_size,
					'offset'         => $offset,
				)
			);

			$results['total']      += $batch_results['total'];
			$results['classified'] += $batch_results['classified'];

			foreach ( $batch_results['breakdown'] as $term => $count ) {
				$results['breakdown'][ $term ] += $count;
			}

			$progress->tick( $batch_results['total'] );
			$offset += $batch_size;

			// Prevent memory issues.
			wp_cache_flush();
		}

		$progress->finish();

		// Display results.
		WP_CLI::log( '' );
		WP_CLI::success( "✅ Classification complete!" );
		WP_CLI::log( '' );
		WP_CLI::log( '📈 Results:' );
		WP_CLI::log( "   Total posts: {$results['total']}" );
		WP_CLI::log( "   Classified: {$results['classified']}" );
		WP_CLI::log( '' );
		WP_CLI::log( '📂 Breakdown:' );
		WP_CLI::log( "   🎥 Video Quick: {$results['breakdown']['video-quick']}" );
		WP_CLI::log( "   🎬 Video Feature: {$results['breakdown']['video-feature']}" );
		WP_CLI::log( "   📝 Standard: {$results['breakdown']['standard']}" );
	}

	/**
	 * Migrate Avada Fusion Builder posts to Gutenberg blocks.
	 *
	 * Converts [fusion_*] shortcodes to native WordPress blocks.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview migration without modifying database.
	 *
	 * [--limit=<number>]
	 * : Maximum number of posts to migrate. Default: all.
	 *
	 * [--batch-size=<size>]
	 * : Number of posts per batch. Default: 50.
	 *
	 * [--post-id=<id>]
	 * : Migrate a single post by ID.
	 *
	 * [--post-type=<type>]
	 * : Post type to migrate. Default: post.
	 *
	 * [--status=<status>]
	 * : Post status to migrate. Default: publish.
	 *
	 * ## EXAMPLES
	 *
	 *     # Dry run to preview migration
	 *     $ wp gcb migrate-posts --dry-run --limit=10
	 *
	 *     # Migrate all posts in batches of 50
	 *     $ wp gcb migrate-posts
	 *
	 *     # Migrate single post
	 *     $ wp gcb migrate-posts --post-id=1234
	 *
	 *     # Migrate with smaller batches
	 *     $ wp gcb migrate-posts --batch-size=25
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function migrate_posts( array $args, array $assoc_args ): void {
		$dry_run    = isset( $assoc_args['dry-run'] );
		$limit      = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : -1;
		$batch_size = intval( $assoc_args['batch-size'] ?? 50 );
		$single_id  = isset( $assoc_args['post-id'] ) ? intval( $assoc_args['post-id'] ) : null;
		$post_type  = $assoc_args['post-type'] ?? 'post';
		$status     = $assoc_args['status'] ?? 'publish';

		// Load migration classes.
		$this->load_migration_classes();

		$service = new GCB_Migration_Service();

		WP_CLI::log( '🚀 Starting Avada to Gutenberg migration...' );
		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::warning( '🔍 DRY RUN MODE - No changes will be saved' );
			WP_CLI::log( '' );
		}

		// Single post migration.
		if ( null !== $single_id ) {
			$this->migrate_single_post( $service, $single_id, $dry_run );
			return;
		}

		// Batch migration.
		$this->migrate_batch( $service, $post_type, $status, $batch_size, $limit, $dry_run );
	}

	/**
	 * Migrate a single post.
	 *
	 * @param GCB_Migration_Service $service Migration service.
	 * @param int                   $post_id Post ID.
	 * @param bool                  $dry_run Dry run mode.
	 * @return void
	 */
	private function migrate_single_post( GCB_Migration_Service $service, int $post_id, bool $dry_run ): void {
		$post = get_post( $post_id );

		if ( ! $post ) {
			WP_CLI::error( "Post ID {$post_id} not found." );
			return;
		}

		WP_CLI::log( "📄 Migrating post: {$post->post_title} (ID: {$post_id})" );

		if ( ! $service->needsMigration( $post->post_content ) ) {
			WP_CLI::warning( 'Post does not contain Fusion Builder shortcodes. Skipping.' );
			return;
		}

		$result = $service->migrateContent( $post->post_content, $dry_run );

		if ( ! $result->success ) {
			WP_CLI::error( 'Migration failed: ' . implode( ', ', $result->errors ) );
			return;
		}

		// Show warnings.
		foreach ( $result->warnings as $warning ) {
			WP_CLI::warning( $warning );
		}

		if ( $dry_run ) {
			WP_CLI::log( '' );
			WP_CLI::log( '📝 Preview of converted content:' );
			WP_CLI::log( '─────────────────────────────────' );
			WP_CLI::log( substr( $result->content, 0, 2000 ) . ( strlen( $result->content ) > 2000 ? '...' : '' ) );
			WP_CLI::log( '─────────────────────────────────' );
			WP_CLI::log( '' );
			WP_CLI::success( 'Dry run complete. No changes made.' );
		} else {
			// Update post content.
			$update_result = wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $result->content,
				),
				true
			);

			if ( is_wp_error( $update_result ) ) {
				WP_CLI::error( 'Failed to update post: ' . $update_result->get_error_message() );
				return;
			}

			// Store original content as backup.
			update_post_meta( $post_id, '_gcb_original_avada_content', $result->originalContent );
			update_post_meta( $post_id, '_gcb_migrated_at', current_time( 'mysql' ) );

			WP_CLI::success( "Post {$post_id} migrated successfully!" );
			WP_CLI::log( "   Shortcodes converted: {$result->stats['shortcodes_converted']}" );
		}
	}

	/**
	 * Migrate posts in batches.
	 *
	 * @param GCB_Migration_Service $service    Migration service.
	 * @param string                $post_type  Post type.
	 * @param string                $status     Post status.
	 * @param int                   $batch_size Batch size.
	 * @param int                   $limit      Max posts (-1 for all).
	 * @param bool                  $dry_run    Dry run mode.
	 * @return void
	 */
	private function migrate_batch(
		GCB_Migration_Service $service,
		string $post_type,
		string $status,
		int $batch_size,
		int $limit,
		bool $dry_run
	): void {
		// Get posts with Fusion shortcodes.
		$query_args = array(
			'post_type'      => $post_type,
			'post_status'    => $status,
			'posts_per_page' => $limit > 0 ? min( $limit, 1000 ) : 1000,
			's'              => '[fusion_',
			'fields'         => 'ids',
		);

		$query       = new WP_Query( $query_args );
		$total_found = $query->found_posts;
		$post_ids    = $query->posts;

		if ( $limit > 0 && $total_found > $limit ) {
			$total_found = $limit;
			$post_ids    = array_slice( $post_ids, 0, $limit );
		}

		WP_CLI::log( "📊 Found {$total_found} posts with Fusion Builder shortcodes" );
		WP_CLI::log( "   Post Type: {$post_type}" );
		WP_CLI::log( "   Status: {$status}" );
		WP_CLI::log( "   Batch Size: {$batch_size}" );
		WP_CLI::log( '' );

		if ( 0 === $total_found ) {
			WP_CLI::success( 'No posts need migration!' );
			return;
		}

		// Initialize counters.
		$stats = array(
			'migrated'            => 0,
			'skipped'             => 0,
			'failed'              => 0,
			'shortcodes_total'    => 0,
			'warnings'            => 0,
		);

		// Open error log.
		$log_file = null;
		if ( ! $dry_run ) {
			$log_path = WP_CONTENT_DIR . '/migration_errors.log';
			$log_file = fopen( $log_path, 'a' );
			if ( $log_file ) {
				fwrite( $log_file, "\n\n=== Migration started: " . current_time( 'mysql' ) . " ===\n" );
			}
		}

		// Process in batches.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Migrating posts', $total_found );
		$batches  = array_chunk( $post_ids, $batch_size );

		foreach ( $batches as $batch_index => $batch_ids ) {
			foreach ( $batch_ids as $post_id ) {
				$post = get_post( $post_id );

				if ( ! $post ) {
					$stats['skipped']++;
					$progress->tick();
					continue;
				}

				if ( ! $service->needsMigration( $post->post_content ) ) {
					$stats['skipped']++;
					$progress->tick();
					continue;
				}

				$result = $service->migrateContent( $post->post_content, $dry_run );

				if ( ! $result->success ) {
					$stats['failed']++;
					if ( $log_file ) {
						fwrite( $log_file, "[{$post_id}] FAILED: " . implode( ', ', $result->errors ) . "\n" );
					}
					$progress->tick();
					continue;
				}

				// Log warnings.
				foreach ( $result->warnings as $warning ) {
					$stats['warnings']++;
					if ( $log_file ) {
						fwrite( $log_file, "[{$post_id}] WARNING: {$warning}\n" );
					}
				}

				if ( ! $dry_run && $result->hasChanges ) {
					// Update post.
					$update_result = wp_update_post(
						array(
							'ID'           => $post_id,
							'post_content' => $result->content,
						),
						true
					);

					if ( is_wp_error( $update_result ) ) {
						$stats['failed']++;
						if ( $log_file ) {
							fwrite( $log_file, "[{$post_id}] UPDATE FAILED: " . $update_result->get_error_message() . "\n" );
						}
					} else {
						// Store backup.
						update_post_meta( $post_id, '_gcb_original_avada_content', $result->originalContent );
						update_post_meta( $post_id, '_gcb_migrated_at', current_time( 'mysql' ) );

						$stats['migrated']++;
						$stats['shortcodes_total'] += $result->stats['shortcodes_converted'] ?? 0;
					}
				} elseif ( $dry_run ) {
					$stats['migrated']++;
					$stats['shortcodes_total'] += $result->stats['shortcodes_converted'] ?? 0;
				}

				$progress->tick();
			}

			// Memory management between batches.
			wp_cache_flush();
			gc_collect_cycles();
		}

		$progress->finish();

		// Close log file.
		if ( $log_file ) {
			fwrite( $log_file, "=== Migration completed: " . current_time( 'mysql' ) . " ===\n" );
			fclose( $log_file );
		}

		// Display results.
		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::success( '🔍 Dry run complete!' );
		} else {
			WP_CLI::success( '✅ Migration complete!' );
		}

		WP_CLI::log( '' );
		WP_CLI::log( '📈 Results:' );
		WP_CLI::log( "   ✅ Migrated: {$stats['migrated']}" );
		WP_CLI::log( "   ⏭️  Skipped: {$stats['skipped']}" );
		WP_CLI::log( "   ❌ Failed: {$stats['failed']}" );
		WP_CLI::log( "   ⚠️  Warnings: {$stats['warnings']}" );
		WP_CLI::log( "   🔄 Shortcodes converted: {$stats['shortcodes_total']}" );

		if ( ! $dry_run && $stats['failed'] > 0 ) {
			WP_CLI::log( '' );
			WP_CLI::warning( "Check wp-content/migration_errors.log for details on failures." );
		}
	}

	/**
	 * Load migration classes.
	 *
	 * @return void
	 */
	private function load_migration_classes(): void {
		$base = dirname( __DIR__ ) . '/migration/';

		// Parser.
		require_once $base . 'Parser/class-gcb-shortcode-node.php';
		require_once $base . 'Parser/class-gcb-shortcode-parser.php';

		// Converter.
		require_once $base . 'Converter/interface-gcb-transformer.php';
		require_once $base . 'Converter/class-gcb-to-block-converter.php';
		require_once $base . 'Converter/Transformers/class-gcb-container-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-row-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-column-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-text-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-youtube-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-separator-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-code-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-button-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-image-transformer.php';

		// Service.
		require_once $base . 'CLI/class-gcb-migration-service.php';
	}
}
