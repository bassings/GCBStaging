<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package goodz-magazine
 */

get_header();

?>

<div class="container">
	<header class="page-header">
		<?php
			printf( '<h1 class="page-title">%s<span>%s</span></h1>', esc_html__( 'Search results:', 'goodz-magazine' ), esc_html( get_search_query() ) );
		?>
	</header><!-- .page-header -->
	<div class="row">

		<section id="primary" class="content-area <?php goodz_magazine_content_cols(); ?>">
			<main id="main" class="site-main" role="main">

			<?php if ( have_posts() ) : ?>

				<div class="row">
					<div class="grid-wrapper clear" id="post-load">

						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<?php get_template_part( 'template-parts/content', get_post_format() ); ?>

						<?php endwhile; ?>

					</div>
				</div>


			<?php else : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; ?>

			</main><!-- #main -->
		</section><!-- #primary -->

		<?php get_sidebar(); ?>

	</div><!-- .row -->

	<?php the_posts_navigation(); ?>

</div><!-- .container -->

<?php get_footer(); ?>
