<?php 
global $wpdb;
$table    = $wpdb->prefix.'wpbot_subscription';

if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
    // do something
    $sql1 = "SELECT count(*) FROM $table where 1";
    $total_user_data = $wpdb->get_var( $sql1 );

}else{
    $total_user_data = 0;
}

$tableuser    = $wpdb->prefix.'wpbot_sessions';

if($wpdb->get_var("SHOW TABLES LIKE '$tableuser'") == $tableuser) {

    $session_exists = $wpdb->get_row( $wpdb->prepare( "select * from $tableuser where 1 and id = %d", 1 ) );

    if(!empty($session_exists)){
        $total_session = $session_exists->session;
    }else{
        $total_session = 0;
    }

}else{
    $total_session = 0;
}


global $woocommerce, $wp_scripts;
        $suffix = defined('qcopd_sld_SCRIPT_DEBUG') && qcopd_sld_SCRIPT_DEBUG ? '' : '.min';
            
            wp_register_style('qlcd-sld-admin-style', qcopd_sld_addon_url . 'css/admin-style.css', '', '', 'screen');
            wp_enqueue_style('qlcd-sld-admin-style');

            wp_enqueue_script('jquery');

            wp_register_style('qcld-sld-bootcampqc-css', qcopd_sld_addon_url . 'css/bootstrap.min.css', '', '', 'screen');
            wp_enqueue_style('qcld-sld-bootcampqc-css');



?>
<div class="wrap">
    <h1 class="wpbot_header_h1"><?php echo esc_html__('Simple Link Directory', 'qc-opd'); ?> </h1>
