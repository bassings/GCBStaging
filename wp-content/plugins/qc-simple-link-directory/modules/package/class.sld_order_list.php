<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Sld_order_list
{
	// class instance
	static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'sld_custom_plugin_admin_menu' ) );

	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function deny_subscriber_profile($id){
        global $wpdb;

        $sql 		= $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
        $identifier = time();
        $pdata 		= $wpdb->get_row($sql);

        if( $pdata->approval==1 || $pdata->approval==3 ){

            $searchQuery = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = %d and `meta_key` = 'qcopd_list_item01' and meta_value LIKE '%".$pdata->custom."%'", $pdata->sld_list);
            $result = @$wpdb->get_row($searchQuery);

            $meta_id = @$result->meta_id;

            @$wpdb->delete(
                "{$wpdb->prefix}postmeta",
	            array( 'meta_id' => $meta_id ),
	            array( '%d' )
            );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => '',
                    'approval'=> 2
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
        }

    }
	
	
	public function sld_custom_plugin_admin_menu() {

		$hook = add_submenu_page(
			'edit.php?post_type=sld',
			__('Your Orders', 'qc-opd'),
			__('Your Orders', 'qc-opd'),
			'manage_options',
			'qcsld_order_list',
			array(
				$this,
				'qc_sld_plugin_order_list_page'
			)
		);

	}
	public function top_action(){
		global $wpdb;
		$table             = $wpdb->prefix.'sld_package_purchased';
		if(isset($_GET['act']) and $_GET['act']=='delete' ){
			$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
			$pdata = $wpdb->get_row( $wpdb->prepare( "Select * from $table where 1 and `id`=%d", $id ) );
			$userid = $pdata->user_id;
			$packageid = $pdata->package_id;
			
			$results = $wpdb->get_results( $wpdb->prepare( "select * from ".$wpdb->prefix."sld_user_entry where 1 and `package_id`=%d and `user_id`=%d", $packageid, $userid ) );
			
			foreach($results as $result){
				$this->deny_subscriber_profile($result->id);
			}
			
			$wpdb->delete(
				$table,
				array( 'id' => $id ),
				array( '%d' )
			);
			echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Order Deleted.','qc-opd').' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','qc-opd').'</span></button></div>';
		}
		
		if(isset($_GET['act']) and $_GET['act']=='cancel'){
			
			$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
			$pdata = $wpdb->get_row( $wpdb->prepare( "Select * from $table where 1 and `id`=%d", $id ) );
			$userid = $pdata->user_id;
			$packageid = $pdata->package_id;
			
			$results = $wpdb->get_results( $wpdb->prepare( "select * from ".$wpdb->prefix."sld_user_entry where 1 and `package_id`=%d and `user_id`=%d", $packageid, $userid ) );
			
			foreach($results as $result){
				$this->deny_subscriber_profile($result->id);
			}
			
			$wpdb->update(
                $table,
                array(
                    'status'  => 'cancel'
                ),
                array( 'id' => $id),
                array(
                    '%s',
                ),
                array( '%d')
            );
			echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Order has been canceled.','qc-opd').' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','qc-opd').'</span></button></div>';
		}
		
	}
	public function qc_sld_plugin_order_list_page(){
		global $wpdb;
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$table             = $wpdb->prefix.'sld_package_purchased';
		$current_user = wp_get_current_user();
		$this->top_action();

	?>
	<div class="wrap">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
				<div id="post-body-content" >
					<h1 class="wp-heading-inline"><?php echo __('Order List', 'qc-opd') ?></h1>
					<hr class="wp-header-end">
					<div class="qchero_sliders_list_wrapper">
						<div class="qchero_slider_table_area">
							<table class="wp-list-table widefat fixed striped posts">
								<thead >
									<tr>
										<th class="sld_payment_cell">
											<?php _e( 'Date', 'qc-opd' ) ?>
										</th>
										<!--<th class="sld_payment_cell">
											<?php _e( 'Transaction Id', 'qc-opd' ) ?>
										</th>-->
										<th class="sld_payment_cell">
											<?php _e( 'Buyer Name', 'qc-opd' ) ?>
										</th>
										<th class="sld_payment_cell">
											<?php _e( 'User Name', 'qc-opd' ); ?>
										</th>
										<th class="sld_payment_cell">
											<?php _e( 'Amount', 'qc-opd' ); ?>
										</th>
										<th class="sld_payment_cell">
											<?php _e( 'Recurring', 'qc-opd' ); ?>
										</th>
										<th class="sld_payment_cell">
											<?php _e( 'Status', 'qc-opd' ); ?>
										</th>
										<th class="sld_payment_cell">
											<?php _e( 'Action', 'qc-opd' ); ?>
										</th>
									</tr>
								</thead>
						<tbody>
						<?php
						$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d order by `date` DESC ", 1 ) );
						foreach($rows as $row){
						?>
							<tr class="sld_payment_row">
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Date', 'qc-opd') ?></div>
									<?php echo date('m/d/Y', strtotime($row->date)); ?>
								</td>
								<!--<div class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Transaction Id', 'qc-opd') ?></div>
									<?php echo $row->transaction_id; ?>
								</div>-->
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Buyer Name', 'qc-opd') ?></div>
									<?php echo $row->payer_name; ?>
								</td>
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('User Name', 'qc-opd') ?></div>
									<?php
										$userinfo = get_user_by( 'ID', $row->user_id );
										echo $userinfo->user_login;
									?>
								</td>
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Amount', 'qc-opd') ?></div>
									<?php echo $row->paid_amount ?>
								</td>
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Recurring', 'qc-opd') ?></div>
									<?php echo ($row->recurring==1?'Yes':'No'); ?>
								</td>
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Status', 'qc-opd') ?></div>
									<?php echo $row->status; ?>
								</td>
								<td class="sld_payment_cell">
									<div class="sld_responsive_head"><?php echo __('Action', 'qc-opd') ?></div>
									<a class="button button-primary" href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=sld&page=qcsld_order_list&act=delete&id=' . $row->id ), 'sld' ) ?>">
										<?php echo __('Delete', 'qc-opd') ?>
									</a>
									<a class="button button-danger" href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=sld&page=qcsld_order_list&act=cancel&id=' . $row->id ), 'sld' ) ?>">
										<?php echo __('Cancel Order', 'qc-opd') ?>
										
									</a>
								</td>
							</tr>
						<?php
						}
						?>
						</tbody>

						</table>

					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
}
function Sld_order_list(){
	return Sld_order_list::get_instance();
}
Sld_order_list();