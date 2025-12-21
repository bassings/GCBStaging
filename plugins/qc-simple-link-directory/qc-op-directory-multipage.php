<?php
defined('ABSPATH') or die("No direct script access!");

function sld_show_category($exclude){
	
	global $wp,$post;
	$current_url = home_url( $wp->request );

	$category_id = '';
	if($exclude!=''){
		$category_id = explode(',', $exclude);
	}

	$cterms = get_terms( 'sld_cat', array(
		'hide_empty' 	=> true,
		'orderby' 		=> 'name',
		'exclude' 		=> $category_id,
		'order' 		=> 'ASC' 
	));
	if(sld_get_option('sld_multi_cat_sub_cat')=='on'){

		$cterms = get_terms( 'sld_cat', array(
			'hide_empty' 	=> true,
			'orderby' 		=> 'name',
	        'parent'   		=> 0,
			'exclude' 		=> $category_id,
			'order' 		=> 'ASC' 
		));

	}


	if($exclude!=''){
		$args['post__not_in'] = explode(',', $exclude);
	}
	
	if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
		$optionPage = get_page(sld_get_option_page('sld_directory_page'));
		
		if (strpos($current_url, $optionPage->post_name) === false) {
			$current_url = home_url().'/'.$optionPage->post_name;
		}
		
	}else{
		$optionPage = get_page(sld_get_option_page('sld_directory_page'));
		$current_url = home_url().'/'.$optionPage->post_name;
		
	}

	
	
	$temp_style = (sld_get_option('sld_multipage_template')!=''?sld_get_option('sld_multipage_template'):'style-15-multipage');
	if(!empty($cterms)){
		require SLD_QCOPD_DIR_CAT.'/'.$temp_style.'.php';
	}
}

add_shortcode('qcopd-directory-multipage', 'SLD_QCOPD_DIRectory_multipage_full_shortcode');
function SLD_QCOPD_DIRectory_multipage_full_shortcode( $atts = array() ){
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
	global $wp_query, $wp;
	ob_start();
	
	extract( shortcode_atts(
		array(
			'orderby' 					=> 'menu_order',
			'order' 					=> 'ASC',
			'item_orderby' 				=> "",
			'actual_pagination' 		=> "",
			'multipage_item_details' 	=> 'true',
			'upvote' 					=> 'on',
			'exclude' 					=> "",
			'per_page' 					=> 5,
		), $atts
	));

	flush_rewrite_rules();
	
	
	$current_url = home_url( $wp->request );
	if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
		$optionPage = get_page(sld_get_option_page('sld_directory_page'));
		
		if (strpos($current_url, $optionPage->post_name) === false) {
			$current_url = $current_url.'/'.$optionPage->post_name;
		}
		
	}
	$temp_style = (sld_get_option('sld_multipage_template')!=''?sld_get_option('sld_multipage_template'):'style-15-multipage');

	$customCss = sld_get_option( 'sld_custom_style' );

	$tag = (sld_get_option('sld_multi_tag')=='on'?' enable_tag_filter="true"':' enable_tag_filter="false"');
	$favorite = (sld_get_option('sld_enable_bookmark')=='on'?' favorite="enable"':' favorite="disable"');


	
	if( trim($customCss) != "" ) :

		wp_add_inline_style( 'qcopd-custom-css', trim($customCss) );
		// qcopd-custom-css
	
	?>

	<?php endif;
	
	
    if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){

		$catObj 			= get_term_by('slug', $wp_query->query_vars['sldcat'], 'pd_cat');
		$sbdlistObj 		= get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'pd' );
		$slug_name 			= ( isset($catObj->name) && !empty( $catObj->name ) ) ? $catObj->name : str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat']));
		$post_title_slug 	= ( isset($sbdlistObj->post_title) && !empty( $sbdlistObj->post_title ) ) ? $sbdlistObj->post_title : str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist']));
		
		$slditem = isset($wp_query->query_vars['slditem']) ? $wp_query->query_vars['slditem'] : '';
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$citem = '';
			foreach($lists as $k=>$list){
				
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					
					$citem = $k;
				?>
					<div class="sld_single_item_container">
						
						
						<div class="sld_single_content">
						<div class="feature-top-setion">
								<div class="feature-image">
								<?php
									$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

									$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";

									$faviconImgUrl = "";
									$faviconFetchable = false;
									$filteredUrl = "";
									$item_url = $list['qcopd_item_link'];
									$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";

									if( $showFavicon == 1 )
									{
										$filteredUrl = qcsld_remove_http( $item_url );

										if( $item_url != '' )
										{

											$faviconImgUrl = 'https://www.google.com/s2/favicons?domain=' . $filteredUrl;
										}

										if( $directImgLink != '' )
										{

											$faviconImgUrl = trim($directImgLink);
										}

										if( $faviconImgUrl != '' ){
											$faviconImgUrl = apply_filters('sld_generate_img_youtube', $faviconImgUrl, $item_url, $directImgLink );
										}

										$faviconFetchable = true;

										if( $item_url == '' && $directImgLink == '' ){
											$faviconFetchable = false;
										}
									}
									?>
								<!-- Image, If Present -->
									<?php if( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>


										<?php
											$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
											
										?>
										<img src="<?php echo $img[0]; ?>" alt="">


									<?php elseif( $iconClass != "" ) : ?>

										<span class="icon fa-icon">
											<i class="fa <?php echo $iconClass; ?>"></i>
										</span>

									<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>

										<img src="<?php echo $faviconImgUrl; ?>" alt="">

									<?php else: ?>

										<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

									<?php endif; ?>
								</div>
							
								<div class="feature-title-subtitle" >

								<div class="sld_single_breadcrumb">
							<ul class="sld_breadcrumb">
							
							<?php 
							$bcurl = get_page_link();
							if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
								$optionPage = get_page(sld_get_option_page('sld_directory_page'));
								
								if (strpos($bcurl, $optionPage->post_name) === false) {
									$bcurl = $bcurl.$optionPage->post_name.'/';
								}
							}
							?>
							
								<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
							  <?php if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''): ?>
								<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat']; ?>"><?php echo $slug_name; ?></a></li>
							 <?php endif; ?>
							 
							 <?php if(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''): ?>
								<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat'].'/'.$wp_query->query_vars['sldlist']; ?>"><?php echo $post_title_slug; ?></a></li>
								<li class="sld_breadcrumb_last_child"><a href="#" disabled><?php echo $list['qcopd_item_title'] ?></a></li>
							<?php endif; ?>
							</ul>
							
							

						</div>

								<h2><?php echo $list['qcopd_item_title']; ?> 
							
								<div class="upvote-section upvote-section-style-single">
								<span data-post-id="<?php echo $post->ID; ?>" data-unique="<?php echo $post->ID.'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="sld-upvote-btn-single upvote-on">
									<i class="fa fa-thumbs-up"></i>
								</span>
								<span class="upvote-count count">
									<?php
									  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
										echo (int)$list['qcopd_upvote_count'];
									  }
									?>
								</span>
							</div>
								</h2>
								<p><?php echo $list['qcopd_item_subtitle'] ?></p>



								<?php 

