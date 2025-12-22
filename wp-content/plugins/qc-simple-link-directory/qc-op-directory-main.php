<?php
/**
* Plugin Name: Simple Link Directory - Pro
* Plugin URI: https://www.quantumcloud.com/
* Description: Simple Link Directory is an advanced solution to all link page or partners page needs on your website. 
* Version: 14.7.9
* Author: QuantumCloud
* Author URI: https://www.quantumcloud.com/
* Requires at least: 4.6
* Tested up to: 6.8.1
* Text Domain: qc-opd
* Domain Path: /lang/
* License: GPL2
*/

defined('ABSPATH') or die("No direct script access!");

global $sld_plugin_version;

$sld_plugin_version = '14.7.9';


// Custom Constants
define('SLD_QCOPD_URL', plugin_dir_url(__FILE__));
define('SLD_QCOPD_IMG_URL', SLD_QCOPD_URL . "/assets/images");
define('SLD_QCOPD_ASSETS_URL', SLD_QCOPD_URL . "assets");
define('SLD_QCOPD_ASSETS_TPL_URL', trailingslashit(SLD_QCOPD_URL) . "assets");

define('SLD_QCOPD_DIR', dirname(__FILE__));
define('SLD_QCOPD_DIR_MOD', dirname(__FILE__)."/modules");
define('SLD_QCOPD_DIR_CAT', dirname(__FILE__)."/category_template");
define('SLD_QCOPD_INC_DIR', SLD_QCOPD_DIR . "/inc");
define('SLD_OCOPD_TPL_DIR', SLD_QCOPD_DIR . "/templates");
define('SLD_OCOPD_UPLOAD_DIR', SLD_QCOPD_DIR . "/uploads");

define('SLD_OCOPD_TPL_URL', trailingslashit(SLD_QCOPD_URL) . "templates");
define('SLD_OCOPD_UPLOAD_URL', SLD_QCOPD_URL . "uploads");


/**
 * Load plugin textdomain.
 */
