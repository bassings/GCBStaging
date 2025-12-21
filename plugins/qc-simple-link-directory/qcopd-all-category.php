<?php
/**
 * Created by QuantunCloud.
 * Date: 9/14/2017
 * Time: 3:16 PM
 */

defined('ABSPATH') or die("No direct script access!");

add_shortcode('sld-tab', 'SLD_QCOPD_DIRectory_all_category');
function SLD_QCOPD_DIRectory_all_category($atts = array()){

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
	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'orderby' => 'menu_order',
			'order' => 'ASC',
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
			'per_page' => 5,
			'category_orderby'=>'date',
			'category_order'=>'ASC',
			'category_remove'=>'',
			'list_title_font_size' => '' ,
			'list_title_line_height' => '' ,
			'actual_pagination'	=>	'false',
			'title_font_size' => '',
			'subtitle_font_size' => '',
			'title_line_height' => '',
			'subtitle_line_height' => '',
			'filter_area' => 'normal',
			'topspacing' => 0,
			'enable_tag_filter'=>'false',
			'main_click'=> '',
			'video_click' => 'popup',
			'hide_list_title' => 'false',
			'subcategories_as_dropdown' => 'false'

		), $atts
	));


    //Category remove array
    if($category_remove != ''){
	    $category_remove = explode(',',$category_remove);
	    $categoryremove = $category_remove;
    }else{
	    $categoryremove = array();
    }

	$cterms = get_terms( 'sld_cat', array(
		'hide_empty' => true,
		'orderby' => $category_orderby,
		'order' => $category_order
	) );

	ob_start();

    if(!empty($cterms)){
?>
		
        <div class="qcld_sld_tab_main"><!--start qcld_sld_tab_main-->
           
                <?php
                	echo  '<div class="qcld_sld_tab sld-overflow-visible">';
                if( $subcategories_as_dropdown == 'true' ){
                	$taxonomyName = "sld_cat";
					//This gets top layer terms only.  This is done by setting parent to 0.  
					$parent_terms = get_terms( array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false, 'taxonomy'=>'sld_cat' ) );  

					echo '<div class="sld-tab-subcategory-container"><ul class="sld-tab-subcategory">';
					foreach ( $parent_terms as $pterm ) {
					    //Get the Child terms
					    $terms = get_terms( array( 'child_of' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false, 'taxonomy'=>'sld_cat' ) );
						$image_id = get_term_meta ( $pterm -> term_id, 'category-image-id', true ); 
					    if( !empty($terms) ){
					    	$li_has_subcat_classes = ' sld-has-subcat ';
					    }else{
					    	$li_has_subcat_classes = ' ';
					    }
					    echo '<li class="sld-subcat-parent-li '.$li_has_subcat_classes.'"><a href="'.get_term_link( $pterm ).'" data-cterm="sld-cat'. $pterm->term_id.'" class="qcld_sld_tablinks">' . $pterm->name . '';
					    echo '<span class="cat_img_top">';
						if($image_id){
							echo wp_get_attachment_image ( $image_id, 'thumbnail' );
						}
						echo '</span></a>';
					    if( !empty($terms) ){
					    	echo '<span class="sld-caret"><i class="fa fa-caret-down"></i></span>';
					    	echo '<ul class="sld-tab-subcategory-inner">';
						    foreach ( $terms as $term ) {
						    	$child_term_image_id = get_term_meta ( $term->term_id, 'category-image-id', true ); 

						        echo '<li><a href="'.get_term_link( $term ).'" data-cterm="sld-cat'. $term->term_id.'" class="qcld_sld_tablinks">' . $term->name . ''; 
						        echo '<span class="cat_img_top">';
								if($child_term_image_id){
									echo wp_get_attachment_image ( $child_term_image_id, 'thumbnail' );
								}
								echo '</span></a></li>';  
						    }
						    echo '</ul>';
					    }
					}
					echo '</li></ul><div class="clearfix"></div></div></div>';
                }else{
                	echo  '<div class="qcld_sld_tab">';
	                $ci = 0;
	                foreach ($cterms as $cterm){
	                    if(!in_array($cterm->term_id,$categoryremove)){
			
							$image_id 		= get_term_meta ( $cterm->term_id, 'category-image-id', true );
							$tab_color 		= get_term_meta ( $cterm->term_id, 'sld_cat_tab_color', true );
							$tab_text_color = get_term_meta ( $cterm->term_id, 'sld_cat_tab_text_color', true );

							$text_color = !empty($tab_text_color) ? 'color:'.$tab_text_color : '';

							if( !empty( $tab_color ) ) {
								$class_name = 'sld_cat_tab_color_'.$cterm->term_id;
								$sld_cat_tab_color = '
								body .qcld_sld_tab .qcld_sld_tablinks.'.$class_name.' {
    								background-color: '.$tab_color.';
    								'.$text_color.'
    							}
								body .qcld_sld_tab .qcld_sld_tablinks.'.$class_name.'.qcld_sld_active:hover,
								body .qcld_sld_tab .qcld_sld_tablinks.'.$class_name.':hover {
    								background-color: '.$tab_color.';
    								'.$text_color.'
    							}
								body .qcld_sld_tab .qcld_sld_tablinks.'.$class_name.'::after {
									border-top: 44px solid '.$tab_color.';
									
								}
								body .qcld_sld_tab .qcld_sld_tablinks.'.$class_name.'::before {
									border-bottom: 44px solid '.$tab_color.';
								}';

								wp_add_inline_style( 'sld-tab-css', $sld_cat_tab_color );
							}
	                        ?>
	                            <button style="<?php echo (!$image_id?'padding-left:22px!important;':''); ?>" class="qcld_sld_tablinks sld_cat_tab_color_<?php echo $cterm->term_id; ?> <?php echo ($ci==0?'qcld_sld_active':''); ?>" data-cterm="sld-cat<?php echo $cterm->term_id; ?>" ><?php echo $cterm->name; ?>
								<span class="cat_img_top">
								<?php if($image_id) echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?></span>
								</button>
	                        <?php
	                        $ci++;
	                    }
	                }
	                echo '</div>';
                }
                ?>
            

	        <?php
	        $ci = 0;
	        foreach ($cterms as $cterm){
		        if(!in_array($cterm->term_id,$categoryremove)){
					//if($ci==1)continue;
			        ?>

                    <div id="sld-cat<?php echo $cterm->term_id; ?>" class="qcld_sld_tabcontent" <?php echo ($ci==0?'style="display:block"':''); ?>>
				        <?php
                            $shortcodeText = '[qcopd-directory category="'.$cterm->slug.'" search="'.$search.'" upvote="'.$upvote.'" item_count="'.$item_count.'" top_area="'.$top_area.'" mask_url="'.$mask_url.'" hide_list_title="'.$hide_list_title.'" tooltip="'.$tooltip.'" paginate_items="'.$paginate_items.'" per_page="'.$per_page.'" style="'.$style.'" column="'.$column.'" orderby="'.$orderby.'" order="'.$order.'" list_title_font_size="'.$list_title_font_size.'" item_orderby="'.$item_orderby.'" item_order="'.$item_order.'" list_title_line_height="'.$list_title_line_height.'" title_font_size="'.$title_font_size.'" subtitle_font_size="'.$subtitle_font_size.'" title_line_height="'.$title_line_height.'" subtitle_line_height="'.$subtitle_line_height.'" filter_area="'.$filter_area.'" topspacing="'.$topspacing.'" enable_tag_filter="'.$enable_tag_filter.'" video_click="'.$video_click.'" main_click="'.$main_click.'" actual_pagination="'.$actual_pagination.'" cattabid="'.$ci.'"]';
				        echo do_shortcode($shortcodeText);
				        ?>
                    </div>

			        <?php
			        $ci++;
		        }
	        }
	        ?>



        </div><!--end qcld_sld_tab_main-->

		
		<?php if(sld_get_option('sld_enable_filtering_left')=='on'): ?>
			<script>
				jQuery(document).ready(function ($) {

					var fullwidth = window.innerWidth;
					if (fullwidth < 479) {
						$('.filter-carousel').not('.slick-initialized').slick({


							infinite: false,
							speed: 500,
							slidesToShow: 1,


						});
					} else {
						$('.filter-carousel').not('.slick-initialized').slick({

							dots: false,
							infinite: false,
							speed: 500,
							slidesToShow: 1,
							centerMode: false,
							variableWidth: true,
							slidesToScroll: 3,

						});
					}

				});
				
			</script>
		<?php endif; ?>
			<script>
				var per_page = <?php echo $per_page; ?>;
			</script>
		
