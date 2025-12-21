<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package goodz-magazine
 */

get_header();

?>

<div class="container">

	<header class="page-header">
		<?php
			goodz_magazine_archive_title( '<h1 class="page-title">', '</h1>' );
			the_archive_description( '<div class="taxonomy-description">', '</div>' );
		?>
	</header><!-- .page-header -->

	<div class="row">

		<div id="primary" class="content-area <?php goodz_magazine_content_cols(); ?>">
			<main id="main" class="site-main" role="main">

				<?php if ( have_posts() ) : ?>

					<div class="row">
						<div class="grid-wrapper clear" id="post-load">
							<?php while ( have_posts() ) : the_post(); ?>

								<?php get_template_part( 'template-parts/content', get_post_format() ); ?>

							<?php endwhile; ?>
						</div>
					</div>


				<?php else : ?>

					<?php get_template_part( 'template-parts/content', 'none' ); ?>

				<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

		<?php get_sidebar(); ?>

	</div><!-- .row -->
	<?php the_posts_navigation(); ?>
</div><!-- .container -->

<?php get_footer(); ?>
