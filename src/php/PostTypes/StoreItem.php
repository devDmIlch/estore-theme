<?php
/**
 * Store item class template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\PostTypes;

use EStore\Ext\Helpers;
use EStore\Taxonomies\TaxonomyController;
use EStore\ThemeController;

/**
 * EStore Item post type class
 */
class StoreItem extends GenericPostType {

	#region Private Fields.

	/**
	 * Name of the custom 'variations' table in DB.
	 * @var string $table_name
	 */
	private static string $table_name = 'estore_item_vars';

	/**
	 * Name of the namespace for endpoints related to 'store-item' post type.
	 * @var string $rest_namespace
	 */
	private string $rest_namespace;

	#endregion


	#region Public Properties.

	/**
	 * Returns a name of the custom 'variations' table in DB.
	 *
	 * @return string name of the table without prefix.
	 */
	public static function get_table_name(): string {
		return self::$table_name;
	}

	#endregion


	#region Initializing Methods.

	/**
	 * Class initialization function.
	 */
	public function init(): void {
		// Set Post Type Slug.
		$this->set_slug( 'store-item' );
		// Set Naming.
		$this->set_name( __( 'Товар', 'estore-theme' ) );
		$this->set_singular_name( __( 'Одиниця Товару', 'estore-theme' ) );
		$this->set_plural_name( __( 'Товари', 'estore-theme' ) );
		// Set Description.
		$this->set_description( __( 'Одиниця товару магазину', 'estore-theme' ) );
		// Set icon for post type.
		$this->set_menu_icon( 'dashicons-products' );

		// Set labels.
		$this->set_labels(
			[
				'name'                     => $this->get_plural_name(),
				'singular_name'            => $this->get_singular_name(),
				'add_new'                  => __( 'Додати Товар', 'svitmov' ),
				'add_new_item'             => __( 'Додати Новий Товар', 'svitmov' ),
				'edit_item'                => __( 'Редагувати Товар', 'svitmov' ),
				'new_item'                 => __( 'Новий Товар', 'svitmov' ),
				'view_item'                => __( 'Переглянути Товар', 'svitmov' ),
				'view_items'               => __( 'Переглянути Товар', 'svitmov' ),
				'search_items'             => __( 'Шукати Товари', 'svitmov' ),
				'not_found'                => __( 'Не Знайдено Товарів', 'svitmov' ),
				'not_found_in_trash'       => __( 'Не Знайдено Товарів в Смітнику', 'svitmov' ),
				'all_items'                => __( 'Усі Товари', 'svitmov' ),
				'archives'                 => __( 'Архів Товарів', 'svitmov' ),
				'attributes'               => __( 'Атрибути Товару', 'svitmov' ),
				'insert_into_item'         => __( 'Додати до Товару', 'svitmov' ),
				'insert_into_this_item'    => __( 'Додати до Цього Товару', 'svitmov' ),
				'featured_image'           => __( 'Зображення Товару', 'svitmov' ),
				'set_featured_image'       => __( 'Встановити Зображення Товару', 'svitmov' ),
				'remove_featured_image'    => __( 'Прибрати Зображення Товару', 'svitmov' ),
				'menu_name'                => __( 'Товари', 'svitmov' ),
				'filter_items_list'        => __( 'Фільтрувати Товари', 'svitmov' ),
				'filter_by_date'           => __( 'Фільтрувати Товари за Датою', 'svitmov' ),
				'items_list_navigation'    => __( 'Навігація по Списку Товарів', 'svitmov' ),
				'items_list'               => __( 'Список Товарів', 'svitmov' ),
				'item_published'           => __( 'Товар Опубліковано', 'svitmov' ),
				'item_published_privately' => __( 'Товар Опубліковано Приватним Записом', 'svitmov' ),
				'item_reverted_to_draft'   => __( 'Товар Повернено до Чернеток', 'svitmov' ),
				'item_trashed'             => __( 'Товар Переміщено до Смітника', 'svitmov' ),
				'item_updated'             => __( 'Товар Оновлено', 'svitmov' ),
				'item_link'                => __( 'Посилання на Товар', 'svitmov' ),
				'item_link_description'    => __( 'Опис Посилання на Товар', 'svitmov' ),
			]
		);

		// Set supported features.
		$this->supports = [ 'title', 'excerpt' ];

		// Set rewriting rules.
		$this->rewrite = [
			'slug' => 'products',
		];

		// Set name for endpoint routes.
		$this->rest_namespace = 'estore/store-item';

		// Proceed with default initialization.
		parent::init();

		// Initialize post type hooks.
		$this->hooks();
	}

