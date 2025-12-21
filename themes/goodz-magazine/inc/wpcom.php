<?php
/**
 * WordPress.com-specific functions and definitions.
 *
 * This file is centrally included from `wp-content/mu-plugins/wpcom-theme-compat.php`.
 *
 * @package goodz-magazine
 */

/**
 * Adds support for wp.com-specific theme functions.
 *
 * @global array $themecolors
 */
function goodz_magazine_wpcom_setup() {
    global $themecolors;

    // Set theme colors for third party services.
    if ( ! isset( $themecolors ) ) {
        $themecolors = array(
            'bg'     => 'ffffff',
            'border' => '000000',
            'text'   => '838383',
            'link'   => '000000',
            'url'    => '000000',
        );
    }

    // Add print style support
    add_theme_support( 'print-style' );
}
add_action( 'after_setup_theme', 'goodz_magazine_wpcom_setup' );