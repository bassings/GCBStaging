<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wp;
$current_url 	=  home_url( $wp->request );
$id 			= isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';

$ctable 		= $wpdb->prefix.'sld_claim_purchase';
$cptable 		= $wpdb->prefix.'sld_claim_configuration';
$dashboard_url 	= qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
$current_user 	= wp_get_current_user();
$msg 			= '';
//Claim listing add handle goes here
if(isset($_POST['editsldclaimitem'])){
	
	$wpdb->update(
        $ctable,
        array(
			'listid'		=>	$_POST['claim_list'],
			'item'			=>	$_POST['claim_item'],
        ),
        array( 'id' => $id ),
        array(
            '%s',
            '%s',
        ),
        array( '%d' )
    );

	wp_reset_query();

	$msg = esc_html('Your Item has been Updated sucessfully.');
	
}

$claim = $wpdb->get_row( $wpdb->prepare( "select * from $ctable where 1 and `user_id`=%d and `id`=%d", $current_user->ID, $id ) );

?>
<h2><?php echo (sld_get_option('dashboard_lan_text_edit_claim_listing')!=''?sld_get_option('dashboard_lan_text_edit_claim_listing'):__('Edit Claim Listing', 'qc-opd')); ?></h2>

<?php if( isset( $msg ) && !empty( $msg ) ): ?>
<p style="color:#fff;background:green;padding:10px;text-align:center"><?php echo $msg ?></p>
<?php endif; ?>

<form action="<?php echo $current_url.'/?action=claim_edit&id='.$id; ?>" method="POST">
	<ul class="sld_form-style-1 sld_width">

		<li>
			<label> <?php echo (sld_get_option('dashboard_lan_text_add_claim_list_name')!=''?sld_get_option('dashboard_lan_text_add_claim_list_name'):__('Select List Name', 'qc-opd')); ?></label>
			<select style="" id="sld_claim_list" name="claim_list" required>
				<option value=""><?php _e('None', 'qc-opd') ?></option>
				<?php 
				$list_args_total = array(
					'post_type' => 'sld',
					'posts_per_page' => -1,
				);
				$list_query = new WP_Query( $list_args_total );
				while ( $list_query->have_posts() )
				{
					$list_query->the_post();
					echo '<option value="'.get_the_ID().'" '.selected( get_the_ID(), $claim->listid ).' >'.get_the_title().'</option>';
				}
				wp_reset_query();
				?>
			</select>
		</li>
		<li>
			<label> <?php echo (sld_get_option('dashboard_lan_text_add_claim_list_item_name')!=''?sld_get_option('dashboard_lan_text_add_claim_list_item_name'):__('Select Item Name', 'qc-opd')); ?></label>
			<select style="" id="sld_list_item" name="claim_item" required>
				<option value=""><?php _e('None', 'qc-opd') ?></option>
				<?php 

				$listId = $claim->listid;
				$lists = get_post_meta( $listId, 'qcopd_list_item01' );
				
				foreach( $lists as $list ) :
					$qcopd_item_title 	= isset( $list['qcopd_item_title'] ) ? preg_replace("/[^A-Za-z0-9 ]/", '', $list['qcopd_item_title'] ) : '';
					$claim_item 		= isset( $claim->item ) ? preg_replace("/[^A-Za-z0-9 ]/", '', $claim->item ) : '';
					echo '<option value="'.$list['qcopd_item_title'].'" '.selected( $qcopd_item_title, $claim_item ).' >'.$list['qcopd_item_title'].'</option>';
				endforeach;

				?>
			</select>
		</li>

        <li>
			<input type="submit" name="editsldclaimitem" class="sld_submit_style" value="<?php echo (sld_get_option('dashboard_lan_text_add_claim_list_submit')!=''?sld_get_option('dashboard_lan_text_add_claim_list_submit'):__('Submit', 'qc-opd')); ?>" />
		</li>
	</ul>
</form>
