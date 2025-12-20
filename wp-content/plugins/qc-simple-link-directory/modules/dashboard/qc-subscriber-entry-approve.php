<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class Sld_user_entry {

	// class instance
	static $instance;

	// subscriber entry WP_List_Table object
	public $subscribers_obj;

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
            __('Manage User Links', 'qc-opd'),
            __('Manage User Links', 'qc-opd'),
            'manage_options',
            'qcsld_user_entry_list',
            array(
                $this,
                'qc_sld_plugin_settings_page'
            )
        );

		add_action( "load-$hook", array( $this, 'screen_option' ) );

		
	}
	
	
	/**
	* Screen options
	*/
	public function screen_option() {

		$option = 'per_page';
		$args   = array(
			'label'   => 'User Entry',
			'default' => 5,
			'option'  => 'entry_per_page'
        );

		add_screen_option( $option, $args );

		$this->subscribers_obj = new User_entry_list();
	}

	/**
	* Plugin settings page
	*/
	public function qc_sld_plugin_settings_page() {
			$this->subscribers_obj->prepare_item_actions();
			if(isset($_GET['action']) and $_GET['action']=='edit'){
				
			}else{
		?>
		<div class="wrap">
			<h2>Manage User Links</h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<?php $this->subscribers_obj->sld_table_header(); ?>
							<form method="post" action="<?php echo get_site_url().'/wp-admin/edit.php?post_type=sld&page=qcsld_user_entry_list' ?>">
								<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
								<?php
									if( isset($_POST['s']) ){
										$this->subscribers_obj->prepare_items($_POST['s']);
									 } else {
										$this->subscribers_obj->prepare_items();
									 }
									$this->subscribers_obj->search_box( 'search', 'search_id' );
									
									$this->subscribers_obj->display(); 
									
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
			}
	}
	
	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
}