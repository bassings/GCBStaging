<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package goodz-magazine
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function goodz_magazine_body_classes( $classes ) {
	// Get global layout setting
	$global_layout = get_theme_mod( 'archive_layout_setting', 'boxed' );
	$single_layout = get_theme_mod( 'single_layout_setting', 'boxed' );
	$sticky_header = get_theme_mod( 'sticky_header_setting', 0 );
	$slider_width  = get_theme_mod( 'featured_slider_width', 0 );

	if ( is_single() ) {
		$classes[] = $single_layout . '-single';
	}
	else {
		if ( ! is_page() || is_front_page() ) {
			$classes[] = $global_layout . '-blog';
		}
	}

	if ( $sticky_header ) {
		$classes[] = 'sticky-header';
	}

	if ( is_home() || is_front_page() ) {
		if ( $slider_width ) {
			$classes[] = 'featured-slider-fullwidth';
		}
	}

	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of tk-theme-frontend when viewing frontend.
	if ( !is_admin() ) {
		$classes[] = 'tk-theme-frontend';
	}

	return $classes;
}
add_filter( 'body_class', 'goodz_magazine_body_classes' );

/**
 * Filter content column classes
 *
 * @since goodz magazine 1.0
 */
function goodz_magazine_content_cols() {

	// Primary container classes
	$cols = 'col-lg-12';

	// Get global layout setting
	$global_layout = get_theme_mod( 'archive_layout_setting', 'boxed' );

	if ( is_active_sidebar( 'sidebar-1' ) ) {

		if ( 'boxed' == $global_layout ) {
			$cols = 'col-md-9 has-sidebar';
		}
		else {
			$cols = 'col-lg-10 col-md-9 has-sidebar';
		}
	}

	if ( is_single() ) {
		// Container classes relevant to sidebar
		$cols = 'no-sidebar';

		if ( is_active_sidebar( 'sidebar-1' ) ) {
		    $cols = 'has-sidebar';
		}
	}

	echo esc_attr( $cols );
}

/**
 * Filter sidebar column classes
 *
 * @since goodz magazine 1.0
 */
function goodz_magazine_sidebar_cols() {

	// Get global layout setting
	$global_layout = get_theme_mod( 'archive_layout_setting', 'boxed' );

	if ( is_archive() || is_home() || is_search() ) {

		if ( 'boxed' == $global_layout ) {
			$cols = 'col-md-3';
		}
		else {
			$cols = 'col-lg-2 col-md-3';
		}
	}

	else {
		$cols = 'col-md-3';
	}

	echo esc_attr( $cols );
}

/**
 * Filter post_class() additional classes
 *
 * @since goodz magazine 1.0
 */
function goodz_magazine_post_classes( $classes, $class, $post_id ) {
	// Get global layout setting
	$global_layout     = get_theme_mod( 'archive_layout_setting', 'boxed' );
	$two_column_layout = get_theme_mod( 'two_columns_layout_setting', 'three-columns' );

	if ( ! is_single() ) :

		// If global layout is set to boxed
		if ( 'boxed' == $global_layout ) {
		    if ( is_sticky() ) {
		    	if ( 'two-columns' == $two_column_layout ) {
		    		$classes[] = 'col-sm-6';
		    	}
		    	else {
		    		if ( is_active_sidebar( 'sidebar-1' ) ) {
		    			$classes[] = 'col-sm-12';
		    		}
		    		else {
		    			$classes[] = 'col-lg-8 col-sm-12';
		    		}
		    	}
			}
			else {
				if ( 'two-columns' == $two_column_layout ) {
					$classes[] = 'col-sm-6';
				}
				else {
					if ( is_active_sidebar( 'sidebar-1' ) ) {
						$classes[] = 'col-sm-6';
					}
					else {
						$classes[] = 'col-lg-4 col-sm-6';
					}
				}
			}
		}
		else {
			if ( is_sticky() ) {
				if ( 'two-columns' == $two_column_layout ) {
					$classes[] = 'col-sm-6';
				}
				else {
					if ( is_active_sidebar( 'sidebar-1' ) ) {
						$classes[] = 'col-lg-8 col-sm-12';
					}
					else {
						$classes[] = 'col-lg-6 col-sm-12';
					}
				}
			}
			else {
				if ( 'two-columns' == $two_column_layout ) {
					$classes[] = 'col-sm-6';
				}
				else {
					if ( is_active_sidebar( 'sidebar-1' ) ) {
						$classes[] = 'col-lg-4 col-sm-6';
					}
					else {
						$classes[] = 'col-lg-3 col-sm-6';
					}
				}
			}
		}

	endif;

    if ( 'jetpack-portfolio' == get_post_type() ) {
        $classes[] = 'post';
    }

	return $classes;

}
add_filter( 'post_class', 'goodz_magazine_post_classes', 10, 3 );

/**
 * Remove Related posts from content
 *
 * @since  goodz magazine 1.0
 */
function goodz_magazine_remove_related_posts() {
	if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
		$jprp     = Jetpack_RelatedPosts::init();
		$callback = array( $jprp, 'filter_add_target_to_dom' );
	    remove_filter( 'post_flair', $callback, 40 );
	}
	else {
		return;
	}
}
add_filter( 'wp', 'goodz_magazine_remove_related_posts', 20 );

/**
 * Check for embed content in post and extract
 *
 * @since goodz magazine 1.0
 */
function goodz_magazine_get_embed_media() {
    $content = get_the_content();
    $embeds  = get_media_embedded_in_content( $content );

    if ( !empty( $embeds ) ) {
        //check what is the first embed containg video tag, youtube or vimeo
        foreach( $embeds as $embed ) {
            if ( strpos( $embed, 'video' ) || strpos( $embed, 'youtube' ) || strpos( $embed, 'vimeo' ) ) {
                return $embed;
            }
        }
    } else {
        //No video embedded found
        return false;
    }
}

/**
 * Filter content for gallery post format
 *
 * @since  goodz-magazine 1.0
 */
function goodz_magazine_filter_post_content( $content ) {
	$orig_content = $content;
	if ( 'video' == get_post_format() && 'post' == get_post_type() ) {
	    $video_content = goodz_magazine_get_embed_media();
	    if ( $video_content ) {
	        $content = str_replace( $video_content, '', $content );
	    }
	}

	if ( 'gallery' == get_post_format() && 'post' == get_post_type() ) {
	    $regex   = '/\[gallery.*]/';
	    $content = preg_replace( $regex, '', $content, 1 );
	}

	// Escape content if it has been filtered.
	if ( $content !== $orig_content ) {
	  $content = wp_kses_post( $content );
	}

	return $content;
}
add_filter( 'the_content', 'goodz_magazine_filter_post_content' );

/**
 * Add Read More to post excerpt
 *
 * @since  goodz-magazine 1.0
 */
function new_excerpt_more( $excerpt ) {
	return $excerpt .' <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html__( 'Read more', 'goodz-magazine' ) . '</a>';
}
add_filter( 'the_excerpt', 'new_excerpt_more' );
