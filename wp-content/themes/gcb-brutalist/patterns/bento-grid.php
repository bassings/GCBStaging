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
			'posts_per_page' => 7,
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
			// First item is featured (spans full row - 3 columns on desktop)
			$is_featured = ( 0 === $index );
			$size_class  = $is_featured ? 'bento-item--featured bento-item--large' : '';
			$grid_span   = $is_featured ? 'grid-column: 1 / -1;' : '';

			// Get thumbnail with dimensions for CLS prevention.
			// Use medium_large (768px) as default src for faster mobile LCP,
			// with full srcset for larger screens to pick appropriate size.
			$thumbnail_id  = get_post_thumbnail_id( $post_id );
			$default_size  = $is_featured ? 'medium_large' : 'medium';
			$srcset_size   = 'large'; // Generate srcset from large for full range
			$thumbnail     = get_the_post_thumbnail_url( $post_id, $default_size );
			$srcset        = $thumbnail_id ? wp_get_attachment_image_srcset( $thumbnail_id, $srcset_size ) : '';
			$sizes         = $is_featured ? '(max-width: 768px) 100vw, (max-width: 1024px) 66vw, 800px' : '(max-width: 768px) 85vw, 280px';
			
			// Open carousel wrapper after hero (index 1)
			if ( 1 === $index ) :
			?>
			<!-- Mobile Carousel Wrapper (horizontal scroll on mobile only) -->
			<div class="gcb-mobile-carousel-wrapper">
				<button class="gcb-carousel-arrow gcb-carousel-arrow--prev hidden" aria-label="Previous article">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
				</button>
				<div class="gcb-mobile-carousel" role="region" aria-label="More featured stories">
			<?php endif; ?>

			<!-- Bento Grid Item -->
			<div class="bento-item gcb-bento-card bento-item--standard <?php echo esc_attr( $size_class ); ?>" data-size="<?php echo $is_featured ? 'large' : 'standard'; ?>" style="<?php echo esc_attr( $grid_span ); ?> border: 2px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black); overflow: hidden; display: flex; flex-direction: column; height: 100%;">

				<!-- Thumbnail -->
				<?php if ( $thumbnail ) : ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="gcb-bento-card__image-link" style="display: block; position: relative; flex-shrink: 0; overflow: hidden;">
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
							style="width: 100%; height: 100%; object-fit: cover; display: block;<?php echo ( defined( 'GCB_IMAGE_MODE' ) && 'grayscale' === GCB_IMAGE_MODE ) ? ' filter: grayscale(100%) contrast(1.3);' : ''; ?>"
							<?php if ( $is_featured ) : ?>
							fetchpriority="high"
							loading="eager"
							decoding="sync"
							<?php else : ?>
							loading="lazy"
							decoding="async"
							<?php endif; ?>
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

					<!-- Excerpt -->
					<p class="gcb-bento-card__excerpt" style="font-family: var(--wp--preset--font-family--system-sans); font-size: 0.875rem; line-height: 1.5; color: var(--wp--preset--color--brutal-grey); margin: 0 0 0.75rem 0; flex-grow: 1;">
						<?php
						if ( $is_featured ) {
							echo esc_html( get_the_excerpt() ); // Full excerpt for hero
						} else {
							echo esc_html( wp_trim_words( get_the_excerpt(), 55 ) ); // Standard cards
						}
						?>
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
		
		// Close carousel wrapper if we opened it (had more than 1 post)
		if ( $index > 1 ) :
		?>
				</div><!-- .gcb-mobile-carousel -->
				<button class="gcb-carousel-arrow gcb-carousel-arrow--next" aria-label="Next article">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
				</button>
			</div><!-- .gcb-mobile-carousel-wrapper -->
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

	</div>

</div>
<!-- /wp:group -->

