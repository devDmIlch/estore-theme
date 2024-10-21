<?php
/**
 * Activation email body template.
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
	<?php esc_html_e( 'Активація Акаунта', 'estore-theme' ); ?>
</h1>
<p>
	<?php
	/* translators: %s in this instance refers to the name of the website */
	echo esc_html( sprintf( __( 'Ви щойно зареєструвались на %s, для підтрердження пошти на активації акаунту перейдіть по посиланню нижче.', 'estore-theme' ), $args['site_name'] ) );
	?>
</p>
<p>
	<a href="<?php echo esc_url( $args['activation-link'] ); ?>">
		<?php esc_html_e( 'Активувати Акаунт', 'estore-theme' ); ?>
	</a>
</p>
<p>
	<?php esc_html_e( 'Якщо ви не реєструвались на сайті, можете просто проігнорувати цей лист', 'estore-theme' ); ?>
</p>
