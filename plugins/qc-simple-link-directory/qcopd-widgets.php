<?php

/**
 * 1. Adds QcOpdMostPopular_Widget widget.
 */
class QcOpdMostPopular_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'QcOpdMostPopular_Widget',
			esc_html__( 'SLD - Most Popular Links', 'qc-opd' ),
			array( 
				'description' => esc_html__( 'Widget to display most popular list items from - simple link directory.', 'qc-opd' ),
			)
		);
		    if(is_active_widget(false, false, $this->id_base))
			{
				add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
			}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$limit = 5;
		$subtitle = '';
		$category = '';

		if ( ! empty( $instance['limit'] ) ) {
			$limit = $instance['limit'];
		}

		if ( ! empty( $instance['subtitle'] ) ) {
			$subtitle = $instance['subtitle'];
		}

		if ( ! empty( $instance['category'] ) ) {
			$limit = $instance['category'];
		}

		echo qcopd_get_most_popular_links_wi( $limit, $category );

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Popular Links', 'qc-opd' );
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : esc_html__( '5', 'qc-opd' );
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : esc_html__( '', 'qc-opd' );
		$category = ! empty( $instance['category'] ) ? $instance['category'] : esc_html__( '', 'qc-opd' );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_attr_e( 'Limit:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>">
				<?php esc_attr_e( 'Subtitle:', 'qc-opd' ); ?>
			</label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>">
				<option><?php echo esc_attr( 'Hide' ); ?></option>
				<option value="show" <?php echo ( $subtitle == 'show' ? 'selected' : '' ); ?>><?php echo esc_attr( 'Show' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
				<?php esc_attr_e( 'Category:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>" type="text" value="<?php echo esc_attr( $category ); ?>">
		</p>
		<p><i><?php echo esc_html( 'You can show specific category, leave empty for show all. You can add multiple Category ID as coma(,) seperated value.' ); ?></i></p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';
		$instance['subtitle'] = ( ! empty( $new_instance['subtitle'] ) ) ? strip_tags( $new_instance['subtitle'] ) : '';
		$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';

		return $instance;
	}

} // class QcOpdMostPopular_Widget


/**
 * 2. Adds QcOpdRandomLinks_Widget widget.
 */
class QcOpdRandomLinks_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'QcOpdRandomLinks_Widget',
			esc_html__( 'SLD - Random Links', 'qc-opd' ),
			array( 
				'description' => esc_html__( 'Widget to display randomly picked list items from - simple link directory.', 'qc-opd' ),
			)
		);
		if(is_active_widget(false, false, $this->id_base))
			{
				add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
			}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$limit = 5;
		$category = '';
		$subtitle = '';

		if ( ! empty( $instance['limit'] ) ) {
			$limit = $instance['limit'];
		}

		if ( ! empty( $instance['subtitle'] ) ) {
			$subtitle = $instance['subtitle'];
		}

		if ( ! empty( $instance['category'] ) ) {
			$limit = $instance['category'];
		}

		echo qcopd_get_random_links_wi( $limit, $category, $subtitle );

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Random Links', 'qc-opd' );
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : esc_html__( '5', 'qc-opd' );
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : esc_html__( '', 'qc-opd' );
		$category = ! empty( $instance['category'] ) ? $instance['category'] : esc_html__( '', 'qc-opd' );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_attr_e( 'Limit:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>">
				<?php esc_attr_e( 'Subtitle:', 'qc-opd' ); ?>
			</label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>">
				<option><?php echo esc_attr( 'Hide' ); ?></option>
				<option value="show" <?php echo ( $subtitle == 'show' ? 'selected' : '' ); ?>><?php echo esc_attr( 'Show' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
				<?php esc_attr_e( 'Category:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>" type="text" value="<?php echo esc_attr( $category ); ?>">
		</p>
		<p><i><?php echo esc_html( 'You can show specific category, leave empty for show all. You can add multiple Category ID as coma(,) seperated value.' ); ?></i></p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';
		$instance['subtitle'] = ( ! empty( $new_instance['subtitle'] ) ) ? strip_tags( $new_instance['subtitle'] ) : '';
		$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';

		return $instance;
	}

} // class QcOpdRandomLinks_Widget


/**
 * 3. Adds QcOpdLatestLinks_Widget widget.
 */
class QcOpdLatestLinks_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'QcOpdLatestLinks_Widget',
			esc_html__( 'SLD - Latest Links', 'qc-opd' ),
			array( 
				'description' => esc_html__( 'Widget to display the most recent list items from - simple link directory.', 'qc-opd' ),
			)
		);
		if(is_active_widget(false, false, $this->id_base))
			{
				add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
			}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$limit = 5;
		$category = '';
		$subtitle = '';

		if ( ! empty( $instance['limit'] ) ) {
			$limit = $instance['limit'];
		}

		if ( ! empty( $instance['subtitle'] ) ) {
			$subtitle = $instance['subtitle'];
		}

		if ( ! empty( $instance['category'] ) ) {
			$category = $instance['category'];
		}

		echo qcopd_get_latest_links_wi( $limit, $category, $subtitle );

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Latest Links', 'qc-opd' );
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : esc_html__( '5', 'qc-opd' );
		$category = ! empty( $instance['category'] ) ? $instance['category'] : esc_html__( '', 'qc-opd' );
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : esc_html__( '', 'qc-opd' );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_attr_e( 'Limit:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>">
				<?php esc_attr_e( 'Subtitle:', 'qc-opd' ); ?>
			</label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>">
				<option><?php echo esc_attr( 'Hide' ); ?></option>
				<option value="show" <?php echo ( $subtitle == 'show' ? 'selected' : '' ); ?>><?php echo esc_attr( 'Show' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
				<?php esc_attr_e( 'Category:', 'qc-opd' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>" type="text" value="<?php echo esc_attr( $category ); ?>">
		</p>
		<p><i><?php echo esc_html( 'You can show specific category, leave empty for show all. You can add multiple Category ID as coma(,) seperated value.' ); ?></i></p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';
		$instance['subtitle'] = ( ! empty( $new_instance['subtitle'] ) ) ? strip_tags( $new_instance['subtitle'] ) : '';
		$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';

		return $instance;
	}

} // class QcOpdLatestLinks_Widget


// Register Widgets
function qcopd_register_custom_widgets() {
    register_widget( 'QcOpdMostPopular_Widget' );
    register_widget( 'QcOpdRandomLinks_Widget' );
    register_widget( 'QcOpdLatestLinks_Widget' );
}

add_action( 'widgets_init', 'qcopd_register_custom_widgets' );