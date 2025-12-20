	<?php 

		wp_enqueue_style('sld-css-style-multipage-cat', SLD_OCOPD_TPL_URL . "/style-multipage/style.css" );
		wp_enqueue_style('sld-css-style-multipage-res-cat', SLD_OCOPD_TPL_URL . "/style-multipage/responsive.css" );

		$qc_grid_wrap = "";
		$qc_grid = "";
		$qc_grid_item = "";
		$opd_list_holder = "";
		$opt_column_03 = "sld-style10-column3";
		if(sld_get_option('sld_multi_cat_sub_cat')=='on'){
			$qc_grid_wrap = "sld_multi_cat_sub_cat_wrap";
			$qc_grid = "qc-grid";
			$qc_grid_item = "qc-grid-item";
			$opt_column_03 = "sld-style10-column3";
			$opd_list_holder = "opd-list-holder";
		}
		?>
		<div class="qcld_sld_category_list <?php echo $qc_grid_wrap; ?>">
			<ul class="<?php echo $temp_style; ?>">
			<?php
				$ci = 0;
				foreach ($cterms as $cterm){
					?>
						<li>
						
							<div class="column-grid3">
								<div class="sld-main-content-area bg-color-0<?php echo (($ci%5)+1); ?>">
									<div class="sld-main-panel">
										<div class="panel-title">
											<h3><?php echo $cterm->name; ?></h3>
										</div>
										<?php $image_id = get_term_meta ( $cterm -> term_id, 'category-image-id', true );
										if($image_id){
										?>
										<div class="feature-image">					
											<?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
										</div>
										<?php } ?>
									</div>
									<div class="sld-hover-content">
										<p><?php echo $cterm->description; ?></p>
										<?php 
											if(sld_get_option('sld_lan_visit_page')!=''){
												$visit_page = sld_get_option('sld_lan_visit_page');
											}else{
												$visit_page = __('Visit Page','qc-opd');
											}
										?>

							  	
										<?php 
										//sld_multi_cat_sub_cat
										if(sld_get_option('sld_multi_cat_sub_cat')=='on'){
									    $args = array(
								            'taxonomy'      => 'sld_cat',
								            'child_of'      => 0,
								            'parent'        => $cterm->term_id,
								            'hide_empty'    => 1,
								            'order'         => 'ASC',
								            'orderby'       => 'menu_order',
								            'hierarchical'  => 0,
								        );

									    $sub_cats = get_categories( $args );
									    if(!empty($sub_cats)){
									    	$sld_multi_cat_sub_cat_text = sld_get_option('sld_multi_cat_sub_cat_text') ? sld_get_option('sld_multi_cat_sub_cat_text') : 'Sub Categories';
									    	?>
									  	<div class="sub-cat-content">
											
											<div class="qcld-sub-accordion">
												<div class="qcld-sub-top">
													<div class="qcld-sub-text"><p><?php echo esc_html( $sld_multi_cat_sub_cat_text ); ?></p></div>
													<img src="https://assets-global.website-files.com/63e49089cb05f507aba64457/63e49089cb05f5c614a6446a_icon_plus.svg"
														alt="">
													<input type="checkbox">
												</div>
												<div class="qcld-sub-bottom">
													<div class="qcld-sub-text">

												    	<?php 
							        					foreach($sub_cats as $sub_category){
														?>
														<a href="<?php echo esc_url($current_url); ?>/<?php echo $sub_category->slug; ?>"><?php echo $sub_category->name; ?></a>
														<?php 
													    } ?>
													</div>
												</div>
											</div>
								
									  	</div>

										<?php 
											} 
										} 
									?>
										<a href="<?php echo $current_url; ?>/<?php echo $cterm->slug; ?>" ><?php echo $visit_page; ?></a>
									</div>
							<?php
							  
								$args = array(
									'numberposts' => -1,
									'post_type'   => 'sld',
								);
								$taxArray = array(
									array(
										'taxonomy' => 'sld_cat',
										'field'    => 'id',
										'terms'    => $cterm -> term_id,
									),
								);
								$args = array_merge($args, array( 'tax_query' => $taxArray ));								
								$listItems = get_posts( $args );
								$total_post = 0;
								if(!empty($listItems))
									$total_post = count($listItems);
								$total_items = 0;
								foreach ($listItems as $item){
									
									$total_items += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
									
								}
								
							  ?>	
								<div class="sld_total_lists"><?php echo $total_post; ?> List<?php echo ($total_post>1?'s':'') ?></div>
								<div class="sld_total_items"><?php echo $total_items; ?> Item<?php echo ($total_items>1?'s':'') ?></div>
								</div>
							</div>
						
						</li>
					<?php
					$ci++;					
				}
			?>
			</ul>
		</div>