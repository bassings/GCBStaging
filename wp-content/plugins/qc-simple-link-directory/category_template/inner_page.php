
							<div class="sld_single_item_container">
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
								  <li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat']; ?>"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])); ?></a></li>
								  <?php endif; ?>
								  <?php if(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''): ?>
								  <li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat'].'/'.$wp_query->query_vars['sldlist']; ?>"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist'])); ?></a></li>
								  <li class="sld_breadcrumb_last_child"><a href="#" disabled><?php echo $list['qcopd_item_title'] ?></a></li>
								  <?php endif; ?>
								</ul>
							  </div>
							  <div class="sld_single_content">
								<div class="sld_single_content_left">
								  <div class="feature_image">
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
									<a href="<?php echo $list['qcopd_item_link']; ?>" target="_blank" class="">
									<!-- Image, If Present -->
									<?php if( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
									<?php

										$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'large');
																		
									?>
									<img src="<?php echo $img[0]; ?>" alt="">
									<?php elseif( $iconClass != "" ) : ?>
									<span class="icon fa-icon"> <i class="fa <?php echo $iconClass; ?>"></i> </span>
									<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
									<img src="<?php echo $faviconImgUrl; ?>" alt="">
									<?php else : ?>
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">
									<?php endif; ?>
									</a>
        
        
        
									<div class="sld_resource_action">
											<?php 
										if(sld_get_option('sld_lan_visit_link')!=''){
											$visitlink = sld_get_option('sld_lan_visit_link');
										}else{
											$visitlink = __('Visit This Link','qc-opd');
										}
									?>
									<a href="<?php echo esc_url( $list['qcopd_item_link'] ); ?>" target="_blank" class="sld_single_button"><i class="fa fa-external-link" aria-hidden="true"></i>  <?php echo $visitlink; ?></a> </div>       
									
									
									
									
								  </div>
								</div>
								<div class="sld_single_content_right">
								  <h2><?php echo $list['qcopd_item_title']; ?></h2>
								  <div class="sld_rating_container">
									<?php 
									global $wpdb;
									$sql = "SELECT count(*) as cnt FROM {$wpdb->prefix}sld_rating where 1 and listid = ".$post->ID." and itemid=".$list['qcopd_timelaps'];
									$sql2 = "SELECT sum(rating) as rating FROM {$wpdb->prefix}sld_rating where 1 and listid = ".$post->ID." and itemid=".$list['qcopd_timelaps'];
									$pdata = $wpdb->get_row($sql);
									$pdata2 = $wpdb->get_row($sql2);
									
									?>
									
									
									
									<?php if((isset($_COOKIE['sld_user_rating_'.$list['qcopd_timelaps']]) && $_COOKIE['sld_user_rating_'.$list['qcopd_timelaps']]!='') || isset($_POST['rating']) ): ?>
									
									<label>User's Rating: </label>
									<?php sld_show_rating_stars($pdata2->rating/$pdata->cnt); ?>
									
									<?php else: ?>
									<div class="sld_rating_form1 sld_div">
									  <form action="<?php echo $current_url; ?>" method="POST">
										<label>User's Rating: </label>
										<input name="rating" value="0" id="rating_stars" type="hidden" />
										<input type="hidden" value="<?php echo $post->ID; ?>" name="ratinglistid" class="ratinglistid" />
										<input type="hidden" value="<?php echo $list['qcopd_timelaps']; ?>" name="ratingitemid" class="ratingitemid" />
									  </form>
									</div>
									<?php 

								
									$qcopd_rating_stars_custom_js = "
									jQuery(function() {
										jQuery('#rating_stars').codexworld_rating_widget({
											starLength: '5',
											initialValue: '".ceil($pdata2->rating/$pdata->cnt)."',
											callbackFunctionName: '',
											imageDirectory: '".SLD_QCOPD_IMG_URL."',
											inputAttr: 'postID'
										});
									});";


									wp_add_inline_script( 'qcopd-custom-script', $qcopd_rating_stars_custom_js);

					              



								endif; ?>
									
									
								  </div>
								  <?php 
										if(isset($_POST['rating'])){
											echo '<p class="sld_error_msg">Thank you for voting, we value your feedback!</p>';
										}
									?>
								 <div class="SLD_Contens"> <p><?php echo $list['qcopd_item_subtitle'] ?></p>
								  
								  <?php echo (isset($list['qcopd_description']) ? apply_filters('the_content', $list['qcopd_description']) : '' ); ?></div>
								  
								</div>
							  </div>
							</div>

<!-- Rating feature-->
						
						
					
<!-- Rating feature-->