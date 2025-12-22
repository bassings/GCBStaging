<?php

/**
 * This function register new custom post type - sld
 *
 * @param void
 *
 * @return null
 */
function qc_qcopd_register_cpt_sld() {
	//Register New Post Type
	$qc_list_labels = array(
		'name'               => _x( 'Manage List Items', 'qc-opd' ),
		'singular_name'      => _x( 'Manage List Item', 'qc-opd' ),
		'add_new'            => __( 'New List', 'qc-opd' ),
		'add_new_item'       => __( 'Add New List Item','qc-opd' ),
		'edit_item'          => __( 'Edit List Item','qc-opd' ),
		'new_item'           => __( 'New List Item','qc-opd' ),
		'all_items'          => __( 'Manage List Items','qc-opd' ),
		'view_item'          => __( 'View List Item','qc-opd' ),
		'search_items'       => __( 'Search List Item','qc-opd' ),
		'not_found'          => __( 'No List Item found','qc-opd' ),
		'not_found_in_trash' => __( 'No List Item found in the Trash','qc-opd' ), 
		'parent_item_colon'  => '',
		'menu_name'          => __('Simple Link Directory','qc-opd')
	);

	$qc_list_args = array(
		'labels'        		=> $qc_list_labels,
		'description'   		=> __('This post type holds all posts for your directory items.','qc-opd'),
		'public'        		=> true,
		'publicly_queryable' 	=> false,
		'menu_position' 		=> 25,
		'exclude_from_search' 	=> true,
		'show_in_nav_menus' 	=> false,
		'supports'      		=> array( 'title' ),
		'has_archive'   		=> true,
		'menu_icon' 			=> SLD_QCOPD_IMG_URL . '/menu_icon.png',
	);

	register_post_type( 'sld', $qc_list_args );	

	//Register New Taxonomy for Our New Post Type
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'List Categories', 'List Categories', 'qc-opd' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name', 'qc-opd' ),
		'search_items'      => __( 'Search List Categories', 'qc-opd' ),
		'all_items'         => __( 'All List Categories', 'qc-opd' ),
		'parent_item'       => __( 'Parent List Categories', 'qc-opd' ),
		'parent_item_colon' => __( 'Parent List Category:', 'qc-opd' ),
		'edit_item'         => __( 'Edit List Category', 'qc-opd' ),
		'update_item'       => __( 'Update List Category', 'qc-opd' ),
		'add_new_item'      => __( 'Add New List Category', 'qc-opd' ),
		'new_item_name'     => __( 'New List Category Name', 'qc-opd' ),
		'menu_name'         => __( 'List Categories', 'qc-opd' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'public'			=> false,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'sld_cat' ),
	);

	register_taxonomy( 'sld_cat', array( 'sld' ), $args );

}

/**
 * 
 * This hook register new custom post type and taxonomy for sld
 * Post Type: sld
 * Taxonomy: sld_cat
 *
 */
add_action( 'init', 'qc_qcopd_register_cpt_sld' );


/**
 * 
 * Require CMB Metabox if it not exists already by some other
 * extensions.
 *
 */

if ( ! class_exists( 'CMB_Meta_Box' ) )
{
	require_once SLD_QCOPD_INC_DIR . '/cmb/custom-meta-boxes.php';
}

/**
 * Metabox for our custom post type - sld
 * This function enables all costm made metabox for the directory.
 *
 * @param array of meta fields
 *
 * @return array of meta fields
 */

