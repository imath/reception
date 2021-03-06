<?php
/**
 * Réception Globals.
 *
 * @package   reception
 * @subpackage \inc\globals
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register plugin globals.
 *
 * @since 1.0.0
 */
function reception_globals() {
	$reception = reception();

	// Plugin version.
	$reception->version = '1.0.0';

	// Path.
	$reception->dir = plugin_dir_path( dirname( __FILE__ ) );

	// Templates dir.
	$reception->tpl_dir = trailingslashit( $reception->dir ) . 'templates';

	// URL.
	$reception->url = plugin_dir_url( dirname( __FILE__ ) );
}
add_action( 'bp_include', 'reception_globals' );
