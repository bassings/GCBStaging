<?php
/**
 * GCB Brutalist Theme Functions
 *
 * Editorial Brutalism meets automotive culture.
 * A bold FSE block theme featuring the "Neon Noir" design system.
 *
 * @package GCB_Brutalist
 */

// Security: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add responsive video CSS for Fusion Builder embeds
 *
 * Makes Fusion Builder video embeds responsive on all screen sizes.
 */
function gcb_responsive_video_css(): void {
	// Only on frontend
	if ( is_admin() ) {
		return;
	}

	// Inline CSS to make Fusion Builder videos responsive
	$css = '
		/* Responsive Fusion Builder video embeds */
		.video-shortcode {
			position: relative;
			padding-bottom: 56.25%; /* 16:9 aspect ratio */
			height: 0;
			overflow: hidden;
			max-width: 100%;
		}
		.video-shortcode iframe {
			position: absolute;
			top: 0;
			left: 0;
			width: 100% !important;
			height: 100% !important;
			max-width: 100%;
		}
	';

	wp_add_inline_style( 'wp-block-library', $css );
}
add_action( 'wp_enqueue_scripts', 'gcb_responsive_video_css' );

/**
 * Add content_format taxonomy classes to body
 *
 * Enables CSS targeting of video/standard/gallery posts via body classes.
 * Integrates with GCB Content Intelligence plugin.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function gcb_add_content_format_body_classes( array $classes ): array {
	if ( ! is_singular( 'post' ) ) {
		return $classes;
	}

	$post_id = get_the_ID();
	$format  = get_post_meta( $post_id, '_gcb_content_format', true );

	if ( ! empty( $format ) ) {
		$classes[] = 'content-format-' . sanitize_html_class( $format );
	}

	// Also add taxonomy term classes
	$terms = wp_get_object_terms( $post_id, 'content_format', array( 'fields' => 'slugs' ) );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term_slug ) {
			$classes[] = 'tax-content_format-' . sanitize_html_class( $term_slug );
		}
	}

	return $classes;
}
add_filter( 'body_class', 'gcb_add_content_format_body_classes' );

/**
 * Register Video Rail block pattern
 *
 * Registers the PHP-generated video rail pattern for use in templates.
 */
function gcb_register_video_rail_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/video-rail',
		array(
			'title'       => __( 'Video Rail', 'gcb-brutalist' ),
			'description' => __( 'Horizontal scrolling rail of video posts with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'video', 'rail', 'horizontal', 'scroll', 'brutalism' ),
			'content'     => gcb_get_video_rail_content(),
		)
	);
}
add_action( 'init', 'gcb_register_video_rail_pattern' );

/**
 * Get video rail pattern content
 *
 * Generates the HTML for the video rail pattern by querying video posts.
 *
 * @return string Video rail HTML
 */
function gcb_get_video_rail_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/video-rail.php';
	return ob_get_clean();
}

/**
 * Register Bento Grid block pattern
 *
 * Registers the PHP-generated bento grid pattern for use in templates.
 */
function gcb_register_bento_grid_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/bento-grid',
		array(
			'title'       => __( 'Bento Grid', 'gcb-brutalist' ),
			'description' => __( 'Mixed layout grid combining video and standard posts with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'bento', 'grid', 'mixed', 'layout', 'brutalism' ),
			'content'     => gcb_get_bento_grid_content(),
		)
	);
}
add_action( 'init', 'gcb_register_bento_grid_pattern' );

/**
 * Get bento grid pattern content
 *
 * Generates the HTML for the bento grid pattern by querying mixed posts.
 *
 * @return string Bento grid HTML
 */
function gcb_get_bento_grid_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/bento-grid.php';
	return ob_get_clean();
}

/**
 * Register Hero Section block pattern
 *
 * Registers the PHP-generated hero section pattern for use in templates.
 */
function gcb_register_hero_section_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/hero-section',
		array(
			'title'       => __( 'Hero Section', 'gcb-brutalist' ),
			'description' => __( 'Two-column hero section with feature and opinion cards using Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'hero', 'featured', 'opinion', 'brutalism', 'two-column' ),
			'content'     => gcb_get_hero_section_content(),
		)
	);
}
add_action( 'init', 'gcb_register_hero_section_pattern' );

