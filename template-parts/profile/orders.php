<?php
/**
 * Orders subpage of profile page.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

if ( $args['orders_exist'] ) {
	?>
	<h2><?php esc_html_e( 'Мої замовлення', 'estore-theme' ); ?></h2>
	<?php
	get_template_part( 'template-parts/components/archive', 'loader' );

	$archive_args = [
		'filters' => false,
		'state'   => false,
		'class'   => 'initial-load',
	];
	get_template_part( 'template-parts/archive/controls', args: $archive_args );
} else {
	?>
	<p class="no-orders-message">
		<?php esc_html_e( 'Ви ще не зробили жодного замовлення.', 'estore-theme' ); ?>
	</p>
	<?php
}