function cmb_SLD_QCOPD_DIR_fields( array $meta_boxes ) {

	//Config Fields
	$le_fields = array(
		array( 'id' => 'list_border_color',  'name' => 'List Holder Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_bg_color',  'name' => 'Item Background Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_bg_color_hov',  'name' => 'Item Background Color', 'type' => 'colorpicker', 'desc' => '(On Mouseover)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_bg_featured_color',  'name' => 'Item Featured Background Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_txt_color',  'name' => 'Item Text Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_txt_color_hov',  'name' => 'Item Text Color', 'type' => 'colorpicker', 'desc' => '(On Mouseover)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_subtxt_color',  'name' => 'Item Sub Text Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_subtxt_color_hov',  'name' => 'Item Sub Text Color', 'type' => 'colorpicker', 'desc' => '(On Mouseover)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'item_bdr_color',  'name' => 'Item Border Color', 'type' => 'colorpicker', 'desc' => '(Normal State)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'item_bdr_color_hov',  'name' => 'Item Border Color', 'type' => 'colorpicker', 'desc' => '(On Mouseover)', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'list_title_color',  'name' => 'List Title Color', 'type' => 'colorpicker', 'desc' => '', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'filter_background_color',  'name' => 'Filter Button Background Color', 'type' => 'colorpicker', 'desc' => '', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'filter_text_color',  'name' => 'Filter Button Text Color', 'type' => 'colorpicker', 'desc' => '', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'title_link',  'name' => 'Link List Title to a Page', 'type' => 'text', 'desc' => 'ex: http://example.com', 'cols' => 4, 'default' => '' ),
		array( 'id' => 'title_link_new_tab',  'name' => 'Open Link in New Tab', 'type' => 'checkbox', 'desc' => 'ex: http://example.com', 'cols' => 4, 'default' => 0 ),
	);

	$meta_boxes[] = array(
		'title' => 'List Configurations',
		'pages' => 'sld',
		'fields' => array(
			array(
				'id' => 'qcopd_list_conf',
				'name' => '',
				'type' => 'group',
				'repeatable' => false,
				'sortable' => false,
				'fields' => $le_fields,
				'desc' => ''
			)
		)
	);


	//Repeatable Fields
	$qcopd_item_fields = array(
		//array( 'id' => 'qcopd_timelaps',  'name' => 'Item ID', 'type' => 'text', 'cols' => 12, 'default' => '', 'show_option_none' => true ),
		array( 'id' => 'qcopd_item_link',  'name' => 'Item Link (Ex: http://example.com or https://example.com)', 'type' => 'text', 'cols' => 6 ),
		array( 'id' => 'qcopd_generate_title',  'name' => 'Generate info', 'type' => 'checkbox', 'cols' => 1 ),
		array( 'id' => 'qcopd_item_title',  'name' => 'Item Title', 'type' => 'text', 'cols' => 5 ),
		
		array( 'id' => 'qcopd_item_subtitle',  'name' => 'Item Subtitle', 'type' => 'text', 'cols' => 6 ),
		array( 'id' => 'qcopd_item_img_link',  'name' => 'Favicon / External Image / YouTube Thumb / Direct Image Link', 'type' => 'text', 'cols' => 6 ),
		array( 'id' => 'qcopd_new',  'name' => 'Mark Item as New', 'type' => 'checkbox', 'cols' => 3, 'default' => 0, 'desc' => '' ),
		array( 'id' => 'qcopd_featured',  'name' => 'Mark Item as Featured', 'type' => 'checkbox', 'cols' => 3, 'default' => 0, 'desc' => '' ),	
		array( 'id' => 'qcopd_image_from_link',  'name' => __('Generate Image from Website Link','qc-opd'), 'type' => 'checkbox', 'cols' => 3, 'default' => 0, 'desc' => '' ),
		
		array( 'id' => 'qcopd_fa_icon', 'name' => 'FontAwesome Icon', 'type' => 'text', 'cols' => 4, 'classes' => 'fa-popup-field', 'desc' => 'If you upload an image, then that image will be used instead of this FontAwesome icon.' ),
		array( 'id' => 'qcopd_item_img', 'name' => 'List Image', 'type' => 'image', 'repeatable' => false, 'show_size' => false, 'cols' => 3, 'desc' => ''  ),
		array( 'id' => 'qcopd_multipage_item_img', 'name' => 'Multipage / Popup Image', 'type' => 'image', 'repeatable' => false, 'show_size' => false, 'cols' => 3, 'desc' => ''  ),
		array( 'id' => 'qcopd_item_ugc',  'name' => 'Ugc', 'type' => 'checkbox', 'cols' => 2, 'default' => 0 ),
		array( 'id' => 'qcopd_item_nofollow',  'name' => 'No Follow', 'type' => 'checkbox', 'cols' => 2, 'default' => 0 ),
		array( 'id' => 'qcopd_item_newtab',  'name' => 'Open Link in a New Tab', 'type' => 'checkbox', 'cols' => 3, 'default' => 0 ),
		
		array( 'id' => 'qcopd_use_favicon',  'name' => 'Pick Image from the Direct Link', 'type' => 'checkbox', 'cols' => 4, 'default' => 0, 'desc' => '' ),
		
		
		array( 'id' => 'qcopd_upvote_count',  'name' => 'Upvote Count', 'type' => 'text', 'cols' => 6, 'default' => '0' ),
		array( 'id' => 'list_item_bg_color',  'name' => 'Item Background Color', 'type' => 'colorpicker', 'cols' => 6, 'default' => '0' ),
		array( 'id' => 'qcopd_entry_time',  'name' => 'Entry Time', 'type' => 'text', 'cols' => 4, 'default' => ''.date("Y-m-d H:i:s").'' ),	
		array( 'id' => 'qcopd_is_bookmarked',  'name' => 'Is Bookmarked', 'type' => 'text', 'cols' => 4, 'default' => '0' ),
		array( 'id' => 'qcopd_click',  'name' => 'Click', 'type' => 'text', 'cols' => 4, 'default' => '0' ),
		array(
			'id'   => 'qcopd_description',
			'name' => 'Long Description',
			'type' => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 3
			)

		),
		
		array( 'id' => 'qcopd_timelaps',  'name' => 'Item ID', 'type' => 'text', 'cols' => 2, 'default' => '' ),	
		array( 'id' => 'qcopd_tags',  'name' => __('Tags','qc-opd'), 'type' => 'text', 'cols' => 3, 'default' => '', 'desc' => '' ),
		array( 'id' => 'qcopd_unpublished',  'name' => __('Unpublish this Item','qc-opd'), 'type' => 'checkbox', 'cols' => 2, 'default' => 0, 'desc' => '' ),	
		array( 'id' => 'qcopd_other_list',  'name' => __('Copy this item to other Lists','qc-opd'), 'type' => 'text', 'cols' => 3, 'default' => '', 'desc' => '' ),

	);

	//code for extra video field
	$extra_video_field = array();
	if(sld_get_option('sld_enable_extra_video_field') == 'on'){
		$extra_video_field = array(
			array( 'id' => 'sld_extra_video_field', 'name' => 'Youtube/Vimeo Videos', 'type' => 'text', 'cols' => 12 ),
		);
	}
	$qcopd_item_fields = array_merge( $qcopd_item_fields, $extra_video_field );

	
	

