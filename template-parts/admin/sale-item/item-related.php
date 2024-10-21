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

?>
<div class="preferences-section">
	<div class="estore-metabox-row">
		<div class="estore-metabox-column">
			<div class="estore-metabox-subsection">
				<div class="post-selector multi-selector">
					<input type="hidden" class="input-selected" name="related-posts" id="related-posts" value="<?php echo esc_attr( implode( ',', $args['selected'] ) ); ?>">
					<div class="selection-area-wrap">
						<input type="text" class="input-search" placeholder="<?php esc_attr_e( 'Пошук по товарам', 'estore-theme' ); ?>">
						<div class="selection-area">
							<?php foreach ( $args['posts'] as $post_obj ) : ?>
								<div class="option <?php echo esc_attr( in_array( (string) $post_obj->ID, $args['selected'], true ) ? 'active' : '' ); ?>" value="<?php echo esc_attr( $post_obj->ID ); ?>">
									<?php echo esc_html( $post_obj->post_title ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="selected-values">
						<?php
						foreach ( $args['selected'] as $__post_id ) {
							get_template_part( 'template-parts/admin/store-item/post', 'controls', [ 'post_data' => \EStore\PostTypes\StoreItem::get_store_item_data( $__post_id ) ] );
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
