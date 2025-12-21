<?php 
 //code for form data entry
if(isset($_GET['id']) && $_GET['id']!=''){
$current_user = wp_get_current_user();
	if(isset($_POST['uid']) and $_POST['uid']!=''){
		
		$uid 				= isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) :'';
		$sql 				= $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $uid );
		$pdata 				= $wpdb->get_row($sql);
		
		$item_title 		= isset($_POST['item_title']) ? sanitize_text_field(stripcslashes($_POST['item_title'])) :'';
		$item_link 			= isset($_POST['item_link']) ? sanitize_text_field($_POST['item_link']) :'';
		$item_subtitle 		= isset($_POST['item_subtitle']) ? sanitize_text_field($_POST['item_subtitle']) :'';
		$item_description 	= isset($_POST['item_long_description']) ? sanitize_text_field($_POST['item_long_description']) :'';
		$sld_tag 			= isset($_POST['sld_tags']) ? $_POST['sld_tags'] : '';

		if(isset($_POST['item_no_follow']) and $_POST['item_no_follow']==1){
			$item_no_follow = 1;
		}else{
			$item_no_follow = 0;
		}

		/* Image upload script */
		$file_name = '';
		$errors= array();
		$upload_dir = wp_upload_dir();
		if( isset($_POST['sld-auto-generate-img']) && $_POST['sld-auto-generate-img'] != '' ){
			$file_name=$_POST['sld-auto-generate-img'];
			if($file_name!=''){
				$imageurl = $file_name;
			}else{
				$imageurl = $pdata->image_url;
			}
		}else{
			if(isset($_FILES['sld_link_image']) and $_FILES['sld_link_image']['name']!=''){
			  	$file_name = $_FILES['sld_link_image']['name'];
			  	$file_size = $_FILES['sld_link_image']['size'];
			  	$file_tmp  = $_FILES['sld_link_image']['tmp_name'];
			  	$file_type = $_FILES['sld_link_image']['type'];
			  
			  	//$file_ext=strtolower(end(explode('.',$_FILES['sld_link_image']['name'])));
	  			$file_ext 	 = explode('.', $file_name);
				$file_ext 	 = strtolower( end($file_ext) );
			  	$custom_name = strtolower(explode('.',$_FILES['sld_link_image']['name'])[0]);
			  	$file_name   = $custom_name.'_'.time().'.'.$file_ext;
			  
			  	$expensions  = array("jpeg","jpg","png","gif");
			  
			  	if(in_array($file_ext,$expensions)=== false){
				 	$errors[]="Extension not allowed, please choose a JPEG or PNG file.";
			  	}
			  
			  	if($file_size > 2097152){
				 	$errors[]='File size must be excately 2 MB';
			  	}
			  
			  	if(empty($errors)==true){
				 	move_uploaded_file($file_tmp,$upload_dir['path']."/".$file_name);
			  	}else{
				  $file_name='';
			  	}
			}
			if($file_name!=''){
				$imageurl = $upload_dir['url'].'/'.$file_name;
			}else{
				$imageurl = $pdata->image_url;
			}
		}
		
		
		$qc_sld_category = isset($_POST['qc_sld_category']) ? sanitize_text_field($_POST['qc_sld_category']) : '';
		$qc_sld_list = isset($_POST['qc_sld_list']) ? sanitize_text_field($_POST['qc_sld_list']) : '';
		$datetime = date('Y-m-d H:i:s');
		$package_id = isset($_POST['package_id']) ? $_POST['package_id'] : '';
		//Image delete code

		$item_link_exchange = isset($_POST['item_link_exchange']) ? sanitize_text_field($_POST['item_link_exchange']) : '';

		$item_link_exchange_url = isset($_POST['item_link_exchange_url']) ? sanitize_text_field($_POST['item_link_exchange_url']) : '';


		if($package_id == 555){
			$validate_result = apply_filters('qcld_backlink_check_link_validate_check', $item_link_exchange_url, $item_link_exchange, $post_id );

			$qcld_anchor_text = !empty($validate_result) ? $validate_result['text'] : '';
			$qcld_status_text = !empty($validate_result['rel']) ? $validate_result['rel'] : '';

			$qcld_lan_link_found = 'Link Found';
			$qcld_lan_link_no_found = get_option('qcld_lan_link_no_found') ? get_option('qcld_lan_link_no_found') : 'Link Not Found';
			$qcld_lan_link_no_allow = get_option('qcld_lan_link_no_allow') ? get_option('qcld_lan_link_no_allow') : 'Link Not Allow';
			$qcld_link_status = !empty($validate_result) ? $qcld_lan_link_found : $qcld_lan_link_no_found;
			$qcld_status_text = apply_filters('qcopd_link_exchange_rel', $validate_result['rel'] );

			$qcld_link_status =  ($qcld_status_text == 1) ? $validate_result['rel']." ".$qcld_lan_link_no_allow : $qcld_link_status;
		}
		
		if($pdata->sld_list!=$qc_sld_list){
			$this->deny_subscriber_profile($uid);
		}

		if( isset( $_GET['claim_list_id'] ) && !empty( $_GET['claim_list_id'] ) ){
			$qc_sld_list = isset($_GET['claim_list_id']) ? sanitize_text_field($_GET['claim_list_id']) : '';
		}

		
		
		$wpdb->update(
			$table,
			array(
				'item_title'  	=> $item_title,
				'item_link'   	=> $item_link,
				'item_subtitle' => $item_subtitle,
				'category'   	=> $qc_sld_category,
				'sld_list'  	=> $qc_sld_list,
				'user_id'		=> $current_user->ID,
				'image_url'		=> $imageurl,
				'description'	=>$item_description,
				'nofollow'		=> $item_no_follow,
                'package_id'	=> $package_id,
				'approval'		=> 3,
				'qcopd_tags' 	=> $sld_tag,
				'link_form' 	=> $item_link_exchange,
				'link_to' 		=> $item_link_exchange_url,
				'link_anchor_text'=> isset($qcld_anchor_text) ? $qcld_anchor_text: '',
				'link_anchor_attr'=> isset($qcld_status_text) ? $qcld_status_text: '',
				'link_status'	=> isset($qcld_link_status) ? $qcld_link_status: '',
			),
			array( 'id' => $uid),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			),
			array( '%d')
		);
		wp_reset_query();

	
		if(in_array('administrator',$current_user->roles)){
			$this->approve_subscriber_profile($uid);

			$success_msg = sld_get_option('dashboard_lan_text_success_update_msg')!=''?sld_get_option('dashboard_lan_text_success_update_msg'):__('Your link has been updated sucessfully.', 'qc-opd');

			echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__($success_msg,'qc-opd').' <br/></div>';
        }else{

        	if(sld_get_option('sld_email_notification')=='on'){
			
				if(isset($package_id) && $package_id == '555'){

					$this->sld_edit_item_notification_link_exchange($current_user->ID, $item_title);
				}else if(isset($package_id) && $package_id == '7777'){

					$this->sld_edit_item_notification_offline_payments($current_user->ID, $item_title);
				}else{

					$this->sld_edit_item_notification($current_user->ID, $item_title);
				}
			}


			if(sld_get_option('sld_enable_auto_approval')=='on' && (isset($package_id) && $package_id !== '7777') ){

				$this->approve_subscriber_profile($uid);
				$success_msg = sld_get_option('dashboard_lan_text_success_update_msg')!=''?sld_get_option('dashboard_lan_text_success_update_msg'):__('Your link has been updated sucessfully.', 'qc-opd');
				echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__( $success_msg,'qc-opd').' <br/></div>';
			}else{
				$success_msg = sld_get_option('dashboard_lan_text_success_wait_update_msg')!=''?sld_get_option('dashboard_lan_text_success_wait_update_msg'):__('Your link has been updated! Waiting for approval.', 'qc-opd');
				echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__( $success_msg,'qc-opd').' <br/></div>';
			}
        }

		if(!empty($errors)){
			foreach($errors as $error){
				echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.$error.'</div>';
			}
		}
	}

