<?php
defined('ABSPATH') or die("No direct script access!");



/* URL Filtering Logic, to remove http:// or https:// */
function qcsld_remove_http($url) {
   $disallowed = array('http://', 'https://');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return trim($url);
}

/*Custom Item Sort Logic*/
function custom_sort_by_tpl_click($a, $b) {
	
    //return @($a['qcopd_click'] * 1 < $b['qcopd_click'] * 1);
	$aTime = isset($a['qcopd_click']) && !empty( $a['qcopd_click'] ) ? (int)$a['qcopd_click'] : 0;
	$bTime = isset($b['qcopd_click']) && !empty( $b['qcopd_click'] ) ? (int)$b['qcopd_click'] : 0;

	if( $aTime === $bTime ){
		return 0;
	}

	return $aTime < $bTime  ? 1 : -1;
	
}

function sld_custom_sort_by_tpl_upvotes($a, $b) {
   // return @($a['qcopd_upvote_count'] * 1 < $b['qcopd_upvote_count'] * 1);
	$aTime = isset($a['qcopd_upvote_count']) && !empty( $a['qcopd_upvote_count'] ) ? (int)$a['qcopd_upvote_count'] : 0;
	$bTime = isset($b['qcopd_upvote_count']) && !empty( $b['qcopd_upvote_count'] ) ? (int)$b['qcopd_upvote_count'] : 0;

	if( $aTime === $bTime ){
		return 0;
	}

	return $aTime > $bTime  ? 1 : -1;
}

function sld_custom_sort_by_tpl_upvotes_asc($a, $b) {
   // return @($a['qcopd_upvote_count'] * 1 < $b['qcopd_upvote_count'] * 1);
	$aTime = isset($a['qcopd_upvote_count']) && !empty( $a['qcopd_upvote_count'] ) ? (int)$a['qcopd_upvote_count'] : 0;
	$bTime = isset($b['qcopd_upvote_count']) && !empty( $b['qcopd_upvote_count'] ) ? (int)$b['qcopd_upvote_count'] : 0;

	if( $aTime === $bTime ){
		return 0;
	}

	return $aTime < $bTime  ? 1 : -1;
}

function custom_sort_by_tpl_featured($a, $b) {
   // return @($a['qcopd_featured'] * 1 < $b['qcopd_featured'] * 1);
	$aTime = isset($a['qcopd_featured']) && !empty( $a['qcopd_featured'] ) ? (int)$a['qcopd_featured'] : 0;
	$bTime = isset($b['qcopd_featured']) && !empty( $b['qcopd_featured'] ) ? (int)$b['qcopd_featured'] : 0;

	if( $aTime === $bTime ){
		return 0;
	}

	return $aTime < $bTime  ? 1 : -1;
}

function sld_custom_sort_by_tpl_title_asc($a, $b) {
	if( isset($a['qcopd_item_title']) && isset($b['qcopd_item_title']) ){
    	return strnatcasecmp( trim($a['qcopd_item_title']), trim($b['qcopd_item_title']));
	}
}

function sld_custom_sort_by_tpl_title($a, $b) {
	if( isset($a['qcopd_item_title']) && isset($b['qcopd_item_title']) ){
    	return strnatcasecmp(trim($b['qcopd_item_title']), trim($a['qcopd_item_title']));
	}
}

function sld_custom_sort_by_tpl_timestamp($a, $b) {
	if( isset($a['qcopd_timelaps']) && isset($b['qcopd_timelaps']) ){
		// $aTime = (int)$a['qcopd_timelaps'];
		// $bTime = (int)$b['qcopd_timelaps'];
		// return $aTime < $bTime;
		$aTime = isset($a['qcopd_timelaps']) && !empty( $a['qcopd_timelaps'] ) ? (int)$a['qcopd_timelaps'] : 0;
		$bTime = isset($b['qcopd_timelaps']) && !empty( $b['qcopd_timelaps'] ) ? (int)$b['qcopd_timelaps'] : 0;

		if( $aTime === $bTime ){
			return 0;
		}

		return $aTime < $bTime  ? 1 : -1;
	}
}

