<?php

//Registering Sub Menu for Ordering
add_action( 'admin_menu', 'qcopd_register_sld_sorting_menu' );

function qcopd_register_sld_sorting_menu() {
	add_submenu_page(
		'edit.php?post_type=sld',
		__('List Ordering', 'qc-opd'),
		__('List Ordering', 'qc-opd'),
		'edit_pages', 'sld-order',
		'qcopd_sld_order_page'
	);
}

//Submenu Callback to show ordering page contents
function qcopd_sld_order_page() {
?>
	<div class="wrap">

		<?php 

		$terms = get_terms( array(
		    'taxonomy' => 'sld_cat',
		    'hide_empty' => false,
		) );

		?>

		<h2><?php echo esc_html('Order Directory Items'); ?></h2>
		<p><?php echo esc_html('Simply drag the item up or down and they will be saved in that order.'); ?></p>

		<div class="filter-sld-tax" style="margin-bottom: 15px; padding-bottom: 5px; border-bottom: 1px solid #ccc;">
			<a href="#" class="filter-btn" data-filter="all">
				<?php _e('Show All', 'qc-opd'); ?>
			</a>

			<?php foreach ($terms as $term) : ?>
				<a href="#" class="filter-btn" data-filter="<?php echo esc_attr($term->slug); ?>">
					<?php echo esc_html($term->name); ?>
				</a>
			<?php endforeach; ?>
		</div>

	<?php $sld = new WP_Query( array( 'post_type' => 'sld', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'menu_order' ) ); ?>
	<?php if( $sld->have_posts() ) : ?>

		<table id="opd-sort-tbl" class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th class="column-order"><?php echo esc_html('Order'); ?></th>
					<th class="column-title"><?php echo esc_html('Title'); ?></th>
					<th class="column-cat"><?php echo esc_html('Category'); ?></th>
					<th class="column-elem"><?php echo esc_html('Number of Elements'); ?></th>
					<th class="column-code"><?php echo esc_html('Shortcode'); ?></th>
				</tr>
			</thead>
			<tbody class="tbl-body" data-post-type="sld">
			<?php while( $sld->have_posts() ) : $sld->the_post(); ?>

				<?php 
					//Get all the term slugs for this post
					$terms = get_the_terms( get_the_ID(), 'sld_cat' );
					$termListTitles = "";
					$termListSlugs = "";

					if( $terms && !is_wp_error( $terms ) ) 
					{
						
						$count = 1;
						$length = count($terms);

					    foreach( $terms as $term ) 
					    {
					        
					        $termListSlugs .= esc_attr($term->slug) . " ";
					        $termListTitles .= esc_html($term->name);
					        
					        if( $count != $length ){
					        	$termListTitles .= ", ";
					        }

					        $count++;

					    }

					} 

				?>

				<tr id="post-<?php the_ID(); ?>" class="all-row <?php echo esc_attr($termListSlugs); ?>">
					<td class="column-order">
						<img src="<?php echo SLD_QCOPD_IMG_URL . '/move_alt1.png'; ?>" title="" alt="Move Icon" width="24" height="24" class="" />
					</td>
					<td class="column-title">
						<strong><?php the_title(); ?></strong>
					</td>
					<td class="column-category">
						<?php echo esc_html($termListTitles); ?>
					</td>
					<td class="column-elem">
						<?php echo count(get_post_meta( get_the_ID(), 'qcopd_list_item01' )); ?>
					</td>
					<td class="column-code">
					<?php echo '[qcopd-directory mode="one" list_id="'.get_the_ID().'"]'; ?>
					</td>
				</tr>
			<?php endwhile; ?>
			</tbody>
			<tfoot>
				<tr>
					<th class="column-order"><?php echo esc_html('Order'); ?></th>
					<th class="column-title"><?php echo esc_html('Title'); ?></th>
					<th class="column-cat"><?php echo esc_html('Category'); ?></th>
					<th class="column-elem"><?php echo esc_html('Number of Elements'); ?></th>
					<th class="column-code"> <?php echo esc_html('Shortcode'); ?></th>
				</tr>
			</tfoot>

		</table>

	<?php else: ?>

		<p> <?php echo esc_html('No team found, why not'); ?><a href="<?php echo esc_url('post-new.php?post_type=gts_team'); ?>"> <?php echo esc_html('create one?'); ?></a></p>

	<?php endif; ?>
	<?php wp_reset_postdata(); // Don't forget to reset again! ?>

	</div><!-- .wrap -->

<?php

}

//jQuery UI Sorting
add_action( 'admin_enqueue_scripts', 'qcopd_admin_enqueue_scripts' );

function qcopd_admin_enqueue_scripts() {
	if( isset($_GET['post_type']) && $_GET['post_type'] == 'sld' ){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'qcopd-sorting-scripts', SLD_QCOPD_ASSETS_URL . '/js/qcopd-admin-scripts.js' );

		$params 					= array(
		  'ajaxurl' 				=> admin_url('admin-ajax.php'),
		  'ajax_nonce' 				=> wp_create_nonce('quantum_ajax_validation_18')
		);
		wp_localize_script( 'qcopd-sorting-scripts', 'sld_ajax_object', $params );

	}
}

//Registering ajax for saving sort order
add_action( 'wp_ajax_sld_update_post_order', 'sld_update_post_order' );

function sld_update_post_order() {

	check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
	global $wpdb;

	$post_type    = isset($_POST['postType']) ? $_POST['postType'] : '';
	$order        = isset($_POST['order']) ? $_POST['order'] : '';

	foreach( $order as $menu_order => $post_id )
	{
		$post_id         = intval( str_ireplace( 'post-', '', $post_id ) );
		$menu_order     = intval($menu_order);
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => $menu_order ) );
	}

	die( '1' );
	
}