/*	$exclude_field = array();

	$exclude_field = array(
		array( 'id' => 'sld_exclude_field', 'name' => 'Item ID', 'type' => 'text', 'cols' => 2, 'default' =>'' ),
	);	
	$qcopd_item_fields = array_merge($qcopd_item_fields,$exclude_field);*/
	
	
	$meta_boxes[] = array(
		'title' 	=> 'List Elements',
		'pages' 	=> 'sld',
		'fields' 	=> array(
			array(
				'id' 			=> 'qcopd_list_item01',
				'name' 			=> 'Create List Elements',
				'type' 			=> 'group',
				'repeatable' 	=> true,
				'sortable' 		=> true,
				'fields' 		=> $qcopd_item_fields,
				'desc' 			=> 'If you upload an image that will be shown instead of Icon or Favicon. Using Favicon can slow down your site with some servers. Switch to icon or image if that is the case. <br><br><i style="color:indianred;font-size:15px !important">If you are unable to save a long List, please increase the value of max_input_vars to 15000 on your server.</i>'
			)
		)
	);

	//Config Fields
	$le_fields2 = array(
		array( 
			'id' 		=> 'add_block_text',  
			'name' 		=> 'Raw Codes or Texts', 
			'type' 		=> 'wysiwyg', 
			'desc' 		=> 'This content will be displayed after list elements. Please paste your codes in TEXT mode only. You can use general text contents in both mode.', 
			'rows' 		=> 4, 
			'default' 	=> '' 
		),
	);

	$meta_boxes[] = array(
		'title' 	=> 'Text/Ad Block',
		'pages' 	=> 'sld',
		'fields' 	=> array(
			array(
				'id' 			=> 'sld_add_block',
				'name' 			=> '',
				'type' 			=> 'group',
				'repeatable' 	=> false,
				'sortable' 		=> false,
				'fields' 		=> $le_fields2,
				'desc' 			=> ''
			)
		)
	);

	return $meta_boxes;

}

add_filter( 'cmb_meta_boxes', 'cmb_SLD_QCOPD_DIR_fields' );

/**
 * Custom Columns for Directory Listing in the backend
 *
 * @param default column fields
 *
 * @return all column fields after modification
 */
