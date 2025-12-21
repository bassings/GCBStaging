<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package goodz-magazine
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="container">
			<div class="row">
				<div class="custom-menus col-sm-6">
					<?php
						if ( is_active_sidebar( 'footer-widgets-1' ) ) {
							dynamic_sidebar( 'Footer Widgets 1' );
						}
					?>
				</div>
				<?php if ( is_active_sidebar( 'footer-widgets-2' ) ) { ?>
					<div class="col-lg-3 col-sm-6 widget-area">
						<?php dynamic_sidebar( 'Footer Widgets 2' ); ?>
					</div>
				<?php } ?>
				<?php if ( is_active_sidebar( 'footer-widgets-3' ) ) { ?>
					<div class="col-lg-3 col-sm-6 widget-area">
						<?php dynamic_sidebar( 'Footer Widgets 3' ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="site-info">
				<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'goodz-magazine' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'goodz-magazine' ), 'WordPress' ); ?></a>
				<span class="sep"> | </span>
				<?php printf( esc_html__( 'Theme: %1$s by %2$s.', 'goodz-magazine' ), 'Goodz Magazine', '<a href="http://themeskingdom.com" rel="designer">Themes Kingdom</a>' ); ?>
			</div><!-- .site-info -->
		</div><!-- .container -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
