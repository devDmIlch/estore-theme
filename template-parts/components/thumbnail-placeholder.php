<?php
/**
 * Sale-item post default generating thumbnail.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="gen-thumbnail">
	<div class="related-categories">
		<?php foreach ( $args['terms'] as $cat_name ) : ?>
			<div class="single-cat">
				<?php echo esc_html( $cat_name ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
