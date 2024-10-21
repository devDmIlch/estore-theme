<?php
/**
 * Template file for EStore Theme
 *
 * @package estore/theme
 * @since 0.0.1
 */

get_template_part( 'template-parts/header' );

// Get current post type to decide which template to load.
$__post_type = get_post_type();

if ( is_archive() || is_search() ) {
	\EStore\ThemeController::get_archive_controller()->load_archive_page();
}

if ( is_single() ) {
	\EStore\ThemeController::get_post_type_controller()->load_single_page();
}

if ( is_page() ) {
	switch ( get_queried_object()->post_name ) {
		case 'login':
			\EStore\Core\Users\Login::load_login_page();
			break;
		case 'register':
			\EStore\Core\Users\Login::load_register_page();
			break;
		case 'recovery':
			\EStore\Core\Users\Login::load_recovery_page();
			break;
		case 'profile':
		case 'orders':
		case 'settings':
			\EStore\Core\Users\Profile::load_profile_page();
			break;
		case 'cart':
			\EStore\Core\Shopping\Cart::load_cart_page();
			break;
		case 'checkout':
			\EStore\Core\Shopping\Checkout::load_checkout_page();
			break;
		default:
			\EStore\ThemeController::load_default_page();
			break;
	}
}

if ( is_home() ) {
	\EStore\ThemeController::load_home_page();
}

if ( is_404() ) {
	get_template_part( 'template-parts/pages/404' );
}

get_template_part( 'template-parts/footer' );
