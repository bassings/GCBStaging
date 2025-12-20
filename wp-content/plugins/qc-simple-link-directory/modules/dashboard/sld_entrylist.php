<?php 

if(isset($_GET['did']) and $_GET['did']!=''){
	$did = isset($_GET['did']) ? sanitize_text_field($_GET['did']) : '';
    $this->delete_subscriber_profile($did);
    echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'. __('Your Item has been Deleted sucessfully.', 'qc-opd') .'<br/></div>';

}
$s       = 1;
$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d and user_id=%d", $s, $current_user->ID ) );

?>
<h2><?php echo (sld_get_option('dashboard_lan_text_link_list_page')!=''?sld_get_option('dashboard_lan_text_link_list_page'):__('Link List Page', 'qc-opd')) ?></h2>
<div class="qc_sld_table_area">
  <div class="qc_sld_table">
	
	<div class="qc_sld_row sld_header">
	  
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php echo (sld_get_option('dashboard_lan_text_link_list_page_image')!=''?sld_get_option('dashboard_lan_text_link_list_page_image'):__('Image', 'qc-opd')) ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php echo (sld_get_option('dashboard_lan_text_link_list_page_link_title')!=''?sld_get_option('dashboard_lan_text_link_list_page_link_title'):__('Link Title', 'qc-opd')) ?>
	  </div>

	  <div class="qc_sld_cell qc_sld_table_head">
		<?php echo (sld_get_option('dashboard_lan_text_link_list_page_link_subtitle')!=''?sld_get_option('dashboard_lan_text_link_list_page_link_subtitle'):__('Link Subtitle', 'qc-opd')) ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php echo (sld_get_option('dashboard_lan_text_link_list_page_category')!=''?sld_get_option('dashboard_lan_text_link_list_page_category'):__('Category', 'qc-opd')) ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php echo (sld_get_option('dashboard_lan_text_link_list_page_list')!=''?sld_get_option('dashboard_lan_text_link_list_page_list'):__('List', 'qc-opd')) ?>
	  </div>

        <div class="qc_sld_cell qc_sld_table_head">
			<?php echo (sld_get_option('dashboard_lan_text_link_list_page_package')!=''?sld_get_option('dashboard_lan_text_link_list_page_package'):__('Package', 'qc-opd')) ?>
        </div>

	  <div class="qc_sld_cell qc_sld_table_head">
			<?php echo (sld_get_option('dashboard_lan_text_link_list_page_status')!=''?sld_get_option('dashboard_lan_text_link_list_page_status'):__('Status', 'qc-opd')) ?>
	  </div>
	  
	  <?php
	  	$hide_action_row = 0;
	  	if(
	  		(sld_get_option('sld_prevent_user_edit_link') == 'on') &&
	  		(sld_get_option('sld_prevent_user_delete_link') == 'on')
	  	){
	  		$hide_action_row = 1;
	  	}
	  	if( $hide_action_row == 0 ){
	  ?>
	  <div class="qc_sld_cell qc_sld_table_head">
			<?php echo (sld_get_option('dashboard_lan_text_link_list_page_action')!=''?sld_get_option('dashboard_lan_text_link_list_page_action'):__('Action', 'qc-opd')) ?>
	  </div>
	<?php } ?>
	</div>
<?php
$c=0;
foreach($rows as $row):
$c++;
?>

	<div class="qc_sld_row">
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_image')!=''?sld_get_option('dashboard_lan_text_link_list_page_image'):__('Image', 'qc-opd')) ?></div>
		<a href="<?php echo $row->item_link; ?>" target="_blank" title="<?php echo $row->item_link; ?>"><?php 
			echo $this->getImage($row->image_url); 
		?></a>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_link_title')!=''?sld_get_option('dashboard_lan_text_link_list_page_link_title'):__('Link Title', 'qc-opd')) ?></div>
		<?php echo $row->item_title; ?>
	  </div>
	 
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_link_subtitle')!=''?sld_get_option('dashboard_lan_text_link_list_page_link_subtitle'):__('Link Subtitle', 'qc-opd')) ?></div>
		<?php echo ( $row->item_subtitle ); ?>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_category')!=''?sld_get_option('dashboard_lan_text_link_list_page_category'):__('Category', 'qc-opd')) ?></div>
		<?php echo ($row->category) ?>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_list')!=''?sld_get_option('dashboard_lan_text_link_list_page_list'):__('List', 'qc-opd')) ?></div>
		<?php echo get_the_title( $row->sld_list ); ?>
	  </div>

        <div class="qc_sld_cell">
            <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_package')!=''?sld_get_option('dashboard_lan_text_link_list_page_package'):__('Package', 'qc-opd')) ?></div>
			<?php
                if($row->package_id=='0'){
                	$sld_lan_free = sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd');
                    echo __( $sld_lan_free, 'qc-opd');
                }else if($row->package_id=='555'){
                	$link_exchange = ( get_option('qcld_lan_link_exchange') !='' ? get_option('qcld_lan_link_exchange') : esc_html('Link Exchange', 'qc-opd') );
                    echo __( $link_exchange, 'qc-opd');
                }else if($row->package_id=='7777'){


        		$offline_payment = (sld_get_option('sld_lan_for_offline_payment')!=''?sld_get_option('sld_lan_for_offline_payment'):esc_html('Offline Payment', 'qc-opd'));
                    echo __($offline_payment, 'qc-opd');
                }else{
	                $package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $package_table WHERE 1 and id=%d", $row->package_id ) );
	                echo $package->title;
                }
            ?>
        </div>

	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_status')!=''?sld_get_option('dashboard_lan_text_link_list_page_status'):__('Status', 'qc-opd')) ?></div>
		<?php echo $this->getStatus($row->approval) ?>
	  </div>

	  <?php
	  	if( $hide_action_row == 0 ){
	  ?>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_action')!=''?sld_get_option('dashboard_lan_text_link_list_page_action'):__('Action', 'qc-opd')) ?></div>

	  <?php if( sld_get_option('sld_prevent_user_edit_link') != 'on' ){ ?>
		<a href="<?php echo esc_url( add_query_arg( array('action'=>'entryedit','id'=>$row->id), $url ) ); ?>"><button class="entry_list_edit"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_edit')!=''?sld_get_option('dashboard_lan_text_link_list_page_edit'):__('Edit', 'qc-opd')) ?></button></a>
	  <?php } ?>

		<?php if( sld_get_option('sld_prevent_user_delete_link') != 'on' ){ ?>
			<a title="delete" class="delete" onclick="return confirm('Are you sure to delete this Record?')" href="<?php echo esc_url( add_query_arg( array('action'=>'entrylist','did'=>$row->id), $url ) ); ?>"><button class="entry_list_delete"><?php echo (sld_get_option('dashboard_lan_text_link_list_page_delete')!=''?sld_get_option('dashboard_lan_text_link_list_page_delete'):__('Delete', 'qc-opd')) ?></button></a>
		<?php } ?>
	  </div>
	  <?php } ?>
	  
	</div>
  <?php 
  endforeach;
  ?>

  </div>

</div>