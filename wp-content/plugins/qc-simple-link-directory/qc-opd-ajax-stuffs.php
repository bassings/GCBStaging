<?php

add_action('wp_head', 'sld_qcopd_ajax_ajaxurl');
add_action('admin_head', 'sld_qcopd_ajax_ajaxurl');

function sld_qcopd_ajax_ajaxurl()
{

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

//Doing ajax action stuff

function sld_upvote_ajax_action_stuff(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
    //Get posted items

    $action 	= isset($_POST['action']) ? sanitize_text_field($_POST['action']):'';
    $post_id 	= isset($_POST['post_id']) ? absint(sanitize_text_field($_POST['post_id'])): '';
    $meta_title = isset($_POST['meta_title']) ? sanitize_text_field($_POST['meta_title']): '';
    $meta_link 	= isset($_POST['meta_link']) ? esc_url_raw($_POST['meta_link']): '';
    $li_id 		= isset($_POST['li_id']) ? sanitize_text_field($_POST['li_id']) : '';
    $uniqueid 	= isset($_POST['uniqueid']) ? trim($_POST['uniqueid']) : '';
    $security 	= isset($_POST['security']) ? trim($_POST['security']) : '';
	
	if(isset($_COOKIE['usnidg']) && $_COOKIE['usnidg']!=$security){
		wp_die();
	}
	
    //Check wpdb directly, for all matching meta items
    global $wpdb;
	$utable = $wpdb->prefix.'sld_ip_table';
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ));

    //Defaults
    $votes = 0;

    $data['votes'] = 0;
    $data['vote_status'] = 'failed';

    if( isset($_COOKIE['voted_li'])){
    	$exists = in_array("$uniqueid", $_COOKIE['voted_li']);
    }else{
    	$exists = false;
    }
	$userip = sld_get_the_user_ip();
    //If li-id not exists in the cookie, then prceed to vote
	
    if (!$exists || sld_get_option('sld_upvote_restrict_by_ip')=='on') {
		
		if(sld_get_option('sld_upvote_restrict_by_ip')=='on'){
			
			$oldate = date('Y-m-d H:i:s',strtotime("-1 days"));
			
			//checking with ip block
			$ipblocks = array();
			$ipblocks = explode('.',$userip);
			if( isset($ipblocks) && !empty($ipblocks) && is_array($ipblocks) && sizeof($ipblocks)>2){
				$ipblocks = array_pop($ipblocks);
				if( is_array($ipblocks) ){
					$userid = implode('.', $ipblocks);
				}else{
					$userid = $ipblocks;
				}
			}
			
			
			$find = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $utable WHERE 1 and item_id=%d and `ip` like '%".$userip."%' and time > '$oldate'", $uniqueid ) );
			
			if(!empty($find)){
				if( isset($_COOKIE['voted_li']) ){
					$data['cookies'] = $_COOKIE['voted_li'];
				}else{
					$data['cookies'] = '';
				}
				echo json_encode($data);
				wp_die(); // stop executing script
			}
			
		}
		
		if(sld_get_option('sld_upvote_user_login')=='on' && !is_user_logged_in()){
			wp_die();
		}
		
		
        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = maybe_unserialize($value->meta_value);

            //If meta title and link matches with unserialized data
			
            if (trim($unserialized['qcopd_item_title']) == wp_unslash(trim($meta_title)) && trim($unserialized['qcopd_item_link']) == trim($meta_link)) {

                $metaId = $meta_id;

                //Defaults for current iteration
                $upvote_count = 0;
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if ( !empty($unserialized) && is_array($unserialized) && array_key_exists('qcopd_upvote_count', $unserialized)) {
                    $upvote_count = (int)$unserialized['qcopd_upvote_count'];
					
					$expire = sld_get_option('sld_upvote_expire_after');
					if($expire!='' && (int)$expire>0){
						$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));
						$item_id = $post_id.'_'.$unserialized['qcopd_timelaps'];
						$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $utable WHERE item_id = '$item_id' and time > '$expire_date'");
						$upvote_count = $rowcount;
					}
					
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_upvote_count') {
                            $new_array[$key] = $upvote_count + 1;
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                if (!$flag) {
                    $new_array['qcopd_upvote_count'] = $upvote_count + 1;
                }

                $votes = (int)$new_array['qcopd_upvote_count'];

                $updated_value = serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

                $voted_li = array("$uniqueid");

                $total = 0;

                if( isset($_COOKIE['voted_li']) ){
                	$total = count($_COOKIE['voted_li']);
                }

                $total = $total + 1;

				
				$wpdb->insert(
					$utable,
					array(
						'item_id'=> $uniqueid,
						'ip'	=> $userip,
						'time'  => date('Y-m-d H:i:s')
					)
				);
				
				if(sld_get_option('sld_upvote_restrict_by_ip')!='on'){
					setcookie("voted_li[$total]", $uniqueid, time() + (86400 * 7), "/");
				}

                $data['vote_status'] = 'success';
                $data['votes'] = $votes;

            }

        }
		
    }

    if( isset($_COOKIE['voted_li']) ){
    	$data['cookies'] = $_COOKIE['voted_li'];
    }else{
    	$data['cookies'] = '';
    }

    echo json_encode($data);


    wp_die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_upvote_action', 'sld_upvote_ajax_action_stuff'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_upvote_action', 'sld_upvote_ajax_action_stuff'); // ajax for not logged in users

