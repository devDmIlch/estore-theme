<?php
/**
 * Term thumbnail selector template file
 *
 * @package estore/theme
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<tr class="form-field term-meta-text-wrap">
	<th scope="row"><label for="term-meta-text"><?php echo esc_html( $args['title'] ?? __( 'Зображення', 'estore-theme' ) ); ?></label></th>
	<td>
		<div class="term-thumbnail-selector">
			<a class="button gallery-selector-trigger" relation="<?php echo esc_attr( $args['name'] ); ?>">
				<?php esc_html_e( 'Додати Зображення' ); ?>
			</a>
			<div class="image-list media-selected" relation="<?php echo esc_attr( $args['name'] ); ?>">
				<?php if ( ! empty( $args['value'] ) ) : ?>
					<img class="selected-image" src="<?php echo esc_url( wp_get_attachment_image_url( $args['value'] ) ); ?>" alt="<?php esc_attr_e( 'Вибране зображення таксономії', 'estore-theme' ); ?>">
				<?php else : ?>
					<span><?php esc_html_e( 'Зображення не вибране', 'estore-theme' ); ?></span>
				<?php endif; ?>
			</div>
			<input type="hidden" id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $args['value'] ?? '' ); ?>">
		</div>
	</td>
</tr>