echo (isset($list['qcopd_tags']) && !empty( $list['qcopd_tags'] ) ? '<p><b>'.esc_html('Tags').': '. esc_attr($list['qcopd_tags']).'</b></p>' :'' );

if(sld_get_option('sld_lan_visit_link')!=''){
	$visitlink = sld_get_option('sld_lan_visit_link');
}else{
	$visitlink = __('Visit This Link','qc-opd');
}
?>
<a href="<?php echo esc_url($list['qcopd_item_link']); ?>" target="_blank" class="sld_single_button"><?php echo $visitlink; ?></a>



								</div>
								</div>
								<div class="feature-full-deacription" >
								<?php echo isset($list['qcopd_description']) ? apply_filters( 'the_content', $list['qcopd_description'] ) : ''; ?>
								</div>
								<div class="sld_resource_action">
									
									<nav class="sld-nav-socials">
										<h5 class="sld-social-title"><?php echo esc_html__('Share', 'qc-opd'); ?></h5>
										<ul>
											<li class="nav-socials__item">
												
												<a href="https://twitter.com/share?url=<?php echo $current_url; ?>/&amp;text=<?php echo urlencode($list['qcopd_item_title']); ?>" title="Twitter" target="_blank">
													<i class="fa fa-twitter"></i>
													
												</a>
											</li>
											<li class="nav-socials__item">
												
												<a href="https://facebook.com/sharer.php?u=<?php echo $current_url; ?>/&amp;t=<?php echo urlencode($list['qcopd_item_title']); ?>+<?php echo $current_url; ?>/" title="Facebook" target="_blank">
													<i class="fa fa-facebook-f"></i>
												</a>
											</li>
											<li class="nav-socials__item">
												
												<?php
													$param = '';
													if(isset($list['qcopd_item_img']) && $list['qcopd_item_img']!=''){
														$imgurlm = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
														if(isset($imgurlm[0]) && $imgurlm[0]!=''){
															$param = '&amp;media='.$imgurlm[0];
														}
													}
												?>
												
												<a href="https://pinterest.com/pin/create/button/?url=<?php echo $current_url; ?>/<?php echo $param; ?>&amp;description=<?php echo urlencode($list['qcopd_item_title']); ?>" title="Pinterest" target="_blank">
													<i class="fa fa-pinterest-p"></i>
												</a>
											</li>
										</ul>
									</nav>									
									
								</div>
								
								<?php
									if(sld_get_option('sld_show_alexa_rank')=='on'):
									$parse_url = parse_url($list['qcopd_item_link']);
									$new_url = @$parse_url['scheme'].'://'.@$parse_url['host'];
									$rankdata = sld_alexaRank($new_url);
									if(!empty($rankdata)):
								?>
								
								<div class="sld_alexa_rank">
									<span>Alexa Global Rank - <?php echo @$rankdata['globalRank'][0]; ?></span> <br> <span> <?php echo 'Alexa Country '.@$rankdata['CountryRank']['@attributes']['NAME']; ?> - <?php echo @$rankdata['CountryRank']['@attributes']['RANK']; ?></span>
								</div>
								<?php 
									endif;
									endif;
								?>

							<?php do_action('qcopdr_single_item_review', $list, $post->ID); ?>
						</div>
						
					</div>
				<?php	
				}
			}
			?>
			<?php if(count($lists)>3): ?>
				<link rel="stylesheet" type="text/css" href="<?php echo SLD_OCOPD_TPL_URL . "/style-multipage/style.css"; ?>" />
				<link rel="stylesheet" type="text/css" href="<?php echo SLD_OCOPD_TPL_URL . "/style-multipage/responsive.css"; ?>" />
				<div class="sld_single_related_content">
					<?php
						$it=0;
						$relatedArray = array();
						foreach($lists as $f=>$list){
							if($f>$citem && $it<3){
								$relatedArray[] = $list;
								$it++;
							}
						}
						if(count($relatedArray) < 3 and count($lists) > 3){
							for($rr=0;count($relatedArray)<3;$rr++){
								
								$relatedArray[] = $lists[$rr];
							}
						}
						if(sld_get_option('sld_lan_related_items')!=''){
							$relateditems = sld_get_option('sld_lan_related_items');
						}else{
							$relateditems = __('Related Items','qc-opd');
						}
						
					?>
					<h2><?php echo $relateditems; ?></h2>	
					<div class="qcld_sld_category_list">
					
						<ul class="sld_single_related tooltip_tpl12-tpl sld-list <?php echo $temp_style; ?>" id="jp-list-<?php echo $post->ID; ?>">
							<?php $count = 1; ?>
							<?php foreach( $relatedArray as $list ) : ?>
							<?php
								$canContentClass = "subtitle-present";

								if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
								{
									$canContentClass = "subtitle-absent";
								}
							?>
							
							<li id="item-<?php echo $post->ID ."-". $count; ?>" class="sld-26">
								<?php
									$item_url = $list['qcopd_item_link'];
									$masked_url = $list['qcopd_item_link'];
									$mask_url = 'off';
									if( $mask_url == 'on' ){
										$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
									}
								?>
								<div class="column-grid3">
									<div class="sld-main-content-area bg-color-0<?php echo (($count%5)+1); ?>">
										<div class="sld-main-panel">
											<div class="panel-title">
												<h3><?php
													echo trim($list['qcopd_item_title']);
												?></h3>
											</div>
											<div class="feature-image">
												<?php
													$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

													$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";

													$faviconImgUrl = "";
													$faviconFetchable = false;
													$filteredUrl = "";

													$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";

													if( $showFavicon == 1 )
													{
														$filteredUrl = qcsld_remove_http( $item_url );

														if( $item_url != '' )
														{

															$faviconImgUrl = 'https://www.google.com/s2/favicons?domain=' . $filteredUrl;
														}

														if( $directImgLink != '' )
														{

															$faviconImgUrl = trim($directImgLink);
														}

														if( $faviconImgUrl != '' ){
															$faviconImgUrl = apply_filters('sld_generate_img_youtube', $faviconImgUrl, $item_url, $directImgLink );
														}

														$faviconFetchable = true;

														if( $item_url == '' && $directImgLink == '' ){
															$faviconFetchable = false;
														}
													}
													?>
												<!-- Image, If Present -->
													<?php if(isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>


														<?php
																$img = wp_get_attachment_image_src($list['qcopd_item_img']);
															?>
															<img src="<?php echo $img[0]; ?>" alt="">


													<?php elseif( $iconClass != "" ) : ?>

													<span class="icon fa-icon">
														<i class="fa <?php echo $iconClass; ?>"></i>
													</span>

													<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>


														<img src="<?php echo $faviconImgUrl; ?>" alt="">


													<?php else : ?>

														<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

													<?php endif; ?>
											</div>
											
										</div>
										<div class="sld-hover-content">
											<div class="style-14-upvote-section">
												

												<!-- upvote section -->
												<div class="upvote-section upvote-section-style-14 upvote upvote-icon">
													<span data-post-id="<?php echo $post->ID; ?>" data-unique="<?php echo $post->ID.'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
														<i class="fa fa-thumbs-up"></i>
													</span>
													<span class="upvote-count count">
														<?php
														  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
															echo (int)$list['qcopd_upvote_count'];
														  }
														?>
													</span>
												</div>
												<!-- /upvote section -->

											
											
											</div>
											<p><?php echo trim($list['qcopd_item_subtitle']); ?></p>
											<?php 
												if(sld_get_option('sld_lan_visit_page')!=''){
													$visit_page = sld_get_option('sld_lan_visit_page');
												}else{
													$visit_page = __('Visit Page','qc-opd');
												}
											?>
											<?php 
											$bcurl = get_page_link();
											if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
												$optionPage = get_page(sld_get_option_page('sld_directory_page'));
												
												if (strpos($bcurl, $optionPage->post_name) === false) {
													$bcurl = $bcurl.$optionPage->post_name.'/';
												}
											}
											?>
											<a href="<?php echo $bcurl.$wp_query->query_vars['sldcat'].'/'.$wp_query->query_vars['sldlist'].'/'.urlencode(str_replace(' ','-',strtolower(trim($list['qcopd_item_title'])))).'/'.$list['qcopd_timelaps']; ?>" ><?php echo $visit_page; ?></a>

										</div>
									</div>
								</div>
							</li>

							<?php $count++; endforeach; ?>

						</ul>
					</div>					
				</div>
			<?php endif; ?>
			<?php
		}
		
		
	}
	elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){
		
		if ( $post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' ) )
			$id = $post->ID;
		else
			$id = 0;
		
		if($id>0){
			?>
				<div class="sld_single_breadcrumb">
					<ul class="sld_breadcrumb">
					
					<?php 
							$bcurl = get_page_link();
							if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
								$optionPage = get_page(sld_get_option_page('sld_directory_page'));
								
								if (strpos($bcurl, $optionPage->post_name) === false) {
									$bcurl = $bcurl.$optionPage->post_name.'/';
								}
							}

							$catObj 			= get_term_by('slug', $wp_query->query_vars['sldcat'], 'pd_cat');
							$sbdlistObj 		= get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'pd' );
							$slug_name 			= ( isset($catObj->name) && !empty( $catObj->name ) ) ? $catObj->name : str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat']));
							$post_title_slug 	= ( isset($sbdlistObj->post_title) && !empty( $sbdlistObj->post_title ) ) ? $sbdlistObj->post_title : str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist']));
							?>
					
					
						<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
					  <?php if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''): ?>
						<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat']; ?>"><?php echo $slug_name; ?></a></li>
					 <?php endif; ?>
					 
					 <?php if(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''): ?>
						<li class="sld_breadcrumb_last_child"><a href="#"><?php echo $post_title_slug; ?></a></li>
					<?php endif; ?>
					</ul>
				</div>
			<?php
			
			echo do_shortcode('[qcopd-directory mode="one" actual_pagination="'.$actual_pagination.'" list_id="'.$id.'" style="'.$temp_style.'" column="3" upvote="on" search="true" item_count="on" orderby="'.$orderby.'" filterorderby="date" order="'.$order.'" multipage_item_details="'.$multipage_item_details.'" filterorder="ASC" paginate_items="false" favorite="disable" tooltip="true" list_title_font_size="" item_orderby="'.$item_orderby.'" upvote="'.$upvote.'" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing="" per_page="'.$per_page.'" multipage="true" '.$tag.' '.$favorite.']');
		}
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		?>
		<div class="sld_single_breadcrumb">
			<ul class="sld_breadcrumb">
			<?php 
				$bcurl = get_page_link();
				if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
					$optionPage = get_page(sld_get_option_page('sld_directory_page'));
					
					if (strpos($bcurl, $optionPage->post_name) === false) {
						$bcurl = $bcurl.$optionPage->post_name.'/';
					}
				}
				?>
				<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
			  <?php
			  	if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''):
			  		$cat_slug = $wp_query->query_vars['sldcat'];
					$term_info = get_term_by('slug', $cat_slug, 'sld_cat');
					if( !is_wp_error($term_info) && !empty($term_info) ){
						$term_title = $term_info->name;
					}else{
						$term_title = str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])); 
					}

			  ?>
				<li class="sld_breadcrumb_last_child"><a href="#"><?php echo $term_title; ?></a></li>
			 <?php endif; ?>
			 
			 
			</ul>
		</div>
		<?php
		echo do_shortcode('[qcopd-directory category="'.$wp_query->query_vars['sldcat'].'" style="'.$temp_style.'" column="3" upvote="on" search="true" item_count="on" orderby="'.$orderby.'" filterorderby="date" order="'.$order.'" filterorder="ASC" actual_pagination="'.$actual_pagination.'" multipage_item_details="'.$multipage_item_details.'" paginate_items="false" upvote="'.$upvote.'" favorite="disable" tooltip="false" list_title_font_size="" item_orderby="'.$item_orderby.'" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing=""  per_page="'.$per_page.'" multipage="true" '.$tag.' '.$favorite.']');

	}else{
		sld_show_category($exclude);
	}

		//var_dump( $wp_query->query_vars );
		//wp_die();
	
    $content = ob_get_clean();
    return $content;
}


