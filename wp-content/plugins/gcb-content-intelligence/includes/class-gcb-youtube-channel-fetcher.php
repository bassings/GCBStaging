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
	private const CACHE_DURATION   = HOUR_IN_SECONDS; // 1 hour.

	/**
	 * Cron hook name
	 */
	private const CRON_HOOK = 'gcb_refresh_youtube_videos';

	/**
	 * Maximum videos to fetch
	 */
	private const MAX_RESULTS = 10;

	/**
	 * Register WordPress hooks
	 */
	public static function init(): void {
		// Schedule hourly cron job.
		add_action( 'init', array( __CLASS__, 'schedule_refresh' ) );

		// Hook cron job to fetch function.
		add_action( self::CRON_HOOK, array( __CLASS__, 'refresh_videos' ) );
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
	 * Get YouTube API key from wp-config.php or WordPress option
	 *
	 * Checks wp-config.php first (preferred), then falls back to
	 * WordPress option (gcb_youtube_api_key) for WordPress.com compatibility.
	 *
	 * @return string API key or empty string if not found.
	 */
	private static function get_api_key(): string {
		// Priority 1: Check wp-config.php constant.
		if ( defined( 'GCB_YOUTUBE_API_KEY' ) && ! empty( GCB_YOUTUBE_API_KEY ) ) {
			return GCB_YOUTUBE_API_KEY;
		}

		// Priority 2: Check WordPress option (for WordPress.com hosting).
		$api_key = get_option( 'gcb_youtube_api_key', '' );
		return $api_key;
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

		$response = wp_remote_get( $api_url, array( 'timeout' => 10 ) );

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

		$response = wp_remote_get( $api_url, array( 'timeout' => 10 ) );

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

		$response = wp_remote_get( $api_url, array( 'timeout' => 10 ) );

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
	 * Schedule hourly cron job
	 */
	public static function schedule_refresh(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
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
		}
	}

	/**
	 * Clear scheduled refresh on deactivation
	 */
	public static function clear_scheduled_refresh(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
		delete_transient( self::TRANSIENT_KEY );
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
	}
}

// Initialize hooks.
GCB_YouTube_Channel_Fetcher::init();
