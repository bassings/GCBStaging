<?php
/**
 * Avada Child Theme Functions
 *
 * Performance optimizations for non-WooCommerce site
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue parent and child theme styles
 */
function avada_child_enqueue_styles() {
    wp_enqueue_style( 'avada-parent-stylesheet', get_template_directory_uri() . '/style.css', array(), '7.14.2' );
    wp_enqueue_style( 'avada-child-stylesheet', get_stylesheet_directory_uri() . '/style.css', array( 'avada-parent-stylesheet' ), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'avada_child_enqueue_styles', 20 );

/**
 * Disable WooCommerce integration in Avada
 * Since WooCommerce is not installed, prevent loading unnecessary assets
 */
function avada_child_disable_woocommerce() {
    // Disable WooCommerce support in Avada
    add_filter( 'fusion_load_woocommerce', '__return_false' );

    // Remove WooCommerce-specific Avada features
    remove_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'avada_child_disable_woocommerce', 11 );

/**
 * Dequeue WooCommerce assets if they somehow get enqueued
 */
function avada_child_dequeue_woocommerce_assets() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        // Dequeue Avada's WooCommerce styles
        wp_dequeue_style( 'avada-woocommerce' );
        wp_deregister_style( 'avada-woocommerce' );

        // Dequeue Avada's WooCommerce scripts
        wp_dequeue_script( 'avada-woocommerce' );
        wp_deregister_script( 'avada-woocommerce' );

        // Dequeue any WooCommerce-related Fusion styles
        wp_dequeue_style( 'fusion-woocommerce' );
        wp_deregister_style( 'fusion-woocommerce' );
    }
}
add_action( 'wp_enqueue_scripts', 'avada_child_dequeue_woocommerce_assets', 100 );
add_action( 'wp_print_styles', 'avada_child_dequeue_woocommerce_assets', 100 );

/**
 * Performance: Disable unused Avada features
 * Uncomment features you want to disable
 */
function avada_child_disable_unused_features() {
    // Disable emojis (already in wp-config, but reinforced here)
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );

    // Disable WordPress embeds if not needed
    // wp_deregister_script( 'wp-embed' );
}
add_action( 'init', 'avada_child_disable_unused_features' );
