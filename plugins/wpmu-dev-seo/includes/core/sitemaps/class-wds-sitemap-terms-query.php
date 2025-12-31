<?php

class Smartcrawl_Sitemap_Terms_Query extends Smartcrawl_Sitemap_Query {
	function get_items( $type = '', $page_number = 0 ) {
		return $this->get_term_items( $type, $page_number );
	}

	/**
	 * @param $term WP_Term
	 *
	 * @return bool
	 */
	public function is_term_included( $term ) {
		if ( ! is_a( $term, 'WP_Term' ) ) {
			return false;
		}

		if ( ! in_array( $term->taxonomy, $this->get_supported_types(), true ) ) {
			return false;
		}

		$term_items = $this->get_term_items( $term->taxonomy, 0, array( $term->term_id ) );
		return ! empty( $term_items );
	}

	private function get_term_items( $type, $page_number, $include_ids = array() ) {
		if ( smartcrawl_is_switch_active( 'SMARTCRAWL_SITEMAP_SKIP_TAXONOMIES' ) ) {
			return array();
		}

		$terms = $this->query_terms( $type, $page_number, $include_ids );
		$items = array();
		foreach ( $terms as $term_data ) {
			$term = new WP_Term( $term_data );
			$url = $this->get_term_url( $term );
			if ( smartcrawl_get_term_meta( $term, $term->taxonomy, 'wds_noindex' ) || Smartcrawl_Sitemap_Utils::is_url_ignored( $url ) ) {
				continue;
			}

			$item = new Smartcrawl_Sitemap_Item();
			$item->set_location( $url )
			     ->set_priority( $this->get_term_priority( $term ) )
			     ->set_change_frequency( Smartcrawl_Sitemap_Item::FREQ_WEEKLY )
			     ->set_last_modified( $this->get_term_last_modified( $term ) );
			$items[] = $item;
		}

		return $items;
	}

	private function query_terms( $type, $page_number, $include_ids ) {
		global $wpdb;

		$terms = $wpdb->terms;
		$term_taxonomy = $wpdb->term_taxonomy;
		$term_relationships = $wpdb->term_relationships;
		$posts = $wpdb->posts;

		$included_types = empty( $type ) ? $this->get_supported_types() : array( $type );
		if ( empty( $included_types ) ) {
			return array();
		}
		$included_types_placeholders = $this->get_db_placeholders( $included_types );
		$included_types_string = $wpdb->prepare( $included_types_placeholders, $included_types );
		$types_where = "AND taxonomy IN ({$included_types_string}) ";

		$include_ids_where = '';
		if ( $include_ids ) {
			$include_ids_placeholders = $this->get_db_placeholders( $include_ids, '%d' );
			$include_ids_string = $wpdb->prepare( $include_ids_placeholders, $include_ids );
			$include_ids_where = "AND {$terms}.term_id IN ({$include_ids_string})";
		}

		$limit = $this->get_limit( $page_number );
		$offset = $this->get_offset( $page_number );

		$sub_query = "SELECT term_taxonomy_id, MAX(post_modified) AS last_modified FROM {$term_relationships} " .
		             "INNER JOIN {$posts} ON object_id = ID " .
		             "GROUP BY term_taxonomy_id";

		// TODO check if we need to sort on taxonomy before anything else so that terms of the same taxonomy are grouped together
		$query = "SELECT {$terms}.term_id, name, slug, term_group, {$term_taxonomy}.term_taxonomy_id, taxonomy, description, parent, count, last_modified FROM {$terms} " .
		         "INNER JOIN {$term_taxonomy} ON {$terms}.term_id = {$term_taxonomy}.term_id " .
		         "LEFT OUTER JOIN ({$sub_query}) AS term_post_data ON term_post_data.term_taxonomy_id = {$term_taxonomy}.term_taxonomy_id " .
		         "WHERE count > 0 " .
		         "{$include_ids_where} " .
		         "{$types_where} " .
		         "ORDER BY last_modified ASC, term_id ASC " .
		         "LIMIT {$limit} OFFSET {$offset}";

		$terms = $wpdb->get_results( $query );

		return $terms ? $terms : array();
	}

	private function get_db_placeholders( $items, $single_placeholder = '%s' ) {
		return join( ',', array_fill( 0, count( $items ), $single_placeholder ) );
	}

	public function get_supported_types() {
		$smartcrawl_options = Smartcrawl_Settings::get_options();
		$candidates = get_taxonomies( array(
			'public'  => true,
			'show_ui' => true,
		), 'objects' );

		$sitemap_taxonomies = array();
		foreach ( $candidates as $taxonomy ) {
			if ( ! empty( $smartcrawl_options[ 'taxonomies-' . $taxonomy->name . '-not_in_sitemap' ] ) ) {
				continue;
			}
			$sitemap_taxonomies[] = $taxonomy->name;
		}

		return $sitemap_taxonomies;
	}

	public function get_filter_prefix() {
		return 'wds-sitemap-terms';
	}

	private function get_term_url( $term ) {
		$canonical = smartcrawl_get_term_meta( $term, $term->taxonomy, 'wds_canonical' );
		return $canonical
			? $canonical
			: get_term_link( $term, $term->taxonomy );
	}

	private function get_term_priority( $term ) {
		$default = $term->count > 3 ? 0.4 : 0.2;
		$priority = $term->count > 10
			? 0.6
			: $default;

		return apply_filters( 'wds-term-priority', $priority );
	}

	private function get_term_last_modified( $term ) {
		return empty( $term->last_modified )
			? time()
			: strtotime( $term->last_modified );
	}
}