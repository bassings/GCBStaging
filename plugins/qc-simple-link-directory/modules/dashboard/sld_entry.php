<?php 
 //code for form data entry
global $wp;
$current_url =  get_permalink( get_page_by_path($wp->request) );
$current_user = wp_get_current_user();

if(isset($_POST['item_title']) and $_POST['item_title']!='' and $_POST['package_id']!=''){

	
	$item_title = isset($_POST['item_title']) ? sanitize_text_field(stripcslashes($_POST['item_title'])) : '';
	$item_link = isset($_POST['item_link']) ? sanitize_text_field($_POST['item_link']) : '';
	$item_subtitle = isset($_POST['item_subtitle']) ? sanitize_text_field($_POST['item_subtitle']) : '';
	$item_description = isset($_POST['item_long_description']) ? sanitize_text_field($_POST['item_long_description']) : '';
	if(isset($_POST['item_no_follow']) and $_POST['item_no_follow']==1){
	    $item_no_follow = 1;
    }else{
		$item_no_follow = 0;
    }


    $post_id =1;
  


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
		  
		  	//$file_ext = strtolower(end(explode('.',$_FILES['sld_link_image']['name'])));
  			$file_ext 	= explode('.', $file_name);
			$file_ext 	= strtolower( end($file_ext) );
		  	$custom_name = strtolower(explode('.',$_FILES['sld_link_image']['name'])[0]);
		  	$file_name  = $custom_name.'_'.time().'.'.$file_ext;
		  
		  	$expensions = array("jpeg","jpg","png","gif");
		  
		  	if(in_array($file_ext,$expensions)=== false){
			 	$errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
		  	}
		  
		  	if($file_size > 2097152){
			 	$errors[] = 'File size must be excately 2 MB';
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
			$imageurl = '';
	    }
	}

	if( isset($_POST['qc_sld_category']) && !empty($_POST['qc_sld_category']) ){
		$qc_sld_category = sanitize_text_field($_POST['qc_sld_category']);
	}else{
		$qc_sld_category = '';
	}
	$qc_sld_list = isset($_POST['qc_sld_list']) ? sanitize_text_field($_POST['qc_sld_list']) : '';
	$datetime = date('Y-m-d H:i:s');
	$package_id = isset($_POST['package_id']) ? sanitize_text_field($_POST['package_id']) : '';

	$sld_tag = isset($_POST['sld_tags']) ? $_POST['sld_tags'] : '';

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



		$wpdb->insert(
			$table,
			array(
				'item_title'  	=> $item_title,
				'item_link'   	=> $item_link,
				'item_subtitle' => $item_subtitle,
				'category'   	=> $qc_sld_category,
				'sld_list'  	=> $qc_sld_list,
				'user_id'		=> $current_user->ID,
				'image_url'		=> $imageurl,
				'time'			=> $datetime,
				'nofollow'		=> $item_no_follow,
                'package_id'	=> $package_id,
				'link_form'		=> $item_link_exchange,
				'link_to'		=> $item_link_exchange_url,
				'link_anchor_text'=> isset($qcld_anchor_text) ? $qcld_anchor_text: '',
				'link_anchor_attr'=> isset($qcld_status_text) ? $qcld_status_text: '',
				'link_status'	=> isset($qcld_link_status) ? $qcld_link_status: '',
				'description'	=> $item_description,
				'qcopd_tags' 	=> $sld_tag
			)
		);
		wp_reset_query();


    if(in_array('administrator',$current_user->roles)){
        $lastid = $wpdb->insert_id;
        $this->approve_subscriber_profile($lastid);

        
        $success_msg = sld_get_option('dashboard_lan_text_success_msg')!=''?sld_get_option('dashboard_lan_text_success_msg'):__('Your list link has been successfully published.', 'qc-opd');

        echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__( $success_msg,'qc-opd').' </div>';
    }else{

	    if(sld_get_option('sld_email_notification')=='on'){
	    	if(isset($package_id) && $package_id == '555'){

	        	$this->sld_new_item_notification_link_exchange($current_user->ID, $item_title);
	    		
	    	}else if(isset($package_id) && $package_id == '7777'){

	        	$this->sld_new_item_notification_offline_payments($current_user->ID, $item_title);
	    		
	    	}else{

	        	$this->sld_new_item_notification($current_user->ID, $item_title);

	    	}
        }

        if(sld_get_option('sld_enable_auto_approval')=='on' && (isset($package_id) && $package_id !== '7777')){

            $lastid = $wpdb->insert_id;
            $this->approve_subscriber_profile($lastid);

            $success_msg = sld_get_option('dashboard_lan_text_success_msg')!=''?sld_get_option('dashboard_lan_text_success_msg'):__('Your list link has been successfully published.', 'qc-opd');

            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__( $success_msg,'qc-opd').' </div>';

        }else{

        	$renew_success_msg = sld_get_option('dashboard_lan_text_renew_success_msg')!=''?sld_get_option('dashboard_lan_text_renew_success_msg'):__('Your link has been successfully submitted. We will review your item information before Publishing. Thank you for your patience.', 'qc-opd');

            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__( $renew_success_msg,'qc-opd').' <br/></div>';

        }
    }

	if(!empty($errors)){
		foreach($errors as $error){
			echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.$error.'</div>';
		}
	}

}
if($this->allow_item_submit==false){
	$free_link_submission_limit = sld_get_option('dashboard_lan_text_free_link_submission_limit') ? sld_get_option('dashboard_lan_text_free_link_submission_limit') : 'You have reached your free link submission limit.';
	echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__($free_link_submission_limit,'qc-opd').' <br/></div>';
	return;
}

