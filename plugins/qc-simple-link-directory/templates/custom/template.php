<?php wp_enqueue_style('sld-css-simple', SLD_OCOPD_TPL_URL . "/$template_code/template.css" ); ?>

<?php if( $tooltip == true ) : ?>
<?php wp_enqueue_style('sld-css-simple-tooltip', SLD_OCOPD_TPL_URL . "/$template_code/html5tooltips.css" ); ?>
<?php wp_enqueue_style('sld-css-simple-tooltip-animate', SLD_OCOPD_TPL_URL . "/$template_code/html5tooltips.animation.css" ); ?>
<?php wp_enqueue_script('sld-simple-html5tooltips', SLD_OCOPD_TPL_URL . "/$template_code/html5tooltips.js"); ?>
<?php endif; ?>

<?php if( $paginate_items == true ) : ?>
	<?php
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-jpages', SLD_OCOPD_TPL_URL . "/$template_code/jPages.min.js", array('jquery'));

		wp_enqueue_style('sld-jpages-css', SLD_OCOPD_TPL_URL . "/$template_code/jPages.css" );
		wp_enqueue_style('sld-animate-css', SLD_OCOPD_TPL_URL . "/$template_code/animate.css" );
	?>
<?php endif; ?>

<?php

$bookmark_list = $mode;

