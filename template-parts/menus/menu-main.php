<?php
/**
 * Main menu template
 *
 * @package estore/theme
 * @since 0.0.1
 */

// Get Item Categories.
$categories = \EStore\Taxonomies\TaxonomyController::get_term_related_terms( 0, 'item-category' )['item-category'];

?>
<div class="main-menu">
	<?php
	// Create a flag to close a dropdowns.
	$is_sub_category = false;

	foreach ( $categories as $__cat_id => $cat_data ) {
		// Omit any categories beyond level 1.
		if ( $cat_data['depth'] > 1 ) {
			continue;
		}

		if ( $cat_data['depth'] > 0 ) {
			if ( ! $is_sub_category ) {
				$is_sub_category = true;
				?>
				<div class="dropdown-wrap">
					<div class="category-dropdown dropdown-target" relation="<?php echo esc_attr( $cat_data['parent'] ); ?>">
				<?php
			}
			?>
			<div class="category-item">
				<a class="archive-link" href="<?php echo esc_url( get_term_link( $__cat_id ) ); ?>">
					<?php echo esc_html( $cat_data['name'] ); ?>
				</a>
			</div>
			<?php
		}

		if ( $cat_data['depth'] < 1 ) {
			if ( $is_sub_category ) {
				$is_sub_category = false;
				?>
					</div>
				</div>
				<?php
			}
			?>
			<div class="category-item parent-category" relation="<?php echo esc_attr( $__cat_id ); ?>">
				<a class="archive-link" href="<?php echo esc_url( get_term_link( $__cat_id ) ); ?>">
					<?php echo esc_html( $cat_data['name'] ); ?>
				</a>
				<?php if ( $cat_data['has_children'] ) : ?>
					<span class="dropdown-trigger dropdown-ico" relation="<?php echo esc_attr( $__cat_id ); ?>"></span>
				<?php endif; ?>
			</div>
			<?php
		}
	}
	?>
</div>