$isFree = false;
?>

<h2><?php echo (sld_get_option('sld_lan_add_your_link')!=''?sld_get_option('sld_lan_add_your_link'):__('Add Your Link', 'qc-opd')) ?></h2>
<form action="<?php echo $current_url.'/?action=entry'; ?>" method="POST" enctype="multipart/form-data">
	<ul class="sld_form-style-1 sld_width">

		<li><label> <?php echo (sld_get_option('sld_lan_select_package')!=''?sld_get_option('sld_lan_select_package'):__('Select Package', 'qc-opd')) ?> <span class="sld_required">*</span></label>
            <select name="package_id">
				<?php
				$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = 0 and user_id =".$current_user->ID);
				if(in_array('administrator',$current_user->roles)){
					$isFree = true;
				    ?>
                    <option value="0"><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?> </option>
                    <?php
                }else{
					if(sld_get_option('sld_enable_free_submission')=='on'){
						$isFree = true;
						if(sld_get_option('sld_free_item_limit')!='' and sld_get_option('sld_free_item_limit') > $submited_item->cnt){
							
							?>
                            <option value="0"><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?></option>
							<?php
						}else{
							
							?>
                            <option value="0" disabled><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?></option>
							<?php
                        }
					}
                }

                apply_filters( 'qcld_offline_link_select_option', '','' );
                apply_filters( 'qcld_backlink_exchange_link_select_option', '','' );

				?>

				
                <?php
					$pkglist = $wpdb->get_results("select ppt.id as pid, ppt.package_id as id, ppt.expire_date as expiredate,ppt.recurring,ppt.status, pt.title, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");

					if(!empty($pkglist)){
						$isFree = false;
					}
					
                 foreach($pkglist as $row){
	                 $submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = ".$row->id." and user_id =".$current_user->ID);
					 if($row->recurring==1){
						
						if(trim($row->status)!='cancel' and $row->total_item > $submited_item->cnt){
							?>
							<option value="<?php echo $row->pid; ?>"><?php echo $row->title; ?></option>							
							<?php
						}else{
							?>
							<option value="<?php echo $row->pid; ?>" disabled><?php echo $row->title; ?></option>
							<?php
						}
						
					 }else{
						 
						if(strtotime(date('Y-m-d')) < strtotime($row->expiredate) and $row->total_item > $submited_item->cnt){
						 ?>
							 <option value="<?php echo $row->pid; ?>"><?php echo $row->title; ?></option>
						<?php
						 }else{
							 ?>
							 <option value="<?php echo $row->pid; ?>" disabled><?php echo $row->title; ?></option>
							<?php
						 }
						 
					 }
                     
                 }
                 ?>
            </select>
        </li>
        <?php apply_filters( 'qcld_exchange_link_html', '', '' ); ?>
				<li>
			<label><?php echo (sld_get_option('sld_lan_change_category')!=''?sld_get_option('sld_lan_change_category'):__('Category', 'qc-opd')); ?></label>
			
				<?php 
					$taxonomy = 'sld_cat';
					$terms = get_terms($taxonomy); //

					/*$cterms = get_terms( 'sld_cat', array(
						'hide_empty' => true,
						'orderby' => 'name',
						'order' => 'ASC' 
					) );*/

					// var_dump($terms);
					
					if ( $terms && !is_wp_error( $terms ) ) :
					?>
						<select id="qc_sld_category" class="sld_text_width" name="qc_sld_category">
							<option value="" ><?php echo (sld_get_option('sld_lan_change_all_category')!=''?sld_get_option('sld_lan_change_all_category'):__('All Categories', 'qc-opd')); ?></option>
							<?php foreach ( $terms as $term ) { 
							
							?>
								<option value="<?php echo $term->name; ?>"><?php echo esc_attr($term->name); ?></option>
							<?php } ?>
						</select>
					<?php
					endif;
					$excluded_lists = sld_get_option('sld_exclude_list');
					$excluded_lists = explode(',',$excluded_lists);
				?>
		</li>
		<li>
			<label> <?php echo (sld_get_option('sld_lan_change_select_list')!=''?sld_get_option('sld_lan_change_select_list'):__('Select List', 'qc-opd')); ?> <span class="sld_required">*</span></label>
			<select id="qc_sld_list" class="sld_text_width" name="qc_sld_list" required>
				<?php
					$sld = new WP_Query( array( 
						'post_type' 		=> 'sld',				
						'posts_per_page' 	=> -1,
						'order' 			=> 'ASC',
						'orderby' 			=> 'menu_order'
						) 
					);
					
					while( $sld->have_posts() ) : $sld->the_post();
						if( !empty($excluded_lists) ){
							if(!in_array(get_the_ID(),$excluded_lists)){
							?>
								<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
							<?php
							}
						}else{
					?>
							<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
					<?php
						}
					endwhile;
					wp_reset_query();
				?>

			</select>
		</li>
		<li>
			<label> <?php echo (sld_get_option('sld_lan_change_link_http')!=''?sld_get_option('sld_lan_change_link_http'):__('Link (Include http:// or https://)', 'qc-opd')) ?> <span class="sld_required">*</span> </label>
			<input type="text" id="sld_item_link" name="item_link" class="field-long sld_text_width" value="" required />
		</li>
	
		<li>
            <label><?php echo (sld_get_option('sld_lan_change_link_generotor')!=''?sld_get_option('sld_lan_change_link_generotor'):__('Generator info from Link', 'qc-opd')) ?></label>
            <input type="button" id="sld_generate" class="" value="<?php echo (sld_get_option('sld_lan_change_generotor')!=''?sld_get_option('sld_lan_change_generotor'):__('Generate', 'qc-opd')) ?>" />
        </li>
	
        <li><label><?php echo (sld_get_option('sld_lan_change_link_title')!=''?sld_get_option('sld_lan_change_link_title'):__('Link Title', 'qc-opd')) ?> <span class="sld_required">*</span></label><input type="text" name="item_title" id="sld_title" class="field-long sld_text_width" value="" required/></li>
		
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_subtitle')!=''?sld_get_option('sld_lan_change_link_subtitle'):__('Link Subtitle', 'qc-opd')) ?> </label>
			<input type="text" id="sld_subtitle" name="item_subtitle" class="field-long sld_text_width" value=""  />
		</li>
		
		<?php 	

		if( (sld_get_option('sld_enable_long_desc_free_submission')=='on' && $isFree) || ( sld_get_option('sld_enable_long_desc_on_off')=='on') ): ?>
		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_long_des')!=''?sld_get_option('sld_lan_change_link_long_des'):__('Link Long Description', 'qc-opd')) ?> </label>
			<textarea class="field-long sld_text_width" name="item_long_description"></textarea>
		</li>
        <?php endif; ?>

        <?php if(sld_get_option('sld_enable_frontend_submission_tag_field')=='on'){ ?>
	        <li>
				<label><?php echo (sld_get_option('sld_lan_change_link_tags')!=''?sld_get_option('sld_lan_change_link_tags'):__('Tags', 'qc-opd')) ?> </label>
				<input type="text" id="sld_tags" name="sld_tags" class="field-long sld_text_width" value=""  />
				<input type="hidden" />
			</li>
		<?php } ?>
		
        <?php if(sld_get_option('sld_image_upload')=='on'){ ?>

	        <?php if(sld_get_option('sld_enable_auto_generate_btn')=='on'){ ?>	
		        <li>
		            <label> <?php echo (sld_get_option('sld_lan_change_link_generotor_image')!=''?sld_get_option('sld_lan_change_link_generotor_image'):__('Generator Image from Link', 'qc-opd')) ?> </label>
		            <input type="button" id="sld_generate_image_from_link" class="" value="<?php echo (sld_get_option('sld_lan_change_generotor_image')!=''?sld_get_option('sld_lan_change_generotor_image'):__('Generator Image', 'qc-opd')) ?>" />
		        </li>
	        <?php } ?>

		<li>
			<label><?php echo (sld_get_option('sld_lan_change_link_image')!=''?sld_get_option('sld_lan_change_link_image'):__('Link Image', 'qc-opd')) ?> </label>
			
			<input type="file" name="sld_link_image" id="sld_link_image" >
			
			<div style="clear:both"></div>
			<div id="sld_preview_img"></div>
			
		</li>
        <?php } ?>
		

<?php if(sld_get_option('sld_disable_no_follow')!='on'){ ?>
        <li>
            <label><?php echo (sld_get_option('sld_lan_change_link_no_follow')!=''?sld_get_option('sld_lan_change_link_no_follow'):__('No Follow', 'qc-opd')) ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" checked />
        </li>
<?php }else{ ?>
	<li>
            <label><?php echo (sld_get_option('sld_lan_change_link_no_follow')!=''?sld_get_option('sld_lan_change_link_no_follow'):__('No Follow', 'qc-opd')) ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" checked disabled="" />
        </li>
	<?php } ?>

        <li>
			<input type="submit" name="submititem" class="sld_submit_style" value="<?php echo (sld_get_option('sld_lan_change_link_submit')!=''?sld_get_option('sld_lan_change_link_submit'):__('Submit', 'qc-opd')) ?>" />
		</li>
	</ul>
</form>