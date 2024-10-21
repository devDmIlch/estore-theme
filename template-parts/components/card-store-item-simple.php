<?php
/**
 * Store item simplified card template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires parameters.
if ( empty( $args ) ) {
	return;
}

?>
<div class="card-store-item simple">
	<div class="item-thumbnail">
		<a href="<?php echo esc_url( $args['link'] ); ?>" title="<?php esc_attr_e( 'Посилання на товар', 'estore-theme' ); ?>">
			<img src="<?php echo esc_url( $args['thumbnails'][0] ); ?>" alt="<?php esc_html_e( 'Зображення товару', 'estore-theme' ); ?>">
		</a>
	</div>
	<div class="item-details">
		<h4 class="item-name">
			<?php echo esc_html( $args['title'] ); ?>
		</h4>
		<div class="item-variations">
			<div class="single-var" var="<?php echo esc_attr( $args['variations']['data']['id'] ); ?>">
				<?php
				switch ( $args['variations']['type'][0] ) {
					case 'name':
						?>
						<div class="var-name">
							<?php echo esc_html( $args['variations']['data']['name'] ); ?>
						</div>
						<?php
						break;
					case 'color':
						?>
						<div class="var-color" style="background-color: <?php echo esc_attr( $args['variations']['data']['colour'] ); ?>"></div>
						<?php
						break;
					case 'name-color':
						?>
						<div class="var-color" style="background-color: <?php echo esc_attr( $args['variations']['data']['color'] ); ?>"></div>
						<div class="var-name">
							<?php echo esc_html( $args['variations']['data']['name'] ); ?>
						</div>
						<?php
						break;
				}
				?>
			</div>
		</div>
	</div>
</div>
