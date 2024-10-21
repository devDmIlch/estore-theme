<?php
/**
 * Custom Login page class.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Users;

use EStore\Core\Emails\EmailController;
use EStore\Ext\Helpers;

/**
 * Login page class
 */
class Login {

	#region Private Fields.

	/**
	 * REST API namespace.
	 * @var string $api_namespace
	 */
	private string $api_namespace;

	/**
	 * Recaptcha site key.
	 * @var string $recaptcha_site_key
	 */
	private string $recaptcha_site_key;

	/**
	 * List of login related pages.
	 * @var array $pages
	 */
	private array $pages;

	#endregion


	#region Public Properties.

	/**
	 * Returns a list of pages utilized in logging-in functionality.
	 * @return array array of pages, where page slugs are keys and page titles are slugs.
	 */
	public function get_pages(): array {
		return $this->pages;
	}

	#endregion


	#region Construction Methods.

	/**
	 * Class initialization method.
	 */
	public function init(): void {
		// Set namespace for login related functionality.
		$this->api_namespace = 'estore/login';
		// Set recaptcha site key.
		$this->recaptcha_site_key = get_option( 'recaptcha-site-key', null );
		// Set pages related to logging-in functionality.
		$this->pages = [
			'login'    => __( 'Вхід', 'estore-theme' ),
			'register' => __( 'Реєстрація', 'estore-theme' ),
			'recovery' => __( 'Відновлення Пароля', 'estore-theme' ),
		];

		$this->hooks();
	}

	/**
	 * Class hooks initialization method.
	 */
	protected function hooks(): void {
		// Redirect to a custom login page upon visiting wp-login.php page.
		add_action( 'init', [ $this, 'redirect_to_custom_login' ] );
		// Register captcha script for the login pages.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_login_captcha' ], 5 );
		// Initialize user log out.
		add_action( 'init', [ $this, 'logout_user' ] );
		// Redirect user to home page upon log out.
		add_action(
			'wp_logout',
			function () {
				wp_safe_redirect( get_site_url() );
				exit();
			}
		);

		// Register endpoints.
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	#endregion


	#region Private Methods.

	/**
	 * Try activating user with code.
	 *
	 * @param string $user_email      Email of a user that should be activated.
	 * @param string $activation_code Activation code to compare with.
	 * @param string $message         User activation success/failure message. Passed by reference.
	 *
	 * @return bool Whether the activation was successful.
	 */
	private static function maybe_activate_user( string $user_email, string $activation_code, string &$message = '' ): bool {
		// Try looking for user with supplied email.
		$user_obj = get_user_by( 'email', $user_email );

		if ( ! $user_obj ) {
			$message = __( 'Користувача з такою електронною поштою не існує.', 'estore-theme' );
			return false;
		}

		// Test whether the user is already activated.
		if ( ! in_array( 'pending', $user_obj->roles, true ) ) {
			$message = __( 'Цей користувач вже активований!', 'estore-theme' );
			return true;
		}

		// Get user's verification code.
		$user_activation_code = get_user_meta( $user_obj->ID, 'verification_code', true );

		// Check whether provided activation code matches activation code of a user.
		if ( $user_activation_code !== $activation_code ) {
			$message = __( 'Неправильний код активації!', 'estore-theme' );
			return false;
		}

		$user_obj->remove_role( 'pending' );
		$message = __( 'Акаунт користувача активовано.', 'estore-theme' );

		// Remove activation code from users meta.
		delete_user_meta( $user_obj->ID, 'verification_code' );

		return true;
	}

	/**
	 * Compares recovery code of the user with code passed as an argument.
	 *
	 * @param string $user_email    Email of a user.
	 * @param string $recovery_code Password recovery code to compare with.
	 * @param string $message       User recovery code matching message. Passed by reference.
	 *
	 * @return bool whether the recovery codes match.
	 */
	private static function check_recovery_codes( string $user_email, string $recovery_code, string &$message = '' ): bool {
		// Try looking for user with supplied email.
		$user_obj = get_user_by( 'email', $user_email );

		if ( ! $user_obj ) {
			$message = __( 'Користувача з такою електронною поштою не існує.', 'estore-theme' );
			return false;
		}

		// Get user's verification code.
		$user_activation_code = get_user_meta( $user_obj->ID, 'recovery_code', true );

		// Check whether provided activation code matches activation code of a user.
		if ( $user_activation_code !== $recovery_code ) {
			$message = __( 'Неправильний код відновлення паролю!', 'estore-theme' );
			return false;
		}

		$message = __( 'Введіть новий пароль до вашого акаунту.', 'estore-theme' );
		return true;
	}

	#endregion


	#region Public Methods.

	/**
	 * Logs user out if they visit static logout page.
	 */
	public function logout_user(): void {
		// Check if the user is on one of the profile pages.
		if ( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) !== '/logout' ) {
			return;
		}

		// Log out current user.
		wp_logout();
	}

