<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package goodz-magazine
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'goodz-magazine' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<div class="container">

			<div class="site-branding">

				<!-- Logo -->
				<?php if ( function_exists( 'jetpack_the_site_logo' ) ) : ?>
					<?php jetpack_the_site_logo(); ?>
				<?php endif; ?>

				<?php if ( is_home() ) : ?>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php else : ?>
					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php endif; ?>
				<p class="site-description"><?php bloginfo( 'description' ); ?></p>

			</div><!-- .site-branding -->

			<nav id="site-navigation" class="main-navigation" role="navigation">
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
					<?php esc_html_e( 'Primary Menu', 'goodz-magazine' ); ?>
				</button>
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu' ) ); ?>
			</nav><!-- #site-navigation -->

			<!-- Search form -->
			<div class="search-wrap"><?php get_search_form(); ?><div class="search-instructions"><?php esc_html_e( 'Press Enter / Return to begin your search.', 'goodz-magazine' ); ?></div></div>
			<button id="big-search-trigger"><span class="screen-reader-text"><?php esc_html__( 'open search form', 'goodz-magazine' ); ?></span><i class="icon-search"></i></button>
			<button id="big-search-close"><span class="screen-reader-text"><?php esc_html__( 'close search form', 'goodz-magazine' ); ?></span><i class="icon-close"></i></button>

		</div><!-- container -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">

	<!-- Featured Slider -->

	<?php if ( goodz_magazine_has_featured_posts() && is_front_page() ) : ?>

		<div class="featured-slider-wrap">
			<div class="featured-slider">

				<?php

					// Load featured images
					$featured_posts = goodz_magazine_get_featured_posts();

					foreach ( (array) $featured_posts as $post ) :
						setup_postdata( $post );

						// Include the featured content template.
						get_template_part( 'template-parts/content', 'slider' );
					endforeach;

					wp_reset_postdata();

				?>

			</div><!-- .featured-slider -->
		</div>

	<?php endif; ?>
