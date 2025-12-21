<?php
$MobilefilterType = sld_get_option( 'sld_enable_list_filter_dropdown_mobile' );
$args = array(
	'numberposts' => -1,
	'post_type'   => 'sld',
	'orderby'     => $filterorderby,
	'order'       => $filterorder,
);

if( !empty($category))
{
	$taxArray = array(
		array(
			'taxonomy' => 'sld_cat',
			'field'    => 'slug',
			'terms'    => $category,
		),
	);

	$args = array_merge($args, array( 'tax_query' => $taxArray ));

}

$listItems = get_posts( $args );

$filterType = sld_get_option( 'sld_filter_ptype' ); //normal, carousel
if($cattabid!=''){
	$filterType = 'normal';
}


if( $MobilefilterType == 'on' ){
	
	$item_count_disp_all = 0;
	foreach ($listItems as $item){
		if( $item_count == "on" ){
			@$item_count_disp_all += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
		}
	}
					
?>
	<div class="sld-filter-area-select-mobile">
		<select>
			<option value="all">
				<?php 
					if(sld_get_option('sld_lan_show_all')!=''){
						echo sld_get_option('sld_lan_show_all');
					}else{
						_e('Show All', 'qc-opd'); 
					}
				?>	
			</option>

			<?php foreach ($listItems as $item) :
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
					// $item_count_disp = count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
					$item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
				}
				?>

	            <option value="<?php echo $item->ID; ?>" >
					<?php echo esc_html($item->post_title); ?>
					<?php
					if($item_count == 'on'){
						echo '<span class="opd-item-count-fil">('.$item_count_disp.')</span>';
					}
					?>
	            </option>

			<?php endforeach; ?>

		</select>
	</div>
<?php
}

//If FILTER TYPE is NORMAL

if( $filterType == 'normal' ) :

	?>

    <div class="filter-area">

					<?php 
						$item_count_disp_all = 0;
						foreach ($listItems as $item){
							if( $item_count == "on" ){
								@$item_count_disp_all += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
							}
						}
					?>
					<a href="#" class="filter-btn filter-active" data-filter="all"  title="<?php echo sld_get_option('sld_lan_show_all') ? sld_get_option('sld_lan_show_all') :esc_html('Show All', 'qc-opd');  ?>">
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

		<?php foreach ($listItems as $item) :
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
				// $item_count_disp = count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
				$item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
			}
			?>

            <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>"  title="<?php echo esc_attr($item->post_title); ?>">
				<?php echo esc_html($item->post_title); ?>
				<?php
				if($item_count == 'on'){
					echo '<span class="opd-item-count-fil">('.$item_count_disp.')</span>';
				}
				?>
            </a>

		<?php endforeach; ?>

    </div>

<?php endif; ?>

<?php
//If FILTER TYPE is CAROUSEL

if( $filterType == 'carousel' ) :
	?>

    
    <div class="filter-area-main">
        <div class="filter-area" style="width: 100%;">

            <div class="filter-carousel">
                <div class="item">
					<?php 
						$item_count_disp_all = 0;
						foreach ($listItems as $item){
							if( $item_count == "on" ){
								$item_count_disp_all += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
							}
						}
					?>
					<a href="#" class="filter-btn filter-active" data-filter="all"  title="<?php echo sld_get_option('sld_lan_show_all') ? sld_get_option('sld_lan_show_all') :esc_html('Show All', 'qc-opd');  ?>">
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

				<?php foreach ($listItems as $item) :
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
						// $item_count_disp = count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
						$item_count_disp = qcld_item_count_by_function($item->ID) ? qcld_item_count_by_function($item->ID) : count( get_post_meta( $item->ID, 'qcopd_list_item01' ) ) ;
					}
					?>

                    <div class="item">
                        <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>"  title="<?php echo esc_attr($item->post_title); ?>">
							<?php echo esc_html($item->post_title); ?>
							<?php
							if($item_count == 'on'){
								echo '<span class="opd-item-count-fil">('.$item_count_disp.')</span>';
							}
							?>
                        </a>
                    </div>

				<?php endforeach; ?>

            </div>

            <?php 


				$qcopd_slick_custom_js = "jQuery(document).ready(function($){

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


				wp_add_inline_script( 'qcopd-custom-script', $qcopd_slick_custom_js);


            ?>

        </div>
    </div>

<?php endif; ?>