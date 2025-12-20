<?php
$id = empty( $id ) ? '' : $id;
?>
<div class="wds-data-reset-modal-body">
	<p><?php esc_html_e( 'Are you sure you want to reset SmartCrawl’s settings and data back to the factory defaults?', 'wds' ); ?></p>

	<button type="button"
	        class="sui-button sui-button-ghost wds-cancel-button"
	        data-a11y-dialog-hide="<?php echo esc_attr( $id ); ?>">

		<?php esc_html_e( 'Cancel', 'wds' ); ?>
	</button>

	<button type="button"
	        class="sui-button sui-button-ghost sui-button-red wds-data-reset-button">
		<span class="sui-loading-text">
			<i class="sui-icon-refresh" aria-hidden="true"></i> <?php esc_html_e( 'Reset', 'wds' ); ?>
		</span>

		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>
</div>

<?php wp_nonce_field( 'wds-data-reset-nonce', '_data_reset_nonce', false ); ?>