// The Loop
if ( $list_query->have_posts() )
{

	//Getting Settings Values
	if($search=='true'){
		$searchSettings = 'on';
	}else{
		if($search=='false'){
			$searchSettings = 'off';
		}else{
			$searchSettings = sld_get_option( 'sld_enable_search' );
		}
	}


	$itemAddSettings = sld_get_option( 'sld_enable_add_new_item' );
	$itemAddLink = sld_get_option( 'sld_add_item_link' );
	$enableTopArea = sld_get_option( 'sld_enable_top_part' );
	$enableFiltering = sld_get_option( 'sld_enable_filtering' );

	//Check if border should be set
	$borderClass = "";

	if( $searchSettings == 'on' || $itemAddSettings == 'on' )
	{
		$borderClass = "sld-border-bottom";
	}

	//Hook - Before Search Template
	do_action( 'qcsld_before_search_tpl', $shortcodeAtts);

	//If the top area is not disabled (both serch and add item)
	if( $enableTopArea == 'on' && $top_area != 'off' ) :

		//Load Search Template
		if($bookmark_list != "favorite" ){
			require ( dirname(__FILE__) . "/search-template.php" );
		}

	endif;

	if($enable_tag_filter=='true')
		sld_show_tag_filter($category,$shortcodeAtts);

	//Hook - Before Filter Template
	do_action( 'qcsld_before_filter_tpl', $shortcodeAtts);

	//Enable Filtering
	if( $enableFiltering == 'on' && $mode == 'all' && $enable_left_filter!='true') :

		//Load Search Template
		require ( dirname(__FILE__) . "/filter-template.php" );

	endif;
	
	if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
		
		if($style=="simple" && $infinityscroll==1){
	
			$args = array(
				'numberposts' => $itemperpage,
				'post_type'   => 'sld',
			);
			
		}else{
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'sld',
				'orderby'     => $filterorderby,
				'order'       => $filterorder,
			);
			
		}

		if ( $category != "" ) {
			$taxArray = array(
				array(
					'taxonomy' => 'sld_cat',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);

			$args = array_merge( $args, array( 'tax_query' => $taxArray ) );

		}

		$listItems = get_posts( $args );

		/* for exclude item */
		$listItems = apply_filters('sld_exclude_item_by_attr', $listItems, $exclude );
		?>
      
        <div class="filter-area-main sld_filter_mobile_view">
            <div class="filter-area" style="width: 100%;">

                <div class="filter-carousel">
                    <div class="item">
					<?php 
						$item_count_disp_all = '';
						foreach ($listItems as $item){
							if( $item_count == "on" ){
								$item_count_disp_all .= qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
							}
						}
					?>
					<a href="#" class="filter-btn" data-filter="all">
						<?php 
							if(sld_get_option('sld_lan_show_all')!=''){
								echo esc_html(sld_get_option('sld_lan_show_all'));
							}else{
								_e('Show All', 'qc-opd'); 
							}
						?>
						<?php
							if($item_count == 'on'){
								echo '<span class="opd-item-count-fil">('.esc_html($item_count_disp_all).')</span>';
							}
						?>
					</a>
                    </div>

					<?php foreach ( $listItems as $item ) :
						$config = get_post_meta( $item->ID, 'qcopd_list_conf' );
						$filter_background_color = '';
						$filter_text_color = '';
						if ( isset( $config[0]['filter_background_color'] ) and $config[0]['filter_background_color'] != '' ) {
							$filter_background_color = $config[0]['filter_background_color'];
						}
						if ( isset( $config[0]['filter_text_color'] ) and $config[0]['filter_text_color'] != '' ) {
							$filter_text_color = $config[0]['filter_text_color'];
						}
						?>

						<?php
						$item_count_disp = "";

						if ( $item_count == "on" ) {
							// $item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
							$item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
						}
						?>

                        <div class="item">
                            <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>"
                               style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
								<?php echo esc_html($item->post_title); ?>
								<?php
								if ( $item_count == 'on' ) {
									echo '<span class="opd-item-count-fil">(' . esc_html($item_count_disp) . ')</span>';
								}
								?>
                            </a>
                        </div>

					<?php endforeach; ?>

                </div>
				<?php if($cattabid==''): 




				$qcopd_cat_custom_js = "jQuery(document).ready(function($){

                    var fullwidth = window.innerWidth;
                    if(fullwidth < 479){
                        $('.filter-carousel').not('.slick-initialized').slick({


                            infinite: false,
                            speed: 500,
                            slidesToShow: 1,


                        });
                    }else{
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

                });";


				wp_add_inline_script( 'qcopd-custom-script', $qcopd_cat_custom_js);

				?>
				<?php endif; ?>

            </div>
        </div>
		<?php
	}
	//If RTL is Enabled
	$rtlSettings = sld_get_option( 'sld_enable_rtl' );
	$rtlClass = "";

	if( $rtlSettings == 'on' )
	{
	   $rtlClass = "direction-rtl";
	}


	//Hook - Before Main List
	do_action( 'qcsld_before_main_list', $shortcodeAtts);

	//Directory Wrap or Container

	echo '<div class="qcld-main-container-style-custom"><div class="qcopd-list-wrapper qc-full-wrapper">';
	?>

	<?php
    if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
		
	    if($style=="simple" && $infinityscroll==1){
	
			$args = array(
				'numberposts' => $itemperpage,
				'post_type'   => 'sld',
			);
			
		}else{
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'sld',
				'orderby'     => $filterorderby,
				'order'       => $filterorder,
			);
			
		}

	    if ( $category != "" ) {
		    $taxArray = array(
			    array(
				    'taxonomy' => 'sld_cat',
				    'field'    => 'slug',
				    'terms'    => $category,
			    ),
		    );

		    $args = array_merge( $args, array( 'tax_query' => $taxArray ) );

	    }

	    $listItems = get_posts( $args );

	    /* for exclude item */
		$listItems = apply_filters('sld_exclude_item_by_attr', $listItems, $exclude );

	    $filterType = sld_get_option( 'sld_filter_ptype' ); //normal, carousel

			// If FILTER TYPE is NORMAL

		    ?>

            <div class="filter-area left-side-filter">

                <a href="#" class="filter-btn" data-filter="all">
				    <?php 
						if(sld_get_option('sld_lan_show_all')!=''){
							echo esc_html(sld_get_option('sld_lan_show_all'));
						}else{
							_e('Show All', 'qc-opd'); 
						}
					?>
                </a>

			    <?php foreach ( $listItems as $item ) :
				    $config = get_post_meta( $item->ID, 'qcopd_list_conf' );
				    $filter_background_color = '';
				    $filter_text_color = '';
				    if(isset($config[0]['filter_background_color']) and $config[0]['filter_background_color']!=''){
					    $filter_background_color = $config[0]['filter_background_color'];
				    }
				    if(isset($config[0]['filter_text_color']) and $config[0]['filter_text_color']!=''){
					    $filter_text_color = $config[0]['filter_text_color'];
				    }
                    ?>

				    <?php
				    $item_count_disp = "";

				    if ( $item_count == "on" ) {
					    // $item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
					    $item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
				    }
				    ?>

                    <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
					    <?php echo esc_html($item->post_title); ?>
					    <?php
					    if ( $item_count == 'on' ) {
						    echo '<span class="opd-item-count-fil">(' . esc_html($item_count_disp) . ')</span>';
					    }
					    ?>
                    </a>

			    <?php endforeach; ?>

            </div>

	    <?php
    }
	?>
