<?php
/**
 * Template part for displaying link posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package goodz-magazine
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="highlighted">
		<header class="entry-header">

			<?php goodz_magazine_entry_header(); ?>

			<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		</header><!-- .entry-header -->

		<div class="entry-content">

			<?php
				the_content( the_title( '<span class="screen-reader-text">"', '"</span>', false ) );
			?>

			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'goodz-magazine' ),
					'after'  => '</div>',
				) );
			?>

		</div><!-- .entry-content -->
	</div>

</article><!-- #post-## -->
