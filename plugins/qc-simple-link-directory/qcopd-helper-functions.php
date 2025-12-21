<?php

function sld_get_option($key=''){
	
	if($key=='')
		return false;
	
	$data = get_option('sld_option_tree');
	
	if( !empty($data) && is_array($data) && array_key_exists($key, $data)){
		return $data[$key];
	}else{
		return false;
	}
	
}

function sld_is_youtube_video($link){

	if ( ( !empty($link) && strpos($link, 'youtube') > 0 ) || ( !empty($link) && strpos($link, 'youtu') > 0 )  ) {
		return true;
	}else if( isset( $link ) && !empty( $link ) ){
		$method = parse_url( $link, PHP_URL_QUERY ) ?? '';
		parse_str( $method, $my_array_of_vars );
	//var_dump( $link );
	//wp_die();
		if(isset($my_array_of_vars['v']) && $my_array_of_vars['v']!=''){
			return true;
		}
	}
	return false;
	
}
function sld_is_vimeo_video($link){
	$urls = parse_url($link);
	if(isset($urls['host']) && $urls['host']=='vimeo.com'){
		return true;
	}
	return false;
}
function sld_is_audio_url($link){
	$urls = parse_url($link);
	
	$mime_types = array(
		'mp3' => 'audio/mpeg',
		'wav' => 'audio/wav',
		'aac' => 'audio/aac',
		'ogg' => 'audio/ogg',
		'opus' => 'audio/ogg',
	);

	if(!empty($link) && isset($urls['host']) ){

		$var = explode('.',$link);
		$ext = strtolower(array_pop($var));
		if (isset($link) && array_key_exists($ext, $mime_types)) {
			return true;
		}
		return false;

	}
	return false;

}
function sld_is_mail_url($link){

	if(filter_var($link, FILTER_VALIDATE_EMAIL)) {
        // valid address
		return true;
    }
	return false;

}


/*
* Alexa ranking code
*/
function sld_alexaRank($url) {
 $alexaData = @simplexml_load_file("http://data.alexa.com/data?cli=10&url=".$url);
 $alexa['globalRank'] =  isset($alexaData->SD->POPULARITY) ? $alexaData->SD->POPULARITY->attributes()->TEXT : 0 ;
 $alexa['CountryRank'] =  isset($alexaData->SD->COUNTRY) ? $alexaData->SD->COUNTRY->attributes() : 0 ;
 if($alexa['globalRank']==0 && $alexa['CountryRank']==0){
	return array(); 
 }else{
	 return json_decode(json_encode($alexa), TRUE);
 }
 
}

function qc_get_id_by_shortcode($shortcode) {
	global $wpdb;
	$sql = 'SELECT ID
		FROM ' . $wpdb->posts . '
		WHERE
			post_type = "page"
			AND post_status="publish"
			AND post_content LIKE "%' . $shortcode . '%" limit 1';

	$id = $wpdb->get_var($sql);

	return $id;
}

