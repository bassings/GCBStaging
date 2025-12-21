<?php
/**
 * Metabox preview template
 *
 * @package wpmu-dev-seo
 */

$post = empty( $post ) ? null : $post;
if ( ! $post ) {
	return;
}

$post_parent = wp_is_post_revision( $post->ID );
$link = empty( $post_parent ) ? get_permalink( $post->ID ) : get_permalink( $post_parent );
$title = empty( $title ) ? '' : $title;
$description = empty( $description ) ? '' : $description;
$after = empty( $after ) ? '' : $after;
?>
<div class="wds-metabox-preview">
	<label class="sui-label"><?php esc_html_e( 'Google Preview' ); ?></label>

	<?php
	if ( apply_filters( 'wds-metabox-visible_parts-preview_area', true ) ) {
		$this->_render( 'onpage/onpage-preview', array(
			'link'        => esc_url( $link ),
			'title'       => esc_html( $title ),
			'description' => esc_html( $description ),
		) );
	}
	?>

	<?php echo wp_kses_post( $after ); ?>
</div>