<?php
	echo '<div id="opd-list-holder" class="qc-grid qcopd-list-hoder '.$rtlClass.'">';
	global $wpdb;
	if(is_user_logged_in() and $sldfavorite=='on'){
		$b_title = sld_get_option('sld_bookmark_title');
		
		$userid = get_current_user_id();
		
		$user_meta_data = get_user_meta($userid, 'sld_bookmark_user_meta');




        // sld-css-style-1
        $bookmark_custom_css = "#bookmark_list.simple ul{
				border-top-color: ".(sld_get_option('sld_bookmark_list_holder_color')!=''?sld_get_option('sld_bookmark_list_holder_color'):'').";
			}

			#bookmark_list.simple ul li a{
				background-color: ".(sld_get_option('sld_bookmark_item_background_color')!=''?sld_get_option('sld_bookmark_item_background_color'):'').";
				color: ".(sld_get_option('sld_bookmark_item_text_color')!=''?sld_get_option('sld_bookmark_item_text_color'):'').";


				
				border-bottom-color: ".(sld_get_option('sld_bookmark_item_border_color')!=''?sld_get_option('sld_bookmark_item_border_color'):'')." !important;
				
			}

			#bookmark_list.simple ul li a:hover{
				background-color: ".(sld_get_option('sld_bookmark_item_background_color_hover')!=''?sld_get_option('sld_bookmark_item_background_color_hover'):'').";
				color: ".(sld_get_option('sld_bookmark_item_text_color_hover')!=''?sld_get_option('sld_bookmark_item_text_color_hover'):'').";

				
				border-bottom-color: ".(sld_get_option('sld_bookmark_item_border_color_hover')!=''?sld_get_option('sld_bookmark_item_border_color_hover'):'')." !important;
				
			}

			#bookmark_list.simple .upvote-section .upvote-btn, #bookmark_list.simple .upvote-section .upvote-count {
				color: ".(sld_get_option('sld_bookmark_item_text_color')!=''?sld_get_option('sld_bookmark_item_text_color'):'').";
			}

			#bookmark_list.simple .upvote-section .upvote-btn:hover, #bookmark_list.simple li:hover .upvote-btn, #bookmark_list.simple li:hover .upvote-count{
				color: ".(sld_get_option('sld_bookmark_item_text_color_hover')!=''?sld_get_option('sld_bookmark_item_text_color_hover'):'').";
			}";

        wp_add_inline_style( 'sld-css-simple', $bookmark_custom_css );
		
?>
		<div id="bookmark_list" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " " . $style;?>">
			<div class="qcopd-single-list">
				
				<h2><?php echo esc_html($b_title!=''?$b_title:'Quick Links'); ?></h2>
				<ul id="sld_bookmark_ul">