	/**
	 * Redirects to custom login page when trying to enter wp-login.php page.
	 */
	public function redirect_to_custom_login(): void {
		if ( is_login() ) {
			wp_redirect( get_site_url() . '/login' );
		}
	}

	/**
	 * Enqueues login captcha for the login pages.
	 */
	public function enqueue_login_captcha(): void {
		// Check whether a user is currently on login pages.
		global $pagename;
		if ( ! is_page() || ! array_key_exists( $pagename, $this->get_pages() ) ) {
			return;
		}

		// Check fi captcha exists.
		if ( ! empty( $this->recaptcha_site_key ) ) {
			// Enqueue JavaScript.
			wp_enqueue_script(
				'login_captcha_script',
				'https://www.google.com/recaptcha/api.js?render=' . $this->recaptcha_site_key,
			);
		}
	}

	/**
	 * Verifies user's login data and logins them if they are correct.
	 *
	 * @param \WP_REST_Request $request POST request data.
	 *
	 * @return array request response.
	 */
	public function login_user( \WP_REST_Request $request ): array {
		// Get request parameters.
		$params = $request->get_params();
		// Try looking for users with supplied email.
		$user_obj = get_user_by( 'email', sanitize_email( $params['email'] ) );
		// Get user-provided password.
		$password = $params['password'];

		// Check whether the user exists and return 'user doesn't exist' message if it doesn't.
		if ( ! isset( $user_obj ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Невірна електронна пошта або пароль', 'estore-theme' ),
			];
		}
		// Check whether provided password matches user's password.
		if ( ! wp_check_password( $password, $user_obj->data->user_pass, $user_obj->ID ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Невірна електронна пошта або пароль', 'estore-theme' ),
			];
		}
		// Check whether the user has activated email.
		if ( in_array( 'pending', $user_obj->roles, true ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Необхідно активувати акаунт. Перевірте пошту, ми вам відправивли листа з посиланням для активації', 'estore-theme' ),
			];
		}

		// Login user.
		wp_signon(
			[
				'user_login'    => $user_obj->user_login,
				'user_password' => $password,
			]
		);

		return [
			'status'  => 200,
			'success' => true,
		];
	}

	/**
	 * Attempts to register user with provided data.
	 *
	 * @param \WP_REST_Request $request POST request data.
	 *
	 * @return array request response.
	 */
	public function register_pending_user( \WP_REST_Request $request ): array {
		$params = $request->get_params();
		// Get supplied email.
		$email = sanitize_email( $params['email'] );
		// Check whether email is already taken.
		if ( email_exists( $email ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Користувач з такою електронною поштою вже існує', 'estore-theme' ),
			];
		}
		// Create verification code for a user.
		$verification_code = wp_generate_password( 20, false );
		// Insert new user with given parameters.
		$user_id = wp_insert_user(
			[
				'user_login'           => $email,
				'user_email'           => $email,
				'user_pass'            => $params['password'],
				'show_admin_bar_front' => false,
				'role'            => 'customer',
				'meta_input'           => [
					'verification_code' => $verification_code,
				],
			]
		);
		// Add 'pending' role to user to prevent them from accessing website before account activation.
		get_user_by( 'id', $user_id )->add_role( 'pending' );
		// Send account activation email.
		EmailController::get_instance()->send_account_activation_email( $email, $verification_code );

		return [
			'status'  => 200,
			'success' => true,
			'message' => __( 'Реєстрація успішна!<br><br>Залишився останній крок. Активуйте свій акаунт перейшовши за посиланням, яке ми надіслали ван листом на електронну пошту', 'estore-theme' ),
		];
	}

	/**
	 * Checks whether user with email passed through REST parameters exists and sends them password recovery email.
	 *
	 * @param \WP_REST_Request $request POST request data.
	 *
	 * @return array request response.
	 **/
	public function process_account_recovery_request( \WP_REST_Request $request ): array {
		$params = $request->get_params();
		// Get email of a user that should have their password recovered.
		$email = sanitize_email( $params['email'] );
		// Check whether user with email exists.
		if ( ! email_exists( $email ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Користувача з такою поштою не існує', 'estore-theme' ),
			];
		}
		// If user exists get user object by the email.
		$user_obj = get_user_by( 'email', $email );
		// Generate recovery code using WP default password generator.
		$recovery_code = wp_generate_password( 20, false );
		// Add recovery code to the users meta.
		update_user_meta( $user_obj->ID, 'recovery_code', $recovery_code );
		// Send user an email with recovery link.
		EmailController::get_instance()->send_password_recovery_email( $email, $recovery_code );

		return [
			'status'  => 200,
			'success' => true,
			'message' => __( 'На Вашу електронну пошту було відправлено листа з інструкцією по відновленню паролю', 'estore-theme' ),
		];
	}