</div>
<div class="wp-chatbot-wrap">
    <div class="wpbot_dashboard_header container"><h1><?php echo esc_html__('Simple Link Directory', 'qc-opd'); ?></h1></div>

    <div class="wpbot_addons_section container">
        <div class="wpbot_single_addon_wrapper qc-display-flex qc-justify-center qc-flex-wrap kbx_pb_0">
            <h2 class="wpbot_single_addon_title"><a href="<?php echo esc_url('https://www.quantumcloud.com/products/simple-link-directory/'); ?>" target="_blank" ><?php echo esc_html__('Simple Link Directory Pro', 'qc-opd'); ?></a> <?php echo esc_html__(' Addons', 'qc-opd'); ?></h2>
         
            
            <div class="wpbot_single_addon kbx-center-addon">
                <div class="wpbot_single_content">
                    <div class="wpbot_addon_image">
                        <img src="<?php echo esc_url(qcopd_sld_addon_url.'images/multi-page-addon.png'); ?>" title="" />
                    </div>
                    <div class="wpbot_addon_content">
                        <div class="wpbot_addon_title"><?php echo esc_html__('MultiPage Advanced Addon', 'qc-opd'); ?></div>
                        <div class="wpbot_addon_details">
                            
                            <?php
                                if( is_plugin_active('multi-page-advanced-addon/multi-page-advanced-addon.php') ){
                                    echo '<span class="wp_addon_installed">Installed</span>';
                                }else{
                                    echo '<span class="wp_addon_notinstalled">Not Installed</span>';
                                }
                            ?>
                            <p><?php echo esc_html('Global Search, Pagination, Category view with Lists on home page.'); ?></p>
                            <a class="button button-secondary" href="<?php echo esc_url('https://www.quantumcloud.com/products/simple-link-directory-addons/'); ?>" target="_blank" ><?php echo esc_html__('Get It Now', 'qc-opd'); ?></a>
                        </div>            
                    </div>
                </div>
            </div>

            <div class="wpbot_single_addon kbx-center-addon">
                <div class="wpbot_single_content">
                    <div class="wpbot_addon_image">
                        <img src="<?php echo esc_url(qcopd_sld_addon_url.'images/link-checker.png'); ?>" title="" />
                    </div>
                    <div class="wpbot_addon_content">
                        <div class="wpbot_addon_title"><?php echo esc_html__('Directory Broken Link Checker', 'qc-opd'); ?></div>
                        <div class="wpbot_addon_details">
                            <?php
                                if( is_plugin_active('qc-broken-link-checker/qc-directory-broken-link-checker.php') ){
                                    echo '<span class="wp_addon_installed">Installed</span>';
                                }else{
                                    echo '<span class="wp_addon_notinstalled">Not Installed</span>';
                                }
                            ?>
                            <p><?php echo esc_html('Check Broken Links for SLD and SBD and other Post Types Links'); ?></p>
                            <a class="button button-secondary" href="<?php echo esc_url('https://www.quantumcloud.com/products/simple-link-directory-addons/'); ?>" target="_blank" ><?php echo esc_html__('Get It Now', 'qc-opd'); ?></a>
                        </div>            
                    </div>
                </div>
            </div>

            <div class="wpbot_single_addon kbx-center-addon">
                <div class="wpbot_single_content">
                    <div class="wpbot_addon_image">
                        <img src="<?php echo esc_url(qcopd_sld_addon_url.'images/rating-addon-logo.png'); ?>" title="" />
                    </div>
                    <div class="wpbot_addon_content">
                        <div class="wpbot_addon_title"><?php echo esc_html__('Review, Rating for SLD Pro', 'qc-opd'); ?></div>
                        <div class="wpbot_addon_details">
                            <?php
                                if( is_plugin_active('sld-rating-review-addon/sld-rating-review.php') ){
                                    echo '<span class="wp_addon_installed">Installed</span>';
                                }else{
                                    echo '<span class="wp_addon_notinstalled">Not Installed</span>';
                                }
                            ?>
                            <p><?php echo esc_html('Allow your site users to leave a review comment and rate the link listings.'); ?></p>
                            <a class="button button-secondary" href="<?php echo esc_url('https://www.quantumcloud.com/products/simple-link-directory-addons/'); ?>" target="_blank" ><?php echo esc_html__('Get It Now', 'qc-opd'); ?></a>
                        </div>            
                    </div>
                </div>
            </div>

            <div class="wpbot_single_addon kbx-center-addon">
                <div class="wpbot_single_content">
                    <div class="wpbot_addon_image">
                        <img src="<?php echo esc_url(qcopd_sld_addon_url.'images/link-exchange.png'); ?>" title="" />
                    </div>
                    <div class="wpbot_addon_content">
                        <div class="wpbot_addon_title"><?php echo esc_html__('Link Exchange AddOn for SLD Pro', 'qc-opd'); ?></div>
                        <div class="wpbot_addon_details">
                            <?php
                                if( is_plugin_active('link-exchange-addon/qcld-link-exchange-main.php') ){
                                    echo '<span class="wp_addon_installed">Installed</span>';
                                }else{
                                    echo '<span class="wp_addon_notinstalled">Not Installed</span>';
                                }
                            ?>
                            <p><?php echo esc_html('Allow your site users Exchange Links with Other Websites'); ?></p>
                            <a class="button button-secondary" href="<?php echo esc_url('https://www.quantumcloud.com/products/simple-link-directory-addons/'); ?>" target="_blank" ><?php echo esc_html__('Get It Now', 'qc-opd'); ?></a>
                        </div>            
                    </div>
                </div>
            </div>


        </div>


        <div class="wpbot_single_addon_wrapper">
            <h2 class="wpbot_single_addon_title"><?php echo esc_html__('Themes', 'qc-opd'); ?></h2>
            <div class="wpbot_single_addon kbx-center-addon qc_addon_page_full_addon">
                <div class="wpbot_single_content">
                    <div class="wpbot_addon_image qc_addon_page_full_img">
                        <img src="<?php echo esc_url(qcopd_sld_addon_url.'images/theme.jpg'); ?>" title="" />
                    </div>
                    <div class="wpbot_addon_content">
                        <div class="wpbot_addon_title">Simple Link Directory Theme</div>
                        <div class="wpbot_addon_details">
                            <span class="wp_addon_installed">Not Installed</span>
                            <p>Crafted carefully to make the best out of the popular Simple Link Directory plugin. One Click Install, Demo Data, Compatible with the Elementor and the Gutenberg Page Builder!</p>
                            <a class="button button-secondary" href="https://www.quantumcloud.com/products/themes/simple-link-directory/" target="_blank" >Get It Now</a>
                        </div>            
                    </div>

                </div>

            </div>

            
            <div style="clear:both"></div>
            
        </div>


    </div>
</div>