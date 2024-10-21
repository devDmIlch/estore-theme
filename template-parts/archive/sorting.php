<?php
/**
 * Archive sorting control template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires parameters.
if ( empty( $args ) ) {
	return;
}

// Set selected to the first element, if it isn't specified.
if ( empty( $args['selected'] ) ) {
	$args['selected'] = array_key_first( $args['options'] );
}

?>
<div class="archive-sorter">
	<div class="sorter-name dropdown-trigger">
		<span class="sort-ico"></span>
		<?php esc_html_e( 'Сортування: ', 'estore-theme' ); ?>
		<span class="sort-option-selected">
			<?php echo esc_html( $args['options'][ $args['selected'] ] ); ?>
		</span>
		<span class="dropdown-ico"></span>
	</div>
	<div class="sorter-dropdown dropdown-target">
		<?php foreach ( $args['options'] as $option_val => $option_name ) : ?>
			<?php if ( $args['selected'] === $option_val ) : ?>
				<div class="sort-option active" option="<?php echo esc_attr( $option_val ); ?>">
					<?php echo esc_html( $option_name ); ?>
				</div>
			<?php else : ?>
				<div class="sort-option" option="<?php echo esc_attr( $option_val ); ?>">
					<?php echo esc_html( $option_name ); ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
