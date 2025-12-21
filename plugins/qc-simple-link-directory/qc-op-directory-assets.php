<?php

/*Load Scripts only in the shortcode page*/
function qcopd_check_for_shortcode($posts) {
	if ( empty($posts) )
		return $posts;

	// false because we have to search through the posts first
	$found = false;

	// search through each post
	foreach ($posts as $post) {
		// check the post content for the short code
		if ( stripos($post->post_content, 'qcopd-directory') )
			// we have found a post with the short code
			$found = true;
		// stop the search
		break;
	}

	if ($found){
		//Load Script and Stylesheets
		//add_action('wp_enqueue_scripts', 'qcopd_load_all_scripts');
	}

	return $posts;
}

//perform the check when the_posts() function is called
add_action('the_posts', 'qcopd_check_for_shortcode');

add_action('template_redirect', 'qcopd_check_for_shorcode');
function qcopd_check_for_shorcode(){
	global $wp_query;
	if ( is_singular() ) {
		$post = $wp_query->get_queried_object();
		
		
		if ( $post && strpos($post->post_content, 'qcopd-directory' ) !== false ) {
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
		}
		
		if($post && strpos($post->post_content, 'sld_dashboard' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
        
        if($post && strpos($post->post_content, 'sld_login' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}

		if($post && strpos($post->post_content, 'sld_registration' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}

		if($post && strpos($post->post_content, 'sld_restore' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}

		if($post && strpos($post->post_content, 'qcopd-directory-multipage' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
		
		if($post && strpos($post->post_content, 'sld-tab' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
			add_action('wp_enqueue_scripts', 'qcsld_category_tab');

		}

		if($post && strpos($post->post_content, 'sld_claim_listing' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}

		if($post && strpos($post->post_content, 'sld-multipage-category' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
	}
}




/*Load Global Scripts*/


add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
function qcopd_load_global_scripts()
{
	 if(sld_get_option('sld_image_upload')=='on'){
	    wp_enqueue_media();
	 }
	//FontAwesome
	
	wp_register_style( 'sldcustom_dashboard-css', SLD_QCOPD_ASSETS_URL.'/css/dashboardstyle.css', __FILE__ );
	$customCss = sld_get_option( 'sld_custom_style' );

		if( trim($customCss) != "" ) :

			wp_add_inline_style( 'sldcustom_dashboard-css', $customCss );
		endif;
	//wp_enqueue_style( 'sldcustom_dashboard-css' );
	    //FontAwesome
    wp_register_style('qcopd-embed-form-css', SLD_QCOPD_URL . 'embed/css/embed-form.css');

    //Scripts
    wp_register_script('qcopd-embed-form-script', SLD_QCOPD_URL . 'embed/js/embed-form.js', array('jquery'));
	
	wp_register_style( 'qcfontawesome-css', SLD_QCOPD_ASSETS_URL . '/css/font-awesome.min.css');
	wp_register_style( 'qcopd-custom-css', SLD_QCOPD_ASSETS_URL . '/css/directory-style.css');
	wp_register_style( 'qcopd-custom-rwd-css', SLD_QCOPD_ASSETS_URL . '/css/directory-style-rwd.css');
	wp_register_style( 'qcopd-custom-registration-css', SLD_QCOPD_ASSETS_URL . '/css/sld_registration.css');
	wp_register_style( 'qcopd-sldcustom-common-css', SLD_QCOPD_ASSETS_URL . '/css/sldcustomize-common.css');
	wp_register_style('qcopd-embed-form-css', SLD_QCOPD_URL . 'embed/css/embed-form.css');
	wp_register_style( 'sld-tab-css', SLD_QCOPD_ASSETS_URL . '/css/tab_style.css');
	wp_register_style( 'qcopd-magpopup-css', SLD_QCOPD_ASSETS_URL . '/css/magnific-popup.css');

	wp_register_style( 'sldcustom_login-css', SLD_QCOPD_ASSETS_URL.'/css/style.css', __FILE__ );
	//wp_enqueue_style( 'sldcustom_login-css' );

	

	$customCss = sld_get_option( 'sld_custom_style' );

	if( !empty(trim($customCss)) ) {

		wp_add_inline_style( 'qcopd-custom-css', $customCss );
		// qcopd-custom-css
	}

	//Scripts
	//wp_enqueue_script( 'jquery', 'jquery');
	wp_enqueue_script("jquery");
	//wp_deregister_script('jquery');
	
	wp_register_script( 'qcopd-magpopup-js', SLD_QCOPD_ASSETS_URL . '/js/jquery.magnific-popup.min.js', array('jquery'));
	wp_register_script( 'qcopd-tooltipster', SLD_QCOPD_ASSETS_URL . '/js/tooltipster.bundle.min.js', array('jquery'),'',true);

	
	wp_register_script('qcopd-embed-form-script', SLD_QCOPD_URL . 'embed/js/embed-form.js', array('jquery'),'',true);

	wp_register_script( 'qcopd-custom-script-sticky', SLD_QCOPD_ASSETS_URL . '/js/jquery.sticky.js', array('jquery'),'',true);

	wp_register_script( 'qcopd-sldcustom-2co-script', SLD_QCOPD_ASSETS_URL . '/js/2co.min1.js', array(),'',true);

	wp_register_script( 'qcopd-sldcustom-common-script', SLD_QCOPD_ASSETS_URL . '/js/sldcustomization-common.js', array('jquery'),'',true);
	
	
	
	
	$filterType = sld_get_option( 'sld_filter_ptype' );

		wp_register_style( 'jq-slick.css-css', SLD_QCOPD_ASSETS_URL . '/css/slick.css');
		wp_register_style( 'jq-slick-theme-css', SLD_QCOPD_ASSETS_URL . '/css/slick-theme.css', array(), '1.0.1');
		wp_register_script( 'jq-slick.min-js', SLD_QCOPD_ASSETS_URL . '/js/slick.min.js', array('jquery'));

}
function qcsld_category_tab(){
	wp_register_script( 'qcopd-custom1-script', SLD_QCOPD_ASSETS_URL . '/js/category-tab.js', array('jquery'),'',true);
}


add_action( 'wp_enqueue_scripts', 'sld_my_scripts', 20, 1);
function sld_my_scripts() {
	
	wp_register_script( 'qcopd-grid-packery', SLD_QCOPD_ASSETS_URL . '/js/packery.pkgd.js', array('jquery'),'',true);
	wp_register_script( 'qcopd-custom-script', SLD_QCOPD_ASSETS_URL . '/js/directory-script.js', array('jquery', 'qcopd-grid-packery', 'qcopd-image-loaded' ),'1.0',true);
	wp_register_script( 'qcopd-image-loaded', SLD_QCOPD_ASSETS_URL . '/js/imagesloaded.js', array('jquery'),'1.0',true);
	// wp_enqueue_script('qcopd-image-loaded');
	$inclusive_tag_filter 		= sld_get_option('inclusive_tag_filter') ? sld_get_option('inclusive_tag_filter') : 'off';
	$sld_enable_image_loaded 	= sld_get_option('sld_enable_image_loaded') ? sld_get_option('sld_enable_image_loaded') : 'off';
	$sld_no_results_found 		= sld_get_option('sld_no_results_found') ? sld_get_option('sld_no_results_found') : esc_html('No Results Found for Your Search');

	$params 					= array(
	  'ajaxurl' 				=> admin_url('admin-ajax.php'),
	  'ajax_nonce' 				=> wp_create_nonce('quantum_ajax_validation_18'),
	  'rtl'						=> (sld_get_option('sld_enable_rtl')=='on'?'on':'off'),
	  'main_click_upvote'		=> (sld_get_option('sld_main_click_upvote')=='on'?'on':'off'),
	  'no_result_found_text' 	=> $sld_no_results_found,
	  'inclusive_tag_filter' 	=> $inclusive_tag_filter,
	  'sld_enable_image_loaded' => $sld_enable_image_loaded
	);
	wp_localize_script( 'qcopd-custom-script', 'sld_ajax_object', $params );

	$customjs = sld_get_option( 'sld_custom_js' );
	
	if(!empty($customjs)){

		$qcopd_custom_js = "jQuery(document).ready(function($){
			".$customjs."
		})";

		wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);

	}


}



/*******************************
 * Admin Script
 *******************************/
function sld_qcsld_admin_enqueue($hook) {
	global $typenow;
	$post_types = get_post_types();
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'qcsld-fa-script', SLD_QCOPD_ASSETS_URL . '/js/admin-fa-script.js', array('jquery') );
	wp_enqueue_script( 'qcsld-admin-cmn-js', SLD_QCOPD_ASSETS_URL . '/js/admin-common.js', array('jquery') );
	wp_enqueue_script( 'qcsld-admin-tagInput-js', SLD_QCOPD_ASSETS_URL . '/js/tagInput.js', array('jquery') );
	wp_enqueue_script( 'qcopd-sldcustom-common-script-admin', SLD_QCOPD_ASSETS_URL . '/js/sldcustomization-common.js', array('jquery'));

	$params 					= array(
	  'ajaxurl' 				=> admin_url('admin-ajax.php'),
	  'ajax_nonce' 				=> wp_create_nonce('quantum_ajax_validation_18')
	);
	wp_localize_script( 'qcsld-admin-cmn-js', 'sld_ajax_object', $params );
	wp_localize_script( 'qcsld-fa-script', 'sld_ajax_object', $params );
	
	//wp_enqueue_script( 'qcopd-sldcustom-common-select2', SLD_QCOPD_URL . 'inc/cmb/js/vendor/select2/select2.js', array('jquery'));
	
	wp_enqueue_style( 'qcsld-fa-modal-css', SLD_QCOPD_ASSETS_URL . '/css/admin-fa-css.css' );
	wp_enqueue_style( 'qcsld-fa-css', SLD_QCOPD_ASSETS_URL . '/css/font-awesome.min.css' );

	if( $typenow === 'sld' ){
		wp_enqueue_style( 'qcsld-common-css', SLD_QCOPD_ASSETS_URL . '/css/admin-common.css' );
	}else{
		foreach ($post_types as $key => $value) {
			if( $key == $typenow ){
				wp_enqueue_style( 'qcsld-common-css', SLD_QCOPD_ASSETS_URL . '/css/admin-common.css' );
			}
		}
	}
	
	wp_enqueue_style( 'qcopd-sldcustom-common-css-admin', SLD_QCOPD_ASSETS_URL . '/css/sldcustomize-common.css');
	
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script( 'ilist-admin-quicksearch-js', SLD_QCOPD_ASSETS_URL . '/js/jquery.quicksearch.js', array('jquery'), $ver = false, $in_footer = false );
	$screen = get_current_screen();
	if($screen->post_type=='sld'){
		wp_deregister_script('alpha-color-picker');
		wp_deregister_style('alpha-color-picker');
		wp_deregister_style('Total_Soft_Poll');
		
	}

}

add_action( 'admin_enqueue_scripts', 'sld_qcsld_admin_enqueue' );

/*Global Font Configs*/


//add_action('wp_head', 'sld_global_font_configurations_func');


add_action( 'wp_enqueue_scripts', 'sld_global_font_configurations_func' );

function sld_global_font_configurations_func()
{

	$sld_use_global_font = sld_get_option('sld_use_global_font');
	if(isset($sld_use_global_font) and $sld_use_global_font=='yes'){
		$sldFontConfig = sld_get_option( 'sld_global_font' );
		
		if( isset($sldFontConfig) && count($sldFontConfig) > 0 ){
			$fontFamily = (trim($sldFontConfig[0]['family']));
			
			$json  = wp_remote_fopen( SLD_QCOPD_ASSETS_URL . '/fonts/webfont.json' );
			$json = json_decode($json);
			
			foreach($json->items as $fonts){
				
				if($fontFamily==str_replace(' ','',strtolower($fonts->family))){
					$fontFamily = $fonts->family;
					break;
				}
			}
			
			wp_enqueue_style( 'qcld-font-family', 'https://fonts.googleapis.com/css?family='.str_replace(" ", "+", $fontFamily) ); 
			
			if( !empty($fontFamily) ){

				$qcld_font_family_custom_css ="";
                $qcld_font_family_custom_css .="    .qc-grid-item h3, .qc-grid-item h2, .qc-grid-item h3 span, .qc-grid-item .upvote-count, .qc-grid-item ul li, .qc-grid-item ul li a, .sldp-holder a, .html5tooltip-top .html5tooltip-text, .html5tooltip-top a, .tooltipster-base{";
					
						if($fontFamily=='Indieflower'){
						
							$qcld_font_family_custom_css .="  font-family: 'Indie Flower',cursive;";
						
						}else{
						
							$qcld_font_family_custom_css .=" font-family: ".$fontFamily.", sans-serif !important;";
						
						}
						
                    $qcld_font_family_custom_css .=" }";
                
				wp_add_inline_style( 'qcld-font-family', $qcld_font_family_custom_css );

			}

		}
	}

}
