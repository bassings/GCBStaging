<?php
/**
 * Jetpack Compatibility File.
 *
 * @link https://jetpack.me/
 *
 * @package goodz-magazine
 */

/**
 * Add theme support for Infinite Scroll.
 * See: https://jetpack.me/support/infinite-scroll/
 */
function goodz_magazine_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container'      => 'post-load',
		'render'         => 'goodz_magazine_infinite_scroll_render',
		'footer'         => 'colophon',
		'footer_widgets' => array( 'footer-widgets-1', 'footer-widgets-2', 'footer-widgets-3' ),
		'wrapper'        => false
	) );

	// Add theme support for Responsive videos.
	add_theme_support( 'jetpack-responsive-videos' );

	add_theme_support( 'jetpack-portfolio' );

	// Add image size for jetpack's site logo
	add_image_size( 'goodz-magazine-logo', 340, 999999, false );

	// Add theme support for Site Logo
	add_theme_support( 'site-logo', array(
	    'size' => 'goodz-magazine-logo'
	) );


	// Add Featured Content Support
	add_theme_support( 'featured-content', array(
		'filter' => 'goodz_magazine_get_featured_posts'
	) );

	// Custom render function for Infinite Scroll.
	function goodz_magazine_infinite_scroll_render() {
		while ( have_posts() ) : the_post();
			get_template_part( 'template-parts/content', get_post_format() );
		endwhile;
	}

	// Featured posts filter function
	function goodz_magazine_get_featured_posts() {
	    return apply_filters( 'goodz_magazine_get_featured_posts', array() );
	}

	// A helper conditional function that returns a boolean value.
	function goodz_magazine_has_featured_posts() {
		return (bool) goodz_magazine_get_featured_posts();
	}

} // end function goodz_magazine_jetpack_setup
add_action( 'after_setup_theme', 'goodz_magazine_jetpack_setup' );