add_action( 'init', 'qcld_opd_load_textdomains' );
function qcld_opd_load_textdomains() {
    load_plugin_textdomain( 'qc-opd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

//Helper function to extract domain
function qcsld_get_domain($url)
{
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}

//Include files and scripts


require_once( 'plugin-upgrader/plugin-upgrader.php' );


do_action('sld_before_ot_include');

if ( ! class_exists( 'OT_Loader' ) ) {
	add_filter( 'ot_theme_mode', '__return_false', 999 );
	require_once( 'option-tree/ot-loader.php' );
}

require_once( 'qc-opd-setting-option.php' );

require_once( 'qc-op-directory-post-type.php' );
require_once( 'qc-op-directory-assets.php' );
require_once( 'qcopd-helper-functions.php' );

require_once( 'qc-op-directory-shortcodes.php' );
require_once( 'qc-opd-sorting-support.php' );
require_once( 'qc-opd-ajax-stuffs.php' );
require_once( 'qc-opd-ajax-bookmark.php' );

require_once( 'qcopd-widgets.php' );
require_once( 'qcopd-custom-hooks.php' );

require_once( 'qc-op-directory-import.php' );
require_once( 'qcopd-shortcode-generator.php' );

require_once( 'qcopd-fa-modal.php' );

require_once( 'qcopd-all-category.php' );
require_once( 'qc-op-directory-multipage.php' );
require_once( 'qc-op-help-license.php' );

require_once( 'qc-op-directory-shortcodes-rand-item-show.php' );

//captcha module

require_once( SLD_QCOPD_DIR_MOD.'/captcha/simple-php-captcha.php' );
//Module for custom user registration sld.

require_once( SLD_QCOPD_DIR_MOD.'/registration/class.sld_registration.php' );
require_once( SLD_QCOPD_DIR_MOD.'/login/class.sld_login.php' );
require_once( SLD_QCOPD_DIR_MOD.'/dashboard/class.sld_dashboard.php' );

//Module for user approval
require_once( SLD_QCOPD_DIR_MOD.'/approval/sld-user-approve.php' );
//Package module integration
require_once( SLD_QCOPD_DIR_MOD.'/package/class.sld_package.php' );

require_once( SLD_QCOPD_DIR_MOD.'/claim_listing/class.sld_claim_listing_page.php' );
require_once( SLD_QCOPD_DIR_MOD.'/claim_listing/class.sld_claim_order_list.php' );
require_once( SLD_QCOPD_DIR_MOD.'/package/class.sld_order_list.php' );
require_once( SLD_QCOPD_DIR_MOD.'/click_report/class.sld_click_list.php' );
require_once( SLD_QCOPD_DIR_MOD.'/category/sld_category_image.php' );
require_once( SLD_QCOPD_DIR_MOD.'/large_list/manage_large_list.php' );

require_once( 'qc-support-promo-page/class-qc-support-promo-page.php' );

require_once( 'modules/addons/addons.php' );

register_activation_hook( __FILE__, 'qcld_sld_activate');
//Remove Slug Edit Box

//adding session
/*
add_action( 'init', function() {
	if(session_id() == '')
		session_start();
});
*/
/* Inserting jquery */

function sld_insert_jquery(){
wp_enqueue_script('jquery', false, array(), false, false);
}
add_filter('wp_enqueue_scripts','sld_insert_jquery',1);


//Check if outbound click tracking is ON
add_action('wp_head', 'sld_qcopd_add_outbound_click_tracking_script');

function sld_qcopd_add_outbound_click_tracking_script()
{

  if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
  }
  if(is_user_logged_in()){
    $current_user = wp_get_current_user();
    if(in_array('administrator',$current_user->roles)){
      return;
    }
  }


    $outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

    if ( isset($outbound_conf) && $outbound_conf == 'on' ) {

        ?>
        <script>


function _gaLt(event) {

		
		if (typeof ga !== "undefined") {
		

			/* If GA is blocked or not loaded, or not main|middle|touch click then don't track */
			if (!ga.hasOwnProperty("loaded") || ga.loaded != true || (event.which != 1 && event.which != 2)) {
				return;
			}

			var el = event.srcElement || event.target;

			/* Loop up the DOM tree through parent elements if clicked element is not a link (eg: an image inside a link) */
			while (el && (typeof el.tagName == 'undefined' || el.tagName.toLowerCase() != 'a' || !el.href)) {
				el = el.parentNode;
			}

			/* if a link with valid href has been clicked */
			if (el && el.href) {

				var link = el.href;

				/* Only if it is an external link */
				if (link.indexOf(location.host) == -1 && !link.match(/^javascript\:/i)) {

					/* Is actual target set and not _(self|parent|top)? */
					var target = (el.target && !el.target.match(/^_(self|parent|top)$/i)) ? el.target : false;

					/* Assume a target if Ctrl|shift|meta-click */
					if (event.ctrlKey || event.shiftKey || event.metaKey || event.which == 2) {
						target = "_blank";
					}

					var hbrun = false; // tracker has not yet run

					/* HitCallback to open link in same window after tracker */
					var hitBack = function() {
						/* run once only */
						if (hbrun) return;
						hbrun = true;
						window.location.href = link;
					};

					if (target) { /* If target opens a new window then just track */
						ga(
							"send", "event", "Outgoing Links", link,
							document.location.pathname + document.location.search
						);
					} else { /* Prevent standard click, track then open */
						event.preventDefault ? event.preventDefault() : event.returnValue = !1;
						/* send event with callback */
						ga(
							"send", "event", "Outgoing Links", link,
							document.location.pathname + document.location.search, {
								"hitCallback": hitBack
							}
						);

						/* Run hitCallback again if GA takes longer than 1 second */
						setTimeout(hitBack, 1000);
					}
				}
			}
		}
    }

    var _w = window;
    /* Use "click" if touchscreen device, else "mousedown" */
    var _gaLtEvt = ("ontouchstart" in _w) ? "click" : "mousedown";
    /* Attach the event to all clicks in the document after page has loaded */
    _w.addEventListener ? _w.addEventListener("load", function() {document.body.addEventListener(_gaLtEvt, _gaLt, !1)}, !1)
        : _w.attachEvent && _w.attachEvent("onload", function() {document.body.attachEvent("on" + _gaLtEvt, _gaLt)});
        </script>
        <?php

    }
}

/**
 * Submenu filter function. Tested with Wordpress 4.1.1
 * Sort and order submenu positions to match your custom order.
 *
 * @author Hendrik Schuster <contact@deviantdev.com>
 */
function sld_order_index_menu_page( $menu_ord ) 
{

  global $submenu;

  // Enable the next line to see a specific menu and it's order positions
  //echo '<pre>'; print_r( $submenu['edit.php?post_type=sld'] ); echo '</pre>'; exit();

  $arr = array();

  	if( current_user_can( 'edit_posts' ) ):
  		if(isset($submenu['edit.php?post_type=sld'][5]))
			$arr[] = $submenu['edit.php?post_type=sld'][5];

  		if(isset($submenu['edit.php?post_type=sld'][10]))
			$arr[] = $submenu['edit.php?post_type=sld'][10];

  		if(isset($submenu['edit.php?post_type=sld'][15]))
			$arr[] = $submenu['edit.php?post_type=sld'][15];

  		if(isset($submenu['edit.php?post_type=sld'][16]))
			$arr[] = $submenu['edit.php?post_type=sld'][16];

  		if(isset($submenu['edit.php?post_type=sld'][17]))
			$arr[] = $submenu['edit.php?post_type=sld'][17];

  		if(isset($submenu['edit.php?post_type=sld'][19]))
			$arr[] = $submenu['edit.php?post_type=sld'][19];

  		if(isset($submenu['edit.php?post_type=sld'][20]))
			$arr[] = $submenu['edit.php?post_type=sld'][20];

  		if(isset($submenu['edit.php?post_type=sld'][21]))
			$arr[] = $submenu['edit.php?post_type=sld'][21];

  		if(isset($submenu['edit.php?post_type=sld'][22]))
			$arr[] = $submenu['edit.php?post_type=sld'][22];

			// $arr[] = $submenu['edit.php?post_type=sld'][23];
  		if(isset($submenu['edit.php?post_type=sld'][24]))
			$arr[] = $submenu['edit.php?post_type=sld'][24];

  		if(isset($submenu['edit.php?post_type=sld'][25]))
			$arr[] = $submenu['edit.php?post_type=sld'][25];

  		if(isset($submenu['edit.php?post_type=sld'][26]))
			$arr[] = $submenu['edit.php?post_type=sld'][26];

  		if(isset($submenu['edit.php?post_type=sld'][27]))
			$arr[] = $submenu['edit.php?post_type=sld'][27];

			// $arr[] = $submenu['edit.php?post_type=sld'][250];
  		if(isset($submenu['edit.php?post_type=sld'][18]))
			$arr[] = $submenu['edit.php?post_type=sld'][18];
		
  	endif;
  

  $submenu['edit.php?post_type=sld'] = $arr;

  return $menu_ord;

}

// add the filter to wordpress
add_filter( 'custom_menu_order', 'sld_order_index_menu_page' );


/*
* Register Activation hook for multi & single site
*
*/
 function qcld_sld_activate($network_wide){
 	/* Free version replace code.
     */
    $free = array( 'simple-link-directory/qc-op-directory-main.php' );
    $all_plugins = get_plugins();
    foreach( $all_plugins as $plugin => $plugin_details ){
        if( in_array( $plugin, $free ) ){
            if( is_plugin_active( $plugin ) ){
                if( deactivate_plugins( array( $plugin ) ) ){
                    delete_plugins( array( $plugin ) );
                }
            } else {
                delete_plugins( array( $plugin ) );
            }
        }
    }
    /**
     * Free version replace code End.
     */
    
	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			sld_create_table();
			restore_current_blog();
		}
	} else {
		sld_create_table();
	}


	//exit( wp_redirect( admin_url( 'edit.php?post_type=sld&page=sld-options-page' ) ) );
}

