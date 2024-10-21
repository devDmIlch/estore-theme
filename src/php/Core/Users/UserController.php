<?php
/**
 * User Controller class template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Users;

/**
 * User controller class
 */
class UserController {

	#region Private Fields.

	/**
	 * Login controller object instance.
	 * @var Login $login_controller
	 */
	private Login $login_controller;

	/**
	 * API Namespace for REST requests.
	 * @var string $api_namespace
	 */
	private string $api_namespace;

	#endregion

	#region Initializing Methods.

	/**
	 * Class initializing Method
	 */
	public function init(): void {
		// Initialize login controller.
		$this->login_controller = new Login();
		$this->login_controller->init();

		$profile_controller = new Profile();
		$profile_controller->init();

		// Set endpoint path.
		$this->api_namespace  = 'estore/user';

		$this->hooks();
	}

	/**
	 * Class hooks initializing method.
	 */
	public function hooks(): void {
		// Add custom roles related to the theme.
		add_action( 'switch_theme', [ $this, 'create_theme_roles' ] );
		// Register pages related to the theme.
		add_action( 'switch_theme', [ $this, 'maybe_register_user_pages' ], 10, 3 );

		// Register Rest Routes.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	#endregion


	#region Public Methods.

	/**
	 * Creates custom roles related to the theme.
	 */
	public function create_theme_roles(): void {
		// Register 'customer' if it doesn't exist.
		$customer_role = get_role( 'customer' );
		if ( ! isset( $customer_role ) ) {
			add_role(
				'customer',
				__( 'Покупець', 'estore-theme' ),
				[
					'read_posts' => true,
				]
			);
		}

		// Register 'pending' role if it doesn't exist.
		$user_pending_role = get_role( 'pending' );
		if ( ! isset( $user_pending_role ) ) {
			add_role(
				'pending',
				__( 'Не підтверджено', 'estore-theme' ),
				[
					'read_posts' => false,
				]
			);
		}
	}

	/**
	 * Checks whether the website includes user related theme pages and add them if they were not found.
	 * (These include: registration page, custom login page, profile page etc.)
	 *
	 * @param string    $new_name  Name of the new theme.
	 * @param \WP_Theme $new_theme WP_Theme instance of the new theme.
	 * @param \WP_Theme $old_theme WP_Theme instance of the old theme.
	 */
	public function maybe_register_user_pages( string $new_name, \WP_Theme $new_theme, \WP_Theme $old_theme ): void {
		// Create pages for the theme.
		$theme_pages = array_merge(
			[
				'profile'  => __( 'Особистий Кабінет', 'estore-theme' ),
				'settings' => __( 'Налаштування', 'estore-theme' ),
				'orders'   => __( 'Мої Замовлення', 'estore-theme' ),
				'cart'     => __( 'Корзина', 'estore-theme' ),
				'checkout' => __( 'Оформлення Замовлення', 'estore-theme' ),
			],
			$this->login_controller->get_pages()
		);

		foreach ( $theme_pages as $name => $title ) {
			// Check if login page exists and create one .
			if ( empty( get_page_by_path( $name ) ) ) {
				wp_insert_post(
					[
						'post_type'   => 'page',
						'post_status' => 'publish',
						'post_name'   => $name,
						'post_title'  => $title,
					]
				);
			}
		}
	}

	/**
	 * Saves user data passed in the POST request.
	 *
	 * @param \WP_REST_Request $request POST request data.
	 *
	 * @return array Request response.
	 */
	public function save_user_data( \WP_REST_Request $request ): array {
		// Get request parameters.
		$params = $request->get_params();

		// Update only current user.
		$user_id = get_current_user_id();

		// Prepare meta values.
		$meta_input = [];
		if ( ! empty( $params['first_name'] ) ) {
			$meta_input['first-name'] = sanitize_text_field( $params['first_name'] );
		}
		if ( ! empty( $params['last_name'] ) ) {
			$meta_input['last-name'] = sanitize_text_field( $params['last_name'] );
		}
		if ( ! empty( $params['middle_name'] ) ) {
			$meta_input['middle-name'] = sanitize_text_field( $params['middle_name'] );
		}
		if ( ! empty( $params['phone'] ) ) {
			$meta_input['phone'] = sanitize_text_field( $params['phone'] );
		}

		wp_update_user(
			[
				'ID'         => $user_id,
				'meta_input' => $meta_input,
			]
		);

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Attempts to update user email if the password to the current user is correct.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array Request response.
	 */
	public function update_user_email( \WP_REST_Request $request ): array {
		// Get request parameters.
		$params = $request->get_params();

		// Get current user.
		$user_obj = wp_get_current_user();

		// Check if the user is logged in.
		if ( ! isset( $user_obj ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Необхідно увійти в особистий профіль!', 'estore-theme' ),
			];
		}

		// Test if the password matches password of current user.
		if ( ! wp_check_password( sanitize_text_field( $params['password'] ), $user_obj->data->user_pass, $user_obj->ID ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Невірний пароль!', 'estore-theme' ),
			];
		}

		// Get the email.
		$new_email = sanitize_email( $params['email'] );

		// Check if the email is valid.
		if ( ! preg_match( '/(@).+(\.).+/', $new_email ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Невірний формат елекронної пошти!', 'estore-theme' ),
			];
		}

		// Update user.
		$result = wp_update_user(
			[
				'ID'         => $user_obj->ID,
				'user_email' => $new_email,
			]
		);

		// Check if wp_update_user returned an error.
		if ( is_wp_error( $result ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'message' => __( 'Не вдалось оновити електронну пошту.', 'estore-theme' ),
			];
		}

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Registers endpoints related to general user functionality.
	 */
	public function register_rest_routes(): void {

		register_rest_route(
			$this->api_namespace,
			'save-data',
			[
				[
					'methods'             => [ 'POST' ],
					'permission_callback' => static fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'callback'            => [ $this, 'save_user_data' ],
					'args'                => [
						'first_name'  => [
							'type'     => 'string',
							'required' => false,
						],
						'last_name'   => [
							'type'     => 'string',
							'required' => false,
						],
						'middle_name' => [
							'type'     => 'string',
							'required' => false,
						],
						'phone'       => [
							'type'     => 'string',
							'required' => false,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'update-email',
			[
				[
					'methods'             => [ 'POST' ],
					'permission_callback' => static fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'callback'            => [ $this, 'update_user_email' ],
					'args'                => [
						'email'    => [
							'type'     => 'string',
							'required' => true,
						],
						'password' => [
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
