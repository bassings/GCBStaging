

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

	<!-- Section Header -->
	<div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 2rem;">
		<h2 style="font-family: var(--wp--preset--font-family--playfair); font-size: 2.5rem; font-weight: bold; color: var(--wp--preset--color--off-white);">
			LATEST VIDEOS
		</h2>
		<a href="/video/" style="font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; text-transform: uppercase; color: var(--wp--preset--color--brutal-grey); text-decoration: none; min-height: 44px; display: flex; align-items: center;">
			View All →
		</a>
	</div>

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

		/* Video card hover effect */
		.gcb-video-card:hover {
			border-color: var(--wp--preset--color--acid-lime) !important;
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
			<div class="gcb-video-card video-rail-item" style="flex: 0 0 300px; scroll-snap-align: start; border: 1px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black);">

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

					<!-- Play Button Overlay -->
					<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center;">
						<svg class="video-play-button" style="width: 4rem; height: 4rem; color: var(--wp--preset--color--acid-lime); filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5)); pointer-events: none;" viewBox="0 0 100 100" fill="currentColor">
							<polygon points="30,20 30,80 80,50" />
						</svg>
					</div>

					<!-- Duration Badge -->
					<?php if ( $duration ) : ?>
						<span class="gcb-video-card__duration video-duration" data-duration="<?php echo esc_attr( $duration ); ?>" style="position: absolute; bottom: 8px; right: 8px; background: rgba(5, 5, 5, 0.9); color: var(--wp--preset--color--off-white); padding: 4px 8px; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; border: 1px solid var(--wp--preset--color--acid-lime);">
							<?php echo esc_html( gcb_format_video_duration( $duration ) ); ?>
						</span>
					<?php endif; ?>
				</a>

				<!-- Card Content -->
				<div style="padding: 1rem;">
					<!-- Title -->
					<h3 class="gcb-video-card__title video-title" style="font-family: var(--wp--preset--font-family--playfair); font-size: 1.125rem; line-height: 1.3; margin: 0 0 0.5rem 0; color: var(--wp--preset--color--off-white);">
						<a href="<?php echo esc_url( get_permalink() ); ?>" style="color: inherit; text-decoration: none;">
							<?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?>
						</a>
					</h3>

					<!-- Metadata -->
					<div class="gcb-video-card__meta" style="font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey); text-transform: uppercase;">
						<?php if ( $duration ) : ?>
							<span><?php echo esc_html( gcb_format_video_duration( $duration ) ); ?></span>
							<span> • </span>
						<?php endif; ?>
						<?php if ( $view_count ) : ?>
							<span><?php echo esc_html( gcb_format_view_count( intval( $view_count ) ) ); ?> Views</span>
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

/**
 * Helper function to format view count
 *
 * Converts large numbers to K/M format (e.g., 245000 → "245K")
 *
 * @param int $count View count
 * @return string Formatted view count
 */
function gcb_format_view_count( int $count ): string {
	if ( $count >= 1000000 ) {
		return round( $count / 1000000, 1 ) . 'M';
	} elseif ( $count >= 1000 ) {
		return round( $count / 1000 ) . 'K';
	}
	return number_format( $count );
}
?>
