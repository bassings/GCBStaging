<?php
/**
 * GCB Content Detector
 *
 * Detects YouTube URLs in post content and classifies content format.
 * Extracts video IDs and stores them in post meta.
 *
 * @package GCB_Content_Intelligence
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Content_Detector
 *
 * Handles content format detection and classification.
 */
class GCB_Content_Detector {

    /**
     * YouTube URL regex patterns
     *
     * Supports multiple YouTube URL formats:
     * - https://www.youtube.com/watch?v=VIDEO_ID
     * - https://youtu.be/VIDEO_ID
     * - https://www.youtube.com/embed/VIDEO_ID
     *
     * @var array<string>
     */
    private const YOUTUBE_PATTERNS = array(
        '#https?://(?:www\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})#i',
        '#https?://youtu\.be/([a-zA-Z0-9_-]{11})#i',
        '#https?://(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{11})#i',
    );

    /**
     * Register post meta fields
     *
     * Makes video-related metadata available via REST API.
     *
     * @return void
     */
    public static function register_post_meta(): void {
        // Video ID
        register_post_meta(
            'post',
            '_gcb_video_id',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'description'  => 'YouTube video ID extracted from content',
            )
        );

        // Content format (for caching)
        register_post_meta(
            'post',
            '_gcb_content_format',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'description'  => 'Detected content format (video/standard/gallery)',
            )
        );

        // Video metadata (for future YouTube API integration)
        register_post_meta(
            'post',
            '_gcb_video_duration',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'description'  => 'Video duration in ISO 8601 format (e.g., PT12M45S)',
            )
        );

        register_post_meta(
            'post',
            '_gcb_video_views',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'integer',
                'description'  => 'YouTube video view count',
            )
        );

        register_post_meta(
            'post',
            '_gcb_video_upload_date',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'description'  => 'Video upload date in ISO 8601 format',
            )
        );
    }

    /**
     * Extract video metadata on post save
     *
     * WordPress save_post hook callback that extracts video IDs from post content.
     * Runs with priority 5 (before content classifier at priority 10).
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated.
     * @return void
     */
    public static function extract_video_metadata( int $post_id, WP_Post $post, bool $update ): void {
        // Skip autosaves and revisions
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Only process 'post' post type
        if ( 'post' !== $post->post_type ) {
            return;
        }

        // Create detector instance and analyze content
        $detector = new self();
        $content_format = $detector->detect_content_format( $post_id );

        // Store detected content format in post meta
        update_post_meta( $post_id, '_gcb_content_format', $content_format );
    }

    /**
     * Detect content format for a post
     *
     * Analyzes post content and returns the appropriate content format.
     *
     * @param int $post_id Post ID
     * @return string Content format: 'video', 'standard', or 'gallery'
     */
    public function detect_content_format( int $post_id ): string {
        $post = get_post( $post_id );

        if ( ! $post instanceof WP_Post ) {
            return 'standard';
        }

        // Check for YouTube URLs
        if ( $this->has_youtube_url( $post->post_content ) ) {
            $video_id = $this->extract_video_id( $post->post_content );
            if ( $video_id ) {
                update_post_meta( $post_id, '_gcb_video_id', $video_id );
                update_post_meta( $post_id, '_gcb_has_video', 1 );
            }
            return 'video';
        }

        // Future: Check for image galleries
        // if ( $this->has_gallery( $post->post_content ) ) {
        //     return 'gallery';
        // }

        return 'standard';
    }

    /**
     * Check if content contains YouTube URL
     *
     * @param string $content Post content
     * @return bool True if YouTube URL found
     */
    private function has_youtube_url( string $content ): bool {
        foreach ( self::YOUTUBE_PATTERNS as $pattern ) {
            if ( preg_match( $pattern, $content ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract YouTube video ID from content
     *
     * Returns the first video ID found in content.
     *
     * @param string $content Post content
     * @return string|null Video ID or null if not found
     */
    private function extract_video_id( string $content ): ?string {
        foreach ( self::YOUTUBE_PATTERNS as $pattern ) {
            if ( preg_match( $pattern, $content, $matches ) ) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Get all YouTube video IDs from content
     *
     * Returns all video IDs if multiple videos are embedded.
     *
     * @param string $content Post content
     * @return array<string> Array of video IDs
     */
    public function get_all_video_ids( string $content ): array {
        $video_ids = array();

        foreach ( self::YOUTUBE_PATTERNS as $pattern ) {
            if ( preg_match_all( $pattern, $content, $matches ) ) {
                $video_ids = array_merge( $video_ids, $matches[1] );
            }
        }

        return array_unique( $video_ids );
    }
}