/**
 * Get hero section pattern content
 *
 * Generates the HTML for the hero section pattern by querying latest posts.
 *
 * @return string Hero section HTML
 */
function gcb_get_hero_section_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/hero-section.php';
	return ob_get_clean();
}

/**
 * Register Culture Grid block pattern
 *
 * Registers the PHP-generated culture grid pattern for use in templates.
 */
function gcb_register_culture_grid_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/culture-grid',
		array(
			'title'       => __( 'Culture Grid', 'gcb-brutalist' ),
			'description' => __( '4-column responsive grid displaying text-only editorial content cards with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'culture', 'grid', 'editorial', 'text-only', 'brutalism' ),
			'content'     => gcb_get_culture_grid_content(),
		)
	);
}
add_action( 'init', 'gcb_register_culture_grid_pattern' );

/**
 * Get culture grid pattern content
 *
 * Generates the HTML for the culture grid pattern by querying standard posts.
 *
 * @return string Culture grid HTML
 */
function gcb_get_culture_grid_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/culture-grid.php';
	return ob_get_clean();
}

/**
 * Register Header Navigation block pattern
 *
 * Registers the PHP-generated header navigation pattern for use in templates.
 */
function gcb_register_header_navigation_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/header-navigation',
		array(
			'title'       => __( 'Header Navigation', 'gcb-brutalist' ),
			'description' => __( 'Sticky header with desktop navigation and mobile slide-out menu using Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'header', 'gcb-content' ),
			'keywords'    => array( 'header', 'navigation', 'mobile', 'menu', 'sticky', 'brutalism' ),
			'content'     => gcb_get_header_navigation_content(),
		)
	);
}
add_action( 'init', 'gcb_register_header_navigation_pattern' );

/**
 * Get header navigation pattern content
 *
 * Generates the HTML for the header navigation pattern.
 *
 * @return string Header navigation HTML
 */
function gcb_get_header_navigation_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/header-navigation.php';
	return ob_get_clean();
}

/**
 * Register Terminal Search Modal block pattern
 *
 * Registers the PHP-generated search modal pattern for use in templates.
 */
function gcb_register_search_terminal_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/search-terminal',
		array(
			'title'       => __( 'Terminal Search Modal', 'gcb-brutalist' ),
			'description' => __( 'Full-screen terminal-style search modal with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'search', 'gcb-content' ),
			'keywords'    => array( 'search', 'terminal', 'modal', 'brutalism' ),
			'content'     => gcb_get_search_terminal_content(),
		)
	);
}
add_action( 'init', 'gcb_register_search_terminal_pattern' );

/**
 * Get terminal search modal pattern content
 *
 * Generates the HTML for the terminal search modal pattern.
 *
 * @return string Terminal search modal HTML
 */
function gcb_get_search_terminal_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/search-terminal.php';
	return ob_get_clean();
}

/**
 * Register Search Results Grid block pattern
 *
 * Registers the PHP-generated search results pattern for use in templates.
 */
function gcb_register_search_results_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/search-results',
		array(
			'title'       => __( 'Search Results Grid', 'gcb-brutalist' ),
			'description' => __( '3x3 bento-style grid displaying search results with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'search', 'gcb-content' ),
			'keywords'    => array( 'search', 'results', 'bento', 'grid', 'brutalism' ),
			'content'     => gcb_get_search_results_content(),
		)
	);
}
add_action( 'init', 'gcb_register_search_results_pattern' );

/**
 * Get search results pattern content
 *
 * Generates the HTML for the search results pattern by querying search term.
 *
 * @return string Search results HTML
 */
function gcb_get_search_results_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/search-results.php';
	return ob_get_clean();
}

/**
 * Search Results Grid Shortcode
 *
 * Displays filtered search results in a bento-style grid.
 * Usage: [gcb_search_results]
 */
