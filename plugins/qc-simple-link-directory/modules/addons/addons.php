<?php
define( 'qcopd_sld_addon_url', plugin_dir_url(__FILE__) );
define( 'qcopd_sld_SCRIPT_DEBUG', true );
add_action('admin_menu', 'qcopd_sld_addon_page', 999);
function qcopd_sld_addon_page(){
	add_submenu_page(
  		'edit.php?post_type=sld',
  		'AddOns',
  		'AddOns',
  		'manage_options',
  		'sld-addons-page',
  		'qcopd_sld_addon_page_cb'
  	);
}

function qcopd_sld_addon_page_cb(){
	require_once('admin_ui2.php');
}