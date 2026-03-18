<?php
/**
 * GCB YouTube Channel Fetcher
 *
 * Fetches latest videos from @GayCarBoys YouTube channel.
 * Uses YouTube Data API v3 with hourly cron refresh.
 *
 * @package GCB_Content_Intelligence
 * @since 1.1.0
 */

declare(strict_types=1);

/**
 * Class GCB_YouTube_Channel_Fetcher
 *
 * Handles channel-level YouTube API operations.
 * Separate from GCB_Video_Processor (post-level operations).
 */
class GCB_YouTube_Channel_Fetcher {

	/**
	 * YouTube Data API v3 endpoints
	 */
	private const CHANNEL_ENDPOINT  = 'https://www.googleapis.com/youtube/v3/channels';
	private const PLAYLIST_ENDPOINT = 'https://www.googleapis.com/youtube/v3/playlistItems';
	private const VIDEOS_ENDPOINT   = 'https://www.googleapis.com/youtube/v3/videos';

	/**
	 * Channel configuration
	 */
	private const CHANNEL_ID = 'UCYAQE20p01w8TZXkvcjna8Q'; // @GayCarBoys channel ID.

	/**
	 * Caching configuration
	 */
	private const TRANSIENT_KEY    = 'gcb_youtube_channel_videos';
	private const CACHE_DURATION   = 6 * HOUR_IN_SECONDS; // 6 hours (reduces API quota usage).

	/**
	 * Cron hook name
	 */
	private const CRON_HOOK = 'gcb_refresh_youtube_videos';

	/**
	 * Maximum videos to fetch for video rail
	 */
	private const MAX_RESULTS = 10;

	/**
	 * Maximum videos per API request (YouTube API limit is 50)
	 */
	private const MAX_RESULTS_PER_PAGE = 50;

	/**
	 * Transient key for all videos (archive page)
	 */
	private const TRANSIENT_KEY_ALL = 'gcb_youtube_all_videos';

