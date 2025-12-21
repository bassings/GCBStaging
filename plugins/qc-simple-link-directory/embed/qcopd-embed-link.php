<?php 
wp_head();

wp_enqueue_style('sld-embed-awesome-css', SLD_QCOPD_ASSETS_TPL_URL . "/css/font-awesome.min.css" );
wp_enqueue_style('sld-embed-directory-css', SLD_QCOPD_ASSETS_TPL_URL . "/css/directory-style.css" );
wp_enqueue_style('sld-embed-form-css', SLD_QCOPD_URL . "/embed/css/embed-form.css" );
wp_enqueue_style('sld-embed-directory-rwd', SLD_QCOPD_ASSETS_TPL_URL . "/css/directory-style-rwd.css" );
wp_enqueue_style('sld-embed-slick-css', SLD_QCOPD_ASSETS_TPL_URL . "/css/slick.css" );
wp_enqueue_style('sld-embed-magnific-css', SLD_QCOPD_ASSETS_TPL_URL . "/css/magnific-popup.css" );
wp_enqueue_style('sld-embed-slick-theme-css', SLD_QCOPD_ASSETS_TPL_URL . "/css/slick-theme.css" );




wp_enqueue_script( 'sld-embed-js', SLD_QCOPD_URL . '/embed/js/jquery-1.11.3.js' );
wp_enqueue_script( 'sld-embed-packery-js', SLD_QCOPD_ASSETS_TPL_URL . '/js/packery.pkgd.js' );
wp_enqueue_script( 'sld-embed-tooltipster-js', SLD_QCOPD_ASSETS_TPL_URL . '/js/tooltipster.bundle.min.js' );
wp_enqueue_script( 'sld-embed-embed-form-js', SLD_QCOPD_URL . '/embed/js/embed-form.js' );
wp_enqueue_script( 'sld-embed-magnific-js', SLD_QCOPD_ASSETS_TPL_URL . '/js/jquery.magnific-popup.min.js' );
wp_enqueue_script( 'sld-embed-directory-script-js', SLD_QCOPD_ASSETS_TPL_URL . '/js/directory-script.js' );
wp_enqueue_script( 'sld-embed-slick-script-js', SLD_QCOPD_ASSETS_TPL_URL . '/js/slick.min.js' );


$qcopd_embed_custom_js = "var ajaxurl = '".admin_url('admin-ajax.php')."';";

wp_add_inline_script( 'sld-embed-js', $qcopd_embed_custom_js);


$inclusive_tag_filter 		= sld_get_option('inclusive_tag_filter') ? sld_get_option('inclusive_tag_filter') : 'off';
$sld_enable_image_loaded 	= sld_get_option('sld_enable_image_loaded') ? sld_get_option('sld_enable_image_loaded') : 'off';
$sld_no_results_found 		= sld_get_option('sld_no_results_found') ? sld_get_option('sld_no_results_found') : esc_html('No Results Found for Your Search');

$params = array(
  'ajaxurl' 				=> admin_url('admin-ajax.php'),
  'ajax_nonce' 				=> wp_create_nonce('quantum_ajax_validation_18'),
  'rtl'						=> (sld_get_option('sld_enable_rtl')=='on'?'on':'off'),
  'main_click_upvote'		=> (sld_get_option('sld_main_click_upvote')=='on'?'on':'off'),
  'no_result_found_text' 	=> $sld_no_results_found,
  'inclusive_tag_filter' 	=> $inclusive_tag_filter,
  'sld_enable_image_loaded' => $sld_enable_image_loaded
);
wp_localize_script( 'sld-embed-directory-script-js', 'sld_ajax_object', $params );



$orderby 		= sanitize_text_field(isset($_GET['orderby'])?$_GET['orderby']:'date');
$order 			= sanitize_text_field(isset($_GET['order'])?$_GET['order']:'DESC');
$mode 			= sanitize_text_field(isset($_GET['mode'])?$_GET['mode']:'all');
$column 		= sanitize_text_field(isset($_GET['column'])?$_GET['column']:'2');
$style 			= sanitize_text_field(isset($_GET['style'])?$_GET['style']:'simple');
$search 		= sanitize_text_field(isset($_GET['search'])?$_GET['search']:'false');
$category 		= sanitize_text_field(isset($_GET['category'])?$_GET['category']:'');
$upvote 		= sanitize_text_field(isset($_GET['upvote'])?$_GET['upvote']:'off');
$tooltip 		= sanitize_text_field(isset($_GET['tooltip'])?$_GET['tooltip']:'');
$list_id 		= sanitize_text_field(isset($_GET['list_id'])?$_GET['list_id']:'');

