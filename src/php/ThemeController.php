<?php
/**
 * Theme Controller class for the EStore theme
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore;

use EStore\Core\Archive\Archive;
use EStore\Core\Shopping\Cart;
use EStore\Core\Users\UserController;
use EStore\PostTypes\PostTypeController;
use EStore\PostTypes\StoreItem;
use EStore\Taxonomies\TaxonomyController;

/**
 * Theme Controller class.
 */
class ThemeController {

	#region Private Fields.

	/**
	 * Compile folder of the theme.
	 * @var string $build_folder
	 */
	private string $build_folder;

	/**
	 * Prefix of theme styles and scripts
	 * @var string $theme_prefix
	 */
	private string $theme_prefix;

	/**
	 * Post type controller of the theme.
	 * @var PostTypeController $theme_post_types
	 */
	private static PostTypeController $theme_post_types;

	/**
	 * Archive controller class instance.
	 * @var Archive $archive_controller
	 */
	private static Archive $archive_controller;

	#endregion


	#region Public Properties.

	/**
	 * Returns post type controller for the theme.
	 * @return PostTypeController instance of initialized PostTypeController class.
	 */
	public static function get_post_type_controller(): PostTypeController {
		return self::$theme_post_types;
	}

	/**
	 * Returns archive controller for the theme.
	 * @return Archive instance of initialized Archive class.
	 */
	public static function get_archive_controller(): Archive {
		return self::$archive_controller;
	}

	#endregion


	#region Construction Methods.

	/**
	 * Initializing method for the class.
	 */
	public function init(): void {
		// Set theme variables.
		$this->build_folder = get_stylesheet_directory_uri() . '/assets/build/';
		$this->theme_prefix = 'estore_';

		// Initialize Post Types.
		self::$theme_post_types = new PostTypeController();
		self::$theme_post_types->init();

		// Initialize Taxonomies.
		$theme_taxonomies = new TaxonomyController();
		$theme_taxonomies->init();

		// Initialize features for the theme.
		$this->init_theme_supported_features();

		// Initialize the archives.
		self::$archive_controller = new Archive();
		self::$archive_controller->init();

		// Initialize user functionality.
		$user_controller = new UserController();
		$user_controller->init();

		// Initialize cart functionality.
		$cart_controller = new Cart();
		$cart_controller->init();

		// Register Menus.
		$this->register_theme_menus();

		// Initialize hooks.
		$this->hooks();
	}

	/**
	 * Hooks initialization method for the class.
	 */
	protected function hooks(): void {
		// Create necessary tables on theme change.
		add_action( 'after_switch_theme', [ $this, 'set_up_theme_tables' ] );

		// Enqueue style and script files.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_theme_files' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_theme_admin_files' ] );

		// Add actions to customize wp-admin.
		add_action( 'admin_menu', [ $this, 'adjust_wp_admin_menu' ], 99 );
	}


	#endregion


	#region Private Methods.


	#endregion


	#region Public Methods.

	/**
	 * Adjusts position of the elements in the wp-admin menu.
	 */
	public function adjust_wp_admin_menu(): void {
		// Remove default comments page from wp-admin.
		remove_menu_page( 'edit-comments.php' );
		// Adds separator before pages.
		$this->add_admin_menu_separator( 12 );
		// Adds separator before CPTs.
		$this->add_admin_menu_separator( 24 );
	}

	/**
	 * Initializes theme supported features.
	 */
	public function init_theme_supported_features(): void {
		add_theme_support( 'menus' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'custom-logo',
			[
				'height' => 480,
				'width'  => 720,
			]
		);
	}

	/**
	 * Adds separator to wp-admin menu at position.
	 *
	 * @param int $pos position in the menu.
	 */
	private function add_admin_menu_separator( int $pos ): void {
		global $menu;
		$index = 0;
		foreach ( $menu as $offset => $section ) {
			if ( str_starts_with( $section[2], 'separator' ) ) {
				$index++;
			}
			if ( $offset >= $pos ) {
				$menu[ $pos ] = [ '', 'read', "separator{$index}", '', 'wp-menu-separator' ]; // phpcs:ignore
				break;
			}
		}
		ksort( $menu );
	}

	/**
	 * Registers theme menus.
	 */
	public function register_theme_menus(): void {
		register_nav_menus(
			[
				'footer-menu-1' => __( 'Ліве меню в футері', 'estore-theme' ),
				'footer-menu-2' => __( 'Центральне меню в футері', 'estore-theme' ),
				'footer-menu-3' => __( 'Праве меню в футері', 'estore-theme' ),
			]
		);
	}

	/**
	 * Checks and creates necessary tables in the DB for a theme to work.
	 */
	public function set_up_theme_tables(): void {
		global $wpdb;
		// Get variations table name.
		$variations_table_name = $wpdb->prefix . StoreItem::get_table_name();
		// Check whether the table exists in the DB.
		$table_exists = count( $wpdb->get_results( "SHOW TABLES LIKE '$variations_table_name'", ARRAY_N ) ) > 0; // phpcs:ignore
		// Create DB table for the product variations.
		if ( ! $table_exists ) {
			$wpdb->query( "CREATE TABLE $variations_table_name ( id INTEGER NOT NULL AUTO_INCREMENT, post_id INTEGER NOT NULL, name TINYTEXT, quantity INTEGER, quantity_rsrv INTEGER, quantity_sold INTEGER, price DECIMAL(10, 2) NOT NULL, price_sale DECIMAL(10, 2), attachments TEXT, colour VARCHAR(8), reviews TEXT, score DECIMAL(1, 1), pos TINYINT, status BIT, PRIMARY KEY (id) ) " ); // phpcs:ignore
		}
	}