function sld_custom_sort_by_tpl_timestamp_asc($a, $b) {
	if( isset($a['qcopd_timelaps']) && isset($b['qcopd_timelaps']) ){
		// $aTime = (int)$a['qcopd_timelaps'];
		// $bTime = (int)$b['qcopd_timelaps'];
		// return $bTime < $aTime;
		$aTime = isset($a['qcopd_timelaps']) && !empty( $a['qcopd_timelaps'] ) ? (int)$a['qcopd_timelaps'] : 0;
		$bTime = isset($b['qcopd_timelaps']) && !empty( $b['qcopd_timelaps'] ) ? (int)$b['qcopd_timelaps'] : 0;

		if( $aTime === $bTime ){
			return 0;
		}

		return $bTime < $aTime  ? 1 : -1;
	}
}


$SLD_QCOPD_DIRectory_instance_count=0;
//For all list elements
add_shortcode('qcopd-directory', 'SLD_QCOPD_DIRectory_full_shortcode');

function SLD_QCOPD_DIRectory_full_shortcode( $atts = array() )
{

wp_enqueue_script( 'qcopd-custom1-script'); //category tab
wp_enqueue_script( 'qcopd-custom-script');
wp_enqueue_script( 'qcopd-grid-packery');
wp_enqueue_script( 'jq-slick.min-js');
wp_enqueue_style( 'jq-slick-theme-css');
wp_enqueue_style( 'jq-slick.css-css');
wp_enqueue_script( 'qcopd-sldcustom-common-script');
wp_enqueue_script( 'qcopd-sldcustom-2co-script');
wp_enqueue_script( 'qcopd-custom-script-sticky');
wp_enqueue_script('qcopd-embed-form-script');
wp_enqueue_script( 'qcopd-tooltipster');
wp_enqueue_script( 'qcopd-magpopup-js');
wp_enqueue_style( 'sldcustom_login-css');
wp_enqueue_style( 'qcopd-magpopup-css');
wp_enqueue_style( 'sld-tab-css');
wp_enqueue_style('qcopd-embed-form-css');
wp_enqueue_style( 'qcopd-sldcustom-common-css');
wp_enqueue_style( 'qcopd-custom-registration-css');
wp_enqueue_style( 'qcopd-custom-rwd-css');
wp_enqueue_style( 'qcopd-custom-css');
wp_enqueue_style( 'qcfontawesome-css');
wp_enqueue_style('qcopd-embed-form-css');
wp_enqueue_script('qcopd-embed-form-script');
	ob_start();
    sld_show_qcopd_full_list( $atts );
    $content = ob_get_clean();
    return $content;
}

