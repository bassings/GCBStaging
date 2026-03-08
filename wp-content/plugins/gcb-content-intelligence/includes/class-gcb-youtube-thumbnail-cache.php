<?php
/**
 * GCB YouTube Thumbnail Cache
 *
 * Downloads YouTube thumbnails into the WordPress media library, running them
 * through the theme's image pipeline (WebP conversion, physical thumbnails
 * at 150/300/400/768/1024/1200px). Eliminates the third-party connection to
 * i.ytimg.com and serves optimised WebP via Photon CDN.
 *
 * Thumbnails are cached by video ID. If the attachment is deleted from the
 * media library, it will be re-downloaded on the next fetch cycle.
 *
 * @package GCB_Content_Intelligence
 * @since 1.2.0
 */

declare(strict_types=1);

class GCB_YouTube_Thumbnail_Cache {

	/**
	 * Option key for the video_id → attachment_id mapping.
	 */
	private const OPTION_KEY = 'gcb_youtube_thumbnail_map';

	/**
	 * Get the local attachment ID for a YouTube video thumbnail.
	 *
	 * Returns the cached attachment ID if it exists and the file is still valid.
	 * Returns 0 if no cache exists (caller should use the original YouTube URL).
	 *
	 * @param string $video_id YouTube video ID.
	 * @return int Attachment ID, or 0 if not cached.
	 */
	public static function get_attachment_id( string $video_id ): int {
		$map = get_option( self::OPTION_KEY, array() );

		if ( empty( $map[ $video_id ] ) ) {
			return 0;
		}

		$attachment_id = (int) $map[ $video_id ];

		// Verify the attachment still exists (user may have deleted it).
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			// Clean up stale entry.
			unset( $map[ $video_id ] );
			update_option( self::OPTION_KEY, $map, false );
			return 0;
		}