function sld_upvote_ajax_action_stuff2()
{

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
    //Get posted items
    $action 	= isset($_POST['action']) ? sanitize_text_field($_POST['action']):'';
    $post_id 	= isset($_POST['post_id']) ? absint(sanitize_text_field($_POST['post_id'])): '';
    $meta_title = isset($_POST['meta_title']) ? sanitize_text_field($_POST['meta_title']): '';
    $meta_link 	= isset($_POST['meta_link']) ? esc_url_raw($_POST['meta_link']): '';
    $li_id 		= isset($_POST['li_id']) ? sanitize_text_field($_POST['li_id']) : '';
    $uniqueid 	= isset($_POST['uniqueid']) ? trim($_POST['uniqueid']) : '';
    $security 	= isset($_POST['security']) ? trim($_POST['security']) : '';

	
	if(isset($_COOKIE['usnidg']) && $_COOKIE['usnidg']!=$security){
		wp_die();
	}
	
    //Check wpdb directly, for all matching meta items
    global $wpdb;
	$utable = $wpdb->prefix.'sld_ip_table';
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ) );

    //Defaults
    $votes = 0;

    $data['votes'] = 0;
    $data['vote_status'] = 'failed';

    if( isset($_COOKIE['voted_lii']) ){
    	$exists = in_array("$uniqueid", $_COOKIE['voted_lii']);
    }else{
    	$exists = false;
    }
	$userip = sld_get_the_user_ip();
    //If li-id not exists in the cookie, then prceed to vote
	
    if (!$exists || sld_get_option('sld_upvote_restrict_by_ip')=='on') {
		
		if(sld_get_option('sld_upvote_restrict_by_ip')=='on'){
			
			$oldate = date('Y-m-d H:i:s',strtotime("-1 days"));
			
			//checking with ip block
			$ipblocks = explode('.',$userip);
			if( is_array($ipblocks) && sizeof($ipblocks)>2){
				$userid = implode('.',array_pop($ipblocks));
			}
			
			
			$find = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $utable WHERE 1 and item_id=%d and `ip` like '%".$userip."%' and time > '$oldate'", $uniqueid ) );
			
			if(!empty($find)){
				
				if( isset($_COOKIE['voted_lii']) ){
					$data['cookies'] = $_COOKIE['voted_lii'];
				}else{
					$data['cookies'] = '';
				}
				echo json_encode($data);
				wp_die(); // stop executing script
			}
			
		}
		
		if(sld_get_option('sld_upvote_user_login')=='on' && !is_user_logged_in()){
			wp_die();
		}
		
		
        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = maybe_unserialize($value->meta_value);

            //If meta title and link matches with unserialized data
			
            if (trim($unserialized['qcopd_item_title']) == wp_unslash(trim($meta_title)) && trim($unserialized['qcopd_item_link']) == trim($meta_link)) {

                $metaId = $meta_id;

                //Defaults for current iteration
                $upvote_count = 0;
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if ( !empty($unserialized) && is_array($unserialized) && array_key_exists('qcopd_upvote_count', $unserialized)) {
                    $upvote_count = (int)$unserialized['qcopd_upvote_count'];
					
					$expire = sld_get_option('sld_upvote_expire_after');
					if($expire!='' && (int)$expire>0){
						$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));
						$item_id = $post_id.'_'.$unserialized['qcopd_timelaps'];
						$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $utable WHERE item_id = '$item_id' and time > '$expire_date'");
						$upvote_count = $rowcount;
					}
					
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_upvote_count') {
                            $new_array[$key] = $upvote_count + 1;
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                if (!$flag) {
                    $new_array['qcopd_upvote_count'] = $upvote_count + 1;
                }

                $votes = isset($new_array['qcopd_upvote_count']) ? (int)$new_array['qcopd_upvote_count'] : 0;

                $updated_value = maybe_serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

                $voted_li = array("$uniqueid");

                $total = 0;
                if( isset($_COOKIE['voted_lii']) ){
                	$total = count($_COOKIE['voted_lii']);
                }
                $total = $total + 1;

				
				$wpdb->insert(
					$utable,
					array(
						'item_id'=> $uniqueid,
						'ip'	=> $userip,
						'time'  => date('Y-m-d H:i:s')
					)
				);
				
				if(sld_get_option('sld_upvote_restrict_by_ip')!='on'){
					setcookie("voted_lii[$total]", $uniqueid, time() + (86400 * 7), "/");
				}

                $data['vote_status'] = 'success';
                $data['votes'] = $votes;

            }

        }
		
    }

    if(isset( $_COOKIE['voted_lii'] ) ){
    	$data['cookies'] = $_COOKIE['voted_lii'];
    }else{
    	$data['cookies'] = '';
    }

    echo json_encode($data);


    wp_die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_upvote_action2', 'sld_upvote_ajax_action_stuff2'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_upvote_action2', 'sld_upvote_ajax_action_stuff2'); // ajax for not logged in users

