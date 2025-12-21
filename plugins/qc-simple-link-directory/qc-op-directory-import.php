<?php

class Qcopd_BulkImport
{

    function __construct()
    {
        //Add a menu in admin panel to link Import Export
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    public $post_id;

    //Callback function for Import Export Menu
    function qcopd_info_menu()
    {
        add_submenu_page(
            'edit.php?post_type=sld',
            __('Bulk Import', 'qc-opd'),
            __('Import/Export', 'qc-opd'),
            'manage_options',
            'qcopd_bimport_page',
            array(
                $this,
                'qcopd_bimport_page_content'
            )
        );
    }

    function qcopd_bimport_page_content()
    {
        ?>
        <div class="wrap">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-3">

                    <div id="post-body-content">
    
                        <h1><?php esc_html_e('Bulk Export/Import', 'qc-opd'); ?></h1>
					<div class="sld-main-import-container">
                        <div>
                            <div class="qcld-sld-square-section-block notice notice-error qc-notice-error">
	                            <p style="color: red; margin: 15px">
									<strong><?php esc_html_e('Please Note:', 'qc-opd'); ?></strong> <?php esc_html_e('The Export Import Feature is still in Beta. We have been testing the feature extensively and it works great. However, before performing any sort of Imports, it is strongly recommended that you take a full backup of your website database first. So that if something went wrong during the import, you can revert and no data is lost.', 'qc-opd'); ?>
								</p>
							</div>

							<div class="qcld-sld-square-section-block">
								<p>
	                                <strong><?php esc_html_e('Sample List CSV File:', 'qc-opd'); ?></strong>
	                                <a href="<?php echo SLD_QCOPD_ASSETS_URL . '/file/sample-csv-file.csv'; ?>" target="_blank">
	                                    <?php esc_html_e('Download', 'qc-opd'); ?>
	                                </a>
	                            </p>

	                            <p><strong><?php esc_html_e('NOTES:', 'qc-opd'); ?></strong></p>

	                            <p>
	                                <ol>
	                                    <li><?php esc_html_e('Attached file should be a plain CSV file.', 'qc-opd'); ?></li>
	                                    <li><?php esc_html_e('File must be prepared as per provided sample CSV file or as per the exported CSV file.', 'qc-opd'); ?></li>
	                                    <li><?php esc_html_e('To add new items, Export your Lists. Edit the CSV file and add the new items to the relevant lists as needed. Import the CSV back using the button: Delete Existing Items the Add New Items.', 'qc-opd'); ?></li>

	                                    <li><?php esc_html_e('In you CSV set the Generate Image from Website Link Field value to 1 to auto generate image from website link.', 'qc-opd'); ?></li>
	                                    <li><?php esc_html_e('In you CSV set the Generate Description from Website Link Field value to 1 to auto generate title and subtitle from website link.', 'qc-opd'); ?></li>
	                                </ol>
	                            </p>
	                            <p><?php esc_html_e('For first time Import, we recommend, creating the Lists manually with 2/3 items in each list. Then follow the point no. 3 above.', 'qc-opd'); ?></p>
                        	</div>
                        </div>

				
						<div style="padding: 15px; margin: 20px 0;" id="sld-export-container" class="qcld-sld-square-section-block">
							<div >
								<h3><u><?php esc_html_e('Export to a CSV File', 'qc-opd'); ?></u></h3>

		                        <p>
		                        	<strong><u><?php esc_html_e('Option Details:', 'qc-opd'); ?></u></strong>
		                        </p>
		                        <p><?php esc_html_e('Export button will create a downloadable CSV file with all of your existing SLD lists and its elements.', 'qc-opd'); ?></p>
		                        <?php
								$args = array(
									'numberposts' => -1,
									'post_type'   => 'sld',
									'orderby'     => 'title',
									'order'       => 'ASC',
								);

								$listItemss = get_posts( $args );

								if( !empty( $listItemss )){ 

								?>
		                        <p>
									<select id="sld_export_select_list">
										<option value=""><?php esc_html_e('Export All Data'); ?></option>
										<?php 
										 	foreach ($listItemss as $item_data ) :
											$item_count_disp = "";
											$item_count_disp = count( get_post_meta( $item_data->ID, 'qcopd_list_item01'));
											?>
								            <option value="<?php echo esc_attr($item_data->ID); ?>" >
												<?php echo esc_html($item_data->post_title);
													echo '<span> ('.$item_count_disp.')</span>';
												?>
								            </option>
										<?php endforeach; ?>
									</select>
		                        </p>
		                    	<?php } ?>

								<a class="button-primary sld_export_select_btn" href="<?php echo admin_url( 'admin-post.php?action=sldprint.csv' ); ?>"><?php esc_html_e('Export SLD Data', 'qc-opd'); ?></a>
							</div>

                        </div>

                        <div style="padding: 15px; margin: 10px 0;" class="qcld-sld-square-section-block">

                        <h3><u><?php esc_html_e('Import from a CSV File', 'qc-opd'); ?></u></h3>

                        <p><strong><u><?php esc_html_e('Importing in Another Website:', 'qc-opd'); ?></u></strong> <?php esc_html_e('Please note that categories will not be copied if you import the full CSV to another WordPress installation.', 'qc-opd'); ?></p>

                        <p>
                        	<strong><u><?php esc_html_e('Option Details:', 'qc-opd'); ?></u></strong>
                        </p>
                        <p>
                        	<?php esc_html_e('In both of the below cases, attached CSV file must be identical as per the provided format or as per the exported format.', 'qc-opd'); ?>
                        </p>
                        <p>
                        	<strong><u><?php esc_html_e('Add New Items:', 'qc-opd'); ?> </u></strong>
                        	<?php esc_html_e('This option will add new lists and its elements from the CSV file. No lists or its elements get\'s deleted or updated by this option. If there exist any lists with the same title as CSV lines, then duplicate lists will get created during import.', 'qc-opd'); ?>
                        </p>
                        <p>
                        	<strong><u<?php esc_html_e('>Delete Existing Items then Add New Items:', 'qc-opd'); ?> </u></strong>
                        	<?php esc_html_e('This option will first delete ALL the existing SLD lists and its elements [without attached images] from the database, then it attempts to import lists and elements from the attached CSV file. This option is suitable for editing list elements. If you follow this option for a single site, then all previously attached images will get relinked.', 'qc-opd'); ?>
                        </p>

                        <!-- Handle CSV Upload -->

                        <?php

                        //Generate a 5 digit random number based on microtime
                        $randomNum = substr(sha1(mt_rand() . microtime()), mt_rand(0,35), 5);


                        /*******************************
                         * If Add New or Delete then Add New button was pressed
                         * then proceed for further processing
                         *******************************/
                        if( !empty($_POST) && isset($_POST['upload_csv']) || !empty($_POST) && isset($_POST['delete_upload_csv']) ) 
                        {

                        	//First check if the uploaded file is valid
                        	$valid = true;
                        	
                        	$allowedTypes = array(
                        			'application/vnd.ms-excel',
                        			'text/comma-separated-values', 
                        			'text/csv', 
                        			'application/csv', 
                        			'application/excel', 
                        			'application/vnd.msexcel', 
                        			'text/anytext',
                        			'application/octet-stream',
                        		);
							//echo $_FILES['csv_upload']['type'];exit;
                        	if( !in_array($_FILES['csv_upload']['type'], $allowedTypes) ){
                        		$valid = false;
                        	}

                        	if( ! $valid ){
                        		echo "Status: Invalid file type.";
                        	}
                            
                            //If the file is valid and delete button was pressed
                            if( $valid && !empty($_POST) && isset($_POST['delete_upload_csv']) )
                            {
                            	
                            	$allposts = get_posts( 'numberposts=-1&post_type=sld&post_status=any' );

								foreach( $allposts as $postinfo ) {

								    delete_post_meta( $postinfo->ID, 'qcopd_list_conf' );
								    delete_post_meta( $postinfo->ID, 'qcopd_list_item01' );
								    delete_post_meta( $postinfo->ID, 'sld_add_block' );

								    wp_delete_post( $postinfo->ID, true );

								}

                            }

                            //If the file is valid and client is logged in
                            if ( $valid && function_exists('is_user_logged_in') && is_user_logged_in() ) 
							{

                                $tmpName = $_FILES['csv_upload']['tmp_name'];
								
								if( $tmpName != "" )
								{
								
									$file = fopen($tmpName, "r");
                                    $flag = true;
									
									//Reading file and building our array
									
									$baseData = array();

									$count = 0;

									$laps = 1;
									
									//Read fields from CSV file and dump in $baseData
									while(($data = fgetcsv($file)) !== FALSE) 
									{
										
										if ($flag) {
											$flag = false;
											continue;
										}
										
										$baseData[$data[0]][] = array(
											'list_id' 					=> trim($data[0]),
											'list_title' 				=> isset($data[1]) 	? sanitize_text_field((trim($data[1]))) : '',
											'qcopd_item_title' 			=> isset($data[2]) 	? sanitize_text_field((trim($data[2]))) : '',
											'qcopd_item_link' 			=> isset($data[3]) 	? trim($data[3]) : '',
											'qcopd_item_nofollow' 		=> isset($data[4]) 	? trim($data[4]) : 0,
											'qcopd_item_ugc' 			=> isset($data[5]) 	? trim($data[5]) : '',
											'qcopd_item_newtab' 		=> isset($data[6]) 	? trim($data[6]) : 0,
											'qcopd_item_subtitle' 		=> isset($data[7]) 	? sanitize_text_field((trim($data[7]))) : '',
											'qcopd_fa_icon' 			=> isset($data[8]) 	? sanitize_text_field((trim($data[8]))) : '',
											'qcopd_use_favicon' 		=> isset($data[9]) 	? trim($data[9]) : '',
											'qcopd_item_img' 			=> isset($data[10]) ? trim($data[10]) : '',
											'qcopd_item_img_title' 		=> isset($data[11]) ? trim($data[11]) : '',
											'qcopd_item_img_link' 		=> isset($data[12]) ? trim($data[12]) : '',
											'qcopd_upvote_count' 		=> isset($data[13]) ? trim($data[13]) : 0,
											'list_item_bg_color' 		=> isset($data[14]) ? trim($data[14]) : '',
											'attached_terms' 			=> isset($data[15]) ? trim($data[15]) : '',
											'qcopd_entry_time' 			=> date("Y-m-d H:i:s"),
											'qcopd_timelaps' 			=> $laps,
											'qcopd_description' 		=> isset($data[31]) ? trim($data[31]) : '',
											'qcopd_tags' 				=> isset($data[32]) ? trim($data[32]) : '',
											'qcopd_new' 				=> isset($data[33]) ? trim($data[33]) : '',
											'qcopd_featured' 			=> isset($data[34]) ? trim($data[34]) : '',
											'qcopd_image_from_link' 	=> isset($data[35]) ? trim($data[35]) : '',
											'qcopd_generate_title' 		=> isset($data[36]) ? trim($data[36]) : '',
											'list_border_color' 		=> isset($data[16]) ? trim($data[16]) : '',
											'list_bg_color' 			=> isset($data[17]) ? trim($data[17]) : '',
											'list_bg_color_hov' 		=> isset($data[18]) ? trim($data[18]) : '',
											'list_txt_color' 			=> isset($data[19]) ? trim($data[19]) : '',
											'list_txt_color_hov' 		=> isset($data[20]) ? trim($data[20]) : '',
											'list_subtxt_color' 		=> isset($data[21]) ? trim($data[21]) : '',
											'list_subtxt_color_hov' 	=> isset($data[22]) ? trim($data[22]) : '',
											'item_bdr_color' 			=> isset($data[23]) ? trim($data[23]) : '',
											'item_bdr_color_hov' 		=> isset($data[24]) ? trim($data[24]) : '',
											'list_title_color'			=> isset($data[25]) ? trim($data[25]) : '',
											'filter_background_color'	=> isset($data[26]) ? trim($data[26]) : '',
											'filter_text_color'			=> isset($data[27]) ? trim($data[27]) : '',
											'add_block_text' 			=> isset($data[28]) ? sanitize_text_field((trim($data[28]))) : '',
											'menu_order' 				=> isset($data[29]) ? trim($data[29]) : '',
											'post_status' 				=> isset($data[30]) ? trim($data[30]) : '',
										);

										$count++;
										$laps++;

									}
									
									fclose($file);
									//print_r($baseData);exit;
									//Inserting Data from our built array
									
									$keyCounter = 0;
									$metaCounter = 0;
									
									global $wpdb;

									//Sort $baseData numerically
									ksort($baseData, SORT_NUMERIC);
									
									//Parse $baseData and insert in the database
									foreach( $baseData as $key => $data ){
									
										
										//Check menu order for current SLD post, set 0 if empty
										$menu_order_val = isset($data[0]['menu_order']) ? $data[0]['menu_order'] : 0;

										$post_id = (isset($data[0]['list_id']) && $data[0]['list_id'] != "" ) ? $data[0]['list_id'] : '';

										//Grab current LIST title
										$post_title = (isset($data[0]['list_title']) && $data[0]['list_title'] != "" ) ? $data[0]['list_title'] : '';

										//Grab current LIST status, set 'publish' if empty
										$post_status = (isset($data[0]['post_status']) && $data[0]['post_status'] != "" ) ? $data[0]['post_status'] : 'publish';

										if( !empty($post_id) ){
											$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'sld' AND post_status = 'publish' AND ID = $post_id ORDER BY ID DESC LIMIT 1");

											if( empty($post_id) && !empty($post_title) ){
												$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'sld' AND post_status = 'publish' AND post_title LIKE '%$post_title%' ORDER BY ID DESC LIMIT 1");
											}
										}

										//If $post_title is empty, then go for next iteration
										if( $post_title == '' ){
											continue;
										}

						
										$existing_list = false;

										//Existing post array
										if(!empty(get_post($post_id))){
											$newest_post_id = $post_id;
											$existing_list = true;
										}else{
											//Build post array and insert as new POST
											$post_arr = array(
												'post_title' 	=> trim($post_title),
												'post_status' 	=> $post_status,
												'post_author' 	=> get_current_user_id(),
												'post_type' 	=> 'sld',
												'menu_order' 	=> $menu_order_val,
											);

											wp_insert_post($post_arr);

											//Get the newest post ID, that we just inserted
											$newest_post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'sld' ORDER BY ID DESC LIMIT 1");
										}

										$attachedTerms = '';

										$innerListCounter = 0;

										$configArray = array();
										$addBlockArray = array();

										//Add list meta fields. i.e. list items and configs
										foreach( $data as $k => $item ){

											if( $innerListCounter == 0 && $existing_list===false ){

												$attachedTerms 							= isset($item['attached_terms']) 	? $item['attached_terms'] 		: '';
												$configArray['list_border_color'] 		= isset($item['list_border_color']) ? $item['list_border_color'] 	: '';
												$configArray['list_bg_color'] 			= isset($item['list_bg_color']) 	? $item['list_bg_color'] 		: '';
												$configArray['list_bg_color_hov'] 		= isset($item['list_bg_color_hov']) ? $item['list_bg_color_hov'] 	: '';
												$configArray['list_txt_color'] 			= isset($item['list_txt_color']) 	? $item['list_txt_color'] 		: '';
												$configArray['list_txt_color_hov'] 		= isset($item['list_txt_color_hov']) ? $item['list_txt_color_hov'] 	: '';
												$configArray['list_subtxt_color'] 		= isset($item['list_subtxt_color']) ? $item['list_subtxt_color'] 	: '';
												$configArray['list_subtxt_color_hov'] 	= isset($item['list_subtxt_color_hov']) ? $item['list_subtxt_color_hov']: '';
												$configArray['item_bdr_color'] 			= isset($item['item_bdr_color']) 	? $item['item_bdr_color'] 		: '';
												$configArray['item_bdr_color_hov'] 		= isset($item['item_bdr_color_hov']) ? $item['item_bdr_color_hov'] 	: '';
												$addBlockArray['add_block_text'] 		= isset($item['add_block_text']) 	? $item['add_block_text'] 		: '';

												add_post_meta(
													$newest_post_id, 
													'qcopd_list_conf', array(
														'list_border_color' 		=> 	isset($item['list_border_color']) 		? $item['list_border_color'] : '',
														'list_bg_color' 			=> 	isset($item['list_bg_color']) 			? $item['list_bg_color'] : '',
														'list_bg_color_hov' 		=> 	isset($item['list_bg_color_hov']) 		? $item['list_bg_color_hov'] : '',
														'list_txt_color' 			=> 	isset($item['list_txt_color']) 			? $item['list_txt_color'] : '',
														'list_txt_color_hov' 		=> 	isset($item['list_txt_color_hov']) 		? $item['list_txt_color_hov']: '',
														'list_subtxt_color' 		=> 	isset($item['list_subtxt_color']) 		? $item['list_subtxt_color'] : '',
														'list_subtxt_color_hov' 	=> 	isset($item['list_subtxt_color_hov'])	? $item['list_subtxt_color_hov']: '',
														'item_bdr_color' 			=> 	isset($item['item_bdr_color']) 			? $item['item_bdr_color'] : '',
														'item_bdr_color_hov' 		=>  isset($item['item_bdr_color_hov']) 		? $item['item_bdr_color_hov']: '',
														'list_title_color' 			=>  isset($item['list_title_color']) 		? $item['list_title_color'] : '',
														'filter_background_color' 	=>  isset($item['filter_background_color']) ? $item['filter_background_color']: '',
														'filter_text_color' 		=>  isset($item['filter_text_color']) 		? $item['filter_text_color']: '',
													)
												);

												add_post_meta(
													$newest_post_id, 
													'sld_add_block', array(
														'add_block_text' =>  $item['add_block_text'],
													)
												);

												$innerListCounter++;
											}


											$qcopd_item_title = ( isset($item['qcopd_item_title']) && !empty($item['qcopd_item_title']) ) ? iconv(mb_detect_encoding($item['qcopd_item_title']), "UTF-8", $item['qcopd_item_title']) : '';
											$qcopd_item_subtitle = ( isset($item['qcopd_item_subtitle']) && !empty($item['qcopd_item_subtitle']) ) ? iconv(mb_detect_encoding($item['qcopd_item_subtitle']), "UTF-8", $item['qcopd_item_subtitle']) : '';

											$qcopd_generate_title 	= isset($item['qcopd_generate_title']) ? $item['qcopd_generate_title'] : '';
											$qcopd_item_link 		= isset($item['qcopd_item_link']) ? $item['qcopd_item_link'] : '';

											if( isset($qcopd_generate_title)  && ( $qcopd_generate_title == 1 ) && isset($qcopd_item_link) && !empty($qcopd_item_link) ){

												
												$qcopd_item_title = apply_filters('sld_auto_generate_title_from_website_link_filter', $qcopd_item_link, $qcopd_item_title );
												$qcopd_item_subtitle = apply_filters('sld_auto_generate_subtitle_from_website_link_filter', $qcopd_item_link, $qcopd_item_subtitle );


											}


											
											$attachment_id = "";
											$attachmentId = isset($item['qcopd_item_img']) ? $item['qcopd_item_img'] : '';
											$qcopd_image_from_link = isset($item['qcopd_image_from_link']) ? $item['qcopd_image_from_link'] : '';

											$externalImageLinks 	= ( isset($item['qcopd_item_img_link'])  && !empty($item['qcopd_item_img_link'])  ) ? trim($item['qcopd_item_img_link']) : '';

											$sld_direct_link_img_upload_for_list_item = sld_get_option('sld_direct_link_img_upload_for_list_item');

											if( isset($qcopd_image_from_link) && ( $qcopd_image_from_link == 1 ) && isset($qcopd_item_link) && !empty($qcopd_item_link) ){

												$qcld_image_filename = ( isset($qcopd_item_title) && !empty($qcopd_item_title) ) ? $qcopd_item_title : 'sldwebsite';

												$attachment_id = apply_filters('sld_auto_generate_image_from_website_link_filter', $qcopd_item_link, $qcld_image_filename, $attachmentId );



											}else if( !empty( $attachmentId ) && is_numeric($attachmentId) && wp_get_attachment_url( $attachmentId ) ){
											  $attachment_id = $attachmentId;
											}else if( !empty( $attachmentId ) ){

												$image_attachment_id = attachment_url_to_postid( $attachmentId );

												if( isset($image_attachment_id) && !empty($image_attachment_id) ){
													$attachment_id = $image_attachment_id;
												}else{

													require_once ABSPATH . 'wp-admin/includes/media.php';
													require_once ABSPATH . 'wp-admin/includes/file.php';
													require_once ABSPATH . 'wp-admin/includes/image.php';
													if (filter_var($attachmentId, FILTER_VALIDATE_URL)) {
												        // Get the file type
												        $file_type = wp_check_filetype(basename($attachmentId), null);

												        // Prepare an array of data for the attachment
												        $attachment = array(
												            'post_title'     => sanitize_file_name(basename($attachmentId)),
												            'post_mime_type' => $file_type['type'],
												        );

												        // Try to upload the image
												        $attachment_id = media_sideload_image($attachmentId, 0, null, 'id');
												    }

												}


											}else if( !empty( $externalImageLinks ) && ( isset( $sld_direct_link_img_upload_for_list_item ) && ( $sld_direct_link_img_upload_for_list_item == 'on' ) ) ){

												require_once ABSPATH . 'wp-admin/includes/media.php';
												require_once ABSPATH . 'wp-admin/includes/file.php';
												require_once ABSPATH . 'wp-admin/includes/image.php';

												if (filter_var($externalImageLinks, FILTER_VALIDATE_URL)) {

													$array              = explode('/', getimagesize($externalImageLinks)['mime']);
									                $imagetype          = end($array);
									                
									                $qcld_article_text 	= ( isset($item['qcopd_item_title'])  && !empty($item['qcopd_item_title'])  ) ? trim($item['qcopd_item_title']) : '';

									                $uniq_name          = preg_replace( '%\s*[-_\s]+\s*%', ' ',  substr($qcld_article_text, 0, 50) );
									                $uniq_name          = str_replace( ' ', '-',  $uniq_name );
									                $uniq_name          = strtolower( $uniq_name );
									                $uniq_name          = preg_replace('/[^a-zA-Z0-9_ -]/s', '',$uniq_name);
									                $filename           = $uniq_name .'-'. uniqid() . '.' . $imagetype;

									                $uploaddir          = wp_upload_dir();
									                $target_file_name   = $uploaddir['path'] . '/' . $filename;

									                $contents           = file_get_contents( $externalImageLinks );

									                if(!empty($contents)){

										                $savefile           = fopen($target_file_name, 'w');
										                fwrite($savefile, $contents);
										                fclose($savefile);

										                /* add the image title */
										                $image_title        = ucwords( $uniq_name );

										                unset($externalImageLinks);

										                /* insert the attachment */
										                $wp_filetype = wp_check_filetype(basename($target_file_name), null);
										                $attachment  = array(
										                    'guid'              => $uploaddir['url'] . '/' . basename($target_file_name),
										                    'post_mime_type'    => $wp_filetype['type'],
										                    'post_title'        => $image_title,
										                    'post_status'       => 'inherit'
										                );
										                $post_id     = isset($_REQUEST['post_id']) ? absint( sanitize_text_field( $_REQUEST['post_id'])): '';
										                $attachment_id   = wp_insert_attachment($attachment, $target_file_name, $post_id);

											       
											    	}
											    	
											    }




											}
											
											add_post_meta(
												$newest_post_id, 
												'qcopd_list_item01', array(
													'qcopd_item_title' 		=> 	$qcopd_item_title,
													'qcopd_item_link' 		=> 	isset($item['qcopd_item_link']) 		? $item['qcopd_item_link']		: '',
													'qcopd_item_subtitle' 	=> 	$qcopd_item_subtitle,
													'qcopd_item_nofollow' 	=> 	isset($item['qcopd_item_nofollow']) 	? $item['qcopd_item_nofollow']	: '',
													'qcopd_item_ugc' 		=> 	isset($item['qcopd_item_ugc']) 			? $item['qcopd_item_ugc']		: '',
													'qcopd_item_newtab' 	=> 	isset($item['qcopd_item_newtab']) 		? $item['qcopd_item_newtab']	: '',
													'qcopd_fa_icon' 		=> 	isset($item['qcopd_fa_icon']) 			? $item['qcopd_fa_icon']		: '',
													'qcopd_use_favicon' 	=> 	isset($item['qcopd_use_favicon']) 		? $item['qcopd_use_favicon']	: '',
													'qcopd_item_img' 		=> 	$attachment_id,
													'qcopd_upvote_count' 	=> 	isset($item['qcopd_upvote_count']) 		? $item['qcopd_upvote_count']	: 0,
													'list_item_bg_color' 	=> 	isset($item['list_item_bg_color']) 		? $item['list_item_bg_color']	: '',
													'qcopd_entry_time' 		=>  isset($item['qcopd_entry_time']) 		? $item['qcopd_entry_time']		: '',
													'qcopd_timelaps' 		=>  isset($item['qcopd_timelaps']) 			? $item['qcopd_timelaps']		: '',
													'qcopd_item_img_link' 	=>  isset($item['qcopd_item_img_link']) 	? $item['qcopd_item_img_link']	: '',
													'qcopd_description' 	=>  isset($item['qcopd_description']) 		? iconv(mb_detect_encoding($item['qcopd_description']), "UTF-8", $item['qcopd_description'])	: '',
													'qcopd_featured' 		=>  isset($item['qcopd_featured']) 			? $item['qcopd_featured']		: '',
													'qcopd_image_from_link' =>  isset($item['qcopd_image_from_link']) 	? $item['qcopd_image_from_link'] : '',
													'qcopd_generate_title'  =>  isset($item['qcopd_generate_title']) 	? $item['qcopd_generate_title'] : '',
													'qcopd_new' 			=>  isset($item['qcopd_new']) 				? $item['qcopd_new']			: '',
													'qcopd_tags' 			=>  isset($item['qcopd_tags']) 				? $item['qcopd_tags']			: '',
												)
											);
											
											$metaCounter++;
											
										} //end of inner-foreach
										
										$keyCounter++;

										//Relate terms, if exists
										if( !empty($attachedTerms) ){
											
											$termIds = array();

											$postTerms = explode(',', $attachedTerms);

											foreach ($postTerms as $term ) {

												$termId = intval(trim($term));

												$term_name = trim($term);

												if( term_exists($termId, 'sld_cat') ) {

													array_push($termIds, $termId);
													
												}else if(!empty($term_name)) {

													if( term_exists( $term_name, 'sld_cat' ) ){

		                                        		$term_id 	= term_exists( $term_name, 'sld_cat' );
		                                        		$term_id 	= isset($term_id['term_id']) ? intval($term_id['term_id']) : intval($term_id);
		                                        		array_push($termIds, $term_id);

		                                        	}else{
											       	
											       		$term_id 	= wp_insert_term(
											           					$term_name,
											           					'sld_cat',
															           	array(
															             	'description' => ''
															           	)
											       					);
											       		
											       		$term_id 	= isset($term_id['term_id']) ? intval($term_id['term_id']) : intval($term_id);
		                                        		array_push($termIds, $term_id);

		                                        	}


												}

											}

											wp_set_post_terms( $newest_post_id, $termIds, 'sld_cat' );

										}
									
									} //end of outer-foreach

									//Display iteration result
									if( ( isset($keyCounter) && $keyCounter > 0 ) && ( isset($metaCounter) && $metaCounter > 0 ) ){

										echo  '<div><span style="color: red; font-weight: bold;">'.esc_html('RESULT:', 'qc-opd').'</span> <strong>'.$keyCounter.'</strong> '.esc_html('entry with', 'qc-opd').' <strong>'.$metaCounter.'</strong> '.esc_html('element(s) was made successfully.', 'qc-opd').'</div>';
									}
								
							    }
								else
								{
								   echo esc_html("Status: Please upload a valid CSV file.", "qc-opd");
								}

                            }

                        } 
                        else 
                        {
							//echo "Attached file is invalid!";
                        }

                        ?>
                            <br>
                            
                            <p>
                                <strong>
                                    <?php esc_html_e('Upload a CSV file here to Import: ', 'qc-opd'); ?>
                                </strong>
                            </p>

                            <form name="uploadfile" id="uploadfile_form" method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
                                
                                <?php wp_nonce_field('qcsld_import_nonce', 'qc-opd'); ?>

                                <p>
                                    <?php esc_html_e('Select file to upload', 'qc-opd') ?>
                                    <input type="file" name="csv_upload" id="csv_upload" size="35" class="uploadfiles"/>
                                </p>
								<p style="color:red;"><?php esc_html_e('**CSV File & Characters must be saved with UTF-8 encoding**', 'qc-opd') ?></p>
                                <p>
                                    <input class="button-primary sld-add-as-new" type="submit" name="upload_csv" id="" value="<?php esc_html_e('Add New Items', 'qc-opd') ?>"/>

                                    <input class="button-primary delete-old" type="submit" name="delete_upload_csv" id="" value="<?php esc_html_e('Delete Existing Items then Add New Items', 'qc-opd') ?>"/>
                                </p>
								

                            </form>
                            <br>
                        </div>
                        <div>

							<div class="qcld-sld-square-section-block">
								<p>
	                                <strong><?php esc_html_e('Sample Categories CSV File:', 'qc-opd'); ?></strong>
	                                <a href="<?php echo SLD_QCOPD_ASSETS_URL . '/file/sample-csv-file-cat.csv'; ?>" target="_blank">
	                                    <?php esc_html_e('Download', 'qc-opd'); ?>
	                                </a>
	                            </p>

	                            <p><strong><?php esc_html_e('NOTES:', 'qc-opd'); ?></strong></p>

	                            <p>
	                                <ol>
	                                    <li><?php esc_html_e('Attached file should be a plain CSV file.', 'qc-opd'); ?></li>
	                                    <li><?php esc_html_e('File must be prepared as per the provided sample Categories CSV file.', 'qc-opd'); ?></li>
	                                    <li><b><?php esc_html_e('Image ID:'); ?> </b> <?php esc_html_e('You need to add the Image url to the csv file in the Image url field.', 'qc-opd'); ?></li>
	                                    <li><b><?php esc_html_e('List ID:'); ?> </b> <?php esc_html_e('If you want to use the current, already created categories then use the exact same Cateory names or the Category IDs. If you want to add multiple lists to the same category, you have to add the list IDs separated by dashes.', 'qc-opd'); ?></li>
	                                    <li><?php esc_html_e('Category Name field is required. Enter one category name per row. Other fields are optional.', 'qc-opd'); ?></li>
	                                </ol>
	                            </p>
	                            
                        	</div>
                            
                        </div>
				
						<div style="padding: 15px; margin: 20px 0;" id="sld-export-container" class="qcld-sld-square-section-block">
							<div >
								<h3><u><?php esc_html_e('Export to Categories CSV File', 'qc-opd'); ?></u></h3>

		                        <p>
		                        	<strong><u><?php esc_html_e('Option Details:', 'qc-opd'); ?></u></strong>
		                        </p>
		                        <p><?php esc_html_e('Export button will create a downloadable Categories CSV file with all of your existing SLD Categories.', 'qc-opd'); ?></p>

								<a class="button-primary" href="<?php echo admin_url( 'admin-post.php?action=sldcategoryprint.csv' ); ?>"><?php esc_html_e('Export SLD Categories', 'qc-opd'); ?></a>
							</div>

                        </div>

                        <div style="padding: 15px; margin: 10px 0;" class="qcld-sld-square-section-block">
                            
                            <p>
                                <strong>
                                    <?php esc_html_e('Upload a CSV file here to Import List Categories: ', 'qc-opd'); ?>
                                </strong>
                            </p>

                            <?php

                        //Generate a 5 digit random number based on microtime
                        $randomNum = substr(sha1(mt_rand() . microtime()), mt_rand(0,35), 5);


                        /*******************************
                         * If Add New or Delete then Add New button was pressed
                         * then proceed for further processing
                         *******************************/
                        if( !empty($_POST) && isset($_POST['upload_csv_category']) ) 
                        {

                            //First check if the uploaded file is valid
                            $valid = true;
                            
                            $allowedTypes = array(
                                    'application/vnd.ms-excel',
                                    'text/comma-separated-values', 
                                    'text/csv', 
                                    'application/csv', 
                                    'application/excel', 
                                    'application/vnd.msexcel', 
                                    'text/anytext',
                                    'application/octet-stream',
                                );
                            //echo $_FILES['csv_upload']['type'];exit;
                            if( !in_array($_FILES['csv_upload_category']['type'], $allowedTypes) ){
                                $valid = false;
                            }

                            if( ! $valid ){
                                echo "Status: Invalid file type.";
                            }

                            //If the file is valid and client is logged in
                            if ( $valid && function_exists('is_user_logged_in') && is_user_logged_in() ) {


                                $tmpName = $_FILES['csv_upload_category']['tmp_name'];
                                
                                if( $tmpName != "" ){


                                
                                    $files = fopen($tmpName, "r");
                                    $flags = true;
                                    
                                    //Reading file and building our array
                                    
                                    $baseData = array();

                                    $count = 0;

                                    $laps = 1;
                                    
                                    //Read fields from CSV file and dump in $baseData
                                    while(($data = fgetcsv($files)) !== FALSE) {

                                        if ($flags) {
                                            $flags = false;
                                            continue;
                                        }

                                        $category_name 		= isset($data[0])  ? sanitize_text_field((trim($data[0]))) : '';
                                        $description 		= isset($data[1])  ? sanitize_text_field((trim($data[1]))) : '';
                                        $tab_color 			= isset($data[2])  ? sanitize_text_field((trim($data[2]))) : '';
                                        $tab_text_color 	= isset($data[3])  ? sanitize_text_field((trim($data[3]))) : '';
                                        $category_image_id 	= isset($data[4])  ? sanitize_text_field((trim($data[4]))) : '';
                                        $new_post_id 		= isset($data[5])  ? sanitize_text_field((trim($data[5]))) : '';

                                        if( !empty($category_name) ) {

                                        	if( term_exists( $category_name, 'sld_cat' ) ){

                                        		$term_id 	= term_exists( $category_name, 'sld_cat' );

                                        	}else{
									       	
									       		$term_id 	= wp_insert_term(
									           					$category_name,
									           					'sld_cat',
													           	array(
													             	'description' => $description
													           	)
									       					);

                                        	}
	                                        
	                                        $get_term_id = isset($term_id["term_id"]) ? $term_id["term_id"] : '';

	                                        if( isset($tab_color) && !empty($tab_color) ){
	                                        	if ( !empty($get_term_id) ) {
										       		update_term_meta($get_term_id, 'sld_cat_tab_color', $tab_color);
										       	}
									       	}

	                                        if( isset($tab_text_color) && !empty($tab_text_color) ){
	                                        	if ( !empty($get_term_id) ) {
										       		update_term_meta($get_term_id, 'sld_cat_tab_text_color', $tab_text_color);
										       	}
									       	}


											if( isset( $category_image_id ) && !empty( $category_image_id ) ){
												$attachment_id = '';
												require_once ABSPATH . 'wp-admin/includes/media.php';
												require_once ABSPATH . 'wp-admin/includes/file.php';
												require_once ABSPATH . 'wp-admin/includes/image.php';
												if (filter_var($category_image_id, FILTER_VALIDATE_URL)) {
											        // Get the file type
											        $file_type = wp_check_filetype(basename($category_image_id), null);

											        // Prepare an array of data for the attachment
											        $attachment = array(
											            'post_title'     => sanitize_file_name(basename($category_image_id)),
											            'post_mime_type' => $file_type['type'],
											        );

											        // Try to upload the image
											        $attachment_id = media_sideload_image($category_image_id, 0, null, 'id');
											    }
	                                        	if ( !empty($get_term_id) ) {
											     	update_term_meta( $get_term_id, 'category-image-id', $attachment_id, true );
											 	}
											}

	                                        //Relate terms, if exists
	                                        if( isset($new_post_id) && !empty($new_post_id) ){
	                                            
	                                            $post_ids 	= explode('-', $new_post_id );

	                                            foreach ($post_ids as $post_id ) {

	                                            	$term_list = get_the_terms($post_id, 'sld_cat');
	                                            	$types ='';
	                                            	$termIds 	= array();
	                                            	if( !empty($term_list) ){
														foreach($term_list as $term_single) {
														    array_push($termIds, $term_single->term_id);
														}

	                                            	}

													//$get_term_id = isset($term_id["term_id"]) ? $term_id["term_id"] : '';

													if ( !empty($get_term_id) && !in_array($get_term_id, $termIds)) {
													    array_push($termIds, $get_term_id);
													}

													if ( !empty($termIds)) {
		                                            	wp_set_post_terms($post_id, $termIds, 'sld_cat', true);
		                                            }

	                                            }

	                                        }


	                                        $count++;
	                                        $laps++;
									   	}

                                    }
                                    
                                    fclose($files);

                                    echo  '<div><span style="color: red; font-weight: bold;">'.esc_html('RESULT:', 'qc-opd').'</span> <strong>'.$count.'</strong> '.esc_html('Category successfully Submitted.', 'qc-opd').'</div>';
                                    //print_r($baseData);exit;
                                    //Inserting Data from our built array


                                }else{

                                   echo esc_html("Status: Please upload a valid CSV file.", "qc-opd");

                                }


                            }


                        }

                            ?>

                            <form name="uploadfile" id="uploadfile_form" method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
                                
                                <?php wp_nonce_field('qcsld_import_nonce', 'qc-opd'); ?>

                                <p>
                                    <?php esc_html_e('Select file to upload', 'qc-opd') ?>
                                    <input type="file" name="csv_upload_category" id="csv_upload_category" size="35" class="uploadfiles"/>
                                </p>
								<p style="color:red;"><?php esc_html_e('**CSV File & Characters must be saved with UTF-8 encoding**', 'qc-opd') ?></p>
                                <p>
                                    <input class="button-primary sld-add-as-new" type="submit" name="upload_csv_category" id="" value="<?php esc_html_e('Add New Category', 'qc-opd') ?>"/>
                                </p>
								

                            </form>
                            <br>
                            <br>

                        </div>

                        <div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;" class="qcld-sld-square-section-block">
                            <?php esc_html_e('Crafted By:', 'qc-opd') ?> <a href="<?php echo esc_url('http://www.quantumcloud.com', 'qc-opd'); ?>" target="_blank"> <?php esc_html_e('Web Design Company', 'qc-opd') ?> </a> - QuantumCloud
                        </div>

                        </div>
						<!-- //end sld-main-import-container -->
                    </div>
                    <!-- /post-body-content -->

                </div>
                <!-- /post-body-->

            </div>
            <!-- /poststuff -->


        </div>
        <!-- /wrap -->

        <?php
    }
}

new Qcopd_BulkImport;


function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    /*header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");*/

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }

   ob_start();

   $df = fopen("php://output", 'w');

   $titles = array('List ID', 'List Title', 'Item Title', 'Link', 'No Follow', 'Ugc', 'New Tab', 'Sub Title', 'FA Icon Class', 'Use Favicon', 'Attachment ID', 'Attachment Title', 'Direct/External Image Link', 'Upvotes', 'Item bg Color', 'Terms', 'List Holder Color', 'Item Background Color', 'Item Background Color (Hover)', 'Item Text Color', 'Item Text Color (Hover)', 'Item Sub Text Color', 'Item Sub Text Color (Hover)', 'Item Border Color', 'Item Border Color (Hover)','List Title Color','Filter Button Background Color','Filter Button Text Color', 'Ad Content', 'List Order', 'Post Status', 'Long Description','Tags','New (supported value  1, 0)','Featured (supported value 1, 0)','Generate Image from Website Link (supported value 1, 0)','Generate title and subtitle from Website Link (supported value 1, 0)');

   fputcsv($df, $titles);

   foreach ($array as $row) {
      fputcsv($df, $row);
   }

   fclose($df);

   return ob_get_clean();
}

