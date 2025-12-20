<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 

	$sld_lan_reset_password = sld_get_option('sld_lan_reset_password')?sld_get_option('sld_lan_reset_password'): esc_html('Reset password');
	$sld_lan_username_or_email = sld_get_option('sld_lan_username_or_email')?sld_get_option('sld_lan_username_or_email'): esc_html('Username (or E-mail)');
	$sld_lan_website = sld_get_option('sld_lan_website')?sld_get_option('sld_lan_website'): esc_html('Website');
	$sld_lan_restore_password = sld_get_option('sld_lan_restore_password')?sld_get_option('sld_lan_restore_password'): esc_html('Restore password');
?>

<div class="cleanlogin-container">
	<form class="cleanlogin-form" method="post" action="">
		<h2><?php echo __($sld_lan_reset_password, 'qc-opd') ?></h2>
		<fieldset>
		
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-username" type="text" name="username" value="" placeholder="<?php echo __( $sld_lan_username_or_email, 'qc-opd' ) ; ?>">
			</div>

			<div class="cleanlogin-field-website">
				<label for='website'><?php echo __($sld_lan_website, 'qc-opd') ?></label>
	    		<input type='text' name='website' value=".">
	    	</div>
		
		</fieldset>
		
		<div style="text-align:center">	
			<input type="submit" value="<?php echo __( $sld_lan_restore_password, 'qc-opd' ); ?>" name="submit">
			<input type="hidden" name="action" value="restore">	
			<?php wp_nonce_field( 'qc-opd' ); ?>	
		</div>

	</form>
</div>