/*function sld_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'edit.php?post_type=sld&page=sld-options-page#section_help') ) );
    }
}
add_action( 'activated_plugin', 'sld_activation_redirect' );*/
	
//create table function
function sld_create_table(){
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {

				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {

				$collate .= " COLLATE $wpdb->collate";

			}
		}

		$table             	= $wpdb->prefix.'sld_user_entry';
		$table1             = $wpdb->prefix.'sld_package';
		$table3             = $wpdb->prefix.'sld_claim_configuration';
		$table4             = $wpdb->prefix.'sld_claim_purchase';
		$table2             = $wpdb->prefix.'sld_package_purchased';

		$sql_sliders_Table = "
		CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `item_title` varchar(150) NOT NULL,
		  `item_link` varchar(150) NOT NULL,
		  `item_subtitle` text NOT NULL,
		  `category` varchar(50) NOT NULL,
		  `sld_list` varchar(100) NOT NULL,
		  `nofollow` varchar(10) NOT NULL,
		  `opennewtab` varchar(10) NOT NULL,
		  `approval` int(11) NOT NULL,
		  `package_id` int(11) NOT NULL,
		  `time` datetime NOT NULL,
		  `image_url` text NOT NULL,
		  `user_id` varchar(50) NOT NULL,
		  `description` text NOT NULL,
		  `custom` text NOT NULL,
		  `link_form` varchar(150)  NULL,
		  `link_to` varchar(150)  NULL,
		  `link_anchor_text` varchar(100) NULL,
		  `link_anchor_attr` varchar(20) NULL,
		  `link_status` varchar(20) NULL,
		  `qcopd_tags` text NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";


     $sql_sld_package = "
      CREATE TABLE IF NOT EXISTS `$table1` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(200) NOT NULL,
      `description` text NOT NULL,
      `date` datetime NOT NULL,
      `duration` varchar(10) NOT NULL,
      `Amount` float NOT NULL,
      `currency` varchar(10) NOT NULL,
      `item` varchar(10) NOT NULL,
      `paypal` varchar(100) NOT NULL,
      `sandbox` int(11) NOT NULL,
      `recurring` int(11) NOT NULL,
      `enable` int(11) NOT NULL,
      `menu_order` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";
	
	$sql_sld_claim_configuration = "
      CREATE TABLE IF NOT EXISTS `$table3` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `Amount` float NOT NULL,
      `currency` varchar(10) NOT NULL,
      `enable` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";

     $sql_sld_package_purchased = "
      CREATE TABLE IF NOT EXISTS `$table2` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `renew_date` datetime NOT NULL,
      `expire_date` datetime NOT NULL,
      `package_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `recurring` int(11) NOT NULL,
      `paid_amount` float NOT NULL,
      `payment_method` varchar(50) NULL,
      `transaction_id` varchar(150) NOT NULL,
      `payer_name` varchar(100) NOT NULL,
      `payer_email` varchar(100) NOT NULL,
      `status` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";
	
	$sql_sld_claim_purchased = "
      CREATE TABLE IF NOT EXISTS `$table4` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `user_id` int(11) NOT NULL,
      `listid` int(11) NOT NULL,
	   `item` varchar(255) NOT NULL,
      `paid_amount` float NOT NULL,
      `payment_method` varchar(50) NULL,
      `transaction_id` varchar(150) NOT NULL,
      `payer_name` varchar(100) NOT NULL,
      `payer_email` varchar(100) NOT NULL,
      `status` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";


		 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		 dbDelta( $sql_sliders_Table );
		 dbDelta( $sql_sld_package );
		 dbDelta( $sql_sld_package_purchased );
		 dbDelta( $sql_sld_claim_configuration );
		 dbDelta( $sql_sld_claim_purchased );

		 if(!function_exists('qc_sld_isset_table_column')) {
			 function qc_sld_isset_table_column($table_name, $column_name)
			 {
				 global $wpdb;
				 $columns = $wpdb->get_results("SHOW COLUMNS FROM  " . $table_name, ARRAY_A);
				 foreach ($columns as $column) {
					 if ($column['Field'] == $column_name) {
						 return true;
					 }
				 }
			 }
		 }


		 if ( ! @qc_sld_isset_table_column( $table, 'package_id' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `package_id` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'description' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `description` text NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'link_form' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `link_form` varchar(150)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'link_to' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `link_to` varchar(150)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'link_anchor_text' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `link_anchor_text` varchar(100)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'link_anchor_attr' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `link_anchor_attr` varchar(20)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'link_status' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `link_status` varchar(50)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'qcopd_tags' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `qcopd_tags` text NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }


		 if ( ! @qc_sld_isset_table_column( $table1, 'sandbox' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `sandbox` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table1, 'recurring' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `recurring` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 
		 if ( ! @qc_sld_isset_table_column( $table1, 'enable' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `enable` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table1, 'item' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `item` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }

		 if ( ! @qc_sld_isset_table_column( $table2, 'renew' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `renew` datetime NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table2, 'expire_date' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `expire_date` datetime NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table2, 'recurring' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `recurring` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table2, 'payment_method' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `payment_method` varchar(50)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table4, 'payment_method' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table4` ADD `payment_method` varchar(50)  NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
}
	
	
//Remove top admin bar for slduser only

