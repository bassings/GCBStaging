

<?php
/**
 * Title: Video Rail
 * Slug: gcb-brutalist/video-rail
 * Categories: featured, gcb-content
 * Description: Horizontal scrolling rail of video posts with Editorial Brutalism styling
 * Keywords: video, rail, horizontal, scroll, brutalism
 */

// Query video posts from content_format taxonomy
$video_posts = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'tax_query'      => array(
			array(
				'taxonomy' => 'content_format',
				'field'    => 'slug',
				'terms'    => array( 'video-quick', 'video-feature' ),
			),
		),
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( ! $video_posts->have_posts() ) {
	return;
}
?>

<!-- wp:group {"className":"gcb-video-rail","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"top":"0","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group gcb-video-rail" data-pattern="video-rail" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"level":2,"style":{"typography":{"fontFamily":"var(--wp--preset--font-family--playfair)","fontSize":"2.5rem","lineHeight":"1.2"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}},"color":{"text":"var:preset|color|acid-lime"}}} -->
	<h2 class="wp-block-heading has-acid-lime-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--40);font-family:var(--wp--preset--font-family--playfair);font-size:2.5rem;line-height:1.2">Latest Videos</h2>
	<!-- /wp:heading -->

	<!-- wp:separator {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}},"color":{"background":"var:preset|color|acid-lime"}},"backgroundColor":"acid-lime","className":"is-style-wide"} -->
	<hr class="wp-block-separator has-text-color has-acid-lime-background-color has-alpha-channel-opacity has-acid-lime-background-color has-background is-style-wide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40);color:var(--wp--preset--color--acid-lime);background-color:var(--wp--preset--color--acid-lime)"/>
	<!-- /wp:separator -->

	<!-- Custom Scrollbar Styling -->
	<style>
		.gcb-video-rail__container::-webkit-scrollbar {
			height: 6px;
		}
		.gcb-video-rail__container::-webkit-scrollbar-track {
			background: var(--wp--preset--color--void-black);
		}
		.gcb-video-rail__container::-webkit-scrollbar-thumb {
			background: var(--wp--preset--color--brutal-border);
			border: 1px solid var(--wp--preset--color--acid-lime);
		}
		.gcb-video-rail__container::-webkit-scrollbar-thumb:hover {
			background: var(--wp--preset--color--acid-lime);
		}
	</style>

	<!-- Video Rail Scroll Container -->
	<div class="gcb-video-rail__container video-rail-scroll" style="display: flex; gap: 2rem; overflow-x: auto; overflow-y: hidden; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; padding-bottom: 1rem;">

		<?php
		while ( $video_posts->have_posts() ) :
			$video_posts->the_post();
			$post_id        = get_the_ID();
			$video_id       = get_post_meta( $post_id, '_gcb_video_id', true );
			$metadata_json  = get_post_meta( $post_id, '_gcb_video_metadata', true );
			$metadata       = ! empty( $metadata_json ) ? json_decode( $metadata_json, true ) : array();
			$duration       = $metadata['duration'] ?? '';
			$view_count     = $metadata['viewCount'] ?? '';
			$thumbnail      = $video_id ? "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg" : get_the_post_thumbnail_url( $post_id, 'medium' );
			?>

			<!-- Video Card -->
			<div class="gcb-video-card video-rail-item" style="flex: 0 0 300px; scroll-snap-align: start; border: 2px solid var(--wp--preset--color--acid-lime); background: var(--wp--preset--color--void-black);">

				<!-- Thumbnail -->
				<a href="<?php echo esc_url( get_permalink() ); ?>" style="display: block; position: relative;">
					<?php if ( $thumbnail ) : ?>
						<img
							src="<?php echo esc_url( $thumbnail ); ?>"
							alt="<?php echo esc_attr( get_the_title() ); ?>"
							style="width: 100%; height: 169px; object-fit: cover; display: block; border-bottom: 2px solid var(--wp--preset--color--brutal-border); filter: grayscale(100%) contrast(1.3);"
							loading="lazy"
						/>
					<?php endif; ?>

					<!-- Duration Badge -->
					<?php if ( $duration ) : ?>
						<span class="gcb-video-card__duration video-duration" data-duration="<?php echo esc_attr( $duration ); ?>" style="position: absolute; bottom: 8px; right: 8px; background: rgba(5, 5, 5, 0.9); color: var(--wp--preset--color--hyper-white); padding: 4px 8px; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; border: 1px solid var(--wp--preset--color--acid-lime);">
							<?php echo esc_html( gcb_format_video_duration( $duration ) ); ?>
						</span>
					<?php endif; ?>
				</a>

				<!-- Card Content -->
				<div style="padding: 1rem;">
					<!-- Title -->
					<h3 class="gcb-video-card__title video-title" style="font-family: var(--wp--preset--font-family--playfair); font-size: 1.125rem; line-height: 1.3; margin: 0 0 0.5rem 0; color: var(--wp--preset--color--hyper-white);">
						<a href="<?php echo esc_url( get_permalink() ); ?>" style="color: inherit; text-decoration: none;">
							<?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?>
						</a>
					</h3>

					<!-- Metadata -->
					<div class="gcb-video-card__meta" style="display: flex; gap: 0.75rem; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey);">
						<!-- Post Date -->
						<time class="gcb-video-card__date video-date post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
						</time>

						<!-- View Count -->
						<?php if ( $view_count ) : ?>
							<span class="gcb-video-card__views">
								<?php echo esc_html( number_format( intval( $view_count ) ) ); ?> views
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>

		<?php endwhile; ?>

		<?php wp_reset_postdata(); ?>

	</div>

	<!-- Scroll Hint (mobile only) -->
	<p style="font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey); margin-top: 1rem; text-align: center;">
		← Scroll horizontally to see more →
	</p>

</div>
<!-- /wp:group -->

<?php
/**
 * Helper function to format video duration
 *
 * Converts ISO 8601 duration (PT3M33S) to human-readable format (3:33)
 *
 * @param string $duration ISO 8601 duration string
 * @return string Formatted duration
 */
function gcb_format_video_duration( string $duration ): string {
	// Parse ISO 8601 duration (PT3M33S)
	preg_match( '/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches );

	$hours   = isset( $matches[1] ) ? intval( $matches[1] ) : 0;
	$minutes = isset( $matches[2] ) ? intval( $matches[2] ) : 0;
	$seconds = isset( $matches[3] ) ? intval( $matches[3] ) : 0;

	if ( $hours > 0 ) {
		return sprintf( '%d:%02d:%02d', $hours, $minutes, $seconds );
	} else {
		return sprintf( '%d:%02d', $minutes, $seconds );
	}
}
?>
