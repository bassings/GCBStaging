<?php
// Load template for embed link page url
function sld_qcopd_load_embed_link_template($template)
{
    if (is_page('embed-link')) {
        return dirname(__FILE__) . '/qcopd-embed-link.php';
    }
    return $template;
}

add_filter('template_include', 'sld_qcopd_load_embed_link_template', 99);


// Create embed page when plugin install or activate

//register_activation_hook(__FILE__, 'sld_qcopd_create_embed_page');
add_action('init', 'sld_qcopd_create_embed_page');
function sld_qcopd_create_embed_page()
{

    $query = new WP_Query(
        array(
            'post_type'              => 'page',
            'title'                  => 'Embed Link',
            'post_status'            => 'all',
            'posts_per_page'         => 1,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
        )
    );

    $page_got_by_title = !empty( $query->post ) ? $query->post : null;

    if ( $page_got_by_title == NULL) {
        //post status and options
        $post = array(
            'comment_status'  => 'closed',
            'ping_status'     => 'closed',
            'post_author'     => get_current_user_id(),
            'post_date'       => date('Y-m-d H:i:s'),
            'post_status'     => 'publish',
            'post_title'      => 'Embed Link',
            'post_type'       => 'page',
        );
        //insert page and save the id
        $embedPost = wp_insert_post($post, false);
        //save the id in the database
        update_option('hclpage', $embedPost);
    }
}


    

add_action('qcsld_after_add_btn', 'sld_qcld_custom_embedder');
function sld_qcld_custom_embedder($shortcodeAtts)
{
	$embed_link_button = sld_get_option('sld_enable_embed_list');
	if ($embed_link_button == 'on') {
    global $post;
	
  	$site_title = get_bloginfo('title');
  	$site_link = get_bloginfo('url');

  	if( sld_get_option( 'sld_embed_credit_title' ) != "" ){
  		$site_title = sld_get_option( 'sld_embed_credit_title' );
  	}

  	if( sld_get_option( 'sld_embed_credit_link' ) != "" ){
  		$site_link = sld_get_option( 'sld_embed_credit_link' );
  	}

    $pagename = $post->post_name;

    $item_count = isset($shortcodeAtts['item_count']) ? $shortcodeAtts['item_count']: '';
    $hide_list_title = isset($shortcodeAtts['hide_list_title']) ? $shortcodeAtts['hide_list_title']: '';
    $display_username = isset($shortcodeAtts['display_username']) ? $shortcodeAtts['display_username']: '';
    $item_details_page = isset($shortcodeAtts['item_details_page']) ? $shortcodeAtts['item_details_page']: '';
    $filterorderby = isset($shortcodeAtts['filterorderby']) ? $shortcodeAtts['filterorderby']: '';
    $filterorder = isset($shortcodeAtts['filterorder']) ? $shortcodeAtts['filterorder']: '';
    $paginate_items = isset($shortcodeAtts['paginate_items']) ? $shortcodeAtts['paginate_items']: '';
    $actual_pagination = isset($shortcodeAtts['actual_pagination']) ? $shortcodeAtts['actual_pagination']: '';
    $per_page = isset($shortcodeAtts['per_page']) ? $shortcodeAtts['per_page']: '';
    $favorite = isset($shortcodeAtts['favorite']) ? $shortcodeAtts['favorite']: '';
    $enable_left_filter = isset($shortcodeAtts['enable_left_filter']) ? $shortcodeAtts['enable_left_filter']: '';
    $main_click = isset($shortcodeAtts['main_click']) ? $shortcodeAtts['main_click']: '';
    $video_click = isset($shortcodeAtts['video_click']) ? $shortcodeAtts['video_click']: '';
    $enable_tag_filter = isset($shortcodeAtts['enable_tag_filter']) ? $shortcodeAtts['enable_tag_filter']: '';
    $list_title_font_size = isset($shortcodeAtts['list_title_font_size']) ? $shortcodeAtts['list_title_font_size']: '';
    $item_orderby = isset($shortcodeAtts['item_orderby']) ? $shortcodeAtts['item_orderby']: '';
    $list_title_line_height = isset($shortcodeAtts['list_title_line_height']) ? $shortcodeAtts['list_title_line_height']: '';
    $title_font_size = isset($shortcodeAtts['title_font_size']) ? $shortcodeAtts['title_font_size']: '';
    $subtitle_font_size = isset($shortcodeAtts['subtitle_font_size']) ? $shortcodeAtts['subtitle_font_size']: '';
    $title_line_height = isset($shortcodeAtts['title_line_height']) ? $shortcodeAtts['title_line_height']: '';
    $subtitle_line_height = isset($shortcodeAtts['subtitle_line_height']) ? $shortcodeAtts['subtitle_line_height']: '';
    $filter_area = isset($shortcodeAtts['filter_area']) ? $shortcodeAtts['filter_area']: '';
    $topspacing = isset($shortcodeAtts['topspacing']) ? $shortcodeAtts['topspacing']: '';


    if ($pagename != 'embed-link') {

        $query = new WP_Query(
            array(
                'post_type'              => 'page',
                'title'                  => 'Embed Link',
                'post_status'            => 'all',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby'                => 'post_date ID',
                'order'                  => 'ASC',
            )
        );

        $page_got_by_title = ! empty( $query->post ) ? $query->post->guid : null;

        ?>

		    <!-- Generate Embed Code -->

        <a class="button-link js-open-modal cls-embed-btn" href="#" data-modal-id="popup"
           data-url="<?php echo rtrim($page_got_by_title,'/'); ?>"
           data-orderby="<?php echo $shortcodeAtts['orderby']; ?>"
           data-order="<?php echo $shortcodeAtts['order']; ?>"
           data-mode="<?php echo $shortcodeAtts['mode']; ?>"
           data-list-id="<?php echo $shortcodeAtts['list_id']; ?>"
           data-column="<?php echo $shortcodeAtts['column']; ?>"
           data-style="<?php echo $shortcodeAtts['style']; ?>"
           data-search="<?php echo $shortcodeAtts['search']; ?>"
           data-category="<?php echo $shortcodeAtts['category']; ?>"
           data-upvote="<?php echo $shortcodeAtts['upvote']; ?>"
           data-tooltipp="<?php echo $shortcodeAtts['tooltip']; ?>"
           data-credittitle="<?php echo $site_title; ?>"
           
           data-item_count="<?php echo $item_count; ?>"
           data-hide_list_title="<?php echo $hide_list_title; ?>"
           data-display_username="<?php echo $display_username; ?>"
           data-item_details_page="<?php echo $item_details_page; ?>"
           data-filterorderby="<?php echo $filterorderby; ?>"
           data-filterorder="<?php echo $filterorder; ?>"
           data-paginate_items="<?php echo $paginate_items; ?>"
           data-actual_pagination="<?php echo $actual_pagination; ?>"
           data-per_page="<?php echo $per_page; ?>"
           data-favorite="<?php echo $favorite; ?>"
           data-enable_left_filter="<?php echo $enable_left_filter; ?>"
           data-main_click="<?php echo $main_click; ?>"
           data-video_click="<?php echo $video_click; ?>"
           data-enable_tag_filter="<?php echo $enable_tag_filter; ?>"
           data-list_title_font_size="<?php echo $list_title_font_size; ?>"
           data-item_orderby="<?php echo $item_orderby; ?>"
           data-list_title_line_height="<?php echo $list_title_line_height; ?>"
           data-title_font_size="<?php echo $title_font_size; ?>"
           data-subtitle_font_size="<?php echo $subtitle_font_size; ?>"
           data-title_line_height="<?php echo $title_line_height; ?>"
           data-subtitle_line_height="<?php echo $subtitle_line_height; ?>"
           data-filter_area="<?php echo $filter_area; ?>"
           data-topspacing="<?php echo $topspacing; ?>"

           data-creditlink="<?php echo $site_link; ?>" title="Embed this List on your website!">
		     <?php 
				if(sld_get_option('sld_lan_share_list')!=''){
					echo sld_get_option('sld_lan_share_list');
				}else{
					echo __('Share List', 'qc-opd') ;
				}
			 ?>
			<i class="fa fa-share-alt"></i>
		   </a>
            <?php
                add_action( 'wp_footer', 'sld_sld_share_modal' );
            ?>
    <?php }}
}