	/**
	 * Checks whether user with email passed through REST parameters exists and sends them password recovery email.
	 *
	 * @param \WP_REST_Request $request POST request data.
	 *
	 * @return array request response.
	 **/
	public function process_account_password_reset( \WP_REST_Request $request ): array {
		$params = $request->get_params();
		// Get email of a user that should have their password updated.
		$email = sanitize_email( $params['email'] );
		// Get recovery code from the form.
		$form_recovery_code = sanitize_text_field( $params['recovery-code'] );
		// Get new password from the form.
		$new_password = $params['password'];
		// Check whether user with email exists.
		if ( ! email_exists( $email ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Користувача з такою поштою не існує', 'estore-theme' ),
			];
		}
		// If user exists get user object by the email.
		$user_obj = get_user_by( 'email', $email );
		// Get recovery code from user's meta.
		$user_recovery_code = get_user_meta( $user_obj->ID, 'recovery_code', true );
		// Do an additional check on whether the code is valid.
		if ( $user_recovery_code !== $form_recovery_code ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Невірний код відновлення паролю!', 'estore-theme' ),
			];
		}
		// Check whether new password matches old password.
		if ( wp_check_password( $new_password, $user_obj->data->user_pass, $user_obj->ID ) ) {
			return [
				'status'  => 200,
				'success' => false,
				'error'   => __( 'Новий пароль не повинен співпадати зі старим паролем!', 'estore-theme' ),
			];
		}
		// Update user password.
		wp_set_password( $new_password, $user_obj->ID );
		// Send user an email notifying user about password reset, in case they didn't do it.
		EmailController::get_instance()->send_password_update_notice_email( $email );

		return [
			'status'  => 200,
			'success' => true,
			'message' => __( 'Пароль до вашого акаунту було оновлено!', 'estore-theme' ),
		];
	}

	/**
	 * Registers endpoint routes related to the logging-in functionality
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->api_namespace,
			'verify-user',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'login_user' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'email' => [
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

		register_rest_route(
			$this->api_namespace,
			'register',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'register_pending_user' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'email' => [
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

		register_rest_route(
			$this->api_namespace,
			'recovery',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'process_account_recovery_request' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'email' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$this->api_namespace,
			'reset-password',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'process_account_password_reset' ],
					'permission_callback' => fn ( \WP_REST_Request $request ) => wp_verify_nonce( $request->get_header( 'x_wp_nonce' ), 'wp_rest' ),
					'show_in_index'       => true,
					'args'                => [
						'email' => [
							'type'     => 'string',
							'required' => true,
						],
						'recovery-code' => [
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


	#region Public Static Methods.

	/**
	 * Login page template loader.
	 */
	public static function load_login_page(): void {
		// Retrieve email from the GET parameters.
		$user_email = sanitize_email( wp_unslash( $_GET['user'] ?? '' ) );
		// Retrieve activation code from the GET parameters.
		$activation_code = sanitize_text_field( wp_unslash( $_GET['activation_code'] ?? '' ) );

		// Prepare login template arguments array.
		$template_args = [
			'user_email' => $user_email,
		];

		// If activation code is provided, attempt to activate user.
		if ( ! empty( $activation_code ) ) {
			$activation_message = '';

			if ( self::maybe_activate_user( $user_email, $activation_code, $activation_message ) ) {
				$template_args['success_message'] = $activation_message;
			} else {
				$template_args['error_message'] = $activation_message;
			}
		}

		get_template_part( 'template-parts/pages/login', args: $template_args );
	}

	/**
	 * Registration page template loader.
	 */
	public static function load_register_page(): void {
		get_template_part( 'template-parts/pages/register' );
	}

	/**
	 * Password recovery page template loader.
	 */
	public static function load_recovery_page(): void {
		// Retrieve email from the GET parameters.
		$user_email = sanitize_email( wp_unslash( $_GET['user'] ?? '' ) );
		// Retrieve recovery code from the GET parameters.
		$recovery_code = sanitize_text_field( wp_unslash( $_GET['recovery_code'] ?? '' ) );

		if ( empty( $recovery_code ) ) {
			get_template_part( 'template-parts/pages/recovery' );
			return;
		}

		$recovery_message = '';
		// If activation code is provided, attempt to activate user.
		if ( ! self::check_recovery_codes( $user_email, $recovery_code, $recovery_message ) ) {
			get_template_part( 'template-parts/pages/recovery', args: [ 'error_message' => $recovery_message ] );
			return;
		}

		$template_args = [
			'recovery_code'   => $recovery_code,
			'user_email'      => $user_email,
			'success_message' => $recovery_message,
		];

		get_template_part( 'template-parts/pages/recovery', 'reset', $template_args );
	}

	#endregion
}
