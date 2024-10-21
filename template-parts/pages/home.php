<?php
/**
 * Home Page Template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="home-page">
	<aside class="home-menu">
		<?php get_template_part( 'template-parts/menus/menu', 'main' ); ?>
	</aside>
	<main class="home-content">
		<?php if ( ! empty( $args['sale_posts'] ) ) : ?>
		<section class="sales-carousel">
			<div class="sales-slider splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $args['sale_posts'] as $post_data ) : ?>
							<li class="single-slide splide__slide" href="<?php echo esc_url( $post_data['link'] ); ?>">
								<?php get_template_part( 'template-parts/posts/sale-thumbnail', args: $post_data ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>
		<?php endif; ?>
		<?php if ( ! empty( $args['categories'] ) ) : ?>
			<section class="home-categories">
				<h2 class="section-title">
					<?php esc_html_e( 'Категорії', 'estore-theme' ); ?>
				</h2>
				<?php foreach ( $args['categories'] as $__term ) : ?>
					<a class="category-link" href="<?php echo esc_url( $__term['link'] ); ?>">
						<div class="category">
							<?php if ( ! empty( $__term['icon'] ) ) : ?>
								<img class="category-icon" src="<?php echo esc_url( wp_get_attachment_image_url( $__term['icon'], 'medium' ) ); ?>">
							<?php endif; ?>
							<span class="text">
								<?php echo esc_html( $__term['name'] ); ?>
							</span>
						</div>
					</a>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>
		<?php if ( ! empty( $args['brands'] ) ) : ?>
			<section class="home-categories">
				<h2 class="section-title">
					<?php esc_html_e( 'Бренди', 'estore-theme' ); ?>
				</h2>
				<?php foreach ( $args['brands'] as $__term ) : ?>
					<a class="category-link" href="<?php echo esc_url( $__term['link'] ); ?>">
						<div class="category">
							<img class="category-icon" src="<?php echo esc_url( wp_get_attachment_image_url( $__term['icon'], 'medium' ) ); ?>">
							<span class="text">
								<?php echo esc_html( $__term['name'] ); ?>
							</span>
						</div>
					</a>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>
	</main>
</div>
