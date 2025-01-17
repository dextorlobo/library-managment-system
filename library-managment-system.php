<?php
/**
 * The plugin bootstrap file.
 *
 * @since 1.0.0
 * @package img-lms-wp
 *
 * @wordpress-plugin
 * Plugin Name:       Library Managment System
 * Description:       Library Managment System plugin.
 * Version:           1.0.8
 * Author:            Arun Sharma
 * Author URI:        https://www.imarun.me/
 * Text Domain:       img-lms-wp
 */

declare( strict_types = 1 );

use Imarun\LibraryManagmentSystem\Plugin;
use Imarun\LibraryManagmentSystem\Api;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * API and Plugin version constants.
 */
define( 'IMG_LMS_PLUGIN_VERSION', '1.0.8' );
define( 'IMG_LMS_PLUGIN_PATH', __FILE__ );
define( 'IMG_LMS_PLUGIN_URL', plugins_url( 'inc', __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once __DIR__ . '/vendor/autoload.php';
} else {
	throw new \Exception( 'Missing vendor/autoload.php. Please run composer install.' );
}

( new Plugin() )->init();

