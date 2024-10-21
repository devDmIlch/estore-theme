<?php
/**
 * Sale Page class template
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\PostTypes;

use EStore\Ext\Helpers;
use EStore\Taxonomies\Category;
use EStore\Taxonomies\TaxonomyController;

/**
 * EStore Sale Page post type class
 */
class SaleItem extends GenericPostType {

	#region Initializing Methods.

	/**
	 * Class initialization fuction.
	 */
	public function init(): void {
		// Set Post Type Slug.
		$this->set_slug( 'sale-page' );
		// Set Naming.
		$this->set_name( __( 'Розпродажі', 'estore-theme' ) );
		$this->set_singular_name( __( 'Розпродаж', 'estore-theme' ) );
		$this->set_plural_name( __( 'Розпродажі', 'estore-theme' ) );
		// Set Description.
		$this->set_description( __( 'Сторінки розпродажів на яких розписано інформацію про знижки на товари', 'estore-theme' ) );
		// Set icon for post type.
		$this->set_menu_icon( 'dashicons-tag' );

		// Set labels.
		$this->set_labels(
			[
				'name'                     => $this->get_plural_name(),
				'singular_name'            => $this->get_singular_name(),
				'add_new'                  => __( 'Додати Розпродаж', 'svitmov' ),
				'add_new_item'             => __( 'Додати Новий Розпродаж', 'svitmov' ),
				'edit_item'                => __( 'Редагувати Розпродаж', 'svitmov' ),
				'new_item'                 => __( 'Новий Розпродаж', 'svitmov' ),
				'view_item'                => __( 'Переглянути Розпродаж', 'svitmov' ),
				'view_items'               => __( 'Переглянути Розпродажі', 'svitmov' ),
				'search_items'             => __( 'Шукати Розпродажі', 'svitmov' ),
				'not_found'                => __( 'Не Знайдено Розпродажів', 'svitmov' ),
				'not_found_in_trash'       => __( 'Не Знайдено Розпродажів в Смітнику', 'svitmov' ),
				'all_items'                => __( 'Усі Розпродажі', 'svitmov' ),
				'archives'                 => __( 'Архів Розпродажів', 'svitmov' ),
				'attributes'               => __( 'Атрибути Розпродажу', 'svitmov' ),
				'insert_into_item'         => __( 'Додати до Розпродажу', 'svitmov' ),
				'insert_into_this_item'    => __( 'Додати до Цього Розпродажу', 'svitmov' ),
				'featured_image'           => __( 'Зображення Розпродажу', 'svitmov' ),
				'set_featured_image'       => __( 'Встановити Зображення Розпродажу', 'svitmov' ),
				'remove_featured_image'    => __( 'Прибрати Зображення Розпродажу', 'svitmov' ),
				'menu_name'                => __( 'Розпродажі', 'svitmov' ),
				'filter_items_list'        => __( 'Фільтрувати Розпродажі', 'svitmov' ),
				'filter_by_date'           => __( 'Фільтрувати Розпродажі за Датою', 'svitmov' ),
				'items_list_navigation'    => __( 'Навігація по Списку Розпродажів', 'svitmov' ),
				'items_list'               => __( 'Список Розпродажів', 'svitmov' ),
				'item_published'           => __( 'Розпродаж Опубліковано', 'svitmov' ),
				'item_published_privately' => __( 'Розпродаж Опубліковано Приватним Записом', 'svitmov' ),
				'item_reverted_to_draft'   => __( 'Розпродаж Повернено до Чернеток', 'svitmov' ),
				'item_trashed'             => __( 'Розпродаж Переміщено до Смітника', 'svitmov' ),
				'item_updated'             => __( 'Розпродаж Оновлено', 'svitmov' ),
				'item_link'                => __( 'Посилання на Розпродаж', 'svitmov' ),
				'item_link_description'    => __( 'Опис Посилання на Розпродаж', 'svitmov' ),
			]
		);

		// Set supported features.
		$this->supports = [ 'title', 'editor', 'thumbnail', 'excerpt' ];

		// Proceed with default initialization.
		parent::init();

		// Initialize hooks.
		$this->hooks();
	}

	/**
	 * Hooks initialization class method.
	 */
	protected function hooks(): void {
		// Registers metabox with the item settings.
		add_action( 'add_meta_boxes', [ $this, 'register_settings_metabox' ], 10, 1 );

		// Add saving functionality for the metabox.
		add_action( 'save_post', [ $this, 'save_settings_metabox_data' ], 15, 3 );
		// Localization Script.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_archive_script_data' ], 20 );
	}

	#endregion


	#region Public Methods.

	/**
	 * Registers custom metabox for item settings.
	 **/
	public function register_settings_metabox(): void {
		// Add 'preferences section' metabox.
		add_meta_box( 'sale-item-settings', __( 'Налаштування Розпродажу', 'estore-theme' ), [ $this, 'render_settings_metabox_content' ], $this->get_slug() );
		// Add 'related section' metabox.
		add_meta_box( 'sale-item-related', __( 'Товари на розпродаж', 'estore-theme' ), [ $this, 'render_related_metabox_content' ], $this->get_slug() );
	}

