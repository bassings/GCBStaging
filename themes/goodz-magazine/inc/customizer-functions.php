<?php
/**
 * Customizer specific functions
 *
 * @package goodz-magazine
 */

// List all categories in dropdown
function goodz_magazine_get_categories_select() {
    $teh_cats = get_categories();
    $results = [];

    $count = is_countable( $teh_cats ) ? count( $teh_cats ) : 0;
    $results['default'] = esc_html__( '-- Select --', 'goodz-magazine' );

    for ( $i=0; $i < $count; $i++ ) {
        if ( isset( $teh_cats[$i] ) )
            $results[$teh_cats[$i]->slug] = $teh_cats[$i]->name;
        else
            $count++;
    }
    return $results;
}