add_filter('pre_get_document_title', 'sld_wp_title_for_multipage', 20);
function sld_wp_title_for_multipage( $title )
{
	global $wp_query, $wp;
	
	if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$slditem = isset($wp_query->query_vars['slditem']) ? $wp_query->query_vars['slditem'] : '';
			foreach($lists as $list){
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					$title = trim($list['qcopd_item_title']).' | '.get_bloginfo( 'name' ); 
				}
			}
		}
	}
	elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){

		$sbdlistObj 		= get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'pd' );
		$post_title_slug 	= ( isset($sbdlistObj->post_title) && !empty( $sbdlistObj->post_title ) ) ? $sbdlistObj->post_title : str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist']));
		
		$title 				= $post_title_slug .' | '.get_bloginfo( 'name' );
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		$cat_slug = $wp_query->query_vars['sldcat'];
		$term_info = get_term_by('slug', $cat_slug, 'sld_cat');
		if( !is_wp_error($term_info) && !empty($term_info) ){
			$title = $term_info->name;
		}else{
			$title = str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])).' | '.get_bloginfo( 'name' ); 
		}
	}

   /*  
    $title['page'] = '2'; // optional
    $title['tagline'] = 'Home Of Genesis Themes'; // optional
    $title['site'] = 'DevelopersQ'; //optional
    */
	
    return $title; 
}


