<?php
/**
 * Store item desc subpage template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<section class="subpage subpage-desc" subpage="desc">
	<?php echo wp_kses_post( isset( $args['post_data']['desc'] ) ? $args['post_data']['desc'][0] : '' ); ?>
</section>
