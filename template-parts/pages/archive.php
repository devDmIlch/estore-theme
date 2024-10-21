<?php
/**
 * Archive page template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<section class="page-content">
	<h1><?php echo esc_html( $args['title'] ); ?></h1>
	<?php
	// Render loader to avoid awkward "underloaded" state.
	get_template_part( 'template-parts/components/archive', 'loader' );
	// Render active components of the archive page.
	get_template_part( 'template-parts/archive/controls', args: array_merge( $args, [ 'class' => 'initial-load' ] ) );
	?>
</section>
