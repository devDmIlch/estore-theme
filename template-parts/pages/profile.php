<?php
/**
 * User profile page template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="profile-page page-content">
	<h1><?php esc_html_e( 'Особистий кабінет', 'estore-theme' ); ?></h1>
	<aside class="profile-menu">
		<?php foreach ( $args['menu']['subpages'] as $__slug => $__title ) : ?>
			<?php if ( $__slug === $args['menu']['selected'] ) : ?>
				<a class="subpage-link selected">
					<?php echo esc_html( $__title ); ?>
				</a>
			<?php else : ?>
				<a class="subpage-link" href="<?php echo esc_url( get_site_url() . '/' . $__slug ); ?>">
					<?php echo esc_html( $__title ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</aside>
	<main class="subpage-content">
		<div class="success-message disabled">
			<?php esc_html_e( 'Дані було оновлено.', 'estore-theme' ); ?>
		</div>
		<div class="error-message disabled">
			<?php esc_html_e( 'Під час оновлення даних виникла помилка.', 'estore-theme' ); ?>
		</div>
		<?php get_template_part( 'template-parts/profile/' . $args['menu']['selected'], args: $args['data'] ); ?>
	</main>
</div>
