<?php
/**
 * Helpers class with various methods.
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Ext;

/**
 * Helpers class
 */
class Helpers {

	#region Public Static Methods.

	/**
	 * Render template into string.
	 *
	 * @param string  $template path to a template.
	 * @param ?string $var      template variation.
	 * @param array   $args     arguments passed to template.
	 *
	 * @return string template rendered to a string.
	 */
	public static function render_template_to_string( string $template, ?string $var = null, array $args = [] ): string {
		ob_start();

		get_template_part( $template, $var, $args );

		return ob_get_clean();
	}

	/**
	 * Get path to the theme image.
	 *
	 * @param string $filename Filename of the image.
	 *
	 * @return string Full path to the image in the theme.
	 */
	public static function get_theme_image_path( string $filename ): string {
		return get_stylesheet_directory_uri() . '/assets/images/png/' . $filename;
	}

	/**
	 * Displays the svg file data.
	 *
	 * @param string $filename Name of the svg file.
	 */
	public static function the_svg_file( string $filename ): void {
		// Check if the file contains ".svg" part.
		if ( ! str_contains( $filename, '.svg' ) ) {
			$filename .= '.svg';
		}
		// Create cache name and group.
		$cache_name  = 'svg_file_' . $filename;
		$cache_group = 'svg_loaded';
		// Check whether there already was a call for this file.
		$exists = wp_cache_get( $cache_name, $cache_group );
		// Check if the file has been loaded before.
		if ( false === $exists ) {
			// Check if the file exist.
			if ( file_exists( ESTORE_THEME_PATH . '/assets/images/svg/' . $filename ) ) {
				$exists = 1;
			} else {
				$exists = 0;
			}
			// Save data in cache.
			wp_cache_set( $cache_name, $exists, $cache_group );
		}
		// Check if the file is found.
		if ( 0 === $exists ) {
			return;
		}
		// If the file is found load the svg.
		require $filename;
	}

	/**
	 * Recursively get taxonomy and its children
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param int    $parent   Parent term id.
	 *
	 * @return array
	 */
	public static function get_taxonomy_hierarchy( string $taxonomy, int $parent = 0 ): array {
		// Get all direct descendants of the $parent.
		$terms = get_terms(
			[
				'taxonomy' => $taxonomy,
				'parent'   => $parent,
			]
		);

		// Prepare a new array. These are the children of $parent
		// We'll ultimately copy all the $terms into this new array, but only after they find their own children.
		$children = [];

		// Go through all the direct descendants of $parent, and gather their children.
		foreach ( $terms as $term ) {
			// Recurse to get the direct descendants of "this" term.
			$term->children = self::get_taxonomy_hierarchy( $taxonomy, $term->term_id );

			// Add the term to our new array.
			$children[ $term->term_id ] = $term;
		}

		// Send the results back to the caller.
		return $children;
	}

	/**
	 * Recursively get all taxonomies as complete hierarchies
	 *
	 * @param array $taxonomies Array of taxonomy slugs.
	 * @param int   $parent     Starting parent term id.
	 *
	 * @return array
	 */
	public static function get_taxonomy_hierarchy_multiple( array $taxonomies, int $parent = 0 ): array {
		$results = [];

		foreach ( $taxonomies as $taxonomy ) {
			$terms = self::get_taxonomy_hierarchy( $taxonomy, $parent );

			if ( $terms ) {
				$results[ $taxonomy ] = $terms;
			}
		}

		return $results;
	}

	/**
	 * Checks whether the user is logged in and redirects them to login page if they aren't.
	 */
	public static function force_user_login(): void {
		// Redirect to login page if the user is logged out.
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( '/login?redirect_url=' . wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), 302 );
		}
	}

	#endregion
}