function sld_sld_share_modal() {
    ?>
    <div id="popup" class="modal-box">
            <header>
                <a href="#" class="js-modal-close close">×</a>
                <h3><?php echo __('Generate Embed Code For This List', 'qc-opd') ?></h3>
            </header>
            <div class="modal-body">
                <div class="iframe-css">
                    <div class="iframe-main">
                        <div class="ifram-row">
                            <div class="ifram-sm">
                                <span><?php echo __("Width: (in '%' or 'px')", 'qc-opd') ?></span>
                                <input id="igwidth" name="igwidth" type="text" value="100">
                            </div>
                            <div class="ifram-sm" style="width: 70px;">
                                <span>&nbsp;</span>
                                <select name="igsizetype" class="iframe-main-select">
                                    <option value="%">%</option>
                                    <option value="px"><?php echo __("px", 'qc-opd') ?></option>
                                </select>
                            </div>
                            <div class="ifram-sm">
                                <span><?php echo __("Height: (in 'px')", 'qc-opd') ?></span>
                                <input id="igheight" name="igheight" type="text" value="400">
                            </div>
                            <div class="ifram-sm">
                                <span>&nbsp;</span>
                                <a class="btn icon icon-code" id="generate-igcode"
                                   onclick=""><?php echo __('Generate & Copy', 'qc-opd') ?></a>
                                </select>
                            </div>
                        </div>

                        <div class="ifram-row">
                            <div class="ifram-lg">
                                <span class="qcld-span-label"><?php echo __('Generated Code', 'qc-opd') ?></span>
                                <br>
                                <textarea id="igcode_textarea" class="igcode_textarea" name="igcode" style="width:100%; height:120px;"
                                          readonly="readonly"></textarea>
                                <p class="guideline"><?php echo __("Hit 'Generate & Copy' button to generate embed code. It will be copied
                                    to your Clipboard. You can now paste this embed code inside your website's HTML where
                                    you want to show the List.", 'qc-opd') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