function sld_get_the_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/*
* This function return most voted link items of SLD
*/
function qcopd_get_most_popular_links_wi( $limit = null, $category = null, $subtitle = null )
{
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
	$multipage = false;
	if(sld_get_option_page('sld_directory_page')==get_queried_object_id()){
		$multipage = true;
	}
	
	
	if( $limit == null )
	{
		$limit = 5;
	}

	$arrayOfElements = array();

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$list_args = array(
		'post_type' => 'sld',
		'orderby' => 'date',
		'order' => 'desc',
		'posts_per_page' => -1,
	);

	if( isset($category) && !empty($category) ){
		
		$category = explode(',',$category);

		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'ID',
				'terms'    => $category,
			),
		);

		$list_args = array_merge($list_args, array( 'tax_query' => $taxArray ));

	}

	$list_query = new WP_Query( $list_args );

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			$title = get_the_title();
			$id = get_the_ID();
			$category = get_the_terms( $id, 'sld_cat' );   
			
			foreach( $lists as $list )
			{
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src($list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src($list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = isset($list['qcopd_item_title']) ? trim($list['qcopd_item_title']) : '';
				$item['item_img'] = $img;
				$item['item_img_icon'] = isset($list['qcopd_fa_icon']) ? trim($list['qcopd_fa_icon']) : '';
				$item['item_subtitle'] = isset($list['qcopd_item_subtitle']) ? trim($list['qcopd_item_subtitle']) : '';
				$item['item_link'] = isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : '';
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab'] = $newtab;
				$item['item_votes'] = $votes;
				$item['item_parent'] = $title;
				$item['item_parent_id'] = $id;
				$item['item_unique'] = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';
				$item['item_timelaps'] = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';
				
				$item['item_category'] = (!empty($category)?$category[0]->slug:'no_category');

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}

	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_votes");

    ob_start();

	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 10111;
    $numberOfItems = count( $arrayOfElements );

    echo '<ul class="widget-sld-list">';

     if(sld_get_option('sld_use_global_thumbs_up')!=''){
	     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
	 }else{
	     $sld_thumbs_up = 'fa-thumbs-up';
	 }
    
    foreach( $arrayOfElements as $item ){
		$mainimg = $item['item_img'];
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}
		$iconClass = isset($item['item_img_icon']) ? $item['item_img_icon'] : '';
		
		if($multipage==true && sld_get_option('sld_widget_link_multipage')=='on'){
			$url = home_url().'/'.get_post_field( 'post_name', get_queried_object_id() ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}elseif( sld_get_option('sld_enable_widget_links_to_item_details_page')=='on'){
			$multipage_obj = sld_get_option_page('sld_directory_page');
			$url = home_url().'/'.get_post_field( 'post_name', $multipage_obj ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}else{
			$url = $item['item_link'];
		}
		
		
    	?>
    	<li id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>">

			<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $url; ?>">

				<?php if( $imgurlnew != "" ) : ?>
				
					<img class="widget-avatar" src="<?php echo $imgurlnew; ?>" alt="">

				<?php elseif( $iconClass != "" ) : ?>
					<span class="list-img">
						<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
					</span>

				<?php else : ?>

					<img class="widget-avatar" src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

				<?php endif; ?>
			
					

				<?php echo '<span class="sld-widget-title">'.$item['item_title'].'</span>'; ?>

				<?php if( $enableUpvoting == 'on' ) : ?>

				<div class="widget-vcount">
				
					<div class="upvote-section">
						
						<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-unique="<?php echo $item['item_parent_id'].'_'.$item['item_unique']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on">
							<span class="opening-bracket">
								(
							</span>
							<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
							<span class="upvote-count">
								<?php echo $item['item_votes']; ?>
							</span>
							<span class="closing-bracket">
								)
							</span>
						</span>	
						
					</div>

				</div>

				<?php endif; ?>

			</a>

			<?php 
			if(isset($subtitle) && $subtitle == 'show' ){
				echo '<span class="sld-widget-subtitle">'.$item['item_subtitle'].'</span>'; 
			}

			?>

		</li>
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

    echo '</ul>';

    $content = ob_get_clean();

    return $content;

}
//get popular new style

function qcopd_get_popular_new( $limit = null )
{
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
	$multipage = false;
	if(sld_get_option_page('sld_directory_page')==get_queried_object_id()){
		$multipage = true;
	}
	
	
	if( $limit == null )
	{
		$limit = 6;
	}

	$arrayOfElements = array();

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$list_args = array(
		'post_type' => 'sld',
		'orderby' => 'date',
		'order' => 'desc',
		'posts_per_page' => -1,
	);

	$list_query = new WP_Query( $list_args );

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			$title = get_the_title();
			$id = get_the_ID();
			$category = get_the_terms( $id, 'sld_cat' );   
			
			foreach( $lists as $list )
			{
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src($list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src($list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = isset($list['qcopd_item_title']) ? trim($list['qcopd_item_title']) : '';
				$item['item_img'] = $img;
				$item['item_img_icon'] = isset($list['qcopd_fa_icon']) ? trim($list['qcopd_fa_icon']) : '';
				$item['item_subtitle'] = isset($list['qcopd_item_subtitle']) ? trim($list['qcopd_item_subtitle']) : '';
				$item['item_link'] 	   = isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : '';
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab'] = $newtab;
				$item['item_votes'] = $votes;
				$item['item_parent'] = $title;
				$item['item_parent_id'] = $id;
				$item['item_unique'] = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';
				$item['item_timelaps'] = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';
				
				$item['item_category'] = (!empty($category)?$category[0]->slug:'no_category');

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}

	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_votes");

    ob_start();

	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 10111;
    $numberOfItems = count( $arrayOfElements );

    
    
    foreach( $arrayOfElements as $item ){
		$mainimg = $item['item_img'];
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}
		$iconClass = isset($item['item_img_icon']) ? $item['item_img_icon'] : '';
		
		if($multipage==true && sld_get_option('sld_widget_link_multipage')=='on'){
			$url = home_url().'/'.get_post_field( 'post_name', get_queried_object_id() ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}elseif( sld_get_option('sld_enable_widget_links_to_item_details_page')=='on'){
			$multipage_obj = sld_get_option_page('sld_directory_page');
			$url = home_url().'/'.get_post_field( 'post_name', $multipage_obj ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}else{
			$url = $item['item_link'];
		}
		
    	?>
    	<div class="qc-column-4" id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>"><!-- qc-column-4 -->
			<!-- Feature Box 1 -->
			<div class="featured-block ">
				<div class="featured-block-img">
					<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $url; ?>"> 
						<?php if( $imgurlnew != "" ) : ?>
					
						<img src="<?php echo $imgurlnew; ?>" alt="">

						<?php elseif( $iconClass != "" ) : ?>
							<span class="list-img">
								<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
							</span>

						<?php else : ?>

							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

						<?php endif; ?>
					</a>
				</div>
				<div class="featured-block-info">
					<h4><a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $item['item_link']; ?>"><?php echo $item['item_title']; ?> </a></h4>

				</div>
				<?php if( $enableUpvoting == 'on' ) : ?>
				<div class="featured-block-upvote upvote-section">
					<span class="upvote-count count"> <?php echo $item['item_votes']; ?> </span>
					<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on"><i class="fa fa-thumbs-up"></i> </span>
				</div>
				<?php endif; ?>
			</div>
		</div><!--/qc-column-4 -->
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

   

    $content = ob_get_clean();

    return $content;

} //End of get_most_popular_links

// Define the custom sort function
function custom_sort_by_votes($a, $b) {
    // return $a['item_votes'] < $b['item_votes'];
	if( isset($a['item_votes']) && isset($b['item_votes']) ){
		$aTime = isset($a['item_votes']) && !empty( $a['item_votes'] ) ? (int)$a['item_votes'] : 0;
		$bTime = isset($b['item_votes']) && !empty( $b['item_votes'] ) ? (int)$b['item_votes'] : 0;

		if( $aTime === $bTime ){
			return 0;
		}

		return $aTime < $bTime  ? 1 : -1;
	}
	
}


/*
* This function return randomly picked link items of SLD
*/
function qcopd_get_random_links_wi( $limit = null, $category = null, $subtitle = null )
{
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
	$multipage = false;
	if(sld_get_option_page('sld_directory_page')==get_queried_object_id()){
		$multipage = true;
	}
	
	
	if( $limit == null )
	{
		$limit = 5;
	}

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$arrayOfElements = array();

	$list_args = array(
		'post_type' => 'sld',
		'orderby' => 'date',
		'order' => 'desc',
		'posts_per_page' => -1,
	);

	if( isset($category) && !empty($category) ){
		
		$category = explode(',',$category);

		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'ID',
				'terms'    => $category,
			),
		);

		$list_args = array_merge($list_args, array( 'tax_query' => $taxArray ));

	}

	$list_query = new WP_Query( $list_args );

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			$title = get_the_title();
			$id = get_the_ID();
			$category = get_the_terms( $id, 'sld_cat' );   

			foreach( $lists as $list )
			{
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = isset($list['qcopd_item_title']) ? trim($list['qcopd_item_title']) : '';
				$item['item_img'] = $img;
				$item['item_img_icon'] = isset($list['qcopd_fa_icon']) ? trim($list['qcopd_fa_icon']) : '';
				$item['item_subtitle'] = isset($list['qcopd_item_subtitle']) ? trim($list['qcopd_item_subtitle']) : '';
				$item['item_link'] = isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : '';
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab'] = $newtab;
				$item['item_votes'] = $votes;
				$item['item_parent'] = $title;
				$item['item_parent_id'] = $id;
				$item['item_timelaps'] = isset($list['qcopd_timelaps']) ? $list['qcopd_timelaps'] : '';
				
				$item['item_category'] = (!empty($category)?$category[0]->slug:'no_category');

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}

	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_votes");

    shuffle( $arrayOfElements );

    ob_start();
	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 20111;
    $numberOfItems = count( $arrayOfElements );

    echo '<ul class="widget-sld-list">';

     if(sld_get_option('sld_use_global_thumbs_up')!=''){
	     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
	 }else{
	     $sld_thumbs_up = 'fa-thumbs-up';
	 }
    
    foreach( $arrayOfElements as $item ){
		$mainimg = $item['item_img'];
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}
		$iconClass = isset($item['item_img_icon']) ? $item['item_img_icon'] : '';
		
		if($multipage==true && sld_get_option('sld_widget_link_multipage')=='on'){
			$url = home_url().'/'.get_post_field( 'post_name', get_queried_object_id() ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}elseif( sld_get_option('sld_enable_widget_links_to_item_details_page')=='on'){
			$multipage_obj = sld_get_option_page('sld_directory_page');
			$url = home_url().'/'.get_post_field( 'post_name', $multipage_obj ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}else{
			$url = $item['item_link'];
		}
		
    	?>
    	<li id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>">
			<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $url; ?>">

				<?php if( $imgurlnew != "" ) : ?>
				
					<img class="widget-avatar" src="<?php echo $imgurlnew; ?>" alt="">

				<?php elseif( $iconClass != "" ) : ?>
					<span class="list-img">
						<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
					</span>

				<?php else : ?>

					<img class="widget-avatar" src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

				<?php endif; ?>

				<?php echo '<span class="sld-widget-title">'.$item['item_title'].'</span>'; ?>

				<?php if( $enableUpvoting == 'on' ) : ?>

				<div class="widget-vcount">
				
					<div class="upvote-section">
						
						<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on">
							<span class="opening-bracket">
								(
							</span>
							<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
							<span class="upvote-count">
								<?php echo $item['item_votes']; ?>
							</span>
							<span class="closing-bracket">
								)
							</span>
						</span>	
						
					</div>

				</div>

				<?php endif; ?>

			</a>

			<?php 
			if(isset($subtitle) && $subtitle == 'show' ){
				echo '<span class="sld-widget-subtitle">'.$item['item_subtitle'].'</span>'; 
			}

			?>
		</li>
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

    echo '</ul>';

    $content = ob_get_clean();

    return $content;

} //End of qcopd_get_random_links_wi

//Random New

function qcopd_get_random_new( $limit = null )
{
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
	if( $limit == null )
	{
		$limit = 6;
	}

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$arrayOfElements = array();

	$list_args = array(
		'post_type' => 'sld',
		'orderby' => 'date',
		'order' => 'desc',
		'posts_per_page' => -1,
	);

	$list_query = new WP_Query( $list_args );

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			$title = get_the_title();
			$id = get_the_ID();
			$category = get_the_terms( $id, 'sld_cat' ); 

			foreach( $lists as $list )
			{
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = isset($list['qcopd_item_title']) ? trim($list['qcopd_item_title']) : '';
				$item['item_img'] = $img;
				$item['item_img_icon'] = isset($list['qcopd_fa_icon']) ? trim($list['qcopd_fa_icon']) : '';
				$item['item_subtitle'] = isset($list['qcopd_item_subtitle']) ? trim($list['qcopd_item_subtitle']) : '';
				$item['item_link'] 	   = isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : '';
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab']   = $newtab;
				$item['item_votes']    = $votes;
				$item['item_parent']   = $title;
				$item['item_parent_id'] = $id;

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}

	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_votes");

    shuffle( $arrayOfElements );

    ob_start();
	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 20111;
    $numberOfItems = count( $arrayOfElements );

    
    
    foreach( $arrayOfElements as $item ){
		$mainimg = $item['item_img'];
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}
		$iconClass = isset($item['item_img_icon']) ? $item['item_img_icon'] : '';
    	?>
    	<div class="qc-column-4" id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>"><!-- qc-column-4 -->
			<!-- Feature Box 1 -->
			<div class="featured-block ">
				<div class="featured-block-img">
					<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $item['item_link']; ?>"> 
						<?php if( $imgurlnew != "" ) : ?>
					
						<img src="<?php echo $imgurlnew; ?>" alt="">
						
						<?php elseif( $iconClass != "" ) : ?>
							<span class="list-img">
								<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
							</span>

						<?php else : ?>

							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

						<?php endif; ?>
					</a>
				</div>
				<div class="featured-block-info">
					<h4><a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $item['item_link']; ?>"><?php echo $item['item_title']; ?> </a></h4>

				</div>
				<?php if( $enableUpvoting == 'on' ) : ?>
				<div class="featured-block-upvote upvote-section">
					<span class="upvote-count count"> <?php echo $item['item_votes']; ?> </span>
					<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on"><i class="fa fa-thumbs-up"></i> </span>
				</div>
				<?php endif; ?>
			</div>
		</div><!--/qc-column-4 -->
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

    

    $content = ob_get_clean();

    return $content;

}


/*
* This function return the most recent link items of SLD
*/
function qcopd_get_latest_links_wi( $limit = null, $category = null, $subtitle = null )
{
	
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

	$multipage = false;
	if(sld_get_option_page('sld_directory_page')==get_queried_object_id()){
		$multipage = true;
	}

	
	if( $limit == null )
	{
		$limit = 5;
	}

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$arrayOfElements = array();

	$list_args = array(
		'post_type' => 'sld',
		'orderby' 	=> 'date',
		'order' 	=> 'desc',
		'posts_per_page' => -1,
	);

	if( isset($category) && !empty($category) ){
		
		$category = explode(',',$category);

		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'ID',
				'terms'    => $category,
			),
		);

		$list_args = array_merge($list_args, array( 'tax_query' => $taxArray ));

	}


	$list_query = new WP_Query( $list_args );

	 if(sld_get_option('sld_use_global_thumbs_up')!=''){
	     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
	 }else{
	     $sld_thumbs_up = 'fa-thumbs-up';
	 }

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			
			$title = get_the_title();
			$id = get_the_ID();
			$category = get_the_terms( $id, 'sld_cat' ); 

			foreach( $lists as $list )
			{
				
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src(@$list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = isset($list['qcopd_item_title']) ? trim($list['qcopd_item_title']) : '';
				$item['item_img'] = $img;
				$item['qcopd_fa_icon'] = isset($list['qcopd_fa_icon']) ? trim($list['qcopd_fa_icon']) : '';
				$item['item_subtitle'] = isset($list['qcopd_item_subtitle']) ? trim($list['qcopd_item_subtitle']) : '';
				$item['item_link'] = isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : '';
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab'] = $newtab;
				$item['item_votes'] = $votes;
				$item['item_parent'] = $title;
				$item['item_parent_id'] = $id;

				$item['item_time'] = '0';

				$item['item_category'] = (!empty($category)?$category[0]->slug:'no_category');
				$item['item_timelaps'] = $list['qcopd_timelaps'];

				if( isset($list['qcopd_timelaps']) && $list['qcopd_timelaps'] != "" )
				{
					$item['item_time'] = $list['qcopd_timelaps'];
				}

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}
	
	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_entry_time");

    ob_start();
	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 30111;

    $arrayOfElements = sld_remove_duplicate_array_link_item($arrayOfElements, 'item_link');

    $numberOfItems = count( $arrayOfElements );

    echo '<ul class="widget-sld-list">';
    
    foreach( $arrayOfElements as $item ){
		
		$mainimg = $item['item_img'];
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}

		if($multipage==true && sld_get_option('sld_widget_link_multipage')=='on'){
			$url = home_url().'/'.get_post_field( 'post_name', get_queried_object_id() ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}elseif( sld_get_option('sld_enable_widget_links_to_item_details_page')=='on'){
			$multipage_obj = sld_get_option_page('sld_directory_page');
			$url = home_url().'/'.get_post_field( 'post_name', $multipage_obj ).'/'.$item['item_category'].'/'.str_replace('-',' ',strtolower($item['item_parent'])).'/'.urlencode(str_replace(' ','-',strtolower($item['item_title']))).'/'.$item['item_timelaps'];
		}else{
			$url = $item['item_link'];
		}
		
    	?>
    	<li id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>">
			<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $url; ?>">

				<?php 

				$iconClass = isset($item['qcopd_fa_icon']) ? $item['qcopd_fa_icon'] : "";

				if( $imgurlnew != "" ) : ?>

					<img class="widget-avatar" src="<?php echo $imgurlnew; ?>" alt="">

				<?php elseif( $iconClass != "" ) : ?>
				
					<span class="icon fa-icon">
						<i class="fa <?php echo $iconClass; ?>"></i>
					</span>

				<?php else : ?>

					<img class="widget-avatar" src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

				<?php endif; ?>

				<?php echo '<span class="sld-widget-title">'.$item['item_title'].'</span>'; ?>

				<?php if( $enableUpvoting == 'on' ) : ?>

				<div class="widget-vcount">
				
					<div class="upvote-section">
						
						<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on">
							<span class="opening-bracket">
								(
							</span>
							<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
							<span class="upvote-count">
								<?php
									//echo $item['item_votes'];
									$sld_shorten_upvote = sld_get_option('sld_upvote_shorten_number');
								  	if( $sld_shorten_upvote == 'on' ){
								  		echo apply_filters('sld_shorten_upvote_number', qc_sld_shorten($item['item_votes']));
								  	}else{
								  		echo (int)$item['item_votes'];
								  	}
								?>
							</span>
							<span class="closing-bracket">
								)
							</span>
						</span>	
						
					</div>

				</div>

				<?php endif; ?>

			</a>

			<?php 
			if(isset($subtitle) && $subtitle == 'show' ){
				echo '<span class="sld-widget-subtitle">'.$item['item_subtitle'].'</span>'; 
			}

			?>
		</li>
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

    echo '</ul>';

    $content = ob_get_clean();

    return $content;

} //End of qcopd_get_latest_links_wi

//get latest new style
function qcopd_get_latest_new( $limit = null )
{
	
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
	if( $limit == null )
	{
		$limit = 6;
	}

	$enableUpvoting = sld_get_option( 'sld_enable_widget_upvote' );

	$arrayOfElements = array();

	$list_args = array(
		'post_type' => 'sld',
		'orderby' => 'date',
		'order' => 'desc',
		'posts_per_page' => -1,
	);

	$list_query = new WP_Query( $list_args );

	if( $list_query->have_posts() )
	{
		$count = 0;
		
		while ( $list_query->have_posts() ) 
		{
			$list_query->the_post();

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
			$lists = sldmodifyupvotes(get_the_ID(), $lists);
			
			$title = get_the_title();
			$id = get_the_ID();

			foreach( $lists as $list )
			{
				
				$img = "";
				$newtab = 0;
				$nofollow = 0;
				$votes = 0;

				$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";
				
				$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";
				
				if( $showFavicon == 1 )
				{
					if( $directImgLink != '' )
					{
						$img = trim($directImgLink);
					}else{
						$img = wp_get_attachment_image_src($list['qcopd_item_img']);
					}
				}else{
					$img = wp_get_attachment_image_src($list['qcopd_item_img']);
				}

				if( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1 ) 
				{
					$nofollow = 1;
				}

				if( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1 ) 
				{
					$newtab = 1;
				}

				if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 )
				{
			  	  $votes = (int)$list['qcopd_upvote_count'];
			    }

				$item['item_title'] = trim($list['qcopd_item_title']);
				$item['item_img'] = $img;
				$item['item_img_icon'] = trim($list['qcopd_fa_icon']);
				$item['item_subtitle'] = trim($list['qcopd_item_subtitle']);
				$item['item_link'] = $list['qcopd_item_link'];
				$item['item_nofollow'] = $nofollow;
				$item['item_newtab'] = $newtab;
				$item['item_votes'] = $votes;
				$item['item_parent'] = $title;
				$item['item_parent_id'] = $id;

				$item['item_time'] = '0';

				if( isset($list['qcopd_timelaps']) && $list['qcopd_timelaps'] != "" )
				{
					$item['item_time'] = $list['qcopd_timelaps'];
				}

				array_push($arrayOfElements, $item);

			}

			$count++;
		}
		wp_reset_query();
	}
	else
	{
		return __('No list elements was found.', 'qc-opd');
	}
	
	// Sort the multidimensional array
    usort($arrayOfElements, "custom_sort_by_entry_time");

    ob_start();
	//echo '<link rel="stylesheet" type="text/css" href="'.SLD_QCOPD_ASSETS_URL.'/css/directory-style.css" />';
    $count = 1;
    $listCount = 30111;
    $numberOfItems = count( $arrayOfElements );

    
    
    foreach( $arrayOfElements as $item ){
		
		$mainimg = isset($item['item_img']) ? $item['item_img'] : '';
		$imgurlnew = '';

		if( !empty($mainimg) && is_array($mainimg) && @array_key_exists(0,$mainimg)){
			$imgurlnew = $mainimg[0];
		}else{
			$imgurlnew = $mainimg;
		}
		$iconClass = isset($item['item_img_icon']) ? $item['item_img_icon'] : '';
		
    	?>
		
		
		<div class="qc-column-4" id="item-<?php echo $item['item_parent_id'] ."-". $listCount; ?>"><!-- qc-column-4 -->
			<!-- Feature Box 1 -->
			<div class="featured-block ">
				<div class="featured-block-img">
					<a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $item['item_link']; ?>"> 
						<?php if( $imgurlnew != "" ) : ?>
					
						<img src="<?php echo $imgurlnew; ?>" alt="">

						<?php elseif( $iconClass != "" ) : ?>
							<span class="list-img">
								<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
							</span>

						<?php else : ?>

							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">

						<?php endif; ?>
					</a>
				</div>
				<div class="featured-block-info">
					<h4><a <?php echo (isset($item['item_nofollow']) && $item['item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> <?php echo (isset($item['item_newtab']) && $item['item_newtab'] == 1) ? 'target="_blank"' : ''; ?> href="<?php echo $item['item_link']; ?>"><?php echo $item['item_title']; ?> </a></h4>

				</div>
				<?php if( $enableUpvoting == 'on' ) : ?>
				<div class="featured-block-upvote upvote-section">
					<span class="upvote-count count"> <?php echo $item['item_votes']; ?> </span>
					<span data-post-id="<?php echo $item['item_parent_id']; ?>" data-item-title="<?php echo $item['item_title']; ?>" data-item-link="<?php echo $item['item_link']; ?>" class="upvote-btn upvote-on"><i class="fa fa-thumbs-up"></i> </span>
				</div>
				<?php endif; ?>
			</div>
		</div><!--/qc-column-4 -->
		
    	<?php

    	if( $numberOfItems > $limit )
    	{
    		if( $limit == $count )
    		{
    			break;
    		} //if $limit == $count

    	} //if $numberOfItems > $limit

    	$count++;
    	$listCount++;

    } //End Foreach

    

    $content = ob_get_clean();

    return $content;

}



function custom_sort_by_entry_time($a, $b) {
    //return ( $a['item_time'] ) < ( $b['item_time'] );
	$aTime = isset($a['item_time']) && !empty( $a['item_time'] ) ? (int)$a['item_time'] : 0;
	$bTime = isset($b['item_time']) && !empty( $b['item_time'] ) ? (int)$b['item_time'] : 0;

	if( $aTime === $bTime ){
		return 0;
	}

	return $aTime < $bTime  ? 1 : -1;
}

function sld_new_expired($id){
	//global $wpdb;
	//$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = ".$id." AND meta_key = 'qcopd_list_item01' order by `meta_id` ASC");
	// var_dump( $results );
	// wp_die();

	$metas = get_post_meta($id, 'qcopd_list_item01');
	$expire = sld_get_option('sld_new_expire_after');
	$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));
	
	$rearrange = array();
	foreach($metas as $meta){

		if(isset($meta['qcopd_entry_time']) && strtotime($meta['qcopd_entry_time']) < strtotime($expire_date)){
			
			if(isset($meta['qcopd_new'])){
				unset($meta['qcopd_new']);
			}
			$rearrange[] = $meta;
		}else{
			$rearrange[] = $meta;
		}
	}
	
	delete_post_meta($id, 'qcopd_list_item01');
	foreach($rearrange as $meta_data){
		add_post_meta( $id, 'qcopd_list_item01', $meta_data );
	}

}


function sldmodifyupvotes($id, $lists){
	global $wpdb;
	$utable = $wpdb->prefix.'sld_ip_table';
	$expire = sld_get_option('sld_upvote_expire_after');
	
	if($expire!='' && (int)$expire>0){
		
		$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));
		$newArray = array();
		
		foreach($lists as $list){
			
			$subArray = array();
			
			foreach($list as $k=>$v){
				
				if($k=='qcopd_upvote_count'){
					
					$item_id = $id.'_'.$list['qcopd_timelaps'];
					
					$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $utable WHERE item_id = '$item_id' and time > '$expire_date'");
					$subArray[$k] = $rowcount;
					
				}else{
					$subArray[$k] = $v;
				}
			}
			$newArray[] = $subArray;
		}
		return $newArray;
		
	}else{
		return $lists;
	}
	
}

function sld_featured_at_top($lists){
	
	$featured = array();
	foreach($lists as $k=>$v){
		if(isset($v['qcopd_featured']) and $v['qcopd_featured']==1){
			unset($lists[$k]);
			$featured[] = $v;
		}
	}
	
	return array_merge($featured,$lists);
}


function sld2_get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $content;
}
function sld_show_tag_filter($category='', $shortcodeAtts=array()){
	$args = array(
		'numberposts' => -1,
		'post_type'   => 'sld',
	);
	
	if($category!=''){
		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);

		$args = array_merge($args, array( 'tax_query' => $taxArray ));
	}
	if( $shortcodeAtts['mode'] == 'one' ){
		if( isset($shortcodeAtts['list_id']) && !empty($shortcodeAtts['list_id']) ){
			$args['post__in'] = array($shortcodeAtts['list_id']);
		}
	}
	$listItems = get_posts( $args );
	$tags = array();
	foreach($listItems as $item){
		$configs = get_post_meta( $item->ID, 'qcopd_list_item01' );
		foreach($configs as $config){
			if(isset($config['qcopd_tags']) && $config['qcopd_tags']!=''){				
				foreach(explode(',',$config['qcopd_tags']) as $itm){
					$itm = trim($itm);
					if(!in_array($itm, $tags)){
						array_push($tags, $itm);
					}
				}				
			}
		}
	}

	// function sld_trim_space($item) {
	//     return trim($item);
	// }

	// $tags = array_map('sld_trim_space', $tags);

	$sld_order_tag_alphabetically = sld_get_option('sld_order_tag_alphabetically');
	$sort_tag_class = 'sld-sort-tag-none';
	if( isset($sld_order_tag_alphabetically) && ($sld_order_tag_alphabetically == 'asc') ){
		$sort_tag_class = 'sld-sort-tag-asc';
		sort($tags);
	}elseif( isset($sld_order_tag_alphabetically) && ($sld_order_tag_alphabetically == 'desc') ){
		$sort_tag_class = 'sld-sort-tag-desc';
		rsort($tags);
	}
	
	?>
	<div class="sld-tag-filter-area <?php echo $sort_tag_class; ?> <?php if( sld_get_option('sld_enable_tag_filter_dropdown_mobile') == 'on' ){echo 'sld-tag-filter-hide-mobile'; } ?>">
		<?php echo (sld_get_option('sld_lan_tags')!=''?sld_get_option('sld_lan_tags'):__('Tags', 'qc-opd')); ?>: 
		<a class="sld_tag_filter" data-tag=""><?php echo (sld_get_option('sld_lan_all')!=''? esc_attr(sld_get_option('sld_lan_all')):__('All', 'qc-opd')); ?></a>
		<?php foreach($tags as $tag): ?>
		<a class="sld_tag_filter" data-tag="<?php echo esc_attr(trim($tag)); ?>"><?php echo esc_html(trim($tag)); ?></a>
		<?php endforeach; ?>
	
	</div>
	<?php
	if( sld_get_option('sld_enable_tag_filter_dropdown_mobile') == 'on' ){
		echo '<div class="sld_tag_filter_dropdown_mobile">';
			sld_show_tag_filter_dropdown($args);
		echo '</div>';
	}
	
}

function sld_show_tag_filter_dropdown($args){
	if(!empty($args)){
		$listItems = get_posts( $args );
		$tags = array();
		foreach($listItems as $item){
			$configs = get_post_meta( $item->ID, 'qcopd_list_item01' );
			foreach($configs as $config){
				if(isset($config['qcopd_tags']) && $config['qcopd_tags']!=''){				
					foreach(explode(',',$config['qcopd_tags']) as $itm){
						if(!in_array($itm, $tags)){
							array_push($tags, $itm);
						}
					}				
				}
			}
		}
		sort($tags);
		?>
		<div class="sld_tag_filter_dropdown">
			<span class="sld_tag_filter_dropdown_label"><?php echo (sld_get_option('sld_lan_tags')!=''?sld_get_option('sld_lan_tags'):__('Tags', 'qc-opd')); ?> </span> 
			<select class="sld_tag_filter_select">
				<option value=""><?php echo (sld_get_option('sld_lan_all')!=''?sld_get_option('sld_lan_all'):__('All', 'qc-opd')); ?></option>
				<?php foreach($tags as $tag): ?>
				<option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}
}

function sld_widget_tab_style($limit=6){
	wp_enqueue_style('sld-style_widget_tab-css', SLD_QCOPD_ASSETS_URL . "/css/style_widget_tab.css" );
?>

<div class="qc_tab_container">
	
    <div class="qc_tab_style clearfix-div">
      <button class="qc_tablinks active" onclick="qcTab(event, 'qc_tab_01')"><?php esc_html_e( 'Latest', 'qc-opd' ); ?></button>
      <button class="qc_tablinks" onclick="qcTab(event, 'qc_tab_02')"><?php esc_html_e( 'Popular', 'qc-opd' ); ?> </button>
      <button class="qc_tablinks" onclick="qcTab(event, 'qc_tab_03')"><?php esc_html_e( 'Random', 'qc-opd' ); ?></button>
    </div>
    
    <div id="qc_tab_01" class="qc_tabcontent clearfix-div" style="display:block">
    	<div class="qc-row widget-sld-list">
		
           <?php echo qcopd_get_latest_new($limit); ?>

        </div>
        <!--qc row-->
    </div>
    
    <div id="qc_tab_02" class="qc_tabcontent clearfix-div" style="display:none">
		<?php echo qcopd_get_popular_new($limit); ?>
    </div>
    
    <div id="qc_tab_03" class="qc_tabcontent clearfix-div" style="display:none">
    	 <?php echo qcopd_get_random_new($limit); ?>
    </div>

</div>

<script>
function qcTab(evt, qc_id) {
    var i, qc_tabcontent, qc_tablinks;
    qc_tabcontent = document.getElementsByClassName("qc_tabcontent");
    for (i = 0; i < qc_tabcontent.length; i++) {
        qc_tabcontent[i].style.display = "none";
    }
    qc_tablinks = document.getElementsByClassName("qc_tablinks");
    for (i = 0; i < qc_tablinks.length; i++) {
        qc_tablinks[i].className = qc_tablinks[i].className.replace(" active", "");
    }
    document.getElementById(qc_id).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>
<?php
}

// Package Expire Notification

function sld_send_package_expire_notification($package){
	
	global $wpdb;

	$package_table = $wpdb->prefix.'sld_package';
	
	$user = get_user_by( 'ID', $package->user_id );
	$package_info = $wpdb->get_row( $wpdb->prepare( "select * from $package_table where 1 and id = %d", $package->id ) );
	
	$email_content = sld_get_option('sld_package_expire_email');
	$email_content = str_replace(array('#user_name','#package_name','#expire_date','#create_date'),array($user->user_login, $package_info->title, date("d/m/Y", strtotime($package->expire_date)), date("d/m/Y", strtotime($package->date))),$email_content);

	@wp_mail($user->user_email, sprintf(__('[%s] Your package has been expired! %s!'), get_option('blogname')), $email_content);
	
}

function sld_remove_duplicate_master($post_id){
	if(sld_get_option('sld_auto_remove_dup_item')=='on'){
		if($post_id!=''){
			$datas = get_post_meta($post_id, 'qcopd_list_item01');
			$tempArr = array_unique(array_column($datas, 'qcopd_item_title'));
			$new_datas = array_intersect_key($datas, $tempArr);
			if(count($datas) != count($new_datas) and count($datas)>count($new_datas)){
				delete_post_meta($post_id, 'qcopd_list_item01');
				foreach($new_datas as $value){
					add_post_meta( $post_id, 'qcopd_list_item01', $value );
				}
			}
		}
	}
	

}


function sld_get_user_details_by_item_timelaps( $timelap ){
	global $wpdb;

	$user_entry = $wpdb->prefix.'sld_user_entry';
	$user_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT user_id from $user_entry where custom = %d", $timelap 
		)
	);
	if( $user_id ){
		$userdata = get_user_by( 'id', $user_id );
		if( $userdata ){
			return $userdata;
		}
	}

	return false;
}


function sld_Modify_Single_List_Upvotes($id, $list){
	global $wpdb;
	$utable = $wpdb->prefix.'sld_ip_table';
	$expire = sld_get_option('sld_upvote_expire_after');
	
	if($expire!='' && (int)$expire>0){
		
		$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));

			
			foreach($list as $k=>$v){
				
				if($k=='qcopd_upvote_count'){
					
					$item_id = $id.'_'.$list['qcopd_timelaps'];
					
					$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $utable WHERE item_id = '$item_id' and time > '$expire_date'");
					$subArray[$k] = $rowcount;
					
				}else{
					$subArray[$k] = $v;
				}
			}
			return $subArray;
	}else{
		return $list;
	}
	
}

