<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
	$register_url = $this->sldcustom_login_get_translated_option_page( 'sld_register_url', '');
	$restore_url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');

	$sld_lan_login = sld_get_option('sld_lan_login')?sld_get_option('sld_lan_login'): esc_html('Login');
    $sld_lan_username = sld_get_option('sld_lan_username')?sld_get_option('sld_lan_username'): esc_html('Username');
    $sld_lan_password = sld_get_option('sld_lan_password')?sld_get_option('sld_lan_password'): esc_html('Password');
    $sld_lan_create_account 	  = sld_get_option('sld_lan_create_account')?sld_get_option('sld_lan_create_account'): esc_html('Create account');
    $sld_lan_forgot_password 	  = sld_get_option('sld_lan_forgot_password')?sld_get_option('sld_lan_forgot_password'): esc_html('Forgot your password?');
?>

<div class="cleanlogin-container">		

	<form class="cleanlogin-form" method="post" action="<?php echo $login_url;?>">
		<h2> <?php echo __($sld_lan_login, 'qc-opd') ?> </h2>
		
		<fieldset>
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-username" type="text" name="log" placeholder="<?php echo __( $sld_lan_username, 'qc-opd' ); ?>">
			</div>
			
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-password" type="password" name="pwd" placeholder="<?php echo __( $sld_lan_password, 'qc-opd' ); ?>">
			</div>
		</fieldset>
		
		<fieldset style="text-align:center; padding:0 !important;">
			<input class="cleanlogin-field submit_registration" type="submit" value="<?php echo __( $sld_lan_login, 'qc-opd' ); ?>" name="submit">
			<input type="hidden" name="action" value="login">
			<?php wp_nonce_field( 'qc-opd' ); ?>
		</fieldset>
		
		<div class="cleanlogin-form-bottom">
			
			<div class="cleanlogin-field-remember">
				<?php 
					echo "<a style='float: right;color: #18191f ;font-weight: bold; padding-left:15px;' href='$register_url' class='cleanlogin-form-pwd-link'>". __( $sld_lan_create_account, 'qc-opd' ) ."</a>";
				?>
			</div>

			<?php 
				echo "<a style='float: right;color: #666;font-weight: bold; padding-right:15px;' href='$restore_url' class='cleanlogin-form-pwd-link'>". __( $sld_lan_forgot_password, 'qc-opd' ) ."</a>";
			?>
						
		</div>
		

	</form>

</div>