	/**
	 * Register WordPress hooks
	 */
	public static function init(): void {
		// Schedule hourly cron job.
		add_action( 'init', array( __CLASS__, 'schedule_refresh' ) );

		// Hook cron job to fetch function.
		add_action( self::CRON_HOOK, array( __CLASS__, 'refresh_videos' ) );

		// Admin actions: manual triggers.
		add_action( 'admin_init', array( __CLASS__, 'handle_manual_thumbnail_cache' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_manual_refresh' ) );
	}

	/**
	 * Handle manual full refresh from WP Admin.
	 *
	 * Visit: /wp-admin/?gcb_refresh_youtube=1
	 * Clears video transients, fetches fresh from API, then caches thumbnails.
	 * Requires manage_options capability.
	 */
	public static function handle_manual_refresh(): void {
		if ( empty( $_GET['gcb_refresh_youtube'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised.' );
		}

		// Clear all caches.
		self::clear_cache();

		// Also clear the bento grid transient so it picks up fresh data.
		delete_transient( 'gcb_bento_grid_' . gmdate( 'Y-m-d-H' ) );

		// Fetch fresh videos from API.
		$videos = self::get_videos();

		// Cache thumbnails if available.
		$cached = 0;
		$thumb_stats = array( 'total' => 0, 'valid' => 0, 'stale' => 0 );
		if ( class_exists( 'GCB_YouTube_Thumbnail_Cache' ) && ! empty( $videos ) ) {
			// DISABLED: $cached = GCB_YouTube_Thumbnail_Cache::cache_thumbnails( $videos );
			$thumb_stats = GCB_YouTube_Thumbnail_Cache::get_stats();
		}

		$output = '<h2>GCB YouTube Refresh</h2>';
		$output .= '<h3>Videos</h3>';
		$output .= sprintf( '<p><strong>Videos fetched:</strong> %d</p>', count( $videos ) );

		if ( ! empty( $videos ) ) {
			$output .= '<ol>';
			foreach ( $videos as $v ) {
				$output .= sprintf(
					'<li><strong>%s</strong> (%s) — %s views</li>',
					esc_html( $v['title'] ?? '' ),
					esc_html( $v['video_id'] ?? '' ),
					number_format( (int) ( $v['view_count'] ?? 0 ) )
				);
			}
			$output .= '</ol>';
		}

		$output .= '<h3>Thumbnails</h3>';
		$output .= sprintf( '<p><strong>New thumbnails cached:</strong> %d</p>', $cached );
		$output .= sprintf(
			'<p><strong>Cache status:</strong> %d total (%d valid, %d stale)</p>',
			$thumb_stats['total'], $thumb_stats['valid'], $thumb_stats['stale']
		);

		$output .= sprintf(
			'<p><a href="%s">Refresh again</a> | <a href="%s">Cache more thumbnails</a> | <a href="%s">Dashboard</a></p>',
			admin_url( '?gcb_refresh_youtube=1' ),
			admin_url( '?gcb_cache_youtube_thumbnails=1' ),
			admin_url()
		);

		wp_die( $output, 'GCB YouTube Refresh', array( 'back_link' => false ) );
	}

	/**
	 * Handle manual thumbnail cache trigger from WP Admin.
	 *
	 * Visit: /wp-admin/?gcb_cache_youtube_thumbnails=1
	 * Requires manage_options capability.
	 */
	public static function handle_manual_thumbnail_cache(): void {
		if ( empty( $_GET['gcb_cache_youtube_thumbnails'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised.' );
		}

		// Fetch current videos (from cache or API).
		$videos = self::get_videos();

		if ( empty( $videos ) ) {
			// Try the all-videos cache too.
			$videos = self::get_all_videos( 50 );
		}

		$cached = 0;
		$stats_before = array( 'total' => 0, 'valid' => 0, 'stale' => 0 );

		if ( class_exists( 'GCB_YouTube_Thumbnail_Cache' ) ) {
			$stats_before = GCB_YouTube_Thumbnail_Cache::get_stats();
			// DISABLED: $cached = GCB_YouTube_Thumbnail_Cache::cache_thumbnails( $videos );
			$stats_after = GCB_YouTube_Thumbnail_Cache::get_stats();
		}

		wp_die( sprintf(
			'<h2>GCB YouTube Thumbnail Cache</h2>'
			. '<p><strong>Videos found:</strong> %d</p>'
			. '<p><strong>New thumbnails cached:</strong> %d</p>'
			. '<p><strong>Before:</strong> %d total (%d valid, %d stale)</p>'
			. '<p><strong>After:</strong> %d total (%d valid, %d stale)</p>'
			. '<p><a href="%s">Run again</a> | <a href="%s">Back to Dashboard</a></p>',
			count( $videos ),
			$cached,
			$stats_before['total'], $stats_before['valid'], $stats_before['stale'],
			$stats_after['total'] ?? 0, $stats_after['valid'] ?? 0, $stats_after['stale'] ?? 0,
			admin_url( '?gcb_cache_youtube_thumbnails=1' ),
			admin_url()
		), 'GCB YouTube Thumbnail Cache', array( 'back_link' => false ) );
	}

	/**
	 * Get cached channel videos or fetch fresh data
	 *
	 * @return array Array of video data or empty array on failure.
	 */
	public static function get_videos(): array {
		// Step 1: Check transient cache.
		$cached_videos = get_transient( self::TRANSIENT_KEY );

		if ( false !== $cached_videos && is_array( $cached_videos ) ) {
			return $cached_videos;
		}

		// Step 2: Cache miss - fetch fresh data.
		$videos = self::fetch_channel_videos();

		// Step 3: Store in transient (even if empty, to prevent repeated API calls).
		if ( ! empty( $videos ) ) {
			set_transient( self::TRANSIENT_KEY, $videos, self::CACHE_DURATION );
		}

		return $videos;
	}

	/**
	 * Fetch videos from YouTube channel
	 *
	 * @return array Array of video objects.
	 */
	private static function fetch_channel_videos(): array {
		// Allow WordPress option to override test mode (for staging/production).
		// This lets you disable test mode even if GCB_TEST_KEY is defined in wp-config.php.
		$force_real_api = get_option( 'gcb_force_real_api', false );

		// Test mode: Return mock data (unless overridden).
		if ( ! $force_real_api && defined( 'GCB_TEST_KEY' ) && ! empty( GCB_TEST_KEY ) ) {
			return self::get_mock_videos();
		}

		// Check API key (wp-config.php or WordPress option).
		$api_key = self::get_api_key();
		if ( empty( $api_key ) ) {
			error_log( 'GCB: YouTube API key not configured' );
			return array();
		}

		// Step 1: Get channel's "uploads" playlist ID.
		$uploads_playlist_id = self::get_uploads_playlist_id();

		if ( empty( $uploads_playlist_id ) ) {
			return array();
		}

		// Step 2: Fetch playlist items (latest videos).
		$video_ids = self::fetch_playlist_items( $uploads_playlist_id );

		if ( empty( $video_ids ) ) {
			return array();
		}

		// Step 3: Fetch video details (duration, views, statistics).
		$videos = self::fetch_video_details( $video_ids );

		return $videos;
	}

	/**
	 * Get YouTube API key from WordPress option or wp-config.php
	 *
	 * Checks WordPress option first (allows runtime updates without wp-config access),
	 * then falls back to wp-config.php constant.
	 *
	 * @return string API key or empty string if not found.
	 */
	private static function get_api_key(): string {
		// Priority 1: Check WordPress option (allows updates on WordPress.com hosting).
		$api_key = get_option( 'gcb_youtube_api_key', '' );
		if ( ! empty( $api_key ) ) {
			return $api_key;
		}

		// Priority 2: Fall back to wp-config.php constant.
		if ( defined( 'GCB_YOUTUBE_API_KEY' ) && ! empty( GCB_YOUTUBE_API_KEY ) ) {
			return GCB_YOUTUBE_API_KEY;
		}

		return '';
	}

	/**
	 * Get channel's uploads playlist ID
	 *
	 * @return string Playlist ID or empty string on failure.
	 */
	private static function get_uploads_playlist_id(): string {
		$api_url = add_query_arg(
			array(
				'part' => 'contentDetails',
				'id'   => self::CHANNEL_ID,
				'key'  => self::get_api_key(),
			),
			self::CHANNEL_ENDPOINT
		);

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'GCB: YouTube API error - ' . $response->get_error_message() );
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( 'GCB: YouTube API JSON parse error - ' . json_last_error_msg() );
			return '';
		}

		if ( isset( $data['error'] ) ) {
			error_log( 'GCB: YouTube API error - ' . ( $data['error']['message'] ?? 'Unknown' ) );

			// If quota exceeded, try to use cached data.
			if ( isset( $data['error']['code'] ) && 403 === $data['error']['code'] ) {
				error_log( 'GCB: YouTube API quota exceeded - using cached data' );
			}

			return '';
		}

		if ( empty( $data['items'] ) ) {
			error_log( 'GCB: Channel not found - ' . self::CHANNEL_ID );
			return '';
		}

		return $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? '';
	}

	/**
	 * Fetch playlist items (video IDs)
	 *
	 * @param string $playlist_id Uploads playlist ID.
	 * @return array Array of video IDs.
	 */
	private static function fetch_playlist_items( string $playlist_id ): array {
		$api_url = add_query_arg(
			array(
				'part'       => 'snippet,contentDetails',
				'playlistId' => $playlist_id,
				'maxResults' => self::MAX_RESULTS,
				'key'        => self::get_api_key(),
			),
			self::PLAYLIST_ENDPOINT
		);

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'GCB: YouTube API error - ' . $response->get_error_message() );
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( 'GCB: YouTube API JSON parse error - ' . json_last_error_msg() );
			return array();
		}

		if ( empty( $data['items'] ) ) {
			return array();
		}

		$video_ids = array();

		foreach ( $data['items'] as $item ) {
			$video_id = $item['contentDetails']['videoId'] ?? '';
			if ( ! empty( $video_id ) ) {
				$video_ids[] = $video_id;
			}
		}

		return $video_ids;
	}

	/**
	 * Fetch video details (duration, views, statistics)
	 *
	 * @param array $video_ids Array of video IDs.
	 * @return array Array of video objects.
	 */
	private static function fetch_video_details( array $video_ids ): array {
		// API accepts comma-separated video IDs.
		$ids_string = implode( ',', $video_ids );

		$api_url = add_query_arg(
			array(
				'part' => 'snippet,contentDetails,statistics',
				'id'   => $ids_string,
				'key'  => self::get_api_key(),
			),
			self::VIDEOS_ENDPOINT
		);

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'GCB: YouTube API error - ' . $response->get_error_message() );
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( 'GCB: YouTube API JSON parse error - ' . json_last_error_msg() );
			return array();
		}

		if ( empty( $data['items'] ) ) {
			return array();
		}

		$videos = array();

		foreach ( $data['items'] as $item ) {
			$video_id  = $item['id'] ?? '';
			$title     = $item['snippet']['title'] ?? '';
			$thumbnail = $item['snippet']['thumbnails']['high']['url'] ?? '';

			// Fallback to maxresdefault if high quality not available.
			if ( empty( $thumbnail ) && ! empty( $video_id ) ) {
				$thumbnail = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
			}

			$videos[] = array(
				'video_id'     => $video_id,
				'title'        => $title,
				'description'  => $item['snippet']['description'] ?? '',
				'thumbnail'    => $thumbnail,
				'published_at' => $item['snippet']['publishedAt'] ?? '',
				'duration'     => $item['contentDetails']['duration'] ?? '',
				'view_count'   => $item['statistics']['viewCount'] ?? 0,
			);
		}

		return $videos;
	}

