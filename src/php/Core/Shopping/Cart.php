<?php
/**
 * Shopping Cart controller class file
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Shopping;

use EStore\Ext\Helpers;
use EStore\PostTypes\StoreItem;
use EStore\ThemeController;

/**
 * Shopping Cart controller class
 */
class Cart {

	#region Private Fields.

	/**
	 * REST API Namespace.
	 * @var string $api_namespace
	 */
	private string $api_namespace;

	#endregion


	#region Initialization Methods.

	/**
	 * Initialization method for class.
	 */
	public function init(): void {
		$this->api_namespace = 'estore/cart';

		$this->hooks();
	}

	/**
	 * Hook initialization method for class.
	 */
	protected function hooks(): void {
		// Create rest routes for shopping cart related action.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	#endregion


	#region Private Methods.

	/**
	 * Retrieves and parses data about products inside the user's shopping cart.
	 *
	 * @return array
	 */
	private static function get_user_cart_products(): array {
		// Check if user is logged in.
		$user_id = get_current_user_id();

		// Prepare variable for cart details.
		$user_cart = [];
		// If the user is logged in check the value in meta.
		if ( $user_id > 0 ) {
			$user_cart = get_user_meta( $user_id, 'user_cart', true );
			// Make a check to ensure that the cart is assigned an array and not default empty string.
			if ( ! is_array( $user_cart ) ) {
				$user_cart = [];
			}
		}
		// Check the value in the cookies if the user isn't logged in or their cart is empty (if they registered after adding items to cart).
		if ( empty( $user_cart ) ) {
			if ( ! empty( $_COOKIE['cart'] ) ) {
				try {
					$user_cart = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['cart'] ) ), true, 512, JSON_THROW_ON_ERROR );
				} catch ( \JsonException ) {
					$user_cart = [];
				}
			}

