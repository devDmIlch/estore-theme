<?php
/**
 * Sale item single post template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

get_template_part( 'template-parts/posts/sale-thumbnail', args: $args );
?>

<section class="page-content sale-content shrink">
	<?php the_content(); ?>
</section>

<section class="page-related">
	<?php get_template_part( 'template-parts/archive/controls', null, $args['related-archive'] ); ?>
</section>
