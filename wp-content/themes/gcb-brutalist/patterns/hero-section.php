<?php
/**
 * Title: Hero Section
 * Slug: gcb-brutalist/hero-section
 * Categories: featured, gcb-content
 * Description: Two-column hero section with feature and opinion cards using Editorial Brutalism styling (North Star aligned)
 * Keywords: hero, featured, opinion, brutalism, two-column
 */

// Query latest posts for hero section
$hero_posts = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 2,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( ! $hero_posts->have_posts() ) {
	return;
}
?>

<!-- wp:group {"className":"gcb-hero-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group gcb-hero-section" data-pattern="hero-section" style="margin-bottom:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">

	<!-- Hero Grid Container -->
	<div class="gcb-hero__container hero-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">

		<?php
		$index = 0;
		while ( $hero_posts->have_posts() ) :
			$hero_posts->the_post();
			$post_id   = get_the_ID();
			$thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );
			$author_id = get_post_field( 'post_author', $post_id );
			$author_name = get_the_author_meta( 'display_name', $author_id );

			// Get post categories for badge
			$categories    = get_the_category( $post_id );
			$category_name = ! empty( $categories ) ? $categories[0]->name : 'Article';

			// Calculate read time using helper function
			$read_time = gcb_calculate_read_time( $post_id );

			// First post is feature (large card), second is opinion (small card)
			$is_feature   = ( 0 === $index );
			$card_class   = $is_feature ? 'gcb-hero__feature hero-feature-card' : 'gcb-hero__opinion hero-opinion-card';
			$grid_span    = $is_feature ? 'grid-column: span 2;' : 'grid-column: span 1;';
			$heading_tag  = $is_feature ? 'h1' : 'h2';

			// North Star: Opinion badge uses brutal-grey (readable gray), feature uses highlight color
			$badge_border_color = $is_feature ? 'var(--wp--preset--color--highlight)' : 'var(--wp--preset--color--brutal-grey)';
			$badge_text_color   = $is_feature ? 'var(--wp--preset--color--highlight)' : 'var(--wp--preset--color--brutal-grey)';
			?>

			<!-- Hero Card -->
			<div class="<?php echo esc_attr( $card_class ); ?>" style="<?php echo esc_attr( $grid_span ); ?> border: 2px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black); overflow: hidden; position: relative;">

				<!-- Background Image with Gradient Overlay -->
				<?php if ( $thumbnail ) : ?>
					<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
						<img
							src="<?php echo esc_url( $thumbnail ); ?>"
							alt="<?php echo esc_attr( get_the_title() ); ?>"
							style="width: 100%; height: 100%; object-fit: cover;<?php echo ( defined( 'GCB_IMAGE_MODE' ) && 'grayscale' === GCB_IMAGE_MODE ) ? ' filter: grayscale(100%) contrast(1.3);' : ''; ?>"
							loading="lazy"
						/>
						<!-- Dark gradient overlay -->
						<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, var(--wp--preset--color--void-black) 0%, rgba(5, 5, 5, 0.7) 50%, transparent 100%);"></div>
					</div>
				<?php endif; ?>

				<!-- Card Content -->
				<div style="position: relative; z-index: 1; padding: 2rem; height: 100%; display: flex; flex-direction: column; justify-content: flex-end;">

					<!-- Category Badge (North Star: Opinion uses brutal-border gray) -->
					<div class="gcb-category-badge category-label <?php echo $is_feature ? '' : 'opinion-badge'; ?>" style="display: inline-block; padding: 6px 12px; border: 1px solid <?php echo esc_attr( $badge_border_color ); ?>; color: <?php echo esc_attr( $badge_text_color ); ?>; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem; width: fit-content;">
						<?php echo esc_html( $category_name ); ?>
					</div>

					<!-- Headline (North Star: Responsive sizing via CSS classes) -->
					<<?php echo $heading_tag; ?> class="gcb-hero__<?php echo $is_feature ? 'feature' : 'opinion'; ?>-title <?php echo $is_feature ? 'feature-headline' : 'opinion-headline'; ?>" style="font-family: var(--wp--preset--font-family--playfair); line-height: 1.2; margin: 0 0 1rem 0; color: var(--wp--preset--color--off-white);">
						<a href="<?php echo esc_url( get_permalink() ); ?>" style="color: inherit; text-decoration: none;">
							<?php echo esc_html( get_the_title() ); ?>
						</a>
					</<?php echo $heading_tag; ?>>

					<!-- Excerpt (opinion card only) -->
					<?php if ( ! $is_feature ) : ?>
						<p class="gcb-hero__excerpt" style="font-family: var(--wp--preset--font-family--system-sans); font-size: 0.875rem; line-height: 1.6; color: var(--wp--preset--color--off-white); margin: 0 0 1rem 0;">
							<?php echo esc_html( wp_trim_words( get_the_excerpt(), 55 ) ); ?>
						</p>
					<?php endif; ?>

					<!-- Metadata -->
					<div class="gcb-hero__meta" style="display: flex; gap: 1rem; align-items: center; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey); margin-top: auto;">
						<!-- Author -->
						<span class="gcb-hero__author post-author" data-author="<?php echo esc_attr( $author_name ); ?>">
							<?php echo esc_html( $author_name ); ?>
						</span>

						<!-- Separator -->
						<div style="width: 1px; height: 1rem; background-color: var(--wp--preset--color--brutal-grey);"></div>

						<!-- Date -->
						<time class="gcb-hero__date post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
						</time>

						<!-- Separator (Feature only) -->
						<?php if ( $is_feature ) : ?>
							<div style="width: 1px; height: 1rem; background-color: var(--wp--preset--color--brutal-grey);"></div>

							<!-- Read Time (Feature only) -->
							<span class="gcb-hero__read-time read-time" data-read-time="<?php echo esc_attr( $read_time ); ?>" style="color: var(--wp--preset--color--highlight);">
								Read Time: <?php echo esc_html( $read_time ); ?> min
							</span>
						<?php endif; ?>
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

