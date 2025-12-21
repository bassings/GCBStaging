<!-- Style-1 Template -->
<!--Adding Template Specific Style -->
<?php wp_enqueue_style('sld-css-style-6', SLD_OCOPD_TPL_URL . "/$template_code/template.css" ); ?>

<?php if( $paginate_items == true && $actual_pagination == 'false' ) : ?>
	<?php
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-jpages', SLD_OCOPD_TPL_URL . "/simple/jPages.min.js", array('jquery'));
		wp_enqueue_style('sld-jpages-css', SLD_OCOPD_TPL_URL . "/simple/jPages.css" );
		wp_enqueue_style('sld-animate-css', SLD_OCOPD_TPL_URL . "/simple/animate.css" );
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
	//if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
    if( ( sld_get_option('sld_enable_filtering_left')=='on' && $enable_left_filter !='false'  ) || $enable_left_filter=='true') {
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

	$subtitle_present_class = ($display_username == 'true' ? ' qcopd-username-present ' : '');

	echo '<div class="qcld-main-container-style-6"><div class="qcopd-list-wrapper '. $subtitle_present_class . ' qc-full-wrapper">';
	?>
	<?php
	//if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
    if( ( sld_get_option('sld_enable_filtering_left')=='on' && $enable_left_filter !='false'  ) || $enable_left_filter=='true') {
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
			            // $item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
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
	echo '<div id="opd-list-holder" class="qcopd-list-hoder '.$rtlClass.'">';
	global $wpdb;
	$listId = 1;
	if(is_user_logged_in() and $sldfavorite=='on'){
		$b_title = sld_get_option('sld_bookmark_title');
		
		$userid = get_current_user_id();
		$user_meta_data = get_user_meta($userid, 'sld_bookmark_user_meta');
		
		
		$conf['list_bg_color'] = sld_get_option('sld_bookmark_item_background_color');
		
		$conf['list_bg_color_hov'] = sld_get_option('sld_bookmark_item_background_color_hover');
		
		$conf['list_txt_color_hov'] = sld_get_option('sld_bookmark_item_text_color_hover');
		$conf['list_txt_color'] = sld_get_option('sld_bookmark_item_text_color');
		
		$conf['list_border_color'] = sld_get_option('sld_bookmark_item_border_color');
		
		$conf['item_bdr_color'] = sld_get_option('sld_bookmark_item_border_color');
		$conf['item_bdr_color_hov'] = sld_get_option('sld_bookmark_item_border_color_hover');
		
		$conf['list_subtxt_color'] = sld_get_option('sld_bookmark_item_sub_text_color');
		$conf['list_subtxt_color_hov'] = sld_get_option('sld_bookmark_item_sub_text_color_hover');


		$bookmark_list_custom_css = "";
		$bookmark_list_custom_css .= "#bookmark_list.style-6 .ca-menu li {
			    background-color: ".$conf['list_bg_color'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color'].";
			}

			#bookmark_list.style-6 .ca-menu li:hover {
			    background-color: ".$conf['list_bg_color_hov'].";
				color: ".$conf['list_txt_color_hov'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color_hov'].";
			}

			#bookmark_list.style-6 .ca-menu li .ca-main {
			  color: ".$conf['list_txt_color'].";";
				if( isset($conf['item_title_font_size']) && $conf['item_title_font_size']!=''):
				$bookmark_list_custom_css .= "font-size: ".$conf['item_title_font_size']." !important;";
				endif;

				if( isset($conf['item_title_line_height']) && $conf['item_title_line_height']!=''):
				$bookmark_list_custom_css .= "line-height: ".$conf['item_title_line_height']." !important;";
				endif;
			$bookmark_list_custom_css .= "}

			#bookmark_list.style-6 .ca-menu li:hover .ca-main {
			  color: ".$conf['list_txt_color_hov'].";
			}

			#bookmark_list.style-6 .ca-menu li .ca-sub {
			  color: ".$conf['list_subtxt_color'].";";
				if( isset($conf['item_subtitle_font_size']) && $conf['item_subtitle_font_size']!=''):
				$bookmark_list_custom_css .= "font-size: ".$conf['item_subtitle_font_size']." !important;";
				endif;

				if( isset($conf['item_subtitle_line_height']) && $conf['item_subtitle_line_height']!=''):
				$bookmark_list_custom_css .= "line-height:".$conf['item_subtitle_line_height']."!important;";
				endif;
			$bookmark_list_custom_css .= "}

			#bookmark_list.style-6 .ca-menu li:hover .ca-sub {
			  color: ".$conf['list_subtxt_color_hov'].";
			}

			#bookmark_list.style-6 .ca-menu li .upvote-section .upvote-btn, #qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li .upvote-section .upvote-count{
			  color: ".$conf['list_txt_color'].";
			}

			#bookmark_list.style-6 .ca-menu li:hover .upvote-section .upvote-btn, #bookmark_list.style-6 .ca-menu li:hover .upvote-section .upvote-count{
			  color: ".$conf['list_txt_color_hov'].";
			}";

        wp_add_inline_style( 'sld-css-style-6', $bookmark_list_custom_css );
			