//captcha image change script
function qcld_sld_change_captcha(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	session_start();
	if(isset($_SESSION['captcha'])){
		unset($_SESSION['captcha']);
	}
	$_SESSION['captcha'] = sld_simple_php_captcha();
	echo $_SESSION['captcha']['image_src'];
	wp_die();
}

add_action('wp_ajax_qcld_sld_change_captcha', 'qcld_sld_change_captcha'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_change_captcha', 'qcld_sld_change_captcha'); // ajax for not logged in users


function qcld_sld_loadmore_function(){

	if(sld_get_option('sld_use_global_thumbs_up')!=''){
		$sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
	}else{
		$sld_thumbs_up = 'fa-thumbs-up';
	}

	
	
	$paged 			= isset($_POST['page']) ? $_POST['page'] : '';
	$column 		= isset($_POST['column']) ? $_POST['column'] : '';
	$upvote 		= isset($_POST['upvote']) ? $_POST['upvote'] : '';
	$itemperpage 	= isset($_POST['itemperpage']) ? $_POST['itemperpage'] : '';
	$item_count 	= isset($_POST['itemcount']) ? $_POST['itemcount'] : '';
	

	$list_args = array(
		'post_type' 		=> 'sld',
		'posts_per_page' 	=> $itemperpage,
		'paged'				=> $paged
		
	);
	
	$list_query = new WP_Query( $list_args );
	
	
	$listId = 1;

	while ( $list_query->have_posts() )
	{
		$list_query->the_post();

		$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );

		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		$addvertise = get_post_meta( get_the_ID(), 'sld_add_block', true );

		$addvertiseContent = isset($addvertise['add_block_text']) ? $addvertise['add_block_text'] : '';

		//adding extra variable in config
		$conf['item_title_font_size'] = $title_font_size;
		$conf['item_subtitle_font_size'] = $subtitle_font_size;
		$conf['item_title_line_height'] = $title_line_height;
		$conf['item_subtitle_line_height'] = $subtitle_line_height;

		?>

		

		<!-- Override Set Style Elements -->
		<style>
			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul{
				border-top-color: <?php echo $conf['list_border_color']; ?>;
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul li a{
				background-color: <?php echo $conf['list_bg_color']; ?>;
				color: <?php echo $conf['list_txt_color']; ?>;

				<?php if($conf['item_title_font_size']!=''): ?>
				font-size:<?php echo $conf['item_title_font_size']; ?>;
				<?php endif; ?>

				<?php if($conf['item_title_line_height']!=''): ?>
				line-height:<?php echo $conf['item_title_line_height']; ?>;
				<?php endif; ?>

				<?php if( $conf['item_bdr_color'] != "" ) : ?>
				border-bottom-color: <?php echo $conf['item_bdr_color']; ?> !important;
				<?php endif; ?>
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul li a:hover{
				background-color: <?php echo $conf['list_bg_color_hov']; ?>;
				color: <?php echo $conf['list_txt_color_hov']; ?>;

				<?php if( $conf['item_bdr_color_hov'] != "" ) : ?>
				border-bottom-color: <?php echo $conf['item_bdr_color_hov']; ?> !important;
				<?php endif; ?>
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-btn, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-count {
				color: <?php echo $conf['list_txt_color']; ?>;
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-btn:hover, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple li:hover .upvote-btn, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple li:hover .upvote-count{
				color: <?php echo $conf['list_txt_color_hov']; ?>;
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default{
				border-top: 5px solid #f86960;
				border-top-color: <?php echo $conf['list_border_color']; ?>;
				box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}

		</style>


		<!-- Individual List Item -->
		<div id="list-item-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " simple";?> <?php echo "opd-list-id-" . get_the_ID(); ?>">
			<div class="qcopd-single-list">
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						//$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
						$item_count_disp = qcld_item_count_by_function(  get_the_ID() ) ? qcld_item_count_by_function(  get_the_ID() ) : count( get_post_meta(  get_the_ID(), 'qcopd_list_item01' ) ) ;
					}
				?>
				<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
					<?php 
					if(isset($multipage) && $multipage=='true'):
						echo '<a href="'.$current_url.'/'.get_post(get_the_ID())->post_name.'">';
					endif;
					?>
					<?php echo get_the_title(); ?>
					<?php
						if($item_count == 'on'){
							$item_count_disp = isset($lists) ? count($lists) : $item_count_disp;
							echo '<span class="opd-item-count">('.$item_count_disp.')</span>';
						}
					?>
					<?php 
					if(isset($multipage) && $multipage=='true'):
						echo '</a>';
					endif;
					?>
				</h2>
				<?php 
				
				?>
				<ul id="jp-list-<?php echo get_the_ID(); ?>">
					<?php

						if( $item_orderby == 'upvotes' )
						{
    						usort($lists, "sld_custom_sort_by_tpl_upvotes");
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

						$count = 1;

						foreach( $lists as $list ) :
						
						$tooltip_content = '';

						if( $tooltip === 'true' ){
							$tooltip_content = ' data-tooltip="'.$list['qcopd_item_subtitle'].'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}
						//print_r($list);exit;
					?>
					<li id="item-<?php echo get_the_ID() ."-". $count; ?>" <?php echo $tooltip_content; ?>>

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
						?>
						<!-- List Anchor -->
						<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> href="<?php echo $masked_url; ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?>  >

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
									<img src="<?php echo $img[0]; ?>" alt="">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="list-img">
									<i class="fa <?php echo $iconClass; ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="list-img favicon-loaded">
									<img src="<?php echo $faviconImgUrl; ?>" alt="">
								</span>
							<?php else : ?>
								<span class="list-img">
									<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<?php
								echo $list['qcopd_item_title'];
							?>

						</a>

						<?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section upvote-section-simple">
								<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
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
						
							<?php if(sld_get_option('sld_enable_bookmark')=='on'): ?>
							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-simple">
							
								<?php 
								$bookmark = 0;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv)){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo get_the_ID(); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
								</span>
								
							</div>
							<?php endif; ?>
							
							<?php if(isset($list['qcopd_new']) and $list['qcopd_new']==1):?>
							<!-- new icon section -->
							<div class="new-icon-section">
								<span> <?php 
								$lan_text_new = sld_get_option('dashboard_lan_text_new') ? sld_get_option('dashboard_lan_text_new') : 'new';
								_e( $lan_text_new, 'qc-opd' ); 
								?></span>
							</div>
							<!-- /new icon section -->
							<?php endif; ?>
							
							
							<?php if(isset($list['qcopd_featured']) and $list['qcopd_featured']==1):?>
							<!-- featured section -->
							<div class="featured-section">
								
							</div>
							<!-- /featured section -->
							<?php endif; ?>

					</li>

					<?php $count++; endforeach; ?>

				</ul>

				

			</div>

		</div>
		<!-- /Individual List Item -->


		<?php

		$listId++;
	}

	
	
	wp_die();
}


add_action('wp_ajax_qcld_sld_loadmore', 'qcld_sld_loadmore_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_loadmore', 'qcld_sld_loadmore_function'); // ajax for not logged in users


function qcld_sld_loadmore_filter_function(){

	$paged 			= isset($_POST['page']) ? $_POST['page'] : '';
	$column 		= isset($_POST['column']) ? $_POST['column'] : '';
	$item_count 	= isset($_POST['itemcount']) ? $_POST['itemcount'] : '';
	$itemperpage 	= isset($_POST['itemperpage']) ? $_POST['itemperpage'] : '';
	

	$list_args = array(
		'post_type' 		=> 'sld',
		'posts_per_page' 	=> $itemperpage,
		'paged'				=> $paged
		
	);
	
	$listItems = get_posts( $list_args );
	
	
	foreach ($listItems as $item) :
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

		if( $item_count == "on" ){
			//$item_count_disp = count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
			$item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
		}
		?>

		<a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
			<?php echo esc_html($item->post_title); ?>
			<?php
			if($item_count == 'on'){
				echo '<span class="opd-item-count-fil">('.esc_html($item_count_disp).')</span>';
			}
			?>
		</a>

	<?php endforeach;
	wp_die();
}


add_action('wp_ajax_qcld_sld_loadmore_filter', 'qcld_sld_loadmore_filter_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_loadmore_filter', 'qcld_sld_loadmore_filter_function'); // ajax for not logged in users

function qcopd_search_sld_page_function(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	$shortcode = isset($_POST['shortcode']) ? trim($_POST['shortcode']) : '';

	if( !empty($shortcode) ){
		$list_args = array(
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'Scheduled')
		);
		$listItems = get_posts( $list_args );
		$data = '';
		foreach($listItems as $item){
			
			if(strpos($item->post_content, $shortcode ) !== false ){
				if($shortcode=='sld_login'){
					$data = 'Login page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_registration'){
					$data = 'Registration page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_dashboard'){
					$data = 'Dashboard page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_restore'){
					$data = 'Password Restore page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				break;
			}
		}
		
		if($data!=''){
			echo $data;
		}else{
			
				if($shortcode=='sld_login'){
					
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Login',
						'post_type' => 'page',
						'post_content'=> '[sld_login]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					wp_publish_post($PostID);
					echo 'Login page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
					
				}
				if($shortcode=='sld_registration'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Register',
						'post_type' => 'page',
						'post_content'=> '[sld_registration]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					wp_publish_post($PostID);
					echo 'Registration page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
				if($shortcode=='sld_dashboard'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Dashboard',
						'post_type' => 'page',
						'post_content'=> '[sld_dashboard]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					wp_publish_post($PostID);
					echo 'Dashboard page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
				if($shortcode=='sld_restore'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Restore Password',
						'post_type' => 'page',
						'post_content'=> '[sld_restore]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					wp_publish_post($PostID);
					echo 'Restore page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
			
		}
	}
	wp_die();
}



add_action('wp_ajax_qcopd_search_sld_page', 'qcopd_search_sld_page_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_search_sld_page', 'qcopd_search_sld_page_function'); // ajax for not logged in users

function qcopd_flash_rewrite_rules_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	flush_rewrite_rules();
	wp_die();
}


add_action('wp_ajax_qcopd_flash_rewrite_rules', 'qcopd_flash_rewrite_rules_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_flash_rewrite_rules', 'qcopd_flash_rewrite_rules_fnc'); // ajax for not logged in users

function qcopd_reset_all_upvotes_fnc(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	
	$list = isset($_POST['list']) ? $_POST['list'] : '';
	$item = isset($_POST['item']) ? $_POST['item'] : '';
	
	if($list=='all'){
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE 1 and meta_key = 'qcopd_list_item01'") );
	}else{
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE 1 and post_id = %d and meta_key = 'qcopd_list_item01'", $list ) );
	}
	
	
	
	foreach($results as $key=>$value){
		
		$unserialized = maybe_unserialize($value->meta_value);
		foreach($unserialized as $k=>$v){
			
			if($item=='all' or $item==''){
				if($k=='qcopd_upvote_count'){
					$unserialized[$k]=0;

					$wpdb->delete(
						"{$wpdb->prefix}sld_ip_table",
						array( 'item_id' => $value->post_id.'_'.$unserialized['qcopd_timelaps'] ),
						array( '%s' )
					);
					
				}
			}else{
				if($k=='qcopd_upvote_count'){
					if($unserialized['qcopd_item_title']==$item){
						$unserialized[$k]=0;
						$wpdb->delete(
							"{$wpdb->prefix}sld_ip_table",
							array( 'item_id' => $value->post_id.'_'.$unserialized['qcopd_timelaps'] ),
							array( '%s' )
						);
					}
				}
			}
			
			
		}
		
		$updated_value = maybe_serialize($unserialized);
		
		$wpdb->update(
			$wpdb->postmeta,
			array(
				'meta_value' => $updated_value,
			),
			array('meta_id' => $value->meta_id)
		);
		
	}
	echo '<p style="color:green;font-weight:bold;">Upvote reset successfully!</p>';
	wp_die();
}


add_action('wp_ajax_qcopd_reset_all_upvotes', 'qcopd_reset_all_upvotes_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_reset_all_upvotes', 'qcopd_reset_all_upvotes_fnc'); // ajax for not logged in users

//load list items
function qcopd_list_items_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	$listId = isset($_POST['listid']) ? $_POST['listid'] : '';
	$lists = get_post_meta( $listId, 'qcopd_list_item01' );
	echo '<div class="qcsld_single_field_shortcode"><label style="width: 200px;display: inline-block;">Select Item</label><select style="width: 225px;" id="sld_list_item"><option value="all">All Items</option>';
	foreach( $lists as $list ) :
		echo '<option value="'.$list['qcopd_item_title'].'">'.$list['qcopd_item_title'].'</option>';
	endforeach;
	echo '</select></div>';
	wp_die();
}


add_action('wp_ajax_show_qcsld_list_items', 'qcopd_list_items_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_show_qcsld_list_items', 'qcopd_list_items_fnc'); // ajax for not logged in users

//load list items
function qcld_sld_show_list_item_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	$listId = isset($_POST['listid']) ? $_POST['listid'] : '';
	$lists = get_post_meta( $listId, 'qcopd_list_item01' );
	
	foreach( $lists as $list ) :
		echo '<option value="'.$list['qcopd_item_title'].'">'.$list['qcopd_item_title'].'</option>';
	endforeach;
	
	wp_die();
}


add_action('wp_ajax_qcld_sld_show_list_item', 'qcld_sld_show_list_item_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_show_list_item', 'qcld_sld_show_list_item_fnc'); // ajax for not logged in users

function qcopd_item_click_action_fnc(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );

	global $wpdb;
	$itemid 	= isset($_POST['itemid']) ? trim($_POST['itemid']) : '';
    $itemurl 	= isset($_POST['itemurl']) ? trim($_POST['itemurl']) : '';
    $itemsid 	= isset($_POST['itemsid']) ? trim($_POST['itemsid']) : '';
	$table      = $wpdb->prefix.'sld_click_table';
	$results 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE 1 and post_id = %d and meta_key = 'qcopd_list_item01'", $itemid ) );
	
	foreach($results as $key=>$value){
		$unserialized = maybe_unserialize($value->meta_value);
		if (trim($unserialized['qcopd_item_link']) == trim($itemurl) && trim($unserialized['qcopd_timelaps'])==$itemsid){
			$click_count = 0;
			$new_array = array();
			$flag = 0;

			//Check if there already a set value (previous)
			if ( !empty($unserialized) && is_array($unserialized) && array_key_exists('qcopd_click', $unserialized)) {
				$click_count = (int)$unserialized['qcopd_click'];
				$flag = 1;
			}

			foreach ($unserialized as $k => $v) {
				if ($flag) {
					if ($k == 'qcopd_click') {
						$new_array[$k] = $click_count + 1;
					} else {
						$new_array[$k] = $v;
					}
				} else {
					$new_array[$k] = $v;
				}
			}

			if (!$flag) {
				$new_array['qcopd_click'] = $click_count + 1;
			}
			$updated_value = maybe_serialize($new_array);
			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_value' => $updated_value,
				),
				array('meta_id' => $value->meta_id)
			);
		}
	}
	
	$date 	= date('Y-m-d H:i:s');
	$userip = sld_get_the_user_ip();
	$wpdb->insert(
		$table,
		array(
			'time'  	=> $date,
			'itemurl'   => $itemurl,
			'itemid'   	=> $itemid,
			'ip'   		=> $userip
		)
	);
	
	wp_die();
}


add_action('wp_ajax_qcopd_item_click_action', 'qcopd_item_click_action_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_item_click_action', 'qcopd_item_click_action_fnc'); // ajax for not logged in users


function qcopd_load_long_description_function(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );

	$post_id 	= isset($_POST['post_id']) ? trim($_POST['post_id']) : '';
    $meta_title = isset($_POST['meta_title']) ? wp_unslash(trim($_POST['meta_title'])) : '';
    $meta_link 	= isset($_POST['meta_link']) ? trim($_POST['meta_link']) : '';
	$upvote 	= isset($_POST['upvote']) ? trim($_POST['upvote']) : '';
	global $wpdb;
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ) );
	$conf = get_post_meta( $post_id, 'qcopd_list_conf', true );
	
	?>
	<style type="text/css">
		.sld_popup_content h2:hover{
			color: <?php echo $conf['list_txt_color_hov']; ?>;
		}
		.sld_popup_content h2{
			color: <?php echo $conf['list_txt_color']; ?>;
		}
		.sld_popup_content p:hover{
			color: <?php echo $conf['list_subtxt_color_hov']; ?>;
		}
		.sld_popup_content p{
			color: <?php echo $conf['list_subtxt_color']; ?>;
		}
	</style>
	<?php
	
	
	foreach ($results as $key => $value) {
		$unserialized = maybe_unserialize($value->meta_value);
		$unserialized = sld_Modify_Single_List_Upvotes($post_id, $unserialized);
		if (trim($unserialized['qcopd_item_title']) == trim($meta_title) ) {
			echo '<div class="sld_single_content">';

?>
		<div class="sld_popup_top_part">
		<div class="feature-image" style="">
		<?php
			$iconClass = (isset($unserialized['qcopd_fa_icon']) && trim($unserialized['qcopd_fa_icon']) != "") ? $unserialized['qcopd_fa_icon'] : "";

			$showFavicon = (isset($unserialized['qcopd_use_favicon']) && trim($unserialized['qcopd_use_favicon']) != "") ? $unserialized['qcopd_use_favicon'] : "";

			$faviconImgUrl = "";
			$faviconFetchable = false;
			$filteredUrl = "";

			$directImgLink = (isset($unserialized['qcopd_item_img_link']) && trim($unserialized['qcopd_item_img_link']) != "") ? $unserialized['qcopd_item_img_link'] : "";

			if( !isset($item_url) ){
				$item_url = '';
			}
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
			<?php if( isset($unserialized['qcopd_multipage_item_img'])  && $unserialized['qcopd_multipage_item_img'] != "" ) :

				$img = wp_get_attachment_image_src($unserialized['qcopd_multipage_item_img'], 'medium'); 
				?>
				<img src="<?php echo $img[0]; ?>" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php elseif( isset($unserialized['qcopd_item_img'])  && $unserialized['qcopd_item_img'] != "" ) : ?>
				<?php
					$img = wp_get_attachment_image_src($unserialized['qcopd_item_img'], 'medium');
					
				?>
				<img src="<?php echo $img[0]; ?>" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>

				<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php elseif( $iconClass != "" ) : ?>

				<span class="icon fa-icon">
					<i class="fa <?php echo $iconClass; ?>"></i>
				</span>

			<?php else : ?>

				<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php endif; ?>
		</div>
		<div class="sld_popup_content">
		<h2><?php echo $unserialized['qcopd_item_title']; ?>
			<?php if($upvote=='on'): ?>
			<div class="upvote-section upvote-section-style-single">
				<span data-post-id="<?php echo $post_id; ?>" data-unique="<?php echo $post_id.'_'.$unserialized['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($unserialized['qcopd_item_title']); ?>" data-item-link="<?php echo $unserialized['qcopd_item_link']; ?>" class="sld-upvote-btn-single upvote-on">
					<i class="fa fa-thumbs-up"></i>
				</span>
				<span class="upvote-count count">
					<?php
					  if( isset($unserialized['qcopd_upvote_count']) && (int)$unserialized['qcopd_upvote_count'] > 0 ){
						echo (int)$unserialized['qcopd_upvote_count'];
					  }
					?>
				</span>
			</div>
			<?php endif; ?>
		</h2>
<?php

			if(sld_get_option('sld_lan_visit_link')!=''){
				$visit_page = sld_get_option('sld_lan_visit_link');
			}else{
				$visit_page = __('Visit This Link','qc-opd');
			}
			echo (isset($unserialized['qcopd_tags']) && !empty( $unserialized['qcopd_tags'] ) ? '<p><b>'.esc_html('Tags').': '. esc_attr($unserialized['qcopd_tags']).'</b></p>' :'' );
			echo '<p>'.$unserialized['qcopd_item_subtitle'].'</p>';
			echo '<a href="'.esc_url($unserialized['qcopd_item_link']).'" target="_blank" class="sld_single_button">'.$visit_page.'</a></div></div>';

			$qcopd_description = isset( $unserialized['qcopd_description'] ) ? $unserialized['qcopd_description'] : '';
			
			echo apply_filters('the_content', $qcopd_description );
			
			echo '</div>';
		}
	}
	wp_die();
}



