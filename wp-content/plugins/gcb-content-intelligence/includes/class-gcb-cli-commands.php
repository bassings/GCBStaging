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

			// Clean up Fusion metadata.
			$cleanup_result = $service->cleanupFusionMetadata( $post_id, false );

			WP_CLI::success( "Post {$post_id} migrated successfully!" );
			WP_CLI::log( "   Shortcodes converted: {$result->stats['shortcodes_converted']}" );
			if ( $cleanup_result['deleted'] > 0 ) {
				WP_CLI::log( "   Fusion metadata cleaned: {$cleanup_result['deleted']} keys removed" );
			}
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
		// Use -1 for unlimited, or respect user's --limit flag.
		$query_args = array(
			'post_type'      => $post_type,
			'post_status'    => $status,
			'posts_per_page' => $limit > 0 ? $limit : -1,
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

		// Track unique unknown shortcodes.
		$unknown_shortcodes = array();

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

				// Log warnings and track unknown shortcodes.
				foreach ( $result->warnings as $warning ) {
					$stats['warnings']++;
					if ( $log_file ) {
						fwrite( $log_file, "[{$post_id}] WARNING: {$warning}\n" );
					}
					// Extract shortcode name from warning.
					if ( preg_match( '/Unknown shortcode: \[([^\]]+)\]/', $warning, $matches ) ) {
						$shortcode = $matches[1];
						if ( ! isset( $unknown_shortcodes[ $shortcode ] ) ) {
							$unknown_shortcodes[ $shortcode ] = 0;
						}
						$unknown_shortcodes[ $shortcode ]++;
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

						// Clean up Fusion metadata.
						$service->cleanupFusionMetadata( $post_id, false );

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

		// Display unknown shortcodes summary.
		if ( ! empty( $unknown_shortcodes ) ) {
			arsort( $unknown_shortcodes );
			WP_CLI::log( '' );
			WP_CLI::log( '📋 Unknown shortcodes (need transformers):' );
			foreach ( $unknown_shortcodes as $shortcode => $count ) {
				WP_CLI::log( "   [{$shortcode}] - {$count} occurrences" );
			}
		}
	}

	/**
	 * Audit brand categorization accuracy across all posts.
	 *
	 * Identifies posts where detected brands in content don't match assigned brand categories.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview audit without saving report file.
	 *
	 * [--limit=<number>]
	 * : Maximum number of posts to audit. Default: all.
	 *
	 * [--output=<file>]
	 * : Output file for markdown report. Default: categorization-audit-report.md
	 *
	 * [--post-type=<type>]
	 * : Post type to audit. Default: post.
	 *
	 * [--status=<status>]
	 * : Post status to audit. Default: publish.
	 *
	 * ## EXAMPLES
	 *
	 *     # Audit first 100 posts (dry run)
	 *     $ wp gcb audit-brands --dry-run --limit=100
	 *
	 *     # Full audit with custom output file
	 *     $ wp gcb audit-brands --output=brand-report.md
	 *
	 *     # Audit all posts including drafts
	 *     $ wp gcb audit-brands --status=any
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function audit_brands( array $args, array $assoc_args ): void {
		$dry_run   = isset( $assoc_args['dry-run'] );
		$limit     = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : -1;
		$output    = $assoc_args['output'] ?? 'categorization-audit-report.md';
		$post_type = $assoc_args['post-type'] ?? 'post';
		$status    = $assoc_args['status'] ?? 'publish';

		// Load brand audit classes.
		$this->load_brand_audit_classes();

		WP_CLI::log( '🔍 Starting brand categorization audit...' );
		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::warning( '🔍 DRY RUN MODE - No report file will be saved' );
			WP_CLI::log( '' );
		}

		// Query posts.
		$query_args = array(
			'post_type'      => $post_type,
			'post_status'    => $status,
			'posts_per_page' => $limit > 0 ? $limit : -1,
			'fields'         => 'all',
		);

		$query       = new WP_Query( $query_args );
		$total_posts = $query->found_posts;

		WP_CLI::log( "📊 Found {$total_posts} posts to audit" );
		WP_CLI::log( "   Post Type: {$post_type}" );
		WP_CLI::log( "   Status: {$status}" );
		WP_CLI::log( '' );

		// Audit results storage.
		$stats = array(
			'total'           => 0,
			'with_brands'     => 0,
			'wrong_brand'     => 0,
			'missing_brand'   => 0,
			'extra_brand'     => 0,
			'comparison_posts' => 0,
		);

		$mismatches = array(
			'wrong_brand'   => array(),
			'missing_brand' => array(),
			'extra_brand'   => array(),
			'comparison'    => array(),
		);

		// Process posts with progress bar.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Auditing posts', $total_posts );

		foreach ( $query->posts as $post ) {
			$stats['total']++;

			// Audit this post.
			$result = GCB_Brand_Auditor::audit_post( $post );

			// Track posts with brand categories.
			if ( ! empty( $result['assigned_brands'] ) ) {
				$stats['with_brands']++;
			}

			// Track comparison posts.
			if ( $result['is_comparison'] ) {
				$stats['comparison_posts']++;
				$mismatches['comparison'][] = $result;
			}

			// Track mismatches.
			if ( GCB_Brand_Auditor::has_mismatches( $result ) ) {
				if ( ! empty( $result['mismatches']['wrong_brand'] ) ) {
					$stats['wrong_brand']++;
					$mismatches['wrong_brand'][] = $result;
				}

				if ( ! empty( $result['mismatches']['missing_brand'] ) ) {
					$stats['missing_brand']++;
					$mismatches['missing_brand'][] = $result;
				}

				if ( ! empty( $result['mismatches']['extra_brand'] ) ) {
					$stats['extra_brand']++;
					$mismatches['extra_brand'][] = $result;
				}
			}

			$progress->tick();
		}

		$progress->finish();

		// Generate markdown report.
		$report = $this->generate_brand_audit_report( $stats, $mismatches );

		// Display results.
		WP_CLI::log( '' );
		WP_CLI::success( '✅ Audit complete!' );
		WP_CLI::log( '' );
		WP_CLI::log( '📈 Results:' );
		WP_CLI::log( "   Total posts: {$stats['total']}" );
		WP_CLI::log( "   Posts with brand categories: {$stats['with_brands']}" );
		WP_CLI::log( "   ❌ Wrong brand category: {$stats['wrong_brand']}" );
		WP_CLI::log( "   ⚠️  Missing brand category: {$stats['missing_brand']}" );
		WP_CLI::log( "   ℹ️  Extra brand category: {$stats['extra_brand']}" );
		WP_CLI::log( "   🔄 Comparison posts: {$stats['comparison_posts']}" );

		if ( ! $dry_run ) {
			// Save report to file.
			$output_path = ABSPATH . $output;
			file_put_contents( $output_path, $report );
			WP_CLI::log( '' );
			WP_CLI::success( "Report saved to: {$output}" );
		} else {
			WP_CLI::log( '' );
			WP_CLI::log( '📄 Report preview (first 2000 characters):' );
			WP_CLI::log( '─────────────────────────────────' );
			WP_CLI::log( substr( $report, 0, 2000 ) . '...' );
			WP_CLI::log( '─────────────────────────────────' );
		}
	}

	/**
	 * Generate markdown report from audit results.
	 *
	 * @param array $stats      Statistics summary.
	 * @param array $mismatches Mismatches by type.
	 * @return string Markdown report content.
	 */
	private function generate_brand_audit_report( array $stats, array $mismatches ): string {
		$report = "# Brand Categorization Accuracy Audit Report\n\n";
		$report .= "Generated: " . current_time( 'Y-m-d H:i:s' ) . "\n\n";

		// Executive summary.
		$report .= "## Executive Summary\n\n";
		$report .= "- **Posts Analyzed**: {$stats['total']}\n";
		$report .= "- **Posts with Brand Categories**: {$stats['with_brands']}\n";
		$report .= "- **Wrong Brand Category** (Critical): {$stats['wrong_brand']}\n";
		$report .= "- **Missing Brand Category** (Medium): {$stats['missing_brand']}\n";
		$report .= "- **Extra Brand Category** (Low): {$stats['extra_brand']}\n";
		$report .= "- **Comparison Posts**: {$stats['comparison_posts']}\n\n";

		// Wrong brand section (critical).
		$report .= "## 1. Wrong Brand Category (Critical)\n\n";
		$report .= "Posts with brand categories that don't match detected content.\n\n";

		if ( empty( $mismatches['wrong_brand'] ) ) {
			$report .= "✅ No critical mismatches found!\n\n";
		} else {
			$report .= "| Post ID | Title | Detected Brands | Wrong Category | Fix |\n";
			$report .= "|---------|-------|----------------|----------------|-----|\n";

			foreach ( $mismatches['wrong_brand'] as $result ) {
				$post_id        = $result['post_id'];
				$title          = esc_html( substr( $result['post_title'], 0, 50 ) );
				$detected       = implode( ', ', array_keys( $result['detected_brands'] ) );
				$wrong_cats     = implode( ', ', array_keys( $result['assigned_brands'] ) );
				$recommendations = GCB_Brand_Auditor::generate_recommendations( $result );
				$fix            = implode( '; ', $recommendations );

				$report .= "| {$post_id} | {$title} | {$detected} | {$wrong_cats} | {$fix} |\n";
			}

			$report .= "\n";
		}

		// Missing brand section (medium).
		$report .= "## 2. Missing Brand Category (Medium)\n\n";
		$report .= "Posts mentioning brands without corresponding brand categories.\n\n";

		if ( empty( $mismatches['missing_brand'] ) ) {
			$report .= "✅ No missing brand categories!\n\n";
		} else {
			$report .= "| Post ID | Title | Detected Brand | Confidence | Current Categories | Fix |\n";
			$report .= "|---------|-------|----------------|-----------|-------------------|-----|\n";

			foreach ( $mismatches['missing_brand'] as $result ) {
				$post_id     = $result['post_id'];
				$title       = esc_html( substr( $result['post_title'], 0, 50 ) );
				$missing     = $result['mismatches']['missing_brand'];
				$current     = implode( ', ', array_keys( $result['assigned_brands'] ) );

				foreach ( $missing as $miss ) {
					$brand      = $miss['brand'];
					$confidence = round( $miss['confidence'] * 100 ) . '%';
					$fix        = 'Add ' . GCB_Brand_Dictionary::get_category_from_brand( $brand );

					$report .= "| {$post_id} | {$title} | {$brand} | {$confidence} | {$current} | {$fix} |\n";
				}
			}

			$report .= "\n";
		}

		// Extra brand section (low priority).
		$report .= "## 3. Extra Brand Category (Low Priority)\n\n";
		$report .= "Posts with brand categories but brand not detected in content.\n\n";

		if ( empty( $mismatches['extra_brand'] ) ) {
			$report .= "✅ No extra brand categories!\n\n";
		} else {
			$report .= "| Post ID | Title | Extra Category | Fix |\n";
			$report .= "|---------|-------|---------------|-----|\n";

			foreach ( $mismatches['extra_brand'] as $result ) {
				$post_id    = $result['post_id'];
				$title      = esc_html( substr( $result['post_title'], 0, 50 ) );
				$extra_cats = implode( ', ', array_keys( $result['assigned_brands'] ) );
				$fix        = 'Review categories (brand not mentioned)';

				$report .= "| {$post_id} | {$title} | {$extra_cats} | {$fix} |\n";
			}

			$report .= "\n";
		}

		// Comparison posts section.
		$report .= "## 4. Comparison Posts (Review Needed)\n\n";
		$report .= "Posts that appear to compare multiple brands.\n\n";

		if ( empty( $mismatches['comparison'] ) ) {
			$report .= "No comparison posts detected.\n\n";
		} else {
			$report .= "| Post ID | Title | Brands Mentioned | Categories |\n";
			$report .= "|---------|-------|-----------------|------------|\n";

			foreach ( $mismatches['comparison'] as $result ) {
				$post_id   = $result['post_id'];
				$title     = esc_html( substr( $result['post_title'], 0, 50 ) );
				$brands    = implode( ', ', array_keys( $result['detected_brands'] ) );
				$categories = implode( ', ', array_keys( $result['assigned_brands'] ) );

				$report .= "| {$post_id} | {$title} | {$brands} | {$categories} |\n";
			}

			$report .= "\n";
		}

		return $report;
	}

	/**
	 * Load brand audit classes.
	 *
	 * @return void
	 */
	private function load_brand_audit_classes(): void {
		require_once GCB_CI_DIR . 'includes/class-gcb-brand-dictionary.php';
		require_once GCB_CI_DIR . 'includes/class-gcb-brand-auditor.php';
	}

	/**
	 * Clean up Fusion Builder metadata from all posts.
	 *
	 * Removes legacy Fusion Builder metadata (_fusion, _fusion_google_fonts, etc.)
	 * from posts that no longer need it.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview cleanup without deleting metadata.
	 *
	 * [--post-type=<type>]
	 * : Post type to clean. Default: post.
	 *
	 * [--status=<status>]
	 * : Post status to clean. Default: publish.
	 *
	 * [--limit=<number>]
	 * : Maximum number of posts to process. Default: all.
	 *
	 * ## EXAMPLES
	 *
	 *     # Preview metadata cleanup
	 *     $ wp gcb cleanup-fusion-metadata --dry-run
	 *
	 *     # Clean all published posts
	 *     $ wp gcb cleanup-fusion-metadata
	 *
	 *     # Clean first 100 posts
	 *     $ wp gcb cleanup-fusion-metadata --limit=100
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function cleanup_fusion_metadata( array $args, array $assoc_args ): void {
		$dry_run   = isset( $assoc_args['dry-run'] );
		$post_type = $assoc_args['post-type'] ?? 'post';
		$status    = $assoc_args['status'] ?? 'publish';
		$limit     = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : -1;

		$this->load_migration_classes();
		$service = new GCB_Migration_Service();

		WP_CLI::log( '🧹 Starting Fusion metadata cleanup...' );
		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::warning( '🔍 DRY RUN MODE - No changes will be saved' );
			WP_CLI::log( '' );
		}

		// Find posts with fusion metadata.
		global $wpdb;
		$query = "
			SELECT DISTINCT p.ID
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key LIKE '%fusion%'
			AND p.post_type = %s
			AND p.post_status = %s
		";

		if ( $limit > 0 ) {
			$query .= " LIMIT %d";
			$post_ids = $wpdb->get_col( $wpdb->prepare( $query, $post_type, $status, $limit ) );
		} else {
			$post_ids = $wpdb->get_col( $wpdb->prepare( $query, $post_type, $status ) );
		}

		$total = count( $post_ids );
		WP_CLI::log( "📊 Found {$total} posts with Fusion metadata" );
		WP_CLI::log( '' );

		if ( 0 === $total ) {
			WP_CLI::success( 'No posts with Fusion metadata found!' );
			return;
		}

		$stats = [
			'cleaned'      => 0,
			'meta_deleted' => 0,
		];

		$progress = \WP_CLI\Utils\make_progress_bar( 'Cleaning metadata', $total );

		foreach ( $post_ids as $post_id ) {
			$cleanup_result = $service->cleanupFusionMetadata( $post_id, $dry_run );

			if ( $cleanup_result['deleted'] > 0 ) {
				$stats['cleaned']++;
				$stats['meta_deleted'] += $cleanup_result['deleted'];
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::log( '' );
		if ( $dry_run ) {
			WP_CLI::success( '🔍 Dry run complete!' );
		} else {
			WP_CLI::success( '✅ Cleanup complete!' );
		}

		WP_CLI::log( '' );
		WP_CLI::log( '📈 Results:' );
		WP_CLI::log( "   Posts cleaned: {$stats['cleaned']}" );
		WP_CLI::log( "   Meta keys deleted: {$stats['meta_deleted']}" );
	}

	/**
	 * Convert classic HTML posts to Gutenberg blocks.
	 *
	 * Wraps classic HTML content in a Classic block for proper Gutenberg compatibility.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview conversion without modifying database.
	 *
	 * [--limit=<number>]
	 * : Maximum number of posts to convert. Default: all.
	 *
	 * [--post-type=<type>]
	 * : Post type to convert. Default: post.
	 *
	 * [--status=<status>]
	 * : Post status to convert. Default: publish.
	 *
	 * ## EXAMPLES
	 *
	 *     # Preview conversion
	 *     $ wp gcb convert-classic-html --dry-run --limit=10
	 *
	 *     # Convert all classic HTML posts
	 *     $ wp gcb convert-classic-html
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function convert_classic_html( array $args, array $assoc_args ): void {
		$dry_run   = isset( $assoc_args['dry-run'] );
		$limit     = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : -1;
		$post_type = $assoc_args['post-type'] ?? 'post';
		$status    = $assoc_args['status'] ?? 'publish';

		$this->load_migration_classes();
		$service = new GCB_Migration_Service();

		WP_CLI::log( '🔄 Starting classic HTML to Gutenberg conversion...' );
		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::warning( '🔍 DRY RUN MODE - No changes will be saved' );
			WP_CLI::log( '' );
		}

		// Get all posts.
		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => $status,
			'posts_per_page' => $limit > 0 ? $limit : -1,
			'fields'         => 'ids',
		];

		$query    = new WP_Query( $query_args );
		$post_ids = $query->posts;

		// Filter for classic HTML posts.
		$classic_posts = [];
		foreach ( $post_ids as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );
			if ( $service->isClassicHTML( $content ) ) {
				$classic_posts[] = $post_id;
			}
		}

		$total = count( $classic_posts );
		WP_CLI::log( "📊 Found {$total} classic HTML posts" );
		WP_CLI::log( '' );

		if ( 0 === $total ) {
			WP_CLI::success( 'No classic HTML posts found!' );
			return;
		}

		$stats = [
			'converted' => 0,
			'failed'    => 0,
			'skipped'   => 0,
		];

		$progress = \WP_CLI\Utils\make_progress_bar( 'Converting posts', $total );

		foreach ( $classic_posts as $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post ) {
				$stats['skipped']++;
				$progress->tick();
				continue;
			}

			$result = $service->convertClassicHTML( $post->post_content, $dry_run );

			if ( ! $result->success ) {
				$stats['failed']++;
				$progress->tick();
				continue;
			}

			if ( ! $dry_run && $result->hasChanges ) {
				$update_result = wp_update_post(
					[
						'ID'           => $post_id,
						'post_content' => $result->content,
					],
					true
				);

				if ( is_wp_error( $update_result ) ) {
					$stats['failed']++;
				} else {
					update_post_meta( $post_id, '_gcb_converted_from_classic', current_time( 'mysql' ) );
					$stats['converted']++;
				}
			} elseif ( $dry_run ) {
				$stats['converted']++;
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::log( '' );
		if ( $dry_run ) {
			WP_CLI::success( '🔍 Dry run complete!' );
		} else {
			WP_CLI::success( '✅ Conversion complete!' );
		}

		WP_CLI::log( '' );
		WP_CLI::log( '📈 Results:' );
		WP_CLI::log( "   ✅ Converted: {$stats['converted']}" );
		WP_CLI::log( "   ⏭️  Skipped: {$stats['skipped']}" );
		WP_CLI::log( "   ❌ Failed: {$stats['failed']}" );
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
		require_once $base . 'Converter/Transformers/class-gcb-gallery-transformer.php';
		require_once $base . 'Converter/Transformers/class-gcb-misc-transformer.php';

		// Service.
		require_once $base . 'CLI/class-gcb-migration-service.php';
	}
}