function sld_remove_duplicate_array_link_item( $array, $key ){
	$temp_array = [];
   foreach ($array as &$v) {
       if (!isset($temp_array[$v[$key]]))
       $temp_array[$v[$key]] =& $v;
   }
   $array = array_values($temp_array);
	return $array;
}


function qcsld_pagination_links( $qcsld_paged=1, $total_pagination_page=0, $post_id=0){
	if(  $total_pagination_page > 0 ){	
		$pagination_links = qcsld_paginate_links(array(
	        'current'=>max(1,$qcsld_paged),
	        'total'=>$total_pagination_page,
	        'type'=>'array', //default it will return anchor,
	        'mid_size'=> 1,
	        'list_id' => $post_id,
	        'end_size'=> 3,
	        'format' => '?qcsld_paged=%#%',
	        'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
	    ));

		echo "<div class='sld-page-numbers-container'>\n\t";
		// join( "</div>\n\t<div class='sld-page-numbers-item'>", $pagination_links );
		foreach ($pagination_links as $links) {
	    	echo ( "<div class='sld-page-numbers-item'>". $links."</div>\n\t" );
		}
	    echo "</div>\n";
	}
}

function qcsld_paginate_links( $args = '' ) {
    global $wp_query, $wp_rewrite;
 
    // Setting up default values based on the current URL.
    $pagenum_link = html_entity_decode( get_pagenum_link() );
    $url_parts    = explode( '?', $pagenum_link );
 
    // Get max pages and current page out of the current query, if available.
    $total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
    $current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
    
    $qcsld_list_id = isset($_GET['qcsld_list_id']) && intval( $_GET['qcsld_list_id'] > 0 ) ? sanitize_text_field($_GET['qcsld_list_id']) : 0;

    // Append the format placeholder to the base URL.
    $pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';
 
    // URL base depends on permalink settings.
    $format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
    $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';
 
    $defaults = array(
        'base'               => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
        'format'             => $format, // ?page=%#% : %#% is replaced by the page number
        'total'              => $total,
        'current'            => $current,
        'aria_current'       => 'page',
        'show_all'           => false,
        'prev_next'          => true,
        'prev_text'          => __( '&laquo; Previous' ),
        'next_text'          => __( 'Next &raquo;' ),
        'end_size'           => 1,
        'mid_size'           => 2,
        'type'               => 'plain',
        'add_args'           => array(), // array of query args to add
        'add_fragment'       => '',
        'before_page_number' => '',
        'after_page_number'  => '',
    );
 
    $args = wp_parse_args( $args, $defaults );
 
    if ( ! is_array( $args['add_args'] ) ) {
        $args['add_args'] = array();
    }
 
    // Merge additional query vars found in the original URL into 'add_args' array.
    if ( isset( $url_parts[1] ) ) {
        // Find the format argument.
        $format       = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
        $format_query = isset( $format[1] ) ? $format[1] : '';
        wp_parse_str( $format_query, $format_args );
 
        // Find the query args of the requested URL.
        wp_parse_str( $url_parts[1], $url_query_args );
 
        // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
        foreach ( $format_args as $format_arg => $format_arg_value ) {
            unset( $url_query_args[ $format_arg ] );
        }
 
        $args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
    }
 
    // Who knows what else people pass in $args
    $total = (int) $args['total'];
    if ( $total < 2 ) {
        return;
    }
    $current  = (int) $args['current'];
    $end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
    if ( $end_size < 1 ) {
        $end_size = 1;
    }
    $mid_size = (int) $args['mid_size'];
    if ( $mid_size < 0 ) {
        $mid_size = 2;
    }
 
    $add_args   = $args['add_args'];
    $r          = '';
    $page_links = array();
    $dots       = false;
 
    if ( $args['prev_next'] && $current && 1 < $current ) :
        $link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
        $link = str_replace( '%#%', $current - 1, $link );
        if ( $add_args ) {
            $link = add_query_arg( $add_args, $link );
        }
        $link .= $args['add_fragment'];
 
        $page_links[] = sprintf(
            '<a class="prev page-numbers" href="%s">%s</a>',
            /**
             * Filters the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * @param string $link The paginated link URL.
             */
            esc_url( apply_filters( 'paginate_links', $link ) ),
            $args['prev_text']
        );
    endif;

    for ( $n = 1; $n <= $total; $n++ ) :
        if ( $n == $current && $qcsld_list_id == $args['list_id'] ) :
            $page_links[] = sprintf(
                '<span aria-current="%s" class="page-numbers current">%s</span>',
                esc_attr( $args['aria_current'] ),
                $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number']
            );
 
            $dots = true;
        else :
            if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
                $link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
                $link = str_replace( '%#%', $n, $link );
                if ( $add_args ) {
                    $link = add_query_arg( $add_args, $link );
                }
                $link .= $args['add_fragment'];
 
                $page_links[] = sprintf(
                    '<a class="page-numbers" href="%s">%s</a>',
                    /** This filter is documented in wp-includes/general-template.php */
                    esc_url( apply_filters( 'paginate_links', $link ) ),
                    $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number']
                );
 
                $dots = true;
            elseif ( $dots && ! $args['show_all'] ) :
                $page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
 
                $dots = false;
            endif;
        endif;
    endfor;
 
    if ( $args['prev_next'] && $current && $current < $total ) :
        $link = str_replace( '%_%', $args['format'], $args['base'] );
        $link = str_replace( '%#%', $current + 1, $link );
        if ( $add_args ) {
            $link = add_query_arg( $add_args, $link );
        }
        $link .= $args['add_fragment'];
 
        $page_links[] = sprintf(
            '<a class="next page-numbers" href="%s">%s</a>',
            /** This filter is documented in wp-includes/general-template.php */
            esc_url( apply_filters( 'paginate_links', $link ) ),
            $args['next_text']
        );
    endif;
 
    switch ( $args['type'] ) {
        case 'array':
            return $page_links;
 
        case 'list':
            $r .= "<ul class='page-numbers'>\n\t<li>";
            $r .= join( "</li>\n\t<li>", $page_links );
            $r .= "</li>\n</ul>\n";
            break;
 
        default:
            $r = join( "\n", $page_links );
            break;
    }
 
    return $r;
}