//add_action('init', 'sld_remove_admin_bar_slduser');

function sld_remove_admin_bar_slduser(){
	if(!function_exists('wp_get_current_user')) {
		include(ABSPATH . "wp-includes/pluggable.php");
	}
	if(is_user_logged_in()){
		$current_user = wp_get_current_user();
		if(in_array('slduser',$current_user->roles)){
			add_filter('show_admin_bar', '__return_false');
		}
	}
}



//add_action( 'init', 'qcld_sld_wpdocs_load_textdomain' );

/**
 * Load plugin textdomain.
 */
function qcld_sld_wpdocs_load_textdomain() {
    load_plugin_textdomain( 'qc-opd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/*Menu Order*/
add_action( 'admin_init', 'qcsld_posts_order_wpse' );

function qcsld_posts_order_wpse()
{
    add_post_type_support( 'sld', 'page-attributes' );
}

//Plugin loaded
add_action( 'plugins_loaded', 'qcsld_plugin_loaded_fnc' );
function qcsld_plugin_loaded_fnc(){


	$prev = get_option('option_tree');
	
	if(isset($prev) && !empty($prev) && isset($prev['sld_enable_top_part'])){
		if(!get_option('sld_option_restore')){
			if(get_option('sld_option_tree')){
				update_option('sld_option_tree', $prev);
				add_option( 'sld_option_restore', 'yes', '', 'yes' );
			}else{
				add_option( 'sld_option_tree', $prev, '', 'yes' );
				add_option( 'sld_option_restore', 'yes', '', 'yes' );
			}
		}
	}

	global $wpdb;
	$table      = $wpdb->prefix.'sld_package';
	$pkg 		= $wpdb->get_row( "select * from $table" );
	$getoption1 = get_option('sld_option_tree');
	if( isset($pkg->paypal) && $pkg->paypal!='' ){	
		
		if($getoption1['sld_paypal_email']==''){
			$getoption1['sld_paypal_email'] = $pkg->paypal;
			update_option( 'sld_option_tree', $getoption1, 'yes' );
		}
		
	}
	
	if(isset($prev['sld_custom_style']) && $prev['sld_custom_style']!='' && !get_option('sld_option_restore_css')){
		$getoption1['sld_custom_style'] = $prev['sld_custom_style'];
		update_option( 'sld_option_tree', $getoption1, 'yes' );
		add_option( 'sld_option_restore_css', 'yes', '', 'yes' );
	}
	
}




function sld_click_table_fnc(){

	

	//var_dump( get_option('sld_click_table') );
	//wp_die();


	if(get_option('sld_click_table') !='added'){
	
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {

				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {

				$collate .= " COLLATE $wpdb->collate";

			}
		}

		$table            = $wpdb->prefix.'sld_click_table';
		update_option( 'sld_click_table', 'added' );
		
		$sql_sliders_Table = "
		CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `ip` varchar(255) NOT NULL,
		  `itemurl` varchar(255) NOT NULL,
		  `itemid` varchar(255) NOT NULL,
		  `time` datetime NOT NULL,
		  `optional` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table );

		add_option( 'sld_click_table', 'added', '', 'yes' );
	}
}

add_action('init', 'sld_click_table_fnc');



function sld_upvote_restrict_by_ip(){


	if( sld_get_option('sld_upvote_restrict_by_ip') == 'on' and get_option('sld_ip_table') != 'added' ){
	
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {

				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {

				$collate .= " COLLATE $wpdb->collate";

			}
		}

		$table             = $wpdb->prefix.'sld_ip_table';
		
		$sql_sliders_Table = "CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` varchar(255) NOT NULL,
		  `ip` varchar(255) NOT NULL,
		  `time` datetime NOT NULL,
		  `optional` varchar(15) NOT NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table );

		add_option( 'sld_ip_table', 'added', '', 'yes' );
	}
}

add_action('init', 'sld_upvote_restrict_by_ip');

add_action('pre_get_posts','sld_users_own_attachments');
function sld_users_own_attachments( $wp_query_obj ) {

    global $current_user, $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request )
        return;

	if(in_array('slduser',$current_user->roles) or in_array('subscriber',$current_user->roles))
		$wp_query_obj->set('author', $current_user->ID );

    return;
}

/*Include Update Checker - 06-12-2017, 01:58 AM, Kadir*/
//require_once 'class-qc-plugin-update-checker.php';

// SLD Customization code//