			// Remove the cart data from cookies and move it to user meta.
			if ( $user_id > 0 || ! empty( $user_cart ) ) {
				if ( ! headers_sent() ) {
					// Remove cookie.
					setcookie( 'cart', '' );
				}
				// Update user meta.
				update_user_meta( $user_id, 'user_cart', $user_cart );
			}
		}

		array_walk(
			$user_cart,
			static function ( &$value, $key ) {
				$post_data = StoreItem::get_store_item_data( $value['post_id'] );

				// Variation index in the post_data array.
				$var_index = array_search( $key, array_map( static fn( $var ) => (int) $var['id'], $post_data['variations']['data'] ), true );

				// Create a name of the product.
				$name = $post_data['title'];
				// If there are more than 1 variation, add variation name.
				if ( count( $post_data['variations']['data'] ) > 1 ) {
					$name .= ' — ' . $post_data['variations']['data'][ $var_index ]['name'];
				}

				// Create a thumbnail url for the product.
				$thumbnail_url = '';
				// Check whether the image of the variation exists.
				if ( ! empty( $post_data['variations']['data'][ $var_index ]['attachments'][0] ) ) {
					$thumbnail_url = wp_get_attachment_image_url( $post_data['variations']['data'][ $var_index ]['attachments'][0] );
				} else {
					$thumbnail_url = Helpers::get_theme_image_path( 'no-image.png' );
				}

				$value = [
					'var_id'     => $key,
					'post_id'    => $post_data['id'],
					'name'       => $name,
					'thumbnail'  => $thumbnail_url,
					'price'      => $post_data['variations']['data'][ $var_index ]['price'],
					'price_sale' => $post_data['variations']['data'][ $var_index ]['price_sale'] ?? null,
					'number'    => $value['qua'],
				];
			}
		);

		return $user_cart;
	}

	/**
	 * Retrieve user cart from memory or cookies.
	 *
	 * @param int $user_id ID of a user.
	 *
	 * @return array cart content.
	 */
	private function get_user_cart( int $user_id ): array {
		// Get items in users cart.
		$user_cart = [];
		// If the user is logged in check the value in meta.
		if ( $user_id > 0 ) {
			$user_cart = get_user_meta( $user_id, 'user_cart', true );
			// Make a check to ensure that the cart is assigned an array and not default empty string.
			if ( ! is_array( $user_cart ) ) {
				$user_cart = [];
			}
		}
		// Check the value in the cookies if the user isn't logged in or their cart is empty (if they registered after adding items to cart).
		if ( empty( $user_cart ) && ! empty( $_COOKIE['cart'] ) ) {
			try {
				$user_cart = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['cart'] ) ), true, 512, JSON_THROW_ON_ERROR );
			} catch ( \JsonException ) {
				$user_cart = [];
			}
		}

		return $user_cart;
	}

	/**
	 * Adds an item to cart
	 *
	 * @param int       $var_id       Product variation id.
	 * @param int       $number       Number of items that should be added.
	 * @param ?int      $total_number Total number of the items after adding new ones to user's cart.
	 * @param ?int      $post_id      ID of the product variation belongs to.
	 * @param ?callable $custom_check Additional checks before saving cart content.
	 *
	 * @return bool Whether operation was successful.
	 */
	private function add_item( int $var_id, int $number, ?int &$total_number = null, int $post_id = null, ?callable $custom_check = null ): bool {
		// Check if the user is logged in to determine whether to save in cookies or in meta.
		$user_id = get_current_user_id();
		// Get items in users cart.
		$user_cart = $this->get_user_cart( $user_id );

		// Check whether this item is already present in the cart.
		if ( array_key_exists( $var_id, $user_cart ) ) {
			$user_cart[ $var_id ]['qua'] += $number;
		} else {
			// Return if the post id is unset.
			if ( ! isset( $post_id ) ) {
				return false;
			}

			$user_cart[ $var_id ] = [
				'post_id' => $post_id,
				'qua'     => $number,
			];
		}

		// Optionally do custom check, this is useful to check whether the ordered number exceeds number of item in stock.
		if ( isset( $custom_check ) && ! $custom_check( $var_id, $post_id, $number, $user_cart ) ) {
			return false;
		}

		// Update value passed by reference.
		if ( isset( $total_number ) ) {
			$total_number = $user_cart[ $var_id ]['qua'];
		}

		// Save the values in the cart.
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, 'user_cart', $user_cart );
		} else {
			setcookie( 'cart', wp_json_encode( $user_cart, JSON_THROW_ON_ERROR ) );
		}

		return true;
	}

	/**
	 * Removes an item from cart
	 *
	 * @param int       $var_id       Product variation id.
	 * @param int       $number       Number of items that should be added, 0 (default) - remove all.
	 * @param ?int      $total_number Total number of the items after adding new ones to user's cart.
	 * @param int       $post_id      ID of the product variation belongs to.
	 * @param ?callable $custom_check Additional checks before saving cart content.
	 *
	 * @return bool Whether operation was successful.
	 */
	private function remove_item( int $var_id, int $number = 0, ?int &$total_number = null, int $post_id = 0, ?callable $custom_check = null ): bool {
		// Check if the user is logged in to determine whether to save in cookies or in meta.
		$user_id = get_current_user_id();
		// Get items in users cart.
		$user_cart = $this->get_user_cart( $user_id );

		// Check whether the decremented number is larger than number of items in cart.
		if ( $user_cart[ $var_id ]['qua'] < $number ) {
			return false;
		}

		// Check whether this item is already present in the cart.
		if ( array_key_exists( $var_id, $user_cart ) ) {
			if ( $number > 0 ) {
				$user_cart[ $var_id ]['qua'] -= $number;
			} else {
				$user_cart[ $var_id ]['qua'] = $number;
			}
		} else {
			return false;
		}

		// Check whether the number is equal to zero to remove item completely.
		if ( $user_cart[ $var_id ]['qua'] < 1 ) {
			unset( $user_cart[ $var_id ] );
		}

		// Optionally do custom check, this is useful to check whether the ordered number exceeds number of item in stock.
		if ( isset( $custom_check ) && ! $custom_check( $var_id, $post_id, $number, $user_cart ) ) {
			return false;
		}

		// Update value passed by reference.
		if ( isset( $total_number ) ) {
			$total_number = $user_cart[ $var_id ]['qua'];
		}

		// Save the values in the cart.
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, 'user_cart', $user_cart );
		} else {
			setcookie( 'cart', wp_json_encode( $user_cart, JSON_THROW_ON_ERROR ) );
		}

		return true;
	}

	/**
	 * Returns requested total price of the items in the cart.
	 *
	 * @param ?array $products array of products.
	 *
	 * @returns array total price of the items in user's cart.
	 */
	private static function get_cart_price( ?array $products = null ): int {
		// Get the products if an array was not passed as an argument.
		if ( ! isset( $products ) ) {
			$products = self::get_user_cart_products();
		}

		// Calculate total price of the products in the cart.
		$total_price = 0;
		// Manually add (price * number) to the total price variable.
		foreach ( $products as $product ) {
			$total_price += ( $product['price_sale'] ?? $product['price'] ) * $product['number'];
		}

		return $total_price;
	}

	#endregion


	#region Public Methods.

	/**
	 * Adds an item to cart based on request parameters.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array Request response.
	 */
	public function add_item_to_cart( \WP_REST_Request $request ): array {
		// Get parameters passed with request.
		$params = $request->get_params();
		// Get all parameters.
		$post_id = (int) sanitize_text_field( $params['post_id'] );
		$var_id  = (int) sanitize_text_field( $params['var_id'] );
		$number  = (int) sanitize_text_field( $params['number'] ?? '1' );

		// Check whether the post with the id exists.
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Такого товару не існує', 'estore-theme' ),
			];
		}

		// Get data about post.
		$post_data = StoreItem::get_store_item_data( $post_id );
		// Check whether the variation id is valid for this post.
		$post_variations = array_map( static fn( $var ) => (int) $var['id'], $post_data['variations']['data'] );
		if ( ! in_array( $var_id, $post_variations, true ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Такої варіації товару не існує', 'estore-theme' ),
			];
		}
		// Get data about variations.
		$var_data = $post_data['variations']['data'][ array_search( $var_id, $post_variations, true ) ];
		// Check whether this variation is in stock.
		if ( $var_data['quantity'] < $number ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => $number > 1 ? __( 'Цього товару немає на складі в такій кількості', 'estore-theme' ) : __( 'Цього товару вже немає на складі', 'estore-theme' ),
			];
		}

		$total_items_in_cart = 0;

		// Update user cart in the memory.
		$added_successfully = $this->add_item(
			$var_id,
			$number,
			$total_items_in_cart,
			$post_id,
			static function ( $var_id, $post_id, $number, $user_cart ) use ( $var_data ) {
				return $user_cart[ $var_id ]['qua'] <= $var_data['quantity'];
			}
		);

		if ( ! $added_successfully ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Цього товару немає на складі в такій кількості', 'estore-theme' ),
			];
		}

		$response = [
			'status'  => 200,
			'success' => true,
		];

		if ( rest_sanitize_boolean( $params['render'] ) ) {
			$response['html'] = Helpers::render_template_to_string(
				'template-parts/cart/item',
				args: [
					'var_id'    => $var_id,
					'var_data'  => $var_data,
					'post_data' => $post_data,
					'number'    => $total_items_in_cart,
				]
			);
		}

		return $response;
	}

	/**
	 * Increases number of the products in cart based on passed variation id by one.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array Request response.
	 */
	public function add_single_item_to_cart( \WP_REST_Request $request ): array {
		// Get variation id from request parameters.
		$var_id = (int) sanitize_text_field( $request->get_param( 'var_id' ) );

		// Get variation data.
		$var_data = StoreItem::get_single_variation_data( $var_id );
		// Check if the quantity is in stock.
		$closure = static function ( $var_id, $post_id, $number, $user_cart ) use ( $var_data ) {
			return $user_cart[ $var_id ]['qua'] <= $var_data['quantity'];
		};
		// Try adding items to the cart.
		$op_success = $this->add_item( var_id: $var_id, number: 1, custom_check: $closure );

		// Add item to cart.
		if ( ! $op_success ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Цього товару немає на складі в такій кількості', 'estore-theme' ),
			];
		}

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Removes an item from cart based on request parameters.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array Request response.
	 */
	public function remove_item_from_cart( \WP_REST_Request $request ): array {
		// Get variation id from request parameters.
		$var_id = (int) sanitize_text_field( $request->get_param( 'var_id' ) );

		// Remove single item from cart.
		if ( ! $this->remove_item( $var_id ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Невідома помилка', 'estore-theme' ),
			];
		}

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Decreases number of the products in cart based on passed variation id by one.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array Request response.
	 */
	public function remove_single_item_from_cart( \WP_REST_Request $request ): array {
		// Get variation id from request parameters.
		$var_id = (int) sanitize_text_field( $request->get_param( 'var_id' ) );

		// Remove single item from cart.
		if ( ! $this->remove_item( $var_id, 1 ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Такого товару намає в кошику', 'estore-theme' ),
			];
		}

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Renders cart content for user.
	 *
	 * @return array Request response.
	 */
	public function render_cart_for_user(): array {
		return [
			'status' => 200,
			'html'   => Helpers::render_template_to_string( 'template-parts/cart/layout', args: [ 'products' => self::get_user_cart_products() ] ),
		];
	}

	/**
	 * Registers REST API endpoint routes for shopping cart related actions.
	 */
	public function register_rest_routes(): void {

		register_rest_route(
			$this->api_namespace,
			'get-cart-price',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => function (): array {
						return [
							'status'  => 200,
							'price'   => self::get_cart_price(),
						];
					},
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'add-item',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'add_item_to_cart' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'post_id' => [
							'type'     => 'string',
							'required' => true,
						],
						'var_id' => [
							'type'     => 'string',
							'required' => true,
						],
						'number' => [
							'type'     => 'string',
							'required' => false,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'remove-item',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'remove_item_from_cart' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'var_id' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'add-single',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'add_single_item_to_cart' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'var_id' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'remove-single',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'remove_single_item_from_cart' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'var_id' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'render-cart',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'render_cart_for_user' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [],
				],
			]
		);
	}

	/**
	 * Verifies whether the content of the user's cart is already in stock
	 *
	 * @return bool|array true if items are available, array if there's issue with some of the items.
	 */
	public static function verify_cart_content_availability(): bool|array {
		// Get items in the cart.
		$user_cart = get_user_meta( get_current_user_id(), 'user_cart', true );

		$errors = [];

		foreach ( $user_cart as $var_id => $item_data ) {
			// Get variation data.
			$var_list = StoreItem::get_variations_data( $item_data['post_id'] );
			// Get variation index of the item.
			$var_index = array_search( (string) $var_id, array_column( $var_list, 'id' ), true );

			// Check if the number of items in the cart exceeds number in stock.
			if ( $item_data['qua'] > $var_list[ $var_index ]['quantity'] ) {

				if ( $item_data['qua'] < 2 ) {
					$errors[ $var_id ] = __( 'На складі вже відсутній це товар', 'estore-theme' );
				} else {
					$errors[ $var_id ] = __( 'На складі відсутня така кількість товару', 'estore-theme' );
				}
			}
		}

		// Send the errors if any were found.
		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Loads cart template width content.
	 */
	public static function load_cart_page(): void {
		// Get the products.
		$products = self::get_user_cart_products();

		// Calculate total price of the products in the cart.
		$total_price = self::get_cart_price( $products );

		// Check whether items in the cart are available.
		$errors = self::verify_cart_content_availability();

		// Create array with arguments.
		$args = [
			'cart' => [
				'load_cart_items' => function () use ( $errors, $products ) {
					get_template_part(
						slug: 'template-parts/cart/layout',
						args: [
							'products' => $products,
							'errors'   => is_array( $errors ) ? $errors : [],
						]
					);
				},
				'total'           => $total_price,
			],
			'user'     => [],
			'delivery' => [],
			'payment'  => [],
		];

		// Get user WP_User object.
		$user_obj = wp_get_current_user();
		// Bail if the user doesn't exist.
		if ( ! isset( $user_obj ) ) {
			return;
		}

		// Get user meta.
		$user_meta = get_user_meta( $user_obj->ID, single: true );
		// Add data to the arguments.
		$args['user'] = [
			'first-name'  => isset( $user_meta['first-name'] ) ? $user_meta['first-name'][0] : '',
			'last-name'   => isset( $user_meta['last-name'] ) ? $user_meta['last-name'][0] : '',
			'middle-name' => isset( $user_meta['middle-name'] ) ? $user_meta['middle-name'][0] : '',
			'phone'       => isset( $user_meta['phone'] ) ? $user_meta['phone'][0] : '',
			'email'       => $user_obj->user_email,
		];

		// TODO: Pass saved data about delivery.

		// TODO: Pass saved data about payment.

		// Load template.
		get_template_part( 'template-parts/pages/cart', args: $args );
	}

	#endregion
}
