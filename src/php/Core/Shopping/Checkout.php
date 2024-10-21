<?php
/**
 * Checkout class file template.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Shopping;

/**
 * Checkout class.
 */
class Checkout {

	#region Initialization Methods.

	/**
	 * Class initialization method.
	 */
	public function init(): void { }

	#endregion


	#region Public Methods.

	/**
	 * Loads checkout page template with parameters.
	 */
	public static function load_checkout_page(): void {
		get_template_part( 'template-parts/pages/checkout', args: [] );
	}

	#endregion

}
