<style type="text/css">
	.wds-native-dismiss {
		vertical-align: -4px;
		font-size: 12px;
		font-weight: bold;
		margin-left: 8px;
		color: inherit;
		text-decoration: none;
	}
</style>
<script type="application/javascript">
	jQuery(function ($) {
		$(document).on('click', '.wds-native-dismissible-notice .wds-native-dismiss,.wds-native-dismissible-notice .notice-dismiss', function (e) {
			e.preventDefault();
			var $notice = $(this).closest('.wds-native-dismissible-notice'),
				message_key = $notice.data('messageKey');
			$notice.remove();

			$.post(
				ajaxurl,
				{
					action: 'wds_dismiss_message',
					message: message_key,
					_wds_nonce: '<?php echo esc_js( wp_create_nonce( 'wds-admin-nonce' ) ); ?>'
				},
				'json'
			);
		});
	});
</script>