<!-- Responsive CSS for Bento Grid -->
<style>
	/* Mobile: Hero full-width, rest as horizontal carousel */
	@media (max-width: 768px) {
		.gcb-bento-grid__container {
			display: block !important;
		}
		
		/* Hero stays full width, normal flow */
		.bento-item--featured {
			width: 100% !important;
			margin-bottom: 1rem !important;
		}
		
		/* Mobile carousel - horizontal scroll */
		.gcb-mobile-carousel {
			display: flex !important;
			flex-wrap: nowrap !important;
			overflow-x: auto !important;
			overflow-y: hidden !important;
			scroll-snap-type: x mandatory !important;
			-webkit-overflow-scrolling: touch !important;
			gap: 1rem !important;
			padding-bottom: 1rem !important;
			/* Extend to edges for full-bleed scroll */
			margin-left: -1rem !important;
			margin-right: -1rem !important;
			padding-left: 1rem !important;
			padding-right: 1rem !important;
			/* Hide scrollbar but keep functionality */
			scrollbar-width: none !important;
			-ms-overflow-style: none !important;
		}
		.gcb-mobile-carousel::-webkit-scrollbar {
			display: none !important;
		}
		
		/* Carousel cards - 75% width to leave room for arrow buttons */
		.gcb-mobile-carousel > .bento-item {
			flex: 0 0 75% !important;
			min-width: 75% !important;
			max-width: 75% !important;
			scroll-snap-align: center !important;
			height: auto !important;
		}
		
		/* Ensure last card has room to snap */
		.gcb-mobile-carousel::after {
			content: '';
			flex: 0 0 12.5%;
		}
		
		/* Carousel wrapper with arrow button space */
		.gcb-mobile-carousel-wrapper {
			position: relative;
			padding: 0 44px; /* Space for arrow buttons */
		}
		
		/* Arrow buttons */
		.gcb-carousel-arrow {
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			width: 36px;
			height: 36px;
			background: var(--wp--preset--color--highlight);
			border: none;
			border-radius: 50%;
			z-index: 10;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 0.9;
			-webkit-tap-highlight-color: transparent;
		}
		.gcb-carousel-arrow:active {
			opacity: 1;
			transform: translateY(-50%) scale(0.95);
		}
		.gcb-carousel-arrow svg {
			width: 20px;
			height: 20px;
			fill: var(--wp--preset--color--void-black);
		}
		.gcb-carousel-arrow--prev {
			left: 0;
		}
		.gcb-carousel-arrow--next {
			right: 0;
		}
		/* Hide arrows at edges */
		.gcb-carousel-arrow--prev.hidden,
		.gcb-carousel-arrow--next.hidden {
			opacity: 0.3;
			pointer-events: none;
		}
	}
	
	/* Tablet and up: Carousel wrapper becomes transparent */
	@media (min-width: 769px) {
		.gcb-mobile-carousel-wrapper {
			display: contents !important;
			padding: 0 !important;
		}
		.gcb-mobile-carousel {
			display: contents !important; /* Children flow into parent grid */
		}
		/* Hide arrows on desktop */
		.gcb-carousel-arrow {
			display: none !important;
		}
	}

	/* Tablet: 2 columns */
	@media (min-width: 769px) and (max-width: 1024px) {
		.gcb-bento-grid__container {
			grid-template-columns: repeat(2, 1fr) !important;
		}
		.bento-item--featured {
			grid-column: 1 / -1 !important;
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

	/* Bento Grid Image Container Heights - Standard cards */
	.gcb-bento-card__image-link {
		height: 200px; /* Mobile */
		border-bottom: 2px solid var(--wp--preset--color--brutal-border);
	}
	@media (min-width: 768px) {
		.gcb-bento-card__image-link {
			height: 220px; /* Tablet */
		}
	}
	@media (min-width: 1024px) {
		.gcb-bento-card__image-link {
			height: 240px; /* Desktop */
		}
	}

	/* Hero/Featured Image Container - 16:9 aspect ratio */
	.bento-item--featured .gcb-bento-card__image-link {
		height: 220px; /* Mobile */
	}
	@media (min-width: 768px) {
		.bento-item--featured .gcb-bento-card__image-link {
			height: 430px; /* Tablet */
		}
	}
	@media (min-width: 1024px) {
		.bento-item--featured .gcb-bento-card__image-link {
			height: 640px; /* Desktop - 16:9 ratio */
		}
	}
</style>

<!-- Carousel arrow functionality -->
<script>
(function() {
	'use strict';
	
	function initCarouselArrows() {
		document.querySelectorAll('.gcb-mobile-carousel-wrapper').forEach(function(wrapper) {
			var carousel = wrapper.querySelector('.gcb-mobile-carousel');
			var prevBtn = wrapper.querySelector('.gcb-carousel-arrow--prev');
			var nextBtn = wrapper.querySelector('.gcb-carousel-arrow--next');
			
			if (!carousel || !prevBtn || !nextBtn) return;
			
			var cards = carousel.querySelectorAll('.bento-item');
			var currentIndex = 0;
			
			function updateArrowState() {
				var scrollLeft = carousel.scrollLeft;
				var maxScroll = carousel.scrollWidth - carousel.clientWidth;
				
				// Hide/show arrows based on position
				if (scrollLeft <= 10) {
					prevBtn.classList.add('hidden');
				} else {
					prevBtn.classList.remove('hidden');
				}
				
				if (scrollLeft >= maxScroll - 10) {
					nextBtn.classList.add('hidden');
				} else {
					nextBtn.classList.remove('hidden');
				}
			}
			
			function scrollToCard(index) {
				if (index < 0) index = 0;
				if (index >= cards.length) index = cards.length - 1;
				
				var card = cards[index];
				if (card) {
					card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
					currentIndex = index;
				}
			}
			
			// Find current card index based on scroll position
			function getCurrentIndex() {
				var scrollCenter = carousel.scrollLeft + carousel.clientWidth / 2;
				for (var i = 0; i < cards.length; i++) {
					var card = cards[i];
					var cardLeft = card.offsetLeft;
					var cardRight = cardLeft + card.offsetWidth;
					if (scrollCenter >= cardLeft && scrollCenter <= cardRight) {
						return i;
					}
				}
				return 0;
			}
			
			// Button click handlers
			prevBtn.addEventListener('click', function() {
				currentIndex = getCurrentIndex();
				scrollToCard(currentIndex - 1);
			});
			
			nextBtn.addEventListener('click', function() {
				currentIndex = getCurrentIndex();
				scrollToCard(currentIndex + 1);
			});
			
			// Update arrows on scroll
			carousel.addEventListener('scroll', updateArrowState, { passive: true });
			
			// Initial state
			updateArrowState();
		});
	}
	
	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCarouselArrows);
	} else {
		initCarouselArrows();
	}
})();
</script>
