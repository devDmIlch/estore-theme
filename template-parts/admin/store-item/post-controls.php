<?php
/**
 * Control template for a single store-item post.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="single-store-item-control single-control" value="<?php echo esc_attr( $args['post_data']['id'] ); ?>">
	<div class="item-thumbnail">
		<img src="<?php echo esc_url( $args['post_data']['thumbnails'][0] ); ?>">
	</div>
	<div class="item-data">
		<h3><?php echo esc_html( $args['post_data']['title'] ); ?></h3>
	</div>
	<div class="controls">
		<div class="prev">
			&#x1F808;
		</div>
		<div class="next">
			&#x1F80A;
		</div>
	</div>
</div>