	/**
	 * Enqueues theme files for front end.
	 */
	public function enqueue_theme_files(): void {
		// Enqueue nonces for JS.
		wp_enqueue_script( 'wp-api' );
		wp_localize_script(
			'wp-api',
			'wpApiSettings',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			]
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			$this->theme_prefix . 'script',
			$this->build_folder . 'custom.js',
			[ 'wp-i18n' ],
			ESTORE_THEME_VERSION,
			true,
		);

		// Enqueue Styles.
		wp_enqueue_style(
			$this->theme_prefix . 'styles',
			$this->build_folder . 'styles.css',
			[],
			ESTORE_THEME_VERSION,
			'all',
		);
	}

	/**
	 * Enqueues theme files for admin pages.
	 */
	public function enqueue_theme_admin_files(): void {
		// Enqueue media to allow media selection.
		wp_enqueue_media();

		// Enqueue nonces for JS.
		wp_enqueue_script( 'wp-api' );
		wp_localize_script(
			'wp-api',
			'wpApiSettings',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			]
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			$this->theme_prefix . 'admin_script',
			$this->build_folder . 'admin.js',
			[ 'wp-i18n' ],
			ESTORE_THEME_VERSION,
			true,
		);

		// Enqueue Styles.
		wp_enqueue_style(
			$this->theme_prefix . 'admin_styles',
			$this->build_folder . 'styles-admin.css',
			[],
			ESTORE_THEME_VERSION,
			'all',
		);
	}

	/**
	 * Loads home page content.
	 */
	public static function load_home_page(): void {
		// Prepare array for arguments.
		$args = [];

		// Get 0-level categories for category section.
		$level_0_cats = array_filter(
			TaxonomyController::get_term_related_terms( 0, 'item-category', 'store-item' )['item-category'],
			static fn ( $value ) => 0 === $value['depth']
		);
		// Update values of each category.
		array_walk(
			$level_0_cats,
			static function ( &$value, $key ) {
				$value = [
					'name' => $value['name'],
					'link' => get_term_link( $key ),
					'icon' => get_term_meta( $key, 'thumbnail', 'true' ),
				];
			}
		);
		// Push values into an array.
		$args['categories'] = $level_0_cats;

		$brands = get_terms(
			[
				'taxonomy' => 'brand',
				'number'   => 0,
			]
		);
		// Update values of each category.
		array_walk(
			$brands,
			static function ( &$value ) {
				$value = [
					'name' => $value->name,
					'link' => get_term_link( $value->term_id ),
					'icon' => get_term_meta( $value->term_id, 'thumbnail', 'true' ),
				];
			}
		);
		// Push values into an array.
		$args['brands'] = $brands;

		// Get sale posts for carousel.
		$args['sale_posts'] = array_map(
			static function ( $__post_obj ) {
				// Get post meta for sale item.
				$post_meta = get_post_meta( $__post_obj->ID, single: true );

				// Create formatted for date.
				$formatter = new \IntlDateFormatter( get_locale(), \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT );
				$formatter->setPattern( 'd MMMM' );

				// Create DateTime for sale.
				$start_date = date_create( $post_meta['sale-date-start'][0] . ' +3' );
				$end_date   = date_create( $post_meta['sale-date-end'][0] . ' +3' );

				// Check if the sale is multi-month.
				$is_single_month = $start_date->format( 'n' ) === $end_date->format( 'n' );

				// Create a formatted date.
				if ( $is_single_month ) {
					$date = $start_date->format( 'j' ) . ' – ' . $formatter->format( $end_date );
				} else {
					$date = $formatter->format( $start_date ) . ' – ' . $formatter->format( $end_date );
				}

				return [
					'title'                 => $__post_obj->post_title,
					'link'                  => get_the_permalink( $__post_obj ),
					'post_thumbnail'        => get_the_post_thumbnail_url( $__post_obj->ID, 'large' ),
					'placeholder_thumbnail' => [
						'color' => $post_meta['sale-bg-color'][0],
						'terms' => maybe_unserialize( $post_meta['sale-terms'][0] ),
					],
					'date'                  => $date,
				];
			},
			(
				new \WP_Query(
					[
						'post_type'      => 'sale-page',
						'posts_per_page' => 10,
						'meta_query'     => [
							'relation'  => 'AND',
							'end-date'  => [
								'key'     => 'sale-date-end',
								'value'   => date_create( 'now' )->format( 'Y-m-d' ),
								'type'    => 'DATE',
								'compare' => '>',
							],
							'is_active' => [
								'key'     => 'sale-active',
								'value'   => 'on',
								'compare' => '=',
							],
						],
						'order'          => 'ASC',
						'orderby'        => 'end-date',
					]
				)
			)->get_posts()
		);

		get_template_part( 'template-parts/pages/home', args: $args );
	}

	/**
	 * Loads template with the default page template.
	 **/
	public static function load_default_page(): void {
		// Loads default page template, consisting of the title and content.
		get_template_part( 'template-parts/pages/default' );
	}

	#endregion
}