add_action( 'add_meta_boxes', 'qcld_add_post_meta_boxes' );
function qcld_add_post_meta_boxes(){
	add_meta_box(
		'sld-post-class',
		esc_html__( 'Reset Upvotes', 'qc-opd' ),
		'qcld_reset_post_class_meta_box',
		array('sld'),
		'side',
		'high'
	);
}
function qcld_reset_post_class_meta_box(){
?>
  <p>
    <label for="linkbait-post-class"><?php esc_html_e( "Click the Reset Button to reset upvotes", 'qc-opd' ); ?></label>
    <br />
	<div id="sld_show_msg"></div>
    <br />
	<input id="sld_reset_upvote" value="Reset Upvote" class="button" type="button">
  </p>
<?php
}
add_action( 'add_meta_boxes', 'qcld_add_post_slug_boxes' );
function qcld_add_post_slug_boxes(){
	add_meta_box(
		'sld-post-slug-class',
		esc_html__( 'Slug Name', 'qc-opd' ),
		'qcld_reset_post_slug_class_meta_box',
		array('sld'),
		'side',
		'high'
	  );
}
function qcld_reset_post_slug_class_meta_box(){
	global $post;
?>
  <p>
    <code><?php echo esc_attr( $post->post_name ); ?></code>
    <br/>
    <br/>
  </p>
<?php
}

// To show the column header
function sld_cat_column_header( $columns ){
  $columns['header_name'] = 'ID'; 
 
  return $columns;
}

add_filter( "manage_edit-sld_cat_columns", 'sld_cat_column_header', 1);

// To show the column value
function sld_cat_column_content( $value, $column_name, $tax_id ){
   return $tax_id ;
}
add_action( "manage_sld_cat_custom_column", 'sld_cat_column_content', 1, 3);


add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
  
function my_custom_dashboard_widgets() {
global $wp_meta_boxes;
 
wp_add_dashboard_widget('sld_custom_help_widget', 'Simple Link Directory', 'sld_custom_dashboard_help');
}
function sld_custom_dashboard_help() {
	
global $wpdb;
		
		$getid = "select id from {$wpdb->prefix}sld_user_entry";
		$ids = $wpdb->get_results($getid);

		$total = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1" );
		
		$pending = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and (approval=0 or approval=3)" );
		$deny = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=2 " );
		$approved = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=1 " );
		
		$edited = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=3 " );

		$paiditem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id!=0 " );
		$freeitem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id=0 " );
		echo '<p>Submitted Links summery</p>';
		echo '<p>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s', 'qcsld_user_entry_list' )).'">All '.($total==''||$total==0?0:$total).'</a> <br> 
			
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=pending', 'qcsld_user_entry_list' )).'">'.__('Pending', 'qc-opd').' '.($pending==''||$pending==0?0:$pending).' </a><br> 
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=approved', 'qcsld_user_entry_list' )).'">'.__('Approved', 'qc-opd').' '.($approved==''||$approved==0?0:$approved).'</a> <br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=denied', 'qcsld_user_entry_list' )).'">'.__('Denied', 'qc-opd').'  '.($deny==''||$deny==0?0:$deny).'</a> </br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=paid', 'qcsld_user_entry_list' )).'">'.__('Paid', 'qc-opd').'  '.($paiditem==''||$paiditem==0?0:$paiditem).'</a> <br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=free', 'qcsld_user_entry_list' )).'">'.__('Free', 'qc-opd').'  '.($freeitem==''||$freeitem==0?0:$freeitem).'</a>

		</p>';

}

add_action( 'admin_menu', 'sld_pending_users_bubble', 999 );


function sld_pending_users_bubble() {
	global $menu, $wpdb;

		$pending = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and (approval=0 or approval=3)" );
		$pending_users = ($pending==''||$pending==0?0:$pending);
		// Locate the key of
		$key = sld_recursive_array_search( 'Simple Link Directory', $menu );
		// Not found, just in case
		if ( ! $key ) {
			return;
		}
		// Modify menu item
		$menu[$key][0] .= sprintf( '<span class="update-plugins count-%1$s" style="background-color:#de4848;color:white;margin-left:5px;"><span class="plugin-count">%1$s</span></span>', $pending_users );
	
}


function sld_recursive_array_search( $needle, $haystack ) {
	foreach ( $haystack as $key => $value ) {
		$current_key = $key;
		if ( $needle === $value || ( is_array( $value ) && sld_recursive_array_search( $needle, $value ) !== false ) ) {
			return $current_key;
		}
	}
	return false;
}


add_action( 'added_post_meta', 'sld_copy_to_other_list', 10, 4 );
function sld_copy_to_other_list( $meta_id, $post_id, $meta_key, $meta_value ) {
	global $wpdb;
    if ( $meta_key == 'qcopd_list_item01' ) { // we've been editing the post
		if(isset($meta_value['qcopd_other_list']) && !empty($meta_value['qcopd_other_list']) ){
			
			$listids = explode(',',$meta_value['qcopd_other_list']);
			$meta_value['qcopd_other_list'] = '';

			@$wpdb->delete(
                "{$wpdb->prefix}postmeta",
	            array( 'meta_id' => $meta_id ),
	            array( '%d' )
            );
			
			add_post_meta( $post_id, 'qcopd_list_item01', $meta_value );
			if(!empty($listids)){
				foreach($listids as $listid){
					if($post_id != $listid){
						@add_post_meta( $listid, 'qcopd_list_item01', $meta_value );
					}
						
				}
			}
		}
    }
}