add_action('wp_head', 'sldmyCallbackToAddMeta', 1);
function sldmyCallbackToAddMeta(){
	
	global $wp_query, $wp;
	
	if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$slditem = isset( $wp_query->query_vars['slditem'] ) ? $wp_query->query_vars['slditem'] : '';
			foreach($lists as $list){
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					
					$title = trim($list['qcopd_item_title']).' | '.get_bloginfo( 'name' ); 
					$description = trim($list['qcopd_item_subtitle']);

					if(isset($list['qcopd_item_img']) && $list['qcopd_item_img']!=''){
						if (strpos($list['qcopd_item_img'], 'http') === FALSE){
							$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
							$itemImg = $img[0];
						}else{
							$itemImg = $list['qcopd_item_img'];
						}
					}elseif(isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != ""){
						$itemImg = $list['qcopd_item_img_link'];
					}else{
						$itemImg = SLD_QCOPD_IMG_URL.'/list-image-placeholder.png';
					}
					
					echo '<link rel="canonical" href="'.home_url( $wp->request ).'" />'."\n";
					echo '<meta property="og:title" content="'.$list['qcopd_item_title'].'" />'."\n";
					echo '<meta property="og:description" content="'.$description.'" />'."\n";
					echo '<meta property="og:image" content="'.$itemImg.'" />'."\n";
					echo "<meta name='description' content='".$description."'>\n";

				?>
				 <script type='application/ld+json'> 
					{
					  "@context": "http://www.schema.org",
					  "@type": "LocalBusiness",
					  "name": "<?php echo $list['qcopd_item_title']; ?>",
					  "url": "<?php echo $list['qcopd_item_link']; ?>",
					  "logo": "<?php echo (isset($img[0]) ? $img[0] : ''); ?>",
					  "image": "<?php echo (isset($img[0]) ? $img[0] : ''); ?>",
					  "description": "<?php echo $list['qcopd_item_subtitle']; ?>",
					  "address": {},
					  "priceRange": {},
					  "telephone":{}
					}
				</script>
				
				<?php
				}
			}
		}
		
	}elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){
		
		$post = get_term_by('slug', $wp_query->query_vars['sldcat'], 'sld_cat');
		if(!empty($post)){
			echo "<meta name='description' content='".str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist']))." - ".$post->description."'>\n";
		}
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		
		$post = get_term_by('slug', $wp_query->query_vars['sldcat'], 'sld_cat');
		if(!empty($post)){
			echo "<meta name='description' content='".$post->description."'>\n";
		}
		
	}
  
}