<!-- Responsive CSS for Hero Section (North Star Aligned) -->
<style>
	/* North Star: Responsive Feature Headline Sizes (text-2xl → text-6xl) */
	.feature-headline {
		font-size: 1.5rem; /* Mobile: text-2xl / 24px */
	}
	@media (min-width: 640px) {
		.feature-headline {
			font-size: 1.875rem; /* sm: text-3xl / 30px */
		}
	}
	@media (min-width: 768px) {
		.feature-headline {
			font-size: 2.25rem; /* md: text-4xl / 36px */
		}
	}
	@media (min-width: 1024px) {
		.feature-headline {
			font-size: 3rem; /* lg: text-5xl / 48px */
		}
	}
	@media (min-width: 1280px) {
		.feature-headline {
			font-size: 3.75rem; /* xl: text-6xl / 60px */
		}
	}

	/* North Star: Responsive Opinion Headline Sizes (text-2xl → text-3xl) */
	.opinion-headline {
		font-size: 1.5rem; /* Mobile: text-2xl / 24px */
	}
	@media (min-width: 640px) {
		.opinion-headline {
			font-size: 1.875rem; /* sm: text-3xl / 30px */
		}
	}

	/* North Star: Opinion Badge Hover Effect (gray → lime) */
	.gcb-hero__opinion:hover .opinion-badge {
		color: var(--wp--preset--color--highlight) !important;
	}

	/* Responsive Card Heights - Optimized to reduce image cropping */
	.gcb-hero__feature {
		height: 300px; /* Mobile: reduced from 384px */
	}
	@media (min-width: 768px) {
		.gcb-hero__feature {
			height: 360px; /* Tablet: new intermediate size */
		}
	}
	@media (min-width: 1024px) {
		.gcb-hero__feature {
			height: 450px; /* Desktop: reduced from 500px */
		}
	}

	/* Opinion Card Heights - Optimized for 3:2 aspect ratio */
	.gcb-hero__opinion {
		height: 240px; /* Mobile: reduced from 256px */
	}
	@media (min-width: 768px) {
		.gcb-hero__opinion {
			height: 280px; /* Tablet/Desktop: increased from 256px */
		}
	}

	/* Desktop: 3-column grid with feature card spanning 2 columns */
	@media (min-width: 1025px) {
		.gcb-hero__container {
			grid-template-columns: repeat(3, 1fr);
		}
	}

	/* Tablet: 2-column grid */
	@media (min-width: 769px) and (max-width: 1024px) {
		.gcb-hero__container {
			grid-template-columns: repeat(2, 1fr) !important;
		}
		.gcb-hero__feature {
			grid-column: span 2 !important;
		}
		.gcb-hero__opinion {
			grid-column: span 2 !important;
		}
	}

	/* Mobile: Stack to single column */
	@media (max-width: 768px) {
		.gcb-hero__container {
			grid-template-columns: 1fr !important;
		}
		.gcb-hero__feature {
			grid-column: span 1 !important;
		}
		.gcb-hero__opinion {
			grid-column: span 1 !important;
		}
	}

	/* Hover effect: Acid Lime border */
	.gcb-hero__feature:hover,
	.gcb-hero__opinion:hover {
		border-color: var(--wp--preset--color--highlight);
	}

	/* Ensure link covers entire card for better UX */
	.gcb-hero__feature-title a,
	.gcb-hero__opinion-title a {
		display: block;
	}
</style>

<?php
/**
 * Helper function to calculate post read time
 *
 * Estimates reading time based on word count at 200 words per minute.
 * Minimum read time is 1 minute.
 *
 * @param int $post_id Post ID
 * @return int Read time in minutes
 */
function gcb_calculate_read_time( int $post_id ): int {
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( strip_tags( $content ) );
	return max( 1, ceil( $word_count / 200 ) );
}
?>