$item_count 		= sanitize_text_field(isset($_GET['item_count'])?$_GET['item_count']:'off');
$hide_list_title 	= sanitize_text_field(isset($_GET['hide_list_title'])?$_GET['hide_list_title']:'');
$display_username 	= sanitize_text_field(isset($_GET['display_username'])?$_GET['display_username']:'');
$item_details_page 	= sanitize_text_field(isset($_GET['item_details_page'])?$_GET['item_details_page']:'');
$filterorderby 		= sanitize_text_field(isset($_GET['filterorderby'])?$_GET['filterorderby']:'');
$filterorder 		= sanitize_text_field(isset($_GET['filterorder'])?$_GET['filterorder']:'');
$paginate_items 	= sanitize_text_field(isset($_GET['paginate_items'])?$_GET['paginate_items']:'');
$actual_pagination 	= sanitize_text_field(isset($_GET['actual_pagination'])?$_GET['actual_pagination']:'');
$per_page 			= sanitize_text_field(isset($_GET['per_page'])?$_GET['per_page']:'');
$favorite 			= sanitize_text_field(isset($_GET['favorite'])?$_GET['favorite']:'disable');
$enable_left_filter = sanitize_text_field(isset($_GET['enable_left_filter'])?$_GET['enable_left_filter']:'false');
$main_click 		= sanitize_text_field(isset($_GET['main_click'])?$_GET['main_click']:'');
$video_click 		= sanitize_text_field(isset($_GET['video_click'])?$_GET['video_click']:'');
$enable_tag_filter 	= (isset($_GET['enable_tag_filter'])?sanitize_text_field($_GET['enable_tag_filter']):'');
$list_title_font_size = sanitize_text_field(isset($_GET['list_title_font_size'])?$_GET['list_title_font_size']:'');
$item_orderby 		= sanitize_text_field(isset($_GET['item_orderby'])?$_GET['item_orderby']:'title');
$list_title_line_height = sanitize_text_field(isset($_GET['list_title_line_height'])?$_GET['list_title_line_height']:'');
$title_font_size 	= sanitize_text_field(isset($_GET['title_font_size'])?$_GET['title_font_size']:'');
$subtitle_font_size = sanitize_text_field(isset($_GET['subtitle_font_size'])?$_GET['subtitle_font_size']:'');
$title_line_height 	= sanitize_text_field(isset($_GET['title_line_height'])?$_GET['title_line_height']:'');
$subtitle_line_height = sanitize_text_field(isset($_GET['subtitle_line_height'])?$_GET['subtitle_line_height']:'');
$filter_area 		= sanitize_text_field(isset($_GET['filter_area'])?$_GET['filter_area']:'');
$topspacing 		= sanitize_text_field(isset($_GET['topspacing'])?$_GET['topspacing']:'');

echo '<div class="clear">';

echo do_shortcode('[qcopd-directory mode="' . $mode . '" list_id="' . $list_id . '" style="' . $style . '" tooltip="' . $tooltip . '" column="' . $column . '" search="' . $search . '" category="' . $category . '" upvote="' . $upvote . '" item_count="'.$item_count.'" orderby="' .$orderby. '" order="' . $order . '" item_count="'.$item_count.'"  hide_list_title="'.$hide_list_title.'"  display_username="'.$display_username.'"  item_details_page="'.$item_details_page.'" filterorderby="'.$filterorderby.'" filterorder="'.$filterorder.'" paginate_items="'.$paginate_items.'" actual_pagination="'.$actual_pagination.'" per_page="'.$per_page.'" favorite="'.$favorite.'" enable_left_filter="'.$enable_left_filter.'" main_click="'.$main_click.'" video_click="'.$video_click.'" enable_tag_filter="'.$enable_tag_filter.'" list_title_font_size="'.$list_title_font_size.'" item_orderby="'.$item_orderby.'" list_title_line_height="'.$list_title_line_height.'" title_font_size="'.$title_font_size.'" subtitle_font_size="'.$subtitle_font_size.'" title_line_height="'.$title_line_height.'" subtitle_line_height="'.$subtitle_line_height.'" filter_area="'.$filter_area.'" topspacing="'.$topspacing.'" 
]');

echo '</div>'; 

?>
<?php 
wp_footer();
			// mode
			// style
			// column
			// upvote
			// search
// item_count
// hide_list_title
// display_username
// item_details_page
			// orderby
// filterorderby
			// order
// filterorder
// paginate_items
// actual_pagination
// per_page
// favorite
// enable_left_filter
// main_click
// video_click
// enable_tag_filter
			// tooltip
// list_title_font_size
// item_orderby
// list_title_line_height
// title_font_size
// subtitle_font_size
// title_line_height
// subtitle_line_height
// filter_area
// topspacing
?>