	/**
	 * Schedule twice-daily cron job (every 12 hours)
	 */
	public static function schedule_refresh(): void {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		// If already scheduled, check if it's the correct schedule.
		if ( $timestamp ) {
			// Get the current schedule for this event.
			$cron = _get_cron_array();
			$schedule = '';
			if ( isset( $cron[ $timestamp ][ self::CRON_HOOK ] ) ) {
				$hook_data = $cron[ $timestamp ][ self::CRON_HOOK ];
				$schedule = reset( $hook_data )['schedule'] ?? '';
			}

			// If not twicedaily, clear and reschedule.
			if ( 'twicedaily' !== $schedule ) {
				wp_clear_scheduled_hook( self::CRON_HOOK );
				wp_schedule_event( time(), 'twicedaily', self::CRON_HOOK );
			}
		} else {
			// Not scheduled, schedule it now.
			wp_schedule_event( time(), 'twicedaily', self::CRON_HOOK );
		}
	}

	/**
	 * Refresh videos via cron job
	 */
	public static function refresh_videos(): void {
		// Delete transient to force fresh fetch.
		delete_transient( self::TRANSIENT_KEY );

		// Fetch and cache new videos.
		$videos = self::fetch_channel_videos();

		if ( ! empty( $videos ) ) {
			set_transient( self::TRANSIENT_KEY, $videos, self::CACHE_DURATION );

			// Cache thumbnails into media library (processes up to 5 per cycle).
			if ( class_exists( 'GCB_YouTube_Thumbnail_Cache' ) ) {
				// DISABLED: $cached = GCB_YouTube_Thumbnail_Cache::cache_thumbnails( $videos );
				if ( $cached > 0 ) {
					error_log( sprintf( 'GCB: Cached %d YouTube thumbnails into media library', $cached ) );
				}
			}
		}
	}

