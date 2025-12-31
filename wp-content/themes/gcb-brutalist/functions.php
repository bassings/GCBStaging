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
 * Enqueue sticker drag-and-drop assets
 *
 * Loads JavaScript and CSS for the social sticker feature.
 * Only enqueues on frontend singular pages (posts/pages).
 */
function gcb_enqueue_sticker_assets(): void {
	// Only load on frontend
	if ( is_admin() ) {
		return;
	}

	// Enqueue JavaScript
	wp_enqueue_script(
		'gcb-stickers',
		get_template_directory_uri() . '/assets/js/stickers.js',
		array(), // No dependencies (vanilla JS)
		filemtime( get_template_directory() . '/assets/js/stickers.js' ), // Cache bust on file change
		true // Load in footer
	);

	// Enqueue CSS
	wp_enqueue_style(
		'gcb-stickers',
		get_template_directory_uri() . '/assets/css/stickers.css',
		array(),
		filemtime( get_template_directory() . '/assets/css/stickers.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'gcb_enqueue_sticker_assets' );

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
 * Auto-inject stickers into post content
 *
 * Injects 3 draggable stickers into every singular post/page.
 * Stickers are positioned deterministically based on post ID.
 *
 * @param string $content Post content.
 * @return string Modified content with stickers.
 */
function gcb_inject_stickers( string $content ): string {
	// Only inject on singular posts/pages (not admin, not archives)
	if ( ! is_singular() || is_admin() ) {
		return $content;
	}

	$post_id = get_the_ID();

	// Generate 3 stickers with deterministic positions
	$stickers_html = '';
	for ( $i = 0; $i < 3; $i++ ) {
		$stickers_html .= gcb_generate_sticker_html( $post_id, $i );
	}

	// Inject after first paragraph
	$paragraphs = explode( '</p>', $content );
	if ( count( $paragraphs ) > 1 ) {
		$paragraphs[0] .= '</p>' . $stickers_html;
		return implode( '</p>', $paragraphs );
	}

	// Fallback: Append to content
	return $content . $stickers_html;
}
add_filter( 'the_content', 'gcb_inject_stickers', 20 );

/**
 * Generate single sticker HTML
 *
 * Creates a draggable sticker element with deterministic positioning.
 * Uses post ID + index as seed for pseudo-random but consistent placement.
 *
 * @param int $post_id Post ID.
 * @param int $index Sticker index (0-2).
 * @return string Sticker HTML markup.
 */
function gcb_generate_sticker_html( int $post_id, int $index ): string {
	// Deterministic pseudo-random positioning based on post ID + index
	$seed         = $post_id * 100 + $index;
	$top_percent  = ( ( $seed * 17 ) % 60 ) + 10; // 10-70%
	$left_percent = ( ( $seed * 23 ) % 70 ) + 5;  // 5-75%

	// Sticker variants (SVG inline for performance)
	$sticker_types = array(
		'heart'     => gcb_get_heart_svg(),
		'star'      => gcb_get_star_svg(),
		'lightning' => gcb_get_lightning_svg(),
	);

	$type = array_keys( $sticker_types )[ $index % 3 ];
	$svg  = $sticker_types[ $type ];

	return sprintf(
		'<div class="gcb-sticker"
			  data-post-id="%d"
			  data-sticker-index="%d"
			  data-touch-enabled="true"
			  style="--initial-top: %d%%; --initial-left: %d%%;">
			%s
		</div>',
		esc_attr( $post_id ),
		esc_attr( $index ),
		esc_attr( $top_percent ),
		esc_attr( $left_percent ),
		$svg // SVG already safe (no user input)
	);
}

/**
 * Heart sticker SVG
 *
 * Acid Lime background with Void Black heart icon.
 * 2px brutalist border.
 *
 * @return string SVG markup.
 */
function gcb_get_heart_svg(): string {
	return '<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="60" height="60" fill="#CCFF00"/>
		<rect x="1" y="1" width="58" height="58" fill="none" stroke="#050505" stroke-width="2"/>
		<path d="M30 45L15 30C12 27 12 22 15 19C18 16 22 16 25 19L30 24L35 19C38 16 42 16 45 19C48 22 48 27 45 30L30 45Z" fill="#050505"/>
	</svg>';
}

/**
 * Star sticker SVG
 *
 * Chrome (silver) background with Void Black star icon.
 * 2px brutalist border.
 *
 * @return string SVG markup.
 */
function gcb_get_star_svg(): string {
	return '<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="60" height="60" fill="#C0C0C0"/>
		<rect x="1" y="1" width="58" height="58" fill="none" stroke="#050505" stroke-width="2"/>
		<path d="M30 15L33 27H45L35 34L38 46L30 39L22 46L25 34L15 27H27L30 15Z" fill="#050505"/>
	</svg>';
}

/**
 * Lightning sticker SVG
 *
 * Error Red background with Hyper White lightning bolt.
 * 2px brutalist border.
 *
 * @return string SVG markup.
 */
function gcb_get_lightning_svg(): string {
	return '<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
		<rect width="60" height="60" fill="#FF3333"/>
		<rect x="1" y="1" width="58" height="58" fill="none" stroke="#050505" stroke-width="2"/>
		<path d="M35 10L20 30H28L25 50L40 30H32L35 10Z" fill="#FAFAFA"/>
	</svg>';
}

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