add_action( 'admin_post_sldprint.csv', 'sld_export_print_csv' );

function sld_export_print_csv()
{
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) )
        return;


    $current_list_id = ( isset( $_GET['list_id'] ) && !empty($_GET['list_id']) ) ? sanitize_text_field($_GET['list_id']) : '';

    if( !empty( $current_list_id ) ){

	    $args = array(
			'post_type' 		=> 'sld',
			'posts_per_page' 	=> -1,
			'p'         		=> $current_list_id,
			'orderby' 			=> 'id',
			'order' 			=> 'ASC'
		);

    }else{

	    $args = array(
			'post_type' 		=> 'sld',
			'posts_per_page' 	=> -1,
			'orderby' 			=> 'id',
			'order' 			=> 'ASC',
		);

    }

    //Build the array first
    $export_query = new WP_Query( $args );

    $childArray = array();
    
	if ( $export_query->have_posts() ) 
	{

		$childArray = array();

		while ( $export_query->have_posts() ) 
		{
			$export_query->the_post();

			$post_title = get_the_title();

			$list_id = get_the_ID();

			$menu_order = get_post_field( 'menu_order', get_the_ID() );

			$post_status = get_post_status( get_the_ID() );

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );

			$config = get_post_meta( get_the_ID(), 'qcopd_list_conf' );

			$addBlock = get_post_meta( get_the_ID(), 'sld_add_block' );

			$add_content = "";

			if( count($addBlock) > 0 )
			{
				$add_content = $addBlock[0]['add_block_text'];
			}
			
			$config_exists = false;

			if( count($config) > 0 )
			{
				$config_exists = true;
			}

			$terms = array();

			$terms = get_the_terms( get_the_ID(), 'sld_cat' );

			$termArray = array();
			$attachedTerms = '';

			if( $terms && count($terms) > 0 )
			{

				foreach ( $terms as $term ) 
				{
			       // $termArray[] = $term->term_id;


			        $sld_export_csv_file_term_type = sld_get_option('sld_export_csv_file_term_type');

			        if( isset( $sld_export_csv_file_term_type ) && $sld_export_csv_file_term_type == 'name' ){
			        	$termArray[] = $term->name;
			        }else if( isset( $sld_export_csv_file_term_type ) && $sld_export_csv_file_term_type == 'slug' ){
			        	$termArray[] = $term->slug;
			        }else{
			        	$termArray[] = $term->term_id;
			        }
			    }
			                         
			    $attachedTerms = join( ", ", $termArray );
			}

			if( count($lists) > 0 )
			{

				$innerListNumber = 1;

				foreach( $lists as $list )
				{
					$innerArray = array();

					$title 				= isset($list['qcopd_item_title']) ? $list['qcopd_item_title'] : "";
					$subtitle 			= isset($list['qcopd_item_subtitle']) ? $list['qcopd_item_subtitle'] : "";
					$link 				= isset($list['qcopd_item_link']) ? $list['qcopd_item_link'] : "";
					$nofollow 			= ( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] != '0' ) ? 1 : 0;
					$ugc 				= ( isset($list['qcopd_item_ugc']) && $list['qcopd_item_ugc'] != '0' ) ? 1 : 0;
					$newtab 			= ( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] != '0' ) ? 1 : 0;

					$faIconClass 		= (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";
					$qcopd_description 	= (isset($list['qcopd_description']) && trim($list['qcopd_description']) != "") ? $list['qcopd_description'] : "";
					
					$qcopd_new 			= (isset($list['qcopd_new']) && trim($list['qcopd_new']) != "") ? $list['qcopd_new'] : "";
					
					$qcopd_featured 	= (isset($list['qcopd_featured']) && trim($list['qcopd_featured']) != "") ? $list['qcopd_featured'] : "";
					$qcopd_image_from_link = (isset($list['qcopd_image_from_link']) && trim($list['qcopd_image_from_link']) != "") ? $list['qcopd_image_from_link'] : "";
					$qcopd_generate_title = (isset($list['qcopd_generate_title']) && trim($list['qcopd_generate_title']) != "") ? $list['qcopd_generate_title'] : "";
					$qcopd_tags 		= (isset($list['qcopd_tags']) && trim($list['qcopd_tags']) != "") ? $list['qcopd_tags'] : "";

					$useFavicon 		= (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "0") ? 1 : 0;

					$setImageId 		= ( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) ?  wp_get_attachment_image_url( trim($list['qcopd_item_img']), 'full' ) : '';

					$externalImageLink 	= ( isset($list['qcopd_item_img_link'])  && $list['qcopd_item_img_link'] != "" ) ? trim($list['qcopd_item_img_link']) : '';

					$upvotes 			= ( isset($list['qcopd_upvote_count'])  && $list['qcopd_upvote_count'] != "" ) ? trim($list['qcopd_upvote_count']) : 0;
					$item_bg_color 		= ( isset($list['list_item_bg_color'])  && $list['list_item_bg_color'] != "" ) ? trim($list['list_item_bg_color']) : '';

					$image 				= wp_get_attachment_metadata( $setImageId );

					$imageTitle = isset( $image['file'] ) ? $image['file'] : '';

					$innerArray[0] = trim($list_id);
					$innerArray[1] = trim($post_title);
					$innerArray[2] = $title;
					$innerArray[3] = $link;
					$innerArray[4] = $nofollow;
					$innerArray[5] = $ugc;
					$innerArray[6] = $newtab;
					$innerArray[7] = $subtitle;
					$innerArray[8] = $faIconClass;
					$innerArray[9] = $useFavicon;
					$innerArray[10] = $setImageId;
					$innerArray[11] = $imageTitle;
					$innerArray[12] = $externalImageLink;
					$innerArray[13] = $upvotes;
					$innerArray[14] = $item_bg_color;
					$innerArray[15] = $attachedTerms;

					$innerArray[16] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_border_color'] : "";
					$innerArray[17] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_bg_color'] : "";
					$innerArray[18] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_bg_color_hov'] : "";
					$innerArray[19] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_txt_color'] : "";
					$innerArray[20] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_txt_color_hov'] : "";
					$innerArray[21] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_subtxt_color'] : "";
					$innerArray[22] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_subtxt_color_hov'] : "";
					$innerArray[23] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['item_bdr_color'] : "";
					$innerArray[24] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['item_bdr_color_hov'] : "";
					$innerArray[25] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_title_color'] : "";
					$innerArray[26] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['filter_background_color'] : "";
					$innerArray[27] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['filter_text_color'] : "";

					$innerArray[28] = ( $innerListNumber == 1 ) ? $add_content : "";

					$innerArray[29] = ( isset($menu_order) && $menu_order != '' ) ? $menu_order : 0;

					$final_post_status = ( isset($post_status) && $post_status != '' ) ? $post_status : 'publish';

					$innerArray[30] = ( $innerListNumber == 1 ) ? $final_post_status : '';
					$innerArray[31] = $qcopd_description;
					$innerArray[32] = $qcopd_tags;
					$innerArray[33] = $qcopd_new;
					$innerArray[34] = $qcopd_featured;
					$innerArray[35] = $qcopd_image_from_link;
					$innerArray[36] = $qcopd_generate_title;
					

					array_push($childArray, $innerArray);

					$innerListNumber++;
				}
			}

		}


		wp_reset_postdata();
	}

	/*echo '<pre>';
		print_r( $childArray );
	echo '</pre>';*/

	if( !empty( $current_list_id ) ){
		$current_post_title 	 = get_the_title($current_list_id);
        $uniq_post_name          = preg_replace( '%\s*[-_\s]+\s*%', ' ',  substr($current_post_title, 0, 50) );
        $uniq_post_name          = str_replace( ' ', '-',  $uniq_post_name );
        $uniq_post_name          = strtolower( $uniq_post_name );
        $uniq_post_name          = preg_replace('/[^a-zA-Z0-9_ -]/s', '',$uniq_post_name);
		download_send_headers("sld_list-" . $uniq_post_name ."-". date("Y-m-d") . ".csv");
    }else{
		download_send_headers("sld_lists_" . date("Y-m-d") . ".csv");
    }

	$result = array2csv($childArray);

	print $result;

}