add_action('wp_ajax_qcopd_load_long_description', 'qcopd_load_long_description_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_load_long_description', 'qcopd_load_long_description_function'); // ajax for not logged in users

function qcopd_load_video_function(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	$video_link = isset($_POST['videurl']) ? esc_url(trim($_POST['videurl'])) : '';

	//$video_link = str_replace('watch?v=','embed/',$video_link);

	$urls = parse_url($video_link);
	if(isset($urls['host']) && $urls['host']=='vimeo.com'){
		
		$videoId = explode('/',$video_link);
		
		$video_link = 'https://player.vimeo.com/video/'.end($videoId);
		echo '<iframe width="560" height="315" src="'.$video_link.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
	}else if ( !empty($video_link) && ( ( strpos($video_link, 'youtube') == true ) || ( strpos($video_link, 'youtu') == true  ) ) ) {
		
		$youtube_url_id = preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_link, $match);
		
		$youtube_video_id = isset($match[1]) ? $match[1] : '';

		parse_str( parse_url( $video_link, PHP_URL_QUERY ), $my_array_of_vars );
		
		$starts = isset($my_array_of_vars['t']) ? "start=". (int) $my_array_of_vars['t'] : '';
		$start 	= isset($my_array_of_vars['start']) ? "start=". (int) $my_array_of_vars['start'] : $starts;
		$start .= isset($my_array_of_vars['end']) ? "&end=". (int) $my_array_of_vars['end'] : '';
		$start .= isset($my_array_of_vars['rel']) ? "&rel=". (int) $my_array_of_vars['rel'] : '';
		$start .= isset($my_array_of_vars['showinfo']) ? "&showinfo=". (int) $my_array_of_vars['showinfo'] : '';

		echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$youtube_video_id.'?'.$start.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';

	}else{
		
		parse_str( parse_url( $video_link, PHP_URL_QUERY ), $my_array_of_vars );

		$start 	= isset($my_array_of_vars['t']) ? "start=". (int) $my_array_of_vars['t'] : '';
		$start .= isset($my_array_of_vars['e']) ? "&end=". (int) $my_array_of_vars['e'] : '';
		$start .= isset($my_array_of_vars['rel']) ? "&rel=". (int) $my_array_of_vars['rel'] : '';
		$start .= isset($my_array_of_vars['showinfo']) ? "&showinfo=". (int) $my_array_of_vars['showinfo'] : '';

		echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$my_array_of_vars['v'].'?'.$start.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';

	}

	wp_die();
}



