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
 * Install the DB tables needed by the plugin.
 *
 * @since 1.0.0
 */
function reception_install_tables() {
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $GLOBALS['wpdb']->get_charset_collate();
	$prefix          = bp_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$prefix}reception_verified_emails (
		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		email_hash varchar(255) NOT NULL default '',
		confirmation_code bigint(20) NOT NULL,
		is_confirmed bool DEFAULT 0,
		is_spam bool DEFAULT 0,
		date_confirmed datetime NOT NULL,
		date_last_email_sent datetime NOT NULL
	) {$charset_collate};";

	dbDelta( $sql );
}

/**
 * Install/Reinstall Plugin's emails.
 *
 * @since 1.0.0
 */
function reception_install_emails() {
	$switched = false;

	// Switch to the root blog, where the email posts live.
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched = true;
	}

	// Get Emails.
	$email_types = reception_get_email_templates();

	// Set email types.
	foreach ( $email_types as $email_term => $term_args ) {
		if ( term_exists( $email_term, bp_get_email_tax_type() ) ) {
			$email_type = get_term_by( 'slug', $email_term, bp_get_email_tax_type() );

			$email_types[ $email_term ]['term_id'] = $email_type->term_id;
		} else {
			$term = wp_insert_term(
				$email_term,
				bp_get_email_tax_type(),
				array(
					'description' => $term_args['description'],
				)
			);

			$email_types[ $email_term ]['term_id'] = $term['term_id'];
		}

		// Insert Email templates if needed.
		if ( ! empty( $email_types[ $email_term ]['term_id'] ) && ! is_a( bp_get_email( $email_term ), 'BP_Email' ) ) {
			wp_insert_post(
				array(
					'post_status'  => 'publish',
					'post_type'    => bp_get_email_post_type(),
					'post_title'   => $email_types[ $email_term ]['post_title'],
					'post_content' => $email_types[ $email_term ]['post_content'],
					'post_excerpt' => $email_types[ $email_term ]['post_excerpt'],
					'tax_input'    => array(
						bp_get_email_tax_type() => array( $email_types[ $email_term ]['term_id'] ),
					),
				)
			);
		}
	}

	if ( $switched ) {
		restore_current_blog();
	}
}
add_action( 'bp_core_install_emails', 'reception_install_emails' );

/**
 * Installs the plugin.
 *
 * @since 1.0.0
 */
function reception_admin_install() {
	// Install emails.
	reception_install_emails();

	// Install tables.
	reception_install_tables();

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

	// Réception is up to date.
	if ( $db_version && version_compare( $db_version, $current_version, '=' ) ) {
		return;
	}

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

/**
 * Sanitizes the settings about the Block based member front.
 *
 * @since 1.0.0
 *
 * @param boolean|string $value The value to sanitize.
 * @return boolean The sanitized value.
 */
function reception_admin_sanitize_block_based_member_front( $value ) {
	if ( isset( $_POST['option_page'] ) && 'buddypress' === $_POST['option_page'] ) { // phpcs:ignore
		$value = true;

		if ( isset( $_POST['reception_disable_block_based_member_front'] ) && 1 === (int) $_POST['reception_disable_block_based_member_front'] ) { // phpcs:ignore
			$value = false;
		}
	}

	return rest_sanitize_boolean( $value );
}

/**
 * Outputs the form elements to set the site owner preference about the Block based member front.
 *
 * @since 1.0.0
 */
function reception_admin_setting_block_based_member_front() {
	$disable         = get_option( 'reception_disable_block_based_member_front' );
	$default_page_id = bp_get_option( '_reception_default_template_id', 0 );
	?>
	<input id="reception_disable_block_based_member_front" name="reception_disable_block_based_member_front" type="checkbox" value="1" <?php checked( ! $disable ); ?> />
	<label for="reception_disable_block_based_member_front">
		<?php esc_html_e( 'Utiliser le gabarit basé sur des blocs WordPress.', 'reception' ); ?>
	</label>
	<?php if ( $default_page_id && ! $disable ) : ?>
		<p class="description">
			<?php
			printf(
				/* translators: %s is the edit link to the Block based member's front template page */
				esc_html__( 'Vous pouvez %s ce gabarit à tout moment.', 'reception' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( get_edit_post_link( $default_page_id ) ),
					esc_html__( 'modifier', 'reception' )
				)
			)
			?>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Outputs the form elements to set the site owner preference about the customization of the Block based member front.
 *
 * @since 1.0.0
 */
function reception_admin_setting_members_block_based_front_customs() {
	$enable = get_option( 'reception_allow_members_block_based_front_customs' ) && ! get_option( 'reception_disable_block_based_member_front' );
	?>
	<input id="reception_allow_members_block_based_front_customs" name="reception_allow_members_block_based_front_customs" type="checkbox" value="1" <?php checked( $enable ) . ' ' . disabled( true ); ?> />
	<label for="reception_allow_members_block_based_front_customs">
		<?php esc_html_e( 'Autoriser les membres à personnaliser le gabarit basé sur des blocs WordPress.', 'reception' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Cette possibilité n’est pas disponible pour le moment.', 'reception' ); ?></p>
	<?php
}

/**
 * Registers specific Réception settings into the Members section of the BuddyPress options page.
 *
 * @since 1.0.0
 */
function reception_admin_register_settings() {
	$disabled_block_based_member_front = get_option( 'reception_disable_block_based_member_front' );

	register_setting(
		'buddypress',
		'reception_disable_block_based_member_front',
		array(
			'type'              => 'boolean',
			'description'       => __( 'Indique s’il faut maintenir actif (`true`) ou désactiver (`false`) la page d’accueil des membres basée sur des blocs WordPress.', 'reception' ),
			'sanitize_callback' => 'reception_admin_sanitize_block_based_member_front',
			'show_in_rest'      => false,
			'default'           => false,
		)
	);

	add_settings_field(
		'reception_disable_block_based_member_front',
		__( 'Page d’accueil des membres', 'reception' ),
		'reception_admin_setting_block_based_member_front',
		'buddypress',
		'bp_members'
	);

	if ( ! $disabled_block_based_member_front ) {
		register_setting(
			'buddypress',
			'reception_allow_members_block_based_front_customs',
			array(
				'type'              => 'boolean',
				'description'       => __( 'Indique s’il faut autoriser les membres à personnaliser le gabarit de leur page d’accueil.', 'reception' ),
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => false,
				'default'           => false,
			)
		);

		add_settings_field(
			'reception_allow_members_block_based_front_customs',
			__( 'Personnalisation de la page d’accueil des membres', 'reception' ),
			'reception_admin_setting_members_block_based_front_customs',
			'buddypress',
			'bp_members'
		);
	}
}
add_action( 'bp_register_admin_settings', 'reception_admin_register_settings', 11 );
