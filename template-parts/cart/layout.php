<?php
/**
 * Layout template for shopping cart
 *
 * @package estore/theme
 * @since 0.0.1
 */

if ( empty( $args ) ) {
	return;
}

?>
<div class="shopping-cart">
	<?php
	get_template_part( 'template-parts/cart/empty' );

	if ( ! empty( $args['products'] ) ) {
		foreach ( $args['products'] as $data ) {
			if ( array_key_exists( $data['var_id'], $args['errors'] ?? [] ) ) {
				$data['error'] = $args['errors'][ $data['var_id'] ];
			}
			get_template_part( 'template-parts/cart/item', args: $data );
		}
	}
	?>
</div>
