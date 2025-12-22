<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 
	if(!function_exists('wp_get_current_user')) {
		include(ABSPATH . "wp-includes/pluggable.php"); 
	}
	$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
	$current_user = wp_get_current_user();
	$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
	$show_user_information = get_option( 'cl_hideuser' ) == 'on' ? false : true;
?>

<div class="cleanlogin-container" >
	<div class="cleanlogin-preview">
		<div class="cleanlogin-preview-top">
			<h2><?php echo __('Login', 'qc-opd') ?></h2>
		</div>
		<p style="    font-size: 14px;
    font-weight: bold;
    margin-bottom: 6px;"><?php echo __('User already logged in', 'qc-opd') ?></p>

		<a class="sld_logout_button" href="<?php echo esc_url( add_query_arg( 'action', 'logout', $login_url) ); ?>" class="cleanlogin-preview-logout-link"><?php echo __( 'Log out', 'qc-opd' ); ?></a>	
	</div>		
</div>