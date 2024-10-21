<?php
/**
 * Main subpage of profile page.
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
	<h2><?php esc_html_e( 'Особисті дані', 'estore-theme' ); ?></h2>
	<form class="user-data">
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Ім\'я', 'estore-theme' ); ?></span>
			<input class="text-input" id="first-name" name="first-name" type="text" value="<?php echo esc_html( $args['user']['first-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Прізвище', 'estore-theme' ); ?></span>
			<input class="text-input" id="last-name" name="last-name" type="text" value="<?php echo esc_html( $args['user']['last-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'По-батькові', 'estore-theme' ); ?></span>
			<input class="text-input" id="middle-name" name="middle-name" type="text" value="<?php echo esc_html( $args['user']['middle-name'] ); ?>">
		</label>
		<label class="input-label">
			<span class="label-text"><?php esc_html_e( 'Телефон', 'estore-theme' ); ?></span>
			<input class="text-input" id="phone" name="phone" type="tel" value="<?php echo esc_html( $args['user']['phone'] ); ?>">
		</label>
	</form>
	<div class="save-button-wrap">
		<a class="save-button save-user">
			<?php esc_html_e( 'Зберегти Дані', 'estore-theme' ); ?>
		</a>
	</div>
</section>