//add_action( 'added_post_meta', 'sld_collect_image_from_web', 10, 4 );
function sld_collect_image_from_web( $meta_id, $post_id, $meta_key, $meta_value ){
	global $wpdb;
	if ( $meta_key == 'qcopd_list_item01' ) {
		
		if(isset($meta_value['qcopd_item_img']) && $meta_value['qcopd_item_img']==''){
			
			if(isset($meta_value['qcopd_item_link']) && $meta_value['qcopd_item_link']!=''){
				
				if(isset($meta_value['qcopd_image_from_link']) && $meta_value['qcopd_image_from_link']=='1'){
					
					// Main functionality
					
					
						
						$image = sld2_get_web_page("https://www.googleapis.com/pagespeedonline/v1/runPagespeed?url=".esc_url($meta_value['qcopd_item_link'])."&screenshot=true");
						$image = json_decode($image, true);
						$image = $image['screenshot']['data'];
						$upload_dir       = wp_upload_dir();
						$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
						$image = str_replace(array('_', '-'), array('/', '+'), $image);
						$imgBase64 = str_replace('data:image/jpeg;base64,', '', $image);
						$imgBase64 = str_replace(' ', '+', $imgBase64);
						$decoded = base64_decode($imgBase64);
						$filename         = 'sldwebsite.jpg';
						$hashed_filename  = md5( $filename . microtime() ) . '_' . $filename;
						$image_upload     = file_put_contents( $upload_path . $hashed_filename, $decoded );
						if( !function_exists( 'wp_handle_sideload' ) ) {
						  require_once( ABSPATH . 'wp-admin/includes/file.php' );
						}
						if( !function_exists( 'wp_get_current_user' ) ) {
						  require_once( ABSPATH . 'wp-includes/pluggable.php' );
						}
						$file             = array();
						$file['error']    = '';
						$file['tmp_name'] = $upload_path . $hashed_filename;
						$file['name']     = $hashed_filename;
						$file['type']     = 'image/jpeg';
						$file['size']     = filesize( $upload_path . $hashed_filename );
						$file_return      = wp_handle_sideload( $file, array( 'test_form' => false ) );
						$filename = $file_return['file'];
						$attachment = array(
						 'post_mime_type' => $file_return['type'],
						 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						 'post_content' => '',
						 'post_status' => 'inherit',
						 'guid' => $upload_dir['url'] . '/' . basename($filename)
						 );
						$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
						require_once(ABSPATH . 'wp-admin/includes/image.php');
						$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
						wp_update_attachment_metadata( $attach_id, $attach_data );					
						$meta_value['qcopd_item_img'] = $attach_id;
						
						@$wpdb->delete(
							"{$wpdb->prefix}postmeta",
							array( 'meta_id' => $meta_id ),
							array( '%d' )
						);
						add_post_meta( $post_id, 'qcopd_list_item01', $meta_value );
						
					

				}
				
			}
			
		}
		
	}
}

// add_action( 'admin_menu' , 'qcsld_help_link_submenu', 20 );
// function qcsld_help_link_submenu(){
// 	global $submenu;
	
// 	$link_text = "Help";
// 	$submenu["edit.php?post_type=sld"][250] = array( $link_text, 'activate_plugins' , admin_url('edit.php?post_type=sld&page=sld-options-page#section_help') );
// 	ksort($submenu["edit.php?post_type=sld"]);
	
// 	return ($submenu);
// }


add_action( 'add_meta_boxes', 'qcsld_meta_box_video' );
function qcsld_meta_box_video(){	// --- Parameters: ---

    add_meta_box( 'qc-sld-meta-box-id', // ID attribute of metabox
                  esc_html__( 'Shortcode Generator for SLD', 'qc-opd' ),       // Title of metabox visible to user
                  'qcsld_meta_box_callback', // Function that prints box in wp-admin
                  'page',              // Show box for posts, pages, custom, etc.
                  'side',            // Where on the page to show the box
                  'high' );            // Priority of box in display order
}

function qcsld_meta_box_callback( $post ){
    ?>
    <p>
        <label for="sh_meta_box_bg_effect"><p><?php esc_html_e('Click the button below to generate shortcode'); ?></p></label>
		<input type="button" id="sld_shortcode_generator_meta" class="button button-primary button-large" value="<?php esc_html_e('Generate Shortcode'); ?>" />
    </p>
    
    <?php
}

//wp cron functionality

add_action( 'wp', 'sld_prefix_setup_schedule' );
/**
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
function sld_prefix_setup_schedule() {
    if ( ! wp_next_scheduled( 'sld_prefix_daily_event' ) ) {
        wp_schedule_event( time(), 'daily', 'sld_prefix_daily_event');
    }
}

add_action( 'sld_prefix_daily_event', 'sld_prefix_daily_event_fnc' );

/**
 * On the scheduled action hook, run a function.
 */
function sld_prefix_daily_event_fnc() {
    // check every user and see if their account is expiring, if yes, send your email.
	
	global $wpdb;

	$package_purchased_table = $wpdb->prefix.'sld_package_purchased';
	$package_table = $wpdb->prefix.'sld_package';
	
	if(sld_get_option('sld_enable_email_notification_package_expire')!='on'){
		return;
	}
	
	$userpkgs = $wpdb->get_results( $wpdb->prepare( "select * from $package_purchased_table WHERE 1 and `expire_date` < CURDATE()") );
	if(!empty($userpkgs)){
		
		foreach($userpkgs as $package){
			
			if(get_option('sld_is_email_sent_'.$package->id)!='yes'){
				
				sld_send_package_expire_notification($package);
				
				add_option( 'sld_is_email_sent_'.$package->id, 'yes', '', 'yes' );
			}
			
		}
		
	}

}

