<?php
/**
 * GCB Shortcode Converter
 *
 * Converts legacy Avada Fusion Builder shortcodes to WordPress core blocks.
 * Specifically handles [fusion_youtube] shortcode conversion to wp:embed blocks.
 *
 * @package GCB_Content_Intelligence
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Class GCB_Shortcode_Converter
 *
 * Handles one-time conversion of Avada shortcodes to WordPress blocks.
 */
class GCB_Shortcode_Converter {

    /**
     * Avada fusion_youtube shortcode pattern
     *
     * Matches: [fusion_youtube id="VIDEO_ID" ...other_params...]
     *
     * @var string
     */
    private const FUSION_YOUTUBE_PATTERN = '/\[fusion_youtube\s+id="([a-zA-Z0-9_-]{11})"[^\]]*\]/i';

    /**
     * Register post meta fields for conversion tracking
     *
     * @return void
     */
    public static function register_post_meta(): void {
        // Auth callback for protected meta fields - allow users who can edit posts
        $auth_callback = function( $allowed, $meta_key, $post_id ) {
            return current_user_can( 'edit_post', $post_id );
        };

        register_post_meta(
            'post',
            '_gcb_shortcode_converted',
            array(
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'string',
                'description'   => 'Timestamp when Avada shortcodes were converted to blocks',
                'auth_callback' => $auth_callback,
            )
        );

        register_post_meta(
            'post',
            '_gcb_original_content',
            array(
                'show_in_rest'  => false,
                'single'        => true,
                'type'          => 'string',
                'description'   => 'Backup of original content before shortcode conversion',
                'auth_callback' => $auth_callback,
            )
        );
    }

    /**
     * Convert Avada shortcodes to WordPress blocks
     *
     * This is a one-time conversion. Once converted, posts are flagged with
     * _gcb_shortcode_converted meta to prevent re-conversion.
     *
     * @param int $post_id Post ID
     * @return bool True if conversion occurred, false if skipped
     */
    public function convert_shortcodes( int $post_id ): bool {
        // Check if already converted
        $already_converted = get_post_meta( $post_id, '_gcb_shortcode_converted', true );
        if ( $already_converted ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return false;
        }

        // Check if post contains Avada shortcodes
        if ( ! $this->has_fusion_youtube_shortcode( $post->post_content ) ) {
            return false;
        }

        // Backup original content
        update_post_meta( $post_id, '_gcb_original_content', $post->post_content );

        // Convert fusion_youtube shortcodes to WordPress embed blocks
        $converted_content = $this->convert_fusion_youtube_to_embed( $post->post_content );

        // Update post content
        wp_update_post(
            array(
                'ID'           => $post_id,
                'post_content' => $converted_content,
            ),
            false, // Don't trigger wp_error
            false  // Don't fire additional hooks
        );

        // Mark as converted with timestamp
        update_post_meta( $post_id, '_gcb_shortcode_converted', gmdate( 'Y-m-d H:i:s' ) );

        return true;
    }

    /**
     * Check if content contains fusion_youtube shortcode
     *
     * @param string $content Post content
     * @return bool True if shortcode found
     */
    private function has_fusion_youtube_shortcode( string $content ): bool {
        return (bool) preg_match( self::FUSION_YOUTUBE_PATTERN, $content );
    }

    /**
     * Convert fusion_youtube shortcodes to WordPress embed blocks
     *
     * Converts:
     * [fusion_youtube id="dQw4w9WgXcQ" width="600" height="350"]
     *
     * To:
     * <!-- wp:embed {"url":"https://www.youtube.com/watch?v=dQw4w9WgXcQ","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
     * <figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
     * <div class="wp-block-embed__wrapper">
     * https://www.youtube.com/watch?v=dQw4w9WgXcQ
     * </div>
     * </figure>
     * <!-- /wp:embed -->
     *
     * @param string $content Post content
     * @return string Converted content
     */
    private function convert_fusion_youtube_to_embed( string $content ): string {
        return preg_replace_callback(
            self::FUSION_YOUTUBE_PATTERN,
            function ( array $matches ): string {
                $video_id = $matches[1];
                $youtube_url = "https://www.youtube.com/watch?v={$video_id}";

                // Generate WordPress embed block markup
                $embed_block = $this->generate_embed_block( $youtube_url, $video_id );

                return $embed_block;
            },
            $content
        );
    }

    /**
     * Generate WordPress embed block markup
     *
     * @param string $youtube_url Full YouTube URL
     * @param string $video_id    YouTube video ID
     * @return string WordPress embed block HTML
     */
    private function generate_embed_block( string $youtube_url, string $video_id ): string {
        // Block comment with JSON attributes
        $block_attrs = wp_json_encode(
            array(
                'url'              => $youtube_url,
                'type'             => 'video',
                'providerNameSlug' => 'youtube',
                'responsive'       => true,
                'className'        => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
            )
        );

        // Generate complete embed block
        $embed_block = sprintf(
            '<!-- wp:embed %s -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
<div class="wp-block-embed__wrapper">
%s
</div>
</figure>
<!-- /wp:embed -->',
            $block_attrs,
            esc_url( $youtube_url )
        );

        return $embed_block;
    }

    /**
     * Get conversion statistics for a post
     *
     * @param int $post_id Post ID
     * @return array{converted: bool, timestamp: string|null, backup_exists: bool}
     */
    public function get_conversion_stats( int $post_id ): array {
        $converted_timestamp = get_post_meta( $post_id, '_gcb_shortcode_converted', true );
        $backup_content      = get_post_meta( $post_id, '_gcb_original_content', true );

        return array(
            'converted'      => ! empty( $converted_timestamp ),
            'timestamp'      => $converted_timestamp ?: null,
            'backup_exists'  => ! empty( $backup_content ),
        );
    }
}
