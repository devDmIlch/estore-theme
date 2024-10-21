<?php
/**
 * Archive class file for managing display of store-items
 *
 * @package estore/theme
 * @since   0.0.1
 */

namespace EStore\Core\Archive;

use EStore\Enum\OrderStatus;
use EStore\Ext\Helpers;
use EStore\PostTypes\StoreItem;
use EStore\Taxonomies\TaxonomyController;

/**
 * Archive class
 */
class Archive {

	#region Private Fields.

	/**
	 * REST API namespace
	 * @var string $api_namespace
	 */
	private string $api_namespace;

	#endregion.


	#region Construction Methods.

	/**
	 * Class initialization method.
	 */
	public function init(): void {
		// Set endpoint namespace.
		$this->api_namespace = 'estore/archive';

		// Initialize hooks.
		$this->hooks();
	}

	/**
	 * Class hook initialization method.
	 */
	protected function hooks(): void {
		// Localization Script.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_archive_script_data' ], 20 );

		// REST.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	#endregion.


	#region Private Methods.

	/**
	 * Returns an array of sorting labels.
	 *
	 * @return array An array of labels.
	 */
	private function get_sort_names(): array {
		return [
			'new'        => __( 'Спочатку Новіші', 'estore-theme' ),
			'name'       => __( 'За Алфавітом', 'estore-theme' ),
			'price_asc'  => __( 'Спочатку Дешевші', 'estore-theme' ),
			'price_desc' => __( 'Спочатку Дорожчі', 'estore-theme' ),
		];
	}

	/**
	 * Creates query parameters to narrow down query to selected options.
	 *
	 * @param array $filters   Available filters for type reference.
	 * @param array $selected  Selected values.
	 *
	 * @return array array of query arguments.
	 */
	private function get_query_parameters_for_selected( array $filters, array $selected ): array {
		// Create initial array to be filled with data.
		$args = [];

		foreach ( $selected as $filter_name => $values ) {
			// Skip if the values are empty to avoid showing empty results after deselecting an option.
			if ( empty( $values ) ) {
				continue;
			}

			switch ( $filters[ $filter_name ]['type'] ) {
				case 'tax':
					// Check whether tax query array exists in the list of arguments.
					if ( ! is_array( $args['tax_query'] ) ) {
						$args['tax_query'] = [
							'relation' => 'AND',
						];
					}
					// Add new values to be searched for.
					$args['tax_query'][] = [
						'taxonomy' => $filter_name,
						'field'    => 'term_id',
						'terms'    => $values,
					];

					break;
			}
		}

		// Return array with values.
		return $args;
	}

	/**
	 * Creates query parameters to sort items.
	 *
	 * @param string $selected Selected sorting option.
	 *
	 * @return array array of query arguments.
	 */
	private function get_query_parameters_for_sorting( string $selected ): array {
		$args = [];

		switch ( $selected ) {
			case 'name':
				$args = [
					'order'   => 'ASC',
					'orderby' => 'title',
				];
				break;
			case 'price_asc':
				$args = [
					'order'    => 'ASC',
					'orderby'  => 'meta_value_num',
					'meta_key' => 'estore-default-price',
				];
				break;
			case 'price_desc':
				$args = [
					'order'    => 'DESC',
					'orderby'  => 'meta_value_num',
					'meta_key' => 'estore-default-price',
				];
				break;
			case 'post__in':
				$args = [
					'order'   => 'ASC',
					'orderby' => 'post__in',
				];
				break;
			case 'new':
			default:
				$args = [
					'order'   => 'ASC',
					'orderby' => 'date',
				];
				break;
		}

		return $args;
	}

	/**
	 * Renders store items into a string based on passed query arguments.
	 *
	 * @param array $query_args WP_Query arguments.
	 * @param int   $page_count Number of pages.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_store_items( array $query_args, int &$page_count = 0 ): string {
		// Prepare variable to store HTML into.
		$html = '';

		// Create the query.
		$query = new \WP_Query( $query_args );

		while ( $query->have_posts() ) {
			$query->the_post();
			// Get post details.
			$args = StoreItem::get_store_item_data();

			// Get card html.
			$html .= Helpers::render_template_to_string( 'template-parts/components/card', 'store-item', $args );
		}

		$page_count = $query->max_num_pages;

		wp_reset_postdata();

		return $html;
	}

	/**
	 * Renders orders into a string based on passed query arguments.
	 *
	 * @param array $query_args WP_Query arguments.
	 * @param int   $page_count Number of pages.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_orders( array $query_args, int &$page_count = 0 ): string {
		// Prepare variable to store HTML into.
		$html = '';

		// Create the query.
		$query = new \WP_Query( $query_args );

		while ( $query->have_posts() ) {
			$query->the_post();
			// Create array with post data.
			$args = [
				'title' => get_the_title(),
				'id'    => get_the_ID(),
			];

			// Get info about order items.
			$order_items = get_post_meta( $args['id'], 'order-item', true );

			array_walk(
				$order_items,
				static function ( &$data, $var_id ): void {
					$data = StoreItem::get_store_item_data( $data['post_id'], $var_id, true );
				}
			);

			// Push order items into the argument array.
			$args['order-items'] = $order_items;

			// Push order status into the argument array.
			$args['order-status'] = OrderStatus::tryFrom( (int) get_post_meta( $args['id'], 'order-status', true ) );

			// Check if the order is eligible for cancellation.
			$args['can-cancel'] = ! ( $args['order-status']->value > 5 );

			// Get card html.
			$html .= Helpers::render_template_to_string( 'template-parts/components/card', 'order', $args );
		}

		$page_count = $query->max_num_pages;

		wp_reset_postdata();

		return $html;
	}

	/**
	 * Renders filters into a string based on passed arguments.
	 *
	 * @param array $filters  Present filters.
	 * @param array $selected Selected options.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_estore_filters( array $filters, array $selected = [] ): string {
		// Prepare variable to store HTML into.
		$html = '';

		foreach ( $filters as $tax => $terms ) {
			if ( empty( $terms ) ) {
				continue;
			}

			$args = [
				'tax'      => get_taxonomy( $tax ),
				'terms'    => $terms,
				'selected' => $selected[ $tax ] ?? [],
			];

			$html .= Helpers::render_template_to_string( 'template-parts/components/filter', 'checkbox', $args );
		}

		return $html;
	}

	/**
	 * Renders active filters' controls into a string based on passed arguments.
	 *
	 * @param array $filters  Present filters.
	 * @param array $selected Selected options.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_estore_active_filters( array $filters, array $selected = [] ): string {
		// Prepare variable to store HTML into.
		$html = '';

		foreach ( $filters as $tax => $terms ) {
			// Skip if nothing has been selected.
			if ( empty( $selected[ $tax ] ) ) {
				continue;
			}

			$args = [
				'tax'      => get_taxonomy( $tax ),
				'terms'    => $terms,
				'selected' => $selected[ $tax ],
			];

			$html .= Helpers::render_template_to_string( 'template-parts/archive/active-filter', 'control', $args );
		}

		return $html;
	}

	/**
	 * Renders sorter controls into a string based on passed arguments.
	 *
	 * @param array  $options  Sorting options.
	 * @param string $selected Selected option.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_estore_sorter( array $options, string $selected ): string {
		$html = '';

		if ( ! empty( $options ) ) {
			$args = [
				'options'  => array_filter( $this->get_sort_names(), static fn ( $key ) => in_array( $key, $options, true ), ARRAY_FILTER_USE_KEY ),
				'selected' => $selected,
			];

			$html .= Helpers::render_template_to_string( 'template-parts/archive/sorting', args: $args );
		}

		return $html;
	}

	/**
	 * Renders pagination into a string based on passed arguments.
	 *
	 * @param int $pages   Number of pages in the pagination.
	 * @param int $current Currently selected page.
	 *
	 * @return string Html compiled into a string.
	 */
	private function render_estore_pagination( int $pages, int $current ): string {
		return Helpers::render_template_to_string(
			'template-parts/components/pagination',
			args: [
				'pages'   => $pages,
				'current' => $current,
			]
		);
	}

	#endregion


	#region Public Methods.

	/**
	 * Enqueues data about archive as a localization script.
	 */
	public function enqueue_archive_script_data(): void {
		// Bail if the page is not an archive page.
		if ( ! is_archive() && ! is_search() ) {
			return;
		}

		// Set up localization lines to pass into the JS.
		$localization_lines = [
			'type' => get_post_type() ?? 'any',
		];

		// Set archive type to 'search' if querying search.
		if ( is_search() ) {
			$localization_lines = [
				'type'   => 'search',
				'search' => get_search_query(),
			];
		}

		// Pass the queried term if queried taxonomy archive.
		if ( is_tax() ) {
			$localization_lines['tax'] = [
				get_queried_object()->taxonomy => get_queried_object()->term_id,
			];
		}

		// Enqueue archive preferences variables.
		wp_localize_script( 'estore_script', 'archivePrefs', $localization_lines );
	}

	/**
	 * Renders post cards based on the parameters passed with REST.
	 *
	 * @param \WP_REST_Request $request REST API request.
	 *
	 * @return array Request response.
	 */
	public function render_posts( \WP_REST_Request $request ): array {
		// Prepare response array.
		$response = [
			'status' => 200,
		];

		// Get request parameters.
		$params = $request->get_params();

		// Query available parameters.
		$filters = rest_sanitize_object( $params['params'] ?? [] );
		// Parameters available for selection.
		$available_params = [];

		// Get post type parameter, as we use it several times.
		$post_type = rest_sanitize_array( $params['post_type'] );

		// Get the 'search' parameters.
		$search = sanitize_text_field( $params['search'] ?? '' );

		// Get the necessary filters (Filters that should always be selected).
		$necessary_filters = array_map( static fn ( $val ) => is_array( $val ) ? $val : [ $val ], rest_sanitize_object( $params['necessary'] ?? [] ) );
		// Find available options for necessary values.
		foreach ( $necessary_filters as $tax => $term_id_arr ) {
			foreach ( $term_id_arr as $term_id ) {
				// This is okay since there aren't any cases were this function is called more than once here.
				$available_params = array_replace_recursive( $available_params, TaxonomyController::get_term_related_terms( $term_id, $tax ) );
			}
		}

		// If search is queried, set available parameters based on it.
		if ( ! empty( $search ) ) {
			// This cannot be optimized, thus it will always lag comparing to the simpler queries.
			$available_params = array_replace_recursive( $available_params, TaxonomyController::get_search_related_terms( $search, $post_type ) );
		}

		// Get the default values if no necessary filters are supplied.
		if ( empty( $available_params ) ) {
			$available_params = array_replace_recursive( $available_params, TaxonomyController::get_term_related_terms( 0, '' ) );
		}

		// Set up initial parameters for the archive.
		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => [ 'publish' ],
			'posts_per_page' => (int) sanitize_text_field( $params['number'] ),
			'paged'          => (int) sanitize_text_field( $params['page'] ),
		];

		// Add limit on queryable posts.
		$post__in = rest_sanitize_array( $params['post__in'] ?? [] );
		if ( ! empty( $post__in ) ) {
			$query_args['post__in'] = $post__in;
		}

		// Add search parameter to the query if it was passed.
		if ( ! empty( $search ) ) {
			$query_args['s'] = $search;
		}

		// Get the selected values.
		$selected_params = rest_sanitize_object( $params['selected'] ?? [] );

		// Create separate array for the query creation.
		$selected_incl_necessary_params = $selected_params;
		// Assign necessary filters if nothing is selected.
		foreach ( $necessary_filters as $filter_name => $filter_val ) {
			if ( empty( $selected_incl_necessary_params[ $filter_name ] ) ) {
				$selected_incl_necessary_params[ $filter_name ] = $filter_val;
			}
		}
		// Add query parameters.
		$query_args = array_merge( $query_args, $this->get_query_parameters_for_selected( $filters, $selected_incl_necessary_params ) );

		// Get the selected sorting option.
		$sort_selected = sanitize_text_field( $params['sort_selected'] ?? '' );
		// Add sorting parameter to query arguments.
		$query_args = array_merge( $query_args, $this->get_query_parameters_for_sorting( $sort_selected ) );

		// Prepare a variable to save the number of pages.
		$total_pages = 0;

		// Load right template for the archive.
		switch ( $params['template'] ) {
			case 'store-item':
				$response['content'] = $this->render_store_items( $query_args, $total_pages );
				break;
			case 'order':
				$response['content'] = $this->render_orders( $query_args, $total_pages );
				break;
		}

		// Check if the content exists.
		if ( empty( $response['content'] ) ) {
			// Set flag indicating missing content to true.
			$response['empty'] = true;
			// Render special message.
			if ( ! empty( $search ) ) {
				$response['content'] = Helpers::render_template_to_string( 'template-parts/archive/no-content', args: [ 'message' => __( 'За вашим запитом не знайдено товарів', 'estore-theme' ) ] );
			} else {
				$response['content'] = Helpers::render_template_to_string( 'template-parts/archive/no-content', args: [ 'message' => __( 'Жоден товар не відповідає вибріним фільтрам', 'estore-theme' ) ] );
			}
		}

		// Load filters if an argument was passed.
		if ( rest_sanitize_boolean( $params['filters'] ?? false ) ) {
			$response['filters'] = $this->render_estore_filters( $available_params, $selected_params );
		}
		// Load active filters if an argument was passed.
		if ( rest_sanitize_boolean( $params['active_filters'] ?? false ) ) {
			$response['active_filters'] = $this->render_estore_active_filters( $available_params, $selected_params );
		}
		// Load sorter if an argument was passed.
		if ( rest_sanitize_boolean( $params['sorter'] ?? false ) ) {
			$response['sorter'] = $this->render_estore_sorter( rest_sanitize_array( $params['sort_options'] ?? [] ), $sort_selected );
		}
		// Load pagination if an argument was passed.
		if ( rest_sanitize_boolean( $params['pagination'] ?? false ) ) {
			$response['pagination'] = $this->render_estore_pagination( $total_pages, $query_args['paged'] );
		}

		return $response;
	}

