<?php
/**
 * Store item card template
 *
 * @package estore/theme
 * @since   0.0.1
 */

// This template requires parameters.
if ( empty( $args ) ) {
	return;
}

$possible_tags = [
	'sale' => __( 'Акція', 'estore-theme' ),
	'new'  => __( 'Новинка', 'estore-theme' ),
	'hot'  => __( 'Гарячий товар', 'estore-theme' ),
];

?>
<div class="card-store-item">
	<?php if ( 'none' !== $args['tag'][0] ) : ?>
		<div class="item-tag" type="<?php echo esc_attr( $args['tag'][0] ); ?>">
			<span class="tag-text">
				<?php echo esc_html( $possible_tags[ $args['tag'][0] ] ); ?>
			</span>
		</div>
	<?php endif; ?>
	<div class="item-thumbnail">
		<a href="<?php echo esc_url( $args['link'] ); ?>" title="<?php esc_attr_e( 'Посилання на товар', 'estore-theme' ); ?>">
			<img src="<?php echo esc_url( $args['thumbnails'][0] ); ?>" alt="<?php esc_html_e( 'Зображення товару', 'estore-theme' ); ?>">
		</a>
	</div>
	<div class="item-details">
		<h2 class="item-name">
			<a href="<?php echo esc_url( $args['link'] ); ?>" title="<?php esc_attr_e( 'Посилання на товар', 'estore-theme' ); ?>">
				<?php echo esc_html( $args['title'] ); ?>
			</a>
		</h2>
		<?php if ( count( $args['variations']['data'] ) > 1 ) : ?>
			<div class="item-variations">
				<?php foreach ( $args['variations']['data'] as $var ) : ?>
					<a href="<?php echo esc_url( $args['link'] . '?var=' . $var['id'] ); ?>">
						<div class="single-var" var="<?php echo esc_attr( $var['id'] ); ?>">
							<?php
							switch ( $args['variations']['type'][0] ) {
								case 'name':
									?>
									<div class="var-name">
										<?php echo esc_html( $var['name'] ); ?>
									</div>
									<?php
									break;
								case 'color':
									?>
									<div class="var-color" style="background-color: <?php echo esc_attr( $var['colour'] ); ?>"></div>
									<?php
									break;
								case 'name-color':
									?>
									<div class="var-color" style="background-color: <?php echo esc_attr( $var['color'] ); ?>"></div>
									<div class="var-name">
										<?php echo esc_html( $var['name'] ); ?>
									</div>
									<?php
									break;
							}
							?>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( $args['score'] ) : ?>
			<div class="item-score">

			</div>
		<?php endif; ?>
		<div class="item-price">
			<?php foreach ( $args['variations']['data'] as $var ) : ?>
				<div class="var-price" var="<?php echo esc_attr( $var['id'] ); ?>">
					<?php if ( $var['price_sale'] ) : ?>
						<p class="price-old">
							<s>
								<span class="price-old-num">
									<?php echo esc_html( $var['price'] ); ?>
									<span class="price-old-rel">
										<?php echo esc_html( round( - ( 1 - $var['price_sale'] / $var['price'] ) * 100 ) . '%' ); ?>
									</span>
								</span>
							</s>
						</p>
					<?php endif; ?>
					<p class="price-num">
						<?php echo esc_html( $var['price_sale'] ?? $var['price'] ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="item-link">
			<a href="<?php echo esc_url( $args['link'] ); ?>" title="<?php esc_attr_e( 'Посилання на товар', 'estore-theme' ); ?>">
			</a>
		</div>
	</div>
</div>