		return $attachment_id;
	}

	/**
	 * Get the local thumbnail URL for a YouTube video.
	 *
	 * Returns the WordPress attachment URL if cached, or the original
	 * YouTube thumbnail URL as fallback.
	 *
	 * @param string $video_id     YouTube video ID.
	 * @param string $youtube_url  Original YouTube thumbnail URL (fallback).
	 * @param string $size         WordPress image size (default 'gcb-card').
	 * @return string Image URL.
	 */
	public static function get_thumbnail_url( string $video_id, string $youtube_url, string $size = 'gcb-card' ): string {
		$attachment_id = self::get_attachment_id( $video_id );

		if ( 0 === $attachment_id ) {
			return $youtube_url;
		}

		$url = wp_get_attachment_image_url( $attachment_id, $size );

		return $url ?: $youtube_url;
	}

	/**
	 * Get a full img tag with srcset for a cached YouTube thumbnail.
	 *
	 * @param string $video_id     YouTube video ID.
	 * @param string $youtube_url  Original YouTube thumbnail URL (fallback).
	 * @param string $alt          Alt text for the image.
	 * @param string $size         WordPress image size (default 'gcb-card').
	 * @return array{url: string, srcset: string, attachment_id: int} Image data.
	 */
	public static function get_thumbnail_data( string $video_id, string $youtube_url, string $alt = '', string $size = 'gcb-card' ): array {
		$attachment_id = self::get_attachment_id( $video_id );

		if ( 0 === $attachment_id ) {
			return array(
				'url'           => $youtube_url,
				'srcset'        => '',
				'attachment_id' => 0,
			);
		}

		$url = wp_get_attachment_image_url( $attachment_id, $size );

		// Use the theme's Photon srcset generator if available.
		$srcset = '';
		if ( function_exists( 'gcb_photon_srcset' ) ) {
			$srcset = gcb_photon_srcset( $attachment_id, array( 300, 400, 768 ) );
		} else {
			$srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
		}

		return array(
			'url'           => $url ?: $youtube_url,
			'srcset'        => $srcset ?: '',
			'attachment_id' => $attachment_id,
		);
	}

	/**
	 * Cache thumbnails for an array of videos.
	 *
	 * Processes videos that don't already have cached thumbnails.
	 * Designed to be called during the cron refresh cycle.
	 *
	 * @param array $videos Array of video data from GCB_YouTube_Channel_Fetcher.
	 * @return int Number of new thumbnails cached.
	 */
	public static function cache_thumbnails( array $videos ): int {
		// Require media functions for sideload.
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$map     = get_option( self::OPTION_KEY, array() );
		$cached  = 0;
		$updated = false;

		foreach ( $videos as $video ) {
			$video_id = $video['video_id'] ?? '';
			if ( empty( $video_id ) ) {
				continue;
			}

			// Skip if already cached and attachment still exists.
			if ( ! empty( $map[ $video_id ] ) && wp_attachment_is_image( (int) $map[ $video_id ] ) ) {
				continue;
			}

			// Build the best available thumbnail URL.
			// Prefer maxresdefault (1280x720) for quality, fall back to hqdefault (480x360).
			$thumbnail_url = "https://i.ytimg.com/vi/{$video_id}/maxresdefault.jpg";

			// Check if maxresdefault exists (not all videos have it).
			$check = wp_remote_head( $thumbnail_url, array( 'timeout' => 3 ) );
			if ( is_wp_error( $check ) || 200 !== wp_remote_retrieve_response_code( $check ) ) {
				$thumbnail_url = "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg";
			}

			// Generate alt text from video title.
			$title = $video['title'] ?? "YouTube video {$video_id}";
			$alt   = sprintf( 'Thumbnail for %s', $title );

			// Sideload into media library.
			// media_sideload_image returns attachment ID when $return = 'id'.
			$attachment_id = media_sideload_image( $thumbnail_url, 0, $title, 'id' );

			if ( is_wp_error( $attachment_id ) ) {
				error_log( sprintf( 'GCB: Failed to cache YouTube thumbnail for %s: %s', $video_id, $attachment_id->get_error_message() ) );
				continue;
			}

			// Set alt text.
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );

			// Tag the attachment so we can identify YouTube thumbnails.
			update_post_meta( $attachment_id, '_gcb_youtube_video_id', $video_id );
			update_post_meta( $attachment_id, '_gcb_youtube_thumbnail', '1' );

			// Update the mapping.
			$map[ $video_id ] = $attachment_id;
			$updated = true;
			$cached++;

			// Rate limit: don't hammer YouTube in one go.
			if ( $cached >= 5 ) {
				break;
			}
		}

		if ( $updated ) {
			update_option( self::OPTION_KEY, $map, false );
		}

		return $cached;
	}

	/**
	 * Purge all cached thumbnails from the media library.
	 *
	 * @param bool $delete_files Also delete the attachment files (default true).
	 * @return int Number of attachments deleted.
	 */
	public static function purge_all( bool $delete_files = true ): int {
		$map     = get_option( self::OPTION_KEY, array() );
		$deleted = 0;

		foreach ( $map as $video_id => $attachment_id ) {
			if ( $delete_files ) {
				wp_delete_attachment( (int) $attachment_id, true );
			}
			$deleted++;
		}

		delete_option( self::OPTION_KEY );

		return $deleted;
	}

	/**
	 * Get stats about the thumbnail cache.
	 *
	 * @return array{total: int, valid: int, stale: int}
	 */
	public static function get_stats(): array {
		$map   = get_option( self::OPTION_KEY, array() );
		$valid = 0;
		$stale = 0;

		foreach ( $map as $video_id => $attachment_id ) {
			if ( wp_attachment_is_image( (int) $attachment_id ) ) {
				$valid++;
			} else {
				$stale++;
			}
		}

		return array(
			'total' => count( $map ),
			'valid' => $valid,
			'stale' => $stale,
		);
	}
}
