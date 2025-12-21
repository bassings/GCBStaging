<?php
function sld_show_empty_license_notification_on_plugin_row( $plugin_file, $plugin_data, $status ) {
	
	$qcld_renew_subscription = get_sld_renew_transient();

	if( !get_sld_license_purchase_code() ){		
		$params = array(
					'body' => array(
						'action'       => 'info',
						'plugin-slug'  => sld_LICENSING_PLUGIN_SLUG,
					),
				);
		$request = wp_remote_post(sld_LICENSING_REMOTE_PATH, $params, array('timeout' => 60) );

		$remote_data = '';
		if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			$remote_data = maybe_unserialize( $request['body'] );
			$get_plugin_data = get_sld_licensing_plugin_data();

			$plugin_current_version = $get_plugin_data['Version'];
			
			if ( $remote_data && version_compare( $plugin_current_version, $remote_data->new_version, '<' ) ) {
				echo '<tr class="plugin-update-tr sld-empty-license">
			  			<td colspan="3" class="plugin-update colspanchange">
				        	<div class="update-message notice inline notice-warning notice-alt">
				        		<p>There is a new version available. You have version <strong>'.$plugin_current_version.'</strong> installed. Activate License to Upgrade to <strong>'.$remote_data->new_version.'</strong>. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin='.sld_LICENSING_PLUGIN_NAME.'&section=changelog&TB_iframe=true&width=772&height=520').'" class="thickbox open-plugin-details-modal" >View version details</a>. Automatic update is unavailable for this plugin. To receive automatic updates, valid license is required.Updates are crucial for compatibility and security.</p>
				        	</div>
				        </td>
				    </tr>';
			}
		}
	}
}
// add_action("after_plugin_row_".sld_LICENSING_PLUGIN_SLUG, 'sld_show_empty_license_notification_on_plugin_row', 9, 3 );

function qcld_sld_activate_au()
{
	$plugin_slug = sld_LICENSING_PLUGIN_SLUG;
	$get_plugin_data = get_sld_licensing_plugin_data();

	$plugin_current_version = $get_plugin_data['Version'];
	$plugin_remote_path =  sld_LICENSING_REMOTE_PATH;
	$license_key = get_sld_licensing_key();
	$buy_from = get_sld_licensing_buy_from();
	//6076-qcldpl-4784-1553173833
	//error_log($buy_from.' buy_from');
	if( $buy_from != 'codecanyon' ){
		$upgrader_instance = new QCLD_sld_AutoUpdate ( $plugin_current_version, $plugin_remote_path, $plugin_slug, '', $license_key );
	}
}
add_action( 'init', 'qcld_sld_activate_au' );


function qcld_sld_upgrade_completed( $upgrader_object, $options ) {
	// The path to our plugin's main file
	$plugin_slug = sld_LICENSING_PLUGIN_SLUG;
	// If an update has taken place and the updated type is plugins and the plugins element exists
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin == $plugin_slug ) {
				delete_sld_update_transient();
				delete_sld_renew_transient();
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'qcld_sld_upgrade_completed', 10, 2 );

add_action('admin_enqueue_scripts', 'qcld_sld_licensing_scripts');

function qcld_sld_licensing_scripts(){
	wp_enqueue_style('qcld_sld_licensing_style', plugin_dir_url( __FILE__ ).'/assets/css/style.css');

	//start new-update-for-codecanyon
	wp_enqueue_script('qcld_sld_licensing_script', plugin_dir_url( __FILE__ ).'/assets/js/script.js', array('jquery'), false, true );

	wp_localize_script( 'qcld_sld_licensing_script', 'sld_licensing_admin_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ), 
			'nonce' => wp_create_nonce( "sld_licensing_admin_nonce" )
		)
	);
	//end new-update-for-codecanyon
}