	/**
	 * Get ALL channel videos (for archive page)
	 *
	 * Fetches all videos with pagination support.
	 * Uses separate cache from the video rail (10 videos).
	 *
	 * @param int $max_videos Maximum videos to fetch (default 100, 0 = unlimited).
	 * @return array Array of video data or empty array on failure.
	 */
	public static function get_all_videos( int $max_videos = 100 ): array {
		// Step 1: Check transient cache for all videos.
		$cached_videos = get_transient( self::TRANSIENT_KEY_ALL );

		if ( false !== $cached_videos && is_array( $cached_videos ) ) {
			// Return cached videos, limited to max if specified
			if ( $max_videos > 0 && count( $cached_videos ) > $max_videos ) {
				return array_slice( $cached_videos, 0, $max_videos );
			}
			return $cached_videos;
		}

		// Step 2: Cache miss - fetch all videos with pagination.
		$videos = self::fetch_all_channel_videos( $max_videos );

		// Step 3: Store in transient (cache for 6 hours like regular videos).
		if ( ! empty( $videos ) ) {
			set_transient( self::TRANSIENT_KEY_ALL, $videos, self::CACHE_DURATION );
		}

		return $videos;
	}

	/**
	 * Fetch ALL videos from YouTube channel with pagination
	 *
	 * @param int $max_videos Maximum videos to fetch (0 = unlimited).
	 * @return array Array of video objects.
	 */
	private static function fetch_all_channel_videos( int $max_videos = 100 ): array {
		// Allow WordPress option to override test mode.
		$force_real_api = get_option( 'gcb_force_real_api', false );

		// Test mode: Return mock data (unless overridden).
		if ( ! $force_real_api && defined( 'GCB_TEST_KEY' ) && ! empty( GCB_TEST_KEY ) ) {
			$mock_videos = self::get_mock_videos();
			// For testing, duplicate mock videos to simulate more content
			if ( count( $mock_videos ) < $max_videos ) {
				$extended = array();
				$counter = 0;
				while ( count( $extended ) < min( $max_videos, 50 ) && $counter < 5 ) {
					foreach ( $mock_videos as $video ) {
						$extended[] = $video;
						if ( count( $extended ) >= min( $max_videos, 50 ) ) break;
					}
					$counter++;
				}
				return $extended;
			}
			return array_slice( $mock_videos, 0, $max_videos );
		}

		// Check API key.
		$api_key = self::get_api_key();
		if ( empty( $api_key ) ) {
			error_log( 'GCB: YouTube API key not configured' );
			return array();
		}

		// Step 1: Get channel's "uploads" playlist ID.
		$uploads_playlist_id = self::get_uploads_playlist_id();

		if ( empty( $uploads_playlist_id ) ) {
			return array();
		}

		// Step 2: Fetch ALL playlist items with pagination.
		$video_ids = self::fetch_all_playlist_items( $uploads_playlist_id, $max_videos );

		if ( empty( $video_ids ) ) {
			return array();
		}

		// Step 3: Fetch video details in batches (API limit is 50 per request).
		$all_videos = array();
		$chunks = array_chunk( $video_ids, 50 );

		foreach ( $chunks as $chunk ) {
			$videos = self::fetch_video_details( $chunk );
			$all_videos = array_merge( $all_videos, $videos );
		}

		return $all_videos;
	}

