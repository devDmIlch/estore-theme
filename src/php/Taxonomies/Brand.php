<?php
/**
 * Brand taxonomy.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Taxonomies;

/**
 * Brand taxonomy class
 */
class Brand extends GenericTaxonomy {


	#region Initialization Methods.

	/**
	 * Initialization method for class.
	 */
	public function init(): void {
		// Set taxonomy slug.
		$this->set_slug( 'brand' );
		// Set taxonomy-related post types.
		$this->set_post_types( [ 'store-item', 'sale-page' ] );

		// Set taxonomy's name.
		$this->set_name( __( 'Бренди', 'estore-theme' ) );
		// Set taxonomy's singular name.
		$this->set_singular_name( __( 'Бренд', 'estore-theme' ) );
		// Set taxonomy's plural name.
		$this->set_plural_name( __( 'Бренди', 'estore-theme' ) );

		// Set taxonomy's labels.
		$this->set_labels(
			[
				'name'                  => $this->get_plural_name(),
				'singular_name'         => $this->get_singular_name(),
				'menu_name'             => $this->get_plural_name(),
				'popular_items'         => __( 'Популярні Бренди', 'svitmov' ),
				'search_items'          => __( 'Шукати Бренди', 'svitmov' ),
				'parent_item_colon'     => __( 'Колонка Батьківського Бренду', 'svitmov' ),
				'parent_item'           => __( 'Батьківський Бренд', 'svitmov' ),
				'edit_item'             => __( 'Редагувати Бренд', 'svitmov' ),
				'view_item'             => __( 'Переглянути Бренд', 'svitmov' ),
				'update_item'           => __( 'Оновити Бренд', 'svitmov' ),
				'add_new_item'          => __( 'Додати Новий Бренд', 'svitmov' ),
				'new_item_name'         => __( 'Нова Назва Бренду', 'svitmov' ),
				'not_found'             => __( 'Не Знайдено Брендів', 'svitmov' ),
				'no_terms'              => __( 'Немає Брендів', 'svitmov' ),
				'filter_by_item'        => __( 'Фільтрувати по Брендам', 'svitmov' ),
				'items_list_navigation' => __( 'Навігація по Списку Брендів', 'svitmov' ),
				'items_list'            => __( 'Список Брендів', 'svitmov' ),
				'most_used'             => __( 'Найпоширеніші', 'svitmov' ),
				'back_to_items'         => __( 'Назад до Брендів', 'svitmov' ),
				'item_link'             => __( 'Посилання на Бренд', 'svitmov' ),
				'item_link_description' => __( 'Опис Посилання на Бренд', 'svitmov' ),
			]
		);

		parent::init();

		// Initialize hooks.
		$this->hooks();
	}


	/**
	 * Hook initialization method.
	 */
	protected function hooks(): void {
		// Register customizable meta fields.
		add_action( $this->get_slug() . '_edit_form_fields', [ $this, 'add_custom_edit_fields' ] );
		add_action( $this->get_slug() . '_add_form_fields', [ $this, 'add_custom_new_fields' ] );
		// Save meta fields.
		add_action( 'edit_' . $this->get_slug(), [ $this, 'update_custom_fields' ] );
		add_action( 'create_' . $this->get_slug(), [ $this, 'update_custom_fields' ] );
	}

	#endregion

	#region Public Methods.

	/**
	 * Adds customizable meta fields for the terms page.
	 */
	public function add_custom_new_fields(): void {
		// Prepare arguments for template.
		$args = [
			'title' => __( 'Лого Бренду', 'estore-theme' ),
			'name'  => 'thumbnail',
		];
		// Get template part with the selector.
		get_template_part( 'template-parts/admin/terms/term', 'thumbnail-new', $args );
	}

	/**
	 * Adds customizable meta fields for the terms edit page.
	 *
	 * @param \WP_Term $term_obj Single term object.
	 */
	public function add_custom_edit_fields( \WP_Term $term_obj ): void {
		// Prepare arguments for template.
		$args = [
			'title' => __( 'Лого Бренду', 'estore-theme' ),
			'name'  => 'thumbnail',
			'value' => get_term_meta( $term_obj->term_id, 'thumbnail', true ),
		];
		// Get template part with the selector.
		get_template_part( 'template-parts/admin/terms/term', 'thumbnail', $args );
	}

	/**
	 * Saves term meta after submitting term.php page form.
	 *
	 * @param int $term_id ID of the updated term.
	 */
	public function update_custom_fields( int $term_id ): void {
		// Crete a flag to check whether the request has valid nonce.
		$is_valid_request = false;

		// Check term update nonce.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'update-tag_' . $term_id ) ) {
			$is_valid_request = true;
		}

		// Check the term creation nonce.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_add-tag'] ?? '' ) ), 'add-tag' ) ) {
			$is_valid_request = true;
		}

		// Bail if neither of nonces is valid.
		if ( ! $is_valid_request ) {
			return;
		}

		// Save meta value.
		if ( isset( $_POST['thumbnail'] ) ) {
			update_term_meta( $term_id, 'thumbnail', sanitize_text_field( wp_unslash( $_POST['thumbnail'] ) ) );
		}
	}

	#endregion
}
