<?php
/**
 * Order class template
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\PostTypes;

use EStore\Core\Emails\EmailController;
use EStore\Core\Shopping\Cart;
use EStore\Enum\OrderStatus;

/**
 * EStore Order post type class
 */
class Order extends GenericPostType {

	#region Private Fields.

	/**
	 * REST API namespace of the checkout component.
	 * @var string $api_namespace
	 */
	private string $api_namespace;

	#endregion


	#region Initializing Methods.

	/**
	 * Class initialization fuction.
	 */
	public function init(): void {
		// Set Post Type Slug.
		$this->set_slug( 'user-order' );
		// Set Naming.
		$this->set_name( __( 'Замовлення', 'estore-theme' ) );
		$this->set_singular_name( __( 'Замовлення', 'estore-theme' ) );
		$this->set_plural_name( __( 'Замовлення', 'estore-theme' ) );
		// Set Description.
		$this->set_description( __( 'Замовлення користвачів на товари', 'estore-theme' ) );
		// Set icon for post type.
		$this->set_menu_icon( 'dashicons-cart' );

		// Set labels.
		$this->set_labels(
			[
				'name'                     => $this->get_plural_name(),
				'singular_name'            => $this->get_singular_name(),
				'add_new'                  => __( 'Додати Замовлення', 'svitmov' ),
				'add_new_item'             => __( 'Додати Новий Замовлення', 'svitmov' ),
				'edit_item'                => __( 'Редагувати Замовлення', 'svitmov' ),
				'new_item'                 => __( 'Новий Замовлення', 'svitmov' ),
				'view_item'                => __( 'Переглянути Замовлення', 'svitmov' ),
				'view_items'               => __( 'Переглянути Замовлення', 'svitmov' ),
				'search_items'             => __( 'Шукати Замовлення', 'svitmov' ),
				'not_found'                => __( 'Не Знайдено Замовлення', 'svitmov' ),
				'not_found_in_trash'       => __( 'Не Знайдено Замовлення в Смітнику', 'svitmov' ),
				'all_items'                => __( 'Усі Замовлення', 'svitmov' ),
				'archives'                 => __( 'Архів Замовлення', 'svitmov' ),
				'attributes'               => __( 'Атрибути Замовлення', 'svitmov' ),
				'insert_into_item'         => __( 'Додати до Замовлення', 'svitmov' ),
				'insert_into_this_item'    => __( 'Додати до Цього Замовлення', 'svitmov' ),
				'featured_image'           => __( 'Зображення Замовлення', 'svitmov' ),
				'set_featured_image'       => __( 'Встановити Зображення Замовлення', 'svitmov' ),
				'remove_featured_image'    => __( 'Прибрати Зображення Замовлення', 'svitmov' ),
				'menu_name'                => __( 'Замовлення', 'svitmov' ),
				'filter_items_list'        => __( 'Фільтрувати Замовлення', 'svitmov' ),
				'filter_by_date'           => __( 'Фільтрувати Замовлення за Датою', 'svitmov' ),
				'items_list_navigation'    => __( 'Навігація по Списку Замовлення', 'svitmov' ),
				'items_list'               => __( 'Список Замовлення', 'svitmov' ),
				'item_published'           => __( 'Замовлення Опубліковано', 'svitmov' ),
				'item_published_privately' => __( 'Замовлення Опубліковано Приватним Записом', 'svitmov' ),
				'item_reverted_to_draft'   => __( 'Замовлення Повернено до Чернеток', 'svitmov' ),
				'item_trashed'             => __( 'Замовлення Переміщено до Смітника', 'svitmov' ),
				'item_updated'             => __( 'Замовлення Оновлено', 'svitmov' ),
				'item_link'                => __( 'Посилання на Замовлення', 'svitmov' ),
				'item_link_description'    => __( 'Опис Посилання на Замовлення', 'svitmov' ),
			]
		);

		// Set supported features.
		$this->supports = [ 'title' ];

		// Set namespace for endpoints.
		$this->api_namespace = 'estore/order';

		// Proceed with default initialization.
		parent::init();

		// Initialize hooks.
		$this->hooks();
	}

