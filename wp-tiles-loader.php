<?php

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define( 'WP_TILES_VERSION', '1.0-beta1' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */

define( 'WP_TILES_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_TILES_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_TILES_TEMPLATES_DIR', WP_TILES_DIR . 'templates/' );
define( 'WP_TILES_TEMPLATES_URL', WP_TILES_URL . 'templates/' );
define( 'WP_TILES_ASSETS_URL', WP_TILES_URL . 'assets/' );

/**
 * Requires and includes
 *
 * @since 1.0
 */
if ( !defined( 'VP_VERSION' ) )
    require plugin_dir_path( __FILE__ ) .'vafpress-framework/bootstrap.php';

require WP_TILES_DIR . 'vendor/autoload.php';

/**
 * Activation and backward compat
 */

if ( get_option( 'wp-tiles-options' ) !== 'legacy' ) {
    add_action( 'init', array( 'WPTiles\Legacy', 'convert_options' ), 1 );
}

register_activation_hook( __FILE__, array( 'WPTiles\WPTiles', 'on_plugin_activation' ) );

if ( get_transient( 'wp_tiles_first_run' ) ) {
    add_action( 'init', array( 'WPTiles\WPTiles', 'on_first_run' ) );
}

/**
 * Get the one and only true instance of WP Tiles
 *
 * @return WPTiles\WPTiles
 * @since 0.4.2
 */
function wp_tiles() {
    return \WPTiles\WPTiles::get_instance();
}

// Initialize
wp_tiles();

add_action( 'plugins_loaded', 'wptiles_load_pluggables' );
function wptiles_load_pluggables() {
    require_once( WP_TILES_DIR . '/wp-tiles-pluggables.php' );
}

function wp_tiles_preview_tile() {
    return WPTiles\Admin\Admin::preview_tile();
}
