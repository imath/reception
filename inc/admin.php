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
		confirmation_code varchar(50) NOT NULL,
		is_confirmed bool DEFAULT 0,
		is_spam bool DEFAULT 0,
		date_confirmed datetime NOT NULL,
		date_last_email_sent datetime NOT NULL,
		KEY email_hash (email_hash)
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
			$post_id = wp_insert_post(
				array(
					'post_status'  => 'publish',
					'post_type'    => bp_get_email_post_type(),
					'post_title'   => $email_types[ $email_term ]['post_title'],
					'post_content' => $email_types[ $email_term ]['post_content'],
					'post_excerpt' => $email_types[ $email_term ]['post_excerpt'],
				)
			);

			wp_set_object_terms( $post_id, $email_types[ $email_term ]['term_id'], bp_get_email_tax_type() );
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
 * Register the Block Editor customizations script.
 *
 * @since 1.0.0
 */
function reception_admin_register_scripts() {
	$version = reception_get_version();
	$url     = reception()->url;

	wp_register_script(
		'reception-editor',
		trailingslashit( $url ) . 'js/editor/index.js',
		array(
			'wp-plugins',
			'wp-edit-post',
			'wp-data',
			'wp-i18n',
		),
		$version,
		true
	);

	wp_register_script(
		'reception-admin-verified-emails',
		trailingslashit( $url ) . 'js/admin/verified-emails.js',
		array(
			'wp-element',
			'wp-components',
			'wp-api-fetch',
			'wp-i18n',
			'wp-date',
			'wp-url',
		),
		$version,
		true
	);

	add_action( bp_core_admin_hook(), 'reception_admin_menus' );
}
add_action( 'init', 'reception_admin_register_scripts' );

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
}
add_action( 'bp_register_admin_settings', 'reception_admin_register_settings', 11 );

/**
 * Redirects the current user on his home page if he tries to access the Reception Edit screen.
 *
 * @since 1.0.0
 */
function reception_admin_redirect_reception_edit_screen() {
	$screen = get_current_screen();

	if ( isset( $screen->post_type ) && 'reception' === $screen->post_type ) {
		wp_safe_redirect( bp_loggedin_user_domain() );
		exit();
	}
}
add_action( 'load-edit.php', 'reception_admin_redirect_reception_edit_screen' );

/**
 * Replace the Block Editor's WordPress logo with the current user's avatar.
 *
 * @since 1.0.0
 */
function reception_add_block_editor_inline_css() {
	if ( 'reception' === get_post_type() ) {
		$avatar = bp_core_fetch_avatar(
			array(
				'item_id' => get_current_user_id(),
				'object'  => 'user',
				'type'    => 'thumb',
				'html'    => false,
			)
		);

		wp_add_inline_style(
			'wp-edit-post',
			'a.components-button.edit-post-fullscreen-mode-close.has-icon {
				padding: 0;
			}

			a.components-button.edit-post-fullscreen-mode-close.has-icon:before {
				content: \' \';
				width: 100%;
				height: 100%;
				background: url( ' . $avatar . ' ) no-repeat;
				background-position: center;
			}

			a.components-button.edit-post-fullscreen-mode-close svg,
			.edit-post-more-menu__content .components-menu-group:first-child {
				display: none;
			}'
		);

		wp_enqueue_script( 'reception-editor' );
		wp_localize_script(
			'reception-editor',
			'receptionEditor',
			array(
				'buddyPressOptionsUrl' => esc_url_raw( add_query_arg( 'page', 'bp-settings', bp_get_admin_url( 'admin.php' ) ) ),
			)
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'reception_add_block_editor_inline_css' );

/**
 * Override the Block editor settings to disable code editor for the reception post type.
 *
 * @since 1.0.0
 *
 * @param array   $settings Default editor settings.
 * @param WP_Post $post     Post being edited.
 * @return array the editor settings.
 */
function reception_set_block_editor_settings( $settings = array(), $post ) {
	if ( 'reception' === get_post_type( $post ) ) {
		$settings = array_merge(
			$settings,
			array(
				'codeEditingEnabled' => false,
			)
		);
	}

	return $settings;
}
add_filter( 'block_editor_settings', 'reception_set_block_editor_settings', 10, 2 );

/**
 * Adds a Tools submenu for the Verified Emails Admin screen.
 *
 * @since 1.0.0
 */
function reception_admin_menus() {
	$parent_menu = 'tools.php';
	if ( is_multisite() && bp_core_do_network_admin() ) {
		$parent_menu = 'network-tools';
	}

	add_submenu_page(
		$parent_menu,
		__( 'Gestion des e-mails vérifiés', 'reception' ),
		__( 'E-mails vérifiés', 'reception' ),
		'edit_users',
		'reception',
		'reception_admin_verified_emails'
	);
}

/**
 * Display the Verified Emails Admin screen.
 *
 * @since 1.0.0
 */
function reception_admin_verified_emails() {
	wp_enqueue_style( 'wp-components' );
	wp_enqueue_script( 'reception-admin-verified-emails' );

	printf(
		'<div class="wrap"><h1>%1$s</h1><p class="description">%2$s %3$s</p><div id="reception-verified-emails"></div>',
		esc_html__( 'Gestion des e-mails vérifiés', 'reception' ),
		esc_html__( 'Pour plus de sécurité, les adresses de messagerie des visiteurs sont cryptées.', 'reception' ),
		esc_html__( 'Merci d’utiliser le champ de recherche ci-dessous en spécificant l’adresse e-mail dont vous souhaitez obtenir les informations de vérification.', 'reception' )
	);
}

/**
 * Display a tool card for the Verified Emails Admin screen.
 *
 * @since 1.0.0
 */
function reception_admin_tool_box() {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	$admin_url = add_query_arg( 'page', 'reception', bp_get_admin_url( 'tools.php' ) );
	?>
	<div class="card">
			<h2 class="title"><?php esc_html_e( 'Gestion des e-mails vérifiés', 'reception' ); ?></h2>
			<p>
			<?php
				printf(
					/* translators: %s: link to the Verified Emails Management screen. */
					esc_html__( 'Pour modérer l’autorisation accordée aux visiteurs de contacter les membres de votre site, utilisez %s', 'reception' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $admin_url ),
						esc_html__( 'l’interface de gestion des e-mails vérifiés', 'reception' )
					)
				);
			?>
			</p>
		</div>
	<?php
}
add_action( 'tool_box', 'reception_admin_tool_box', 8 );
