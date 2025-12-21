
		<?php wp_enqueue_style('sld-css-style-15-multi-cat', SLD_OCOPD_TPL_URL . "/style-15-multipage/style.css" ); 

        $custom_css = '.qc-sld-single-item-11 .qc-sld-main {
			border-bottom:1px solid #eee
		}';

        wp_add_inline_style( 'sld-css-style-15-multi-cat', $custom_css );

		$qc_grid_wrap = "";
		$qc_grid = "";
		$qc_grid_item = "";
		$opd_list_holder = "";
		$opt_column_03 = "opt-column-03";
		if(sld_get_option('sld_multi_cat_sub_cat')=='on'){
			$qc_grid_wrap = "sld_multi_cat_sub_cat_wrap";
			$qc_grid = "qc-grid";
			$qc_grid_item = "qc-grid-item";
			$opt_column_03 = "";
			$opd_list_holder = "opd-list-holder";
		}
		?>
		<div class="qc-feature-container <?php echo $qc_grid_wrap; ?> qc-sld-single-item-11">
			<ul class="<?php echo $temp_style; ?> <?php echo $qc_grid; ?>" id="<?php echo $opd_list_holder; ?>">
			<?php
				$ci = 0;
				foreach ($cterms as $cterm){
					?>
						<li class="<?php echo $qc_grid_item; ?> qcopd-list-column <?php echo $opt_column_03; ?>">
							<div class="qc-sld-main">
							<a href="<?php echo $current_url; ?>/<?php echo $cterm->slug; ?>">
							  <div class="qc-feature-media image">

							  <?php
							  
								$args = array(
									'numberposts' => -1,
									'post_type'   => 'sld',
								);
								$taxArray = array(
									array(
										'taxonomy' => 'sld_cat',
										'field'    => 'id',
										'terms'    => $cterm->term_id,
									),
								);
								if(sld_get_option('sld_multi_cat_sub_cat')=='on'){
									$taxArray = array(
										array(
											'taxonomy' => 'sld_cat',
	                    					'parent'   => 0,
											'field'    => 'id',
											'terms'    => $cterm->term_id,
										),
									);

								}
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
							  
								<?php $image_id = get_term_meta($cterm->term_id, 'category-image-id', true );
										if($image_id){
										?>
														
											<?php echo wp_get_attachment_image ( $image_id, 'full' ); ?>
										
										<?php } ?>
									<div class="sld_total_lists"><?php echo $total_post; ?> List<?php echo ($total_post>1?'s':'') ?></div>
									<div class="sld_total_items"><?php echo $total_items; ?> Item<?php echo ($total_items>1?'s':'') ?></div>
							  </div>
							  <div class="qc-sld-content">
								<h4 class="sld-title"><?php echo $cterm->name; ?></h4>
								
								<p class="sub-title"><?php echo $cterm->description; ?></p>
							  </div>
							  
						  	</a>
							  	
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
							  <div class="clear"></div>
							  </div>
						  
					  </li>
					<?php
					$ci++;					
				}
			?>
			</ul>
		</div>