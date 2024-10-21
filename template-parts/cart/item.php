<?php
/**
 * Single item for cart
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="single-cart-item <?php echo esc_attr( isset( $args['error'] ) ? 'error' : '' ); ?>" var-id="<?php echo esc_attr( $args['var_id'] ); ?>">
	<div class="item-error">
		<?php echo esc_html( $args['error'] ?? '' ); ?>
	</div>
	<div class="item-thumbnail">
		<img src="<?php echo esc_url( $args['thumbnail'] ); ?>" alt="<?php esc_attr_e( 'Зображення товару', 'estore-theme' ); ?>">
	</div>
	<div class="item-data">
		<h3 class="item-title">
			<?php echo esc_html( $args['name'] ); ?>
		</h3>
		<p class="item-price">
			<b>
				<?php echo esc_html( $args['price_sale'] ?? $args['price'] ); ?>
			</b>
			<?php if ( isset( $args['price_sale'] ) ) : ?>
				<s class="price-old-num">
					<?php echo esc_html( $args['price'] ); ?>
				</s>
			<?php endif; ?>
		</p>
		<a class="remove-item">
			<?php esc_html_e( 'Видалити', 'estore-theme' ); ?>
		</a>
		<div class="number">
			<div class="number-decrease <?php echo esc_attr( $args['number'] > 1 ? '' : 'disabled' ); ?>">
				-
			</div>
			<span class="number-in-cart">
				<?php echo esc_html( $args['number'] ); ?>
			</span>
			<div class="number-increase">
				+
			</div>
		</div>
	</div>
</div>