<?php
			$lists = array();
			
			if(!empty($user_meta_data)){
				foreach($user_meta_data[0] as $postid=>$metaids){
					
					if(!empty($metaids)){
						foreach($metaids as $metaid){
							
							$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id =%d AND meta_key = 'qcopd_list_item01'", $postid ) );
							if(!empty($results)){
								foreach ($results as $key => $value) {
									$unserialized = maybe_unserialize($value->meta_value);
									if (trim($unserialized['qcopd_timelaps']) == trim($metaid)) {
										$customdata = $unserialized;
										$customdata['postid'] = $postid;
										$lists[] = $customdata;
									}
								}
							}
							
						}
					}
				}
			}

			/* for exclude item */
			$lists = apply_filters('sld_exclude_item_by_attr', $lists, $exclude );
			
			usort($lists, "sld_custom_sort_by_tpl_title");
			$b = 1;			
			foreach($lists as $list){
				$tooltip_content = '';
				
				if( $tooltip === 'true' ){
					$tooltip_content = ' data-tooltip="'.esc_html($list['qcopd_item_subtitle']).'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
				}
?>
					<li id="sld_bookmark_li_<?php echo $b; ?>" <?php echo esc_html($tooltip_content); ?>>

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
						?>
						<!-- List Anchor -->
						<?php 
							$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
							$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;
						?>
						<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_url($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> >

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
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<span class="list-img">
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo esc_url($img[0]); ?>" alt="">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="list-img">
									<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="list-img favicon-loaded">
									<img src="<?php echo esc_url($faviconImgUrl); ?>" alt="">
								</span>
							<?php else : ?>
								<span class="list-img">
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<?php
								echo esc_html($list['qcopd_item_title']);
							?>
							

						</a>
							
							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-simple">
								
								<?php 
								$bookmark = 1;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv) && get_current_user_id()!=0){
										$bookmark = 1;
									}
								}
								?>

								<span data-post-id="<?php echo esc_attr($list['postid']); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" data-li-id="sld_bookmark_li_<?php echo $b; ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa fa-times-circle" aria-hidden="true"></i>
								</span>
								
							</div>
							
							<!-- /upvote section -->
							
							
							
							
							

					</li>
<?php			
				$b++;
				}
			
		
?>
</ul></div></div>
<?php
	
}
	
