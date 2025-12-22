<?php

//Registering Sub Menu for Ordering
add_action( 'admin_menu', 'qcopd_register_sld_manage_large_list' );

function qcopd_register_sld_manage_large_list() {
	add_submenu_page(
		'edit.php?post_type=sld',
		__('Manage Large List', 'qc-opd'),
		__('Manage Large List', 'qc-opd'),
		'edit_pages', 'sld-manage-large-list',
		'qcopd_sld_manage_large_list_page'
	);
}
function sld_delete_elements($metaid, $listid){
	
	global $wpdb;
	$msg = '';
	$table = $wpdb->prefix."postmeta";
	$wpdb->delete(
	$table,
		array( 'meta_id' => $metaid ),
		array( '%d' )
	);
	wp_redirect(admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid=' . $listid ));
	
}
function qcopd_sld_manage_large_list_page(){
	global $wpdb;
	
	
	if(isset($_GET['act']) && $_GET['act']=='edit' && isset($_GET['metaid']) && $_GET['metaid']!=''){
		
		sld_get_element_edit_page(sanitize_text_field($_GET['metaid']));
		
	}else if(isset($_GET['act']) && $_GET['act']=='create' && isset($_GET['listid']) && $_GET['listid']!=''){
		
		sld_get_element_create_page(sanitize_text_field($_GET['listid']));
		
	}
	else if(isset($_GET['listid']) && $_GET['listid']!=''){

		if(isset($_GET['act']) && $_GET['act']=='delete' && isset($_GET['listid']) && $_GET['listid']!='' && isset($_GET['id']) && $_GET['id']!=''){
			
			sld_delete_elements(sanitize_text_field($_GET['id']), sanitize_text_field($_GET['listid']));
			$msg = 'List element deleted successfully!';
		}
		$whereClass = '';
		if(isset($_POST['sld_keyword']) && $_POST['sld_keyword']!=''){
			$whereClass .=" and `meta_value` LIKE '%".sanitize_text_field($_POST['sld_keyword'])."%'";			
		}
	
	$listid = isset($_GET['listid']) ? sanitize_text_field($_GET['listid']) : '';
	
	$sql = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and post_id = '$listid' and meta_key='qcopd_list_item01' $whereClass order by `meta_id` ASC";
	
	$sql2 = $wpdb->prepare( "SELECT count(*)as cnt FROM ".$wpdb->prefix."postmeta WHERE 1 and post_id = '$listid' and meta_key='qcopd_list_item01' $whereClass order by `meta_id` ASC" );
	
	
	$total             = $wpdb->get_var( $sql2 );
	
	$items_per_page = 50;
	
	$page             = isset( $_GET['cpage'] ) ? abs( (int) sanitize_text_field($_GET['cpage']) ) : 1;
	$offset         = ( $page * $items_per_page ) - $items_per_page;
	
	$sql .=" LIMIT $offset, $items_per_page";
	
	$rows = $wpdb->get_results( $wpdb->prepare( $sql ) );

	if(!empty($rows)){
		$totalPage         = ceil($total / $items_per_page);
		$customPagHTML='';
		if($totalPage > 1){
			$customPagHTML     =  '<div><span>Page '.$page.' of '.$totalPage.' </span>'.paginate_links( array(
			'base' => add_query_arg( 'cpage', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $totalPage,
			'current' => $page
			)).'</div>';
		}
	}
	
	
	
	
?>
		
		<div class="wrap">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
					<div id="post-body-content" >
				<h1  class="wp-heading-inline"><?php esc_html_e('All Elements for - ', 'qc-opd').get_the_title($listid); ?></h1>
				<a class="page-title-action" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&act=create&listid=' . $listid ); ?>">
					<?php esc_html_e('Add New List Item', 'qc-opd') ?>
				</a>
				<hr class="wp-header-end">
		<div class="qchero_sliders_list_wrapper">
			<?php 
				if( isset($msg) && $msg!=''){
					echo '<div style="font-size: 20px;color: green;border-bottom: 1px solid;padding-bottom: 10px;margin-bottom: 10px;">'.esc_html($msg).'</div>';
				}
			?>
			<ul class="subsubsub">
				<li class="all"><a href="edit.php?post_type=sld&page=qcsld_package" class="current" aria-current="page">All <span class="count">(<?php echo $total; ?>)</span></a></li>
			</ul>
			
			<form  action=""method="POST" class="qchero_slider_table_area sld-large-list-table-form">
				<p class="search-box">
					<input type="search" id="post-search-input" name="sld_keyword" value="<?php echo (isset($_POST['sld_keyword']) && $_POST['sld_keyword']!=''?$_POST['sld_keyword']:''); ?>" />
					<input type="submit" value="<?php esc_html_e('Search'); ?>" id="search-submit" class="button button-secondary" />
				</p>
			<div class="">
				<table class="wp-list-table widefat fixed striped posts ">
					<thead>
						<tr>
							<th class="sld_payment_cell sld-w-small">
								<?php esc_html_e( 'ID', 'qc-opd' ) ?>
							</th>
							
							<th class="sld_payment_cell">
								<?php esc_html_e( 'Item Title', 'qc-opd' ) ?>
							</th>
							<th class="sld_payment_cell">
								<?php esc_html_e( 'Item Subtitle', 'qc-opd' ) ?>
							</th>
							<th class="sld_payment_cell">
								<?php esc_html_e( 'Tags', 'qc-opd' ) ?>
							</th>
							
							<th class="sld_payment_cell">
								<?php esc_html_e( 'Action', 'qc-opd' ); ?>
							</th>
						</tr>
					</thead>
		<tbody>
			<?php
			if(!empty($rows)){
				foreach($rows as $row){
					$value = unserialize($row->meta_value);
					
				?>
					<tr class="sld_payment_row">
						<td class="sld_payment_cell sld-w-small">
							<div class="sld_responsive_head"><?php esc_html_e('Date', 'qc-opd') ?></div>
							<?php echo $row->meta_id; ?>
						</td>
						
						<td class="sld_payment_cell">
							<div class="sld_responsive_head"><?php esc_html_e('Item Title', 'qc-opd') ?></div>
							<strong>
								<a href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&act=edit&metaid=' . $row->meta_id ); ?>"><?php echo $value['qcopd_item_title']; ?></a>
							</strong>
						</td>
						
						<td class="sld_payment_cell">
							<div class="sld_responsive_head"><?php esc_html_e('Item Subtitle', 'qc-opd') ?></div>
							<?php echo $value['qcopd_item_subtitle']; ?>
						</td>
						
						<td class="sld_payment_cell">
							<div class="sld_responsive_head"><?php esc_html_e('Tangs', 'qc-opd') ?></div>
							<?php echo $value['qcopd_tags']; ?>
						</td>
						
						<td class="sld_payment_cell">
							<div class="sld_responsive_head"><?php esc_html_e('Action', 'qc-opd') ?></div>

							<a class="button button-primary" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&act=edit&metaid=' . $row->meta_id ); ?>">
								<?php esc_html_e('Edit', 'qc-opd') ?>
							</a>
							<a class="button button-primary" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&act=edit&metaid=' . $row->meta_id ); ?>" target="_blank">
								<?php esc_html_e('Edit in new window', 'qc-opd') ?>
							</a>
							<a class="button button-danger" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid='.$listid.'&act=delete&id=' . $row->meta_id ); ?>">
								<?php esc_html_e('Delete', 'qc-opd') ?>
							</a>
						</td>
					</tr>
				<?php
				}
			}
			?>
		</tbody>
			</table>
			</div>

		</form>
		</div>
		<div class="sld_menu_title" style="text-align:left;"><?php echo $customPagHTML; ?><span style="float:right;font-weight:bold;">Total <?php echo esc_attr($total); ?></span></div>
	</div>
</div>
</div>
<?php
	}
	
}

function sld_get_element_edit_page($metaid){
	
	global $wpdb;
	$table = $wpdb->prefix."postmeta";
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and meta_id =%d", $metaid ) );
	$md = unserialize($row->meta_value);
	$msg = '';
	if(isset($_POST['meta_update'])){
		
		$item_title 		= isset($_POST['item_title']) 				? stripcslashes(sanitize_text_field($_POST['item_title'])) : '';
		$item_link 			= isset($_POST['item_link']) 				? sanitize_text_field($_POST['item_link']) : '';
		$item_subtitle 		= isset($_POST['item_subtitle']) 			? stripcslashes(sanitize_text_field($_POST['item_subtitle'])) : '';
		
		$item_fa 			= isset($_POST['item_fa']) 					? sanitize_text_field($_POST['item_fa']) : '';
		$qcopd_upvote_count = isset($_POST['qcopd_upvote_count']) 		? sanitize_text_field($_POST['qcopd_upvote_count']) : 0;
		$list_item_bg_color = isset($_POST['list_item_bg_color']) 		? sanitize_text_field($_POST['list_item_bg_color']) : '';
		$item_description 	= isset($_POST['item_long_description']) 	? stripcslashes($_POST['item_long_description']) : '';
		$item_no_follow 	= isset($_POST['item_no_follow']) 			? sanitize_text_field($_POST['item_no_follow']) : '';
		$qcopd_item_ugc 	= isset($_POST['qcopd_item_ugc']) 			? sanitize_text_field($_POST['qcopd_item_ugc']) : '';
		$item_new_tab 		= isset($_POST['item_new_tab']) 			? sanitize_text_field($_POST['item_new_tab']) : '';
		$item_mark_new 		= isset($_POST['item_mark_new']) 			? sanitize_text_field($_POST['item_mark_new']) : '';
		$item_mark_featured = isset($_POST['item_mark_featured']) 		? sanitize_text_field($_POST['item_mark_featured']) : '';
		$item_unpublish 	= isset($_POST['item_unpublish']) 			? sanitize_text_field($_POST['item_unpublish']) : 0;
		$item_tags 			= isset($_POST['item_tags']) 				? stripcslashes(sanitize_text_field($_POST['item_tags'])) : '';
		$item_image 		= isset($_POST['item_image']) 				? $_POST['item_image'] : '';
		
		$listid 			= isset($_POST['listid']) 				    ? sanitize_text_field($_POST['listid']) : '';
		$datetime 			= date('Y-m-d H:i:s');

		// sld_enable_extra_video_field
		// sld_extra_video_field

		$use_favicon = isset($_POST['sld_pick_image_from_direct_link']) ? sanitize_text_field($_POST['sld_pick_image_from_direct_link']) : '';
		
		$new_array = array(
			'qcopd_item_title'  	=> $item_title,
			'qcopd_item_link'   	=> $item_link,
			'qcopd_item_subtitle' 	=> $item_subtitle,
			
			'qcopd_fa_icon'			=> $item_fa,
			'qcopd_upvote_count'	=> $qcopd_upvote_count,
			'list_item_bg_color'	=> $list_item_bg_color,
			'qcopd_item_nofollow'	=> $item_no_follow,
			'qcopd_item_ugc'		=> $qcopd_item_ugc,
			'qcopd_item_newtab'		=> $item_new_tab,
			'qcopd_new'				=> $item_mark_new,
			'qcopd_featured'		=> $item_mark_featured,
			'qcopd_item_img'		=> $item_image,
			
			//'qcopd_upvote_count'	=> (isset($md['qcopd_upvote_count'])?$md['qcopd_upvote_count']:''),
			//'list_item_bg_color'	=> (isset($md['list_item_bg_color'])?$md['list_item_bg_color']:''),
			'qcopd_entry_time'		=> (isset($md['qcopd_entry_time'])?$md['qcopd_entry_time']:$datetime),
			'qcopd_timelaps'		=> ( (isset($md['qcopd_timelaps']) && $md['qcopd_timelaps'] > 0 ) ?$md['qcopd_timelaps']:time()),
			'qcopd_is_bookmarked'	=> (isset($md['qcopd_is_bookmarked'])?$md['qcopd_is_bookmarked']:''),
			'qcopd_description'		=> $item_description,
			'qcopd_unpublished'		=> $item_unpublish,
			'qcopd_tags'			=> $item_tags,
			'qcopd_use_favicon'		=> $use_favicon,

		);

		if(sld_get_option('sld_enable_extra_video_field') == 'on'){
			$new_array['sld_extra_video_field'] = (isset($_POST['sld_extra_video_field'])?$_POST['sld_extra_video_field']:'');
		}

		$wpdb->update(
			$table,
			array(
				'meta_value'  => serialize($new_array),
			),
			array( 'meta_id' => $metaid),
			array(
				'%s',
			),
			array( '%d')
		);
		$msg = 'List element updated successfully!';
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and meta_id =%d", $metaid ) );
		$md  = unserialize($row->meta_value);

		// Copy to Other Lists
		if( isset($_POST['qcopd_other_list']) && !empty($_POST['qcopd_other_list']) ){
			$listids = explode(',',$_POST['qcopd_other_list']);
			if(!empty($listids)){
				foreach($listids as $listid){
					if($row->post_id != $listid){
						@add_post_meta( $listid, 'qcopd_list_item01', $new_array );
					}
						
				}
			}
		} //End Copy to Other Lists
	}
	
	
	
	?>
	
	<div class="wrap qcopd-manage-large-list">
			
		
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content" >
				<h1 class="wp-heading-inline"><?php esc_html_e('Edit List Item for', 'qc-opd') ?> - <?php echo get_the_title($row->post_id); ?></h1>
				<a class="page-title-action" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&act=create&listid=' . $row->post_id ); ?>">
					<?php esc_html_e('Add New List Item', 'qc-opd') ?>
				</a>
				<hr class="wp-header-end">
				<?php 
					if($msg!=''){
						echo '<div style="font-size: 20px;color: green;border-bottom: 1px solid;padding-bottom: 10px;margin-bottom: 10px;">'.esc_html($msg).'</div>';
					}
				?>
				<div class="qcld-sld-square-section-block">
					<form method="post" action="">
						<table class="form-table">

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Website Link (Ex: http://www.google.com)', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_link" name="item_link" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_link'])?$md['qcopd_item_link']:''); ?>" />

								</td>
							</tr>
							<tr>
								<th><label><?php esc_html_e('Generator info', 'qc-opd') ?> </label>
								</th>
								<td>
									<input type="button" id="sld_generate" class="button button-primary" value="Generate" />
								</td>
							</tr>
						
							<tr>
								<th><label for="sld_item_title"><?php esc_html_e( 'Item Title', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_title" name="item_title" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_title'])?esc_attr($md['qcopd_item_title']):''); ?>" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_subtitle"><?php esc_html_e( 'Item Subtitle', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_subtitle" name="item_subtitle" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_subtitle'])?esc_attr($md['qcopd_item_subtitle']):''); ?>" />

								</td>
							</tr>
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Font Awesome Icon', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_fa" name="item_fa" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_fa_icon'])?$md['qcopd_fa_icon']:''); ?>" />

								</td>
							</tr>
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Upvote Count', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="qcopd_upvote_count" name="qcopd_upvote_count" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_upvote_count'])?$md['qcopd_upvote_count']: 0 ); ?>" />

								</td>
							</tr>
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Item Background Color', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="list_item_bg_color" name="list_item_bg_color" class="sld_wp_color_picker sld_text_width" value="<?php echo (isset($md['list_item_bg_color'])?$md['list_item_bg_color']:''); ?>" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'No Follow', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_no_follow" name="item_no_follow" class=" sld_text_width" <?php echo (isset($md['qcopd_item_nofollow']) && $md['qcopd_item_nofollow']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Ugc', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="qcopd_item_link_ugc" name="qcopd_item_ugc" class="sld_text_width" <?php echo (isset($md['qcopd_item_ugc']) && $md['qcopd_item_ugc']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Open Link in a New Tab', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_new_tab" name="item_new_tab" <?php echo (isset($md['qcopd_item_newtab']) && $md['qcopd_item_newtab']==1)?'checked="checked"':''; ?> class=" sld_text_width" value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Mark Item as New', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_mark_new" name="item_mark_new" class=" sld_text_width" <?php echo (isset($md['qcopd_new']) && $md['qcopd_new']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Mark Item as Featured', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_mark_featured" name="item_mark_featured" class=" sld_text_width" <?php echo (isset($md['qcopd_featured']) && $md['qcopd_featured']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Unpublish this Item', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_unpublish" name="item_unpublish" class=" sld_text_width" <?php echo (isset($md['qcopd_unpublished']) && $md['qcopd_unpublished']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Generate Image from Website Link', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_generate_image" name="sld_generate_image" class=" sld_text_width"  value="" />

								</td>
							</tr>

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Pick Image from the Direct Link', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_pick_image_from_direct_link" name="sld_pick_image_from_direct_link" class=" sld_text_width"  <?php echo (isset($md['qcopd_use_favicon']) && $md['qcopd_use_favicon']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Item Image', 'qc-opd' ); ?></label>
								</th>
								<td>
								
									<input type="button" class="sld_item_image button-secondary" value="<?php echo esc_html('Upload Image'); ?>" />
									<input type="hidden" id="item_image" name="item_image" value="<?php echo (isset($md['qcopd_item_img'])?$md['qcopd_item_img']:''); ?>" class="regular-text"
									value="">
									<div class="sld_item_image_preview" style="margin-top:5px;">
									
									<?php 
										if (strpos($md['qcopd_item_img'], 'http') === FALSE){
									?>
										<?php
											$img = wp_get_attachment_image_src($md['qcopd_item_img']);

											if( isset($img[0]) && !empty($img[0]) ){
										?>
										<img src="<?php echo $img[0]; ?>" width="150" alt="" />
										<br>

									<?php
											}
										}else{
									?>
										<img src="<?php echo $md['qcopd_item_img']; ?>" width="150" alt="" />
									<?php
										}
									?>
										<input type="button" <?php if( !isset($md['qcopd_item_img']) || $md['qcopd_item_img'] == '' ){ echo 'style="display: none;"'; } ?> class="sld_large_list_item_remove_image button-secondary" value="<?php echo esc_html('Remove Image'); ?>" />
									
									</div>

								</td>
							</tr>
							
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Tags', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_tags" name="item_tags" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_tags'])?esc_attr($md['qcopd_tags']):''); ?>" />
								</td>
							</tr>

							<tr>
								<th><label for="qcopd_other_list"><?php esc_html_e( 'Copy this item to other Lists', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="qcopd_other_list" name="qcopd_other_list" class="field-long sld_text_width" value="" />
								</td>
							</tr>

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Long Description (will show in lightbox and on multipage mode)', 'qc-opd' ); ?></label>
								</th>
								<td>
									<?php wp_editor(html_entity_decode(stripcslashes((isset($md['qcopd_description'])?$md['qcopd_description']:''))), 'item_long_description', array('textarea_name' =>
										'item_long_description',
										'textarea_rows' => 20,
										'editor_height' => 100,
										'disabled' => 'disabled',
										'media_buttons' => false,
										'tinymce'       => array(
											'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink',)
									)); ?>
								</td>
							</tr>

						<?php if(sld_get_option('sld_enable_extra_video_field') == 'on'){ ?>
							<tr>
								<th><label for="sld_extra_video_field"><?php esc_html_e( 'Youtube/Vimeo Videos', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_extra_video_field" name="sld_extra_video_field" class="field-long sld_text_width" value="<?php echo (isset($md['sld_extra_video_field'])?$md['sld_extra_video_field']:''); ?>" />

								</td>
							</tr>
						<?php } ?>
							
							<tr>
								<th><label for="sld_item_link"></label>
								</th>
								<td>
									<input type="hidden" name="listid" value="<?php echo $row->post_id; ?>" />
									<input type="hidden" name="metaid" value="<?php echo $row->meta_id; ?>" />
									<input type="submit" class="button button-large button-primary" name="meta_update" value="<?php echo esc_html('Save'); ?>" />
								</td>
								<td>
									
									<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid='.$row->post_id); ?>"><?php echo esc_html('Go Back'); ?></a>
								</td>
							</tr>
							
						</table>
					</form>
				</div>
				
				
				</div>
			</div>
		</div>
			
	</div>
	
	<?php
	
}

function sld_get_element_create_page($listid){
	
	global $wpdb;
	$table = $wpdb->prefix."postmeta";
	$msg = '';
	if(isset($_POST['meta_update'])){
		
		$item_title 		= isset($_POST['item_title']) 				    ?  stripcslashes(sanitize_text_field($_POST['item_title'])) : '';
		$item_link 			= isset($_POST['item_link']) 				    ?  sanitize_text_field($_POST['item_link']) : '';
		$item_subtitle 		= isset($_POST['item_subtitle']) 				?  stripcslashes(sanitize_text_field($_POST['item_subtitle'])) : '';
		
		$item_fa 			= isset($_POST['item_fa']) 				    	?  sanitize_text_field($_POST['item_fa']) : '';
		$list_item_bg_color = isset($_POST['list_item_bg_color']) 			?  sanitize_text_field($_POST['list_item_bg_color']) : '';
		$qcopd_upvote_count = isset($_POST['qcopd_upvote_count']) 			?  sanitize_text_field($_POST['qcopd_upvote_count']) : 0;
		$item_description 	= isset($_POST['item_long_description']) 		?  stripcslashes($_POST['item_long_description']) : '';
		$item_no_follow 	= isset($_POST['item_no_follow']) 				?  sanitize_text_field($_POST['item_no_follow']) : '';
		$qcopd_item_ugc 	= isset($_POST['qcopd_item_ugc']) 				?  sanitize_text_field($_POST['qcopd_item_ugc']) : '';
		$item_new_tab 		= isset($_POST['item_new_tab']) 				?  sanitize_text_field($_POST['item_new_tab']) : '';
		$item_mark_new 		= isset($_POST['item_mark_new']) 				?  sanitize_text_field($_POST['item_mark_new']) : '';
		$item_mark_featured = isset($_POST['item_mark_featured']) 			?  sanitize_text_field($_POST['item_mark_featured']) : '';
		$item_unpublish 	= isset($_POST['item_unpublish']) 				?  sanitize_text_field($_POST['item_unpublish']) : 0;
		$item_tags 			= isset($_POST['item_tags']) 				    ?  stripcslashes(sanitize_text_field($_POST['item_tags'])) : '';
		$item_image 		= isset($_POST['item_image']) 				    ?  $_POST['item_image'] : '';
		
		$listid 			= isset($_POST['listid']) 				    	?  sanitize_text_field($_POST['listid']) : '';
		$datetime 			= date('Y-m-d H:i:s');
		
		$use_favicon 		= isset($_POST['sld_pick_image_from_direct_link']) ?  sanitize_text_field($_POST['sld_pick_image_from_direct_link']) : '';

		$new_array = array(
			'qcopd_item_title'  => $item_title,
			'qcopd_item_link'   => $item_link,
			'qcopd_item_subtitle' => $item_subtitle,
			'qcopd_fa_icon'	=> $item_fa,
			'qcopd_item_nofollow'	=> $item_no_follow,
			//'qcopd_upvote_count'	=> $qcopd_upvote_count,
			//'list_item_bg_color'	=> $list_item_bg_color,
			'qcopd_item_ugc'	=> $qcopd_item_ugc,
			'qcopd_item_newtab'	=> $item_new_tab,
			'qcopd_new'	=> $item_mark_new,
			'qcopd_featured'	=> $item_mark_featured,
			'qcopd_item_img'	=> $item_image,
			'qcopd_upvote_count'	=> (isset($md['qcopd_upvote_count'])?$md['qcopd_upvote_count']: 0 ),
			'list_item_bg_color'	=> (isset($md['list_item_bg_color'])?$md['list_item_bg_color']:''),
			'qcopd_entry_time'	=> (isset($md['qcopd_entry_time'])?$md['qcopd_entry_time']:$datetime),
			'qcopd_timelaps'	=> (isset($md['qcopd_timelaps'])?$md['qcopd_timelaps']:time()),
			'qcopd_is_bookmarked'	=> (isset($md['qcopd_is_bookmarked'])?$md['qcopd_is_bookmarked']:''),
			'qcopd_description'	=> $item_description,
			'qcopd_unpublished'	=> $item_unpublish,
			'qcopd_tags'	=> $item_tags,
			'qcopd_use_favicon'	=> $use_favicon,

		);

		if(sld_get_option('sld_enable_extra_video_field') == 'on'){
			$new_array['sld_extra_video_field'] = (isset($_POST['sld_extra_video_field'])?$_POST['sld_extra_video_field']:'');
		}
		$wpdb->insert(
			$table,
			array(
				'post_id'  => $listid,
				'meta_key'   => 'qcopd_list_item01',
				'meta_value' => serialize($new_array)
			)
		);
		
		
		
		$msg = 'List element created successfully! <a class="button-primary" href="'.admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid='.$listid).'">Go Back</a>';
		

	}
	
	
	
	?>
	
	<div class="wrap">
			
		
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content" >
				<?php 
					if($msg!=''){
						echo '<div style="font-size: 20px;color: green;border-bottom: 1px solid;padding-bottom: 10px;margin-bottom: 10px;">'.esc_html($msg).'</div>';
					}
				?>
				<h1 class="wp-heading-inline"> <?php esc_html_e('Create List Item for', 'qc-opd') ?> - <?php echo get_the_title($listid); ?></h1>

				<hr class="wp-header-end">
				
				<div class="qcld-sld-square-section-block">
					<form method="post" action="">
						<table class="form-table">

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Website Link (Ex: http://www.google.com)', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_link" name="item_link" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_link'])?$md['qcopd_item_link']:''); ?>" />

								</td>
							</tr>
							<tr>
								<th><label><?php esc_html_e('Generator info', 'qc-opd') ?> </label>
								</th>
								<td>
									<input type="button" id="sld_generate" class=" button button-primary" value="<?php echo esc_html('Generate'); ?>" />
								</td>
							</tr>
						
							<tr>
								<th><label for="sld_item_title"><?php esc_html_e( 'Item Title', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_title" name="item_title" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_title'])?esc_attr($md['qcopd_item_title']):''); ?>" required />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_subtitle"><?php esc_html_e( 'Item Subtitle', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_subtitle" name="item_subtitle" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_item_subtitle'])?esc_attr($md['qcopd_item_subtitle']):''); ?>" />

								</td>
							</tr>
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Font Awesome Icon', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_fa" name="item_fa" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_fa_icon'])?$md['qcopd_fa_icon']:''); ?>" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'No Follow', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_no_follow" name="item_no_follow" class=" sld_text_width" <?php echo (isset($md['qcopd_item_nofollow']) && $md['qcopd_item_nofollow']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Open Link in a New Tab', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_new_tab" name="item_new_tab" <?php echo (isset($md['qcopd_item_newtab']) && $md['qcopd_item_newtab']==1)?'checked="checked"':''; ?> class=" sld_text_width" value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Mark Item as New', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_mark_new" name="item_mark_new" class=" sld_text_width" <?php echo (isset($md['qcopd_new']) && $md['qcopd_new']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Mark Item as Featured', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_mark_featured" name="item_mark_featured" class=" sld_text_width" <?php echo (isset($md['qcopd_featured']) && $md['qcopd_featured']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Unpublish this Item', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_item_unpublish" name="item_unpublish" class=" sld_text_width" <?php echo (isset($md['qcopd_unpublished']) && $md['qcopd_unpublished']==1)?'checked="checked"':''; ?> value="1" />

								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Generate Image from Website Link', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_generate_image" name="sld_generate_image" class=" sld_text_width"  value="" />

								</td>
							</tr>

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Pick Image from the Direct Link', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="sld_pick_image_from_direct_link" name="sld_pick_image_from_direct_link" class=" sld_text_width" <?php echo (isset($md['qcopd_use_favicon']) && $md['qcopd_use_favicon']==1)?'checked="checked"':''; ?>  value="1"  />

								</td>
							</tr>

							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Item Image', 'qc-opd' ); ?></label>
								</th>
								<td>
								
									<input type="button" class="sld_item_image button-secondary" value="<?php echo esc_html('Upload Image'); ?>" />
									<input type="hidden" id="item_image" name="item_image" value="<?php echo (isset($md['qcopd_item_img'])?$md['qcopd_item_img']:''); ?>" class="regular-text"
									value="">
									<div class="sld_item_image_preview" style="margin-top:5px;">
										<img src="" width="150" alt="" />
										<br>
										<input type="button" style="display: none;" class="sld_large_list_item_remove_image button-secondary" value="<?php echo esc_html('Remove Image'); ?>" />
									</div>

								</td>
							</tr>
							
							
							
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Tags', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_item_tags" name="item_tags" class="field-long sld_text_width" value="<?php echo (isset($md['qcopd_tags'])?esc_attr($md['qcopd_tags']):''); ?>" />
								</td>
							</tr>
							<tr>
								<th><label for="sld_item_link"><?php esc_html_e( 'Long Description (will show in lightbox and on multipage mode)', 'qc-opd' ); ?></label>
								</th>
								<td>
									
									
									<?php wp_editor(html_entity_decode(stripcslashes((isset($md['qcopd_description'])?$md['qcopd_description']:''))), 'item_long_description', array('textarea_name' =>
										'item_long_description',
										'textarea_rows' => 20,
										'editor_height' => 100,
										'disabled' => 'disabled',
										'media_buttons' => false,
										'tinymce'       => array(
											'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink',)
									)); ?>
									
								</td>
							</tr>

						<?php if(sld_get_option('sld_enable_extra_video_field') == 'on'){ ?>
							<tr>
								<th><label for="sld_extra_video_field"><?php esc_html_e( 'Youtube/Vimeo Videos', 'qc-opd' ); ?></label>
								</th>
								<td>
									<input type="text" id="sld_extra_video_field" name="sld_extra_video_field" class="field-long sld_text_width" value="" />

								</td>
							</tr>
						<?php } ?>
							
							<tr>
								<th><label for="sld_item_link"></label>
								</th>
								<td>
									<input type="hidden" name="listid" value="<?php echo esc_attr($listid); ?>" />
									
									<input type="submit" class="button button-primary" name="meta_update" value="Save" />
								</td>
								<td>
									
									<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=sld&page=sld-manage-large-list&listid='.$listid); ?>"><?php esc_html_e( 'Go Back', 'qc-opd' ); ?></a>
								</td>
							</tr>
							
						</table>
					</form>
				</div>
				
				
				</div>
			</div>
		</div>
			
	</div>
	
	<?php
	
}