add_filter('post_row_actions','qc_sld_action_row', 10, 2);

function qc_sld_action_row($actions, $post){
    //check for your post type
    if ($post->post_type =="sld"){
       $actions['large_list_edit'] = '<a href="'.admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid=' . $post->ID ).'" rel="bookmark" aria-label="View &#8220;PRACTITIONER&#8221;">Edit Large List</a>';
	   return $actions;
    }
    return $actions;
}

// remove_action('template_redirect', 'redirect_canonical');


if( function_exists('register_block_type') ){
	function qcopd__sld_gutenberg_block() {
	    require_once plugin_dir_path( __FILE__ ).'/gutenberg/sld-block/plugin.php';
	}
	add_action( 'init', 'qcopd__sld_gutenberg_block' );
}



function qcsld_admin_importexport_enqueue(){

	wp_enqueue_style( 'wp-color-picker');
	wp_enqueue_script( 'wp-color-picker');

    wp_enqueue_script( 'sld-import-script', SLD_QCOPD_ASSETS_URL . '/admin/js/sld-admin-js.js', array('jquery','wp-color-picker') );
    wp_enqueue_style( 'sld-import-style', SLD_QCOPD_ASSETS_URL . '/admin/css/sld-admin-css.css' );


    global $post_type;

    if ($post_type == 'sld') {
        $customCss = "#edit-slug-box {display:none;} #qcopd_entry_time, #qcopd_is_bookmarked,#qcopd_click { display: none; }";
        wp_add_inline_style( 'sld-import-style', $customCss );
    }
	
}

add_action( 'admin_enqueue_scripts', 'qcsld_admin_importexport_enqueue' );

//plugin activate redirect codecanyon
function qc_SLD_activation_redirect( $plugin ) {

	qcopd_save_post_timelaps_init();

    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url('edit.php?post_type=sld&page=qcld_sld_help_license') ) );
    }
}
add_action( 'activated_plugin', 'qc_SLD_activation_redirect' );




function pd_ot_google_fonts_api_key( $key ) {
	$key = sld_get_option('sld_google_font_api');
	if( !isset($key) || empty($key) ){
		$key = 'AIzaSyCWhl0a3K4X0hi2Srdwm_hb4YQtTtVgNXU';
	}
  return $key;
}
add_filter( 'ot_google_fonts_api_key', 'pd_ot_google_fonts_api_key' );

function qcopd_init(){
  if( is_admin() ){ 
      global $pagenow;

      if (
      	('post.php' === $pagenow && (isset($_GET['post']) && 'sld' != get_post_type( $_GET['post'] )) ) ||
        ( 'post-new.php' === $pagenow && (isset($_GET['post_type']) && 'sld' != $_GET['post_type']) )
      ){
        // Remove scripts for metaboxes to post-new.php & post.php.
        remove_action( 'admin_print_scripts-post-new.php', 'ot_admin_scripts', 11 );
        remove_action( 'admin_print_scripts-post.php', 'ot_admin_scripts', 11 );

        // Remove styles for metaboxes to post-new.php & post.php.
        remove_action( 'admin_print_styles-post-new.php', 'ot_admin_styles', 11 );
        remove_action( 'admin_print_styles-post.php', 'ot_admin_styles', 11 );
      }
  }
}
add_action('init', 'qcopd_init');



add_action('wp_footer', 'sld_ajax_loader_html');
function sld_ajax_loader_html(){
	$img_src = apply_filters('qc_ajax_image_src',SLD_QCOPD_ASSETS_URL . '/images/ajax-loader.gif' );
?>
  <div class="qcld_sld_ajax_loader qc-sld-d-none" style="display:none">
    <div class="qcld_sld_ajax_loader_image">
      <img alt="ajax-loader" src="<?php echo $img_src; ?>">
    </div>
  </div>
  
<?php
	
}



/**
 * @param $n
 * @return string
 * Use to convert large positive numbers in to short form like 1K+, 100K+, 199K+, 1M+, 10M+, 1B+ etc
 */

function qc_sld_shorten($number){
    $suffix = ["", "K", "M", "B", "T"];
    $precision = 2;
    for($i = 0; $i < count($suffix); $i++){
        $divide = $number / pow(1000, $i);
        if($divide < 1000){
        	if( $i>0 ){
            	return round($divide, $precision).$suffix[$i].'+';
        	}else{
        		return round($divide, $precision).$suffix[$i];
        	}
            break;
        }
    }
}


add_filter('http_request_args', 'sld_curl_args', 10, 2);
function sld_curl_args($r, $url){
	$r['sslverify'] = false;
        return $r;
}

add_action('plugins_loaded', 'sld_create_tg_on_sld_user_table');
function sld_create_tg_on_sld_user_table(){
	global $wpdb;
	$check_if_exist = get_option('qcsld_user_tag_col_added');
	if( $check_if_exist ){
		return;
	}

	$table = $wpdb->prefix.'sld_user_entry';
	$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table' AND column_name = 'qcopd_tags'"  );

	if(empty($row)){
	   $add_column = $wpdb->query("ALTER TABLE $table ADD qcopd_tags text NOT NULL");
	   if( $add_column ){
	   	update_option('qcsld_user_tag_col_added', 1);
	   }
	}
}