	/**
	 * Hooks initialization class method.
	 */
	protected function hooks(): void {
		// Registers metabox with the item settings.
		add_action( 'add_meta_boxes', [ $this, 'register_settings_metabox' ], 10, 1 );

		// Add saving functionality for the metabox.
		add_action( 'save_post', [ $this, 'save_settings_metabox_data' ], 15, 3 );

		// Add post list order state column.
		add_filter( 'manage_' . $this->get_slug() . '_posts_columns', [ $this, 'add_custom_admin_column' ] );
		// Display post status in the column.
		add_action( 'manage_' . $this->get_slug() . '_posts_custom_column', [ $this, 'add_order_status_in_column' ], 10, 2 );

		// Register endpoints.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	#endregion


	#region Public Methods.

	/**
	 * Registers custom metabox for item settings.
	 **/
	public function register_settings_metabox(): void {
		// Add 'preferences section' metabox.
		add_meta_box( 'sale-item-settings', __( 'Статус Замовлення', 'estore-theme' ), [ $this, 'render_settings_metabox_content' ], $this->get_slug() );
	}

	/**
	 * Displays content of the 'general settings' metabox.
	 *
	 * @param \WP_Post $post_obj post object.
	 */
	public function render_settings_metabox_content( \WP_Post $post_obj ): void {
		// Get post meta.
		$post_meta = get_post_meta( $post_obj->ID, single: true );

		// Create an argument array to pass into the template.
		$template_args = [
			'details'     => [
				'status' => isset( $post_meta['order-status'] ) ? $post_meta['order-status'][0] : OrderStatus::undefined->value,
			],
			'status_list' => [],
		];

		// Create list of available statuses.
		foreach ( OrderStatus::cases() as $status ) {
			$template_args['status_list'][ $status->value ] = OrderStatus::get_order_status_title( $status );
		}

		// Add user details about order.
		if ( isset( $post_meta['order-user'] ) ) {
			$template_args['details']['user'] = maybe_unserialize( $post_meta['order-user'][0] );
		}

		// Add delivery details about order.
		if ( isset( $post_meta['order-delivery'] ) ) {
			$template_args['details']['delivery'] = maybe_unserialize( $post_meta['order-delivery'][0] );
		}

		// Add delivery details about order.
		if ( isset( $post_meta['order-item'] ) ) {
			// Get list of items from the meta.
			$items = maybe_unserialize( $post_meta['order-item'][0] );
			// Prepare array with the items.
			$template_args['details']['items'] = [];
			foreach ( $items as $var_id => $data ) {
				// Add single item details.
				$template_args['details']['items'][] = get_the_title( $data['post_id'] ) . ' — ' . StoreItem::get_single_variation_data( $var_id )['name'] . ' x' . $data['qua'];
			}
		}

		// Load the settings template.
		get_template_part( 'template-parts/admin/order/item', 'settings', $template_args );
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

		// Get previous status form meta.
		$prev_status = get_post_meta( $post_id, 'order-status', true );
		// Get new status from POST data.
		$new_status = sanitize_text_field( wp_unslash( $_POST['order-status'] ?? (string) OrderStatus::undefined->value ) );

		// Check if the status has changed.
		if ( $prev_status === $new_status ) {
			return;
		}

		$prev_status = OrderStatus::tryFrom( (int) $prev_status );
		if ( ! isset( $prev_status ) ) {
			$prev_status = OrderStatus::undefined;
		}
		$new_status  = OrderStatus::tryFrom( (int) $new_status );

		// Get ordered items.
		$order_items = get_post_meta( $post_id, 'order-item', true );

		// Update post meta .
		update_post_meta( $post_id, 'order-status', $new_status->value );

		// If the new status is 'cancelled' return items to stock.
		if ( in_array( $new_status, [ OrderStatus::cancelled_by_admin, OrderStatus::cancelled_by_user ], true ) ) {
			switch ( $prev_status ) {
				// Continue if changed 'cancelled' type.
				case OrderStatus::cancelled_by_admin:
				case OrderStatus::cancelled_by_user:
					break;
				// If previously order was complete switch return to stock.
				case OrderStatus::order_complete:
					StoreItem::return_items_to_stock( $order_items, were_sold: true );
					break;
				default:
					StoreItem::return_items_to_stock( $order_items, were_sold: false );
			}

			return;
		}

		// If the new status is 'complete' push items to 'sold' column.
		if ( OrderStatus::order_complete === $new_status ) {
			switch ( $prev_status ) {
				// Continue if changed 'cancelled' type.
				case OrderStatus::cancelled_by_admin:
				case OrderStatus::cancelled_by_user:
					StoreItem::push_items_to_sold( $order_items, were_reserved: false );
					break;
				default:
					StoreItem::push_items_to_sold( $order_items, were_reserved: true );
			}

			return;
		}

		// If the old status is 'cancelled' move items to 'reserve' column form stock.
		if ( in_array( $prev_status, [ OrderStatus::cancelled_by_admin, OrderStatus::cancelled_by_user ], true ) ) {
			StoreItem::move_items_to_reserve( $order_items, from_stock: true );
		}

		// if the old status is 'complete' move items to 'reserve' column from 'sold'.
		if ( OrderStatus::order_complete === $prev_status ) {
			StoreItem::move_items_to_reserve( $order_items, from_stock: false );
		}
	}

	/**
	 * Adds custom 'order status' column for the edit.php page
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array modified array of columns.
	 */
	public function add_custom_admin_column( array $columns ): array {
		// Add status columns.
		$columns['status'] = __( 'Статус Замовлення', 'estore-theme' );

		return $columns;
	}

	/**
	 * Displays content in the 'order status' column in the edit.php page
	 *
	 * @param string $column  Name of the column.
	 * @param int    $post_id ID of the post.
	 */
	public function add_order_status_in_column( string $column, int $post_id ): void {
		// Return if the column is not 'status'.
		if ( 'status' !== $column ) {
			return;
		}

		// Retrieve status for the DB.
		$curr_status = OrderStatus::tryFrom( (int) get_post_meta( $post_id, 'order-status', true ) );
		// Assign default status if meta value is empty.
		if ( ! isset( $curr_status ) ) {
			$curr_status = OrderStatus::undefined;
		}

		// Echo the status title.
		?>
		<span class="status-<?php echo esc_attr( $curr_status->name ); ?>">
			<?php echo esc_html( OrderStatus::get_order_status_title( $curr_status ) ); ?>
		</span>
		<?php
	}


	/**
	 * Processes the order based on user cart and request parameters.
	 *
	 * @param \WP_REST_Request $request Request parameters.
	 *
	 * @return array Request response.
	 */
	public function create_order( \WP_REST_Request $request ): array {
		// Get request parameters.
		$params = $request->get_params();
		// Get current user id.
		$user_id = get_current_user_id();

		// Get user cart data.
		$user_cart = get_user_meta( $user_id, 'user_cart', true );

		// Verify cart content.
		$in_stock = Cart::verify_cart_content_availability();

		// Check whether there are any issues with items availability in stock.
		if ( is_array( $in_stock ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'errors'  => $in_stock,
			];
		}

		// Insert new order post.
		$order_id = wp_insert_post(
			[
				'post_title'  => 'Замовлення від ' . ( new \DateTime( 'now' ) )->format( 'H:i d-m-y' ),
				'post_status' => 'publish',
				'post_type'   => $this->get_slug(),
				'meta_input'  => [
					'order-status' => OrderStatus::payment_pending->value,
					'order-item'   => $user_cart,
					'order-user'   => [
						'first_name'  => sanitize_text_field( $params['first_name'] ),
						'last_name'   => sanitize_text_field( $params['last_name'] ),
						'middle_name' => sanitize_text_field( $params['middle_name'] ),
						'phone'       => sanitize_text_field( $params['phone'] ),
						'email'       => sanitize_text_field( $params['email'] ),
					],
				],
			]
		);

		// Move items from 'available' to 'reserved' stock.
		StoreItem::move_items_to_reserve( $user_cart );

		// Try getting user orders from user meta.
		$user_orders = get_user_meta( $user_id, 'user_orders', 'true' );
		// Check if the user has made orders before.
		if ( ! is_array( $user_orders ) ) {
			$user_orders = [];
		}
		// Add order id to the list of user orders.
		$user_orders[] = $order_id;
		// Update user meta.
		update_user_meta( $user_id, 'user_orders', $user_orders );

		// Clear user cart.
		update_user_meta( $user_id, 'user_cart', [] );

		// Send user an email with purchasing notice.
		EmailController::get_instance()->send_purchase_notice_email( wp_get_current_user()->user_email, $user_cart );

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Attempts to request order cancellation by user
	 *
	 * @param \WP_REST_Request $request Request Data.
	 *
	 * @return array Request response.
	 */
	public function user_requested_cancellation( \WP_REST_Request $request ): array {
		// Get request parameters.
		$params = $request->get_params();

		// Get order id.
		$order_id = (int) sanitize_text_field( $params['order_id'] );
		// Get post object.
		$post_obj = get_post( $order_id );

		// Check if order exists.
		if ( ! isset( $post_obj ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Такого замовлення не існує!', 'estore-theme' ),
			];
		}

		// Check if the order author matches with current user.
		if ( get_current_user_id() !== (int) $post_obj->post_author ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Неможливо відмінити замовлення іншого користувача', 'estore-theme' ),
			];
		}

		// Check if the order is not completed, cancelled or requested for cancellation.
		if ( in_array( OrderStatus::tryFrom( (int) get_post_meta( $order_id, 'order-status', true ) ), [ OrderStatus::requested_cancellation, OrderStatus::order_complete, OrderStatus::cancelled_by_user, OrderStatus::cancelled_by_admin ], true ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Це замовлення неможливо відмінити', 'estore-theme' ),
			];
		}

		// Update the status of the order.
		update_post_meta( $order_id, 'order-status', OrderStatus::requested_cancellation->value );

		return [
			'status'       => 200,
			'success'      => true,
			'order_status' => OrderStatus::get_order_status_title( OrderStatus::requested_cancellation ),
		];
	}

	/**
	 * Registers endpoint related to checking out functionality.
	 */
	public function register_rest_routes(): void {

		register_rest_route(
			$this->api_namespace,
			'create-order',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'create_order' ],
					'permission_callback' => static fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'args'                => [
						// User Details.
						'first_name' => [
							'type'     => 'string',
							'required' => true,
						],
						'last_name' => [
							'type'     => 'string',
							'required' => true,
						],
						'middle_name' => [
							'type'     => 'string',
							'required' => false,
						],
						'phone'       => [
							'type'     => 'string',
							'required' => false,
						],
						'email'       => [
							'type'     => 'string',
							'required' => true,
						],
						// Delivery Data.

						// Payment Data.
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'request-cancellation',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'user_requested_cancellation' ],
					'permission_callback' => static fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'args'                => [
						'order_id' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
	}

	#endregion
}
