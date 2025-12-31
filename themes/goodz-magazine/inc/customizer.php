<?php
/**
 * goodz-magazine Theme Customizer.
 *
 * @package goodz-magazine
 */

/**
 * Load Customizer Specific functions
 */
get_template_part( 'inc/customizer', 'functions' );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function goodz_magazine_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

    /**
     * PANELS
     */
    $wp_customize->add_panel( 'theme_options_panel', array(
        'priority'       => 120,
        'capability'     => 'edit_theme_options',
        'theme_supports' => '',
        'title'          => esc_html__( 'Theme Options', 'goodz-magazine' ),
        'description'    => esc_html__( 'Goodz Magazine Theme Options', 'goodz-magazine' )
    ) );

    /**
     * SECTIONS
     */
    $wp_customize->add_section( 'layout_section', array(
        'title'    => esc_html__( 'Layout Settings', 'goodz-magazine' ),
        'priority' => 120,
        'panel'    => 'theme_options_panel'
    ) );

    $wp_customize->add_section( 'header_section', array(
        'title'    => esc_html__( 'Header Settings', 'goodz-magazine' ),
        'priority' => 120,
        'panel'    => 'theme_options_panel'
    ) );

    $wp_customize->add_section( 'featured_slider_settings', array(
        'title'    => esc_html__( 'Featured Slider', 'goodz-magazine' ),
        'priority' => 120,
        'panel'    => 'theme_options_panel'
    ) );

    /**
     * SETTINGS
     */

    // Featured slider width
    $wp_customize->add_setting( 'featured_slider_width', array(
        'default'           => false,
        'sanitize_callback' => 'goodz_magazine_sanitize_checkbox',
    ) );

    $wp_customize->add_control( 'featured_slider_width', array(
        'settings' => 'featured_slider_width',
        'label'    => esc_html__( 'Enable full-width slider display', 'goodz-magazine' ),
        'section'  => 'featured_slider_settings',
        'type'     => 'checkbox'
    ) );

    // Global layout
    $wp_customize->add_setting( 'archive_layout_setting', array(
        'default'           => 'boxed',
        'sanitize_callback' => 'goodz_magazine_sanitize_layout_select',
    ));

    $wp_customize->add_control( 'archive_layout_setting', array(
        'label'    => esc_html__( 'Archive Pages Layout', 'goodz-magazine' ),
        'priority' => 0,
        'section'  => 'layout_section',
        'type'     => 'select',
        'choices'  => array(
            'boxed'     => esc_html__( 'Fixed width', 'goodz-magazine' ),
            'fullwidth' => esc_html__( 'Full width', 'goodz-magazine' )
        ),
    ) );

    // Single Layout
    $wp_customize->add_setting( 'single_layout_setting', array(
        'sanitize_callback' => 'goodz_magazine_sanitize_layout_select',
    ));

    $wp_customize->add_control( 'single_layout_setting', array(
        'label'    => esc_html__( 'Single Page/Post Layout', 'goodz-magazine' ),
        'priority' => 0,
        'section'  => 'layout_section',
        'type'     => 'select',
        'choices'  => array(
            'boxed'     => esc_html__( 'Fixed width', 'goodz-magazine' ),
            'fullwidth' => esc_html__( 'Full width', 'goodz-magazine' )
        ),
    ) );

    // Two / Three Columns Layout
    $wp_customize->add_setting( 'two_columns_layout_setting', array(
        'sanitize_callback' => 'goodz_magazine_sanitize_column_layout',
        'default'           => 'three-columns'
    ) );

    $wp_customize->add_control( 'two_columns_layout_setting', array(
        'settings' => 'two_columns_layout_setting',
        'label'    => esc_html__( 'Blog Layout', 'goodz-magazine' ),
        'priority' => 0,
        'section'  => 'layout_section',
        'type'     => 'select',
        'choices'  => array(
            'two-columns'   => esc_html__( 'Two columns', 'goodz-magazine' ),
            'three-columns' => esc_html__( 'Three columns', 'goodz-magazine' )
        ),
    ) );

    // Sticky header
    $wp_customize->add_setting( 'sticky_header_setting', array(
        'sanitize_callback' => 'goodz_magazine_sanitize_checkbox',
    ));

    $wp_customize->add_control( 'sticky_header_setting', array(
        'label'    => esc_html__( 'Enable Sticky Header', 'goodz-magazine' ),
        'priority' => 0,
        'section'  => 'header_section',
        'type'     => 'checkbox'
    ) );

}
add_action( 'customize_register', 'goodz_magazine_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function goodz_magazine_customize_preview_js() {
	wp_enqueue_script( 'goodz_magazine_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'goodz_magazine_customize_preview_js' );

/**
 * Load Customizer Sanitization functions
 */
get_template_part( 'inc/customizer', 'sanitize' );