function qcsld_remove_admin_menu_items() {
    if( !current_user_can( 'edit_posts' ) ):
        remove_menu_page( 'edit.php?post_type=sld' );
    endif;
}
add_action( 'admin_menu', 'qcsld_remove_admin_menu_items' );

/* exclude item filter */
add_filter('sld_exclude_item_by_attr', 'qc_sld_exclude_items', 10, 2);
function qc_sld_exclude_items($lists, $exclude){
    
    $new_lists = array();
	foreach( $lists as $list ) {

		if( !empty($exclude) ){
			
			$exclude_items = explode(',',$exclude);

			$qcopd_timelaps = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';

			if (in_array($qcopd_timelaps, $exclude_items )){
					
				continue;

			} else {
				$new_lists[] = $list;

			}
			
		} else {

			$new_lists[] = $list;

		}
	}
	$lists = $new_lists;

	return $lists;
}

add_filter('sld_generate_img_youtube', 'sld_generate_img_youtubes', 10, 3);
function sld_generate_img_youtubes($faviconImgUrl, $item_url, $directImgLink){
    
	if ( ( !empty($item_url) && strpos($item_url, 'youtube') > 0 ) || ( !empty($directImgLink) && strpos($directImgLink, 'youtube') > 0 )  ) {
		$youtube_url_path = ( isset($item_url) && !empty($item_url) ) ? $item_url : $directImgLink;
		if( strpos($directImgLink, 'img.youtube') > 0 ){
        	$faviconImgUrl = esc_url($directImgLink);
			return $faviconImgUrl;
		}else{
	        $youtube_url_id = preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_url_path, $match);
			$youtube_video_id = isset($match[1]) ? $match[1] : '';
	        $faviconImgUrl = esc_url('https://img.youtube.com/vi/'.$youtube_video_id.'/mqdefault.jpg');
			return $faviconImgUrl;
		}

    }

	return $faviconImgUrl;
}


function qcopd_save_post_timelaps_init(){
    

    global $post; 
    global $wpdb;

	$args = array(
		'numberposts' => -1,
		'post_type'   => 'sld',
	);

	$listItems = get_posts( $args );

	if(!empty($listItems)){

		foreach ($listItems as $item){

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $item->ID ) );

			if(!empty($results)){

				$laps = 1;
				foreach ($results as $key => $value) {

			        $item = $value;

			        $meta_id = $value->meta_id;

			        $unserialized = maybe_unserialize($value->meta_value);

			        $new_array = array();

			    	foreach($unserialized as $key => $value) {

			            if ($key == 'qcopd_timelaps' && $value == '' ) {
							$new_array[$key] = time() + $laps++;
			            } else {
			                $new_array[$key] = $value;
			            }
			           
			        }

			        $updated_value = maybe_serialize($new_array);

			        $wpdb->update(
			            $wpdb->postmeta,
			            array(
			                'meta_value' => $updated_value,
			            ),
			            array('meta_id' => $meta_id)
			        );

					

			  	}

			}
		}
	}

}

//Allow Contributors to Upload Media
/*if( current_user_can('contributor') && !current_user_can('upload_files') ){
	

}

add_action('admin_init', 'allow_contributor_uploads');
function allow_contributor_uploads() {
     $contributor = get_role('contributor');
     $contributor->add_cap('upload_files');
}*/

/*add_action('admin_init', 'allow_contributor_uploads');
function allow_contributor_uploads() {
     $contributor = get_role('author');
     $contributor->add_cap('upload_files');
}*/

add_action( 'admin_notices', 'qcopd_wp_pro_shortcode_notice',100 );

 function qcopd_wp_pro_shortcode_notice(){

    global $pagenow, $typenow;

    if ( isset($typenow) && $typenow == 'sld'  ) {

           ?>
                <div id="message" class="notice notice-info is-dismissible">
                    <p>
                        <?php

                        printf(
                            __('%s Simple Link Directory %s works the best when you create multiple Lists and show them all in a page. Use the following shortcode to display All lists on any page:  %s %s %s  Use the %s shortcode generator %s to select style and other options.', 'qc-opd' ),
                            '<strong>',
                            '</strong>',
                            '<code>',
                            '[qcopd-directory mode="all" style="simple" column="2" search="true" category="" upvote="on" item_count="on" orderby="date" order="DESC" item_orderby="title"]',
                            '</code>',
                            '<strong>',
                            '</strong>'
                        );

                        ?>
                    </p>
                </div>
        <?php 
        
    }

}


add_filter('qcld_offline_link_select_option', 'qcld_offline_link_select_option', 10, 2);
if(!function_exists('qcld_offline_link_select_option')){
    function qcld_offline_link_select_option($id, $package ){

        if(sld_get_option('sld_enable_offline_payment') != 'on'){
            return;
        }

        $offline_payment = (sld_get_option('sld_lan_for_offline_payment')!=''?sld_get_option('sld_lan_for_offline_payment'):esc_html('Offline Payment', 'qc-opd'));
        
        if($package == 7777){
            echo '<option value="7777" selected="selected">'. __($offline_payment, 'qc-opd').'</option>';
        }else{
            echo '<option value="7777" >'. __($offline_payment, 'qc-opd').'</option>';

        }

    }
}