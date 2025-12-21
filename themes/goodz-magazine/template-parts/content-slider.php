<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package goodz-magazine
 */

?>

<article id="post-<?php the_ID(); ?>" class="post">

	<?php if ( has_post_thumbnail() ) { ?>
		<figure class="featured-image">
			<?php the_post_thumbnail( 'full' ); ?>
		</figure>
	<?php } ?>

	<header class="entry-header">

		<?php if ( is_single() ) : ?>
			<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
		<?php else : ?>
			<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
		<?php endif; ?>

	</header><!-- .entry-header -->


</article><!-- #post-## -->
