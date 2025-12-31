<?php



//Doing ajax action stuff

function bookmark_ajax_insert_action()
{


    //Get posted items
   
    $post_id = trim($_POST['post_id']);
    $item_code = trim($_POST['item_code']);
    
    $userid = trim($_POST['userid']);

    //Check wpdb directly, for all matching meta items
    global $wpdb;

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ) );


        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = maybe_unserialize($value->meta_value);
			
            //If meta title and link matches with unserialized data
            if ( isset($unserialized['qcopd_timelaps']) && trim($unserialized['qcopd_timelaps']) == trim($item_code)) {

                $metaId = $meta_id;
                //Defaults for current iteration
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if ( !empty($unserialized) && is_array($unserialized) && array_key_exists('qcopd_is_bookmarked', $unserialized)) {
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_is_bookmarked') {
							
							if($value!=0 and $value!=''){
								$uv = explode(',',$value);
								$new_array[$key] = implode(',',array_merge($uv,array($userid)));
							}else{
								$new_array[$key] = $userid;
							}
                            
							
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                if (!$flag) {
                    $new_array['qcopd_is_bookmarked'] = $userid;
                }
				
                $updated_value = maybe_serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

            }

        }
    
	$user_meta_key = 'sld_bookmark_user_meta';
	
	$current_meta_value = get_user_meta($userid, $user_meta_key);
	
	if(empty($current_meta_value)){
		
		$user_meta_value = array($post_id=>array($item_code));
		add_user_meta($userid, $user_meta_key, $user_meta_value);
		
	}else{
		
		if(!empty($current_meta_value[0])){
			$newar = array();
			foreach($current_meta_value[0] as $key=>$val){
				$newar[$key] = $val;
			}
			$newar[$post_id][] = $item_code;
			update_user_meta($userid, $user_meta_key, $newar);
			
			
		}else{
			$user_meta_value = array($post_id=>array($item_code));
			update_user_meta($userid, $user_meta_key, $user_meta_value);
		}
		
		
	}
	$current_meta_value = get_user_meta($userid, $user_meta_key);
	print_r($current_meta_value);

    die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_bookmark_insert_action', 'bookmark_ajax_insert_action'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_bookmark_insert_action', 'bookmark_ajax_insert_action'); // ajax for not logged in users

function bookmark_ajax_remove_action()
{


    // Get posted items
   
    $post_id = trim($_POST['post_id']);
    $item_code = trim($_POST['item_code']);
    
    $userid = trim($_POST['userid']);

    //Check wpdb directly, for all matching meta items
    global $wpdb;

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ) );


        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = maybe_unserialize($value->meta_value);
			
            //If meta title and link matches with unserialized data
            if ( isset($unserialized['qcopd_timelaps']) && trim($unserialized['qcopd_timelaps']) == trim($item_code)) {

                $metaId = $meta_id;
                //Defaults for current iteration
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if ( !empty($unserialized) && is_array($unserialized) && array_key_exists('qcopd_is_bookmarked', $unserialized)) {
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_is_bookmarked') {
							
							if($value!=0 and $value!=''){
								$uv = explode(',',$value);
								
								$pos = array_search($userid, $uv);
								unset($uv[$pos]);
								
								$new_array[$key] = implode(',',$uv);
							}else{
								$new_array[$key] = '';
							}
                            
							
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                $updated_value = maybe_serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

            }

        }
    
	
	$user_meta_key = 'sld_bookmark_user_meta';
	
	
	$current_meta_value = get_user_meta($userid, $user_meta_key);
	
	if(empty($current_meta_value)){

		
	}else{
		
		if(!empty($current_meta_value[0])){
			$newar = array();
			foreach($current_meta_value[0] as $key=>$val){
				if($key==$post_id){
					$pos = array_search($item_code, $val);
					unset($val[$pos]);
				}
				$newar[$key] = $val;
			}
			update_user_meta($userid, $user_meta_key, $newar);

		}
		
		
	}
	$current_meta_value = get_user_meta($userid, $user_meta_key);
	print_r($current_meta_value);

    die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_bookmark_remove_action', 'bookmark_ajax_remove_action'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_bookmark_remove_action', 'bookmark_ajax_remove_action'); // ajax for not logged in users
