<?php
$idx = empty( $idx ) ? '' : $idx;
$item = empty( $item ) ? array() : $item;
$ignored = empty( $ignored ) ? false : $ignored;
if ( ! $idx || ! $item ) {
	return;
}

$item_id = "wds-checkup-item-{$idx}";
$type = ! empty( $item['type'] )
	? sanitize_html_class( $item['type'] )
	: '';
$custom_class = ! empty( $item['class'] )
	? sanitize_html_class( $item['class'] )
	: '';
$style_class_map = array(
	'ok'       => 'sui-success',
	'info'     => '',
	'warning'  => 'sui-warning',
	'critical' => 'sui-error',
);
if ( $ignored ) {
	$style_class = '';
	$icon_class = 'sui-icon-eye-hide';
} else {
	$style_class = isset( $style_class_map[ $item['type'] ] ) ? $style_class_map[ $item['type'] ] : '';
	$icon_class = 'ok' === $type ? 'sui-icon-check-tick' : 'sui-icon-warning-alert';
}
$title = ! empty( $item['title'] ) ? $item['title'] : '';
$details = ! empty( $item['details'] ) ? $item['details'] : '';
$body = ! empty( $item['body'] ) ? $item['body'] : '';
$fix = ! empty( $item['fix'] ) ? $item['fix'] : '';
$action_button = ! empty( $item['action_button'] ) ? $item['action_button'] : '';
$is_member = empty( $is_member ) ? false : $is_member;
$show_action_button = (boolean) $action_button;
$show_ignore_button = $is_member;
$show_footer = $type !== 'ok'
               && ! $ignored
               && ( $show_action_button || $show_ignore_button );
?>
<div class="sui-accordion-item wds-check-item <?php echo esc_attr( $item_id ); ?> <?php echo esc_attr( $type ); ?> <?php echo esc_attr( $style_class ); ?> <?php echo esc_attr( $custom_class ); ?>"
     id="<?php echo esc_attr( $item_id ); ?>">
	<div class="sui-accordion-item-header">
		<div class="sui-accordion-item-title">
			<i aria-hidden="true"
			   class="<?php echo esc_attr( $style_class ); ?> <?php echo esc_attr( $icon_class ); ?>"></i>
			<?php echo esc_html( $title ); ?>
		</div>

		<?php if ( $ignored && $is_member ): ?>
			<div>
				<button class="wds-unignore sui-button sui-button-ghost"
				        data-title="<?php echo esc_attr( $title ); ?>"
				        data-id="<?php echo esc_attr( $idx ); ?>">
					<span class="sui-loading-text">
						<i class="sui-icon-undo" aria-hidden="true"></i>
						<?php esc_html_e( 'Restore', 'wds' ); ?>
					</span>

					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>
			</div>
		<?php endif; ?>

		<div>
			<span class="sui-accordion-open-indicator">
				<i aria-hidden="true" class="sui-icon-chevron-down"></i>
				<button type="button"
				        class="sui-screen-reader-text"><?php esc_html_e( 'Expand', 'wds' ); ?></button>
			</span>
		</div>
	</div>
	<div class="sui-accordion-item-body wds-check-item-content <?php echo esc_attr( $type ); ?>">
		<div class="sui-box">
			<div class="sui-box-body">
				<?php if ( $details ) : ?>
					<div class="wds-overview">
						<strong><?php esc_html_e( 'Overview', 'wds' ); ?></strong>
						<?php echo wp_kses_post( $details ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $body ) : ?>
					<div class="wds-status">
						<strong><?php esc_html_e( 'Status', 'wds' ); ?></strong>
						<?php echo wp_kses_post( $body ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $fix ) : ?>
					<div class="wds-fix">
						<strong><?php esc_html_e( 'How to Fix', 'wds' ); ?></strong>
						<?php echo wp_kses_post( $fix ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $show_footer ): ?>
				<div class="sui-box-footer">
					<?php if ( $show_ignore_button ): ?>
						<button class="wds-ignore sui-button sui-button-ghost"
						        data-title="<?php echo esc_attr( $title ); ?>"
						        data-id="<?php echo esc_attr( $idx ); ?>">
							<span class="sui-loading-text">
								<i class="sui-icon-eye-hide" aria-hidden="true"></i>
								<?php esc_html_e( 'Ignore', 'wds' ); ?>
							</span>

							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>
					<?php endif; ?>

					<?php if ( $show_action_button ): ?>
						<?php echo wp_kses_post( $action_button ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>