<?php
    }

	$content = ob_get_clean();
	return $content;

}
add_shortcode('sld-multipage-category', 'qcopd_multipage_all_category');
function qcopd_multipage_all_category($atts = array()){
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

	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'exclude' => '',
		), $atts
	));

	sld_show_category($exclude);
	$content = ob_get_clean();
	return $content;
}
add_shortcode('qcopd-directory-random', 'qcopd_random_directory');
function qcopd_random_directory($atts = array()){

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

	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'limit_item' => 5,
			'category' => '',
			'subtitle' 		=> '',
		), $atts
	));

	$limit_item = isset($limit_item) ? $limit_item : 5;
	$category = isset($category) ? $category : '';
	$subtitle = isset($subtitle) ? $subtitle : '';

	ob_start();
	echo '<div class="sld_widget_style"><h2>'.esc_html('Random Links').'</h2>'.qcopd_get_random_links_wi($limit_item, $category, $subtitle ).'</div>';
	$content = ob_get_clean();
	return $content;
}
add_shortcode('qcopd-directory-latest', 'qcopd_latest_directory');
function qcopd_latest_directory($atts = array()){
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

	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'limit_item' 	=> 5,
			'category' 		=> '',
			'subtitle' 		=> '',
		), $atts
	));

	$limit_item = isset($limit_item) ? $limit_item : 5;
	$category = isset($category) ? $category : '';
	$subtitle = isset($subtitle) ? $subtitle : '';

	ob_start();
	echo '<div class="sld_widget_style"><h2>'.esc_html('Latest Links').'</h2>'.qcopd_get_latest_links_wi($limit_item, $category, $subtitle).'</div>';
	$content = ob_get_clean();
	return $content;
}
add_shortcode('qcopd-directory-widget-tab-style', 'qcopd_widget_all_directory');
function qcopd_widget_all_directory($atts = array()){
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
	
	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'limit_item' => 6,
		), $atts
	));

	$limit_item = isset($limit_item) ? $limit_item : 5;

	ob_start();
	sld_widget_tab_style($limit_item);
	$content = ob_get_clean();
	return $content;
}