function sldcategoryarray2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }

   ob_start();

   $df = fopen("php://output", 'w');

   $titles = array('Category Name', 'Description', 'Tab Color', 'Tab Text Color', 'Image url', 'List ID' );

   fputcsv($df, $titles);

   foreach ($array as $row) {
      fputcsv($df, $row);
   }

   fclose($df);

   return ob_get_clean();
}

add_action( 'admin_post_sldcategoryprint.csv', 'sld_category_export_print_csv' );

function sld_category_export_print_csv(){

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) )
        return;


	$args = array(
               'taxonomy' 	=> 'sld_cat',
               'orderby' 	=> 'menu_order',
               'hide_empty' => false,
               'order'   	=> 'ASC'
           );

   	$cats = get_categories($args);
    $childArray = array();

	if( !empty($cats)  ){
		$innerListNumber = 1;
		$childArray = array();
		foreach ( $cats as  $cat ) {

            $get_term_id 	= isset( $cat->term_id ) ? $cat->term_id : '';

	        $innerArray[0] 	= isset($cat->name) ? $cat->name : '';
	        $innerArray[1] 	= isset($cat->description) ? $cat->description : '';

	        $innerArray[2] 	= get_term_meta($get_term_id, 'sld_cat_tab_color', true);
	        $innerArray[3] 	= get_term_meta($get_term_id, 'sld_cat_tab_text_color', true);
	        $img_id 		= get_term_meta($get_term_id, 'category-image-id', true);
	        $image_url 		= wp_get_attachment_image_url( $img_id, 'full' );
	        $innerArray[4]  = isset($image_url) ? $image_url : '';

			if ( !empty($get_term_id ) ) {

				$args = array(
					'numberposts' => - 1,
					'post_type'   => 'sld',
					'orderby'     => 'menu_order',
					'order'       => 'ASC',
				);

				$taxArray = array(
					array(
						'taxonomy' => 'sld_cat',
						'field'    => 'term_id',
						'terms'    => $get_term_id,
					),
				);

				$args = array_merge( $args, array( 'tax_query' => $taxArray ) );

				$listItems = get_posts( $args );
				$item_count_disp_all = array();
				foreach ($listItems as $item){
					$item_count_disp_all[] = $item->ID;
					
				}

		        $innerArray[5] =  implode("-",$item_count_disp_all);

			}
	                                
	    	array_push($childArray, $innerArray);

	    	$innerListNumber++;
	    }
	                         
	}

	download_send_headers("sld_categories_" . date("Y-m-d") . ".csv");

	$result = sldcategoryarray2csv($childArray);

	print $result;

}


