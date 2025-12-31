<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Sld_click_list
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

	public function sld_custom_plugin_admin_menu() {

		$hook = add_submenu_page(
			'edit.php?post_type=sld',
			__('Click Report', 'qc-opd'),
			__('Click Report', 'qc-opd'),
			'manage_options',
			'qcsld_click_list',
			array(
				$this,
				'qc_sld_plugin_click_list_page'
			)
		);

	}
	
	
	public function top_action1(){
		global $wpdb;
		$table             = $wpdb->prefix.'sld_click_table';
		
		$customPagHTML     = "";
		$query             = "SELECT * FROM $table";
		$total_query     = "SELECT COUNT(1) FROM ($query) AS combined_table";
		$total             = $wpdb->get_var( $total_query );
		
		$items_per_page = 5;
		
		$page             = isset( $_GET['cpage'] ) ? abs( (int) sanitize_text_field($_GET['cpage']) ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		$result         = $wpdb->get_results( $query . " ORDER BY `time` DESC LIMIT $offset, $items_per_page" );
		$totalPage         = ceil($total / $items_per_page);
		
		if($totalPage > 1){
			$customPagHTML     =  '<div><span>Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
			'base' => add_query_arg( 'cpage', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $totalPage,
			'current' => $page
			)).'</div>';
		}
		return $customPagHTML;
	}
	
	
	public function qc_sld_plugin_click_list_page(){
		global $wpdb;
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$table             = $wpdb->prefix.'sld_click_table';
		$current_user = wp_get_current_user();
		$url = admin_url('edit.php?post_type=sld&page=qcsld_click_list');
		$customPagHTML = '';
		// Main Report Area
		
		$sql = "SELECT * FROM $table where 1";
		$total_sql = "SELECT count(*) FROM $table where 1";
		
		if(isset($_GET['filter']) && $_GET['filter']=='1'){
			$sql = "SELECT *, count(*) as cnt FROM $table WHERE 1";
		}
		
		
		if(isset($_GET['from']) && $_GET['from']!=''){
			$from = sanitize_text_field($_GET['from']);
			$sql .=" and `time` >= '".$from." 00:00:00'";
			$total_sql .=" and `time` >= '".$from." 00:00:00'";
		}
		if(isset($_GET['to']) && $_GET['to']!=''){
			$to = sanitize_text_field($_GET['to']);
			$sql .=" and `time` <= '".$to." 23:59:59'";
			$total_sql .=" and `time` <= '".$to." 23:59:59'";
		}
		
		if(isset($_GET['filter']) && $_GET['filter']=='1'){
			$sql .= " group by `itemurl` order by cnt desc";
		}else{
			$sql .=" ORDER BY `id` DESC";
		}
		
		
		
		$total             = $wpdb->get_var( $total_sql );
		$items_per_page = 10;
		
		$page             = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		
		$sql .=" LIMIT $offset, $items_per_page";
		
		$rows = $wpdb->get_results( $sql );

		if(!empty($rows)){
			$totalPage         = ceil($total / $items_per_page);
			
			if($totalPage > 1){
				$customPagHTML     =  '<div><span>Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
				'base' => add_query_arg( 'cpage', '%#%' ),
				'format' => '',
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'total' => $totalPage,
				'current' => $page
				)).'</div>';
			}
		}
		

	?>	<link rel="stylesheet" href="<?php echo SLD_QCOPD_ASSETS_URL; ?>/css/jquery_ui.css">
		<div class="qchero_sliders_list_wrapper">
			<div class="sld_menu_title">
				<h2 style="font-size: 26px;text-align:center"><?php echo __('Click Report', 'qc-opd') ?></h2>

			</div>
			<div class="sld_menu_title">
				<p><?php echo __('Filter By', 'qc-opd') ?></p>
				<form action="<?php echo $url; ?>" method="get">
					<input type="hidden" name="post_type" value="sld" />
					<input type="hidden" name="page" value="qcsld_click_list" />
					<div class="sld_inline_form_element">
						<span><?php echo __('From Date', 'qc-opd') ?></span>
						<input type="text" name="from" class="sld_from_date" value="<?php echo (isset($_GET['from'])&&$_GET['from']!=''?$_GET['from']:''); ?>" />
					</div>
					
					<div class="sld_inline_form_element">
						<span><?php echo __('To Date', 'qc-opd') ?></span>
						<input type="text" name="to" class="sld_to_date" value="<?php echo (isset($_GET['to'])&&$_GET['to']!=''?$_GET['to']:''); ?>" />
					</div>
					<div class="sld_inline_form_element">
						<span><?php echo __('Click', 'qc-opd') ?></span>
						<select name="filter">
							<option value=""><?php echo __('None', 'qc-opd') ?></option>
							<option value="1" <?php echo (isset($_GET['filter'])&&$_GET['filter']=='1'?'selected="selected"':''); ?>><?php echo __('Most Clicked Links', 'qc-opd') ?></option>
						</select>
					</div>
					<div class="sld_inline_form_element">
						<input type="submit" value="<?php echo __('Go', 'qc-opd') ?>" />
					</div>
					
				</form>
			</div>
			
			<div class="sld_menu_title" style="text-align:left;"><?php echo $customPagHTML; ?><span style="float:right;font-weight:bold;"> <?php echo __('Total', 'qc-opd') ?> <?php echo $total; ?></span></div>
			<div class="qchero_slider_table_area">
				<div class="sld_payment_table">
					<div class="sld_payment_row header">
						<div class="sld_payment_cell">
							<?php _e( 'Serial', 'qc-opd' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Date', 'qc-opd' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Item Url', 'qc-opd' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Ip', 'qc-opd' ); ?>
						</div>
						<?php if(isset($_GET['filter']) && $_GET['filter']=='1'){  ?>
							<div class="sld_payment_cell">
								<?php _e( 'Total Clicks', 'qc-opd' ); ?>
							</div>
						<?php } ?>
						
					</div>

			<?php
			if(!empty($rows)){
				foreach($rows as $row){
				?>
					<div class="sld_payment_row">
						<div class="sld_payment_cell">
							<div class="sld_responsive_head"><?php echo __('Id', 'qc-opd') ?></div>
							<?php echo $row->id; ?>
						</div>
						<div class="sld_payment_cell">
							<div class="sld_responsive_head"><?php echo __('Date', 'qc-opd') ?></div>
							<?php echo date('m/d/Y', strtotime($row->time)); ?>
						</div>
						<div class="sld_payment_cell">
							<div class="sld_responsive_head"><?php echo __('Item Url', 'qc-opd') ?></div>
							<?php echo $row->itemurl; ?>
						</div>
						<div class="sld_payment_cell">
							<div class="sld_responsive_head"><?php echo __('Ip', 'qc-opd') ?></div>
							<?php
								echo $row->ip;
								
							?>
						</div>

						<?php if(isset($_GET['filter']) && $_GET['filter']=='1'){  ?>
							<div class="sld_payment_cell">
								<?php echo $row->cnt; ?>
							</div>
						<?php } ?>
						
					</div>
				<?php
				}
			}
			?>

			</div>

		</div>
		</div>
		
	<?php
	}
}
function Sld_click_list(){
	return Sld_click_list::get_instance();
}
Sld_click_list();