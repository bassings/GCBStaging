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
