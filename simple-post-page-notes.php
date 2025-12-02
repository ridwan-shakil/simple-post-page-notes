<?php
/**
 * Plugin Name: Simple Post Page Notes
 * Description: Add simple notes to posts, pages, products and CPT's. Includes a preview column & settings to exclude post types.
 * Version: 1.0.0
 * Author: MD. Ridwan
 * Author URI: https://github.com/ridwan-shakil/
 * Text Domain: simple-post-page-notes
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare( strict_types=1 );


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! defined( 'SPPN_VERSION' ) ) {
	define( 'SPPN_VERSION', '1.0.0' );
}


if ( ! defined( 'SPPN_PLUGIN_FILE' ) ) {
	define( 'SPPN_PLUGIN_FILE', __FILE__ );
}


if ( ! defined( 'SPPN_PLUGIN_DIR' ) ) {
	define( 'SPPN_PLUGIN_DIR', plugin_dir_path( SPPN_PLUGIN_FILE ) );
}


if ( ! defined( 'SPPN_PLUGIN_URL' ) ) {
	define( 'SPPN_PLUGIN_URL', plugin_dir_url( SPPN_PLUGIN_FILE ) );
}


/*
* Require files.
*/
require_once SPPN_PLUGIN_DIR . 'includes/class-sppn-metabox.php';
require_once SPPN_PLUGIN_DIR . 'includes/class-sppn-columns.php';
require_once SPPN_PLUGIN_DIR . 'includes/class-sppn-settings.php';


/**
 * Initialize the plugin.
 */
function sppn_init_plugin(): void {
	// Initialize classes.
	new SPPN_Settings();
	new SPPN_Metabox();
	new SPPN_Columns();
}
add_action( 'plugins_loaded', 'sppn_init_plugin' );
