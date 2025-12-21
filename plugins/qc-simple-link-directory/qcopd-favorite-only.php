<?php
/**
 * Created by QuantunCloud.
 * Date: 9/14/2017
 * Time: 3:16 PM
 */

defined('ABSPATH') or die("No direct script access!");

add_shortcode('qcopd-directory-favorite', 'SLD_QCOPD_DIRectory_favorite_fnc');
function SLD_QCOPD_DIRectory_favorite_fnc($atts = array()){
		wp_enqueue_script( 'qcopd-custom1-script'); //category tab
wp_enqueue_script( 'qcopd-custom-script');
wp_enqueue_script( 'qcopd-grid-packery');
wp_enqueue_script( 'jq-slick.min-js');
wp_enqueue_style( 'jq-slick-theme-css');
wp_enqueue_style( 'jq-slick.css-css');
wp_enqueue_script( 'qcopd-sldcustom-common-script');
wp_enqueue_script( 'qcopd-sldcustom-2co-script');
wp_enqueue_script( 'qcopd-custom-script-sticky');
wp_enqueue_script('qcopd-embed-form-script');
wp_enqueue_script( 'qcopd-tooltipster');
wp_enqueue_script( 'qcopd-magpopup-js');
wp_enqueue_style( 'sldcustom_login-css');
wp_enqueue_style( 'qcopd-magpopup-css');
wp_enqueue_style( 'sld-tab-css');
wp_enqueue_style('qcopd-embed-form-css');
wp_enqueue_style( 'qcopd-sldcustom-common-css');
wp_enqueue_style( 'qcopd-custom-registration-css');
wp_enqueue_style( 'qcopd-custom-rwd-css');
wp_enqueue_style( 'qcopd-custom-css');
wp_enqueue_style( 'qcfontawesome-css');
wp_enqueue_style('qcopd-embed-form-css');
wp_enqueue_script('qcopd-embed-form-script');

	ob_start();

		echo do_shortcode('[qcopd-directory mode="all" style="custom" column="4" upvote="on" search="true" item_count="on" orderby="date" filterorderby="date" order="ASC" filterorder="ASC" paginate_items="false" favorite="enable" tooltip="false" list_title_font_size="" item_orderby="" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing="" onlyfavorite="true"]');

	$content = ob_get_clean();
	return $content;

}