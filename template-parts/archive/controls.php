<?php
/**
 * Archive control template
 * Renders various sections of the archive control (e.g. content, filters etc.)
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

// Compile classes.
$classes = implode( ' ', [ 'estore-archive', ...( is_array( $args['class'] ?? [] ) ? $args['class'] ?? [] : [ $args['class'] ] ) ] );

?>
<div class="<?php echo esc_attr( $classes ); ?>">
	<?php if ( $args['filters'] ?? true ) : ?>
		<div class="archive-sidebar">
			<div class="archive-filters"></div>
		</div>
	<?php endif; ?>
	<div class="archive-main-section">
		<?php if ( $args['state'] ?? true ) : ?>
			<div class="archive-state">
				<div class="archive-selected"></div>
				<div class="archive-sort"></div>
			</div>
		<?php endif; ?>
		<div class="archive-content"></div>
		<?php if ( $args['pagination'] ?? true ) : ?>
			<div class="archive-pagination"></div>
		<?php endif; ?>
	</div>
</div>
