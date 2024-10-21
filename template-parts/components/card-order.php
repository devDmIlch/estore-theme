<?php
/**
 * Store item card template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires parameters.
if ( empty( $args ) ) {
	return;
}

?>
<div class="card-order" order-id="<?php echo esc_attr( $args['id'] ); ?>">
	<h3 class="order-title"><?php echo esc_html( $args['title'] ); ?></h3>
	<div class="order-items">
		<?php
		foreach ( $args['order-items'] as $store_item ) {
			get_template_part( 'template-parts/components/card-store-item', 'simple', args: $store_item );
		}
		?>
	</div>
	<p class="post-status">
		<?php esc_html_e( 'Статус: ', 'estore-theme' ); ?>
		<span class="status-text" type="<?php echo esc_attr( $args['order-status']->value ); ?>">
			<?php echo esc_html( \EStore\Enum\OrderStatus::get_order_status_title( $args['order-status'] ) ); ?>
		</span>
		<?php if ( $args['can-cancel'] ) : ?>
			<a class="cancel-order">
				<?php esc_html_e( 'Скасувати Замовлення', 'estore-theme' ); ?>
			</a>
		<?php endif; ?>
	</p>
</div>
