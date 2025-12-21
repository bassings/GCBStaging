<?php
$front_page = empty( $front_page ) ? null : $front_page;
if ( ! is_a( $front_page, 'WP_Post' ) ) {
	return;
}

$resolver = Smartcrawl_Endpoint_Resolver::resolve();
$resolver->simulate_post( $front_page );
$meta_helper = Smartcrawl_Meta_Value_Helper::get();
$meta_title_placeholder = $meta_helper->get_title();
$meta_desc_placeholder = $meta_helper->get_description();
$resolver->stop_simulation();
$macros = array_merge(
	Smartcrawl_Onpage_Settings::get_singular_macros( 'page' ),
	Smartcrawl_Onpage_Settings::get_general_macros()
);
?>

<div class="wds-notice sui-notice sui-notice-info">
	<p>
		<?php
		esc_html_e( 'Your homepage is set to a static page, Homepage. You can edit your homepage meta from here, as well as in the WordPress editor for that page.', 'wds' );
		if ( $front_page ) {
			?>
			<br/>
			<a type="button"
			   href="<?php echo esc_attr( get_edit_post_link( $front_page ) ); ?>"
			   class="sui-button" style="margin-top: 10px">
				<?php esc_html_e( 'Go To Homepage', 'wds' ); ?></a>
			<?php
		}
		?>
	</p>
</div>

<?php $this->_render( 'onpage/onpage-preview' ); ?>

<?php
$this->_render( 'onpage/onpage-general-settings-inner', array(
	'title'             => smartcrawl_get_value( 'title', $front_page->ID ),
	'title_placeholder' => $meta_title_placeholder,
	'title_key'         => 'title-static-home',
	'title_label_desc'  => esc_html__( 'Define the main title of your website that Google will index.', 'wds' ),
	'title_field_desc'  => esc_html__( 'This is generally your brand name, sometimes with a tagline.', 'wds' ),

	'description'             => smartcrawl_get_value( 'metadesc', $front_page->ID ),
	'description_placeholder' => $meta_desc_placeholder,
	'description_key'         => 'metadesc-static-home',
	'meta_label_desc'         => esc_html__( 'Set the default description that will accompany your SEO title in search engine results.', 'wds' ),
	'meta_field_desc'         => esc_html__( 'Remember to keep it simple, to the point, and include a bit about what your website can offer potential visitors.', 'wds' ),
	'macros'                  => $macros,
) );

$this->_render( 'metabox/metabox-social-opengraph', array(
	'post' => $front_page,
) );

$this->_render( 'metabox/metabox-social-twitter', array(
	'post' => $front_page,
) );

$this->_render( 'metabox/metabox-advanced-indexing', array(
	'robots_noindex_value'  => (int) smartcrawl_get_value( 'meta-robots-noindex', $front_page->ID ),
	'robots_nofollow_value' => (int) smartcrawl_get_value( 'meta-robots-nofollow', $front_page->ID ),
	'robots_index_value'    => (int) smartcrawl_get_value( 'meta-robots-index', $front_page->ID ),
	'robots_follow_value'   => (int) smartcrawl_get_value( 'meta-robots-follow', $front_page->ID ),
	'advanced_value'        => explode( ',', smartcrawl_get_value( 'meta-robots-adv', $front_page->ID ) ),
	'post_type_noindexed'   => (bool) smartcrawl_get_array_value( $_view['options'], 'meta_robots-noindex-page' ),
	'post_type_nofollowed'  => (bool) smartcrawl_get_array_value( $_view['options'], 'meta_robots-nofollow-page' ),
) );
?>

<footer class="sui-box-footer">
	<button type="button"
	        class="sui-button sui-button-blue wds-save-static-home-settings">
		<span class="sui-loading-text">
			<i class="sui-icon-save" aria-hidden="true"></i>

			<?php echo esc_html__( 'Save Settings', 'wds' ); ?>
		</span>

		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>
</footer>