function gcb_search_results_shortcode() {
	// Get the search query
	$search_query = get_search_query();
	if ( empty( $search_query ) && isset( $_GET['s'] ) ) {
		$search_query = sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}

	// Build query args - search only in titles for more relevant results
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Use custom title search for better relevance
	if ( ! empty( $search_query ) ) {
		// Instead of 's' parameter, use a custom meta query approach
		// We'll filter by title using posts_where filter
		add_filter( 'posts_where', function( $where ) use ( $search_query ) {
			global $wpdb;
			// Search only in post_title for more relevant results
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.post_title LIKE %s",
				'%' . $wpdb->esc_like( $search_query ) . '%'
			);
			return $where;
		}, 10, 1 );
	}

	// Execute search query
	$search_results = new WP_Query( $args );

	// Remove the filter after query executes
	if ( ! empty( $search_query ) ) {
		remove_all_filters( 'posts_where' );
	}

	// Start output buffering
	ob_start();

	if ( $search_results->have_posts() ) :
		?>
		<div class="wp-block-group alignwide search-results-grid">
			<ul class="gcb-bento-grid__container wp-block-post-template">
				<?php
				while ( $search_results->have_posts() ) :
					$search_results->the_post();
					?>
					<li class="wp-block-post bento-item gcb-bento-card" style="border:2px solid var(--wp--preset--color--brutal-border);background:var(--wp--preset--color--void-black)">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="wp-block-post-featured-image">
								<?php the_post_thumbnail( 'large' ); ?>
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
								<span class="post-type-badge" style="padding:2px 8px;border:1px solid var(--wp--preset--color--brutal-border);font-family:var(--wp--preset--font-family--mono);font-size:0.75rem;text-transform:uppercase;color:var(--wp--preset--color--brutal-grey);">ARTICLE</span>
							</div>
						</div>
					</li>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		</div>
		<?php
	else :
		?>
		<div class="wp-block-query-no-results">
			<p class="search-no-results has-brutal-grey-color" style="padding:var(--wp--preset--spacing--50) 0;font-family:var(--wp--preset--font-family--mono);font-size:1.25rem;color:var(--wp--preset--color--brutal-grey);">No results found. Try a different search term.</p>
		</div>
		<?php
	endif;

	return ob_get_clean();
}
add_shortcode( 'gcb_search_results', 'gcb_search_results_shortcode' );

/**
 * Shortcode: Category Children Grid
 * Displays child categories in a brutalist grid layout
 *
 * @return string HTML output for category children grid
 */