function sld_show_qcopd_full_list( $atts = array() )
{
	$template_code = "";

	global $SLD_QCOPD_DIRectory_instance_count;
	$SLD_QCOPD_DIRectory_instance_count++;

	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'orderby' => 'menu_order',
			'filterorderby' => 'menu_order',
			'order' => 'ASC',
			'filterorder' => 'ASC',
			'mode' => 'all',
			'list_id' => '',
			'column' => '1',
			'style' => 'simple',
			'list_img' => 'true',
			'search' => 'true',
			'category' => "",
			'upvote' => "on",
			'item_count' => "on",
			'top_area' => "on",
			'item_orderby' => "",
			'item_order' => "",
			'mask_url' => "off",
			'tooltip' => 'false',
			'paginate_items' => 'false',
			'actual_pagination'	=>	'false',
			'per_page' => 5,
			'list_title_font_size' => '' ,
			'list_title_line_height' => '',
            'title_font_size' => '',
            'subtitle_font_size' => '',
            'title_line_height' => '',
            'subtitle_line_height' => '',
            'filter_area' => 'normal',
            'topspacing' => 0,
            'infinityscroll' => 0,
            'itemperpage' => 5,
			'favorite'	=> '',
			'multipage'	=>'false',
			'cattabid'	=>'',
			'removetop'	=>'no',
			'clink'		=> '',
			'statistics'=>'',
			'enable_left_filter'=>'',
			'exclude'=>'',
			'enable_tag_filter'=>'false',
			'main_click'=> '',
			'video_click' => 'popup',
			'item_details_page'=> 'off',
			'display_username'=> 'false',
			'hide_list_title' => 'false',
			'review'	=>	'false',
			'multipage_item_details'	=>	'true',
			'favorite_hide'	=>	'',
			'enable_image'	=>	'',

		), $atts
	));

	//ShortCode Atts
	$shortcodeAtts = array(
		'orderby' => $orderby,
		'order' => $order,
		'mode' => $mode,
		'list_id' => $list_id,
		'column' => $column,
		'style' => $style,
		'list_img' => $list_img,
		'search' => $search,
		'category' => $category,
		'upvote' => $upvote,
		'item_count' => $item_count,
		'top_area' => $top_area,
		'item_orderby' => $item_orderby,
		'item_order' => $item_order,
		'mask_url' => $mask_url,
		'tooltip' => $tooltip,
        'list_title_font_size' => $list_title_font_size ,
        'list_title_line_height' => $list_title_line_height ,
        'title_font_size' => $title_font_size,
        'subtitle_font_size' => $subtitle_font_size,
        'title_line_height' => $title_line_height,
        'subtitle_line_height' => $subtitle_line_height,
        'filter_area' => $filter_area,
        'topspacing' => $topspacing,
        'hide_list_title' => $hide_list_title,
        'display_username' => $display_username,

        'item_details_page' => $item_details_page,
        'filterorderby' => $filterorderby,

        'filterorder' => $filterorder,
        'paginate_items' => $paginate_items,
        'actual_pagination' => $actual_pagination,
        'per_page' => $per_page,
        'favorite' => $favorite,
        'enable_left_filter' => $enable_left_filter,
        'main_click' => $main_click,
        'video_click' => $video_click,
        'enable_tag_filter' => $enable_tag_filter,
        'review'	=>	$review,
        'favorite_hide'	=>	$favorite_hide,
        'enable_image'	=>	$enable_image,

	);
	$max_pagination_number = 1;


	$limit = -1;

	if( $mode == 'one' )
	{
		$limit = 1;
	}

	if( $video_click != 'nopopup' ){
		$video_click = 'popup';
	}
	
	
	if($style=="simple" && $infinityscroll==1){
		$list_args_total = array(
			'post_type' => 'sld',
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => -1,
		);
		$total_list_query = new WP_Query( $list_args_total );
		$count = $total_list_query->post_count;
		$total_page_count = ceil($count/$per_page);
		
		//Query Parameters
		$list_args = array(
			'post_type' => 'sld',
			'posts_per_page' => $per_page,
			'paged'			=> 1
			
		);
	}else{
		//Query Parameters
		$list_args = array(
			'post_type' => 'sld',
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => $limit,
			
		);
	}
	$statistic = false;
	
	if($exclude!=''){
		$list_args['post__not_in'] = explode(',', $exclude);
	}
	
	if(sld_get_option('sld_enable_statistics')=="on"){
		$statistic = true;
	}
	
	if(isset($statistics) && $statistics=="false"){
		$statistic = false;
	}
	
	if(isset($statistics) && $statistics=="true"){
		$statistic = true;
	}
	

	if( $list_id != "" && $mode == 'one' ){

		if(is_numeric($list_id)){
			$list_args = array_merge($list_args, array( 'p' => $list_id ));
		}else{
			$the_slug = $list_id;
			$sld_args = array(
			  'name'        => $the_slug,
			  'post_type'   => 'sld',
			  'post_status' => 'publish',
			  'numberposts' => 1
			);
			$sld_posts = get_posts($sld_args);

			$list_id =	$sld_posts[0]->ID;

			$list_args = array_merge($list_args, array( 'p' => $list_id ));
		}

	}

	if( $category != "" ){
		
		$category = explode(',',$category);
		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);

		$list_args = array_merge($list_args, array( 'tax_query' => $taxArray ));

	}


	// The Query
	$list_query = new WP_Query( $list_args );


    if ( isset($atts["style"]) && $atts["style"] )
        $template_code = $atts["style"];

    if (!$template_code)
        $template_code = "simple";

    if( $mode == 'one' and $template_code!='style-13' and $template_code!='style-11' and $template_code!='style-10' and $template_code!='style-3' and $template_code!='style-4' and $template_code!='style-14' and $template_code!='style-15-multipage' and $template_code!='style-15' and $template_code!='custom' ){
    	$column = '1';
    }
	
	if(sld_get_option('sld_enable_bookmark')=='on'){
		$sldfavorite = 'on';
	}else{
		$sldfavorite = 'off';
	}
	
	if($favorite=='enable'){
		$sldfavorite = 'on';
	}elseif($favorite=='disable'){
		$sldfavorite = 'off';
	}
	
	
	
