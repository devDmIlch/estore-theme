<?php
/**
 * Recovery page template
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<section class="recovery-section">
	<div class="website-logo">
		<?php the_custom_logo(); ?>
	</div>
	<h1><?php esc_html_e( 'Відновлення Пароля', 'estore-theme' ); ?></h1>
	<div class="login-links">
		<a class="link registration-redirect" href="/login">
			<?php esc_html_e( 'Вхід', 'estore-theme' ); ?>
		</a>
	</div>
	<div class="login-error-message <?php echo esc_attr( ! isset( $args['error_message'] ) ? 'disabled' : '' ); ?>">
		<?php
		if ( isset( $args['error_message'] ) ) {
			echo esc_html( empty( $args['error_message'] ) ? __( 'Невідома помилка', 'estore-theme' ) : $args['error_message'] );
		}
		?>
	</div>
	<div class="login-success-message <?php echo esc_attr( ! isset( $args['success_message'] ) ? 'disabled' : '' ); ?>">
		<?php
		if ( isset( $args['success_message'] ) ) {
			echo esc_html( empty( $args['success_message'] ) ? __( 'Невідоме повідомлення', 'estore-theme' ) : $args['success_message'] );
		}
		?>
	</div>
	<form class="login-form">
		<label class="input-area input-label">
			<span class="label-text"><?php esc_attr_e( 'Електронна пошта', 'estore-theme' ); ?></span>
			<input name="email" class="text-input" type="email">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Невірно вказана пошта!', 'estore-theme' ); ?></span>
		</label>
	</form>
	<button class="recovery-submit submit-button inactive">
		<?php esc_html_e( 'Запросити Відновлення', 'estore-theme' ); ?>
	</button>
</section>
