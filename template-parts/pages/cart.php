<?php
/**
 * Cart page template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>

<main class="page-content cart-page shrink">
	<h1><?php esc_html_e( 'Оформлення Замовлення', 'estore-theme' ); ?></h1>
	<section class="details-customer">
		<h2><?php esc_html_e( 'Замовник', 'estore-theme' ); ?></h2>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Ім\'я', 'estore-theme' ); ?></span>
			<input class="text-input" id="first-name" name="first-name" type="text" value="<?php echo esc_html( $args['user']['first-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Прізвище', 'estore-theme' ); ?></span>
			<input class="text-input" id="last-name" name="last-name" type="text" value="<?php echo esc_html( $args['user']['last-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'По-батькові', 'estore-theme' ); ?></span>
			<input class="text-input" id="middle-name" name="middle-name" type="text" value="<?php echo esc_html( $args['user']['middle-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Телефон', 'estore-theme' ); ?></span>
			<input class="text-input" id="phone" name="phone" type="tel" value="<?php echo esc_html( $args['user']['phone'] ); ?>">
		</label>
		<label class="input-label disabled">
			<span class="label-text"><?php esc_html_e( 'Електронна пошта', 'estore-theme' ); ?></span>
			<input class="text-input" id="email" name="email" type="email" value="<?php echo esc_html( $args['user']['email'] ); ?>" disabled>
		</label>
		<div class="checkbox-label save-section-data">
			<input class="checkbox-input" id="save-customer" name="save-customer" type="checkbox" checked>
			<label for="save-customer" class="label-text"><?php esc_html_e( 'Зберегти дані', 'estore-theme' ); ?></label>
		</div>
	</section>
	<section class="details-cart">
		<h2><?php esc_html_e( 'Товари', 'estore-theme' ); ?></h2>
		<?php $args['cart']['load_cart_items'](); ?>
		<div class="price-sum">
				<?php esc_html_e( 'Всього: ', 'estore-theme' ); ?>
				<span class="total-price uah-price">
				<?php echo esc_html( $args['cart']['total'] ); ?>
			</span>
		</div>
	</section>
	<section class="details-delivery">
		<h2><?php esc_html_e( 'Доставка', 'estore-theme' ); ?></h2>

	</section>
	<section class="details-payment">
		<h2><?php esc_html_e( 'Оплата', 'estore-theme' ); ?></h2>

	</section>
	<section class="cart-actions">
		<a class="order-confirm">
			<?php esc_html_e( 'Підтвердити Замовлення', 'estore-theme' ); ?>
		</a>
	</section>
</main>
