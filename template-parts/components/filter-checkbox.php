<?php
/**
 * Checkbox filters template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="filter-section" filter="<?php echo esc_attr( $args['tax']->name ); ?>">
	<div class="filter-name">
		<?php echo esc_html( $args['tax']->label ); ?>
	</div>
	<div class="filter-dropdown filter-option-list">
		<?php foreach ( $args['terms'] as $term_id => $term_data ) : ?>
			<div class="filter-option filter-checkbox <?php echo esc_attr( in_array( $term_id, $args['selected'], true ) ? 'active' : '' ); ?>" option="<?php echo esc_attr( $term_id ); ?>" style="margin-left: <?php echo esc_attr( $term_data['depth'] * 10 . 'px' ); ?>">
				<?php echo esc_html( $term_data['name'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
