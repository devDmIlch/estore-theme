<?php
/**
 * Login page template
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<section class="login-section">
	<div class="website-logo">
		<?php the_custom_logo(); ?>
	</div>
	<h1><?php esc_html_e( 'Вхід', 'estore-theme' ); ?></h1>
	<div class="login-links">
		<a class="link registration-redirect" href="/register">
			<?php esc_html_e( 'Зареєструватись', 'estore-theme' ); ?>
		</a>
		<a class="link password-recovery" href="/recovery">
			<?php esc_html_e( 'Забули Пароль?', 'estore-theme' ); ?>
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
			<input name="email" class="text-input" type="email" value="<?php echo esc_attr( $args['user_email'] ?? '' ); ?>">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Невірно вказана пошта!', 'estore-theme' ); ?></span>
		</label>
		<label class="input-area input-label">
			<span class="label-text"><?php esc_attr_e( 'Пароль', 'estore-theme' ); ?></span>
			<input name="password" class="text-input" type="password">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Необхідно вказати пароль!', 'estore-theme' ); ?></span>
		</label>
	</form>
	<button class="login-submit submit-button inactive">
		<?php esc_html_e( 'Увійти', 'estore-theme' ); ?>
	</button>
</section>
