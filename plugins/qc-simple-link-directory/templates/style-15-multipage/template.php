<?php wp_enqueue_style('sld-css-style-15-multi', SLD_OCOPD_TPL_URL . "/$template_code/style.css" ); ?>


<?php

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
		require ( dirname(__FILE__) . "/search-template.php" );

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
	    $args = array(
		    'numberposts' => - 1,
		    'post_type'   => 'sld',
		    'orderby'     => $filterorderby,
			'order'       => $filterorder,
	    );

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
		//$listItems = apply_filters('sld_exclude_item_by_attr', $listItems, $exclude );

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
					<a href="#" class="filter-btn" data-filter="all" title="<?php echo sld_get_option('sld_lan_show_all') ? sld_get_option('sld_lan_show_all') :esc_html('Show All', 'qc-opd');  ?>">
						<?php 
							if(sld_get_option('sld_lan_show_all')!=''){
								echo sld_get_option('sld_lan_show_all');
							}else{
								_e('Show All', 'qc-opd'); 
							}
						?>
						<?php
							if($item_count == 'on'){
								echo '<span class="opd-item-count-fil">('.$item_count_disp_all.')</span>';
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
						    //$item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
						    $item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
					    }
					    ?>

                        <div class="item">
                            <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>"
                               style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>"  title="<?php echo esc_attr($item->post_title); ?>">
							    <?php echo esc_html($item->post_title); ?>
							    <?php
							    if ( $item_count == 'on' ) {
								    echo '<span class="opd-item-count-fil">(' . $item_count_disp . ')</span>';
							    }
							    ?>
                            </a>
                        </div>

				    <?php endforeach; ?>

                </div>

                <?php if($cattabid==''): 

					$qcopd_slick_custom_js = "";
					$qcopd_slick_custom_js .= "jQuery(document).ready(function ($) {

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

                    });";


					wp_add_inline_script( 'qcopd-custom-script', $qcopd_slick_custom_js);

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

	echo '<div class="qcld-main-15-multipage"><div class="qcopd-list-wrapper qc-full-wrapper">';
	?>

	<?php
	echo '<div id="opd-list-holder" class="qc-grid qcopd-list-hoder '.$rtlClass.'">';
	
	echo '<section class="qc-page-section qc-main-section"><div class="qc-sld-inner-row-11 qc-sld-wrapper" id="sld_slide_container"><div class="qc-sld-grid-11" style="width:100%">';
	
	global $wpdb;

	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$listId = 1;
	global $wp;
	$current_url = home_url( $wp->request );
	while ( $list_query->have_posts() )
	{
		$list_query->the_post();

		// if(sld_get_option('sld_new_expire_after')!=''){
		// 	sld_new_expired(get_the_ID());
		// }
		//$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
		$lists = array();
		sld_remove_duplicate_master(get_the_ID());

		// $max_pagination_number = 0;
		$item_order_check = isset($item_order) ? $item_order : 'ASC';
		if( $actual_pagination == "true" ){
			$qcsld_paged = isset($_GET['qcsld_paged']) && intval( $_GET['qcsld_paged'] > 0 ) ? sanitize_text_field($_GET['qcsld_paged']) : 1;
			$all_result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check LIMIT $per_page", get_the_ID() ));
			$total_pagination_page = ceil($all_result / $per_page) ;
			if( $total_pagination_page > $max_pagination_number ){
				$max_pagination_number = $total_pagination_page;
			}
			
			if( $qcsld_paged > 1 ){
				$offset = ( ( $qcsld_paged - 1 ) * $per_page );
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check LIMIT $per_page OFFSET $offset", get_the_ID() ));	
			}else{
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check LIMIT $per_page", get_the_ID() ));	
			}
		}else{
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check", get_the_ID() ));
		}

		if(!empty($results)){
			foreach($results as $result){
				$unserialize = maybe_unserialize($result->meta_value);
				if(!isset($unserialize['qcopd_unpublished']) or $unserialize['qcopd_unpublished']==0)
					$lists[] = $unserialize;
			}
		}
		$lists = sldmodifyupvotes(get_the_ID(), $lists);
		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		$addvertise = get_post_meta( get_the_ID(), 'sld_add_block', true );

		$addvertiseContent = isset($addvertise['add_block_text']) ? $addvertise['add_block_text'] : '';

		/* for exclude item */
		$lists = apply_filters('sld_exclude_item_by_attr', $lists, $exclude );


		if( $item_orderby == 'upvotes' )
		{
			if( $item_order == 'ASC' ){
				usort($lists, "sld_custom_sort_by_tpl_upvotes_asc");	
			}else{
				usort($lists, "sld_custom_sort_by_tpl_upvotes");	
			}
		}

		if( $item_orderby == 'title' )
		{
			if( $item_order == 'ASC' ){
				usort($lists, "sld_custom_sort_by_tpl_title_asc");	
			}else{
				usort($lists, "sld_custom_sort_by_tpl_title");	
			}
		}

		if( $item_orderby == 'timestamp' )
		{
			if( $item_order == 'ASC' ){
				usort($lists, "sld_custom_sort_by_tpl_timestamp_asc");	
			}else{
				usort($lists, "sld_custom_sort_by_tpl_timestamp");
			}
		}

		if( $item_orderby == 'random' )
		{
			shuffle( $lists );
		}
		if(sld_get_option('sld_featured_item_top')=='on'){
			$lists = sld_featured_at_top($lists);
		}
		//adding extra variable in config
		if( !is_array($conf) || empty($conf) ){
			$conf = array();
		}
		@$conf['item_title_font_size'] = $title_font_size;
		@$conf['item_subtitle_font_size'] = $subtitle_font_size;
		@$conf['item_title_line_height'] = $title_line_height;
		@$conf['item_subtitle_line_height'] = $subtitle_line_height;


		$qcopd_list_custom_css = "";
		$qcopd_list_custom_css .= "

			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .ilist-item-main {
					background-color: ".$conf['list_bg_color'].";
			}
			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .ilist-item-main:hover {
					background-color: ".$conf['list_bg_color_hov'].";
			}
            #qcopd-list-".$listId .'-'. get_the_ID()." ul li .item-title-text {
                background: ".$conf['list_border_color'].";
            }

			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .panel-title h3{
			  color: ".$conf['list_txt_color'].";";
				if($conf['item_title_font_size']!=''): 
				$qcopd_list_custom_css .= "font-size:".$conf['item_title_font_size']." !important;";
				endif; 

				if($conf['item_title_line_height']!=''): 
				$qcopd_list_custom_css .= "line-height:".$conf['item_title_line_height']." !important;";
				endif; 
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .panel-title h3:hover{
			  color: ".$conf['list_txt_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .sld-hover-content p{
			  color: ".$conf['list_subtxt_color'].";";
				if($conf['item_subtitle_font_size']!=''): 
				$qcopd_list_custom_css .= "font-size:".$conf['item_subtitle_font_size']." !important;";
				endif; 

				if($conf['item_subtitle_line_height']!=''): 
				$qcopd_list_custom_css .= "line-height:".$conf['item_subtitle_line_height']."!important;";
				endif; 
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID()." ul li .sld-hover-content p:hover{
			  color: ".$conf['list_subtxt_color_hov'].";
			}";

			

        wp_add_inline_style( 'sld-css-style-15-multi', $qcopd_list_custom_css );
		?>

		

		<?php if( $paginate_items === 'true' ) : 
			//
			$qcopd_list_custom_js = 'jQuery(document).ready(function($){
				$("#jp-holder-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
	    			containerID : "jp-list-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
	    			perPage : '.$per_page.',
	  			});';
				
				if(isset($cattabid)&&$cattabid!=''):
				$qcopd_list_custom_js .= '$(".sld_search_filter").keyup(function(){

									setTimeout(function(){
										$(".qc-grid").packery({
										  itemSelector: ".qc-grid-item",
										  gutter: 10
										});
									}, 900);

								});';
				endif;

				$qcopd_list_custom_js .= ' 

				jQuery(document).on("sld_pagination_search_filter_refresh", function(){
					$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
						containerID : "jp-list-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
						perPage : '.$per_page.',
					});
				});

				jQuery(document).on("sld_pagination_tag_filter_refresh", function(){
					$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages("destroy");
					setTimeout(function(){
						$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
							containerID : "jp-list-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
							perPage : '.$per_page.',
						});
					},500);
				});
				

			});';
			
			wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_custom_js);

		?>

			

		<?php endif; ?>

		<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-feature-container qc-grid-item qc-sld-single-item-11 qcpd-list-column <?php echo $style;?> <?php echo "opd-list-id-" . get_the_ID(); ?>">
              	
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						// $item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
						$item_count_disp = qcld_item_count_by_function( get_the_ID() ) ? qcld_item_count_by_function(  get_the_ID() ) : count( get_post_meta( get_the_ID(), 'qcopd_list_item01' ) ) ;
					}
				?>
				<?php if($hide_list_title != 'true'){ ?>
				<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
					<?php
						if(isset($conf['title_link']) && $conf['title_link']!=''):
							echo '<a href="'.$conf['title_link'].'" '.(isset($conf['title_link_new_tab'])&&$conf['title_link_new_tab']==1?'target="_blank"':'').' >';
						endif;
						?>
						<?php echo esc_html(get_the_title()); ?>
						<?php
							if($item_count == 'on'){
								$item_count_disp = isset($lists) ? count($lists) : $item_count_disp;
								echo '<span class="opd-item-count">('.$item_count_disp.')</span>';
							}
						?>
						<?php 
						if( isset($conf['title_link']) && $conf['title_link']!=''):
							echo '</a>';
						endif;
					?>
				</h2>
				<?php } ?>
				<ul id="jp-list-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>" class="<?php echo $style; ?>">
                    <?php

						if( $item_orderby == 'upvotes' )
						{
							if( $item_order == 'ASC' ){
								usort($lists, "sld_custom_sort_by_tpl_upvotes_asc");	
							}else{
								usort($lists, "sld_custom_sort_by_tpl_upvotes");	
							}
						}

						if( $item_orderby == 'title' )
						{
							if( $item_order == 'ASC' ){
								usort($lists, "sld_custom_sort_by_tpl_title_asc");	
							}else{
								usort($lists, "sld_custom_sort_by_tpl_title");	
							}
						}

						if( $item_orderby == 'timestamp' )
						{
							if( $item_order == 'ASC' ){
								usort($lists, "sld_custom_sort_by_tpl_timestamp_asc");	
							}else{
								usort($lists, "sld_custom_sort_by_tpl_timestamp");
							}
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
						$all_location[] = $list;
						
						$tooltip_content = '';

						if( $tooltip === 'true' ){
							$tooltip_content = ' data-tooltip="'.$list['qcopd_item_subtitle'].'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}
						
					?>
                  
                  <li class="opt-column-0<?php echo $column; ?>" id="item-<?php echo get_the_ID() ."-". $count; ?>" <?php echo $tooltip_content; ?> data-title="<?php echo $list['qcopd_item_title']; ?>" data-subtitle="<?php echo $list['qcopd_item_subtitle']; ?>" data-url="<?php echo $list['qcopd_item_link']; ?>">
						<?php 
							global $wp_query;
							//constructing new url for multipage//
							$newurl = home_url();
							if(isset($wp_query->query_vars['pagename']) and $wp_query->query_vars['pagename']!=''){
								$newurl = $newurl.'/'.$wp_query->query_vars['pagename'];
							}
							if(isset($wp_query->query_vars['sldcat']) and $wp_query->query_vars['sldcat']!=''){
								$newurl = $newurl.'/'.$wp_query->query_vars['sldcat'];
							}
							if(isset($wp_query->query_vars['sldlist']) and $wp_query->query_vars['sldlist']!=''){
								$newurl = $newurl.'/'.$wp_query->query_vars['sldlist'];
							}else{
								$newurl = $newurl.'/'.get_post(get_the_ID())->post_name;
							}
							
							
							if(isset($list['qcopd_item_title']) && $list['qcopd_item_title']!=''){
								$newurl = $newurl.'/'.urlencode(str_replace(' ','-',strtolower($list['qcopd_item_title'])));
							}
							
							if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']!=''){
								$newurl = $newurl.'/'.trim($list['qcopd_timelaps']);
							}
							if( $multipage_item_details == 'false' ){
								$item_url = $masked_url = $list['qcopd_item_link'];
							}else{
								$item_url = $masked_url = $newurl;
							}
							$othersetting = '';
							if(sld_get_option('sld_multi_same_window')=='off'){
								if(isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1){
									if(isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1){
										$othersetting .= ' rel="ugc"';
									}else{
										$othersetting .= ' rel="nofollow"';
									}
								}
								if(isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1){
									$othersetting .=' target="_blank"';
								}

								
							}
							
							if(sld_get_option('sld_lan_visit_page')!=''){
								$visit_page = sld_get_option('sld_lan_visit_page');
							}else{
								$visit_page = __('Visit Page','qc-opd');
							}
							
							$popContent = '';
							if($main_click=='popup'){
								$masked_url = '#';
								$popContent = 'class="open-mpf-sld-more2 sld_load_more2" data-post-id="'.get_the_ID().'" data-item-title="'.trim($list['qcopd_item_title']).'" data-item-link="'.$list['qcopd_item_link'].'" data-mfp-src="#sldinfo-'.get_the_ID() ."-". $count."-". esc_attr($list['qcopd_timelaps']).'"';
							}
						?>

						<?php
							//$item_url = esc_url($list['qcopd_item_link']);
							//$masked_url = esc_url($list['qcopd_item_link']);

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
							$popContent = '';
							$videoPopContent = '';
							if($main_click=='popup'){
								$masked_url = '#';
								$popContent = 'class="open-mpf-sld-more2 sld_load_more2" data-post-id="'.get_the_ID().'" data-item-title="'.trim($list['qcopd_item_title']).'" data-item-link="'.$list['qcopd_item_link'].'" data-mfp-src="#sldinfo-'.get_the_ID() ."-". $count."-". esc_attr($list['qcopd_timelaps']).'"';
							}

							if( $video_click=='popup' ){
								$videoPopContent = ' class="open-mpf-sld-video sld_load_video" ';
							}
							$extra_video_url = isset($list['sld_extra_video_field']) ? $list['sld_extra_video_field'] : '';
						?>
						
                        <?php if(sld_is_youtube_video($extra_video_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($extra_video_url); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_attr($extra_video_url).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>"  data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" >
								
						<?php elseif(sld_is_vimeo_video($extra_video_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($extra_video_url); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_url($extra_video_url).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" >
								
						<?php else: ?>
						<?php 
							$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
							$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;
						?>
								
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo $masked_url; ?>" <?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; ?> data-tag="<?php echo (isset($list['qcopd_tags'])?$list['qcopd_tags']:'' ); ?>" <?php echo $popContent; ?> data-itemid="<?php echo get_the_ID(); ?>" data-itemurl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>">
						<?php endif; ?>
                        <div class="qc-sld-main">
                          <div class="qc-feature-media image">
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
									}elseif( $directImgLink == '' ){
										$faviconFetchable = false;
									}
								}

							?>

							<?php
								$separate_multipage_img = sld_get_option('sld_display_separate_multipage_img');
								$used_separate_img = 0;
								if( $separate_multipage_img == 'on' ){
									if( $list['qcopd_multipage_item_img'] != '' ){
										$used_separate_img = 1;
									}
								}
							?>

						<?php if( $used_separate_img == 0 ){ ?>
							<!-- Image, If Present -->
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<?php 
									if (strpos($list['qcopd_item_img'], 'http') === FALSE){
								?>
								
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium_large');

										$image_alt 		= get_post_meta( $list['qcopd_item_img'], '_wp_attachment_image_alt', true);
										$image_alt_text = ( isset( $image_alt ) && !empty( $image_alt ) ) ? $image_alt : $list['qcopd_item_title'];
										
									?>
									<img src="<?php echo $img[0]; ?>"  alt="<?php echo esc_attr( $image_alt_text ); ?>">
								
								<?php
									}else{
								?>
								
									<img src="<?php echo $list['qcopd_item_img']; ?>"  alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
								
								<?php
									}
								?>
								
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								
									<img src="<?php echo $faviconImgUrl; ?>"  alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">

							<?php elseif( $iconClass != "" ) : ?>
								
									<i class="fa <?php echo $iconClass; ?> sld_f_icon"></i>
								
							<?php else : ?>
								
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png"  alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
								
							<?php endif; ?>
						<?php }else{ ?>
							<?php 
								if (strpos($list['qcopd_multipage_item_img'], 'http') === FALSE){
							?>
							
								<?php
									$img = wp_get_attachment_image_src($list['qcopd_multipage_item_img'], 'medium_large');

									$image_alt 		= get_post_meta( $list['qcopd_item_img'], '_wp_attachment_image_alt', true);
									$image_alt_text = ( isset( $image_alt ) && !empty( $image_alt ) ) ? $image_alt : $list['qcopd_item_title'];
									
								?>
								<img src="<?php echo $img[0]; ?>"  alt="<?php echo esc_attr( $image_alt_text ); ?>">
							
							<?php
								}else{
							?>
							
								<img src="<?php echo $list['qcopd_multipage_item_img']; ?>"  alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
							
							<?php
								}
							?>
						<?php } ?>
							
                            <div class="upvote-section">
								<?php 
								$bookmark = 0;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv) && get_current_user_id()!=0){
										$bookmark = 1;
									}
								}
								
								?>
								<?php if(sld_get_option('sld_enable_bookmark')=='on'): ?>
                                <span data-post-id="<?php echo get_the_ID(); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
								</span>
								<?php endif; ?>
								<?php if(isset($list['qcopd_description']) && $list['qcopd_description']!=''): ?>
								<span class="open-mpf-sld-more sld_load_more" data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" style="cursor:pointer" data-mfp-src="#sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>">
									<i class="fa fa-info-circle"></i>
								</span>
								<?php endif; ?>
								<?php if( $upvote == 'on' ) : ?>
                                <div class="favourite">
                                    <span data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" data-unique="<?php echo get_the_ID().'_'.($list['qcopd_timelaps']!=''?$list['qcopd_timelaps']:$count); ?>" class="sld-upvote-btn upvote-btn upvote-on">
									<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
									</span>
									<span class="upvote-count">
										<?php
										    if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
											  	$sld_shorten_upvote = sld_get_option('sld_upvote_shorten_number');
											  	if( $sld_shorten_upvote == 'on' ){
											  		echo apply_filters('sld_shorten_upvote_number', qc_sld_shorten($list['qcopd_upvote_count']));
											  	}else{
											  		echo (int)$list['qcopd_upvote_count'];
											  	}
											}
										?>
									</span>
                                </div>
								<?php endif; ?>
                            </div> 
                          </div>
                          <div class="qc-sld-content">
                            <h4 class="sld-title"><?php echo $list['qcopd_item_title']; ?></h4>
                            
                            <p class="sub-title"><?php echo $list['qcopd_item_subtitle']; ?>
							
							</p>
							
                          </div>
                          
                          <div class="clear"></div>
                          </div>
						<?php if(isset($list['qcopd_new']) and $list['qcopd_new']==1):?>
						<!-- new icon section -->
						<div class="new-icon-section">
							<span> 
							<?php
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
                      </a>
					  

				
							<div id="sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>" class="white-popup mfp-hide">
								<div class="sld_more_text">
									<?php echo esc_html__('Loading...', 'qc-opd'); ?>
								</div>
							</div>
                  </li>
				  
				  <?php $count++; endforeach; ?>

                  </ul>
        </div>


		
		<?php

		$listId++;
	}
?>

		</div>

          <div class="clear"></div>
		
		</div>
		</section>

<?php
	echo '<div class="sld-clearfix"></div>
			</div>
		<div class="sld-clearfix"></div>
	</div></div>';

	//Hook - After Main List
	do_action( 'qcsld_after_main_list', $shortcodeAtts);

}

?>

<?php
	if( $actual_pagination == 'true' && $max_pagination_number > 1 ){
		echo qcsld_pagination_links( $qcsld_paged, $max_pagination_number, 0);
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

	});";

				
wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_filter_custom_js);