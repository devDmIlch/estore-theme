<?php
/**
 * Estore item Category taxonomy.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Taxonomies;

/**
 * Estore item Category taxonomy class
 */
class Category extends GenericTaxonomy {


	#region Initialization Methods.

	/**
	 * Initialization method for class.
	 */
	public function init(): void {
		// Set taxonomy slug.
		$this->set_slug( 'item-category' );
		// Set taxonomy-related post types.
		$this->set_post_types( [ 'store-item' ] );

		// Set taxonomy's name.
		$this->set_name( __( 'Категорії', 'estore-theme' ) );
		// Set taxonomy's singular name.
		$this->set_singular_name( __( 'Категорія', 'estore-theme' ) );
		// Set taxonomy's plural name.
		$this->set_plural_name( __( 'Категорії', 'estore-theme' ) );

		// Set taxonomy's labels.
		$this->set_labels(
			[
				'name'                  => $this->get_plural_name(),
				'singular_name'         => $this->get_singular_name(),
				'menu_name'             => $this->get_plural_name(),
				'popular_items'         => __( 'Популярні Категорії', 'svitmov' ),
				'search_items'          => __( 'Шукати Категорії', 'svitmov' ),
				'parent_item_colon'     => __( 'Колонка Батьківської Категорії', 'svitmov' ),
				'parent_item'           => __( 'Батьківська Категорія', 'svitmov' ),
				'edit_item'             => __( 'Редагувати Категорію', 'svitmov' ),
				'view_item'             => __( 'Переглянути Категорію', 'svitmov' ),
				'update_item'           => __( 'Оновити Категорію', 'svitmov' ),
				'add_new_item'          => __( 'Додати Нову Категорію', 'svitmov' ),
				'new_item_name'         => __( 'Нова Назва Категорії', 'svitmov' ),
				'not_found'             => __( 'Не Знайдено Категорій', 'svitmov' ),
				'no_terms'              => __( 'Немає Категорій', 'svitmov' ),
				'filter_by_item'        => __( 'Фільтрувати по Категоріям', 'svitmov' ),
				'items_list_navigation' => __( 'Навігація по Списку Категорій', 'svitmov' ),
				'items_list'            => __( 'Список Категорій', 'svitmov' ),
				'most_used'             => __( 'Найпоширеніші', 'svitmov' ),
				'back_to_items'         => __( 'Назад до Категорій', 'svitmov' ),
				'item_link'             => __( 'Посилання на Категорію', 'svitmov' ),
				'item_link_description' => __( 'Опис Посилання на Категорію', 'svitmov' ),
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
	 * Adds customizable meta fields for the terms.
	 *
	 * @param \WP_Term $term_obj Single term object.
	 */
	public function add_custom_edit_fields( \WP_Term $term_obj ): void {
		// Prepare arguments for template.
		$args = [
			'title' => __( 'Зображення Категорії', 'estore-theme' ),
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
