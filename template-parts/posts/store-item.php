<?php
/**
 * Store item single post template
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
	<h1><?php echo esc_html( $args['post_data']['title'] ); ?></h1>
	<div class="content-data">
		<div class="thumbnail-area">
			<div class="var-slider splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $args['post_data']['variations']['data'][ $args['var_index'] ]['attachments'] as $img_src ) : ?>
							<li class="single-slide splide__slide">
								<img class="store-item-picture" src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_html( __( 'Зображення Товару: ', 'estore-theme' ) . $args['post_data']['title'] ); ?>" />
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php if ( count( $args['post_data']['thumbnails'] ) > 1 ) : ?>
				<div class="var-slider-nav splide">
					<div class="splide__track">
						<ul class="splide__list">
							<?php foreach ( $args['post_data']['thumbnails'] as $img_src ) : ?>
								<li class="single-slide splide__slide">
									<img class="nav-item-picture" src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_html( __( 'Зображення Товару: ', 'estore-theme' ) . $args['post_data']['title'] ); ?>" />
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="details-area">
			<div class="store-item-menu">
				<?php foreach ( $args['sub_pages'] as $slug => $name ) : ?>
					<div class="sub-nav <?php echo esc_attr( array_key_first( $args['sub_pages'] ) === $slug ? 'active' : '' ); ?>" option="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $name ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php array_walk( $args['sub_pages'], static fn ( $value, $key ) => get_template_part( 'template-parts/posts/store-item', $key, $args ) ); ?>
		</div>
	</div>
</section>
