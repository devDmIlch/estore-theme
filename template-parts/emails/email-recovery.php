<?php
/**
 * Password recovery email body template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
	<h1 class="email-title">
		<?php esc_html_e( 'Відновлення пароля', 'estore-theme' ); ?>
	</h1>
	<p>
		<?php
		/* translators: %s in this instance refers to the name of the website */
		echo esc_html( sprintf( __( 'Ви щойно запросили відновлення пароля до вашого акаунта на %s. Перейдіть за посиланням нижче, щоб оновити пароль.', 'estore-theme' ), $args['site_name'] ) );
		?>
	</p>
	<p>
		<a href="<?php echo esc_url( $args['recovery-link'] ); ?>">
			<?php esc_html_e( 'Оновити пароль', 'estore-theme' ); ?>
		</a>
	</p>
	<p>
		<?php esc_html_e( 'Якщо ви не створювали запит для відновлення пароля, проігноруйте цей лист', 'estore-theme' ); ?>
	</p>
<?php
