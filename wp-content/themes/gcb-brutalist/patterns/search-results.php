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
	<ul class="gcb-bento-grid__container wp-block-post-template">
		<?php
		while ( $search_results->have_posts() ) :
			$search_results->the_post();
			$post_id = get_the_ID();
			?>
			<li class="wp-block-post bento-item gcb-bento-card" style="border:2px solid var(--wp--preset--color--brutal-border);background:var(--wp--preset--color--void-black)">
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" class="wp-block-post-featured-image">
						<?php
						// Use medium_large (768px) for faster loading - cards are max 400px height
						the_post_thumbnail(
							'medium_large',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					</a>
				<?php endif; ?>

				<div class="wp-block-group">
					<h3 class="wp-block-post-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>

					<div class="search-card-meta wp-block-group">
						<time class="wp-block-post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( strtoupper( get_the_date( 'M j, Y' ) ) ); ?>
						</time>
						<span class="post-type-badge">ARTICLE</span>
					</div>
				</div>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
	<?php
else :
	?>
	<div class="wp-block-query-no-results">
		<p class="search-no-results">No results found. Try a different search term.</p>
	</div>
	<?php
endif;
?>