add_action('wp_ajax_qcopd_load_video', 'qcopd_load_video_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_load_video', 'qcopd_load_video_function'); // ajax for not logged in users


function qcopd_tag_pd_page_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	
?>
<div class="fa-field-modal" id="fa-field-modal-tag" style="display:block">
  <div class="fa-field-modal-close">&times;</div>
  <h1 class="fa-field-modal-title"> <?php echo (sld_get_option('sld_lan_add_tags')!=''?sld_get_option('sld_lan_add_tags'):__('Add Tags', 'qc-opd')) ?></h1>
  <p style="margin-top: 28px;margin-bottom: 0;"> <?php echo (sld_get_option('sld_lan_add_tags_subtitle')!=''?sld_get_option('sld_lan_add_tags_subtitle'):__('Press (enter) to add a tag & (Backspace) to remove tag.', 'qc-opd')) ?></p>

	<div class="sld-form-control tags" id="sld-tags" style="margin-top: 40px;">
      	<input type="text" class="labelinput">
        <input id="sldtagvalue" type="hidden" value="" name="result">
     </div>
  <input type="submit" id="sld_tag_select" name="submit" value="<?php echo (sld_get_option('sld_lan_save_tags')!=''?sld_get_option('sld_lan_save_tags'):__('Save Tags', 'qc-opd')) ?>" class="button button-primary" />
  <p style="color: red;"> <?php echo (sld_get_option('sld_lan_add_tags_notice')!=''?sld_get_option('sld_lan_add_tags_notice'):__('You must click on the "Save Tags" button', 'qc-opd')) ?></p>