	/**
	 * Fetch ALL playlist items with pagination
	 *
	 * @param string $playlist_id Uploads playlist ID.
	 * @param int    $max_videos  Maximum videos to fetch (0 = unlimited).
	 * @return array Array of video IDs.
	 */
	private static function fetch_all_playlist_items( string $playlist_id, int $max_videos = 100 ): array {
		$video_ids = array();
		$page_token = '';
		$pages_fetched = 0;
		$max_pages = 10; // Safety limit to prevent infinite loops

		do {
			$args = array(
				'part'       => 'contentDetails',
				'playlistId' => $playlist_id,
				'maxResults' => self::MAX_RESULTS_PER_PAGE,
				'key'        => self::get_api_key(),
			);

			if ( ! empty( $page_token ) ) {
				$args['pageToken'] = $page_token;
			}

			$api_url = add_query_arg( $args, self::PLAYLIST_ENDPOINT );

			$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

			if ( is_wp_error( $response ) ) {
				error_log( 'GCB: YouTube API error - ' . $response->get_error_message() );
				break;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				error_log( 'GCB: YouTube API JSON parse error - ' . json_last_error_msg() );
				break;
			}

			if ( empty( $data['items'] ) ) {
				break;
			}

			foreach ( $data['items'] as $item ) {
				$video_id = $item['contentDetails']['videoId'] ?? '';
				if ( ! empty( $video_id ) ) {
					$video_ids[] = $video_id;

					// Check if we've reached the max
					if ( $max_videos > 0 && count( $video_ids ) >= $max_videos ) {
						return $video_ids;
					}
				}
			}

			// Get next page token
			$page_token = $data['nextPageToken'] ?? '';
			$pages_fetched++;

		} while ( ! empty( $page_token ) && $pages_fetched < $max_pages );

		return $video_ids;
	}

	/**
	 * Clear scheduled refresh on deactivation
	 */
	public static function clear_scheduled_refresh(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
		delete_transient( self::TRANSIENT_KEY );
		delete_transient( self::TRANSIENT_KEY_ALL );
	}

	/**
	 * Get mock videos for testing
	 *
	 * @return array Mock video data.
	 */
	private static function get_mock_videos(): array {
		$mock_file = dirname( __DIR__ ) . '/../gcb-test-utils/includes/class-gcb-youtube-mock-data.php';

		if ( file_exists( $mock_file ) ) {
			require_once $mock_file;

			if ( class_exists( 'GCB_YouTube_Mock_Data' ) ) {
				return GCB_YouTube_Mock_Data::get_channel_videos();
			}
		}

		// Fallback if mock file not found.
		return array();
	}

	/**
	 * Manually clear cache (useful for debugging)
	 */
	public static function clear_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
		delete_transient( self::TRANSIENT_KEY_ALL );
	}
}

// Initialize hooks.
GCB_YouTube_Channel_Fetcher::init();
