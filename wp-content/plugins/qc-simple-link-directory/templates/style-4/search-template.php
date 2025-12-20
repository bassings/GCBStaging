<div class="sld-top-area <?php echo $borderClass; ?>">

	<?php if( $searchSettings == 'on' ) : ?>
        <div class="sld-half">
            <form id="live-search" action="" class="styled" method="post">
				<?php 
					if(sld_get_option('sld_lan_live_search')!=''){
						$srcplaceholder = sld_get_option('sld_lan_live_search');
					}else{
						$srcplaceholder = __('Live Search Items', 'qc-opd');
					}
				?>
                <input type="text" class="text-input sld-search sld_search_filter" placeholder="<?php echo $srcplaceholder; ?>"/>
            </form>
        </div>
	<?php endif; ?>



    <div class="sld-half sld-add">
<?php if(sld_get_option('sld_enable_claim_listing')=='on' and sld_get_option('sld_show_claim_listing_button')=='on'): 
		$claim_page = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_claim_url');
		if($claim_page==''){
			$claim_page = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url').'?action=claim';
		}
		if(is_user_logged_in()){
			$claim_page = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url').'?action=claim';
		}
	?>

		<a href="<?php echo $claim_page; ?>" class="sld-add-btn">
			<?php
   				if( sld_get_option('sld_lan_claim_list') ){
   					echo sld_get_option('sld_lan_claim_list');
   				}else{
   					echo __('Claim Listing', 'qc-opd');
   				}
   			?>
		</a>

	<?php endif; ?>
<?php if(sld_get_option('sld_enable_add_new_item')=='on' || sld_get_option('sld_add_new_behave')=='on'): ?>
		<?php if(sld_get_option('sld_enable_add_new_item')=='on'): ?>
		<?php if(is_user_logged_in()): ?>
        <a style="" href="<?php echo qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url'); ?>" class="sld-add-btn">

			<?php else: ?>
            	<?php if( !empty(sld_get_option( 'sld_alt_add_item_link' )) ){ ?>
					<a href="<?php echo sld_get_option( 'sld_alt_add_item_link' ); ?>" class="sld-add-btn">
				<?php }else{ ?>
	            	<a href="<?php echo qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_login_url'); ?>" class="sld-add-btn">

				<?php } endif; ?>

				<?php elseif(sld_get_option('sld_add_new_behave')=='on'): ?>
                <a href="<?php echo $itemAddLink; ?>" class="sld-add-btn">
				<?php endif; ?>
					<?php 
						if(sld_get_option('sld_lan_add_link')!=''){
							echo sld_get_option('sld_lan_add_link');
						}else{
							_e( 'Add Link', 'qc-opd' ); 
						}
					
					?>
                    <i class="fa fa-plus"></i>
                </a>

		<?php endif; ?>

				<?php

				//Hook - Before Search Template
				do_action( 'qcsld_after_add_btn', $shortcodeAtts);

				?>

    </div>



</div>
