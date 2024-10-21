<?php
/**
 * Store item variations section template
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<div class="variations-section">
	<?php
	if ( ! empty( $args['vars'] ) ) {
		usort( $args['vars'], static fn ( $val_1, $val_2 ) => (int) $val_1['pos'] - (int) $val_2['pos'] );
	}

	// Check the variations.
	foreach ( $args['vars'] ?? [ [] ] as $var_props ) {
		get_template_part( '/template-parts/admin/store-item/variation', 'settings', $var_props );
	}
	?>
	<div class="var-add">
		+
	</div>
</div>
