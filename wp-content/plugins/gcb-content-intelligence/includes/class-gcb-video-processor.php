<?php
/**
 * GCB Video Processor
 *
 * Fetches video metadata from YouTube Data API v3 and caches results.
 * Provides graceful degradation if API fails or quota is exceeded.
 *
 * @package GCB_Content_Intelligence
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Video_Processor
 *
 * Handles YouTube API integration for video metadata fetching.
 */
class GCB_Video_Processor {

	/**
	 * YouTube Data API v3 endpoint
	 *
	 * @var string
	 */
	private const YOUTUBE_API_URL = 'https://www.googleapis.com/youtube/v3/videos';

	/**
	 * Cache duration (24 hours in seconds)
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 86400;

	/**
	 * Register post meta fields for video metadata
	 *
	 * @return void
	 */
	public static function register_post_meta(): void {
		register_post_meta(
			'post',
			'_gcb_video_metadata',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'description'  => 'Cached YouTube video metadata (JSON)',
			)
		);

		register_post_meta(
			'post',
			'_gcb_api_cache_time',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'description'  => 'Timestamp when video metadata was cached',
			)
		);
	}

	/**
	 * Fetch video metadata for a post
	 *
	 * Checks cache first. If cache is fresh, returns cached data.
	 * Otherwise, fetches fresh data from YouTube API.
	 *
	 * @param int    $post_id  Post ID
	 * @param string $video_id YouTube video ID
	 * @return bool True if metadata was fetched/cached, false otherwise
	 */
	public function fetch_video_metadata( int $post_id, string $video_id ): bool {
		// Step 1: Check if we have cached metadata
		if ( $this->has_valid_cache( $post_id ) ) {
			return true;
		}

		// Step 2: Fetch fresh metadata from YouTube API
		$metadata = $this->fetch_from_youtube_api( $video_id );

		// Step 3: Store metadata (even if empty, to prevent repeated API calls)
		update_post_meta( $post_id, '_gcb_video_metadata', wp_json_encode( $metadata ) );
		update_post_meta( $post_id, '_gcb_api_cache_time', (string) time() );

		return ! empty( $metadata );
	}

	/**
	 * Check if post has valid cached metadata
	 *
	 * @param int $post_id Post ID
	 * @return bool True if cache is fresh (< 24 hours old)
	 */
	private function has_valid_cache( int $post_id ): bool {
		$cache_time = get_post_meta( $post_id, '_gcb_api_cache_time', true );

		if ( empty( $cache_time ) ) {
			return false;
		}

		$cache_age = time() - (int) $cache_time;

		return $cache_age < self::CACHE_DURATION;
	}

	/**
	 * Fetch video metadata from YouTube Data API v3
	 *
	 * @param string $video_id YouTube video ID
	 * @return array Video metadata or empty array on failure
	 */
	private function fetch_from_youtube_api( string $video_id ): array {
		// Test mode: Return mock data for known test video IDs
		if ( defined( 'GCB_TEST_KEY' ) && ! empty( GCB_TEST_KEY ) ) {
			return $this->get_mock_metadata( $video_id );
		}

		// Check if API key is configured
		if ( ! defined( 'GCB_YOUTUBE_API_KEY' ) || empty( GCB_YOUTUBE_API_KEY ) ) {
			return array( 'error' => 'YouTube API key not configured' );
		}

		// Build API request URL
		$api_url = add_query_arg(
			array(
				'id'   => $video_id,
				'key'  => GCB_YOUTUBE_API_KEY,
				'part' => 'snippet,contentDetails',
			),
			self::YOUTUBE_API_URL
		);

		// Make API request
		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		// Handle request errors
		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}

		// Parse response
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check if API returned an error
		if ( isset( $data['error'] ) ) {
			return array( 'error' => $data['error']['message'] ?? 'Unknown API error' );
		}

		// Check if video exists in response
		if ( empty( $data['items'] ) ) {
			return array( 'error' => 'Video not found' );
		}

		// Extract metadata
		$video_data = $data['items'][0];

		return array(
			'title'        => $video_data['snippet']['title'] ?? '',
			'description'  => $video_data['snippet']['description'] ?? '',
			'thumbnailUrl' => $video_data['snippet']['thumbnails']['high']['url'] ?? '',
			'uploadDate'   => $video_data['snippet']['publishedAt'] ?? '',
			'duration'     => $video_data['contentDetails']['duration'] ?? '',
		);
	}

	/**
	 * Get cached metadata for a post
	 *
	 * @param int $post_id Post ID
	 * @return array|null Metadata array or null if not cached
	 */
	public function get_cached_metadata( int $post_id ): ?array {
		$metadata_json = get_post_meta( $post_id, '_gcb_video_metadata', true );

		if ( empty( $metadata_json ) ) {
			return null;
		}

		$metadata = json_decode( $metadata_json, true );

		return is_array( $metadata ) ? $metadata : null;
	}

	/**
	 * Clear cached metadata for a post
	 *
	 * @param int $post_id Post ID
	 * @return bool True if cache was cleared
	 */
	public function clear_cache( int $post_id ): bool {
		delete_post_meta( $post_id, '_gcb_video_metadata' );
		delete_post_meta( $post_id, '_gcb_api_cache_time' );

		return true;
	}

	/**
	 * Get mock metadata for testing
	 *
	 * Returns fake video metadata for known test video IDs.
	 * Used in test environments to avoid real YouTube API calls.
	 *
	 * @param string $video_id YouTube video ID
	 * @return array Mock metadata or error array
	 */
	private function get_mock_metadata( string $video_id ): array {
		// Mock data for common test video IDs
		$mock_data = array(
			'dQw4w9WgXcQ'  => array(
				'title'        => 'Rick Astley - Never Gonna Give You Up (Official Video)',
				'description'  => 'The official video for Never Gonna Give You Up by Rick Astley',
				'thumbnailUrl' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
				'uploadDate'   => '2009-10-25T06:57:33Z',
				'duration'     => 'PT3M33S',
			),
			'jNQXAC9IVRw'  => array(
				'title'        => 'Me at the zoo',
				'description'  => 'The first video on YouTube',
				'thumbnailUrl' => 'https://i.ytimg.com/vi/jNQXAC9IVRw/hqdefault.jpg',
				'uploadDate'   => '2005-04-24T03:31:52Z',
				'duration'     => 'PT19S',
			),
			'INVALID_ID'   => array(
				'error' => 'Video not found',
			),
		);

		return $mock_data[ $video_id ] ?? array( 'error' => 'Mock data not available for this video ID' );
	}
}
