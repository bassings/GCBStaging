<?php
/**
 * Title: Bento Grid
 * Slug: gcb-brutalist/bento-grid
 * Categories: featured, gcb-content
 * Description: Mixed layout grid combining video and standard posts with Editorial Brutalism styling
 * Keywords: bento, grid, mixed, layout, brutalism
 */

// Cache key for transient (invalidates when posts are updated)
$cache_key   = 'gcb_bento_grid_' . date( 'Y-m-d-H' ); // Hourly cache.
$grid_posts  = get_transient( $cache_key );

if ( false === $grid_posts ) {
	// Query all recent posts (mixed video and standard)
	$grid_posts = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	// Cache for 1 hour (reduce DB load).
	set_transient( $cache_key, $grid_posts, HOUR_IN_SECONDS );
}

if ( ! $grid_posts->have_posts() ) {
	return;
}
?>

<!-- wp:group {"className":"gcb-bento-grid","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group gcb-bento-grid" data-pattern="bento-grid" style="margin-bottom:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"level":2,"style":{"typography":{"fontFamily":"var(--wp--preset--font-family--playfair)","fontSize":"2.5rem","lineHeight":"1.2"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}},"color":{"text":"var:preset|color|off-white"}}} -->
	<h2 class="wp-block-heading has-off-white-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--40);font-family:var(--wp--preset--font-family--playfair);font-size:2.5rem;line-height:1.2">FEATURED STORIES</h2>
	<!-- /wp:heading -->

	<!-- wp:separator {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}},"color":{"background":"var:preset|color|brutal-border"}},"backgroundColor":"brutal-border","className":"is-style-wide"} -->
	<hr class="wp-block-separator has-text-color has-brutal-border-background-color has-alpha-channel-opacity has-brutal-border-background-color has-background is-style-wide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40);color:var(--wp--preset--color--brutal-border);background-color:var(--wp--preset--color--brutal-border)"/>
	<!-- /wp:separator -->

	<!-- Bento Grid Container -->
	<div class="gcb-bento-grid__container bento-grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; align-items: stretch;">

		<?php
		$index = 0;
		while ( $grid_posts->have_posts() ) :
			$grid_posts->the_post();
			$post_id = get_the_ID();

			// Determine card size (featured vs standard)
			// First item is featured (spans 2 columns on desktop)
			$is_featured = ( 0 === $index );
			$size_class  = $is_featured ? 'bento-item--featured bento-item--large' : '';
			$grid_span   = $is_featured ? 'grid-column: span 2;' : '';

			// Get thumbnail with dimensions for CLS prevention.
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$thumbnail    = get_the_post_thumbnail_url( $post_id, 'large' );
			$srcset       = $thumbnail_id ? wp_get_attachment_image_srcset( $thumbnail_id, 'large' ) : '';
			$sizes        = $is_featured ? '(max-width: 768px) 100vw, 66vw' : '(max-width: 768px) 100vw, 33vw';
			?>

			<!-- Bento Grid Item -->
			<div class="bento-item gcb-bento-card bento-item--standard <?php echo esc_attr( $size_class ); ?>" data-size="<?php echo $is_featured ? 'large' : 'standard'; ?>" style="<?php echo esc_attr( $grid_span ); ?> border: 2px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black); overflow: hidden; display: flex; flex-direction: column; height: 100%;">

				<!-- Thumbnail -->
				<?php if ( $thumbnail ) : ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" style="display: block; position: relative; flex-shrink: 0;">
						<img
							src="<?php echo esc_url( $thumbnail ); ?>"
							alt="<?php echo esc_attr( get_the_title() ); ?>"
							class="gcb-bento-card__image"
							width="800"
							height="450"
							<?php if ( $srcset ) : ?>
								srcset="<?php echo esc_attr( $srcset ); ?>"
								sizes="<?php echo esc_attr( $sizes ); ?>"
							<?php endif; ?>
							style="width: 100%; object-fit: cover; display: block; border-bottom: 2px solid var(--wp--preset--color--brutal-border);<?php echo ( defined( 'GCB_IMAGE_MODE' ) && 'grayscale' === GCB_IMAGE_MODE ) ? ' filter: grayscale(100%) contrast(1.3);' : ''; ?>"
							loading="lazy"
						/>
					</a>
				<?php endif; ?>

				<!-- Card Content -->
				<div style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
					<!-- Title -->
					<h3 class="gcb-bento-card__title" style="font-family: var(--wp--preset--font-family--playfair); font-size: 1.25rem; line-height: 1.3; margin: 0 0 0.75rem 0; color: var(--wp--preset--color--off-white);">
						<a href="<?php echo esc_url( get_permalink() ); ?>" style="color: inherit; text-decoration: none;">
							<?php echo esc_html( get_the_title() ); ?>
						</a>
					</h3>

					<!-- Excerpt (all cards) -->
					<p class="gcb-bento-card__excerpt" style="font-family: var(--wp--preset--font-family--system-sans); font-size: 0.875rem; line-height: 1.5; color: var(--wp--preset--color--brutal-grey); margin: 0 0 0.75rem 0; flex-grow: 1;">
						<?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?>
					</p>

					<!-- Metadata -->
					<div class="gcb-bento-card__meta" style="margin-top: auto; display: flex; gap: 0.75rem; align-items: center; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey);">
						<!-- Post Date -->
						<time class="post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
						</time>

						<!-- Content Format Badge -->
						<span style="padding: 2px 8px; border: 1px solid var(--wp--preset--color--brutal-border); text-transform: uppercase;">
							Article
						</span>
					</div>
				</div>
			</div>

		<?php
			$index++;
		endwhile;
		?>

		<?php wp_reset_postdata(); ?>

	</div>

</div>
<!-- /wp:group -->

<!-- Responsive CSS for Bento Grid -->
<style>
	/* Mobile: Stack to single column */
	@media (max-width: 768px) {
		.gcb-bento-grid__container {
			grid-template-columns: 1fr !important;
		}
		.bento-item--featured {
			grid-column: span 1 !important;
		}
	}

	/* Tablet: 2 columns */
	@media (min-width: 769px) and (max-width: 1024px) {
		.gcb-bento-grid__container {
			grid-template-columns: repeat(2, 1fr) !important;
		}
	}

	/* Desktop: Auto-fit with featured spanning 2 columns */
	@media (min-width: 1024px) {
		.gcb-bento-grid__container {
			grid-template-columns: repeat(3, 1fr);
		}
	}

	/* Hover effect */
	.bento-item:hover {
		border-color: var(--wp--preset--color--highlight) !important;
	}

	/* Bento Grid Image Heights - Uniform across all cards */
	.gcb-bento-card__image {
		height: 200px; /* Mobile */
	}
	@media (min-width: 768px) {
		.gcb-bento-card__image {
			height: 220px; /* Tablet */
		}
	}
	@media (min-width: 1024px) {
		.gcb-bento-card__image {
			height: 240px; /* Desktop - uniform for all cards */
		}
	}
</style>