$recid = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
$s = 1;
$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE %d and id=%d", $s, $recid ) );

?>
<h2><?php echo (sld_get_option('sld_lan_link_edit_form')!=''?sld_get_option('sld_lan_link_edit_form'):__('Link Edit Form', 'qc-opd')) ?> </h2>
<form action="" method="POST" enctype="multipart/form-data">
	<ul class="sld_form-style-1 sld_width">
		<li><label><?php echo (sld_get_option('sld_lan_select_package')!=''?sld_get_option('sld_lan_select_package'):__('Select Package', 'qc-opd')) ?> <span class="sld_required">*</span></label>
            <select name="package_id">
                <option value=""><?php _e('None', 'qc-opd') ?></option>
				<?php
				$sld_lan_free = (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd'));
	            if(in_array('administrator',$current_user->roles)){
		            echo '<option value="0" selected="selected">'. __($sld_lan_free, 'qc-opd').'</option>';

                }else{
		            $submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = 0 and user_id =".$current_user->ID);
		            if(sld_get_option('sld_enable_free_submission')=='on'){
			            if(sld_get_option('sld_free_item_limit')!='' and sld_get_option('sld_free_item_limit') > $submited_item->cnt){
				            if($row->package_id==0){
					            echo '<option value="0" selected="selected">'. __($sld_lan_free, 'qc-opd').'</option>';
				            }else if($row->package_id==555){
					            echo '<option value="555" selected="selected">'. __('Link Exchange', 'qc-opd').'</option>';
				            }else{
					            echo '<option value="0">'. __($sld_lan_free, 'qc-opd').'</option>';
				            }

			            }
		            }
                }


	            apply_filters( 'qcld_offline_link_select_option', 1, $row->package_id );
	            apply_filters( 'qcld_backlink_exchange_link_select_option', 1, $row->package_id );


				?>

				<?php
				$pkglist = $wpdb->get_results("select ppt.id as id, ppt.expire_date as expiredate, pt.title, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");

				foreach($pkglist as $r){
					$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = ".$r->id." and user_id =".$current_user->ID);
					
					if($row->recurring==1){
						if(trim($row->status)!='cancel' and $r->total_item > $submited_item->cnt){
							if($row->package_id==$r->id){
								echo '<option value="'.$r->id.'" selected="selected">'.$r->title.'</option>';
							}else{
								echo '<option value="'.$r->id.'">'.$r->title.'</option>';
							}

						}
					}else{
						if(strtotime(date('Y-m-d')) < strtotime($r->expiredate) and $r->total_item > $submited_item->cnt){
							if($row->package_id==$r->id){
								echo '<option value="'.$r->id.'" selected="selected">'.$r->title.'</option>';
							}else{
								echo '<option value="'.$r->id.'">'.$r->title.'</option>';
							}

						}
					}
					
				}

				?>
            </select>

        </li>
        <?php apply_filters( 'qcld_exchange_link_html', $row->link_form, $row->link_to ); ?>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_category')!=''?sld_get_option('sld_lan_change_category'):__('Category', 'qc-opd')) ?></label>
			
			<?php 
			$taxonomy = 'sld_cat';
			$terms = get_terms($taxonomy); //
			if ( $terms && !is_wp_error( $terms ) ) :
			?>
				<select id="qc_sld_category" class="sld_text_width" name="qc_sld_category" >
					<option value="" ><?php echo (sld_get_option('sld_lan_change_all_category')!=''?sld_get_option('sld_lan_change_all_category'):__('All Categories', 'qc-opd')) ?></option>
					<?php foreach ( $terms as $term ) {?>
						<?php if($term->name==$row->category): ?>
							<option value="<?php echo $term->name; ?>"selected="selected"><?php echo esc_attr($term->name); ?></option>
						<?php else: ?>
							<option value="<?php echo $term->name; ?>"><?php echo esc_attr($term->name); ?></option>
						<?php endif; ?>
						
					<?php } ?>
				</select>
			<?php
			endif;
			?>
		</li>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_select_list')!=''?sld_get_option('sld_lan_change_select_list'):__('Select List', 'qc-opd')); ?> <span class="sld_required">*</span></label>
			<select id="qc_sld_list" class="sld_text_width" name="qc_sld_list" required>
				<?php
					if(!empty($row->category)){
						$sld = new WP_Query( array( 
							'post_type' 		=> 'sld',
							'posts_per_page' 	=> -1,
							'tax_query' 		=> array(
								array (
									'taxonomy' 	=> 'sld_cat',
									'field' 	=> 'name',
									'terms' 	=> $row->category
								)
							),
							'order' 			=> 'ASC',
							'orderby' 			=> 'menu_order'
							) 
						);
					}else{
						$sld = new WP_Query( array( 
							'post_type' => 'sld',
							'posts_per_page' => -1,
							'order' => 'ASC',
							'orderby' => 'menu_order'
							) 
						);
					}
					
					while( $sld->have_posts() ) : $sld->the_post();
					?>
						<?php if(get_the_ID()==$row->sld_list): ?>
							<option value="<?php echo get_the_ID(); ?>" selected="selected"><?php the_title(); ?></option>
						<?php else: ?>
							<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
						<?php endif; ?>
					<?php
					endwhile;
					wp_reset_query();
				?>
			</select>
		</li>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_http')!=''?sld_get_option('sld_lan_change_link_http'):__('Link (Include http:// or https://)', 'qc-opd')) ?> <span class="sld_required">*</span></label>
			<input type="text" id="sld_item_link" name="item_link" class="field-long sld_text_width" value="<?php echo esc_url($row->item_link); ?>" required />
		</li>
		<li>
            <label><?php echo (sld_get_option('sld_lan_change_link_generotor')!=''?sld_get_option('sld_lan_change_link_generotor'):__('Generator info from Link', 'qc-opd')) ?> </label>
            <input type="button" id="sld_generate" class="" value="<?php echo (sld_get_option('sld_lan_change_generotor')!=''?sld_get_option('sld_lan_change_generotor'):__('Generate', 'qc-opd')) ?>" />
        </li>
		<li><label><?php echo (sld_get_option('sld_lan_change_link_title')!=''?sld_get_option('sld_lan_change_link_title'):__('Link Title', 'qc-opd')) ?> <span class="sld_required">*</span></label><input type="text" name="item_title" id="sld_title" class="field-long sld_text_width" value="<?php echo esc_html($row->item_title); ?>" required/></li>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_subtitle')!=''?sld_get_option('sld_lan_change_link_subtitle'):__('Link Subtitle', 'qc-opd')) ?> </label>
			<input type="text" id="sld_subtitle" name="item_subtitle" class="field-long sld_text_width" value="<?php echo esc_html($row->item_subtitle); ?>"  />
		</li>

		
		<?php 		
		if((sld_get_option('sld_enable_long_desc_free_submission') =='on') || ( sld_get_option('sld_enable_long_desc_on_off')=='on') ): ?>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_long_des')!=''?sld_get_option('sld_lan_change_link_long_des'):__('Link Long Description', 'qc-opd')) ?> </label>
			<textarea class="field-long sld_text_width" name="item_long_description"><?php echo ($row->description); ?></textarea>
		</li>
        <?php endif; ?>

		<?php if(sld_get_option('sld_enable_frontend_submission_tag_field')=='on'){ ?>
	        <li>
				<label><?php echo (sld_get_option('sld_lan_change_link_tags')!=''?sld_get_option('sld_lan_change_link_tags'):__('Tags', 'qc-opd')) ?> </label>
				<input type="text" id="sld_tags" name="sld_tags" class="field-long sld_text_width" value="<?php echo $row->qcopd_tags; ?>"  />
				<input type="hidden" />
			</li>
		<?php } ?>
        
	<?php if(sld_get_option('sld_image_upload')=='on'){ ?>
		<?php if(sld_get_option('sld_enable_auto_generate_btn')=='on'){ ?>	
	        <li>
	            <label><?php echo (sld_get_option('sld_lan_change_link_generotor_image')!=''?sld_get_option('sld_lan_change_link_generotor_image'):__('Generator Image from Link', 'qc-opd')) ?> </label>
	            <input type="button" id="sld_generate_image_from_link" class="" value="<?php echo (sld_get_option('sld_lan_change_generotor_image')!=''?sld_get_option('sld_lan_change_generotor_image'):__('Generator Image', 'qc-opd')) ?>" />
	        </li>
        <?php } ?>

		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_image')!=''?sld_get_option('sld_lan_change_link_image'):__('Link Image', 'qc-opd')) ?></label>
			
			<input type="file" name="sld_link_image" id="sld_link_image" >
			
			<div style="clear:both"></div>
			<div id="sld_preview_img">
				<?php if($row->image_url!=''): ?>
					<span class="sld_remove_bg_image">X</span>
					<img src="<?php echo $row->image_url ?>" alt="">
				<?php endif; ?>
			</div>
			
		</li>
	<?php } ?>
		
		
		<?php if(sld_get_option('sld_disable_no_follow')!='on'){ ?>
        <li>
            <label><?php echo (sld_get_option('sld_lan_change_link_no_follow')!=''?sld_get_option('sld_lan_change_link_no_follow'):__('No Follow', 'qc-opd')) ?> </label>
            <input type="checkbox" name="item_no_follow" <?php echo ($row->nofollow==1?'checked="checked"':''); ?> class="" value="1" />
        </li>
        <?php }else{
?>
		<li>
            <label><?php echo (sld_get_option('sld_lan_change_link_no_follow')!=''?sld_get_option('sld_lan_change_link_no_follow'):__('No Follow', 'qc-opd')) ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" <?php echo ($row->nofollow==1?'checked="checked"':''); ?> disabled="" />
        </li>
<?php } ?>

		
		<li>
			<input type="hidden" name="uid" value="<?php echo $recid; ?>" />
			<input type="submit" class="sld_submit_style" value="<?php echo (sld_get_option('sld_lan_change_link_submit')!=''?sld_get_option('sld_lan_change_link_submit'):__('Submit', 'qc-opd')) ?>" />
		</li>
	</ul>
</form>
<?php 
}else{
	echo __('<p>Something Went Wrong.</p>','qc-opd');
}
?>