	/**
	 * Displays content of the 'general settings' metabox.
	 *
	 * @param \WP_Post $post_obj post object.
	 */
	public function render_settings_metabox_content( \WP_Post $post_obj ): void {
		// Retrieve post meta for custom fields.
		$post_meta = get_post_meta( $post_obj->ID );
		// Template parameters.
		$args = [
			'sale-active'     => isset( $post_meta['sale-active'] ) ? $post_meta['sale-active'][0] : 'off',
			'sale-bg-color'   => isset( $post_meta['sale-bg-color'] ) ? $post_meta['sale-bg-color'][0] : '#5185c5',
			'sale-date-start' => isset( $post_meta['sale-date-start'] ) ? $post_meta['sale-date-start'][0] : '',
			'sale-date-end'   => isset( $post_meta['sale-date-end'] ) ? $post_meta['sale-date-end'][0] : '',
		];

		// Load the settings template.
		get_template_part( 'template-parts/admin/sale-item/item', 'settings', $args );
	}

	/**
	 * Displays content of the 'related store items' metabox.
	 *
	 * @param \WP_Post $post_obj post object.
	 */
	public function render_related_metabox_content( \WP_Post $post_obj ): void {
		// Get all products posts.
		$args = [
			'posts' => get_posts(
				[
					'number'    => -1,
					'post_type' => 'store-item',
				]
			),
		];

		// Get selected posts.
		$args['selected'] = get_post_meta( $post_obj->ID, 'sale-related', true );
		if ( ! is_array( $args['selected'] ) ) {
			$args['selected'] = [];
		}

		// Load the settings template.
		get_template_part( 'template-parts/admin/sale-item/item', 'related', $args );
	}

	/**
	 * Saves settings data for the 'store-item' posts.
	 *
	 * @param int      $post_id  ID of the saved post.
	 * @param \WP_Post $post_obj Object of the saved post.
	 * @param bool     $update   Whether the exising post was updated.
	 */
	public function save_settings_metabox_data( int $post_id, \WP_Post $post_obj, bool $update ): void {
		// Check whether the post is right post type.
		if ( $this->get_slug() !== $post_obj->post_type ) {
			return;
		}
		// Check the nonce to ensure data isn't coming from a weird source.
		if ( false === wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'update-post_' . $post_id ) ) {
			return;
		}

		// Save sale active state.
		update_post_meta( $post_id, 'sale-active', sanitize_text_field( wp_unslash( $_POST['sale-active'] ?? 'off' ) ) );
		// Save background color for the sale.
		update_post_meta( $post_id, 'sale-bg-color', sanitize_text_field( wp_unslash( $_POST['bg-color'] ?? '' ) ) );
		// Update post start date.
		update_post_meta( $post_id, 'sale-date-start', sanitize_text_field( wp_unslash( $_POST['sale-date-start'] ?? '' ) ) );
		// Update post end date. We will potentially need this value to set a cron job, so save it in the variable.
		$end_date = sanitize_text_field( wp_unslash( $_POST['sale-date-end'] ?? '' ) );
		update_post_meta( $post_id, 'sale-date-end', $end_date );
		// Update related store items.
		$related_posts = explode( ',', sanitize_text_field( wp_unslash( $_POST['related-posts'] ?? '' ) ) );
		update_post_meta( $post_id, 'sale-related', $related_posts );
		// Set terms related to the sale, as they will be used with the default thumbnail.
		update_post_meta( $post_id, 'sale-terms', wp_get_object_terms( $related_posts, 'item-category', [ 'fields' => 'id=>name' ] ) );
	}


	/**
	 * Enqueues data about archive as a localization script.
	 */
	public function enqueue_archive_script_data(): void {
		// Bail if the page is not an archive page.
		if ( ! is_single() && $this->get_slug() !== get_post_type() ) {
			return;
		}

		// Set up localization lines to pass into the JS.
		$localization_lines = [
			'type'     => 'sale',
			'post__in' => get_post_meta( get_the_ID(), 'sale-related', true ),
		];

		// Enqueue archive preferences variables.
		wp_localize_script( 'estore_script', 'archivePrefs', $localization_lines );
	}

	/**
	 * Loads single page for this post type.
	 */
	public function load_single_page(): void {
		// Get post id.
		$post_id = get_the_ID();
		// Get post meta for sale item.
		$post_meta = get_post_meta( $post_id, single: true );

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

		$__post_obj = get_post();

		// Set up arguments array to pass into template.
		$args = [
			'title'                 => $__post_obj->post_title,
			'post_thumbnail'        => get_the_post_thumbnail_url( $post_id, 'full' ),
			'placeholder_thumbnail' => [
				'color' => $post_meta['sale-bg-color'][0],
				'terms' => maybe_unserialize( $post_meta['sale-terms'][0] ),
			],
			'date'                  => $date,
			'related-archive'       => [
				'filters'    => false,
				'state'      => false,
				'pagination' => false,
			],
		];

		get_template_part( 'template-parts/posts/' . $this->get_slug(), args: $args );
	}

	#endregion
}