/* Generate title from Website Link filter */
add_filter('sld_auto_generate_title_from_website_link_filter', 'sld_auto_generate_title_from_website_link_filter_callback', 10, 2 );
function sld_auto_generate_title_from_website_link_filter_callback( $qcopd_item_link, $qcopd_item_title ){
    
	global $wpdb;
	$url = isset($qcopd_item_link) ? esc_url($qcopd_item_link) : '';

	if( !empty( $url ) ){
		
		$html_data = sld2_get_web_page($url);
		if( !empty( $html_data ) ){
			$html_dom = new DOMDocument();
			@$html_dom->loadHTML($html_data);
			$xpath = new DOMXPath($html_dom);
			
			if( isset($xpath->query('//title')->item(0)->textContent) && ($xpath->query('//title')->item(0)->textContent!='') ){
				$title = iconv("UTF-8", "ISO-8859-1", $xpath->query('//title')->item(0)->textContent);
				if( !$title ){
					$title = $xpath->query('//title')->item(0)->textContent;
				}

				if( !empty($title) && $title == '404 Not Found'){
					return $qcopd_item_title;
				}

				return $title;
			}
		}
		
	}

	return $qcopd_item_title;

}

/* Generate subtitle from Website Link filter */
add_filter('sld_auto_generate_subtitle_from_website_link_filter', 'sld_auto_generate_subtitle_from_website_link_filter_callback', 10, 2 );
function sld_auto_generate_subtitle_from_website_link_filter_callback( $qcopd_item_link, $qcopd_item_subtitle ){
    
    
	global $wpdb;
	$url = isset($qcopd_item_link) ? esc_url($qcopd_item_link) : '';

	if( !empty( $url ) ){
		
		$html_data = sld2_get_web_page($url);
		if( !empty( $html_data ) ){
			$html_dom = new DOMDocument();
			@$html_dom->loadHTML($html_data);
			$xpath = new DOMXPath($html_dom);
			
			if( isset($xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent) && ($xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent !='') ){
				$description = iconv("UTF-8", "ISO-8859-1", $xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent);
				if( !$description ){
					$description = $xpath->query('/html/head/meta[@name="description"]/@content')->item(0)->textContent;
				}
				return $description;
			}
		}
		
	}

	return $qcopd_item_subtitle;

}

