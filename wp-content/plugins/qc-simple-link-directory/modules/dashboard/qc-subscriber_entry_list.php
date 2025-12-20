<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
class User_entry_list extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Profile', 'qc-opd' ), //singular name of the listed records
			'plural'   => __( 'Profiles', 'qc-opd' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

        ) );
	}
	/**
	 * Delete a Subscriber Profile record.
	 *
	 * @param int $id Subscriber Profile
	 */
	public static function get_subscriber_profiles( $per_page = 5, $page_number = 1 ) {

	  global $wpdb;
		
		
		
	  $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1";
	  
	  if(isset($_GET['stat']) and $_GET['stat']!=''){
			switch(sanitize_text_field($_GET['stat'])){
				case 'approved':
					$sql .= " and approval = 1";
					break;
				case 'pending':
					$sql .=" and (approval = 0 or approval = 3)";
					break;
				case 'denied':
					$sql .=" and approval = 2";
					break;
                case 'paid':
                    $sql .= " and package_id!=0";
                    break;
                case 'free':
                    $sql .=" and package_id=0";
                    break;
			}
	  }
	  if(isset($_POST['s']) and $_POST['s']!=''){
		  $sql .= " and (item_title like '%".sanitize_text_field($_POST['s'])."%' or item_subtitle like '%".sanitize_text_field($_POST['s'])."%')";
	  }
	  
	  if ( ! empty( $_REQUEST['orderby'] ) ) {
		$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( sanitize_text_field($_REQUEST['order']) ) : ' ASC';
	  }else{
		   $sql .=' ORDER BY `time` DESC';
	  }

	  $sql .= " LIMIT $per_page";

	  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
	  
	 
	  $result = $wpdb->get_results( $sql, 'ARRAY_A' );

	  return $result;
	}
	
	/**
	 * Delete Subscriber profile.
	 *
	 * @return null
	*/
	
	public static function delete_subscriber_profile( $id ) {
	  global $wpdb;
	
		$sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
		$pdata = $wpdb->get_row($sql);
		
		if(@$pdata->approval==1){
			self::deny_subscriber_profile($id);
		}

		$wpdb->delete(
		"{$wpdb->prefix}sld_user_entry",
			array( 'id' => $id ),
			array( '%d' )
		);
	
 
	}
	
	/**
	 * Approve Subscriber profile.
	 *
	 * @return null
	*/
	 
	public static function approve_subscriber_profile($id){
		global $wpdb;
		
		$sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
		$identifier = time();
		$pdata = $wpdb->get_row($sql);
		$featured = 0;
		if($pdata->package_id > 0){
			$featured = 1;
		}
		
		if(sld_get_option('sld_paid_item_featured')!='on'){
			$featured = 0;
		}

		$qcopd_tags = isset($pdata->qcopd_tags) ? $pdata->qcopd_tags : '';
		
		if( $pdata->approval==0 || $pdata->approval==2){
			$prepare = array( //preparing Meta
				'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
				'qcopd_item_link' 			=> trim($pdata->item_link),
				'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
				'qcopd_item_img_link' 		=> trim($pdata->image_url),
				'qcopd_fa_icon' 			=> '',
				'qcopd_item_img' 			=> '',
				'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
				'qcopd_item_newtab' 		=> 1,
				'qcopd_use_favicon' 		=> 1,
				'qcopd_upvote_count' 		=> 0,
				'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
				'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured,
				'qcopd_tags'				=> $qcopd_tags

			);
			
			//echo '<pre>'.print_r($prepare).'</pre>';exit;
			$meta_data = serialize($prepare);
			add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

			$wpdb->update(
				$wpdb->prefix.'sld_user_entry',
				array(
					'custom'  => $identifier,
					'approval'=> 1
				),
				array( 'id' => $id),
				array(
					'%s',
					'%d',
				),
				array( '%d')
			);			
		}elseif($pdata->approval==3){
			
			$sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
			$pdata = $wpdb->get_row($sql);
			$identifier = time();
			if($pdata->custom!=''){
				self::deny_subscriber_profile($id);
			}
			
			$prepare = array( //preparing Meta
				'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
				'qcopd_item_link' 			=> trim($pdata->item_link),
				'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
				'qcopd_item_img_link' 		=> trim($pdata->image_url),
				'qcopd_fa_icon' 			=> '',
				'qcopd_item_img' 			=> '',
				'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
				'qcopd_item_newtab' 		=> 1,
				'qcopd_use_favicon' 		=> 1,
				'qcopd_upvote_count' 		=> 0,
				'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
				'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured,
				'qcopd_tags'				=> $qcopd_tags

			);
			
			//echo '<pre>'.print_r($prepare).'</pre>';exit;
			$meta_data = serialize($prepare);
			add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

			$wpdb->update(
				$wpdb->prefix.'sld_user_entry',
				array(
					'custom'  => $identifier,
					'approval'=> 1
				),
				array( 'id' => $id),
				array(
					'%s',
					'%d',
				),
				array( '%d')
			);	
		}

	}

	/**
	 * Deny Subscriber profile.
	 *
	 * @return null
	*/
	 
	public static function deny_subscriber_profile($id){
		global $wpdb;
		
		$sql 		= $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
		$identifier = time();
		$pdata 		= $wpdb->get_row($sql);
		
		if( $pdata->approval==1 || $pdata->approval==3 ){

            $searchQuery 	= $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = %d and `meta_key` = 'qcopd_list_item01'", $pdata->sld_list );
            $results 		= @$wpdb->get_results($searchQuery);
			
			foreach($results as $result){
				
				$unserialize = unserialize($result->meta_value);
				if($pdata->custom == $unserialize['qcopd_timelaps']){
					
					$meta_id = @$result->meta_id;

					@$wpdb->delete(
						"{$wpdb->prefix}postmeta",
						array( 'meta_id' => $meta_id ),
						array( '%d' )
					);

				}
				
				
			}
			
			$wpdb->update(
				$wpdb->prefix.'sld_user_entry',
				array(
					'custom'  => '',
					'approval'=> 2
				),
				array( 'id' => $id),
				array(
					'%s',
					'%d',
				),
				array( '%d')
			);

        }

	}


	
	
	public static function edit_subscriber_profile($id){
				if(!function_exists('wp_get_current_user')) {
					include(ABSPATH . "wp-includes/pluggable.php"); 
				}
				global $wpdb;
				$table             = $wpdb->prefix.'sld_user_entry';
				$current_user = wp_get_current_user();
			?>

			<div class="wrap">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
				<div id="post-body-content" >
					<h1>Edit Item</h1>
			<?php

				//form submit section code
				if(isset($_POST['uid']) and $_POST['uid']!=''){
					$uid = isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) :'';
					$item_title = isset($_POST['item_title']) ? sanitize_text_field($_POST['item_title']) :'';
					$item_link = isset($_POST['item_link']) ? sanitize_text_field($_POST['item_link']) :'';
					$item_subtitle = isset($_POST['item_subtitle']) ? sanitize_text_field($_POST['item_subtitle']) :'';
					$item_description = isset($_POST['item_description']) ? sanitize_text_field($_POST['item_description']) :'';
					$item_no_follow = isset($_POST['item_no_follow']) ? sanitize_text_field($_POST['item_no_follow']) : '';
					$imageurl = isset($_POST['sld_pf_image_url']) ? sanitize_text_field($_POST['sld_pf_image_url']) : '';
					
					$qc_sld_category = isset($_POST['qc_sld_category']) ? sanitize_text_field($_POST['qc_sld_category']) : '';
					$qc_sld_list = isset($_POST['qc_sld_list']) ? sanitize_text_field($_POST['qc_sld_list']) : '';
					
					$wpdb->update(
						$table,
						array(
							'item_title'  => $item_title,
							'item_link'   => $item_link,
							'item_subtitle' => $item_subtitle,
							'description' => $item_description,
							'category'   => $qc_sld_category,
							'sld_list'  => $qc_sld_list,
							'image_url'=> $imageurl,
							'nofollow'=> $item_no_follow,
							'approval'=> 3
						),
						array( 'id' => $uid),
						array(
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%d',
							'%s',
							'%s',
							'%d'
						),
						array( '%d')
					);
					if(isset($_POST['approve']) and $_POST['approve']=='1'){
						self::approve_subscriber_profile($uid);
					}

					echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Link has been edited successfully! <a href="edit.php?post_type=sld&page=qcsld_user_entry_list">Click to go back</a></div>';
				}
				
				
				
				$recid = sanitize_text_field($id);
				$s = 1;
				
				$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d and id=%s", $s, $recid ) );
				
				$taxonomy = 'sld_cat';
				$terms = get_terms($taxonomy); //
				
				foreach($rows as $row){


				//var_dump($row->category);
				//var_dump($row->sld_list);
				//wp_die();
				?>
			
			<div class="qcld-sld-square-section-block">
			<form method="post" action="">
				<table class="form-table">
									
					<tr>
						<th><label for="qc_sld_item_title"><?php _e( 'Item Title', 'qc-opd' ); ?><span class="sld_required">*</span></label>
						</th>

						<td>
							<input type="text" id="qc_sld_item_title" name="item_title" value="<?php echo $row->item_title; ?>" required />
							
						</td>
					</tr>

					<tr>
						<th><label for="qc_sld_item_link"><?php _e( 'Item Link', 'qc-opd' ); ?><span class="sld_required">*</span></label>
						</th>

						<td>
							<input type="text" id="qc_sld_item_link" name="item_link" value="<?php echo $row->item_link; ?>" required/>
							
						</td>
					</tr>

					<tr>
						<th><label for="qc_sld_item_subtitle"><?php _e( 'Item Subtitle', 'qc-opd' ); ?></label>
						</th>

						<td>
							<textarea name="item_subtitle" id="qc_sld_item_subtitle" rows="2" cols="40"><?php echo $row->item_subtitle; ?></textarea>
							
							
							
						</td>
					</tr>
					<tr>
						<th><label for="qc_sld_item_subtitle"><?php _e( 'Description', 'qc-opd' ); ?></label>
						</th>

						<td>
							<textarea name="item_description" id="qc_sld_item_description" rows="2" cols="40"><?php echo $row->description; ?></textarea>
							
							
							
						</td>
					</tr>
					<tr>
						<th><label for="qc_sld_jobsize"><?php _e( 'Item Image', 'qc-opd' ); ?></label>
						</th>

						<td>
							<input type="hidden" name="sld_pf_image_url" id="sld_pf_image_url" class="regular-text" value="<?php echo esc_attr($row->image_url); ?>">
							<input type="button" name="upload-btn" id="sld-upload-btn" class="button-secondary" value="Upload Image">
							
							<div style="clear:both"></div>
								<div id="sld_preview_img">
									<?php if($row->image_url!=''): ?>
										<span class="sld_remove_bg_image">X</span>
										<img  src="<?php echo $row->image_url ?>" alt="">
									<?php endif; ?>
								</div>
						</td>
					</tr>
					
					<tr>
						<th><label><?php echo __('Category', 'qc-opd') ?> <span class="sld_required">*</span></label></th>
						<td>
						<?php 
						$taxonomy = 'sld_cat';
						$terms = get_terms($taxonomy, array(
								    'hide_empty' => false,
								)); //
						if ( $terms && !is_wp_error( $terms ) ) :
						?>
							<select id="qc_sld_category" class="sld_text_width" name="qc_sld_category" >
								<?php foreach ( $terms as $term ) { ?>
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
						</td>
					</tr>
					
					<tr>
						<th><label><?php echo __('Select List', 'qc-opd') ?> <span class="sld_required">*</span></label></th>
						<td><select id="qc_sld_list" class="sld_text_width" name="qc_sld_list" required>
							<?php

								if(!empty($row->category) ){
									
									$sld = new WP_Query( array( 
										'post_type' => 'sld',
										'tax_query' => array(
											array (
												'taxonomy' 	=> 'sld_cat',
												'field' 	=> 'name',
												'terms' 	=> $row->category,
											)
										),
										'posts_per_page' 	=> -1,
										'order' 			=> 'ASC',
										'orderby' 			=> 'menu_order'
										) 
									);
									
								}else{
									$sld = new WP_Query( array( 
										'post_type' 		=> 'sld',				
										'posts_per_page' 	=> -1,
										'order' 			=> 'ASC',
										'orderby' 			=> 'menu_order'
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
							?>
						</select>
						</td>
					</tr>	
					
                    <tr>
                        <th><label for="qc_sld_item_subtitle"><?php _e( 'No Follow', 'qc-opd' ); ?></label>
                        </th>

                        <td>
                            <input type="checkbox" value="1" <?php echo ($row->nofollow==1?'checked="checked"':''); ?> name="item_no_follow" />
                        </td>
                    </tr>

					<tr>
						<th><label for="qc_sld_item_subtitle"><?php _e( 'Approve', 'qc-opd' ); ?> </label>
						</th>

						<td>
							<input type="checkbox" value="1" name="approve" <?php echo ($row->approval==1?'checked="checked"':''); ?> />
						</td>
					</tr>
					


				</table>
				<p class="submit">
					<input type="hidden" name="uid" value="<?php echo $recid; ?>" />
					<input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Update Item'); ?>" type="submit">
				</p>
			</form>
			</div>
		</div>
	</div>
</div>
			<?php
		}

	}

	
	/**
	 * Returns the data for table header.
	 *
	 * @return string
	 */
	public function sld_table_header(){
		global $wpdb;
		
		$getid = "select id from {$wpdb->prefix}sld_user_entry";
		$ids = $wpdb->get_results($getid);
		
		
		$total = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1" );
		
		$pending = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and (approval=0 or approval=3)" );
		$deny = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=2 " );
		$approved = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=1 " );
		
		$edited = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=3 " );

		$paiditem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id!=0 " );
		$freeitem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id=0 " );

		echo '<p>
			<a href="'.(sprintf( '?post_type=sld&page=%s', esc_attr( $_REQUEST['page'] ) )).'">All ('.($total==''||$total==0?0:$total).')</a> || 
			
			<a href="'.(sprintf( '?post_type=sld&page=%s&stat=pending', esc_attr( $_REQUEST['page'] ) )).'">'.__('Pending', 'qc-opd').' ('.($pending==''||$pending==0?0:$pending).') </a>|| 
			<a href="'.(sprintf( '?post_type=sld&page=%s&stat=approved', esc_attr( $_REQUEST['page'] ) )).'">'.__('Approved', 'qc-opd').' ('.($approved==''||$approved==0?0:$approved).')</a> ||
			<a href="'.(sprintf( '?post_type=sld&page=%s&stat=denied', esc_attr( $_REQUEST['page'] ) )).'">'.__('Denied', 'qc-opd').'  ('.($deny==''||$deny==0?0:$deny).')</a> ||
			<a href="'.(sprintf( '?post_type=sld&page=%s&stat=paid', esc_attr( $_REQUEST['page'] ) )).'">'.__('Paid', 'qc-opd').'  ('.($paiditem==''||$paiditem==0?0:$paiditem).')</a> ||
			<a href="'.(sprintf( '?post_type=sld&page=%s&stat=free', esc_attr( $_REQUEST['page'] ) )).'">'.__('Free', 'qc-opd').'  ('.($freeitem==''||$freeitem==0?0:$freeitem).')</a>
			
			
		</p>';
		
	}
	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
	  global $wpdb;
		
		$getid = "select id from {$wpdb->prefix}sld_user_entry";
		$ids = $wpdb->get_results($getid);

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}sld_user_entry where 1";
		
	  return $wpdb->get_var( $sql );
	}
	
	/**
	 * Returns the Status format.
	 *
	 * @return string
	 */
	public function getStatus($args){
		if($args==0){
			return '<span style="color:#f4b042;font-weight:bold;">'.__('Pending', 'qc-opd').'</span>';
		}elseif($args==1){
			return '<span style="color:green;font-weight:bold;">'.__('Approved', 'qc-opd').'</span>';
		}elseif($args==2){
			return '<span style="color:red;font-weight:bold;">'.__('Deny', 'qc-opd').'</span>';
		}else{
			return '<span style="color:#f4b042;font-weight:bold;">'.__('Edited', 'qc-opd').'</span>';
		}
	}	
	
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	 
	function column_item_title( $item ) {

		$title = '<strong>' . $item['item_title'] . '</strong>';
		$actions = array(
		
			'edit'      => sprintf('<a href="edit.php?post_type=sld&page=%s&action=%s&book=%s">Edit</a>',sanitize_text_field($_REQUEST['page']),'edit',$item['id']),
			
			'delete'    => sprintf('<a href="edit.php?post_type=sld&page=%s&action=%s&book=%s">Delete</a>',sanitize_text_field($_REQUEST['page']),'delete',$item['id']),

			'view'    => sprintf('<a href="edit.php?post_type=sld&page=%s&action=%s&book=%s">Approve</a>',sanitize_text_field($_REQUEST['page']),'approve',$item['id']),

			'trash'    => sprintf('<a href="edit.php?post_type=sld&page=%s&action=%s&book=%s">Deny</a>',sanitize_text_field($_REQUEST['page']),'deny',$item['id']),
			
		);

		return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	 
	}
	
	
	/**
	 * Method for Image column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_image_url( $item ) {
	  if($item['image_url']!=''){
		  $image = '<img width="80" src="'.$item['image_url'].'" alt="'.$item['item_title'].'" />';
	  }else{
		  $image ='<img src="'.SLD_QCOPD_IMG_URL.'/person-placeholder.png'.'" width="80"/>';
	  }
	  
	  return $image;
	}
	
	/**
	 * Method for sld_list column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_sld_list( $item ) {
		$profession = get_the_title( $item['sld_list'] );
		return ''.$profession.'';
	}
	
	function column_category( $item ) {
		$profession = $item['category'];
		return ''.$profession.'';
	}

	/**
	 * Method for Business Hour column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	
	/**
	 * Method for item_link column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_item_link( $item ) {
		
		return ''.$item['item_link'].'';
	}

	/**
	 * Method for Job Size column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	
	
	/**
	 * Method for item_subtitle column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_item_subtitle( $item ) {
		
		return ''.$item['item_subtitle'].'';
	}
	
	/**
	 * Method for Date column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_time( $item ) {
		
		return ''.$item['time'].'';
	}
	
	
	
	
	/**
	 * Method for User column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_user_id( $item ) {
		$user = get_userdata($item['user_id']);
		return ''.$user->user_login.'';
	}
	
	/**
	 * Method for Status column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_approval( $item ) {
		
		return $this->getStatus($item['approval']);
	}

	/**
	 * Method for Item Type column
	 * @return string
	 */
	function column_package_id( $item ) {

	    if($item['package_id']==0){
            return '<span style="color:#810049;font-weight:bold;">'.esc_html('Free').'</span>';
        }else if($item['package_id']==555){

        	if ( class_exists( 'Qcld_backlink_exchange_control' ) && ( get_option('qcld_link_exchange_enable') == 1) ){

            	apply_filters('qcld_backlink_exchange_link_status', $item['id'], $item['approval'] );
        	}else{

        		return '<span style="color:#810049;font-weight:bold;">'.esc_html('Link Exchange').'</span>';
        	}


        }else if($item['package_id']==7777){

        	$offline_payment = (sld_get_option('sld_lan_for_offline_payment')!=''?sld_get_option('sld_lan_for_offline_payment'):esc_html('Offline Payment', 'qc-opd'));

        	return '<span style="color:#810049;font-weight:bold;">'.esc_html($offline_payment).'</span>';
        	
        }else{
            return '<span style="color:#0B286C;font-weight:bold;">'.esc_html('Paid').'</span>';
        }

	}
	
	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
	  switch ( $column_name ) {
		case 'item_title':
			return $item[ $column_name ];
		case 'item_link':
		  return $item[ $column_name ];
		default:
		  //return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	  }
	}
	
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
	  return sprintf(
		'<input type="checkbox" name="bulk-action[]" value="%s" />', $item['id']
	  );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
	  $columns = array(
		'cb'      => '<input type="checkbox" />',
		
		'image_url'    => __( 'Image', 'qc-opd' ),
		'item_title'    => __( 'Item Title', 'qc-opd' ),
		'item_link'    => __( 'Link', 'qc-opd' ),
		'item_subtitle'    => __( 'Subtitle', 'qc-opd' ),
		'category'    => __( 'Category', 'qc-opd' ),
		'sld_list'    => __( 'List Title', 'qc-opd' ),

		'user_id' => __( 'User', 'qc-opd' ),
		'package_id' => __( 'Item Type', 'qc-opd' ),
		'approval' => __( 'Status', 'qc-opd' ),
		'time' => __( 'Date', 'qc-opd' )
      );

	  return $columns;
	}
	
	
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
	  $sortable_columns = array(
		
		'item_title' => array( 'item_title', true ),
		'time' => array( 'time', false ),
		'approval' => array( 'approval', false ),
		'user_id' => array( 'user_id', false ),
	  );

	  return $sortable_columns;
	}
	
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
	  $actions = array(
		'bulk-approve' => 'Approve',
		'bulk-deny' => 'Deny',
		'bulk-delete' => 'Delete'
      );

	  return $actions;
	}
	
	/**
	 * Handles item actions.
	 */
	public function prepare_item_actions(){
		
	  /** Process bulk action */
	  $this->process_bulk_action();
	  $this->process_bulk_approve();
	  $this->process_bulk_deny();
	  
	  //single action
	  $this->process_delete_action();
	  $this->process_approve_action();
	  $this->process_deny_action();
	  $this->process_edit_action();
	  $this->process_view_action();
	}
	
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

	  $this->_column_headers = $this->get_column_info();


	  $per_page     = $this->get_items_per_page( 'entry_per_page', 5 );
	  $current_page = $this->get_pagenum();
	  $total_items  = self::record_count();

	  $this->set_pagination_args( array(
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
      ) );


	  $this->items = self::get_subscriber_profiles( $per_page, $current_page );
	}

	/**
	 * Bulk Approve action
	 *
	 * @return null
	 */
	public function process_bulk_approve() {

		global $wpdb;
	  // If the delete bulk action is triggered
	  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-approve' )
		   || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-approve' )
	  ) {
			
		$ids = esc_sql( $_POST['bulk-action'] );
		
		// loop over the array of record IDs and Approve them
		foreach ( $ids as $id ) {
			self::approve_subscriber_profile( $id );
		}
		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Bulk link approve Successfully!</div>';

		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}

	/**
	 * Bulk Deny action
	 *
	 * @return null
	 */
	public function process_bulk_deny() {

		global $wpdb;
	  // If the delete bulk action is triggered
	  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-deny' )
		   || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-deny' )
	  ) {
			
		$ids = esc_sql( $_POST['bulk-action'] );
		
		// loop over the array of record IDs and deny them
		foreach ( $ids as $id ) {
			self::deny_subscriber_profile( $id );
		}
		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Bulk link deny Successfully!</div>';

		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}

	/**
	 * Bulk Delete action
	 *
	 * @return null
	 */
	public function process_bulk_action() {

	global $wpdb;
	  // If the delete bulk action is triggered
	  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		   || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
	  ) {
			
		$delete_ids = esc_sql( $_POST['bulk-action'] );
		
		
		// loop over the array of record IDs and delete them
		foreach ( $delete_ids as $id ) {			
			self::delete_subscriber_profile( $id );
		}
		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Bulk link has been delete Successfully!</div>';
		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}

	/**
	 * Delete action
	 *
	 * @return null
	 */
	public function process_delete_action() {

	global $wpdb;
	  if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' )) {
			
		$id = esc_sql( $_GET['book'] );
		self::delete_subscriber_profile( $id );
		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Link has been deleted Successfully!</div>';
	  }
	}

	/**
	 * Approve action
	 *
	 * @return null
	 */
	public function process_approve_action() {

		global $wpdb;

	  if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'approve' )) {
		
		$id = esc_sql( $_GET['book'] );
		
		self::approve_subscriber_profile( $id );

		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Link has been approved Successfully!</div>';
		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}

	/**
	 * Deny action
	 *
	 * @return null
	 */
	public function process_deny_action() {

	global $wpdb;

	  if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'deny' )) {
		
		$id = esc_sql( $_GET['book'] );
		self::deny_subscriber_profile( $id );

		echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 18px;margin-top: 10px;">Link has been denied successfully!</div>';
		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}

	/**
	 * Edit action
	 *
	 * @return null
	 */
	public function process_edit_action() {

	global $wpdb;

	  if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' )) {
		
		$id = esc_sql( $_GET['book'] );
		self::edit_subscriber_profile( $id );

		//wp_redirect( esc_url( add_query_arg() ) );
		//exit;
	  }
	}
	
	
}