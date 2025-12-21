<?php

/*TinyMCE Shortcode Generator Button - 25-01-2017*/

function qcopdsld_tinymce_shortcode_button_function() {
	add_filter ("mce_external_plugins", "qcopdsld_shortcode_generator_btn_js");
	add_filter ("mce_buttons", "qcopdsld_shortcode_generator_btn");
}

function qcopdsld_shortcode_generator_btn_js($plugin_array) {
	$plugin_array['qcsld_shortcode_btn'] = plugins_url('assets/js/qcsld-tinymce-button.js', __FILE__);
	return $plugin_array;
}

function qcopdsld_shortcode_generator_btn($buttons) {
	array_push ($buttons, 'qcsld_shortcode_btn');
	return $buttons;
}

add_action ('init', 'qcopdsld_tinymce_shortcode_button_function');

function qcsld_load_custom_wp_admin_style() {
        wp_register_style( 'sld_shortcode_gerator_css', SLD_QCOPD_ASSETS_URL . '/css/shortcode-modal.css', false, '1.0.0' );
        wp_enqueue_style( 'sld_shortcode_gerator_css' );
}
add_action( 'admin_enqueue_scripts', 'qcsld_load_custom_wp_admin_style' );

function qcsld_render_shortcode_modal() {

	?>

	<div id="sm-modal" class="sld_modal">

		<!-- Modal content -->
		<div class="modal-content">
		
			<span class="close">
				<span class="dashicons dashicons-no"></span>
			</span>
			<h3> 
				<?php _e( 'SLD - Shortcode Generator' , 'qc-opd' ); ?></h3>
			<hr/>
		<div class="sld_shortcode_generator_area">


		<div class="hero_tab">
		    <button class="hero_tablinks hero_active" data-id="hero_general" > <?php echo esc_html('General'); ?></button>
		    <button class="hero_tablinks" data-id="hero_settings" ><?php echo esc_html('Display Settings'); ?></button>
		    <button class="hero_tablinks" data-id="hero_widget" ><?php echo esc_html('Widget Shortcode'); ?></button>

		</div>
        <div id="hero_general" class="hero_tabcontent" style="padding: 6px 12px;">
			<div class="sm_shortcode_list">

				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						 <?php echo esc_html('Mode'); ?>
					</label>
					<select style="width: 225px;" id="sld_mode">
						<option value="all"><?php echo esc_html('All List'); ?></option>
						<option value="one"><?php echo esc_html('One List'); ?></option>
						<option value="category"><?php echo esc_html('List Category'); ?></option>
                        <option value="categorytab"><?php echo esc_html('Category Tab'); ?></option>
					</select>
				</div>

                <div class="qcsld_single_field_shortcode hidden-div" id="sld_cat_orderby">
                    <label style="width: 200px;display: inline-block;">
                        <?php echo esc_html('Category Order By '); ?>
                    </label>
                    <select style="width: 225px;" id="sld_category_orderby">
                        <option value="date"><?php echo esc_html('Date'); ?></option>
                        <option value="ID"><?php echo esc_html('ID'); ?></option>
                        <option value="title"><?php echo esc_html('Title'); ?></option>
                        <option value="modified"><?php echo esc_html('Date Modified'); ?></option>
                        <option value="rand"><?php echo esc_html('Random'); ?></option>
                        <option value="menu_order"><?php echo esc_html('Menu Order'); ?></option>
                    </select>
                </div>

                <div class="qcsld_single_field_shortcode hidden-div" id="sld_cat_order">
                    <label style="width: 200px;display: inline-block;">
                         <?php echo esc_html('Category Order'); ?>
                    </label>
                    <select style="width: 225px;" id="sld_category_order">
                        <option value="ASC"><?php echo esc_html('Ascending'); ?></option>
                        <option value="DESC"><?php echo esc_html('Descending'); ?></option>
                    </select>
                </div>
				
				<div id="sld_list_div" class="qcsld_single_field_shortcode hidden-div">
					<label style="width: 200px;display: inline-block;">
						 <?php echo esc_html('Select List'); ?>
					</label>
					<select style="width: 225px;" id="sld_list_id">
					
						<option value=""><?php echo esc_html('Please Select List'); ?></option>
						
						<?php
						
							$ilist = new WP_Query( array( 'post_type' => 'sld', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC') );
							if( $ilist->have_posts()){
								while( $ilist->have_posts() ){
									$ilist->the_post();
						?>
						
						<option value="<?php echo esc_attr(get_the_ID()); ?>"><?php echo esc_html(get_the_title()); ?></option>
						
						<?php } } ?>
						
					</select>
				</div>
				
				<div id="sld_list_cat" class="qcsld_single_field_shortcode hidden-div">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('List Category'); ?>
					</label>
					<select style="width: 225px;" id="sld_list_cat_id">
					
						<option value=""><?php echo esc_html('Please Select Category'); ?></option>
						
						<?php
						
							$terms = get_terms( 'sld_cat', array(
								'hide_empty' => true,
							) );
							if( $terms ){
								foreach( $terms as $term ){
						?>
						
						<option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
						
						<?php } } ?>
						
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Template Style'); ?>
					</label>
					<select style="width: 225px;" id="sld_style">
						<option value=""><?php echo esc_html('Select Style'); ?></option>
						<option value="simple"><?php echo esc_html('Default Style'); ?></option>
						<option value="style-1"><?php echo esc_html('Style 01'); ?></option>
						<option value="style-2"><?php echo esc_html('Style 02'); ?></option>
						<option value="style-3"><?php echo esc_html('Style 03'); ?></option>
						<option value="style-4"><?php echo esc_html('Style 04'); ?></option>
						<option value="style-5"><?php echo esc_html('Style 05'); ?></option>
						<option value="style-6"><?php echo esc_html('Style 06'); ?></option>
						<option value="style-7"><?php echo esc_html('Style 07'); ?></option>
						<option value="style-8"><?php echo esc_html('Style 08'); ?></option>
						<option value="style-9"><?php echo esc_html('Style 09'); ?></option>
						<option value="style-10"><?php echo esc_html('Style 10'); ?></option>
						<option value="style-11"><?php echo esc_html('Style 11'); ?></option>
						<option value="style-12"><?php echo esc_html('Style 12'); ?></option>
						<option value="style-13"><?php echo esc_html('Style 13'); ?></option>
						<option value="style-14"><?php echo esc_html('Style 14'); ?></option>
						<option value="style-15"><?php echo esc_html('Style 15'); ?></option>
						<option value="style-16"><?php echo esc_html('Style 16'); ?></option>
					</select>
					
					<div id="demo-preview-link">
						<div id="demo-url">
						</div>
					</div>
					
				</div>
				
				<!--<div id="sld_infinity_scroll" class="qcsld_single_field_shortcode" style="display:none;">
					<label style="width: 200px;display: inline-block;">
						Infinity Scroll
					</label>
					<input id="infinityscroll" name="ckbox" value="1" type="checkbox">
				</div>-->
				
				
				
				
				<div id="sld_column_div" class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Column'); ?>
					</label>
					<select style="width: 225px;" id="sld_column">
						<option value="1"><?php echo esc_html('Column 1'); ?></option>
						<option value="2"><?php echo esc_html('Column 2'); ?></option>
						<option value="3"><?php echo esc_html('Column 3'); ?></option>
						<option value="4"><?php echo esc_html('Column 4'); ?></option>
					</select>
				</div>


                <div class="qcsld_single_field_shortcode">
                    <label style="width: 200px;display: inline-block;">
                        <?php echo esc_html('List Item (Link) Order By'); ?>
                    </label>
                    <select style="width: 225px;" id="sld_item_orderby">

                        <option value=""><?php echo esc_html('None'); ?></option>
                        <option value="upvotes"><?php echo esc_html('Upvotes'); ?></option>
                        <option value="clicks"><?php echo esc_html('Clicks'); ?></option>
                        <option value="title"><?php echo esc_html('Title'); ?></option>
                        <option value="timestamp"><?php echo esc_html('Date Modified'); ?></option>
                        <option value="random"><?php echo esc_html('Random'); ?></option>

                    </select>
                </div>
				
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('List Item Order'); ?>
					</label>
					<select style="width: 225px;" id="sld_item_order">
						<option value="ASC"><?php echo esc_html('Ascending'); ?></option>
						<option value="DESC"><?php echo esc_html('Descending'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_orderby">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('List Order By (for multiple Lists)'); ?>
					</label>
					<select style="width: 225px;" id="sld_orderby">
						<option value="date"><?php echo esc_html('Date'); ?></option>
						<option value="ID"><?php echo esc_html('ID'); ?></option>
						<option value="title"><?php echo esc_html('Title'); ?></option>
						<option value="modified"><?php echo esc_html('Date Modified'); ?></option>
						<option value="rand"><?php echo esc_html('Random'); ?></option>
						<option value="menu_order"><?php echo esc_html('Menu Order'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_order">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('List Order'); ?>
					</label>
					<select style="width: 225px;" id="sld_order">
						<option value="ASC"><?php echo esc_html('Ascending'); ?></option>
						<option value="DESC"><?php echo esc_html('Descending'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_orderby">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Filter Button Order By'); ?>
					</label>
					<select style="width: 225px;" id="sld_filter_orderby">
						<option value="date"><?php echo esc_html('Date'); ?></option>
						<option value="ID"><?php echo esc_html('ID'); ?></option>
						<option value="title"><?php echo esc_html('Title'); ?></option>
						<option value="modified"><?php echo esc_html('Date Modified'); ?></option>
						<option value="rand"><?php echo esc_html('Random'); ?></option>
						<option value="menu_order"><?php echo esc_html('Menu Order'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_order">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Filter Button Order'); ?>
					</label>
					<select style="width: 225px;" id="sld_filter_order">
						<option value="ASC"><?php echo esc_html('Ascending'); ?></option>
						<option value="DESC"><?php echo esc_html('Descending'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_favorite">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Favorite'); ?>
					</label>
					<select style="width: 225px;" id="sld_favorite">
						<option value="disable"><?php echo esc_html('Disable'); ?></option>
						<option value="enable"><?php echo esc_html('Enable'); ?></option>

					</select>
				</div>

				<div class="qcopd_single_field_shortcode" id="qcopd_enable_pagination">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Pagination'); ?>
					</label>
					<select style="width: 225px;" id="qcopd_enable_pagination_option">
						<option value=""><?php echo esc_html('No Pagination'); ?></option>
						<option value="js-pagination"><?php echo esc_html('JS Pagination (for small directory)'); ?></option>
						<option value="page-pagination"><?php echo esc_html('Page Pagination (for large directory)'); ?></option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_main_click_pop" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Show Popup for Main Click'); ?>
					</label>
				</div>
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_video_main_click_pop" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Open Youtube and Vimeo video on link'); ?>
					</label>
				</div>
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_left_filter" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Enable Left Filter'); ?>
					</label>
				</div>
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_tag_filter" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Enable Tag Filter'); ?>
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_search" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Search'); ?>
					</label>
				</div>
				
				<!--<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_statistics" name="ckbox" value="true" type="checkbox">
						Statistics
					</label>
				</div>-->
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_upvote" name="ckbox" value="on" type="checkbox">
						<?php echo esc_html('Upvote'); ?>
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_item_count" name="ckbox" value="on" type="checkbox">
						<?php echo esc_html('Item Count'); ?>
					</label>
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_hide_list_title" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Hide List Title'); ?>
					</label>
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_show_username" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Display Username'); ?>
					</label>
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld hidden-div" id="display_subcat_as_dropdown">
					<label>
						<input class="sld_show_subcats_as_dropdown" name="ckbox" value="true" type="checkbox">
						<?php echo esc_html('Show Subcategories as Dropdown'); ?>
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="item_details_page" name="ckbox" value="on" type="checkbox">
						<?php echo esc_html('Link to the multi page mode page directly'); ?><br> <?php echo esc_html('(like open in lightbox)'); ?>
					</label>
				</div>

				<!-- <div class="qcsld_single_field_shortcode checkbox-sld sld-off-field pg-template">
					<label>
						<input class="sld_enable_pagination" name="ckbox" value="on" type="checkbox">
						Enable Pagination
					</label>
				</div> -->

				<div id="sld_column_div" class="qcsld_single_field_shortcode sld-off-field pg-enabled">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Items Per Page'); ?>
					</label>
					<input style="width: 225px;" id="sld_items_per_page" type="text" name="sld_items_per_page" class="sld_items_per_page" value="10">
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld tt-template">
					<label>
						<input class="sld_enable_tooltip" name="ckbox" value="on" type="checkbox">
						<?php echo esc_html('Enable Tooltip / Popup Texts'); ?>
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
					</label>
					<input class="sld-sc-btn" type="button" id="qcsld_add_shortcode" value="<?php echo esc_attr('Generate Shortcode'); ?>" />
				</div>
				
			</div>
		</div>

        <div id="hero_settings" class="hero_tabcontent" style="padding: 6px 12px;">
            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Filter Area'); ?>
                </label>
                <select style="width: 225px;" id="sld_filter_area">
                    <option value="normal"><?php echo esc_html('Normal'); ?></option>
                    <option value="fixed"><?php echo esc_html('Fixed'); ?></option>

                </select>
            </div>
            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Filter Area Top Spacing'); ?>
                </label>
                <input type="text" style="width: 225px;" id="sld_topspacing" placeholder="Ex: 50" />
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('List Title Font Size'); ?>
                </label>
                <select style="width: 225px;" id="sld_list_title_font_size">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('List Title Line Height'); ?>
                </label>
                <select style="width: 225px;" id="sld_list_title_line_height">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Item Title Font Size'); ?>
                </label>
                <select style="width: 225px;" id="sld_title_font_size">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Item Subtitle Font Size'); ?>
                </label>
                <select style="width: 225px;" id="sld_subtitle_font_size">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Item Title Line Height'); ?>
                </label>
                <select style="width: 225px;" id="sld_title_line_height">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Item Subtitle Line Height'); ?>
                </label>
                <select style="width: 225px;" id="sld_subtitle_line_height">
                    <option value=""><?php echo esc_html('Default'); ?></option>
			        <?php
			        for($i=10;$i<50;$i++){
				        echo '<option value="'.esc_attr($i).'px">'.esc_html($i).'px</option>';
			        }
			        ?>
                </select>
            </div>
			<div class="qcsld_single_field_shortcode">
            <label style="width: 200px;display: inline-block;">
            </label>
            <input class="sld-sc-btn" type="button" id="qcsld_add_shortcode" value="<?php echo esc_attr('Generate Shortcode'); ?>" />
        </div>
        </div>
		
		<div id="hero_widget" class="hero_tabcontent" style="padding: 6px 12px;">
            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    <?php echo esc_html('Select Widget'); ?>
                </label>
                <select style="width: 225px;" id="sld_widget_area">
                    <option value="tabstyle"><?php echo esc_html('Widget Tab Style'); ?></option>
                    <option value="latest"><?php echo esc_html('Latest Widget'); ?></option>
                    <option value="popular"><?php echo esc_html('Popular Widget'); ?></option>
                    <option value="random"><?php echo esc_html('Random Widget'); ?></option>

                </select>
            </div>
            
			<div class="qcsld_single_field_shortcode">
            <label style="width: 200px;display: inline-block;">
            </label>
            <input class="sld-sc-btn" type="button" id="qcsld_add_shortcode_widget" value="<?php echo esc_attr('Generate Shortcode'); ?>" />
        </div>
        </div>
        


		</div>
		<div class="sld_shortcode_container" style="display:none;">
			<div class="qcsld_single_field_shortcode">
                <textarea style="width:100%;height:200px" id="sld_shortcode_container"></textarea>
				<p><b><?php echo esc_html('Copy'); ?></b> <?php echo esc_html('the shortcode & use it any text block.'); ?> <button class="sld_copy_close button button-primary button-small" style="float:right"> <?php echo esc_html('Copy & Close'); ?></button></p>
            </div>
		</div>
		</div>

	</div>
	<?php
	exit;
}

add_action( 'wp_ajax_show_qcsld_shortcodes', 'qcsld_render_shortcode_modal');



function qcsld_render_upvote_reset_modal() {

	?>

	<div id="sm-modal" class="sld_modal">

		<!-- Modal content -->
		<div class="modal-content" style="top: 25%;">
		
			<span class="close">
				<span class="dashicons dashicons-no"></span>
			</span>
			<h3> 
				<?php echo esc_html('SLD Reset Upvotes'); ?></h3>
			<hr/>



			<div class="sm_shortcode_list">

				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						<?php echo esc_html('Select List'); ?>
					</label>
					<select style="width: 225px;" id="sld_list">
						<option value="all"><?php echo esc_html('All List'); ?></option>
						<?php 
						$list_args_total = array(
							'post_type' => 'sld',
							'posts_per_page' => -1,
						);
						$list_query = new WP_Query( $list_args_total );
						while ( $list_query->have_posts() )
						{
							$list_query->the_post();
							echo '<option value="'.esc_attr(get_the_ID()).'">'.esc_html(get_the_title()).'</option>';
						}
						?>
					</select>
				</div>
				<div class="sld_reset_child_item">

				</div>
                <div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
					</label>
					<input class="sld-sc-btn" type="button" id="sld_reset_votes" value="<?php echo esc_attr('Reset Upvotes'); ?>" />
				</div>
				
			</div>
			
		</div>

	</div>
	<?php
	exit;
}

add_action( 'wp_ajax_show_qcsld_upvote_reset', 'qcsld_render_upvote_reset_modal');