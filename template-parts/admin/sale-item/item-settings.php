<?php
/**
 * Sale item 'general settings' template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

$sale_active = 'on' === $args['sale-active'];

?>
<div class="preferences-section">
	<div class="estore-metabox-row">
		<div class="estore-metabox-column fixed-width">
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Розпродаж Активний', 'estore-theme' ); ?>
					</span>
					<input type="checkbox" name="sale-active" id="sale-active" class="<?php echo esc_attr( $sale_active ? 'active' : '' ); ?>" <?php echo esc_attr( $sale_active ? 'checked' : '' ); ?>>
				</label>
			</div>
		</div>
		<div class="estore-metabox-column">
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Колір Сторінки Розпродажу', 'estore-theme' ); ?>
					</span>
					<input type="color" name="bg-color" id="bg-color" value="<?php echo esc_attr( $args['sale-bg-color'] ); ?>">
				</label>
			</div>
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Дата Початку Розпродажу', 'estore-theme' ); ?>
					</span>
					<input type="date" name="sale-date-start" id="sale-date-start" value="<?php echo esc_attr( $args['sale-date-start'] ); ?>" required>
				</label>
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Дата Закінчення Розпродажу', 'estore-theme' ); ?>
					</span>
					<input type="date" name="sale-date-end" id="sale-date-end" value="<?php echo esc_attr( $args['sale-date-end'] ); ?>" required>
				</label>
<!--				<label class="estore-metabox-field inline-label">-->
<!--					<span class="field-name">-->
<!--						--><?php //esc_html_e( 'Деактивувати Розпродаж Після Закінчення', 'estore-theme' ); ?>
<!--					</span>-->
<!--					<input class="active" type="checkbox" name="sale-disable-on-end" id="sale-disable-on-end" checked>-->
<!--				</label>-->
			</div>
		</div>
	</div>
</div>