	/**
	 * Registers REST API endpoints.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			$this->api_namespace,
			'posts',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'render_posts' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'args'                => [
						// General Settings.
						'post_type'      => [
							'type'     => 'array',
							'required' => false,
						],
						// Page settings.
						'number'         => [
							'type'     => 'int',
							'required' => false,
						],
						'page'           => [
							'type'     => 'int',
							'required' => false,
						],
						// Display Settings.
						'template'       => [
							'type'     => 'string',
							'required' => false,
						],
						'filters'        => [
							'type'     => 'boolean',
							'required' => false,
						],
						'active_filters' => [
							'type'     => 'boolean',
							'required' => false,
						],
						'sorter'         => [
							'type'     => 'boolean',
							'required' => false,
						],
						// Archive Parameters.
						'params'         => [
							'type'     => 'object',
							'required' => false,
						],
						'selected'       => [
							'type'     => 'object',
							'required' => false,
						],
						'necessary'      => [
							'type'     => 'object',
							'required' => false,
						],
						'search'         => [
							'type'     => 'string',
							'required' => false,
						],
						'post__in'      => [
							'type'     => 'array',
							'required' => false,
						],
						// Sorting Parameters.
						'sort_options'   => [
							'type'     => 'array',
							'required' => false,
						],
						'sort_selected'  => [
							'type'     => 'string',
							'required' => false,
						],
					],
					'show_in_index'       => true,
				],
			]
		);
	}

	#endregion

	/**
	 * Loads currently queried archive page.
	 */
	public function load_archive_page(): void {
		// Prepare initial parameters.
		$args = [
			'filters'    => true,
			'pagination' => true,
		];

		// Add name based on archive type.
		if ( is_archive() ) {
			/* translators: here 'Усі'/'All' precedes name of a post type */
			$args['title'] = __( 'Усі ', 'estore-theme' ) . get_queried_object()->label;
		}

		if ( is_tax() ) {
			$args['title'] = get_queried_object()->name;
		}

		if ( is_search() ) {
			$args['title'] = __( 'Пошук: ', 'estore-theme' ) . get_search_query();
		}

		get_template_part( 'template-parts/pages/archive', args: $args );
	}
}