$search_text = '';
if( isset($_POST['sld_searchtext']) ){ $search_text = $_POST['sld_searchtext']; }

if($topspacing==''){
	$topspacing = 0;
}

?>



<?php 
	$qcopd_custom_js = "var slduserMessage= '".sld_get_option('sld_bookmark_popup_content')."';";
	if(sld_get_option('sld_upvote_user_login')=='on'){

		$qcopd_custom_js .= "var allowupvote = true;";
		if(sld_get_option('sld_upvote_login_url')!=''):
		$qcopd_custom_js .= "var upvoteloginurl = '".sld_get_option('sld_upvote_login_url')."';";
		else:
		$qcopd_custom_js .= "var upvoteloginurl = '';";
		endif;

	}else{
		$qcopd_custom_js .= "var allowupvote = false;";
	}

	wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);


	$custom_css = '';
	$custom_css .= ".sld_scrollToTop{
			width: 30px;
		    height: 30px;
		    padding: 10px !important;
		    text-align: center;
		    font-weight: bold;
		    color: #444;
		    text-decoration: none;
		    position: fixed;
		    top: 88%;
		    right: 29px;
		    display: none;
		    background: url('".esc_url(SLD_QCOPD_IMG_URL)."/up-arrow.ico') no-repeat 5px 5px;
		    background-size: 20px 20px;
		    text-indent: -99999999px;
		    background-color: #ddd;
		    border-radius: 3px;
			z-index:9999999999;
			box-sizing: border-box;
			
		}
		.sld_scrollToTop:hover{
		text-decoration:none;
		}
		.filter-area{z-index: 99 !important;
		    padding: 10px 0px;
		    
		}
		.qc-grid-item h2{";
		    if($list_title_font_size!=''){ 
		     $custom_css .= "font-size: ".$list_title_font_size."; ";
		 	} 
			if($list_title_line_height!=''){
				$custom_css .= "line-height:".$list_title_line_height.";";
			} 
		$custom_css .= "}";

		if(sld_get_option('sld_bookmark_item_featured_background_color')!=''){
			$custom_css .= ".qcopd-list-wrapper .simple .featured-section,
				.qcopd-list-wrapper .".$template_code." .featured-section {
				   background:".sld_get_option('sld_bookmark_item_featured_background_color').";
				    width: 100%;
				    height: 100%;
				    right: 0px;
				    border-width: 0px 0px 0px 0px;
				    border-color: transparent transparent transparent transparent;
				}

				.simple ul li a,
				.".$template_code." ul li a {
					position: relative;
				    z-index: 100;
				}";

		} 


	wp_add_inline_style( 'qcopd-custom-css', $custom_css );

?>

<?php if(sld_get_option('sld_enable_scroll_to_top')=='on'): ?>
<a href="#"class="sld_scrollToTop">Scroll To Top</a>
<?php 
	$qcopd_custom_js  = "";
	$qcopd_custom_js .= "jQuery(document).ready(function($){
						  $(window).scroll(function(){
								if ($(this).scrollTop() > 100) {
									$('.sld_scrollToTop').fadeIn();
								} else {
									$('.sld_scrollToTop').fadeOut();
								}
							});

							//Click event to scroll to top
							$('.sld_scrollToTop').click(function(){
								$('html, body').animate({scrollTop : 0},800);
								return false;
							});";

						if($filter_area=='fixed'):
						    $qcopd_custom_js .= "if($('body').prop('clientWidth')>500){
						        $('.filter-area').sticky({ topSpacing: ".$topspacing.", center:true });
						    }";
						endif;
						$qcopd_custom_js .= "})";
	

	wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);