function sld_custom_rewrite_tag() {
  add_rewrite_tag('%sldcat%', '([^&]+)');
  add_rewrite_tag('%sldlist%', '([^&]+)');
  add_rewrite_tag('%slditemname%', '([^&]+)');
  add_rewrite_tag('%slditem%', '([^&]+)');
}
add_action('init', 'sld_custom_rewrite_tag', 10, 0);



function sld_custom_rewrite_rule4() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);

			$post_name = isset( $optionPage->post_name ) ? $optionPage->post_name : '';

			if(!empty($post_name))
				add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]&slditemname=$matches[3]&slditem=$matches[4]','top');

		}
	}

}
add_action('init', 'sld_custom_rewrite_rule4', 10, 0);

function sld_custom_rewrite_rule3() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);

			$post_name = isset( $optionPage->post_name ) ? $optionPage->post_name : '';

			if(!empty($post_name))
				add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]&slditemname=$matches[3]','top');

		}
	}

}
add_action('init', 'sld_custom_rewrite_rule3', 10, 0);

function sld_custom_rewrite_rule() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);

			$post_name = isset( $optionPage->post_name ) ? $optionPage->post_name : '';

			if(!empty($post_name))
				add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]','top');

		}
		
	}

}
add_action('init', 'sld_custom_rewrite_rule', 10, 0);