/* Generate Image from Website Link filter */
add_filter('sld_auto_generate_image_from_website_link_filter', 'sld_auto_generate_image_from_website_link_filter_callback', 10, 3 );
function sld_auto_generate_image_from_website_link_filter_callback( $qcopd_item_link, $qcld_image_filename, $attachmentId ){
    
    
	$APIKey = sld_get_option('sld_pagespeed_api');
	
	$APIKey = ( isset($APIKey) && !empty($APIKey) ) ? $APIKey : "AIzaSyDgTUsxx59PCEGECgJztbhPT0Os5Vz1vXg";
	
	$image = sld2_get_web_page("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=".esc_url($qcopd_item_link)."&screenshot=true&key=".$APIKey);
	$image = json_decode($image, true);
	
	$image = isset($image['lighthouseResult']['audits']['final-screenshot']['details']['data']) ? $image['lighthouseResult']['audits']['final-screenshot']['details']['data'] : '';

	if( !empty($image) ){
		
		$upload_dir       = wp_upload_dir();
		$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		$image = str_replace(array('_', '-'), array('/', '+'), $image);
		$imgBase64 = str_replace('data:image/jpeg;base64,', '', $image);
		$imgBase64 = str_replace(' ', '+', $imgBase64);
		$decoded = base64_decode($imgBase64);
		//$filename         = 'sldwebsite.jpg';
		

        $uniq_name          = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $qcld_image_filename );
        $uniq_name          = str_replace( ' ', '-',  $uniq_name );
        $uniq_name          = strtolower( $uniq_name );
        $uniq_name          = preg_replace('/[^a-zA-Z0-9_ -]/s', '',$uniq_name);
        //$filename           = $uniq_name .'-'. uniqid() . '.' . $imagetype;
        $hashed_filename    = $uniq_name .'-'. uniqid() . '.' . '.jpg';


		//$hashed_filename  = md5( $filename . microtime() ) . '_' . $filename;
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
		$attachment_id = $attach_id;
		
		return $attachment_id;

	}else if( !empty( $attachmentId ) && is_numeric($attachmentId) && wp_get_attachment_url( $attachmentId ) ){

	  $attachment_id = $attachmentId;
	  return $attachment_id;

	}else if( !empty( $attachmentId ) ){

		$image_attachment_id = attachment_url_to_postid( $attachmentId );

		if( isset($image_attachment_id) && !empty($image_attachment_id) ){
			$attachment_id = $image_attachment_id;
			return $attachment_id;
		}else{

			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			if (filter_var($attachmentId, FILTER_VALIDATE_URL)) {
		        // Get the file type
		        $file_type = wp_check_filetype(basename($attachmentId), null);

		        // Prepare an array of data for the attachment
		        $attachment = array(
		            'post_title'     => sanitize_file_name(basename($attachmentId)),
		            'post_mime_type' => $file_type['type'],
		        );

		        // Try to upload the image
		        $attachment_id = media_sideload_image($attachmentId, 0, null, 'id');
		        return $attachment_id;
		    }

		}


	}

}