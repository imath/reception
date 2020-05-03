<?php
/**
 * Réception Admin Functions.
 *
 * @package   reception
 * @subpackage \inc\admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installs the plugin.
 *
 * @since 1.0.0
 */
function reception_admin_install() {
	// @todo Install emails.

	// Install members home page template.
	$default_template_id = bp_get_option( '_reception_default_template_id', 0 );

	if ( ! $default_template_id ) {
		$default_template_id = wp_insert_post(
			array(
				'post_type'      => 'reception',
				'post_status'    => 'private',
				'post_title'     => __( 'Page de réception du membre par défaut', 'reception' ),
				'post_name'      => 'default-reception-page',
				'post_content'   => '<!-- wp:reception/info /-->' . "\n",
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);

		if ( ! is_wp_error( $default_template_id ) ) {
			$default_template_id = (int) $default_template_id;
			bp_update_option( '_reception_default_template_id', $default_template_id );
		}
	}
}

/**
 * Checks whether the plugin needs to be upgraded.
 *
 * @since 1.0.0
 */
function reception_admin_updater() {
	$db_version      = bp_get_option( '_reception_version', '' );
	$current_version = reception_get_version();

	if ( ! $db_version ) {
		reception_admin_install();
	} elseif ( version_compare( $db_version, $current_version, '<' ) ) {
		wp_die( 'There is no other versions !' );
	}

	bp_update_option( '_reception_version', $current_version );
}
add_action( 'admin_init', 'reception_admin_updater', 1999 );

/**
 * Only allow the Réception blocks into the Réception post type.
 *
 * @since 1.0.0
 *
 * @param boolean|array $allowed_block_types Array of block type slugs, or
 *                                           boolean to enable/disable all.
 * @param WP_Post       $post                The post resource data.
 * @return boolean|array The allowed block types.
 */
function reception_admin_disallow_blocks( $allowed_block_types, $post ) {
	if ( 'reception' !== get_post_type( $post ) ) {
		foreach ( array( 'reception/info', 'reception/member-bio' ) as $block_name ) {
			unregister_block_type( $block_name );
		}
	}

	return $allowed_block_types;
}
add_filter( 'allowed_block_types', 'reception_admin_disallow_blocks', 10, 2 );
