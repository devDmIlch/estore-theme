<?php
/**
 * Profile Page class template
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Users;

use EStore\Ext\Helpers;

/**
 * Profile class
 */
class Profile {

	#region Private Fields.

	/**
	 * List of profile pages.
	 * @var array $profile_pages
	 */
	private static array $profile_pages;

	#endregion


	#region Initialization Methods.

	/**
	 * Class initialization method.
	 */
	public function init(): void {
		// Create an array of profile pages.
		if ( ! isset( self::$profile_pages ) ) {
			self::$profile_pages = [
				'profile'  => __( 'Особистий Дані', 'estore-theme' ),
				'orders'   => __( 'Мої Замовлення', 'estore-theme' ),
				'settings' => __( 'Налаштування', 'estore-theme' ),
			];
		}
		// Set up hooks.
		$this->hooks();
	}

	/**
	 * Class hook initialization method.
	 */
	protected function hooks(): void {
		// Localization Script.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_archive_script_data' ], 20 );
		// Redirect non-logged in users.
		add_action( 'init', [ $this, 'redirect_non_logged_in_users' ] );
	}

	#endregion


	#region Public Methods.

	/**
	 * Redirects users from profile pages if they aren't logged in.
	 */
	public function redirect_non_logged_in_users(): void {
		// Check if the user is on one of the profile pages.
		if ( ! array_key_exists( str_replace( '/', '', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ), self::$profile_pages ) ) {
			return;
		}

		// This page requires being logged on.
		Helpers::force_user_login();
	}

	/**
	 * Enqueues archive data as localization script.
	 */
	public function enqueue_archive_script_data(): void {
		// Bail if the page is not an archive page.
		if ( ! is_page() & get_post_field( 'post_name' ) !== 'orders' ) {
			return;
		}

		// Set up localization lines to pass into the JS.
		$localization_lines = [
			'type'     => 'orders',
			'post__in' => get_user_meta( get_current_user_id(), 'user_orders', true ),
		];

		// Enqueue archive preferences variables.
		wp_localize_script( 'estore_script', 'archivePrefs', $localization_lines );
	}

	/**
	 * Loads profile page template
	 */
	public static function load_profile_page(): void {
		// Retrieve current user.
		$user_obj = wp_get_current_user();

		// Redirect to login page if the user is not logged in.
		if ( ! isset( $user_obj ) ) {
			wp_safe_redirect( '/login' );
		}

		// Get page name.
		$__slug = get_post_field( 'post_name' );

		// Prepare arguments for all profile pages.
		$args = [
			'menu' => [
				'selected' => get_post_field( 'post_name' ),
				'subpages' => self::$profile_pages,
			],
		];

		// Get user meta to populate data for subpages.
		$user_meta = get_user_meta( $user_obj->ID, single: true );

		// Prepare arguments for subpage-level content.
		switch ( $__slug ) {
			case 'profile':
				// Prepare array to populate with data.
				$args['data'] = [
					'user'     => [],
					'delivery' => [],
				];
				// Populate user data with values from meta.
				foreach ( [ 'first-name', 'last-name', 'middle-name', 'phone' ] as $meta_key ) {
					if ( ! empty( $user_meta[ $meta_key ] ) ) {
						$args['data']['user'][ $meta_key ] = $user_meta[ $meta_key ][0];
					}
				}

				// TODO: Prepare delivery data.

				break;
			case 'orders':
				// Check if the orders exist, and if they do they will be populated via Archive class.
				$args['data'] = [
					'orders_exist' => ! empty( $user_meta['user_orders'] ),
				];
				break;
			case 'settings':
				// Obscure user email just in case.
				$user_email    = explode( '@', $user_obj->user_email );
				$user_email[0] = mb_substr( $user_email[0], 0, 1 ) . '•••••••' . mb_substr( $user_email[0], -1 );
				$user_email    = implode( '@', $user_email );
				// Add user email to change it.
				$args['data'] = [
					'email' => $user_email,
				];
				break;
		}

		get_template_part( 'template-parts/pages/profile', args: $args );
	}

	#endregion
}
