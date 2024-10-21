<?php
/**
 * Abstract Generic Taxonomy class.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Taxonomies;

/**
 * Generic Taxonomy class
 */
abstract class GenericTaxonomy {

	#region Private Fields.

	/**
	 * Taxonomy slug.
	 * @var string $slug
	 */
	private string $slug;

	/**
	 * Array of post type associated with taxonomy.
	 * @var array $post_types;
	 */
	private array $post_types;

	#region Naming.

	/**
	 * Default name for the taxonomy.
	 * @var string $name
	 */
	private string $name;

	/**
	 * Singular name for the taxonomy.
	 * @var string $singular_name
	 */
	private string $singular_name;

	/**
	 * Plural name for the taxonomy.
	 * @var string $plural_name
	 */
	private string $plural_name;

	/**
	 * Description of the taxonomy.
	 * @var string $description
	 */
	private string $description;

	/**
	 * List of labels for the taxonomy.
	 * @var array $labels
	 */
	private array $labels;

	#endregion

	#endregion


	#region Protected Properties.

	/**
	 * Sets 'slug' field of the class
	 * @param string $slug New taxonomy slug.
	 */
	protected function set_slug( string $slug ): void {
		$this->slug = $slug;
	}

	/**
	 * Sets 'post_types' field of the class
	 * @param array $post_types New taxonomy related post types.
	 */
	protected function set_post_types( array $post_types ): void {
		$this->post_types = $post_types;
	}

	/**
	 * Sets 'name' field of the class
	 * @param string $name New taxonomy name.
	 */
	protected function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Sets 'singular_name' field of the class
	 * @param string $singular_name New taxonomy singular name.
	 */
	protected function set_singular_name( string $singular_name ): void {
		$this->singular_name = $singular_name;
	}

	/**
	 * Sets 'plural_name' field of the class
	 * @param string $plural_name New taxonomy plural name.
	 */
	protected function set_plural_name( string $plural_name ): void {
		$this->plural_name = $plural_name;
	}

	/**
	 * Sets 'labels' field of the class
	 * @param array $labels New array of labels for the taxonomy.
	 */
	protected function set_labels( array $labels ): void {
		$this->labels = $labels;
	}

	#endregion


	#region Public Properties.

	/**
	 * Returns value of the 'slug' field
	 * @return string Taxonomy's slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Returns value of the 'post_types' field
	 * @return array Array of taxonomy-related post types.
	 */
	public function get_post_types(): array {
		return $this->post_types;
	}

	/**
	 * Returns value of the 'name' field
	 * @return string Taxonomy's name.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Returns value of the 'singular_name' field
	 * @return string Taxonomy's singular name.
	 */
	public function get_singular_name(): string {
		return $this->singular_name;
	}

	/**
	 * Returns value of the 'plural_name' field
	 * @return string Taxonomy's plural name.
	 */
	public function get_plural_name(): string {
		return $this->plural_name;
	}

	/**
	 * Returns value of the 'labels' field
	 * @return array Array of taxonomy's labels.
	 */
	public function get_labels(): array {
		return $this->labels;
	}

	#endregion


	#region Initialization Methods.

	/**
	 * Initialization method for class.
	 */
	public function init(): void {
		register_taxonomy(
			$this->slug,
			$this->post_types,
			[
				'labels'              => $this->labels ?? null,
				'description'         => $this->description ?? '',
				'public'              => $this->is_public ?? true,
				'publicly_queryable'  => $this->is_publicly_queryable ?? true,
				'show_ui'             => $this->is_ui_shown ?? true,
				'show_in_menu'        => $this->is_shown_in_menu ?? true,
				'hierarchical'        => $this->is_hierarchical ?? true,
				'has_archive'         => $this->is_archive_exist ?? true,
				'exclude_from_search' => $this->is_excluded_from_search ?? false,
				'menu_position'       => $this->menu_position ?? null,
				'menu_icon'           => $this->menu_icon ?? null,
				'support'             => $this->supports ?? null,
				'taxonomies'          => $this->taxonomies ?? [],
				'rewrite'             => $this->rewrite ?? null,
			]
		);
	}

	#endregion

}
