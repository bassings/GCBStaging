<!-- Style-1 Template -->
<!--Adding Template Specific Style -->
<?php wp_enqueue_style('sld-css-style-10-multi', SLD_OCOPD_TPL_URL . "/$template_code/template.css" ); ?>


<?php
global $wp;
     $current_url =  home_url( $wp->request );
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
								//$item_count_disp_all += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
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
                               style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>" title="<?php echo esc_attr($item->post_title); ?>">
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

	echo '<div class="qcopd-list-wrapper qc-full-wrapper">';
	?>
	<?php
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

		$filterType = sld_get_option( 'sld_filter_ptype' ); //normal, carousel

//If FILTER TYPE is NORMAL


			?>

            <div class="filter-area left-side-filter">

                <a href="#" class="filter-btn" data-filter="all" title="<?php echo sld_get_option('sld_lan_show_all') ? sld_get_option('sld_lan_show_all') :esc_html('Show All', 'qc-opd');  ?>">
					<?php 
						if(sld_get_option('sld_lan_show_all')!=''){
							echo sld_get_option('sld_lan_show_all');
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
			            //$item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
			            $item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
		            }
		            ?>

                    <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>" title="<?php echo esc_attr($item->post_title); ?>">
			            <?php echo esc_html($item->post_title); ?>
			            <?php
			            if ( $item_count == 'on' ) {
				            echo '<span class="opd-item-count-fil">(' . $item_count_disp . ')</span>';
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

	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$listId = 1;

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
			$all_result = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id =%d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check LIMIT $per_page", get_the_ID() ));
			$total_pagination_page = ceil($all_result / $per_page) ;
			if( $total_pagination_page > $max_pagination_number ){
				$max_pagination_number = $total_pagination_page;
			}
			
			if( $qcsld_paged > 1 ){
				$offset = ( ( $qcsld_paged - 1 ) * $per_page );
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01' order by `meta_id` $item_order_check LIMIT $per_page OFFSET $offset", get_the_ID() ) );	
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
			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li .effect-style-seven{
			    background-color: ".$conf['list_bg_color'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li:hover .effect-style-seven{
			    background-color: ".$conf['list_bg_color_hov'].";
				color: ".$conf['list_txt_color_hov'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li h3 {
			  color: ".$conf['list_txt_color'].";";
				if($conf['item_title_font_size']!=''):
				$qcopd_list_custom_css .= "font-size:".$conf['item_title_font_size']." !important;";
				endif;

				if($conf['item_title_line_height']!=''):
				$qcopd_list_custom_css .= "line-height:".$conf['item_title_line_height']." !important;";
				endif;
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li:hover h3 {
			  color: ".$conf['list_txt_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li p {
			  color: ".$conf['list_subtxt_color'].";";
				if($conf['item_subtitle_font_size']!=''):
				$qcopd_list_custom_css .= "font-size:".$conf['item_subtitle_font_size']." !important;";
				endif;

				if($conf['item_subtitle_line_height']!=''):
				$qcopd_list_custom_css .= "line-height:".$conf['item_subtitle_line_height']."!important;";
				endif;
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li:hover p {
			  color: ".$conf['list_subtxt_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li .upvote-section .upvote-btn, #qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li .upvote-section .upvote-count{
			  color: ".$conf['list_subtxt_color'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li:hover .upvote-section .upvote-btn, #qcopd-list-".$listId .'-'. get_the_ID().".style-10 ul li:hover .upvote-section .upvote-count{
			  color: ".$conf['list_subtxt_color_hov'].";
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default{
				border-radius: 5px;
				box-shadow: 0 6px 12px 5px rgba(0, 0, 0, 0.2);
				overflow: hidden;
				position: relative;
				transition: all 0.5s ease-in-out 0s;
				box-sizing: border-box;
				width: 220px;
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}";
			

        	wp_add_inline_style( 'sld-css-style-10-multi', $qcopd_list_custom_css );
		?>

		

		<div class="list-and-add qc-grid-item <?php echo "opd-list-id-" . get_the_ID(); ?>">

		<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qcopd-list-column <?php echo $style; ?>">

			<div class="qcopd-single-list-1">
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
						$item_count_disp = qcld_item_count_by_function( get_the_ID() ) ? qcld_item_count_by_function( get_the_ID() ) : count( get_post_meta( get_the_ID(), 'qcopd_list_item01' ) ) ;
					}
				?>
				<?php if($hide_list_title != 'true'){ ?>
				<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
					<?php 
					if($multipage=='true'):
						echo '<a href="'.$current_url.'/'.get_post(get_the_ID())->post_name.'">';
					elseif(isset($conf['title_link']) && $conf['title_link']!=''):
						echo '<a href="'.$conf['title_link'].'" '.(isset($conf['title_link_new_tab'])&&$conf['title_link_new_tab']==1?'target="_blank"':'').'>';
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
					if($multipage=='true' or ( isset($conf['title_link']) && $conf['title_link'] !='' ) ):
						echo '</a>';
					endif;
					?>
				</h2>
				<?php } ?>
				<section class="list-section-seven">
			        <div class="container-style-10">
			            <div class="row-style-10">
			                <ul class="portfolio-listing" class="ca-menu column<?php echo $column; ?>">
					<?php $count = 1; 
						if( $item_orderby == 'upvotes' )
						{
							if( $item_order == 'ASC' ){
								usort($lists, "sld_custom_sort_by_tpl_upvotes_asc");	
							}else{
								usort($lists, "sld_custom_sort_by_tpl_upvotes");	
							}
						}
						
						if( $item_orderby == 'clicks' )
						{
    						usort($lists, "custom_sort_by_tpl_click");
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
					?>
					<?php foreach( $lists as $list ) : ?>
					<?php
						$canContentClass = "subtitle-present";

						if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
						{
							$canContentClass = "subtitle-absent";
						}
						
					?>
                    <li id="item-<?php echo get_the_ID() ."-". $count; ?>"  class="list-style-seven listy-style-seven-01 sld-style10-column<?php echo $column; ?>">
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
									$othersetting .= ' rel="nofollow"';
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
							$videoPopContent = '';
							if($main_click=='popup'){
								$masked_url = '#';
								$popContent = 'class="open-mpf-sld-more2 sld_load_more2" data-post-id="'.get_the_ID().'" data-item-title="'.trim($list['qcopd_item_title']).'" data-item-link="'.$list['qcopd_item_link'].'" data-mfp-src="#sldinfo-'.get_the_ID() ."-". $count. "-". esc_attr($list['qcopd_timelaps']).'"';
							}

							if( $video_click=='popup' ){
								$videoPopContent = ' class="open-mpf-sld-video sld_load_video" ';
							}
						?>
						<!-- List Anchor -->
						<?php if(sld_is_youtube_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									<?php echo esc_html__('Loading...', 'qc-opd'); ?>
								</div>
							</div>
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($list['qcopd_item_link']); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1  && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; echo 'data-itemurl="'.$list['qcopd_item_link'].'"'; } ?>  data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?$list['qcopd_tags']:'' ); ?>" >
							
						<?php elseif(sld_is_vimeo_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									<?php echo esc_html__('Loading...', 'qc-opd'); ?>
								</div>
							</div>
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo $masked_url; ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1  && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; echo 'data-itemurl="'.$list['qcopd_item_link'].'"'; } ?>  data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" >
							
						<?php else: ?>
						<?php 
							$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
							$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;
						?>
							
							<a style="<?php echo ( isset($list['list_item_bg_color']) && !empty($list['list_item_bg_color']) ) ? 'background:'. esc_attr($list['list_item_bg_color']) : ''; ?>" title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?>  <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo $masked_url; ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1  && (sld_get_option('sld_multi_same_window')=='off') ) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo get_the_ID(); ?>" data-itemurl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?$list['qcopd_tags']:'' ); ?>" <?php echo $popContent; ?>>
							
						<?php endif; ?>
                            <div class="list-inner-part-seven">

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
										$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'full');
									?>
									<span class="ca-icon list-img-1" style="background-image: url(<?php echo $img[0]; ?>);">
									&nbsp;
									</span>
								<?php else : ?>
									<span class="ca-icon list-img-1" style="background-image: url(<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png);">
									</span>
								<?php endif; ?>
							<?php }else{ ?>
								<?php
									$img = wp_get_attachment_image_src($list['qcopd_multipage_item_img'], 'full');
								?>
								<span class="ca-icon list-img-1" style="background-image: url(<?php echo $img[0]; ?>);">
								&nbsp;
								</span>
                            <?php } ?>

                                <div class="effect-style-seven">
                                    <h3>
                                    	<?php
											echo trim($list['qcopd_item_title']);
										?>
                                    </h3>
                                    <?php if( isset($list['qcopd_item_subtitle']) ) : ?>
                                    <p>
                                    	<?php
											echo trim($list['qcopd_item_subtitle']);
										?>
                                    </p>
                                    <?php endif; ?>

                                </div>

                            </div>
                        </a>
						<div class="style-10-upvote-section">
						
							<div class="sld-style-10_info_icon">
								<?php if(isset($list['qcopd_description']) && $list['qcopd_description']!=''): ?>
								<span class="open-mpf-sld-more sld_load_more" data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" style="cursor:pointer" data-mfp-src="#sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>">
									<i class="fa fa-info-circle"></i>
								</span>
								<?php endif; ?>
							</div>
						
							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-style-10">

								<?php if($sldfavorite=='on'): ?>
								<?php 
								$bookmark = 0;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv) && get_current_user_id()!=0){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo get_the_ID(); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
								</span>
								<?php endif; ?>
							</div>
							
															
							
                        <?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section upvote-section-style-10 upvote-style-10">
								<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.($list['qcopd_timelaps']!=''?$list['qcopd_timelaps']:$count); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
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
							<!-- /upvote section -->

						<?php endif; ?>
						
						</div>
						
							<div id="sldinfo-<?php echo get_the_ID() ."-". $count ."-". esc_attr($list['qcopd_timelaps']); ?>" class="white-popup mfp-hide">
								<div class="sld_more_text">
									<?php echo esc_html__('Loading...', 'qc-opd'); ?>
								</div>
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

                    </li>
					<?php $count++; endforeach; ?>

							</ul>

			            </div>
			        </div>
			    </section>

			</div>
		</div>

		<?php if( $addvertiseContent != '' ) : ?>
		<!-- Add Block -->
		<div class="qcopd-list-column opd-column-<?php echo $column; ?> <?php echo "opd-list-id-" . get_the_ID(); ?>" id="item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block">
			<div class="advertise-block tpl-default">
				<?php echo apply_filters('the_content',$addvertiseContent); ?>
			</div>
		</div>
		<!-- /Add Block -->
		<?php endif; ?>

		</div>

		<?php

		$listId++;
	}

	echo '<div class="sld-clearfix"></div>
			</div>
		<div class="sld-clearfix"></div>
	</div>';

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

	";

				
wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_filter_custom_js);