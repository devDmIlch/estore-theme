<?php
/**
 * Settings subpage of profile page.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<section class="details-customer">
	<h2><?php esc_html_e( 'Оновити пошту', 'estore-theme' ); ?></h2>
	<form class="user-email">
		<label class="input-label full-width disabled">
			<span class="label-text"><?php esc_html_e( 'Поточна пошта', 'estore-theme' ); ?></span>
			<input class="text-input" type="text" value="<?php echo esc_html( $args['email'] ); ?>" disabled>
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Нова Пошта', 'estore-theme' ); ?></span>
			<input class="text-input" id="email" name="email" type="text">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Пароль', 'estore-theme' ); ?></span>
			<input class="text-input" id="password" name="password" type="password">
		</label>
	</form>
	<div class="save-button-wrap">
		<a class="save-button save-email">
			<?php esc_html_e( 'Оновити Пошту', 'estore-theme' ); ?>
		</a>
	</div>
</section>
