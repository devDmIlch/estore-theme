<?php
/**
 * Settings metabox for order post-type template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="preferences-section">
	<div class="estore-metabox-row">
		<div class="estore-metabox-column fixed-width">
			<div class="estore-metabox-subsection">
				<label class="estore-metabox-field inline-label">
						<span class="field-name">
							<?php esc_html_e( 'Статус замовлення', 'estore-theme' ); ?>
						</span>
					<select name="order-status" id="order-status">
						<?php foreach ( $args['status_list'] as $value => $name ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $value === (int) $args['details']['status'] ? 'selected' : '' ); ?>>
								<?php echo esc_html( $name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		</div>
		<div class="estore-metabox-column">
			<div class="estore-metabox-subsection">
				<h4><?php esc_html_e( 'Замовник', 'estore-theme' ); ?></h4>
				<?php if ( ! empty( $args['details']['user'] ) ) : ?>
					<div class="boxed">
						<p class="customer-name">
							<b class="field-title">
								<?php esc_html_e( 'Ім\'я: ', 'estore-theme' ); ?>
							</b>
							<?php echo esc_html( $args['details']['user']['first_name'] . ' ' . $args['details']['user']['last_name'] . ' ' . $args['details']['user']['middle_name'] ); ?>
						</p>
						<p class="customer-email">
							<b class="field-title">
								<?php esc_html_e( 'Пошта: ', 'estore-theme' ); ?>
							</b>
							<?php echo esc_html( $args['details']['user']['email'] ); ?>
						</p>
						<p class="customer-phone">
							<b class="field-title">
								<?php esc_html_e( 'Телефон: ', 'estore-theme' ); ?>
							</b>
							<?php echo esc_html( $args['details']['user']['phone'] ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
			<div class="estore-metabox-subsection">
				<h4><?php esc_html_e( 'Товари', 'estore-theme' ); ?></h4>
				<?php if ( isset( $args['details']['items'] ) ) : ?>
					<div class="boxed">
						<?php foreach ( $args['details']['items'] as $item ) : ?>
							<p>
								<?php echo esc_html( $item ); ?>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="estore-metabox-subsection">
				<h4><?php esc_html_e( 'Доставка', 'estore-theme' ); ?></h4>
				<div class="boxed">
					Тут повинна бути інформація про доставку
				</div>
			</div>
		</div>
	</div>
</div>
