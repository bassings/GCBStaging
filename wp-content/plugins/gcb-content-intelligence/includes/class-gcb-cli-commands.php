<?php
/**
 * GCB WP-CLI Commands
 *
 * Provides WP-CLI command: wp gcb classify-all
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
}
