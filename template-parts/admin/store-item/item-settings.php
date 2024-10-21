<?php
/**
 * Store item 'general settings' template
 *
 * @package estore/theme
 * @since 0.0.1
 */

if ( ! isset( $args ) ) {
	return;
}

$possible_tags = [
	'none' => '—',
	'sale' => __( 'Акція', 'estore-theme' ),
	'new'  => __( 'Новинка', 'estore-theme' ),
	'hot'  => __( 'Гарячий товар', 'estore-theme' ),
];

$var_display_types = [
	'name'       => __( 'Назва', 'estore-theme' ),
	'color'      => __( 'Колір', 'estore-theme' ),
	'name-color' => __( 'Колір + Назва', 'estore-theme' ),
];

// Assign default values if none were passed.
$args['estore-tag']      ??= array_key_first( $possible_tags );
$args['estore-var-type'] ??= array_key_first( $var_display_types );

?>
<div class="preferences-section">
	<div class="estore-metabox-row">
		<div class="estore-metabox-column">
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field block-label" for="item-desc">
					<span class="field-name large editor-name">
						<b><?php esc_html_e( 'Опис Товару', 'estore-theme' ); ?></b>
					</span>
					<?php wp_editor( $args['estore-desc'] ?? '', 'item-desc' ); ?>
				</label>
			</div>
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Позначка', 'estore-theme' ); ?>
					</span>
					<select id="item-tag" name="item-tag">
						<?php foreach ( $possible_tags as $name => $label ) : ?>
							<option value="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $name === $args['estore-tag'] ? 'selected' : '' ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="estore-metabox-field inline-label">
					<span class="field-name">
						<?php esc_html_e( 'Зображення Варіацій', 'estore-theme' ); ?>
					</span>
					<select id="var-type" name="var-type">
						<?php foreach ( $var_display_types as $name => $label ) : ?>
							<option value="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $name === $args['estore-var-type'] ? 'selected' : '' ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		</div>
	</div>
</div>
