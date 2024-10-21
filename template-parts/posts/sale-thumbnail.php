<?php
/**
 * Sale-page post type thumbnail section.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<?php if ( empty( $args['post-thumbnail'] ) ) : ?>
	<section class="page-heading simple-color" style="background-color: <?php echo esc_attr( $args['placeholder_thumbnail']['color'] ); ?>">
		<div class="heading-content">
			<?php get_template_part( 'template-parts/components/thumbnail-placeholder', args: $args['placeholder_thumbnail'] ); ?>
			<div class="title-area">
				<span class="sale-date">
					<?php echo esc_html( $args['date'] ); ?>
				</span>
				<h1 class="page-title">
					<?php echo esc_html( $args['title'] ); ?>
				</h1>
			</div>
		</div>
	</section>
<?php else : ?>
	<section class="page-heading" style="background-image: <?php echo esc_url( $args['post-thumbnail'] ); ?>">
		<div class="heading-content">
			<div class="title-area">
				<span class="sale-date">
					<?php echo esc_html( $args['date'] ); ?>
				</span>
				<h1 class="page-title">
					<?php echo esc_html( $args['title'] ); ?>
				</h1>
			</div>
		</div>
	</section>
<?php endif; ?>