?>
<?php endif; ?>
<?php if($mode == "favorite" ){ ?>

<?php 
	$qcopd_custom_js  = "";
	$qcopd_custom_js .= "var login_url_sld = '".sld_get_option('sld_bookmark_user_login_url')."';
						var template = '".$style."';
						var bookmark = {";
							if( is_user_logged_in() ) {
							$qcopd_custom_js .= "is_user_logged_in:true,";
							} else {
							$qcopd_custom_js .= "is_user_logged_in:false,";
							}
							$qcopd_custom_js .= "userid: ".get_current_user_id()." };";
	

	wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);

?>

<?php } ?>

<?php
 if(sld_get_option('sld_use_global_thumbs_up')!=''){
     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
 }else{
     $sld_thumbs_up = 'fa-thumbs-up';
 }
?>

<?php
	//if( !is_admin() ){
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'sld/templates/'.$template_code.'/template.php' ) ) {
			$current_template_path = get_stylesheet_directory_uri() . '/sld/templates/';
			$tempath = trailingslashit( get_stylesheet_directory() ) . 'sld/templates/'.$template_code.'/template.php';
		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'sld/templates/'.$template_code.'/template.php' ) ) {
			$current_template_path = get_template_directory_uri() . '/sld/templates/';
			$tempath = trailingslashit( get_template_directory() ) . 'sld/templates/'.$template_code.'/template.php';

		// Check theme compatibility last
		} elseif ( file_exists( SLD_QCOPD_DIR ."/templates/".$template_code."/template.php" ) ) {
			$current_template_path = SLD_QCOPD_DIR ."/templates/";
	    	$tempath = SLD_QCOPD_DIR ."/templates/".$template_code."/template.php";
		}

		if( isset($tempath) && !empty( $tempath ) ) {

		    require ( $tempath );

		}
		wp_reset_query();
		
		if($statistic && $multipage!="true"){

			$qcopd_custom_js  = "var statistic = true;";
			wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js, 'before');
		}
	//}

	if($search_text != ''){

		$qcopd_custom_js  = "jQuery(window).on('load',function(){
			jQuery('#live-search input[type=text]').val('".$search_text."');
			jQuery('#live-search input[type=text]').trigger('keyup');
		});";
		wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);

	}
	
}



/*TinyMCE button for Inserting Shortcode*/
/* Add Slider Shortcode Button on Post Visual Editor */
function qcopdsld_tinymce_button_function() {
	add_filter ("mce_external_plugins", "qcopd_sld_btn_js");
	add_filter ("mce_buttons", "qcopd_sld_btn");
}

function qcopd_sld_btn_js($plugin_array) {
	$plugin_array['qcopdsldbtn'] = plugins_url('assets/js/qcopd-tinymce-button.js', __FILE__);
	return $plugin_array;
}

function qcopd_sld_btn($buttons) {
	array_push ($buttons, 'qcopdsldbtn');
	return $buttons;
}

//add_action('init', 'qcopdsld_tinymce_button_function');


add_shortcode('sld-searchbar', 'sld_searchbar_function');
function sld_searchbar_function( $atts, $content = null ){
	$params = shortcode_atts( array(
					'post_id' => 0,
					'placeholder' => __('Search for your Items', 'qc-opd'),
					'search_text' => ''
				), $atts, 'sld-searchbar');
	$action_url = get_permalink($params['post_id']);
	ob_start();
?>
	<div class="pd-half">
        <form action="<?php echo $action_url; ?>" class="styled" method="post">
            <input name="sld_searchtext" type="text" class="text-input sld-search sld_search_filter" placeholder="<?php echo $params['placeholder']; ?>"/>
            <?php
            
            	$sld_global_search_icon = sld_get_option('sld_global_search_icon') ? sld_get_option('sld_global_search_icon') : 'fa-search';
            ?> 
            	<button type="submit" class="qc_submit_btn" value="submit"> <i class="fa <?php echo esc_html($sld_global_search_icon); ?>" ></i></button>
            	
            
        </form>
    </div>
<?php
	return ob_get_clean();
}