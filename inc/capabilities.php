<?php
/**
 * Réception Functions.
 *
 * @package   reception
 * @subpackage \inc\capabilities
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability mapping for the reception post type.
 *
 * @since 1.0.0
 *
 * @param string[] $caps    Array of the user's capabilities.
 * @param string   $cap     Capability name.
 * @param int      $user_id The user ID.
 * @param array    $args    Adds the context to the cap. Typically the object ID.
 * @return string[] Array of the user's capabilities.
 */
function reception_capabilities_mapping( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	if ( false !== strpos( $cap, 'reception' ) ) {
		$caps = array( 'manage_options' );
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'reception_capabilities_mapping', 10, 4 );