function sld_qcopd_list_columns_head($defaults) {

    $new_columns['cb'] 					= '<input type="checkbox" />';
    $new_columns['title'] 				= __('Title');

    $new_columns['qcopd_item_count'] 	= 'Number of Elements';
    $new_columns['qcopd_item_category'] = 'Categories';
    $new_columns['shortcode_col'] 		= 'Shortcode';

    $new_columns['date'] 				= __('Date');

    return $new_columns;
}
//end of function sld_qcopd_list_columns_head
 
/**
 * Custom Column values for Backend SLD post Listing
 *
 * @param column_name, post_ID
 *
 * @return null
 */
function sld_qcopd_list_columns_content($column_name, $post_ID) {
    

    //Item Elements Count
    if ($column_name == 'qcopd_item_count') {
        echo count(get_post_meta( $post_ID, 'qcopd_list_item01' ));
    }

    //Item Categories
    if ($column_name == 'qcopd_item_category') {

        $terms = get_the_terms( $post_ID, 'sld_cat' );

        /* If terms were found. */
        if ( !empty( $terms ) ) {

            $out = array();

            /* Loop through each term, linking to the 'edit posts' page for the specific term. */
            foreach ( $terms as $term ) {
                $out[] = sprintf( '<a href="%s">%s</a>',
                    esc_url( add_query_arg( array( 'post_type' => 'sld', 'sld_cat' => $term->slug ), 'edit.php' ) ),
                    esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'sld_cat', 'display' ) )
                );
            }

            /* Join the terms, separating them with a comma. */
            echo join( ', ', $out );
        }

    }

    //Generated Shortcode
    if ($column_name == 'shortcode_col') {
        echo '[qcopd-directory mode="one" style="simple" list_id="'.$post_ID.'"]';
        	$the_slug = $post_ID;
			$sld_args = array(
			  'p'        => $the_slug,
			  'post_type'   => 'sld',
			  'post_status' => 'publish',
			  'numberposts' => 1
			);
			$sld_posts = get_posts($sld_args);

			if(!empty($sld_posts[0]->post_name)){
				echo "<b> OR </b>";
				echo "<br>";
				echo ' [qcopd-directory mode="one" style="simple" list_id="'.esc_attr($sld_posts[0]->post_name).'"]';

			}


    }

}
//end of function sld_qcopd_list_columns_content

add_filter('manage_sld_posts_columns', 'sld_qcopd_list_columns_head');
add_action('manage_sld_posts_custom_column', 'sld_qcopd_list_columns_content', 10, 2);


//Filter by Custom Taxonomy
add_action( 'restrict_manage_posts', 'qcopd_restrict_manage_posts' );

/**
 * This function enable custom filtering by custom taxonomy type 
 * in the backend post listing
 *
 * @param void
 *
 * @return null
 */
function qcopd_restrict_manage_posts() {

    // only display these taxonomy filters on desired custom post_type listings
    global $typenow;

    if ($typenow == 'sld') {

        // create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
        $filters = array('sld_cat');

        if(!empty($filters)){

	        foreach ($filters as $tax_slug) {
	            // retrieve the taxonomy object
	            $tax_obj = get_taxonomy($tax_slug);
	            $tax_name = $tax_obj->labels->name;
	            // retrieve array of term objects per taxonomy
	            $terms = get_terms($tax_slug);

	            // output html for taxonomy dropdown filter
	            echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
	            echo "<option value=''>Show All $tax_name</option>";
	            foreach ($terms as $term) {
	                // output each select option line, check against the last $_GET to show the current option selected
	                echo '<option value="'. $term->slug.'" '.( isset($_GET[$tax_slug]) && ( $_GET[$tax_slug] == $term->slug ) ) ? "selected=selected":"".' >' . $term->name .' (' . $term->count .')</option>';
	            }
	            echo "</select>";
	        }

	    }


    }
    
}


