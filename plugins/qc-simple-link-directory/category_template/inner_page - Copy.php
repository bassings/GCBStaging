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

						</div>
						
						<div class="sld_single_content">
						
								<div class="feature-image" style="float:right">
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

									<?php else : ?>

										<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

									<?php endif; ?>
								</div>
							
							
								<h2><?php echo $list['qcopd_item_title']; ?></h2>
								<div class="sld_rating_container">
									<?php 
									global $wpdb;
									$sql = "SELECT count(*) as cnt FROM {$wpdb->prefix}sld_rating where 1 and listid = ".$post->ID." and itemid=".$list['qcopd_timelaps'];
									$sql2 = "SELECT sum(rating) as rating FROM {$wpdb->prefix}sld_rating where 1 and listid = ".$post->ID." and itemid=".$list['qcopd_timelaps'];
									$pdata = $wpdb->get_row($sql);
									$pdata2 = $wpdb->get_row($sql2);
									
									?>
									<label>User's Rating: </label>
									<?php sld_show_rating_stars($pdata2->rating/$pdata->cnt); ?>
								
								</div>
								<p><?php echo $list['qcopd_item_subtitle'] ?></p>
								
								<?php echo (isset($list['qcopd_description'])?apply_filters('the_content', $list['qcopd_description']):''); ?>
								
								<div class="sld_resource_action">
									<?php 
										if(sld_get_option('sld_lan_visit_link')!=''){
											$visitlink = sld_get_option('sld_lan_visit_link');
										}else{
											$visitlink = __('Visit This Link','qc-opd');
										}
									?>
									<a href="<?php echo $list['qcopd_item_link']; ?>" target="_blank" class="sld_single_button"><?php echo $visitlink; ?></a>
									
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

							
						</div>
						
					</div>
					
					
					<!-- Rating feature-->
					<div class="sld_rating_form1 sld_div">
						<?php 
							if(isset($_POST['rating'])){
								echo '<p class="sld_error_msg">Thank you.</p>';
							}
						?>
						<form action="<?php echo $current_url; ?>" method="POST">
						<label>Your Rating: </label>
						<?php if((isset($_COOKIE['sld_user_rating']) && $_COOKIE['sld_user_rating']!='') || isset($_POST['rating']) ): 
						
							$rating = @$_COOKIE['sld_user_rating'];
							if(isset($_POST['rating'])){
								$rating = $_POST['rating'];
							}
						
						?>
						<?php sld_show_rating_stars($rating/1); ?>
						<?php else: ?>
						<input name="rating" value="0" id="rating_stars" type="hidden" />
						<input type="hidden" value="<?php echo $post->ID; ?>" name="ratinglistid" class="ratinglistid" />
						<input type="hidden" value="<?php echo $list['qcopd_timelaps']; ?>" name="ratingitemid" class="ratingitemid" />
						<?php endif; ?>
						
						</form>
					</div>
					<!-- Rating feature-->
					<script type="text/javascript">
						jQuery(function() {
							jQuery("#rating_stars").codexworld_rating_widget({
								starLength: '5',
								initialValue: '',
								callbackFunctionName: '',
								imageDirectory: '<?php echo SLD_QCOPD_IMG_URL; ?>',
								inputAttr: 'postID'
							});
						});
					</script>
					<!-- Rating List -->