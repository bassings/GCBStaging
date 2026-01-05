<?php
/**
 * GCB YouTube Mock Data
 *
 * Provides mock YouTube channel data for E2E tests.
 * Prevents real YouTube API calls during testing.
 *
 * @package GCB_Test_Utils
 * @since 1.1.0
 */

declare(strict_types=1);

/**
 * Class GCB_YouTube_Mock_Data
 *
 * Returns mock video data matching the structure of GCB_YouTube_Channel_Fetcher::get_videos().
 */
class GCB_YouTube_Mock_Data {

	/**
	 * Get mock channel videos
	 *
	 * Returns array matching GCB_YouTube_Channel_Fetcher::get_videos() format.
	 * Provides 10 videos for testing horizontal scroll, pagination, and metadata display.
	 *
	 * @return array Array of video objects
	 */
	public static function get_channel_videos(): array {
		return array(
			array(
				'video_id'     => 'dQw4w9WgXcQ',
				'title'        => '2025 Porsche 911 GT3 RS: Track Day Monster',
				'description'  => 'We take the latest GT3 RS to the Nürburgring for a proper shakedown. Is this the ultimate driver car?',
				'thumbnail'    => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
				'published_at' => '2025-01-05T10:30:00Z',
				'duration'     => 'PT12M45S',
				'view_count'   => 245678,
			),
			array(
				'video_id'     => 'jNQXAC9IVRw',
				'title'        => 'Ferrari SF90 vs McLaren Artura: Hybrid Supercar Showdown',
				'description'  => 'Which hybrid hypercar reigns supreme? We test both back-to-back on the same day.',
				'thumbnail'    => 'https://img.youtube.com/vi/jNQXAC9IVRw/maxresdefault.jpg',
				'published_at' => '2025-01-04T14:20:00Z',
				'duration'     => 'PT8M33S',
				'view_count'   => 189234,
			),
			array(
				'video_id'     => 'kJQP7kiw5Fk',
				'title'        => 'Lamborghini Revuelto: First Drive Review',
				'description'  => 'Lamborghini first plug-in hybrid V12 supercar. The future of Sant Agata is electric.',
				'thumbnail'    => 'https://img.youtube.com/vi/kJQP7kiw5Fk/maxresdefault.jpg',
				'published_at' => '2025-01-03T09:15:00Z',
				'duration'     => 'PT15M22S',
				'view_count'   => 567890,
			),
			array(
				'video_id'     => 'fJ9rUzIMcZQ',
				'title'        => 'BMW M4 CSL vs Mercedes-AMG GT Black Series',
				'description'  => 'German engineering at its finest. Two track-focused beasts go head-to-head.',
				'thumbnail'    => 'https://img.youtube.com/vi/fJ9rUzIMcZQ/maxresdefault.jpg',
				'published_at' => '2025-01-02T11:45:00Z',
				'duration'     => 'PT10M18S',
				'view_count'   => 298765,
			),
			array(
				'video_id'     => 'DLzxrzFCyOs',
				'title'        => 'Audi RS e-tron GT: Electric Performance Redefined',
				'description'  => 'Can electric match petrol for driving thrills? We find out in Audi most powerful sedan.',
				'thumbnail'    => 'https://img.youtube.com/vi/DLzxrzFCyOs/maxresdefault.jpg',
				'published_at' => '2025-01-01T16:30:00Z',
				'duration'     => 'PT11M52S',
				'view_count'   => 412389,
			),
			array(
				'video_id'     => '9bZkp7q19f0',
				'title'        => 'Bentley Continental GT Speed: Grand Touring Perfection',
				'description'  => 'Luxury and performance in perfect harmony. Is this the ultimate GT car for 2025?',
				'thumbnail'    => 'https://img.youtube.com/vi/9bZkp7q19f0/maxresdefault.jpg',
				'published_at' => '2024-12-30T08:00:00Z',
				'duration'     => 'PT14M07S',
				'view_count'   => 178456,
			),
			array(
				'video_id'     => 'astISOttCQ0',
				'title'        => 'Porsche Taycan Turbo S: Fastest EV Sedan?',
				'description'  => 'Testing Porsche electric performance claims. Does it live up to the hype?',
				'thumbnail'    => 'https://img.youtube.com/vi/astISOttCQ0/maxresdefault.jpg',
				'published_at' => '2024-12-28T12:30:00Z',
				'duration'     => 'PT9M41S',
				'view_count'   => 325678,
			),
			array(
				'video_id'     => 'lDK9QqIzhwk',
				'title'        => 'Aston Martin DBS 770 Ultimate: V12 Swan Song',
				'description'  => 'Final edition of the iconic V12 grand tourer. The end of an era for Aston Martin.',
				'thumbnail'    => 'https://img.youtube.com/vi/lDK9QqIzhwk/maxresdefault.jpg',
				'published_at' => '2024-12-26T10:15:00Z',
				'duration'     => 'PT13M29S',
				'view_count'   => 456123,
			),
			array(
				'video_id'     => 'YQHsXMglC9A',
				'title'        => 'Maserati MC20: Italian Exotic with Supercar Performance',
				'description'  => 'Maserati return to the supercar game. Does the MC20 have what it takes?',
				'thumbnail'    => 'https://img.youtube.com/vi/YQHsXMglC9A/maxresdefault.jpg',
				'published_at' => '2024-12-24T14:00:00Z',
				'duration'     => 'PT11M15S',
				'view_count'   => 234567,
			),
			array(
				'video_id'     => 'gCYcHz2k5x0',
				'title'        => 'Mercedes-AMG SL 63: Modern Roadster Icon',
				'description'  => 'The SL reinvented for a new generation. Classic looks meet modern performance.',
				'thumbnail'    => 'https://img.youtube.com/vi/gCYcHz2k5x0/maxresdefault.jpg',
				'published_at' => '2024-12-22T09:30:00Z',
				'duration'     => 'PT10M44S',
				'view_count'   => 189345,
			),
		);
	}

	/**
	 * Get single mock video by ID
	 *
	 * @param string $video_id YouTube video ID.
	 * @return array|null Video object or null if not found.
	 */
	public static function get_video_by_id( string $video_id ): ?array {
		$videos = self::get_channel_videos();

		foreach ( $videos as $video ) {
			if ( $video['video_id'] === $video_id ) {
				return $video;
			}
		}

		return null;
	}

	/**
	 * Get empty response (simulates API failure)
	 *
	 * @return array Empty array.
	 */
	public static function get_empty_response(): array {
		return array();
	}
}