add_action('save_post','qcopd_save_post_callback');
function qcopd_save_post_callback($post_id){
	global $post; 

	global $wpdb;
	if (is_object( $post ) && isset($post->post_type) && $post->post_type != 'sld'){
	    return;
	}
	
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'qcopd_list_item01'", $post_id ) );

	$sld_direct_link_img_upload_for_list_item = sld_get_option('sld_direct_link_img_upload_for_list_item');

	if(!empty($results)){

		$laps = 1;
		foreach ($results as $key => $value) {

	        $item = $value;

	        $meta_id = $value->meta_id;

	        $unserialized = maybe_unserialize($value->meta_value);

	        $new_array = array();

	        $qcopd_item_title = '';
	        $qcopd_item_img_link = '';

	    	foreach($unserialized as $key => $value) {

	    		if( $key == 'qcopd_description' ){
	    			$arr = array(
					    'a' 		=> array(
					        'target'=> array(),
					        'href' 	=> array(),
					        'class' => array(),
					        'id' 	=> array(),
					        'title' => array()
					    ),
					    'ul' 		=> array(),
					    'ol' 		=> array(),
					    'li' 		=> array(),
					    'p' 		=> array(
					    	'style' => array(),
					    ),
					    'b' 		=> array(),
					    'i' 		=> array(),
					    'ins' 		=> array(),
					    'del' 		=> array(),
					    'blockquote'=> array(),
					    'br' 		=> array(),
					    'em' 		=> array(),
					    'strong' 	=> array(),
					    'img' 		=> array(
							'title' => array(),
							'src'	=> array(),
							'alt'	=> array(),
							'class'	=> array(),
							'id'	=> array()
						)
					);
	    			$value = wp_kses( $value, $arr );
	    		}else{
	    			$value = sanitize_text_field( $value );
	    		}

	    		if($key == 'qcopd_item_title'){
	            	$qcopd_item_title = $value;
	            }

	    		if($key == 'qcopd_item_img_link' && $value !== ''){
	            	$qcopd_item_img_link = $value;
	            }

	            if ($key == 'qcopd_timelaps' && $value == '' ) {
					$new_array[$key] = time() + $laps++;
	            }else if($key == 'qcopd_item_img' && $value == '' && !empty($qcopd_item_img_link) && ( isset( $sld_direct_link_img_upload_for_list_item ) && ( $sld_direct_link_img_upload_for_list_item == 'on' ) ) ){

	            	$qcopd_item_img_link = $qcopd_item_img_link;

	            	$attachment_id = apply_filters('sld_item_img_link_insert_from_url', $qcopd_item_img_link, $qcopd_item_title );

	            	$new_array[$key] = $attachment_id;

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
	            array('meta_id' => $meta_id)
	        );

			// Prevent infinite loop
	        remove_action('save_post', 'qcopd_save_post_callback', 99);
	        // Update the post into the database
	        // Add hook again
	        add_action('save_post', 'qcopd_save_post_callback', 99);

	  	}

	}

}


add_filter('sld_item_img_link_insert_from_url', 'sld_item_img_link_insert_from_url_callback', 10, 2);
function sld_item_img_link_insert_from_url_callback($qcopd_item_img_link, $qcopd_item_title){
    
	if(isset($qcopd_item_img_link) && !empty( $qcopd_item_img_link ) ){


		$array              = explode('/', getimagesize($qcopd_item_img_link)['mime']);
	    $imagetype          = end($array);
	    
	    $qcld_article_text 	= ( isset($qcopd_item_title)  && !empty($qcopd_item_title)  ) ? trim($qcopd_item_title) : '';

	    $uniq_name          = preg_replace( '%\s*[-_\s]+\s*%', ' ',  substr($qcld_article_text, 0, 50) );
	    $uniq_name          = str_replace( ' ', '-',  $uniq_name );
	    $uniq_name          = strtolower( $uniq_name );
	    $uniq_name          = preg_replace('/[^a-zA-Z0-9_ -]/s', '',$uniq_name);
	    $filename           = $uniq_name .'-'. uniqid() . '.' . $imagetype;

	    $uploaddir          = wp_upload_dir();
	    $target_file_name   = $uploaddir['path'] . '/' . $filename;

	    $contents           = file_get_contents( $qcopd_item_img_link );

	    if(!empty($contents)){

		    $savefile           = fopen($target_file_name, 'w');
		    fwrite($savefile, $contents);
		    fclose($savefile);

		    /* add the image title */
		    $image_title        = ucwords( $uniq_name );

		    unset($qcopd_item_img_link);

		    /* insert the attachment */
		    $wp_filetype = wp_check_filetype(basename($target_file_name), null);
		    $attachment  = array(
		        'guid'              => $uploaddir['url'] . '/' . basename($target_file_name),
		        'post_mime_type'    => $wp_filetype['type'],
		        'post_title'        => $image_title,
		        'post_status'       => 'inherit'
		    );
		    $post_id     = isset($_REQUEST['post_id']) ? absint(sanitize_text_field( $_REQUEST['post_id'])): '';
		    $attachment_id   = wp_insert_attachment($attachment, $target_file_name, $post_id);

			return $attachment_id;
	    	
	    }


	}
}