<?php
/**
 * Store item main subpage template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

// Get variation currently displayed on the page.
$curr_var = $args['post_data']['variations']['data'][ $args['var_index'] ];
// Check whether this variation is available.
$is_available = (int) $curr_var['quantity'] > 0;

?>
<section class="subpage subpage-main active" subpage="main">
	<p class="excerpt desc">
		<?php echo esc_html( $args['post_data']['excerpt'] ); ?>
	</p>
	<?php if ( count( $args['post_data']['variations']['data'] ) > 1 ) : ?>
		<div class="var-selection">
			<?php foreach ( $args['post_data']['variations']['data'] as $var ) : ?>
				<a href="<?php echo esc_url( $args['post_data']['link'] . '?var=' . $var['id'] ); ?>">
					<?php
					$classes = 'single-var';
					if ( (int) $var['id'] === $args['var_id'] ) {
						$classes .= ' current';
					}
					if ( $var['quantity'] < 1 ) {
						$classes .= ' out-of-stock';
					}
					?>
					<div class="<?php echo esc_attr( $classes ); ?>" var="<?php echo esc_attr( $var['id'] ); ?>">
						<?php
						switch ( $args['post_data']['variations']['type'][0] ) {
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
	<div class="price-data">
		<?php if ( ! empty( $curr_var['price_sale'] ) ) : ?>
			<p class="price-old">
				<s>
					<span class="price-old-num">
						<?php echo esc_html( $curr_var['price'] ); ?>
						<span class="price-old-rel">
							<?php echo esc_html( round( - ( 1 - $curr_var['price_sale'] / $curr_var['price'] ) * 100 ) . '%' ); ?>
						</span>
					</span>
				</s>
			</p>
		<?php endif; ?>
		<p class="price-num <?php echo esc_attr( $is_available ? '' : 'inactive' ); ?>">
			<?php echo esc_html( $curr_var['price_sale'] ?? $curr_var['price'] ); ?>
		</p>
	</div>
	<div class="store-item-actions">
		<?php if ( $is_available ) : ?>
			<span class="in-stock"><?php esc_html_e( 'В наявності', 'estore-theme' ); ?></span>
			<a class="add-to-cart action-button">
				<?php esc_html_e( 'Додати до Кошика', 'estore-theme' ); ?>
			</a>
<!--			<a class="quick-buy action-button">-->
<!--				--><?php //esc_html_e( 'Швидка Покупка', 'estore-theme' ); ?>
<!--			</a>-->
		<?php else : ?>
			<a class="inactive out-of-stock action-button">
				<?php esc_html_e( 'Немає в наявності', 'estore-theme' ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
