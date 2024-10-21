<?php
/**
 * Header template file
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
			content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( is_page() ? get_queried_object()->post_name : null ); ?>>
	<header class="website-header">
		<div class="header-menu inactive">
			<?php get_template_part( 'template-parts/menus/menu', 'main' ); ?>
		</div>
		<div class="menu-controls">
			<div class="menu-trigger">
				&#8801;
			</div>
			<div class="website-identity">
				<?php the_custom_logo(); ?>
			</div>
		</div>
		<div class="search-area">
			<input class="search-box" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_html_e( 'Пошук...', 'estore-theme' ); ?>" type="text" alt="<?php esc_attr_e( 'Пошук', 'estore-theme' ); ?>" />
			<a tabindex="0" class="search-submit inactive" href="<?php echo esc_url( get_search_link( '' ) ); ?>">
			</a>
		</div>
		<div class="user-controls">
			<div class="user-cart cart-trigger">
			</div>
			<div class="cart-pop-up">
				<div class="cart-header">
					<?php esc_html_e( 'Кошик', 'estore-theme' ); ?>
				</div>
				<div class="cart-actions">
					<a class="cart-link" href="<?php echo esc_url( get_site_url() . '/cart' ); ?>">
						<?php esc_html_e( 'Оформлення Замовленя', 'estore-theme' ); ?>
					</a>
				</div>
			</div>
			<?php if ( is_user_logged_in() ) : ?>
				<div class="user-profile">
					<a class="profile-link" href="<?php echo esc_url( get_site_url() . '/profile' ); ?>"></a>
				</div>
			<?php else : ?>
				<div class="user-login">
					<a class="login-link" href="/login"></a>
				</div>
			<?php endif; ?>
		</div>
	</header>

