<?php
/**
 * Title: Search Results Grid
 * Slug: gcb-brutalist/search-results
 * Categories: search, gcb-content
 * Description: 3x3 bento-style grid displaying search results with Editorial Brutalism styling
 */

// Get the search query from multiple sources
$search_query = get_search_query();

// Fallback to $_GET if get_search_query() is empty
if ( empty( $search_query ) && isset( $_GET['s'] ) ) {
	$search_query = sanitize_text_field( wp_unslash( $_GET['s'] ) );
}

// Build the query args
$args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

// Only add search parameter if we have a search query
if ( ! empty( $search_query ) ) {
	$args['s'] = $search_query;
}

// Execute the query
$search_results = new WP_Query( $args );

if ( $search_results->have_posts() ) :
	?>
	<div class="gcb-bento-grid__container search-results-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; align-items: stretch;">
		<?php
		while ( $search_results->have_posts() ) :
			$search_results->the_post();
			$post_id      = get_the_ID();
			$thumbnail    = get_the_post_thumbnail_url( $post_id, 'large' );
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$srcset       = $thumbnail_id ? wp_get_attachment_image_srcset( $thumbnail_id, 'large' ) : '';
			?>
			<!-- Bento Grid Item - Matches homepage styling -->
			<div class="bento-item gcb-bento-card bento-item--standard" style="border: 2px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black); overflow: hidden; display: flex; flex-direction: column; height: 100%;">

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
								sizes="(max-width: 768px) 100vw, 33vw"
							<?php endif; ?>
							style="width: 100%; object-fit: cover; display: block; border-bottom: 2px solid var(--wp--preset--color--brutal-border);"
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

					<!-- Excerpt -->
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
		endwhile;
		wp_reset_postdata();
		?>
	</div>
	<?php
else :
	?>
	<div class="wp-block-query-no-results">
		<p class="search-no-results">No results found. Try a different search term.</p>
	</div>
	<?php
endif;
?>
