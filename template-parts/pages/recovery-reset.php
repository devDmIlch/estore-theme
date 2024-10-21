<?php
/**
 * Recovery page template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<section class="recovery-section">
	<div class="website-logo">
		<?php the_custom_logo(); ?>
	</div>
	<h1><?php esc_html_e( 'Відновлення Пароля', 'estore-theme' ); ?></h1>
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
		<label class="input-area input-label disabled">
			<span class="label-text"><?php esc_attr_e( 'Електронна пошта', 'estore-theme' ); ?></span>
			<input name="email" class="text-input" type="email" value="<?php echo esc_attr( $args['user_email'] ?? '' ); ?>">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Невірно вказана пошта!', 'estore-theme' ); ?></span>
		</label>
		<label class="input-area input-label disabled">
			<span class="label-text"><?php esc_attr_e( 'Код відновлення', 'estore-theme' ); ?></span>
			<input name="recovery-code" class="text-input" type="text" value="<?php echo esc_attr( $args['recovery_code'] ?? '' ); ?>">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Невірно вказаний код відновлення!', 'estore-theme' ); ?></span>
		</label>
		<label class="input-area input-label">
			<span class="label-text"><?php esc_attr_e( 'Новий Пароль', 'estore-theme' ); ?></span>
			<input name="password" class="text-input" type="password">
		</label>
		<div class="password-rules disabled">
			<div class="password-strength">
				<div class="password-strength-bar" level="0"></div>
				<?php esc_html_e( 'Надійність пароля: ', 'estore-theme' ); ?>
				<span class="password-strength-level invalid"><?php esc_html_e( 'ненадійний', 'estore-theme' ); ?></span>
				<span class="password-strength-level low"><?php esc_html_e( 'низька', 'estore-theme' ); ?></span>
				<span class="password-strength-level medium"><?php esc_html_e( 'середня', 'estore-theme' ); ?></span>
				<span class="password-strength-level high"><?php esc_html_e( 'висока', 'estore-theme' ); ?></span>
				<span class="password-strength-level formidable"><?php esc_html_e( 'надійний', 'estore-theme' ); ?></span>
			</div>
			<p class="rule valid-length"><?php esc_html_e( 'Пароль повинен містити 8-30 символів', 'estore-theme' ); ?></p>
			<p class="rule contains-number"><?php esc_html_e( 'Пароль повинен містити цифру', 'estore-theme' ); ?></p>
			<p class="rule contains-lowercase"><?php esc_html_e( 'Пароль повинен містити маленьку літеру', 'estore-theme' ); ?></p>
			<p class="rule contains-uppercase"><?php esc_html_e( 'Пароль повинен містити велику літеру', 'estore-theme' ); ?></p>
			<p class="rule contains-latin"><?php esc_html_e( 'Пароль повинен містити лише латинські літери, цифри та спеціальні символи', 'estore-theme' ); ?></p>
		</div>
		<label class="input-area input-label">
			<span class="label-text"><?php esc_attr_e( 'Повторіть Пароль', 'estore-theme' ); ?></span>
			<input name="password-repeat" class="text-input" type="password">
			<span class="missing-field-notice disabled"><?php esc_html_e( 'Паролі не співпадають!', 'estore-theme' ); ?></span>
		</label>
	</form>
	<button class="recovery-submit password-reset submit-button inactive">
		<?php esc_html_e( 'Запросити Відновлення', 'estore-theme' ); ?>
	</button>
</section>
