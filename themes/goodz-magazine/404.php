<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package goodz-magazine
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'goodz-magazine' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<?php

						printf( '<p>%1$s <a href="%2$s" rel="home">%3$s</a>.</p>',
							esc_html__( 'Try a search or go to our', 'goodz-magazine' ),
							esc_url( home_url( '/' ) ),
							esc_html__( 'Homepage', 'goodz-magazine' )
						);

					?>

					<?php get_search_form(); ?>

				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
