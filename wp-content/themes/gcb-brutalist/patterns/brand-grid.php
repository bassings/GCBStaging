<?php
/**
 * Title: Brand Grid
 * Slug: gcb-brutalist/brand-grid
 * Categories: query
 * Description: Displays all brands in a brutalist grid layout
 */

// Get all brands with posts
$brands = get_terms( array(
	'taxonomy'   => 'brand',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );

// Exit if no brands
if ( empty( $brands ) || is_wp_error( $brands ) ) {
	return;
}
?>

<!-- Brands Grid -->
<div class="brand-index-grid" style="margin-bottom: var(--wp--preset--spacing--60);">

	<!-- Section Header -->
	<div style="border-bottom: 2px solid var(--wp--preset--color--brutal-border); padding-bottom: var(--wp--preset--spacing--30); margin-bottom: var(--wp--preset--spacing--40);">
		<h2 style="font-family: var(--wp--preset--font-family--playfair); font-size: 2.5rem; text-transform: uppercase; color: var(--wp--preset--color--off-white); margin: 0;">
			Browse by Brand
		</h2>
		<p style="font-family: var(--wp--preset--font-family--mono); font-size: 0.875rem; color: var(--wp--preset--color--brutal-grey); margin-top: 0.5rem;">
			<?php echo count( $brands ); ?> brands with reviews and news
		</p>
	</div>

	<!-- Brands Grid -->
	<div class="brands-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
		<?php foreach ( $brands as $brand ) : ?>
			<?php
			$brand_link = get_term_link( $brand );
			if ( is_wp_error( $brand_link ) ) {
				continue;
			}
			?>
			<a href="<?php echo esc_url( $brand_link ); ?>"
			   class="brand-card"
			   style="display: block; padding: 1.5rem 1rem; border: 2px solid var(--wp--preset--color--brutal-border); text-decoration: none; transition: none; background: transparent;">

				<div style="font-family: var(--wp--preset--font-family--mono); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--wp--preset--color--off-white); margin-bottom: 0.5rem;">
					<?php echo esc_html( $brand->name ); ?>
				</div>

				<div style="font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey);">
					<?php echo esc_html( $brand->count ); ?> <?php echo $brand->count === 1 ? 'article' : 'articles'; ?>
				</div>
			</a>
		<?php endforeach; ?>
	</div>

	<style>
		.brand-card:hover,
		.brand-card:focus {
			border-color: var(--wp--preset--color--highlight) !important;
			outline: none;
		}

		.brand-card:focus-visible {
			outline: 2px solid var(--wp--preset--color--highlight);
			outline-offset: 2px;
		}

		/* Responsive adjustments */
		@media (max-width: 768px) {
			.brands-grid {
				grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
			}
		}

		@media (max-width: 480px) {
			.brands-grid {
				grid-template-columns: 1fr 1fr !important;
			}
		}
	</style>
</div>