add_shortcode('qcopd-directory-popular', 'qcopd_popular_directory');
function qcopd_popular_directory($atts = array()){
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
	
	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'limit_item' 	=> 5,
			'category' 		=> '',
			'subtitle' 		=> '',
		), $atts
	));

	$limit_item = isset($limit_item) ? $limit_item : 5;
	$category 	= isset($category) ? $category : '';
	$subtitle 	= isset($subtitle) ? $subtitle : '';

	ob_start();
	echo '<div class="sld_widget_style"><h2>'.esc_html('Popular Links').'</h2>'.qcopd_get_most_popular_links_wi($limit_item, $category, $subtitle).'</div>';
	$content = ob_get_clean();
	return $content;
}



if( !function_exists('qcld_item_count_by_function') ){
function qcld_item_count_by_function($itemID){
	global $wpdb;

	if(!empty($itemID)){

		$item_count_disp = count( get_post_meta( $itemID, 'qcopd_list_item01' ) ) ? count( get_post_meta( $itemID, 'qcopd_list_item01' ) ) : 0 ;

		$qcopd_unpublished = 0;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id =%d AND meta_key = 'qcopd_list_item01' order by `meta_id` ASC", $itemID ) );
		
		if(!empty($results)){
			foreach($results as $result){
				$unserialize = maybe_unserialize($result->meta_value);

				if(isset($unserialize['qcopd_unpublished']) || (isset($unserialize['qcopd_unpublished']) && $unserialize['qcopd_unpublished']==1))
			   		$qcopd_unpublished = (is_array( $unserialize['qcopd_unpublished'] ) ? count($unserialize['qcopd_unpublished']) : 0) + $qcopd_unpublished;
			}
		}

		return $item_count_disp - $qcopd_unpublished;

	}

	return '';


}
}