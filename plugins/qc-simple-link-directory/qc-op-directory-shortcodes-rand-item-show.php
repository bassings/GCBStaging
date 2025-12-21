<?php
defined('ABSPATH') or die("No direct script access!");


add_shortcode('qcopd-directory-random-list-item', 'SLD_QCOPD_DIRectory_random_list_shortcode');

function SLD_QCOPD_DIRectory_random_list_shortcode( $atts = array() ){

    
    extract( shortcode_atts(
		array(
			'orderby' 			=> 'rand',
			'order' 			=> 'ASC',
			'background' 		=> '#5c5050',
			'color' 			=> '#fff',
			'category' 			=> '',
			'limit' 			=> 1,

		), $atts
	));

		$list_args = array(
			'post_type' 	=> 'sld',
			'orderby' 		=> $orderby,
			'order' 		=> $order,
			'posts_per_page' => $limit,
		);

		$list_img == "true";

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

		ob_start();


		if ( $list_query->have_posts() ){

			?>

			<div id="qc_sld_random_image" class="qc_sld-random-image-<?php echo get_the_ID(); ?>" >

				<style>
					.qc_sld-random-image-<?php echo get_the_ID(); ?>{
					    background: <?php echo $background; ?>;
					    color: <?php echo $color; ?>;
					    padding-top: 15px;
					    padding-bottom: 15px;
					    display: inline-block;
					}

					.qc_sld-random-image-<?php echo get_the_ID(); ?> .qc_sld-image {
					    text-align: center;
					    height: 215px;
					    width: 215px;
					    max-width: 100%;
					    overflow: hidden;
					}

					.qc_sld-random-image-<?php echo get_the_ID(); ?> .qc_sld-image img{
					    height: 100%;
					    width: auto;
					}

					.qc_sld-random-image-<?php echo get_the_ID(); ?> .qc_sld-image .list-img:hover {
					    opacity: 69%;
					    transition: 0.3s ease all;
					}

					.qc_sld-random-image-<?php echo get_the_ID(); ?> .qc_sld-image .qc_sld-image-title {
					    line-height: 20px;
					    min-height: 50px;
					    overflow: hidden;
					}
				</style>

			<?php 


			while ( $list_query->have_posts() ){

				$list_query->the_post();

				$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );

				shuffle( $lists );

				foreach( $lists as $list ) : 

			?>

			  <div class="qc_sld-image">

				<div class="uqc_sld-image-box">
			      <div class="qc_sld-image-title"> <?php echo ($list['qcopd_item_title']); ?></div>

			      	<?php 
						$qcopd_item_nofollow = (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel=dofollow' : 'rel=dofollow'; 
						$qcopd_item_nofollow = (isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] == 1) ? 'rel=dofollow' : $qcopd_item_nofollow;

							$masked_url = esc_url($list['qcopd_item_link']);
					?>
					
					<a title="<?php echo esc_attr($list['qcopd_item_title']); ?>" <?php echo esc_attr($qcopd_item_nofollow); ?> href="<?php echo esc_url($masked_url); ?>"
					<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo esc_attr(get_the_ID()); ?>" data-itemurl="<?php echo esc_url($list['qcopd_item_link']); ?>" data-itemsid="<?php echo esc_attr($list['qcopd_timelaps']); ?>" data-tag="<?php echo (isset($list['qcopd_tags'])?esc_attr($list['qcopd_tags']):'' ); ?>" <?php echo $popContent; ?>>

					<?php if( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
						<span class="list-img">
							<?php
								$img = wp_get_attachment_image_src($list['qcopd_item_img'], array('215', '215'));
							?>
							<img src="<?php echo esc_url($img[0]); ?>" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
						</span>
					<?php elseif( $iconClass != "" ) : ?>
						<span class="list-img">
							<i class="fa <?php echo esc_attr($iconClass); ?>"></i>
						</span>
					<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
						<span class="list-img favicon-loaded">
							<img src="<?php echo esc_url($faviconImgUrl); ?>" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
						</span>
					<?php else : ?>
						<span class="list-img">
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo esc_attr($list['qcopd_item_title']); ?>">
						</span>
					<?php endif; ?>

					</a>
			      
			    </div> 
			    
			  </div>
			

	<?php 
			break; endforeach;
    		wp_reset_postdata();

			}
			?>
			</div>
			<?php
		}

    $content = ob_get_clean();
    return $content;
}