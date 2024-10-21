<?php
/**
 * Pagination template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="pagination">
	<div class="pag-item dummy first <?php echo esc_attr( 1 < $args['current'] - 2 ? 'shown' : '' ); ?>">
		&#8230;
	</div>
	<div class="pag-item pag-nav prev <?php echo esc_attr( 1 === $args['current'] ? 'is-active-neighbour' : '' )?>" value="prev">
		&#x2039;
	</div>
	<?php for ( $i = 1; $i <= $args['pages']; ++$i ) : ?>
		<?php
		$option_classes = 'pag-item pag-page';
		if ( $i === $args['current'] ) {
			$option_classes .= ' ' . 'active';
		}
		if ( in_array( $args['current'], [ $i + 1, $i - 1 ], true ) ) {
			$option_classes .= ' ' . 'is-active-neighbour';
		}
		if ( $i === 1 ) {
			$option_classes .= ' ' . 'always-show first';
		}
		if ( $i === $args['pages'] ) {
			$option_classes .= ' ' . 'always-show last';
		}
		?>
		<div class="<?php echo esc_attr( $option_classes ); ?>" value="<?php echo esc_attr( $i ); ?>">
			<?php echo esc_html( $i ); ?>
		</div>
	<?php endfor; ?>
	<div class="pag-item pag-nav next <?php echo esc_attr( $args['pages'] === $args['current'] ? 'is-active-neighbour' : '' )?>" value="next">
		&#x203a;
	</div>
	<div class="pag-item dummy last <?php echo esc_attr( $args['pages'] > $args['current'] + 2 ? 'shown' : '' ); ?>">
		&#8230;
	</div>
</div>