	/**
	 * Hooks initialization method.
	 */
	protected function hooks(): void {
		// Registers metabox with the item settings.
		add_action( 'add_meta_boxes', [ $this, 'register_settings_metabox' ], 10, 1 );
		// Add saving functionality for the metabox.
		add_action( 'save_post', [ $this, 'save_settings_metabox_data' ], 10, 3 );
		// Removes all outdated archive related transients to improve data loading time for archive pages.
		add_action( 'set_object_terms', [ $this, 'remove_outdated_archive_related_transients' ], 20, 6 );
		// Updates all archive related transients to improve data loading time for archive pages.
		add_action( 'post_updated', [ $this, 'update_archive_related_transients' ], 20, 1 );

		// Initialize localization lines to safely pass data to JS.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_post_data' ], 20 );

		// REST.
		add_action( 'rest_api_init', [ $this, 'admin_rest_routes' ] );
	}

	#endregion

	#region Public Methods.

	/**
	 * Moves items to 'reserved' stock.
	 *
	 * @param array $items      Array of items with quantities.
	 * @param bool  $from_stock Whether to move items from 'stock' (true), or 'sold' (false) column.
	 *
	 * @return bool Whether the operation was successful.
	 */
	public static function move_items_to_reserve( array $items, bool $from_stock = true ): bool {
		global $wpdb;
		// Get data about items from DB.
		$var_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1$i WHERE id IN (' . esc_sql( implode( ', ', array_keys( $items ) ) ) . ');', $wpdb->prefix . self::get_table_name() ), OBJECT_K );

		// Loop through the items to prepare request.
		foreach ( $items as $var_id => $var_data ) {
			// Update values for item.
			if ( $from_stock ) {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity=%2$d, quantity_rsrv=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity - $var_data['qua'],
						( $var_details[ $var_id ]->quantity_rsrv ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			} else {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity_sold=%2$d, quantity_rsrv=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity_sold - $var_data['qua'],
						( $var_details[ $var_id ]->quantity_rsrv ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			}
		}

		return true;
	}

	/**
	 * Pushes items 'sold' stock.
	 *
	 * @param array $items         Array of items with quantities.
	 * @param bool  $were_reserved Whether to move items from 'reserve' (true), or 'stock' (false) column.
	 *
	 * @return bool Whether the operation was successful.
	 */
	public static function push_items_to_sold( array $items, bool $were_reserved = true ): bool {
		global $wpdb;
		// Get data about items from DB.
		$var_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1$i WHERE id IN (' . esc_sql( implode( ', ', array_keys( $items ) ) ) . ');', $wpdb->prefix . self::get_table_name() ), OBJECT_K );

		// Loop through the items to prepare request.
		foreach ( $items as $var_id => $var_data ) {
			// Update values for item.
			if ( $were_reserved ) {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity_rsrv=%2$d, quantity_sold=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity_rsrv - $var_data['qua'],
						( $var_details[ $var_id ]->quantity_sold ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			} else {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity=%2$d, quantity_sold=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity - $var_data['qua'],
						( $var_details[ $var_id ]->quantity_sold ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			}
		}

		return true;
	}

	/**
	 * Returns items to 'stock' column.
	 *
	 * @param array $items     Array of items with quantities.
	 * @param bool  $were_sold Whether to move items from 'sold' (true), or 'reserve' (false) column.
	 *
	 * @return bool Whether the operation was successful.
	 */
	public static function return_items_to_stock( array $items, bool $were_sold = true ): bool {
		global $wpdb;
		// Get data about items from DB.
		$var_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1$i WHERE id IN (' . esc_sql( implode( ', ', array_keys( $items ) ) ) . ');', $wpdb->prefix . self::get_table_name() ), OBJECT_K );

		// Loop through the items to prepare request.
		foreach ( $items as $var_id => $var_data ) {
			// Update values for item.
			if ( $were_sold ) {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity_sold=%2$d, quantity=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity_sold - $var_data['qua'],
						( $var_details[ $var_id ]->quantity ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			} else {
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE %1$i SET quantity_rsrv=%2$d, quantity=%3$d WHERE id=%4$d; ',
						$wpdb->prefix . self::get_table_name(),
						$var_details[ $var_id ]->quantity_rsrv - $var_data['qua'],
						( $var_details[ $var_id ]->quantity ?? 0 ) + $var_data['qua'],
						$var_id
					)
				);
			}
		}

		return true;
	}

	/**
	 * Enqueues localization script with data about the currently viewed store item.
	 */
	public function enqueue_single_post_data(): void {
		// Return if user is not on single 'store-item' post.
		if ( ! is_single() || $this->get_slug() !== get_post_type() ) {
			return;
		}

		// Add post id to the localization lines.
		$localization_lines = [
			'post_id' => get_the_ID(),
		];

		// Search for the variation id in the GET parameters.
		$var_id = sanitize_text_field( wp_unslash( $_GET['var'] ?? '0' ) );

		// Check if the variation id is valid.
		$variations = array_map( static fn ( $var ) => $var['id'], self::get_store_item_data( $localization_lines['post_id'], $var_id )['variations']['data'] );
		if ( ! in_array( $var_id, $variations, true ) ) {
			$var_id = $variations[0];
		}
		$localization_lines['var_id'] = $var_id;

		// Enqueue archive preferences variables.
		wp_localize_script( 'estore_script', 'postPrefs', $localization_lines );
	}

	/**
	 * Registers custom metabox for item settings.
	 **/
	public function register_settings_metabox(): void {
		// Add 'preferences section' metabox.
		add_meta_box( 'store-item-settings', __( 'Налаштування Товару', 'estore-theme' ), [ $this, 'render_settings_metabox_content' ], $this->get_slug() );
		// Add 'variations section' metabox.
		add_meta_box( 'store-item-variations-settings', __( 'Налаштування Варіацій', 'estore-theme' ), [ $this, 'render_variations_settings_metabox_content' ], $this->get_slug() );
	}

	/**
	 * Displays content of the 'general settings' metabox.
	 *
	 * @param \WP_Post $post_obj post object.
	 */
	public function render_settings_metabox_content( \WP_Post $post_obj ): void {
		// Get metadata about product.
		$post_meta = array_filter( array_map( static fn ( $val ) => $val[0], get_post_meta( $post_obj->ID ) ), static fn ( $key ) => in_array( $key, [ 'estore-desc', 'estore-tag', 'estore-var-type' ] ), ARRAY_FILTER_USE_KEY );
		// Load the settings template.
		get_template_part( 'template-parts/admin/store-item/item', 'settings', $post_meta );
	}

	/**
	 * Displays content of the 'variations settings' metabox.
	 *
	 * @param \WP_Post $post_obj post object.
	 */
	public function render_variations_settings_metabox_content( \WP_Post $post_obj ): void {
		// Get data about variations.
		$var_data = self::get_variations_data( $post_obj->ID );
		// Load the variations' template.
		get_template_part( 'template-parts/admin/store-item/variation', 'section', [ 'vars' => $var_data ] );
	}

	/**
	 * Saves settings data for the 'store-item' posts.
	 *
	 * @param int      $post_id  ID of the saved post.
	 * @param \WP_Post $post_obj Object of the saved post.
	 * @param bool     $update   Whether the exising post was updated.
	 */
	public function save_settings_metabox_data( int $post_id, \WP_Post $post_obj, bool $update ): void {
		// Check whether the post is right post type.
		if ( $this->get_slug() !== $post_obj->post_type ) {
			return;
		}
		// Check the nonce to ensure data isn't coming from a weird source.
		if ( false === wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'update-post_' . $post_id ) ) {
			return;
		}

		// Save post description.
		update_post_meta( $post_id, 'estore-desc', wp_kses_post( wp_unslash( $_POST['item-desc'] ?? '' ) ) );
		// Save item tag.
		update_post_meta( $post_id, 'estore-tag', sanitize_text_field( wp_unslash( $_POST['item-tag'] ?? 'none' ) ) );
		// Save post variation display type.
		update_post_meta( $post_id, 'estore-var-type', sanitize_text_field( wp_unslash( $_POST['var-type'] ?? 'name' ) ) );
		// Save price of the first variant as the reference price.
		update_post_meta( $post_id, 'estore-default-price', empty( $_POST['var-price-sale'][0] ) ? sanitize_text_field( wp_unslash( $_POST['var-price'][0] ?? 0 ) ) : sanitize_text_field( wp_unslash( $_POST['var-price-sale'][0] ) ) );

		// Save variations.
		global $wpdb;

		$params = [
			'var-name'       => 'name',
			'var-color'      => 'colour',
			'var-img'        => 'attachments',
			'var-price'      => 'price',
			'var-price-sale' => 'price_sale',
			'var-quantity-available' => 'quantity',
		];

		// Sanitize values passed in the form.
		foreach ( $params as $param_key ) {
			// Skip parameter if it is empty.
			if ( empty( $_POST[ $param_key ] ) ) {
				continue;
			}
			// Sanitize the value.
			$_POST[ $param_key ] = rest_sanitize_array( wp_unslash( $_POST[ $param_key ] ) ); // phpcs:ignore
		}

		// Update all string values to include quotation marks.
		foreach ( [ 'var-name', 'var-color', 'var-img' ] as $string_val ) {
			// Each of these values should contain an array.
			if ( isset( $_POST[ $string_val ] ) && is_array( $_POST[ $string_val ] ) ) {
				$_POST[ $string_val ] = array_map( fn( $val ) => empty( $val ) ? '""' : '"' . $val . '"', $_POST[ $string_val ] ); // phpcs:ignore
			}
		}

		$db_requests = [];

		// Sanitize variation ids passed in the form.
		$var_ids = rest_sanitize_array( wp_unslash( $_POST['var-id'] ?? [] ) ); // phpcs:ignore

		// TODO: For some reason rest_sanitize_array(), rest_sanitize_object() etc calls triggers error with the WPCS. Investigate and fix later, this is not crucial atm.

		// Set up the SQL request to add item to the DB.
		$index = 0;
		foreach ( $var_ids as $var_id ) {
			if ( empty( $var_id ) ) {
				// phpcs:ignore
				$db_requests []= 'INSERT INTO ' . $wpdb->prefix . self::get_table_name() . '(' . implode( ', ', array_values( array_filter( $params, fn( $value, $key ) => ! empty( $_POST[ $key ][ $index ] ), ARRAY_FILTER_USE_BOTH ) ) ) . ', post_id, pos, status) VALUES (' . implode( ', ', array_map( fn ( $key ) => $_POST[ $key ][ $index ], array_filter( array_keys( $params ), fn( $key ) => ! empty( $_POST[ $key ][ $index ] ) ) ) ) . ', ' . $post_id . ', ' . $index . ', 1);';
			} else {
				// Create an array of updated variation values.
				$updated_data = $params;
				array_walk( $updated_data, static fn ( &$value, $key ) => $value .= '=' . ( '' === $_POST[ $key ][ $index ] ? 'NULL' : $_POST[ $key ][ $index ] ) ); // phpcs:ignore
				// phpcs:ignore
				$db_requests []= 'UPDATE ' . $wpdb->prefix . self::get_table_name() . ' SET ' . implode( ',', $updated_data ) . ', pos=' . $index . ' WHERE id=' . $var_id;
			}

			++$index;
		}

		// Check if the request actually contains anything after all other checks.
		if ( empty( $db_requests ) ) {
			return;
		}

		// Execute each request sequentially, since wpdb::query() cannot execute multiple request in one go.
		foreach ( $db_requests as $request ) {
			$wpdb->query( $request ); // phpcs:ignore
		}
	}

	/**
	 * Creates a control template for a single variation of the estore-item
	 *
	 * @return array Request response.
	 */
	public function get_single_variation_template(): array {
		return [
			'status' => 200,
			'html'   => Helpers::render_template_to_string( 'template-parts/admin/store-item/variation', 'settings' ),
		];
	}

	/**
	 * Creates a control template for a single post of store-item type
	 *
	 * @param \WP_REST_Request $request Request parameters.
	 *
	 * @return array Request response.
	 */
	public function get_single_post_controls_template( \WP_REST_Request $request ): array {
		// Get the post id.
		$post_id = (int) sanitize_text_field( $request->get_param( 'post_id' ) );

		// Get data about post id.
		$post_data = self::get_store_item_data( $post_id );

		return [
			'status'  => 200,
			'success' => true,
			'html'    => Helpers::render_template_to_string( 'template-parts/admin/store-item/post', 'controls', [ 'post_data' => $post_data ] ),
		];
	}

	/**
	 * Registers rest routes that must be available exclusively to admin screen.
	 */
	public function admin_rest_routes(): void {
		// Register endpoint to retrieve controls for a single variation.
		register_rest_route(
			$this->rest_namespace,
			'get-var-ctrl',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'get_single_variation_template' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [],
				],
			]
		);

		// Register endpoint to retrieve controls for a single store item.
		register_rest_route(
			$this->rest_namespace,
			'get-post-ctrl',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'get_single_post_controls_template' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'post_id' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
	}

	/**
	 * Removes outdated transients with related terms.
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      An array of object term IDs or slugs.
	 * @param array  $tt_ids     An array of term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms to the old terms.
	 * @param array  $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function remove_outdated_archive_related_transients( int $object_id, array $terms, array $tt_ids, string $taxonomy, bool $append, array $old_tt_ids ): void {
		if ( $this->get_slug() !== get_post_type( $object_id ) ) {
			return;
		}

		foreach ( [ 0, ...$old_tt_ids ] as $term_id ) {
			delete_transient( TaxonomyController::get_term_related_transient_name( $term_id ) );
		}
	}

	/**
	 * Saves data about related term in transients.
	 *
	 * @param int $post_id ID of the post.
	 */
	public function update_archive_related_transients( int $post_id ): void {
		// Check if the post is right post type.
		if ( get_post_type( $post_id ) !== $this->get_slug() ) {
			return;
		}

		foreach ( get_post_taxonomies( $post_id ) as $tax ) {
			foreach ( wp_get_post_terms( $post_id, $tax ) as $term ) {
				TaxonomyController::get_term_related_terms( $term->term_id, $tax );
			}
		}
	}

	/**
	 * Returns data about variation from the DB.
	 *
	 * @param int $var_id ID of a variation.
	 *
	 * @return array Array of data.
	 */
	public static function get_single_variation_data( int $var_id ): array {
		// Try retrieving data from the object cache.
		$var_details = wp_cache_get( 'var_data_single_' . $var_id );
		// Check if the cached data is an array, since it's possible to have an empty array saved.
		if ( is_array( $var_details ) ) {
			return $var_details;
		}
		// Request data from the table.
		global $wpdb;
		$var_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1$i WHERE id=%2$d;', $wpdb->prefix . self::get_table_name(), $var_id ), ARRAY_A )[0];
		// Set object cache with newly received data.
		wp_cache_set( 'var_data_single_' . $var_id, $var_details );
		// Return the details.
		return $var_details;
	}

	/**
	 * Retrieves data about the variations for a post.
	 *
	 * @param int $post_id ID of the 'store-item' type post.
	 *
	 * @return array
	 */
	public static function get_variations_data( int $post_id ): array {
		// Try retrieving data from the object cache.
		$var_details = wp_cache_get( 'var_data_' . $post_id );
		// Check if the cached data is an array, since it's possible to have an empty array saved.
		if ( is_array( $var_details ) ) {
			return $var_details;
		}
		// Request data from the table.
		global $wpdb;
		$var_details = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1$i WHERE post_id=%2$d;', $wpdb->prefix . self::get_table_name(), $post_id ), ARRAY_A );
		// Set object cache with newly received data.
		wp_cache_set( 'var_data_' . $post_id, $var_details );
		// Return the details.
		return $var_details;
	}

	/**
	 * Retrieves data about store item.
	 *
	 * @param ?int $post_id    ID of the post. If not provided, queried post is utilized.
	 * @param int  $var_id     Variation ID which should be used as default (default: 0, defaults to the first variation).
	 * @param bool $single_var Whether to load only active variation. (default: false).
	 *
	 * @return array Array with the post data.
	 */
	public static function get_store_item_data( ?int $post_id = null, int $var_id = 0, bool $single_var = false ): array {
		// Get the id of the post if the post id was not provided as an argument.
		$__curr_id = $post_id ?? get_the_ID();

		// Attempt to retrieve cached data.
		$args = wp_cache_get( 'post_data_' . $__curr_id . ( $single_var ? '_var_' . $var_id : '' ) );

		// If the cached data for the post is empty, retrieve it from DB.
		if ( empty( $args ) ) {
			// Set up initial data about the post.
			$args = [
				'id'      => $__curr_id,
				'title'   => get_the_title( $post_id ),
				'link'    => get_the_permalink( $__curr_id ),
				'excerpt' => get_the_excerpt( $post_id ),
			];
			// Retrieve post meta for additional fields.
			$post_meta = get_post_meta( $__curr_id );
			// Insert post meta values into the post data array.
			$args['desc'] = $post_meta['estore-desc'];
			$args['tag']  = $post_meta['estore-tag'];
			// Retrieve data about the variations.
			$var_data = self::get_variations_data( $__curr_id );
			// Sort variations if there are more than one present. Push unavailable ones to the end.
			usort( $var_data, static fn ( $var_1, $var_2 ) => (int) $var_1['pos'] - (int) $var_2['pos'] - ( $var_1['quantity'] > 0 ? 0 : -10000 ) );
			// Variation index in the array.
			$var_index = 0;
			// Use data about first variation to set post data.
			if ( empty( $var_data[0]['attachments'] ) ) {
				$args['thumbnails'] = [ Helpers::get_theme_image_path( 'no-image.png' ) ];
			} else {
				// Get variation index.
				$var_index = array_search( (string) $var_id, array_column( $var_data, 'id', null ), true );
				// Create an array of thumbnails for the store item.
				$args['thumbnails'] = array_map( static fn ( $id ) => wp_get_attachment_image_url( (int) $id, 'thumbnail' ), explode( ',', $var_data[ $var_index ]['attachments'] ) );
			}
			// Set initial data about the variations.
			$args['variations'] = [
				'type' => $post_meta['estore-var-type'],
				'data' => [],
			];

			if ( $single_var ) {
				$args['variations']['data'] = [
					'id'          => $var_data[ $var_index ]['id'],
					'name'        => $var_data[ $var_index ]['name'],
					'colour'      => $var_data[ $var_index ]['colour'],
					'attachments' => explode( ',', $var_data[ $var_index ]['attachments'] ),
				];
			} else {
				// Go through each variation to save data.
				foreach ( $var_data as $var ) {
					// Set initial data about variation.
					$var_data = [
						'id'          => $var['id'],
						'name'        => $var['name'],
						'colour'      => $var['colour'],
						'quantity'    => $var['quantity'],
						'price'       => $var['price'],
					];
					// Check if the variation attachments exist.
					if ( ! empty( $var['attachments'] ) ) {
						$var_data['attachments'] = explode( ',', $var['attachments'] );
					}
					// Push data about price on sale only if it's not empty to avoid additional checks in the template.
					if ( ! empty( $var['price_sale'] ) ) {
						$var_data['price_sale'] = $var['price_sale'];
					}
					// Push data about the variation.
					$args['variations']['data'][] = $var_data;
				}
			}
			// Save data in the object cache.
			wp_cache_set( 'post_data_' . $__curr_id . ( $single_var ? '_var_' . $var_id : '' ), $args );
		}

		// Return the data about the post.
		return $args;
	}

	/**
	 * Loads single page for this post type.
	 */
	public function load_single_page(): void {
		// Get data about current variation.
		$var_id = (int) sanitize_text_field( wp_unslash( $_GET['var'] ?? '0' ) );

		// Get data about the store item.
		$post_data = self::get_store_item_data( var_id: $var_id );

		// Check whether this variation exists for this item.
		if ( ! in_array( $var_id, array_map( static fn ( $val ) => (int) $val['id'], $post_data['variations']['data'] ), true ) ) {
			// Set variation id to the first available if it doesn't match any of the existing ones.
			$var_id = (int) $post_data['variations']['data'][0]['id'];
		}
		// Set default variation index to 0.
		$var_index = 0;

		// Get images for each of the variations.
		array_walk(
			$post_data['variations']['data'],
			static function ( &$value, $key ) use ( $var_id, &$var_index ) {
				if ( ! empty( $value['attachments'] ) ) {
					$value['attachments'] = array_map( static fn( $id ) => wp_get_attachment_image_url( (int) $id, 'full' ), $value['attachments'] );
				} else {
					$value['attachments'] = [ Helpers::get_theme_image_path( 'no-image.png' ) ];
				}

				if ( (int) $value['id'] === $var_id ) {
					$var_index = $key;
				}
			}
		);

		// Set up arguments array to pass into template.
		$args = [
			'var_id'    => $var_id,
			'var_index' => $var_index,
			'post_data' => $post_data,
			'sub_pages' => [
				'main' => __( 'Про Товар', 'estore-theme' ),
				'desc' => __( 'Деталі', 'estore-theme' ),
			],
		];

		get_template_part( 'template-parts/posts/' . $this->get_slug(), args: $args );
	}

	#endregion.
}
