<?php
/**
 * Réception Templates management functions.
 *
 * @package   reception
 * @subpackage \inc\templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Injects Plugin's templates dir into the BuddyPress Templates dir stack.
 *
 * @since 1.0.0
 *
 * @param  array $template_stack The list of available locations to get BuddyPress templates.
 * @return array                 The list of available locations to get BuddyPress templates.
 */
function reception_template_stack( $template_stack = array() ) {
	if ( ! reception_has_front() ) {
		return $template_stack;
	}

	return array_merge(
		$template_stack,
		array( trailingslashit( reception()->tpl_dir ) . 'buddypress' ),
	);
}
add_filter( 'bp_get_template_stack', 'reception_template_stack', 10, 1 );