</div>

<?php
	
	wp_die();
	
}

add_action('wp_ajax_qcopd_tag_pd_page', 'qcopd_tag_pd_page_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_tag_pd_page', 'qcopd_tag_pd_page_fnc'); // ajax for not logged in users

function qcopd_search_pd_tags_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;

	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE 1 AND meta_key = 'qcopd_list_item01'") );
	$lists = array();
	if(!empty($results)){
		foreach($results as $result){
			$unserialize = maybe_unserialize($result->meta_value);			
			if(isset($unserialize['qcopd_tags']) && $unserialize['qcopd_tags']!=''){				
				foreach(explode(',',$unserialize['qcopd_tags']) as $itm){
					if(!in_array($itm, $lists)){
						array_push($lists, $itm);
					}
				}				
			}			
		}
	}
	echo implode(',',$lists);
	
	wp_die();
	
}

add_action('wp_ajax_qcopd_search_pd_tags', 'qcopd_search_pd_tags_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_search_pd_tags', 'qcopd_search_pd_tags_fnc'); // ajax for not 

function qcopd_img_download_fnc(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	
	global $wpdb;
	$url = isset($_POST['url']) ? esc_url($_POST['url']) : '';
	$attach_id = '';
	$blueprint = array(
		'attachmentid'	=> '',
		'imgurl' 		=> ''
	);


	$APIKey = sld_get_option('sld_pagespeed_api');
	
	$APIKey = ( isset($APIKey) && !empty($APIKey) ) ? $APIKey : "AIzaSyDgTUsxx59PCEGECgJztbhPT0Os5Vz1vXg";
	
	$image = sld2_get_web_page("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=".$url."&screenshot=true&key=".$APIKey);
	$image = json_decode($image, true);
	
	$image = isset($image['lighthouseResult']['audits']['final-screenshot']['details']['data']) ? $image['lighthouseResult']['audits']['final-screenshot']['details']['data'] : '';

	if(!empty($image)){
		
		$upload_dir       = wp_upload_dir();
		$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		$image = str_replace(array('_', '-'), array('/', '+'), $image);
		$imgBase64 = str_replace('data:image/jpeg;base64,', '', $image);
		$imgBase64 = str_replace(' ', '+', $imgBase64);
		$decoded = base64_decode($imgBase64);
		$filename         = 'sldwebsite.jpg';
		$hashed_filename  = md5( $filename . microtime() ) . '_' . $filename;
		$image_upload     = file_put_contents( $upload_path . $hashed_filename, $decoded );
		if( !function_exists( 'wp_handle_sideload' ) ) {
		  require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		if( !function_exists( 'wp_get_current_user' ) ) {
		  require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}
		$file             = array();
		$file['error']    = '';
		$file['tmp_name'] = $upload_path . $hashed_filename;
		$file['name']     = $hashed_filename;
		$file['type']     = 'image/jpeg';
		$file['size']     = filesize( $upload_path . $hashed_filename );
		$file_return      = wp_handle_sideload( $file, array( 'test_form' => false ) );
		$filename = $file_return['file'];
		$attachment = array(
		 'post_mime_type' => $file_return['type'],
		 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		 'post_content' => '',
		 'post_status' => 'inherit',
		 'guid' => $upload_dir['url'] . '/' . basename($filename)
		 );
		$attach_id = wp_insert_attachment( $attachment, $filename );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		$thumb = @wp_get_attachment_image_src($attach_id,'thumbnail');
		$blueprint['attachmentid'] = $attach_id;
		$blueprint['imgurl'] = (!empty($thumb)?$thumb[0]:'');
		echo json_encode($blueprint);
		wp_die();

	}
	
	
}

add_action('wp_ajax_qcopd_img_download', 'qcopd_img_download_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_img_download', 'qcopd_img_download_fnc'); // ajax for not logged in users

function qcopd_generate_text_fnc(){
	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	$url = isset($_POST['url']) ? esc_url($_POST['url']) : '';
	$schema = array(
		'title' 		=> '',
		'description' 	=> '',
	);

	if($url!=''){
		
		$html_data = sld2_get_web_page($url);

		$html_dom = new DOMDocument();
		@$html_dom->loadHTML($html_data);
		$xpath = new DOMXPath($html_dom);
		
		if( isset($xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent) && ($xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent !='') ){
			$schema['description'] = iconv("UTF-8", "ISO-8859-1", $xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent);
			if( !$schema['description'] ){
				$schema['description'] = $xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent;
			}
		}
		if( isset($xpath->query('//title')->item(0)->textContent) && ($xpath->query('//title')->item(0)->textContent!='') ){
			$schema['title'] = iconv("UTF-8", "ISO-8859-1", $xpath->query('//title')->item(0)->textContent);
			if( !$schema['title'] ){
				$schema['title'] = $xpath->query('//title')->item(0)->textContent;
			}
		}
		echo json_encode($schema);
		
	}
	
	wp_die();
	
}

add_action('wp_ajax_qcopd_generate_text', 'qcopd_generate_text_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_generate_text', 'qcopd_generate_text_fnc'); // ajax for not logged in users


function sld_package_list_item_ordering(){

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;
	$table  = $wpdb->prefix.'sld_package';
	
	$list_id = isset($_POST['order_string']) ? esc_attr($_POST['order_string']) : 0;

	if(!empty($list_id)){

	  	foreach( $list_id as $menu_order => $post_id ){
	    		
			$position     = intval($menu_order);
			$wpdb->query($wpdb->prepare("UPDATE $table SET menu_order='$position' WHERE id=$post_id"));
			
	  	}

	}

  	wp_die('1');
	
}

// ajax for logged in users
add_action('wp_ajax_sld_package_list_item_ordering', 'sld_package_list_item_ordering'); 