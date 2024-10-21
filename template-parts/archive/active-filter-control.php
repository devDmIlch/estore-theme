<?php
/**
 * Active filter control template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="active-filter" filter="<?php echo esc_attr( $args['tax']->name ); ?>">
	<div class="active-filter-name">
		<?php echo esc_html( $args['tax']->label . ':' ); ?>
	</div>
	<?php foreach ( $args['selected'] as $term_id ) : ?>
		<div class="active-option" option="<?php echo esc_attr( $term_id ); ?>">
			<?php echo esc_html( $args['terms'][ $term_id ]['name'] ); ?>
			<span class="remove-option">
				&#10006;
			</span>
		</div>
	<?php endforeach; ?>
</div>
