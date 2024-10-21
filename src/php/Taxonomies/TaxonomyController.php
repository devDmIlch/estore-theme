<?php
/**
 * Taxonomy Controller class template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Taxonomies;

use EStore\Ext\Helpers;

/**
 * Post type controller class for the estore theme
 */
class TaxonomyController {


	#region Initializing Methods.

	/**
	 * Initializing method for the class.
	 */
	public function init(): void {
		// Initialize Store Items.
		$brand_taxonomy = new Brand();
		$brand_taxonomy->init();

		// Initialize Sale Pages.
		$item_categories_taxonomy = new Category();
		$item_categories_taxonomy->init();
	}

	/**
	 * Hooks initialization method for the class.
	 */
	protected function hooks(): void {

	}

	#endregion.


	#region Private Methods.

	/**
	 * Returns list of terms that match posts matching query arguments.
	 *
	 * @param array $query_args    WP_Query arguments.
	 * @param array $exclude_terms List of terms that should be excluded.
	 *
	 * @return array An associative array of viable term.
	 */
	private static function get_terms_based_on_query( array $query_args, array $exclude_terms = [] ): array {
		$query = new \WP_Query( $query_args );

		while ( $query->have_posts() ) {
			$query->the_post();

			$__post_id = get_the_ID();

			foreach ( get_post_taxonomies( $__post_id ) as $tax ) {
				// Set list of terms to array if it isn't already.
				if ( ! isset( $sub_terms[ $tax ] ) ) {
					$sub_terms[ $tax ] = [];
				}
				// Push a value into the array.
				foreach ( wp_get_post_terms( $__post_id, $tax ) as $term ) {
					// Check whether the term is already present in the array.
					if ( array_key_exists( $term->term_id, $sub_terms[ $tax ] ) ) {
						continue;
					}
					// Check if the term is parent to the lopped one.
					if ( in_array( $term->term_id, $exclude_terms, true ) ) {
						continue;
					}

					$sub_terms[ $tax ][ $term->term_id ] = $term->name;
				}
			}
		}

		wp_reset_postdata();

		// Return empty array if sub_terms array is empty.
		if ( empty( $sub_terms ) ) {
			return [];
		}

		// Sort terms by their depth.
		array_walk( $sub_terms, static fn ( &$terms, $tax ) => uksort( $terms, static fn ( $val_1, $val_2 ) => count( get_ancestors( $val_1, $tax ) ) - count( get_ancestors( $val_2, $tax ) ) ) );

		// Create an array to fill with terms in hierarchical order.
		$hierarchically_accurate_terms = [];

		$push_term_into_array = static function ( $term, $tax, &$term_ids, $depth = 0 ) use ( &$push_term_into_array, &$hierarchically_accurate_terms ) {
			// Remove term from the array of ids.
			$term_pos = array_search( $term, $term_ids, true );
			if ( false !== $term_pos ) {
				unset( $term_ids[ $term_pos ] );
			}
			// Get term details.
			$term_obj = get_term( $term, $tax );
			// Get term child ids and filter out not suitable ones.
			$child_term_ids = array_intersect( $term_ids, get_term_children( $term, $tax ) );

			// Push value with information into an array.
			$hierarchically_accurate_terms[ $tax ][ $term ] = [
				'name'         => $term_obj->name,
				'depth'        => $depth,
				'parent'       => $term_obj->parent,
				'has_children' => count( $child_term_ids ) > 0,
			];
			// Do a recursive call to put all children inside the array, before proceeding to next value.
			foreach ( $child_term_ids as $child_term_id ) {
				// Check whether the term id was already inserted as child of other child.
				if ( ! array_key_exists( $child_term_id, $hierarchically_accurate_terms[ $tax ] ) ) {
					$push_term_into_array( $child_term_id, $tax, $term_ids, $depth + 1 );
				}
			}
		};

		array_walk(
			$sub_terms,
			static function ( &$terms, $tax ) use ( $push_term_into_array, &$hierarchically_accurate_terms ) {
				// Create separate array for each taxonomy.
				$hierarchically_accurate_terms[ $tax ] = [];
				// Get a list of ids.
				$term_ids = array_keys( $terms );

				$terms_remain = count( $term_ids );
				while ( $terms_remain > 0 ) {
					// Get next term from the list of term arrays.
					$term = array_shift( $term_ids );
					// Push term into array.
					$push_term_into_array( $term, $tax, $term_ids );

					$terms_remain = count( $term_ids );
				}
			}
		);

		// Return sorted array.
		return $hierarchically_accurate_terms;
	}


	#endregion


	#region Public Methods.

	/**
	 * Returns a name of the transient which stores data about intersecting terms.
	 *
	 * @param int $term_id ID of a term.
	 *
	 * @return string A string-name of the transient.
	 */
	public static function get_term_related_transient_name( int $term_id ): string {
		return 'term_related_' . $term_id;
	}

	/**
	 * Retrieves a list of viable terms for posts for a search query.
	 *
	 * @param string       $search_string Queried search string.
	 * @param string|array $post_type     Post types.
	 *
	 * @return array An associative array of the term intersecting with term passed as an argument.
	 */
	public static function get_search_related_terms( string $search_string, string|array $post_type = 'any' ): array {
		// Set parameters for all terms.
		$query_args = [
			'nopaging'  => true,
			'post_type' => $post_type,
			's'         => $search_string,
		];

		return self::get_terms_based_on_query( $query_args );
	}

	/**
	 * Retrieves a list of viable terms for posts with certain term.
	 * Saves data in transient for improved performance.
	 *
	 * @param int          $term_id   ID of a term.
	 * @param string       $taxonomy  Name of the taxonomy.
	 * @param string|array $post_type Post types.
	 *
	 * @return array An associative array of the term intersecting with term passed as an argument.
	 */
	public static function get_term_related_terms( int $term_id, string $taxonomy, string|array $post_type = 'any' ): array {

		$transient_name = self::get_term_related_transient_name( $term_id );
		// Attempt to retrieve data from cache.
		$sub_terms = get_transient( $transient_name );
		// If the cache is empty get data from DB.
		if ( empty( $sub_terms ) ) {
			$sub_terms = [];

			// Get term ancestors to filter out parent terms.
			$term_ancestors = [ ...get_ancestors( $term_id, $taxonomy ), $term_id ];

			// Set parameters for all terms.
			$query_args = [
				'nopaging'  => true,
				'post_type' => $post_type,
			];
			// Set parameters for non-empty terms id.
			if ( $term_id > 0 ) {
				$query_args['tax_query'] = [
					[
						'taxonomy'         => $taxonomy,
						'field'            => 'term_id',
						'terms'            => [ $term_id ],
						'include_children' => true,
					],
				];
			}

			$sub_terms = self::get_terms_based_on_query( $query_args, $term_ancestors );

			// Set cache for later use.
			set_transient( $transient_name, $sub_terms );
		}
		// Return the data.
		return $sub_terms;
	}

	#endregion.
}
