<?php
/**
 * Post Type Controller class template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\PostTypes;

/**
 * Post type controller class for the estore theme
 */
class PostTypeController {

	/**
	 * 'store-item' post type controller.
	 * @var StoreItem $store_item
	 */
	private StoreItem $store_item;

	/**
	 * 'store-item' post type controller.
	 * @var SaleItem $sale_page
	 */
	private SaleItem $sale_page;


	#region Initializing Methods.

	/**
	 * Initializing method for the class.
	 */
	public function init(): void {
		// Initialize Store Items.
		$this->store_item = new StoreItem();
		$this->store_item->init();

		// Initialize Sale Pages.
		$this->sale_page = new SaleItem();
		$this->sale_page->init();

		// Initialize Orders.
		$order_controller = new Order();
		$order_controller->init();
	}

	#endregion.

	/**
	 * Loads single page based on global post type.
	 */
	public function load_single_page(): void {
		switch ( get_post_type() ) {
			case $this->store_item->get_slug():
				$this->store_item->load_single_page();
				break;
			case $this->sale_page->get_slug():
				$this->sale_page->load_single_page();
				break;
		}
	}
}