function sld_custom_rewrite_rule1() {
	
	if(sld_get_option('sld_enable_multipage')=='on'){
	
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);

			$post_name = isset( $optionPage->post_name ) ? $optionPage->post_name : '';

			if(!empty($post_name))
				add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]','top');
			
			
		}
	}

}
add_action('init', 'sld_custom_rewrite_rule1', 10, 0);

function sld_184163_disable_canonical_front_page( $redirect ) {
    if ( is_page() && $front_page = get_option( 'page_on_front' ) ) {
        if ( is_page( $front_page ) ){
			$post = get_post( $front_page );
			if ( has_shortcode( $post->post_content, 'qcopd-directory-multipage' ) ) {
				$redirect = false;
			}
		}
            //
    }

    return $redirect;
}
add_filter( 'redirect_canonical', 'sld_184163_disable_canonical_front_page' );

/**
 * Detect shortcodes and update the plugin options
 * @param post_id of an updated post
 */
function sld_multipage_get_pages_with_shortcodes($post_ID){
	
	$post = get_post( $post_ID );
	if ( has_shortcode( $post->post_content, 'qcopd-directory-multipage' ) ) {
		update_option( 'sld_directory_page', $post->ID );
	}
}
add_action( 'wp_insert_post', 'sld_multipage_get_pages_with_shortcodes', 1);

function sld_get_option_page($page, $param = false) {
	return get_option($page, $param);
}


add_filter('get_canonical_url', 'sld_get_canonical_url', 10, 2);
function sld_get_canonical_url( $canonical_url, $post ){
	if ( has_shortcode( $post->post_content, 'qcopd-directory-multipage' ) ) {
		$canonical_url = '';
	}
	return $canonical_url;
}

add_filter( 'wpseo_canonical', 'sld_remove_wpseo_canonical',  10, 1 );
function sld_remove_wpseo_canonical($canonical){
	global $post;
	if ( has_shortcode( $post->post_content, 'qcopd-directory-multipage' ) ) {
		$canonical = '';
	}
	return $canonical;
}