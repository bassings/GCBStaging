<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 
	$new_password = isset($_GET['pass']) ? sanitize_text_field( $_GET['pass'] ) : '';
	$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');

	$sld_lan_login = sld_get_option('sld_lan_login')?sld_get_option('sld_lan_login'): esc_html('Login');
	$sld_lan_your_new_pass = sld_get_option('sld_lan_your_new_pass')?sld_get_option('sld_lan_your_new_pass'): esc_html('Your new password is');
?>

<div class="cleanlogin-container">
	<form class="cleanlogin-form">
		
		<fieldset>
			<div class="cleanlogin-field">
				<label><?php echo __( $sld_lan_your_new_pass, 'qc-opd' ); ?></label>
				<input type="text" name="pass" value="<?php echo $new_password; ?>">
			</div>
		
		</fieldset>
		
		<div class="cleanlogin-form-bottom" style="background: none;">
				
			<?php if ( $login_url != '' )
				echo "<a href='$login_url' class='cleanlogin-form-login-link sld_logout_button'>". __( $sld_lan_login, 'qc-opd') ."</a>";
			?>
						
		</div>
	</form>
</div>