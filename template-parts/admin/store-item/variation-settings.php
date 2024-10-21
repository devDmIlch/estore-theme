<?php
/**
 * Store item single variation settings template
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<div class="variation-item">
	<div class="var-controls">
		<div class="var-del">
			&#10006;
		</div>
		<div class="var-move-up">
			&#11165;
		</div>
		<div class="var-move-down">
			&#11167;
		</div>
	</div>
	<div class="estore-metabox-row">
		<div class="estore-metabox-column">
			<label class="estore-metabox-field" for="var-name">
				<span class="field-name large">
					<b><?php esc_html_e( 'Назва Підвиду', 'estore-theme' ); ?></b>
				</span>
				<input type="text" id="var-name" name="var-name[]" value="<?php echo esc_attr( $args['name'] ?? '' ); ?>" required>
			</label>
			<label class="estore-metabox-field" for="var-color">
				<span class="field-name">
					<?php esc_html_e( 'Колір Підвиду', 'estore-theme' ); ?>
				</span>
				<input type="color" id="var-color" name="var-color[]" value="<?php echo esc_attr( $args['colour'] ?? '#ffffff' ); ?>">
			</label>
			<input type="hidden" name="var-id[]" value="<?php echo esc_attr( $args['id'] ?? '' ); ?>">
		</div>
	</div>
	<div class="estore-metabox-row">
		<div class="estore-metabox-column fixed-width">
			<div class="var-images estore-gallery-selector">
				<div class="image-list media-selected" relation="var-img">
				</div>
				<a class="add-prompt link gallery-selector-trigger" relation="var-img">
					<?php esc_html_e( 'Додати Зображення Товару' ); ?>
				</a>
				<input type="hidden" id="var-img" name="var-img[]" value="<?php echo esc_attr( $args['attachments'] ?? '' ); ?>">
			</div>
		</div>
		<div class="estore-metabox-column">
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field" for="var-price">
					<span class="field-name">
						<?php esc_html_e( 'Ціна', 'estore-theme' ); ?>
						<span class="required">*</span>
					</span>
					<input type="number" min="0" step="0.01" id="var-price" name="var-price[]" value="<?php echo esc_attr( $args['price'] ?? '' ); ?>" required>
				</label>
				<label class="estore-metabox-field" for="var-price-sale">
					<span class="field-name">
						<?php esc_html_e( 'Акційна ціна', 'estore-theme' ); ?>
					</span>
					<input type="number" min="0" step="0.01" id="var-price-sale" name="var-price-sale[]" value="<?php echo esc_attr( $args['price_sale'] ?? '' ); ?>">
				</label>
			</div>
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field" for="var-quantity-available">
					<span class="field-name">
						<?php esc_html_e( 'Кількість Доступно', 'estore-theme' ); ?>
						<span class="required">*</span>
					</span>
					<input type="number" min="0" id="var-quantity-available" name="var-quantity-available[]" value="<?php echo esc_attr( $args['quantity'] ?? '' ); ?>" required>
				</label>
				<label class="estore-metabox-field" for="var-quantity-available">
					<span class="field-name">
						<?php esc_html_e( 'Кількість Зарезервовано', 'estore-theme' ); ?>
					</span>
					<input type="number" min="0" id="var-quantity-reserved" name="var-quantity-reserved[]" value="<?php echo esc_attr( $args['quantity_rsrv'] ?? '' ); ?>" disabled>
				</label>
				<label class="estore-metabox-field" for="var-quantity-sold">
					<span class="field-name">
						<?php esc_html_e( 'Кількість Продано', 'estore-theme' ); ?>
					</span>
					<input type="number" min="0" id="var-quantity-sold" name="var-quantity-sold[]" value="<?php echo esc_attr( $args['quantity-sold'] ?? '' ); ?>" disabled>
				</label>
			</div>
		</div>
	</div>
</div>