?>



		<?php  if($favorite_hide != "hide" ){ 

			if($bookmark_list == "favorite" ){
				$new_class= "qcld_favorite_column";
			}else{
				$new_class= "";

			}


			?>
		<div class="list-and-add qc-grid-item <?php echo "opd-list-id-" . get_the_ID(); ?>">

		<div id="bookmark_list" class="qcopd-list-column <?php echo $style; ?>">

			<div class="qcopd-single-list">
				<h2><?php echo ($b_title!=''?$b_title:'Quick Links'); ?></h2>
				<ul class="ca-menu" id="sld_bookmark_ul">
					
					
					<?php
					$lists = array();
			if(!empty($user_meta_data)){
				foreach($user_meta_data[0] as $postid=>$metaids){
					
					if(!empty($metaids)){
						foreach($metaids as $metaid){
							
							$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $postid ));
							if(!empty($results)){
								foreach ($results as $key => $value) {
									$unserialized = maybe_unserialize($value->meta_value);
									if ( isset($unserialized['qcopd_timelaps']) && trim($unserialized['qcopd_timelaps']) == trim($metaid)) {
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
					$b = 1;			
					foreach($lists as $list){
					?>
					<?php
						$canContentClass = "subtitle-present";

						if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
						{
							$canContentClass = "subtitle-absent";
						}
					?>
					<li id="sld_bookmark_li_<?php echo $b; ?>" style="<?php echo ( isset($list['list_item_bg_color']) && !empty($list['list_item_bg_color']) ) ? 'background:'. esc_attr($list['list_item_bg_color']) : ''; ?>">
						<?php
							$item_url = esc_url($list['qcopd_item_link']);
							$masked_url = esc_url($list['qcopd_item_link']);

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
						?>
						<!-- List Anchor -->
						<?php 
							$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
							$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;

							$extra_video_url = isset($list['sld_extra_video_field']) ? $list['sld_extra_video_field'] : '';
						?>
						<?php
							if($main_click=='popup'):
								$masked_url = '#';
								$popContent = 'class="open-mpf-sld-more2 sld_bookmark_load_more" data-post-id="'.esc_attr($list['postid']).'" data-item-title="'.esc_attr(trim($list['qcopd_item_title'])).'" data-item-link="'.esc_url($list['qcopd_item_link']).'" data-mfp-src="#sld-fav-info-'.$list['postid'] ."-". $b.'"';
						?>
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo esc_attr($list['postid']); ?>" data-itemurl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" <?php echo $popContent; ?> data-subtitle="<?php echo esc_attr($list['qcopd_item_subtitle']); ?>">

							<div id="sld-fav-info-<?php echo $list['postid'] ."-". $b; ?>" class="white-popup mfp-hide">
								<div class="sld_more_text">
									<?php echo esc_html__('Loading...', 'qc-opd'); ?>
								</div>
							</div>
								
						<?php elseif(sld_is_audio_url($list['qcopd_item_link'])): ?>
							
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>"  data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" >
						<?php elseif(sld_is_mail_url($list['qcopd_item_link'])): ?>
							
							<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" href="mailto:<?php echo esc_attr($list['qcopd_item_link']); ?>"  data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>">
								
						<?php else: ?>
						<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>" <?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo get_the_ID(); ?>" data-itemurl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" >
						<?php endif; ?>
							<?php
								$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

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

										$faviconImgUrl = esc_url(trim($directImgLink));
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

							<!-- Image, If Present -->
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<span class="ca-icon list-img-1">
									<?php
										//$img = wp_get_attachment_image_src($list['qcopd_item_img']);
										$medium_size_image_for_list_item = sld_get_option('sld_enable_medium_size_image_for_list_item');
										if( isset( $medium_size_image_for_list_item ) && ( $medium_size_image_for_list_item == 'on' ) ){

											$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
										}else{
											$img = wp_get_attachment_image_src($list['qcopd_item_img']);

										}

										$image_alt 		= get_post_meta( $list['qcopd_item_img'], '_wp_attachment_image_alt', true);
										$image_alt_text = ( isset( $image_alt ) && !empty( $image_alt ) ) ? $image_alt : $list['qcopd_item_title'];
									?>
									<img src="<?php echo esc_url($img[0]); ?>" alt="<?php echo esc_attr( $image_alt_text ); ?>">
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="ca-icon list-img-1 favicon-loaded">
									<img src="<?php echo esc_url($faviconImgUrl); ?>" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="ca-icon list-img-1">
									<i class="fa <?php echo $iconClass; ?>"></i>
								</span>
							<?php else : ?>
								<span class="ca-icon list-img-1">
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<div class="ca-content">
                                <h3 class="ca-main <?php echo $canContentClass; ?>">
								<?php
									echo esc_html(trim($list['qcopd_item_title']));
								?>
							</h3>
                                <?php if( isset($list['qcopd_item_subtitle']) ) : ?>
	                                <p class="ca-sub">
	                                <?php
										echo esc_html(trim($list['qcopd_item_subtitle']));
									?>
								</p>
	                            <?php endif; ?>

                            </div>
						<?php if(sld_is_audio_url($list['qcopd_item_link'])): ?>
						<div class="ca-content-audio">
							<audio controls >
							  <source src="<?php echo esc_url($list['qcopd_item_link']); ?>" type="audio/mpeg">
							</audio>
						</div>
                        <?php endif; ?>

						</a>

						<div class="style-6-upvote-section">
						<div class="bookmark-section bookmark-section-style-6">
							
								<?php 
								$bookmark = 1;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv)){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo $list['postid']; ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" data-li-id="sld_bookmark_li_<?php echo $b; ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa fa-times-circle" aria-hidden="true"></i>
								</span>
								
							</div>
						</div>

					</li>
					<?php $b++; }; ?>
				</ul>

				<div class="clear"></div>

				

			</div>
		</div>



		</div>
		
		<?php } ?>
<?php
		
	
}

if($bookmark_list == "favorite" ){
	return false;
}

	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$optionPage = get_page(sld_get_option_page('sld_directory_page'));
	$directoryPage = home_url().'/'.$optionPage->post_name;
	
	$listId = 1;

	while ( $list_query->have_posts() )
	{
		$list_query->the_post();

		// if(sld_get_option('sld_new_expire_after')!=''){
		// 	sld_new_expired(get_the_ID());
		// }
		global $post;
		$terms = get_the_terms( get_the_ID(), 'sld_cat' );
		$slug = $post->post_name;
		//$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
		$lists = array();
		sld_remove_duplicate_master(get_the_ID());
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
			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li {
			    background-color: ".$conf['list_bg_color'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li:hover {
			    background-color: ".$conf['list_bg_color_hov'].";
				color: ".$conf['list_txt_color_hov'].";
				box-shadow: 1px 1px 2px ".$conf['item_bdr_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li .ca-main {
			  color: ".$conf['list_txt_color'].";";
				if($conf['item_title_font_size']!=''):
				$qcopd_list_custom_css .= "font-size: ".$conf['item_title_font_size']." !important;";
				endif;

				if($conf['item_title_line_height']!=''):
				$qcopd_list_custom_css .= "line-height: ".$conf['item_title_line_height']." !important;";
				endif;
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li:hover .ca-main {
			  color: ".$conf['list_txt_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li .ca-sub {
			  color: ".$conf['list_subtxt_color'].";";
				if($conf['item_subtitle_font_size']!=''):
				$qcopd_list_custom_css .= "font-size:".$conf['item_subtitle_font_size']." !important;";
				endif;

				if($conf['item_subtitle_line_height']!=''):
				$qcopd_list_custom_css .= "line-height:".$conf['item_subtitle_line_height']."!important;";
				endif;
			$qcopd_list_custom_css .= "}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li:hover .ca-sub {
			  color: ".$conf['list_subtxt_color_hov'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li .upvote-section .upvote-btn, #qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li .upvote-section .upvote-count{
			  color: ".$conf['list_txt_color'].";
			}

			#qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li:hover .upvote-section .upvote-btn, #qcopd-list-".$listId .'-'. get_the_ID().".style-6 .ca-menu li:hover .upvote-section .upvote-count{
			  color: ".$conf['list_txt_color_hov'].";
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default{
				  background: #fff none repeat scroll 0 0;
				  border: 10px solid #f6f6f6;
				  border-radius: 50%;
				  box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
				  float: left;
				  height: 250px;
				  margin-bottom: 15px;
				  overflow: hidden;
				  position: relative;
				  transition: all 400ms linear 0s;
				  width: 250px;
				  background-color: ".$conf['list_bg_color'].";
				  box-shadow: 1px 1px 2px ".$conf['item_bdr_color'].";
				  box-sizing: border-box;
			}

			#item-".$listId .'-'. get_the_ID()."-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}";
			

			if( isset($conf['list_bg_featured_color']) && !empty(@$conf['list_bg_featured_color'])){ 
			$qcopd_list_custom_css .=".qcopd-list-wrapper .style-6 .featured-section {
			   background: ".@$conf['list_bg_featured_color'].";
			    width: 100%;
			    height: 100%;
			    right: 0px;
			    border-width: 0px 0px 0px 0px;
			    border-color: transparent transparent transparent transparent;
			}

			.style-6 ul li a {
				position: relative;
			    z-index: 100;
			}";

			}

        wp_add_inline_style( 'sld-css-style-6', $qcopd_list_custom_css );
		?>

		

		<?php if( $paginate_items === 'true' && $actual_pagination == 'false' ) : 
			//
				$qcopd_list_custom_js = 'jQuery(document).ready(function($){
					$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
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

									}); ';
					endif;

					$qcopd_list_custom_js .= ' 

					jQuery(document).on("sld_pagination_search_filter_refresh", function(){
						$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
							containerID : "jp-list-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
							perPage : '.$per_page.',
						});
					});

					jQuery(document).on("sld_pagination_tag_filter_refresh", function(){
						//$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages("destroy");
						//setTimeout(function(){
							$("#jp-holder-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'").jPages({
								containerID : "jp-list-'.$SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID().(isset($cattabid)&&$cattabid!=''?'-'.$cattabid:'').'",
								perPage : '.$per_page.',
							});
						//},500);
					});
					

				});';
				
				wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_custom_js);

			?>


		<?php endif; ?>

		<div class="list-and-add qc-grid-item <?php echo "opd-list-id-" . get_the_ID(); ?>">
		
		<?php if( !empty($lists) && (count($lists) > -1) ){ ?>
			<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qcopd-list-column <?php echo $style; ?>">

				<div class="qcopd-single-list">
					<?php
						$item_count_disp = "";

						if( $item_count == "on" ){
							//$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
							$item_count_disp = qcld_item_count_by_function( get_the_ID() ) ? qcld_item_count_by_function(  get_the_ID() ) : count( get_post_meta( get_the_ID(), 'qcopd_list_item01' ) ) ;
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
					<ul class="ca-menu" id="jp-list-<?php echo $SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>">
						<?php $count = 1; ?>
						<?php foreach( $lists as $list ) : ?>
						<?php
							$canContentClass = "subtitle-present";

							if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
							{
								$canContentClass = "subtitle-absent";
							}
							
						?>
						<li id="item-<?php echo get_the_ID() ."-". $count; ?>" style="<?php echo ( isset($list['list_item_bg_color']) && !empty($list['list_item_bg_color']) ) ? 'background:'. esc_attr($list['list_item_bg_color']) : ''; ?>">
							<?php if( $display_username == 'true' ){ ?>
								<span class="qcopd-list-item-author" >
									<?php
										$timelaps = $list['qcopd_timelaps'];
										$userinfo = sld_get_user_details_by_item_timelaps( $timelaps );
										$sld_lan_by = sld_get_option('sld_lan_text_by')!=''?sld_get_option('sld_lan_text_by'):__('by', 'qc-opd');
										if( $userinfo ){
											echo esc_html( $sld_lan_by .' '. $userinfo->display_name );
										}else{
											echo esc_html( $sld_lan_by .' '. get_the_author_meta('display_name') );
										}
									?>
								</span>
							<?php } ?>
							<?php
								$item_url = esc_url($list['qcopd_item_link']);
								$masked_url = esc_url($list['qcopd_item_link']);

								if( $mask_url == 'on' ){
									$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
								}
								$popContent = '';
								$videoPopContent = '';
								if($main_click=='popup'){
									$masked_url = '#';
									$popContent = 'class="open-mpf-sld-more2 sld_load_more2" data-post-id="'.get_the_ID().'" data-item-title="'.esc_attr(trim($list['qcopd_item_title'])).'" data-item-link="'.esc_url($list['qcopd_item_link']).'" data-mfp-src="#sldinfo-'.get_the_ID() ."-". $count.'"';
								}
								if( $video_click=='popup' ){
									$videoPopContent = ' class="open-mpf-sld-video sld_load_video" ';
								}
								$extra_video_url = isset($list['sld_extra_video_field']) ? $list['sld_extra_video_field'] : '';

								$live_search_with_description = sld_get_option('sld_enable_live_search_with_description') ? sld_get_option('sld_enable_live_search_with_description') : '';

								$longdescription = ( isset($list['qcopd_description']) && !empty( $list['qcopd_description'] ) && ( $live_search_with_description == 'on') ) ? strip_tags( $list['qcopd_description']) : '';

								$subtitle = ( isset($list['qcopd_item_subtitle']) && !empty( $list['qcopd_item_subtitle'] ) ) ? $list['qcopd_item_subtitle'] .' '. $longdescription : $longdescription;
							?>
							<!-- List Anchor -->
							<?php if(sld_is_youtube_video($item_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($list['qcopd_item_link']); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_url($list['qcopd_item_link']).'"'; } ?>  data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>">
								
							<?php elseif(sld_is_vimeo_video($item_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo $list['qcopd_item_link']; ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_url($list['qcopd_item_link']).'"'; } ?>  data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>">

							<?php elseif(sld_is_youtube_video($extra_video_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($extra_video_url); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_attr($extra_video_url).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>"  data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>" >
								
							<?php elseif(sld_is_vimeo_video($extra_video_url)): ?>
								<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
									<div class="sld_video">
										<?php echo esc_html__('Loading...', 'qc-opd'); ?>
									</div>
								</div>
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo $videoPopContent; ?> href="<?php echo esc_url($extra_video_url); ?>" <?php if($video_click!='popup'){ echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; echo 'data-itemurl="'.esc_url($extra_video_url).'"'; } ?> data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>" >
								
							<?php elseif(sld_is_audio_url($list['qcopd_item_link'])): ?>
								
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>"  data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>" >
							<?php elseif(sld_is_mail_url($list['qcopd_item_link'])): ?>
							
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" href="mailto:<?php echo esc_attr($list['qcopd_item_link']); ?>"  data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-videourl="<?php echo esc_url($extra_video_url); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>" >
								
							<?php else: ?>

								<?php 
									$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=nofollow' : ''; 
									$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=ugc' : $qcopd_item_nofollow;
								?>
								
								<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>"
								<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo get_the_ID(); ?>" data-itemurl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" data-subtitle="<?php echo esc_attr( $subtitle ); ?>" <?php echo $popContent; ?>>
								
							<?php endif; ?>

								<?php
									$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

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

											$faviconImgUrl = esc_url(trim($directImgLink));
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

								<!-- Image, If Present -->
								<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
									<span class="ca-icon list-img-1">
										<?php
											//$img = wp_get_attachment_image_src($list['qcopd_item_img']);
											$medium_size_image_for_list_item = sld_get_option('sld_enable_medium_size_image_for_list_item');
											if( isset( $medium_size_image_for_list_item ) && ( $medium_size_image_for_list_item == 'on' ) ){

												$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
											}else{
												$img = wp_get_attachment_image_src($list['qcopd_item_img']);

											}

											$image_alt 		= get_post_meta( $list['qcopd_item_img'], '_wp_attachment_image_alt', true);
											$image_alt_text = ( isset( $image_alt ) && !empty( $image_alt ) ) ? $image_alt : $list['qcopd_item_title'];
										?>
										<img src="<?php echo esc_url($img[0]); ?>" alt="<?php echo esc_attr( $image_alt_text ); ?>">
									</span>
								<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
									<span class="ca-icon list-img-1 favicon-loaded">
										<img src="<?php echo esc_url($faviconImgUrl); ?>" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
									</span>
								<?php elseif( $iconClass != "" ) : ?>
									<span class="ca-icon list-img-1">
										<i class="fa <?php echo $iconClass; ?>"></i>
									</span>
								<?php else : ?>
									<span class="ca-icon list-img-1">
										<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
									</span>
								<?php endif; ?>

								<!-- Link Text -->
								<div class="ca-content">
	                                <h3 class="ca-main <?php echo $canContentClass; ?>">
									<?php
										echo esc_html(trim($list['qcopd_item_title']));
									?>
								</h3>
	                                <?php if( isset($list['qcopd_item_subtitle']) ) : ?>
		                                <p class="ca-sub">
		                                <?php
											echo esc_html(trim($list['qcopd_item_subtitle']));
										?>
									</p>
		                            <?php endif; ?>

	                            </div>
						<?php if(sld_is_audio_url($list['qcopd_item_link'])): ?>
						<div class="ca-content-audio">
							<audio controls >
							  <source src="<?php echo esc_url($list['qcopd_item_link']); ?>" type="audio/mpeg">
							</audio>
						</div>
                        <?php endif; ?>

							</a>
							<div class="style-6-upvote-section">
							
								<!-- upvote section -->
								<div class="bookmark-section bookmark-section-style-6">
									<?php if(isset($list['qcopd_description']) && $list['qcopd_description']!=''): ?>
									<span class="open-mpf-sld-more sld_load_more" data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo esc_attr(trim($list['qcopd_item_title'])); ?>" data-item-link="<?php echo esc_url($list['qcopd_item_link']); ?>" style="cursor:pointer" data-mfp-src="#sldinfo-<?php echo get_the_ID() ."-". $count; ?>">
										<i class="fa fa-info-circle"></i>
									</span>
									<?php endif; ?>
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
									
									<?php 
									$category = 'default';
									if(!empty($terms)){
										$category = $terms[0]->slug;
									}
									
									$newurl = $directoryPage.'/'.$category.'/'.$slug.'/'.urlencode(str_replace(' ','-',strtolower($list['qcopd_item_title']))).'/'.$list['qcopd_timelaps'];
									if($item_details_page=='on' && !empty($optionPage) && sld_get_option('sld_enable_multipage')=='on'):
									?>
									
									
									<span><a class="sld_internal_link" href="<?php echo $newurl; ?>" title="Go to link details page"><i class="fa fa-external-link-square" aria-hidden="true"></i></a></span>
									<?php endif; ?>
									
								</div>
								
									<div id="sldinfo-<?php echo get_the_ID() ."-". $count; ?>" class="white-popup mfp-hide">
										<div class="sld_more_text">
											<?php echo esc_html__('Loading...', 'qc-opd'); ?>
										</div>
									</div>								
								
								
							<?php if( $upvote == 'on' ) : ?>

								<!-- upvote section -->
								<div class="upvote-section upvote-section-style-6">
									<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.($list['qcopd_timelaps']!=''?$list['qcopd_timelaps']:$count); ?>" data-item-title="<?php echo esc_attr(trim($list['qcopd_item_title'])); ?>" data-item-link="<?php echo esc_url($list['qcopd_item_link']); ?>" class="upvote-btn upvote-on">
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
								
								
						<?php if( class_exists('QCOPD\QCOPD_Review_Rating') && function_exists('qcopdr_ot_get_option')  && qcopdr_ot_get_option('qcopdr_enable_reviews') != 'off' && ( isset($review) && $review=='true' ) ){ ?>
							<?php
								$saved_post = $post;
								$average_rating_point = 0;
								$negative_rating_point = 5;

								$average_rating = qcopdr_get_item_average_rating( get_the_ID(), $list['qcopd_timelaps'] );

								if( !empty($average_rating) && isset($average_rating['average_rating']) ){
									$average_rating_point = $average_rating['average_rating'];
									$negative_rating_point = (5 - $average_rating_point);

									$half_rating = ($average_rating_point + $negative_rating_point);
								}

								if($saved_post) {
								    $post = $saved_post;
								}
							?>
							<div data-list-id="<?php echo get_the_ID(); ?>" data-item-id="<?php echo $list['qcopd_timelaps']; ?>" data-mfp-src="#sld-item-average-rating-form-<?php echo $count.'-'.get_the_ID().'-'.$list['qcopd_timelaps']; ?>" class="sld-item-review-opener sld-item-review-opener-active">
								<div class="sld-item-review-opener-inner <?php if( $average_rating_point == 0 ){ echo 'sld-no-rating'; } ?>">
									<span class="fa-stack fa-x">
									    <i class="fa fa-star qcopdr-fa-active fa-stack-2x"></i>
									      <span class="fa fa-stack-1x">
									          <span class="rating-number">
									              <?php if( $average_rating_point > 0 ){ echo round($average_rating_point * 2) / 2; }else{ echo 0; } ?>
									          </span>
									    </span>
									</span>	
								</div>
								<div class="sld-item-star-all-icons">
									<?php
										for ($i=1; $i <= $average_rating_point; $i++) { 
											echo '<i data-value="'.$i.'" class="fa fa-star qcopdr-fa-active"></i>';
										}
										if( is_float($average_rating_point) ){
											echo '<i data-value="'.$i.'" class="fa fa-star-half qcopdr-fa-active"></i>';	
											echo '<i data-value="'.$i.'" class="fa fa-star fa-star-blank-half"></i>';	
										}
										for ($i=1; $i <= $negative_rating_point; $i++) { 
											echo '<i data-value="'.$i.'" class="fa fa-star"></i>';
										}
									?>
								</div>
							</div>
							<div data-list-id="<?php echo get_the_ID(); ?>" data-item-id="<?php echo $list['qcopd_timelaps']; ?>" id="sld-item-average-rating-form-<?php echo $count.'-'.get_the_ID().'-'.$list['qcopd_timelaps']; ?>" class="white-popup mfp-hide">
								<div class="sld-review-ajax-form">
									<?php
										if( class_exists('QCPD\FrontEnd\Review\Reviews') ){
											// $sld_review = new \QCPD\FrontEnd\Review\Reviews();
											// $sld_review->display_ajax_review_fields( $list, get_the_ID() );
										}
									?>
								</div>
							</div>
						<?php } ?>

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

					<div class="clear"></div>

					<?php if( $paginate_items === 'true' && $actual_pagination == 'false' ) : ?>

					<!-- navigation panel -->
					<div id="jp-holder-<?php echo $SLD_QCOPD_DIRectory_instance_count.'-'.get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>" class="sldp-holder"></div>

					<?php endif; ?>

					<div class="clear"></div>

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
		<?php } ?>

		</div>

		<?php

		$listId++;
	}

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

	});
	jQuery(window).on('load',function(){
		jQuery('.qc-grid').packery({
		  itemSelector: '.qc-grid-item',
		  gutter: 10
		});
	})";

				
wp_add_inline_script( 'qcopd-custom-script', $qcopd_list_filter_custom_js);