function gcb_category_children_shortcode() {
	// Get current category
	$current_category = get_queried_object();

	if ( ! $current_category || ! isset( $current_category->term_id ) ) {
		return '';
	}

	// Get child categories
	$child_categories = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $current_category->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	// Exit if no children
	if ( empty( $child_categories ) || is_wp_error( $child_categories ) ) {
		return '';
	}

	ob_start();
	?>
	<!-- Child Categories Grid -->
	<div class="category-children-grid" style="margin-bottom: 4rem;">

		<!-- Section Header -->
		<div style="border-bottom: 2px solid var(--wp--preset--color--acid-lime); padding-bottom: 1.5rem; margin-bottom: 2rem;">
			<h2 style="font-family: var(--wp--preset--font-family--playfair); font-size: 2rem; text-transform: uppercase; color: var(--wp--preset--color--off-white); margin: 0;">
				Browse by Brand
			</h2>
		</div>

		<!-- Brands Grid -->
		<div class="brands-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
			<?php foreach ( $child_categories as $category ) : ?>
				<?php
				$category_link = get_term_link( $category );
				if ( is_wp_error( $category_link ) ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $category_link ); ?>"
				   class="brand-card"
				   style="display: block; padding: 1.5rem 1rem; border: 2px solid var(--wp--preset--color--brutal-border); text-decoration: none; transition: none; background: transparent;">

					<div style="font-family: var(--wp--preset--font-family--mono); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--wp--preset--color--off-white); margin-bottom: 0.5rem;">
						<?php echo esc_html( $category->name ); ?>
					</div>

					<div style="font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey);">
						<?php echo esc_html( $category->count ); ?> <?php echo $category->count === 1 ? 'review' : 'reviews'; ?>
					</div>
				</a>
			<?php endforeach; ?>
		</div>

		<style>
			.brand-card:hover,
			.brand-card:focus {
				border-color: var(--wp--preset--color--acid-lime) !important;
				background-color: rgba(204, 255, 0, 0.05) !important;
				outline: none;
			}

			.brand-card:focus-visible {
				outline: 2px solid var(--wp--preset--color--acid-lime);
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
	<?php
	return ob_get_clean();
}
add_shortcode( 'gcb_category_children', 'gcb_category_children_shortcode' );

/**
 * Force search results to order by date descending instead of relevance
 * Ensures most recent articles appear first on search results page
 * Applies to both main query and query blocks on search pages
 */
function gcb_force_search_order_by_date( $query ) {
	// Skip admin queries
	if ( is_admin() ) {
		return $query;
	}

	// Apply to main search query
	if ( $query->is_search() && $query->is_main_query() ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	// Also apply to query blocks on search pages (not main query)
	// Query blocks have is_search() false, so check if we're on a search page
	if ( $query->is_search() && ! $query->is_main_query() ) {
		// This is a query block on a search page - ensure it uses search term
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	return $query;
}
add_action( 'pre_get_posts', 'gcb_force_search_order_by_date' );

/**
 * Add Fusion Builder theme support
 *
 * Enables Fusion Builder to work with this FSE theme by declaring compatibility.
 * Required for legacy content created with Fusion Builder shortcodes.
 */
function gcb_add_fusion_builder_support(): void {
	// Declare Fusion Builder compatibility
	add_theme_support( 'fusion-builder' );
	add_theme_support( 'fusion-core' );

	// Allow Fusion Builder to load its assets
	add_filter( 'fusion_builder_enabled', '__return_true' );
}
add_action( 'after_setup_theme', 'gcb_add_fusion_builder_support' );

/**
 * Enqueue jQuery for Fusion Builder compatibility
 *
 * Fusion Builder requires jQuery to render shortcodes.
 * WordPress includes jQuery by default, but FSE themes don't automatically enqueue it.
 */
function gcb_enqueue_jquery(): void {
	// Only on frontend, not in admin
	if ( is_admin() ) {
		return;
	}

	// Enqueue WordPress's bundled jQuery (in no-conflict mode)
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'gcb_enqueue_jquery' );

/**
 * Fallback: Process video shortcodes if Fusion Builder is not active
 *
 * Ensures YouTube embeds work even when Fusion Builder plugin is inactive.
 * Uses WordPress's built-in oEmbed functionality as a fallback.
 */
function gcb_process_fusion_video_fallback( $content ): string {
	// Only apply fallback if Fusion Builder is NOT active
	// Check both class existence and if shortcode is actually registered
	$fusion_active = class_exists( 'FusionBuilder' ) && shortcode_exists( 'fusion_youtube' );

	if ( $fusion_active ) {
		return $content;
	}

	// Look for fusion_youtube shortcode pattern with id parameter
	$pattern = '/\[fusion_youtube[^\]]*id=["\']?([^"\'\s\]]+)["\']?[^\]]*\]/i';
	$content = preg_replace_callback( $pattern, function( $matches ) {
		$youtube_id = trim( $matches[1] );

		// Generate responsive YouTube embed HTML with explicit dimensions
		// Using absolute positioning for the iframe ensures it fills the container
		$embed_html = '<div class="fusion-youtube video-shortcode" style="position: relative; width: 100%; max-width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; margin: 1rem 0;">' .
			'<iframe ' .
				'src="' . esc_url( 'https://www.youtube.com/embed/' . $youtube_id ) . '" ' .
				'style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" ' .
				'frameborder="0" ' .
				'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' .
				'referrerpolicy="strict-origin-when-cross-origin" ' .
				'allowfullscreen>' .
			'</iframe>' .
		'</div>';

		return $embed_html;
	}, $content );

	// Also handle generic [fusion_code] wrapped YouTube URLs
	$pattern = '/\[fusion_code[^\]]*\](https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)[^\[]*)\[\/fusion_code\]/i';
	$content = preg_replace_callback( $pattern, function( $matches ) {
		$youtube_id = trim( $matches[2] );

		// Generate responsive YouTube embed HTML
		$embed_html = '<div class="fusion-youtube video-shortcode" style="position: relative; width: 100%; max-width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; margin: 1rem 0;">' .
			'<iframe ' .
				'src="' . esc_url( 'https://www.youtube.com/embed/' . $youtube_id ) . '" ' .
				'style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" ' .
				'frameborder="0" ' .
				'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' .
				'referrerpolicy="strict-origin-when-cross-origin" ' .
				'allowfullscreen>' .
			'</iframe>' .
		'</div>';

		return $embed_html;
	}, $content );

	// Handle plain YouTube URLs in content using WordPress oEmbed
	global $wp_embed;
	if ( $wp_embed ) {
		$content = $wp_embed->autoembed( $content );
	}

	return $content;
}
add_filter( 'the_content', 'gcb_process_fusion_video_fallback', 8 ); // Run before do_shortcode (priority 11)

/**
 * Register fallback shortcode handlers for Fusion Builder shortcodes
 *
 * These only register if Fusion Builder hasn't already registered them.
 * This ensures videos work even when Fusion Builder plugin is inactive.
 */
function gcb_register_fusion_fallback_shortcodes(): void {
	// Only register if fusion_youtube shortcode doesn't already exist
	if ( ! shortcode_exists( 'fusion_youtube' ) ) {
		add_shortcode( 'fusion_youtube', 'gcb_fusion_youtube_shortcode_fallback' );
	}

	// Only register if fusion_code shortcode doesn't already exist
	if ( ! shortcode_exists( 'fusion_code' ) ) {
		add_shortcode( 'fusion_code', 'gcb_fusion_code_shortcode_fallback' );
	}
}
add_action( 'init', 'gcb_register_fusion_fallback_shortcodes', 999 ); // Run very late

/**
 * Fallback handler for [fusion_youtube] shortcode
 *
 * @param array $atts Shortcode attributes.
 * @return string YouTube embed HTML.
 */
function gcb_fusion_youtube_shortcode_fallback( array $atts ): string {
	$atts = shortcode_atts(
		array(
			'id'     => '',
			'width'  => '',
			'height' => '',
		),
		$atts,
		'fusion_youtube'
	);

	$youtube_id = trim( $atts['id'] );

	if ( empty( $youtube_id ) ) {
		return '<!-- YouTube ID missing -->';
	}

	// Generate responsive YouTube embed
	return '<div class="fusion-youtube video-shortcode" style="position: relative; width: 100%; max-width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; margin: 1rem 0;">' .
		'<iframe ' .
			'src="' . esc_url( 'https://www.youtube.com/embed/' . $youtube_id ) . '" ' .
			'style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" ' .
			'frameborder="0" ' .
			'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' .
			'referrerpolicy="strict-origin-when-cross-origin" ' .
			'allowfullscreen' .
			'title="YouTube video player">' .
		'</iframe>' .
	'</div>';
}

/**
 * Fallback handler for [fusion_code] shortcode
 *
 * @param array  $atts Shortcode attributes.
 * @param string $content Shortcode content.
 * @return string Processed content.
 */
function gcb_fusion_code_shortcode_fallback( array $atts, string $content = '' ): string {
	// Check if content contains a YouTube URL
	if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/i', $content, $matches ) ) {
		$youtube_id = $matches[1];

		// Return YouTube embed
		return '<div class="fusion-youtube video-shortcode" style="position: relative; width: 100%; max-width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; margin: 1rem 0;">' .
			'<iframe ' .
				'src="' . esc_url( 'https://www.youtube.com/embed/' . $youtube_id ) . '" ' .
				'style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" ' .
				'frameborder="0" ' .
				'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' .
				'referrerpolicy="strict-origin-when-cross-origin" ' .
				'allowfullscreen' .
				'title="YouTube video player">' .
			'</iframe>' .
		'</div>';
	}

	// Otherwise, decode and return as-is (base64 encoded content)
	if ( ! empty( $content ) ) {
		// Fusion Builder encodes content in base64
		$decoded = base64_decode( $content );
		if ( $decoded !== false ) {
			return $decoded;
		}
	}

	return $content;
}

/**
 * Disable Jetpack lazy load for Fusion Builder galleries
 *
 * Jetpack's lazy load interferes with Fusion Builder's JavaScript grid calculations
 * by setting incorrect sizes attributes on images before Fusion runs.
 * This causes galleries to render as single column instead of grid layout.
 *
 * @param array $classes CSS classes to exclude from lazy loading.
 * @return array Modified classes array.
 */
function gcb_disable_jetpack_lazy_load_for_fusion_galleries( array $classes ): array {
	// Exclude Fusion Builder gallery images from Jetpack lazy load
	$fusion_gallery_classes = array(
		'fusion-gallery-image',
		'awb-gallery-image',
		'fusion-gallery',
		'awb-image-gallery',
		'fusion-grid-column',
	);

	return array_merge( $classes, $fusion_gallery_classes );
}
add_filter( 'jetpack_lazy_images_blacklisted_classes', 'gcb_disable_jetpack_lazy_load_for_fusion_galleries' );

/**
 * Register navigation menus
 *
 * Registers theme menu locations for use with WordPress nav menu system.
 * Primary menu displays in header (both desktop and mobile).
 */
function gcb_register_nav_menus(): void {
	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'gcb-brutalist' ),
		)
	);
}
add_action( 'after_setup_theme', 'gcb_register_nav_menus' );

