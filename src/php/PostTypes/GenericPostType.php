<?php
/**
 * Abstract post type class.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\PostTypes;

/**
 * Basis class for the post type classes implementation.
 */
abstract class GenericPostType {

	#region Fields.

	#region Private Fields.

	/**
	 * Post type slug.
	 * @var string $slug
	 */
	private string $slug;

	#region Naming.

	/**
	 * Default name for the post type.
	 * @var string $name
	 */
	private string $name;

	/**
	 * Singular name for the post type.
	 * @var string $singular_name
	 */
	private string $singular_name;

	/**
	 * Plural name for the post type.
	 * @var string $plural_name
	 */
	private string $plural_name;

	/**
	 * Description of the post type.
	 * @var string $description
	 */
	private string $description;

	/**
	 * List of labels for the post type.
	 * @var array $labels
	 */
	private array $labels;

	#endregion.

	#endregion.

	#region Protected Fields

	#region Appearance.

	/**
	 * Whether the post type should be public.
	 * @var bool $public
	 */
	protected bool $public;

	/**
	 * Whether the post type should be hierarcial.
	 * @var bool $hierarchical
	 */
	protected bool $hierarchical;

	/**
	 * Whether the post type should have an archive page.
	 * @var bool $has_archive
	 */
	protected bool $has_archive;

	/**
	 * Whether the post type should be excluded from search.
	 * @var bool $exclude_from_search
	 */
	protected bool $exclude_from_search;

	/**
	 * Whether the post type should be REST queryable.
	 * @var bool $publicly_queryable
	 */
	protected bool $publicly_queryable;

	#endregion

	#region Menu Settings.

	/**
	 * Whether the post type should be shown in the UI.
	 * @var bool $show_ui
	 */
	protected bool $show_ui;

	/**
	 * Position of the post type menu item.
	 * @var int $menu_position
	 */
	protected int $menu_position;

	/**
	 * Icon for the post type menu item.
	 * @var string $menu_icon
	 */
	protected string $menu_icon;

	#endregion

	#region Accessibility Settings.

	/**
	 * Capability type related to the post type.
	 * @var string|array $capability_type
	 */
	protected string|array $capability_type;

	/**
	 * List of capabilities user requires to access post type.
	 * @var array $capabilities
	 */
	protected array $capabilities;

	#endregion.

	#region Supported Features

	/**
	 * List of features supported by post type.
	 * @var array $supports
	 */
	protected array $supports;

	#endregion

	#region Taxonomies Settings

	/**
	 * List of taxonomies related to the post type.
	 * @var array $taxonomies
	 */
	protected array $taxonomies;

	#endregion

	#region Rewrite Rules.

	/**
	 * List of rewrite rules for the post type.
	 * @var array $rewrite
	 */
	protected array $rewrite;

	#endregion.

	#endregion

	#endregion

	#region Properties.

	#region Protected Properties.

	/**
	 * Sets post type slug
	 * @param string $slug new slug.
	 */
	protected function set_slug( string $slug ): void {
		$this->slug = $slug;
	}

	/**
	 * Sets post type name
	 * @param string $name new name.
	 */
	protected function set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Sets post type singular name
	 * @param string $singular_name new singular name.
	 */
	protected function set_singular_name( string $singular_name ): void {
		$this->singular_name = $singular_name;
	}

	/**
	 * Sets post type plural name
	 * @param string $plural_name new plural name.
	 */
	protected function set_plural_name( string $plural_name ): void {
		$this->plural_name = $plural_name;
	}

	/**
	 * Sets post type labels.
	 * @param array $labels array of post type labels.
	 */
	protected function set_labels( array $labels ): void {
		$this->labels = $labels;
	}

	/**
	 * Sets post type description.
	 * @param string $description array of post type description.
	 */
	protected function set_description( string $description ): void {
		$this->description = $description;
	}

	/**
	 * Sets post type menu icon.
	 * @param string $menu_icon menu icon name.
	 */
	protected function set_menu_icon( string $menu_icon ): void {
		$this->menu_icon = $menu_icon;
	}

	#endregion

	#region Public Properties.

	/**
	 * Gets slug of post type.
	 * @return string post type's slug
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets name of post type.
	 * @return string post type's name
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets singular name of post type.
	 * @return string post type's singular name
	 */
	public function get_singular_name(): string {
		return $this->singular_name;
	}

	/**
	 * Gets plural name of post type.
	 * @return string post type's plural name
	 */
	public function get_plural_name(): string {
		return $this->plural_name;
	}

	/**
	 * Gets labels of post type.
	 * @return array post type's labels
	 */
	public function get_labels(): array {
		return $this->labels;
	}

	/**
	 * Gets description of post type.
	 * @return string post type's description
	 */
	public function get_description(): string {
		return $this->description;
	}

	#endregion

	#endregion

	#region Initialization Methods.

	/**
	 * Initializing method for the class.
	 */
	public function init(): void {
		register_post_type(
			$this->slug,
			[
				'labels'              => $this->labels,
				'description'         => $this->description ?? '',
				'public'              => $this->public ?? true,
				'publicly_queryable'  => $this->publicly_queryable ?? true,
				'show_ui'             => $this->show_ui ?? true,
				'hierarchical'        => $this->hierarchical ?? false,
				'has_archive'         => $this->has_archive ?? true,
				'exclude_from_search' => $this->exclude_from_search ?? false,
				'menu_position'       => $this->menu_position ?? null,
				'menu_icon'           => $this->menu_icon ?? null,
				'supports'            => $this->supports ?? null,
				'taxonomies'          => $this->taxonomies ?? [],
				'rewrite'             => $this->rewrite ?? null,
				'show_in_rest'        => false,
			]
		);
	}

	#endregion
}