if($bookmark_list == "favorite" ){
	return false;
}
	
	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$optionPage = get_page(sld_get_option_page('sld_directory_page'));
	$directoryPage = home_url().'/'.$optionPage->post_name;
	
	$listId = 1;
	global $wp;
	$current_url = home_url( $wp->request );
	while ( $list_query->have_posts() )
	{
		
		$list_query->the_post();

		//$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
		
		// if(sld_get_option('sld_new_expire_after')!=''){
		// 	sld_new_expired(get_the_ID());
		// }
		
		global $post;
		$terms = get_the_terms( get_the_ID(), 'sld_cat' );
		$slug = $post->post_name;
		
		$lists = array();
		
		sld_remove_duplicate_master(get_the_ID());
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id =%d AND meta_key = 'qcopd_list_item01' order by `meta_id` ASC", get_the_ID() ) );
		if(!empty($results)){
			foreach($results as $result){
				$unserialize = maybe_unserialize($result->meta_value);
				if(!isset($unserialize['qcopd_unpublished']) or $unserialize['qcopd_unpublished']==0)
					$lists[] = $unserialize;
			}
		}
		$lists = sldmodifyupvotes(get_the_ID(), $lists);
		
		/* for exclude item */
		$lists = apply_filters('sld_exclude_item_by_attr', $lists, $exclude );

		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		$addvertise = get_post_meta( get_the_ID(), 'sld_add_block', true );

		$addvertiseContent = isset($addvertise['add_block_text']) ? $addvertise['add_block_text'] : '';
		if( !is_array($conf) || empty($conf) ){
			$conf = array();
		}
		//adding extra variable in config
		@$conf['item_title_font_size'] = $title_font_size;
		@$conf['item_subtitle_font_size'] = $subtitle_font_size;
		@$conf['item_title_line_height'] = $title_line_height;
		@$conf['item_subtitle_line_height'] = $subtitle_line_height;
		
		if($style=='custom'){
			$style='simple';
		}
		
		?>
		
		<?php if( $style == "simple" ): 

		$qcopd_list_custom_css = "
			#list-item-".$listId .'-'. get_the_ID().".simple ul{
				border-top-color: ".@$conf['list_border_color'].";
			}

			#list-item-".$listId .'-'. get_the_ID().".simple ul li a{
				background-color: ".@$conf['list_bg_color'].";
				color: ".@$conf['list_txt_color'].";";

				if(@$conf['item_title_font_size']!=''):
				$qcopd_list_custom_css .="font-size:".@$conf['item_title_font_size'].";";
				endif;

				if(@$conf['item_title_line_height']!=''):
				$qcopd_list_custom_css .="line-height:".@$conf['item_title_line_height'].";";
				endif;

				if( @$conf['item_bdr_color'] != "" ) : 
				$qcopd_list_custom_css .="border-bottom-color: ".@$conf['item_bdr_color']." !important;";
				endif;
			$qcopd_list_custom_css .="}

			#list-item-".$listId .'-'. get_the_ID().".simple ul li a:hover{
				background-color: ".@$conf['list_bg_color_hov'].";
				color: ".@$conf['list_txt_color_hov'].";
				
			}

			#list-item-".$listId .'-'. get_the_ID().".simple ul li:hover{";
				if( @$conf['item_bdr_color_hov'] != "" ) :
				$qcopd_list_custom_css .="border-bottom-color: ".@$conf['item_bdr_color_hov']." !important;";
				endif;
			$qcopd_list_custom_css .="}

			#list-item-".$listId .'-'. get_the_ID().".simple .upvote-section .upvote-btn, #list-item-".$listId .'-'. get_the_ID().".simple .upvote-section .upvote-count {
				color: ".@$conf['list_txt_color'].";
			}

			#list-item-".$listId .'-'. get_the_ID().".simple .upvote-section .upvote-btn:hover, #list-item-".$listId .'-'. get_the_ID().".simple li:hover .upvote-btn, #list-item-".$listId .'-'. get_the_ID().".simple li:hover .upvote-count{
				color: ".@$conf['list_txt_color_hov'].";
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default{
				border-top: 5px solid #f86960;
				border-top-color: ".@$conf['list_border_color'].";
				box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}";

			if( isset($conf['list_bg_featured_color']) && !empty(@$conf['list_bg_featured_color'])){
				$qcopd_list_custom_css .=".qcopd-list-wrapper .simple .featured-section {
				   background:".@$conf['list_bg_featured_color'].";
				    width: 100%;
				    height: 100%;
				    right: 0px;
				    border-width: 0px 0px 0px 0px;
				    border-color: transparent transparent transparent transparent;
				}

				.simple ul li a {
					position: relative;
				    z-index: 100;
				}";

			}

        wp_add_inline_style( 'sld-css-simple', $qcopd_list_custom_css );


			?>


		<?php if( $paginate_items === 'true' ) : 
			$qcopd_list_custom_js = '
				jQuery(document).ready(function($){
					$("#jp-holder-'.get_the_ID(). (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
		    			containerID : "jp-list-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
		    			perPage : '.$per_page.',
		  			});
					
					
					$(".sld_search_filter").keyup(function(){

						setTimeout(function(){
							$("#jp-holder-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
								containerID : "jp-list-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
								perPage : '.$per_page.',
							});
							$(".qc-grid").packery({
							  itemSelector: ".qc-grid-item",
							  gutter: 10
							});
						}, 900);

					})
					
					

				});';
			
			wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_custom_js);


		?>

		<?php endif; ?>

		<!-- Individual List Item -->
		<div id="list-item-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " " . $style;?> <?php echo "opd-list-id-" . get_the_ID(); ?>">
			<div class="qcopd-single-list">
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
						$item_count_disp = qcld_item_count_by_function(get_the_ID()) ? qcld_item_count_by_function(get_the_ID()) : count( get_post_meta( get_the_ID(), 'qcopd_list_item01' ) ) ;
					}
				?>
				
				<?php if($hide_list_title != 'true'){ ?>
					<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
						<?php 
						
						if($multipage=='true'):
							echo '<a href="'.esc_url($current_url.'/'.get_post(get_the_ID())->post_name).'">';
						elseif(isset($conf['title_link']) && $conf['title_link']!=''):
							echo '<a href="'.esc_url($conf['title_link']).'" '.(isset($conf['title_link_new_tab'])&&$conf['title_link_new_tab']==1?'target="_blank"':'').'>';
						endif;
						?>
						<?php echo esc_html(get_the_title()); ?>
						<?php
							if($item_count == 'on'){
								$item_count_disp = isset($lists) ? count($lists) : $item_count_disp;
								echo '<span class="opd-item-count">('.esc_html($item_count_disp).')</span>';
							}
						?>
						<?php 
						if($multipage=='true' or (isset($conf['title_link']) && $conf['title_link']!='') ):
							echo '</a>';
						endif;
						?>
					</h2>
				<?php 
					}
				?>
				<ul id="jp-list-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>">
					<?php

						if( $item_orderby == 'upvotes' )
						{
    						usort($lists, "sld_custom_sort_by_tpl_upvotes");
						}
						
						if( $item_orderby == 'clicks' )
						{
    						usort($lists, "custom_sort_by_tpl_click");
						}

						if( $item_orderby == 'title' )
						{
    						usort($lists, "sld_custom_sort_by_tpl_title");
						}

						if( $item_orderby == 'timestamp' )
						{
							usort($lists, "sld_custom_sort_by_tpl_timestamp");
						}

						if( $item_orderby == 'random' )
						{
							shuffle( $lists );
						}											
						
						if(sld_get_option('sld_featured_item_top')=='on'){
							$lists = sld_featured_at_top($lists);
						}
						
						
						
						$count = 1;

						foreach( $lists as $list ) :
						
						$tooltip_content = '';

						if( $tooltip === 'true' ){
							
							$tooltip_content = ' data-tooltip="'.esc_attr($list['qcopd_item_subtitle']).'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}
						
						
						
						//print_r($list);exit;
					?>
					<li id="item-<?php echo get_the_ID() ."-". $count; ?>" <?php echo ($tooltip_content); ?>>

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
							$popContent = '';
							$videoPopContent = '';
							if($main_click=='popup'){
								$masked_url = '#';
								$popContent = 'class="open-mpf-sld-more2 sld_load_more2" data-post-id="'.esc_attr(get_the_ID()).'" data-item-title="'.esc_attr(trim($list['qcopd_item_title'])).'" data-item-link="'.esc_url($list['qcopd_item_link']).'" data-mfp-src="#sldinfo-'.get_the_ID() ."-". $count."-". esc_attr($list['qcopd_timelaps']) .'"';
							}

							if( $video_click=='popup' ){
								$videoPopContent = ' class="open-mpf-sld-video sld_load_video" ';
							}
						?>
						
						
						
						<!-- List Anchor -->
						<?php if(sld_is_youtube_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									Loading...
								</div>
							</div>
							<a <?php echo $videoPopContent; ?> href="<?php echo esc_url($list['qcopd_item_link']); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_attr($list['qcopd_item_link']).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>"  data-videourl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" >
							
						<?php elseif(sld_is_vimeo_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									Loading...
								</div>
							</div>
							<a <?php echo $videoPopContent; ?> href="<?php echo esc_url($list['qcopd_item_link']); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_url($list['qcopd_item_link']).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" >
							
						<?php else: ?>

							<?php 
								$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
								$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;
							?>
							
							<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-itemurl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" <?php echo $popContent; ?> data-subtitle="<?php echo esc_attr($list['qcopd_item_subtitle']); ?>">
							
						<?php endif; ?>
						
						

							<?php
								$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? esc_attr($list['qcopd_fa_icon']) : "";

								$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";

								$faviconImgUrl = "";
								$faviconFetchable = false;
								$filteredUrl = "";

								$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? esc_url($list['qcopd_item_img_link']) : "";

								if( $showFavicon == 1 )
								{
									$filteredUrl = qcsld_remove_http( $item_url );

									if( $item_url != '' )
									{

										$faviconImgUrl = esc_url('https://www.google.com/s2/favicons?domain=' . $filteredUrl);
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
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<span class="list-img">
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo esc_url($img[0]); ?>" alt="">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="list-img">
									<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="list-img favicon-loaded">
									<img src="<?php echo esc_url($faviconImgUrl); ?>" alt="">
								</span>
							<?php else : ?>
								<span class="list-img">
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<?php
								echo esc_html($list['qcopd_item_title']);
							?>
							
						</a>

						<?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section upvote-section-simple">
								<span data-post-id="<?php echo esc_attr(get_the_ID()); ?>" data-unique="<?php echo get_the_ID().'_'.($list['qcopd_timelaps']!=''?$list['qcopd_timelaps']:$count); ?>" data-item-title="<?php echo esc_attr(trim($list['qcopd_item_title'])); ?>" data-item-link="<?php echo esc_url($list['qcopd_item_link']); ?>" class="upvote-btn upvote-on">
									<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
								</span>
								<span class="upvote-count">
									<?php
									  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
									  	echo (int)$list['qcopd_upvote_count'];
									  }
									?>
								</span>
							</div>
							<!-- /upvote section -->

						<?php endif; ?>
						
							
							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-simple">
								
								<?php if(isset($list['qcopd_description']) && $list['qcopd_description']!=''): ?>
								<span class="open-mpf-sld-more sld_load_more" data-post-id="<?php echo esc_attr(get_the_ID()); ?>" data-item-title="<?php echo esc_attr(trim($list['qcopd_item_title'])); ?>" data-item-link="<?php echo esc_url($list['qcopd_item_link']); ?>" style="cursor:pointer" data-mfp-src="#sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>">
									<i class="fa fa-info-circle"></i>
								</span>
								<?php endif; ?>

								
								<?php if($sldfavorite=='on'): ?>
								<?php 
								$bookmark = 0;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv)){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo esc_attr(get_the_ID()); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
								</span>
								<?php endif; ?>
								
								<?php 
								$category = 'default';
								if(!empty($terms)){
									$category = $terms[0]->slug;
								}
								
								$newurl = $directoryPage.'/'.$category.'/'.$slug.'/'.urlencode(str_replace(' ','-',strtolower($list['qcopd_item_title']))).'/'.$list['qcopd_timelaps'];
								if($item_details_page=='on' && !empty($optionPage) && sld_get_option('sld_enable_multipage')=='on'):
								?>
								
								
								<span><a class="sld_internal_link" href="<?php echo esc_url($newurl); ?>" title="Go to link details page"><i class="fa fa-external-link-square" aria-hidden="true"></i></a></span>
								<?php endif; ?>
								
							</div>
							

								<div id="sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>" class="white-popup mfp-hide">
									<div class="sld_more_text">
										Loading...
									</div>
								</div>
															

							
							<?php if(isset($list['qcopd_new']) and $list['qcopd_new']==1):?>
							<!-- new icon section -->
							<div class="new-icon-section">
								<span> <?php
								$lan_text_new = sld_get_option('dashboard_lan_text_new') ? sld_get_option('dashboard_lan_text_new') : 'new'; 
								_e( $lan_text_new, 'qc-opd' ); 
								?>
								</span>
							</div>
							<!-- /new icon section -->
							<?php endif; ?>
							
							
							<?php if(isset($list['qcopd_featured']) and $list['qcopd_featured']==1):?>
							<!-- featured section -->
							<div class="featured-section">
								<i class="fa fa-bolt"></i>
							</div>
							<!-- /featured section -->
							<?php endif; ?>

					</li>

					<?php $count++; endforeach; ?>

				</ul>

				<?php if( $paginate_items === 'true' ) : ?>

				<!-- navigation panel -->
				<div id="jp-holder-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>" class="sldp-holder"></div>

				<?php endif; ?>

			</div>

		</div>
		<!-- /Individual List Item -->

		<?php if( $addvertiseContent != '' ) : ?>
		<!-- Add Block -->
		<div class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; ?> <?php echo "opd-list-id-" . get_the_ID(); ?>" id="item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block">
			<div class="advertise-block tpl-default">
				<?php echo apply_filters('the_content',$addvertiseContent); ?>

			</div>
		</div>
		<!-- /Add Block -->
		<?php endif; ?>

		<?php endif; ?>

		<?php

		$listId++;
	}
	
	echo '
		</div>
		<div class="sld-clearfix"></div>';
	
	echo '</div></div>';
?>
<div class="sld_spinner" style="display:none;">
  <div class="sld_bounce1"></div>
  <div class="sld_bounce2"></div>
  <div class="sld_bounce3"></div>
</div>
<?php

	//Hook - After Main List
	do_action( 'qcsld_after_main_list', $shortcodeAtts);

}


$qcopd_list_filter_custom_js = "var login_url_sld = '".sld_get_option('sld_bookmark_user_login_url')."';
var template = '".$style."';";
$qcopd_list_filter_custom_js .= "var bookmark = { ";
	
	if ( is_user_logged_in() ) {
	
	$qcopd_list_filter_custom_js .= "is_user_logged_in:true,";
	
	} else {
	
	$qcopd_list_filter_custom_js .= "is_user_logged_in:false,";
	
	}
	
	$qcopd_list_filter_custom_js .= "userid: ".get_current_user_id()." };

	jQuery(document).ready(function($){

		$( '.filter-btn[data-filter=all]' ).on( 'click', function() {

	  		//Masonary Grid

		    $('.qc-grid').packery({
		      itemSelector: '.qc-grid-item',
		      gutter: 10
		    });
		});

		$( '.filter-btn[data-filter=all]' ).trigger( 'click' );
	
	});
	
	jQuery(window).on('load',function(){
		setTimeout(function(){
			jQuery('.qc-grid').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10,";
			  if( $rtlSettings == 'on' ):
			  $qcopd_list_filter_custom_js .= "originLeft: false"
			  endif;
			$qcopd_list_filter_custom_js .= "});
		},500)
		
	})";



if($style=='simple' and $infinityscroll==1){

$qcopd_list_filter_custom_js .= "jQuery(document).ready(function($) {
	
			var page = 1;
			var totalpage =".(isset($total_page_count)?$total_page_count:10).";
			var running = false;
			$(window).scroll(function() {
			   if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
				   
				if(running){
					return;
				}
				
				if(page<totalpage){
					
					page++;
					running = true;
					$grid = $('.qc-grid');
					$('.sld_spinner').show();
					
					$.post(
						ajaxurl,
						{
							action : 'qcld_sld_loadmore',
							page: page,
							itemperpage: '".$per_page."',
							column: '".$column."',
							upvote: '".$upvote."',
							itemcount: '".$item_count."',
						},
						function(data){
							
							var $data = $(data); 
							$grid.append($data);
							$grid.packery( 'appended', $data );
							
							//$grid.packery('destroy').packery();


						}
					).done(function() {
						running = false;
						
						$('.sld_spinner').hide();
						
					})
					
					$.post(
						ajaxurl,
						{
							action : 'qcld_sld_loadmore_filter',
							page: page,
							itemperpage: '".$per_page."',
							column: '".$column."',
							itemcount: '".$item_count."',
							
						},
						
						function(data){
							
							$('.filter-area').append(data);
						}
					).done(function() {
						
					})
					  
				
				}
			   }
			});
			
		});";

}

				
wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_filter_custom_js);