/**
 * Custom Navigation Walker for GCB Brutalist Theme
 *
 * Outputs flat <a> tags without <ul>/<li> wrappers to maintain
 * existing Editorial Brutalism styling and E2E test compatibility.
 *
 * Based on WordPress Walker_Nav_Menu with minimal output.
 */
class GCB_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Add submenu wrapper with proper classes
		$indent = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\" aria-label=\"Submenu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		// Close submenu wrapper
		$indent = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Starts the element output.
	 *
	 * Outputs <li> wrapper with <a> tag inside. Adds dropdown classes for items with children.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		// Build <li> classes
		$li_classes = array( 'menu-item' );

		// Add class for items with children
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$li_classes[] = 'has-dropdown';
		}

		// Add current page class if this is the active page
		if ( in_array( 'current-menu-item', $item->classes, true ) ) {
			$li_classes[] = 'current-menu-item';
		}

		// Add current parent class if this is a parent of the active page
		if ( in_array( 'current-menu-parent', $item->classes, true ) || in_array( 'current-menu-ancestor', $item->classes, true ) ) {
			$li_classes[] = 'current-menu-parent';
		}

		$li_class_attr = ' class="' . esc_attr( implode( ' ', $li_classes ) ) . '"';

		// Start <li>
		$output .= $indent . '<li' . $li_class_attr . '>';

		// Build link classes
		$link_classes = array();

		// Add link class based on context (desktop vs mobile)
		if ( isset( $args->link_class ) && ! empty( $args->link_class ) ) {
			$link_classes[] = $args->link_class;
		}

		// Add submenu class for depth
		if ( $depth > 0 ) {
			$link_classes[] = 'submenu-link';
		}

		$link_class_attr = ! empty( $link_classes ) ? ' class="' . esc_attr( implode( ' ', $link_classes ) ) . '"' : '';

		// Build link attributes
		$attributes  = '';
		$attributes .= ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_url( $item->url ) . '"' : '';

		// Add ARIA for dropdown toggle
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$attributes .= ' aria-haspopup="true" aria-expanded="false"';
		}

		// Output link
		$output .= '<a' . $link_class_attr . $attributes . '>';
		$output .= esc_html( $item->title );

		// Add dropdown indicator for items with children
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$output .= '<span class="dropdown-indicator" aria-hidden="true">▼</span>';
		}

		$output .= '</a>';
	}

	/**
	 * Ends the element output.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Close <li>
		$output .= "</li>\n";
	}
}

/**
 * Fallback function for primary menu
 *
 * Displays default menu items if no menu is assigned.
 * Maintains backward compatibility with static menu.
 *
 * @param array $args Menu arguments.
 */
function gcb_primary_menu_fallback( $args ) {
	// Extract link class from args
	$link_class = isset( $args['link_class'] ) ? $args['link_class'] : 'nav-link';

	// Default menu items
	$menu_items = array(
		array(
			'url'   => home_url( '/car-reviews/' ),
			'title' => 'Car Reviews',
		),
		array(
			'url'   => home_url( '/car-news/' ),
			'title' => 'Car News',
		),
		array(
			'url'   => home_url( '/electric-cars/' ),
			'title' => 'Electric Cars',
		),
		array(
			'url'   => home_url( '/brands/' ),
			'title' => 'Brands',
		),
	);

	// Output links
	foreach ( $menu_items as $item ) {
		printf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $item['url'] ),
			esc_attr( $link_class ),
			esc_html( $item['title'] )
		);
	}
}
