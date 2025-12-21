<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package goodz-magazine
 */

if ( ! function_exists( 'goodz_magazine_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function goodz_magazine_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( '%s', 'post date', 'goodz-magazine' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'goodz-magazine' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.


	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( esc_html__( 'Comments', 'goodz-magazine' ), esc_html__( '1 Comment', 'goodz-magazine' ), esc_html__( '% Comments', 'goodz-magazine' ) );
		echo '</span>';
	}

	if ( is_single() ) {
		edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' );
	}

}
endif;

if ( ! function_exists( 'goodz_magazine_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function goodz_magazine_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' == get_post_type() ) {

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html__( '&nbsp;' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links">' . esc_html__( 'Tags: %1$s', 'goodz-magazine' ) . '</span>', $tags_list ); // WPCS: XSS OK.
		}
	}

}
endif;

if ( ! function_exists( 'goodz_magazine_entry_header' ) ) :
/**
 * Prints Categories above title
 */
function goodz_magazine_entry_header() {
	/* translators: used between list items, there is a space after the comma */
	$categories_list = get_the_category_list( esc_html__( ', ', 'goodz-magazine' ) );

	if ( 'link' == get_post_format() ) {
		printf( '<span class="cat-links">%1$s%2$s</span>', esc_html__( 'Link', 'goodz-magazine' ), edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' ) );
	}
	elseif ( 'quote' == get_post_format() ) {
		printf( '<span class="cat-links">%1$s%2$s</span>', esc_html__( 'Quote', 'goodz-magazine' ), edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' ) );
	}
	else {
		if ( $categories_list && goodz_magazine_categorized_blog() ) {
			printf( '<span class="cat-links">%1$s%2$s</span>', $categories_list, edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' ) ); // WPCS: XSS OK.
		}

		if ( 'jetpack-portfolio' == get_post_type() ) {
			$portfolio_terms = get_the_term_list( get_the_ID(), 'jetpack-portfolio-type', '', ', ', '' );
			printf( '<span class="cat-links">%1$s%2$s</span>', $portfolio_terms, edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' ) ); // WPCS: XSS OK.

		}

	}

}
endif;

/**
 * Display the archive title based on the queried object.
 *
 * @param string $before Optional. Content to prepend to the title. Default empty.
 * @param string $after  Optional. Content to append to the title. Default empty.
 */
function goodz_magazine_archive_title( $before = '', $after = '' ) {
	$title = '';
	if ( is_category() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span>' . single_cat_title( '', false ) . '</span>' );
	} elseif ( is_tag() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span>' . single_tag_title( '', false ) . '</span>' );
	} elseif ( is_author() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span class="vcard">' . get_the_author() . '</span>' );
	} elseif ( is_year() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span>' . get_the_date( esc_html_x( 'Y', 'yearly archives date format', 'goodz-magazine' ) ) . '</span>' );
	} elseif ( is_month() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span>' . get_the_date( esc_html_x( 'F Y', 'monthly archives date format', 'goodz-magazine' ) ) . '</span>' );
	} elseif ( is_day() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), '<span>' . get_the_date( esc_html_x( 'F j, Y', 'daily archives date format', 'goodz-magazine' ) ) . '</span>' );
	} elseif ( is_tax( 'post_format' ) ) {
		if ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = esc_html_x( 'Asides', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = esc_html_x( 'Galleries', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = esc_html_x( 'Images', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = esc_html_x( 'Videos', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = esc_html_x( 'Quotes', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = esc_html_x( 'Links', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = esc_html_x( 'Statuses', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = esc_html_x( 'Audio', 'post format archive title', 'goodz-magazine' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = esc_html_x( 'Chats', 'post format archive title', 'goodz-magazine' );
		}
	} elseif ( is_post_type_archive() ) {
		$title = sprintf( esc_html__( '%s', 'goodz-magazine' ), post_type_archive_title( '', false ) );
	} elseif ( is_tax() ) {
		$tax = get_taxonomy( get_queried_object()->taxonomy );
		/* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
		$title = sprintf( esc_html__( '%1$s: %2$s', 'goodz-magazine' ), $tax->labels->singular_name, '<span>' . single_term_title( '', false ) . '</span>' );
	} else {
		$title = esc_html__( 'Archives', 'goodz-magazine' );
	}

	/**
	 * Filter the archive title.
	 *
	 * @param string $title Archive title to be displayed.
	 */
	$title = apply_filters( 'get_the_archive_title', $title );

	if ( ! empty( $title ) ) {
		echo $before . $title . $after;  // WPCS: XSS OK.
	}
}


/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function goodz_magazine_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'goodz_magazine_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = is_countable( $all_the_cool_cats ) ? count( $all_the_cool_cats ) : 0;

		set_transient( 'goodz_magazine_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so goodz_magazine_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so goodz_magazine_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in goodz_magazine_categorized_blog.
 */
function goodz_magazine_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'goodz_magazine_categories' );
}
add_action( 'edit_category', 'goodz_magazine_category_transient_flusher' );
add_action( 'save_post',     'goodz_magazine_category_transient_flusher' );


/**
 * Displays post featured image
 *
 * @since  goodz-magazine 1.0
 */
function goodz_magazine_featured_image() {

	if ( has_post_thumbnail() ) :

		if ( is_single() ) { ?>

			<figure class="featured-image">
				<?php the_post_thumbnail( 'goodz-magazine-single-featured-image' ); ?>
			</figure>

		<?php } else { ?>

			<?php
				$thumb_size = 'goodz-magazine-archive-featured-image';

				if ( is_sticky() ) {
					$thumb_size = 'goodz-magazine-sticky-featured-image';
				}
			?>

			<figure class="featured-image">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $thumb_size ); ?></a>
			</figure>

		<?php }

	else :

		return;

	endif;

}

/**
 * Checks selected gallery size
 *
 * @since  goodz-magazine 1.0
 */

function goodz_magazine_gallery_size() {
	global $post;
	$gallery = get_post_gallery( $post, false );
	$sizes = $gallery['size'];

	return $sizes;
}

/**
 * Displays post featured image
 *
 * @since  goodz-magazine 1.0
 */
function goodz_magazine_featured_media() {

	if ( 'gallery' == get_post_format() ) :

        if ( get_post_gallery() && ! post_password_required() ) { ?>

            <div class="entry-gallery">
                <?php echo get_post_gallery(); ?>
            </div><!-- .entry-gallery -->

			<?php if ( is_single() && 'full' === goodz_magazine_gallery_size() ) : ?>
	            <div class="slider-preloader">
					<div class="preloader-content">
						<?php if (jetpack_the_site_logo()): ?>
							<?php jetpack_the_site_logo(); ?>
						<?php
							else :
								printf( '<p>%s</p>', esc_html__( 'loading', 'goodz-magazine' ) );
							endif;
						?>
					</div>
				</div>
			<?php endif; ?>

		<?php } else {

			goodz_magazine_featured_image();

		}

	elseif ( 'video' == get_post_format() ) :

        if ( goodz_magazine_get_embed_media() ) { ?>

            <div class="entry-video">
                <?php echo goodz_magazine_get_embed_media(); ?>
            </div><!-- .entry-video -->

        <?php } else {

            goodz_magazine_featured_image();

        }

	else :

		goodz_magazine_featured_image();

	endif;

}

/**
 * Dispalys Author Box under single post content
 *
 * @since  goodz-magazine 1.0
 */
function goodz_magazine_author_box() {

?>

	<section class="author-box">
		<figure class="author-avatar">
			<?php echo get_avatar( get_the_author_meta( 'ID' ) ); ?>
		</figure>
		<div class="author-info">
			<h6 class="author-name"><?php the_author(); ?></h6>
			<p><?php echo get_the_author_meta( 'description' ); ?></p>
		</div>
	</section>

<?php
}

