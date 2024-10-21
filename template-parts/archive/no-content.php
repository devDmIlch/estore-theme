<?php
/**
 * Archive no content template file
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="no-content">
	<?php echo esc_html( $args['message'] ?? __( 'Не значдено жодного товару', 'estore-theme' ) ); ?>
</div>