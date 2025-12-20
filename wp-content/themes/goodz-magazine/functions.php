<?php
/**
 * goodz-magazine functions and definitions.
 *
 * @link https://codex.wordpress.org/Functions_File_Explained
 *
 * @package goodz-magazine
 */

if ( ! function_exists( 'goodz_magazine_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function goodz_magazine_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on goodz-magazine, use a find and replace
	 * to change 'goodz-magazine' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'goodz-magazine', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	/**
	 * Add thumbnail image sizes
	 */
	add_image_size( 'goodz-magazine-archive-featured-image', 692, 463, true );
	add_image_size( 'goodz-magazine-sticky-featured-image', 1084, 725, true );
	add_image_size( 'goodz-magazine-single-featured-image', 1620, 999999, false );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'goodz-magazine' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 */
	add_theme_support( 'post-formats', array(
		'image',
		'gallery',
		'video',
		'quote',
		'link',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'goodz_magazine_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Add editor style
	add_editor_style( array( 'css/editor-style.css' ) );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Make Gutenberg embeds responsive.
	add_theme_support( 'responsive-embeds' );

	// Adds support for editor font sizes.
	add_theme_support( 'editor-font-sizes', array(
		array(
			'name'      => __( 'small', 'goodz-magazine' ),
			'shortName' => __( 'S', 'goodz-magazine' ),
			'size'      => 15,
			'slug'      => 'small'
		),
		array(
			'name'      => __( 'regular', 'goodz-magazine' ),
			'shortName' => __( 'M', 'goodz-magazine' ),
			'size'      => 18,
			'slug'      => 'regular'
		),
		array(
			'name'      => __( 'large', 'goodz-magazine' ),
			'shortName' => __( 'L', 'goodz-magazine' ),
			'size'      => 24,
			'slug'      => 'large'
		),
		array(
			'name'      => __( 'larger', 'goodz-magazine' ),
			'shortName' => __( 'XL', 'goodz-magazine' ),
			'size'      => 32,
			'slug'      => 'larger'
		),
		array(
			'name'      => __( 'huge', 'goodz-magazine' ),
			'shortName' => __( 'XXL', 'goodz-magazine' ),
			'size'      => 40,
			'slug'      => 'huge'
		)
	) );


}
endif; // goodz_magazine_setup
add_action( 'after_setup_theme', 'goodz_magazine_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function goodz_magazine_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'goodz_magazine_content_width', 1920 );
}
add_action( 'after_setup_theme', 'goodz_magazine_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function goodz_magazine_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'goodz-magazine' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widgets 1', 'goodz-magazine' ),
		'id'            => 'footer-widgets-1',
		'before_widget' => '<aside class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widgets 2', 'goodz-magazine' ),
		'id'            => 'footer-widgets-2',
		'before_widget' => '<aside class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widgets 3', 'goodz-magazine' ),
		'id'            => 'footer-widgets-3',
		'before_widget' => '<aside class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>'
	) );
}
add_action( 'widgets_init', 'goodz_magazine_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function goodz_magazine_scripts() {
	wp_enqueue_style( 'goodz-magazine-style', get_stylesheet_uri() );
	wp_enqueue_script( 'goodz-magazine-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );
	wp_enqueue_script( 'goodz-magazine-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );
	wp_enqueue_script( 'goodz-magazine-slick-slider', get_template_directory_uri() . '/js/slick/slick.js', false, false, true );
	wp_enqueue_script( 'goodz-magazine-call-scripts', get_template_directory_uri() . '/js/common.js', array( 'jquery', 'masonry' ), false, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$js_vars = array(
		'load_more_text' => esc_html__( 'Load More', 'goodz-magazine' )
	);

	wp_localize_script( 'goodz-magazine-call-scripts', 'js_vars', $js_vars );
}
add_action( 'wp_enqueue_scripts', 'goodz_magazine_scripts' );

/**
 * Gutenberg scripts and styles
 *
 */
if ( ! function_exists( 'goodz_gutenberg_scripts' ) ) :
function goodz_gutenberg_scripts() {
	wp_enqueue_style( 'goodz-gutenberg', get_stylesheet_directory_uri() . '/css/gutenberg.css' );
}
endif;
add_action( 'enqueue_block_editor_assets', 'goodz_gutenberg_scripts' );

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

