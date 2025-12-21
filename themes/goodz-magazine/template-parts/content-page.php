<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package goodz-magazine
 */

// Container classes relevant to sidebar
$cols = 'no-sidebar';

if ( is_active_sidebar( 'sidebar-1' ) ) {
	$cols = 'has-sidebar';
}

?>

<header class="page-header">
	<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
	<?php edit_post_link( esc_html__( 'Edit', 'goodz-magazine' ), '<span class="edit-link">', '</span>' ); ?>
</header><!-- .entry-header -->

<article class="page-content">

	<div class="row">
		<div class="col-md-9 <?php echo esc_attr( $cols ); ?>">

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'goodz-magazine' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->

			<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			?>

		</div><!-- .columns -->
		<?php get_sidebar(); ?>
	</div><!-- .row -->

</article><!-- .page-content -->
