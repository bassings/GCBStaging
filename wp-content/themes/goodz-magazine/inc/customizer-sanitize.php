<?php
/**
 * Sanitization functions for Customizer
 *
 * @package  goodz-magazine
 */

/**
 * Sanitize checkbox
 *
 * @param string $checkbox Option checkbox.
 * @return boolean Filtered option.
 */
function goodz_magazine_sanitize_checkbox( $checkbox ) {
    if ( $checkbox ) {
        $checkbox = '1';
    } else {
        $checkbox = false;
    }
    return $checkbox;
}

/**
 * Sanitize archive layout select
 *
 * @param string $selection Option selection.
 * @return string Filtered option.
 */
function goodz_magazine_sanitize_layout_select( $selection ) {
    $options = array( 'boxed', 'fullwidth' );

    if ( in_array( $selection, $options ) ) {
        return $selection;
    }
    else {
        return 'boxed';
    }
}

/**
 * Sanitize columns layout select
 *
 * @param string $selection Option selection.
 * @return string Filtered option.
 */
function goodz_magazine_sanitize_column_layout( $selection ) {
    $options = array( 'two-columns', 'three-columns' );

    if ( in_array( $selection, $options ) ) {
        return $selection;
    }
    else {
        return 'three-columns';
    }
}


