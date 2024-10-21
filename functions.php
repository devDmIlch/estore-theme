<?php
/**
 * EStore theme functions.php file
 *
 * @package estore/theme
 */

// Avoid calling this file directly.
use EStore\ThemeController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version.
 * When modifying js/css files, increase the lowest number here and in style.css.
 */
const ESTORE_THEME_VERSION = '0.0.1';

// Theme path.
const ESTORE_THEME_PATH = __DIR__;

// Theme php path.
const ESTORE_THEME_PHP_PATH = ESTORE_THEME_PATH . '/src/php';

// Core path.
const ESTORE_CORE_PATH = ESTORE_THEME_PHP_PATH . '/Core';

// Set up all dependencies.
require_once ESTORE_THEME_PATH . '/vendor/autoload.php';

// Initialize theme controller.
$theme = new ThemeController();
$theme->init();
