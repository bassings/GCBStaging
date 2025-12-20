<?php

class Smartcrawl_Checkup_Renderer extends Smartcrawl_Renderable {
	private static $_instance;

	private $ignores;

	public function __construct() {
		$this->ignores = new Smartcrawl_Model_Ignores( Smartcrawl_Model_Ignores::IGNORES_CHECKUP_STORAGE );
	}

	public static function get_instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public static function render( $view, $args = array() ) {
		$instance = self::get_instance();
		$instance->_render( $view, $args );
	}

	public static function load( $view, $args = array() ) {
		$instance = self::get_instance();

		return $instance->_load( $view, $args );
	}

	public function get_view_defaults() {
		return $this->_get_view_defaults();
	}

	protected function _get_view_defaults() {
		$service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_CHECKUP );
		$is_member = $service->is_member();
		$results = $service->result();
		$items = smartcrawl_get_array_value( $results, 'items' );
		$error = smartcrawl_get_array_value( $results, 'error' );

		$issue_count = null;
		$score = null;
		$ignored_items = array();
		$active_items = array();

		if ( empty( $error ) && is_array( $items ) ) {
			$passed_count = 0;
			foreach ( $items as $item_key => $item ) {
				if (
					$is_member // Ignores are for members only
					&& $this->ignores->is_ignored( $item_key )
				) {
					$passed_count ++;
					$ignored_items[ $item_key ] = $item;
				} else {
					if ( smartcrawl_get_array_value( $item, 'type' ) === 'ok' ) {
						$passed_count ++;
					}

					$active_items[ $item_key ] = $item;
				}
			}

			$issue_count = count( $items ) - $passed_count;
			$score = $this->percentage( $passed_count, count( $items ) );
		}

		return array(
			'service'                => $service,
			'in_progress'            => $service->in_progress(),
			'is_member'              => $is_member,
			'score'                  => $score,
			'issue_count'            => $issue_count,
			'error'                  => $error,
			'items'                  => $items,
			'ignored_items'          => $ignored_items,
			'active_items'           => $active_items,
			'last_checked_timestamp' => $service->get_last_checked_timestamp(),
		);
	}

	private function percentage( $amount, $base ) {
		if ( $base === 0 ) {
			return 0;
		}

		$percentage = $amount / $base * 100;
		if ( $percentage < 0 ) {
			$percentage = 0;
		}
		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}