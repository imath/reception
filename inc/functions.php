<?php
/**
 * Réception Functions.
 *
 * @package   reception
 * @subpackage \inc\functions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the current version of the plugin.
 *
 * @since 1.0.0
 *
 * @return string The current version of the plugin.
 */
function reception_get_version() {
	return reception()->version;
}

/**
 * Init the plugins by registering custom WordPress objetcs.
 *
 * @since 1.0.0
 */
function reception_init() {
	register_post_type(
		'reception',
		array(
			'label'               => __( 'Pages de réception', 'reception' ),
			'labels'              => array(
				'name'                   => _x( 'Pages de réception', 'General name for the post type', 'reception' ),
				'singular_name'          => _x( 'Page de réception', 'Name for one object of this post type', 'reception' ),
				'add_new'                => _x( 'Ajouter nouvelle', 'reception', 'reception' ),
				'add_new_item'           => _x( 'Ajouter une nouvelle page de réception', 'Label for adding a new singular item', 'reception' ),
				'edit_item'              => _x( 'Modifier la page de réception', 'Label for editing a singular item', 'reception' ),
				'new_item'               => _x( 'Nouvelle page de réception', 'Label for the new item page title', 'reception' ),
				'view_item'              => _x( 'Afficher la page de réception', 'Label for the new item page title', 'reception' ),
				'view_items'             => _x( 'Afficher ma page de réception', 'Label for the view items link', 'reception' ),
				'search_items'           => _x( 'Rechercher des pages de réception', 'Label for searching plural items', 'reception' ),
				'not_found'              => _x( 'Aucune page de réception trouvée', 'Label used when no items are found', 'reception' ),
				'all_items'              => _x( 'Toutes les pages de réception', 'Label to signify all items in a submenu link', 'reception' ),
				'insert_into_item'       => _x( 'Insérer dans la page de réception', 'Label for the media frame button', 'reception' ),
				'uploaded_to_this_item'  => _x( 'Téléversé dans cette page de réception', 'Label for the media frame filter', 'reception' ),
				'filter_items_list'      => _x( 'Filtrer la liste des pages de réception', 'Label for the table views hidden heading', 'reception' ),
				'items_list_navigation'  => _x( 'Navigation de la liste des pages de réception', 'Label for the table pagination hidden heading', 'reception' ),
				'items_list'             => _x( 'Liste des pages de réception', 'Label for the table hidden heading', 'reception' ),
				'item_published'         => _x( 'Page de réception publiée', 'Label used when an item is published', 'reception' ),
				'item_reverted_to_draft' => _x( 'Page de réception reconvertie en brouillon', 'Label used when an item is switched to a draft', 'reception' ),
				'item_updated'           => _x( 'Page de réception mise à jour', ' Label used when an item is updated', 'reception' ),
			),
			'description'         => __( 'Pages d’accueil des membres BuddyPress personnalisable à l’aide de blocs WordPress', 'reception' ),
			'public'              => false,
			'hierarchical'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => true,
			'rest_base'           => 'receptions',
			'capability_type'     => array( 'reception', 'receptions' ),
			'supports'            => array( 'editor' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'can_export'          => true,
			'delete_with_user'    => true,
		)
	);

	// Disable trash for the post type.
	add_filter( 'rest_reception_trashable', '__return_false' );

	// Add custom styles for the WP Admin Bar My Account entry.
	wp_add_inline_style(
		'admin-bar',
		'#wpadminbar #wp-admin-bar-my-account-reception > .ab-item {
			padding-left: 2em;
			padding-right: 1em;
		}

		#wpadminbar #wp-admin-bar-my-account-reception .wp-admin-bar-arrow:before {
			position: absolute;
			top: 1px;
			left: 6px;
			right: 10px;
			padding: 4px 0;
			font: normal 17px/1 dashicons;
			content: "\f102";
		}'
	);
}
add_action( 'bp_init', 'reception_init' );

/**
 * Registers the REST API routes.
 *
 * @since 1.0.0
 */
function reception_register_routes() {
	$verified_email_controller = new Reception_Verified_Email_REST_Controller();
	$verified_email_controller->register_routes();
}
add_action( 'bp_rest_api_init', 'reception_register_routes' );

/**
 * Réception Blocks initialization.
 *
 * @since 1.0.0
 */
function reception_init_blocks() {
	$js_base_url  = trailingslashit( reception()->url ) . 'js/blocks/';
	$css_base_url = trailingslashit( reception()->url ) . 'assets/css/';

	bp_register_block(
		array(
			'name'               => 'reception/member-bio',
			'render_callback'    => 'reception_render_member_bio',
			'attributes'         => array(
				'blockTitle' => array(
					'type'    => 'string',
					'default' => __( 'À propos', 'reception' ),
				),
			),
			'editor_script'      => 'reception-block-member-bio',
			'editor_script_url'  => $js_base_url . 'member-bio.js',
			'editor_script_deps' => array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-i18n',
				'wp-editor',
				'wp-block-editor',
			),
		)
	);

	bp_register_block(
		array(
			'name'               => 'reception/member-contact-form',
			'render_callback'    => 'reception_render_member_contact_form',
			'attributes'         => array(
				'blockTitle' => array(
					'type'    => 'string',
					'default' => __( 'Contacter ce membre', 'reception' ),
				),
			),
			'editor_script'      => 'reception-block-member-contact-form',
			'editor_script_url'  => $js_base_url . 'member-contact-form.js',
			'editor_script_deps' => array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-i18n',
				'wp-editor',
				'wp-block-editor',
			),
			'style'              => 'reception-block-member-contact-form',
			'style_url'          => $css_base_url . 'block-member-contact-form.css',
			'style_deps'         => array(
				'wp-components',
			),
		)
	);

	bp_register_block(
		array(
			'name'               => 'reception/info',
			'editor_script'      => 'reception-info',
			'editor_script_url'  => $js_base_url . 'reception-info.js',
			'editor_script_deps' => array(
				'wp-blocks',
				'wp-element',
				'wp-i18n',
			),
		)
	);
}
add_action( 'bp_blocks_init', 'reception_init_blocks' );

/**
 * Registers front-end scripts and styles.
 *
 * @since 1.0.0
 */
function reception_register_scripts() {
	$js_base_url  = trailingslashit( reception()->url ) . 'js/scripts/';
	$css_base_url = trailingslashit( reception()->url ) . 'assets/css/';
	$version      = reception_get_version();

	if ( bp_is_my_profile() ) {
		wp_register_script(
			'reception-script-member-bio',
			$js_base_url . 'member-bio.js',
			array(
				'wp-element',
				'wp-i18n',
				'wp-api-fetch',
				'wp-block-editor',
				'wp-components',
			),
			$version,
			true
		);

		wp_register_style(
			'reception-script-member-bio',
			$css_base_url . 'script-member-bio.css',
			array(
				'wp-components',
			),
			$version
		);
	}

	wp_register_script(
		'reception-script-member-contact-form',
		$js_base_url . 'member-contact-form.js',
		array(
			'wp-element',
			'wp-i18n',
			'wp-api-fetch',
			'wp-block-editor',
			'wp-components',
			'wp-url',
		),
		$version,
		true
	);
}
add_action( 'bp_enqueue_scripts', 'reception_register_scripts', 1 );

/**
 * Renders the Réception Member's bio block.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string HTML output.
 */
function reception_render_member_bio( $attributes = array() ) {
	$user_id = 0;
	$class   = 'static';

	$container = '<div class="reception-block-member-bio %1$s">%2$s</div>';
	$params    = wp_parse_args(
		$attributes,
		array(
			'blockTitle' => '',
		)
	);

	if ( bp_is_my_profile() ) {
		$class = 'dynamic';
		wp_enqueue_script( 'reception-script-member-bio' );
		wp_localize_script(
			'reception-script-member-bio',
			'receptionMemberBio',
			array(
				'title' => $params['blockTitle'],
			)
		);

		wp_enqueue_style( 'reception-script-member-bio' );

		return sprintf( $container, $class, '' );
	} elseif ( bp_displayed_user_id() ) {
		$user_id = bp_displayed_user_id();
	} else {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return '';
	}

	/** This filter is documented in wp-includes/author-template.php */
	$member_bio = apply_filters( 'the_author_description', get_the_author_meta( 'description', $user_id ) );

	if ( ! $member_bio ) {
		$member_bio = esc_html__( 'Ce membre n’a pas renseigné sa présentation pour le moment.', 'reception' );
	}

	if ( $params['blockTitle'] ) {
		$block_title = str_replace( '{{member.name}}', bp_core_get_user_displayname( $user_id ), $params['blockTitle'] );
		$member_bio  = sprintf(
			'<h3>%1$s</h3>%2$s<p>%3$s</p>',
			esc_html( $block_title ),
			"\n",
			$member_bio
		);
	} else {
		$member_bio = '<p>' . $member_bio . '</p>';
	}

	return sprintf( $container, $class, $member_bio );
}

/**
 * Renders the Réception Member's contact form block.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string HTML output.
 */
function reception_render_member_contact_form( $attributes = array() ) {
	$user_id = 0;
	$class   = 'static';

	$container = '<div class="reception-block-member-contact">%s</div>';
	$params    = wp_parse_args(
		$attributes,
		array(
			'blockTitle' => '',
		)
	);

	if ( bp_displayed_user_id() ) {
		$user_id = bp_displayed_user_id();
	} else {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return '';
	}

	/**
	 * Filters to add custom situations when a visitor contacts a site member.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value The list of associative arrays describing situations
	 */
	$situation_options = apply_filters( 'reception_member_contact_form_situation_options', array() );

	if ( $situation_options ) {
		$situation_options = array_merge(
			array(
				array(
					'label'         => __( 'Envoyer un message', 'reception' ),
					'value'         => 'reception-contact-member',
					'needs_content' => true,
				),
			),
			$situation_options
		);
	}

	if ( bp_is_user() ) {
		$member_contact_form = '';
		$current_user_id     = (int) bp_loggedin_user_id();
		$script_data         = array(
			'displayUserId'  => bp_displayed_user_id(),
			'loggedInUserId' => $current_user_id,
			'name'           => '',
			'email'          => '',
		);

		if ( $current_user_id && ! bp_is_my_profile() ) {
			$script_data['name']  = bp_core_get_user_displayname( $current_user_id );
			$script_data['email'] = bp_core_get_user_email( $current_user_id );
		}

		if ( $situation_options ) {
			$script_data['situations'] = wp_json_encode( $situation_options );
		}

		if ( bp_is_my_profile() ) {
			/**
			 * Filter here to edit the block's title about Member's replies to visitors.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value The block title.
			 */
			$params['blockTitle'] = apply_filters( 'reception_member_contact_form_reply_title', __( 'Répondre à un visiteur', 'reception' ) );
		}

		wp_enqueue_script( 'reception-script-member-contact-form' );
		wp_localize_script(
			'reception-script-member-contact-form',
			'receptionMemberContactForm',
			$script_data
		);

	} else {
		$situation_dropdown = '';
		if ( $situation_options ) {
			$options = '';
			foreach ( $situation_options as $option ) {
				$options .= sprintf(
					'<option value="%1$s">%2$s</option>',
					$option['value'],
					$option['label']
				);
			}

			$situation_dropdown = sprintf(
				'<label for="reception-situation">%1$s</label><select id="reception-situation" name="reception-situation" style="display: block; width:%2$s!important; max-width:%2$s!important;">%3$s</select>%4$s',
				esc_html__( 'Motif de votre contact', 'reception' ),
				'98%',
				$options,
				"\n"
			);
		}

		$member_contact_form = sprintf(
			'<label for="reception-email">%1$s</label><input type="email" name="reception-email" id="reception-email">%2$s
			<label for="reception-name">%3$s</label><input type="text" name="reception-name" id="reception-name">%2$s%4$s
			<button type="button" class="button primary" style="display: block; margin-top: 1.5em">%5$s</button>',
			esc_html__( 'Votre e-mail (obligatoire)', 'reception' ),
			"\n",
			esc_html__( 'Votre nom (obligatoire)', 'reception' ),
			$situation_dropdown,
			esc_html__( 'Rédiger votre message', 'reception' )
		);
	}

	if ( $params['blockTitle'] ) {
		$block_title         = str_replace( '{{member.name}}', bp_core_get_user_displayname( $user_id ), $params['blockTitle'] );
		$member_contact_form = sprintf(
			'<h3>%1$s</h3>%2$s<div class="reception-member-contact-form-content">%3$s</div>',
			esc_html( $block_title ),
			"\n",
			$member_contact_form
		);
	} else {
		$member_contact_form = '<div class="reception-member-contact-form-content">' . $member_contact_form . '</div>';
	}

	return sprintf( $container, $member_contact_form );
}

/**
 * Adds a path to preload on front end for the member's bio block.
 *
 * @since 1.0.0
 *
 * @param array $paths List of paths to preload.
 * @return array List of paths to preload.
 */
function reception_member_bio_preload_path( $paths = array() ) {
	if ( bp_is_my_profile() ) {
		$paths = array_merge(
			$paths,
			array(
				'/wp/v2/users/me?context=edit',
			)
		);
	}

	return $paths;
}
add_filter( 'reception_blocks_preload_paths', 'reception_member_bio_preload_path' );

/**
 * Get the member's front block template ID.
 *
 * @since 1.0.0
 *
 * @return interger  The member's front block template ID.
 */
function reception_get_member_front_id() {
	$member_front_id = bp_get_option( '_reception_default_template_id', 0 );

	/**
	 * Filter here to use a different member's front block template ID.
	 *
	 * @since 1.0.0
	 *
	 * @param interger $member_front_id The member's front block template ID.
	 */
	return (int) apply_filters( 'reception_get_member_front_id', $member_front_id );
}

/**
 * Checks whether the Block based user front is available or not.
 *
 * @since 1.0.0
 *
 * @return boolean True if the Block based user front is available. False otherwise.
 */
function reception_has_front() {
	$disable         = get_option( 'reception_disable_block_based_member_front' );
	$default_page_id = reception_get_member_front_id();

	return ! $disable && $default_page_id;
}

/**
 * Adds a WP Admin Bar My Account menu to reach User's front page.
 *
 * @since 1.0.0
 */
function reception_add_my_account_bar_menu() {
	if ( ! is_user_logged_in() || ! isset( $GLOBALS['wp_admin_bar'] ) || ! reception_has_front() ) {
		return;
	}

	$GLOBALS['wp_admin_bar']->add_node(
		array(
			'parent' => buddypress()->my_account_menu_id,
			'id'     => 'my-account-reception',
			'title'  => sprintf( '<span class="wp-admin-bar-arrow"></span>%s', esc_html_x( 'Accueil', 'My Account Front page', 'reception' ) ),
			'href'   => bp_core_get_userlink( bp_loggedin_user_id(), false, true ),
		)
	);
}
add_action( 'bp_setup_admin_bar', 'reception_add_my_account_bar_menu', 9 );

/**
 * Adds a Réception user nav item to BuddyPress user nav items for the customizer.
 *
 * @since 1.0.0
 *
 * @param array   $items  The array of menu items.
 * @param string  $type   The requested type.
 * @param string  $object The requested object name.
 * @param integer $page   The page num being requested.
 * @return array The paginated BuddyPress user nav items.
 */
function reception_add_customizer_bp_nav_menu( $items = array(), $type = '', $object = '', $page = 0 ) {
	if ( 'bp_loggedin_nav' === $object && 0 === $page && reception_has_front() ) {
		array_unshift(
			$items,
			array(
				'id'         => 'bp-reception',
				'title'      => html_entity_decode( _x( 'Accueil', 'My Account Front page', 'reception' ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
				'type'       => $type,
				'url'        => esc_url_raw( bp_core_get_userlink( bp_loggedin_user_id(), false, true ) ),
				'classes'    => 'bp-menu bp-home-nav',
				'type_label' => _x( 'lien personnalisé', 'customizer menu type label', 'reception' ),
				'object'     => $object,
				'object_id'  => -1,
			)
		);
	}

	return $items;
}
add_filter( 'customize_nav_menu_available_items', 'reception_add_customizer_bp_nav_menu', 20, 4 );

/**
 * Adds Réception specific item to the BuddyPress wp_nav_menu items.
 *
 * @since 1.0.0
 *
 * @param WP_Post $menu_item The menu item.
 * @return WP_Post The modified WP_Post object.
 */
function reception_setup_nav_menu_item( $menu_item ) {
	if ( is_admin() || ! reception_has_front() ) {
		return $menu_item;
	}

	// Prevent a notice error when using the customizer.
	$menu_classes = $menu_item->classes;

	if ( is_array( $menu_classes ) ) {
		$menu_classes = implode( ' ', $menu_item->classes );
	}

	if ( is_user_logged_in() && in_array( 'bp-home-nav', $menu_item->classes, true ) ) {
		$menu_item->url      = bp_loggedin_user_domain();
		$menu_item->guid     = $menu_item->url;
		$menu_item->_invalid = false;

		$current = bp_get_requested_url();
		if ( strpos( $current, $menu_item->url ) !== false ) {
			if ( is_array( $menu_item->classes ) ) {
				$menu_item->classes[] = 'current_page_item';
				$menu_item->classes[] = 'current-menu-item';
			} else {
				$menu_item->classes = array( 'current_page_item', 'current-menu-item' );
			}
		}
	}

	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'reception_setup_nav_menu_item', 20, 1 );

/**
 * Get email templates
 *
 * @since 1.0.0
 *
 * @return array An associative array containing the email type and the email template data.
 */
function reception_get_email_templates() {
	return apply_filters(
		'reception_get_email_templates',
		array(
			'reception-verify-visitor'  => array(
				'description'  => _x( 'Vérification de l’e-mail d’un visiteur du site', 'BP Email message description', 'reception' ),
				'term_id'      => 0,
				'post_title'   => _x( '[{{{site.name}}}] Code de validation de votre e-mail', 'BP Email message object', 'reception' ),
				'post_content' => _x( "{{reception.visitorname}}, vous souhaitez contacter {{reception.membername}}. Afin de nous assurer de la validité de votre adresse e-mail, merci de saisir ce code {{{reception.code}}} dans le champ \"Code de validation\" du formulaire de contact de {{reception.membername}}.\n\nPour accéder à nouveau au formulaire de contact, vous pouvez cliquer sur ce lien : <a href=\"{{{reception.memberurl}}}\">poursuivre mon message à {{reception.membername}}</a>.\n\nCette opération de validation est nécessaire lors de votre première prise de contact via notre site, merci de votre compréhension.", 'BP Email message html content', 'reception' ),
				'post_excerpt' => _x( "{{reception.visitorname}}, vous souhaitez contacter {{reception.membername}}. Afin de nous assurer de la validité de votre adresse e-mail, merci de saisir ce code {{{reception.code}}} dans le champ \"Code de validation\" du formulaire de contact de {{reception.membername}}.\n\nPour accéder à nouveau au formulaire de contact, vous pouvez cliquer sur ce lien :\n\n{{{reception.memberurl}}}.\n\nCette opération de validation est nécessaire lors de votre première prise de contact via notre site, merci de votre compréhension.", 'BP Email message text content', 'reception' ),
			),
			'reception-contact-member'  => array(
				'description'  => _x( 'Un visiteur du site contacte un membre', 'BP Email message description', 'reception' ),
				'term_id'      => 0,
				'post_title'   => _x( '[{{{site.name}}}] Un visiteur du site vous contacte', 'BP Email message object', 'reception' ),
				'post_content' => _x( "{{reception.visitorname}} ({{reception.visitoremail}}) vous a contacté. Voici son message :\n\n{{{reception.content}}}\n\nVous pouvez lui <a href=\"mailto:{{{reception.visitoremail}}}\">répondre directement</a> ou utiliser le site afin d’éviter de lui communiquer votre adresse e-mail : <a href=\"{{{reception.memberurl}}}\">répondre via le site</a>.", 'BP Email message html content', 'reception' ),
				'post_excerpt' => _x( "{{reception.visitorname}} <{{reception.visitoremail}}> vous a contacté. Voici son message :\n\n{{reception.content}}\n\nVous pouvez lui répondre directement en utilisant son email <{{reception.visitoremail}}> ou utiliser le site afin d’éviter de lui communiquer votre adresse e-mail :\n\n{{{reception.memberurl}}}", 'BP Email message text content', 'reception' ),
			),
			'reception-reply-visitor'   => array(
				'description'  => _x( 'Un membre du site répond à un visiteur', 'BP Email message description', 'reception' ),
				'term_id'      => 0,
				'post_title'   => _x( '[{{{site.name}}}] {{reception.membername}} vous a répondu', 'BP Email message object', 'reception' ),
				'post_content' => _x( "<a href=\"{{{reception.memberurl}}}\">{{reception.membername}}</a> vous a répondu. Voici cette réponse :\n\n{{{reception.content}}}\n\nPour contacter à nouveau {{reception.membername}}, vous pouvez utiliser son <a href=\"{{{reception.memberurl}}}\">formulaire de contact depuis notre site</a>.", 'BP Email message html content', 'reception' ),
				'post_excerpt' => _x( "{{reception.membername}} vous a répondu. Voici cette réponse :\n\n{{reception.content}}\n\nPour contacter à nouveau {{reception.membername}}, vous pouvez utiliser son formulaire de contact depuis notre site :\n\n{{{reception.memberurl}}}", 'BP Email message text content', 'reception' ),
			),
			'reception-members-message' => array(
				'description'  => _x( 'Un membre du site écrit à un autre', 'BP Email message description', 'reception' ),
				'term_id'      => 0,
				'post_title'   => _x( '[{{{site.name}}}] {{reception.membername}} vous a écrit', 'BP Email message object', 'reception' ),
				'post_content' => _x( "<a href=\"{{{reception.memberurl}}}\">{{reception.membername}}</a> vous a contacté. Voici son message :\n\n{{{reception.content}}}\n\nPour répondre à {{reception.membername}}, vous pouvez utiliser son <a href=\"{{{reception.memberurl}}}\">formulaire de contact depuis notre site</a>.", 'BP Email message html content', 'reception' ),
				'post_excerpt' => _x( "{{reception.membername}} Voici son message :\n\n{{reception.content}}\n\nPour répondre à {{reception.membername}}, vous pouvez utiliser son formulaire de contact depuis notre site :\n\n{{{reception.memberurl}}}", 'BP Email message text content', 'reception' ),
			),
		)
	);
}

/**
 * Returns the Verified emails DB Table name.
 *
 * @since 1.0.0
 *
 * @return string The Verified emails DB Table name.
 */
function reception_get_email_verification_table_name() {
	return bp_core_get_table_prefix() . 'reception_verified_emails';
}

/**
 * Validate an email entry.
 *
 * @since 1.0.0
 *
 * @param object $entry The email verification entry.
 * @return object The email verification entry
 */
function reception_set_email_verification_entry( $entry ) {
	if ( ! isset( $entry->id ) || ! isset( $entry->is_confirmed ) || ! isset( $entry->is_spam ) ) {
		return false;
	}

	$entry->id           = (int) $entry->id;
	$entry->is_confirmed = (bool) $entry->is_confirmed;
	$entry->is_spam      = (bool) $entry->is_spam;

	return $entry;
}

/**
 * Gets the verification entry for a given email.
 *
 * @since 1.0.0
 *
 * @param string $email_hash The hash of the email.
 * @return WP_Error|string An error if no email was given.
 *                         The verification entry otherwise.
 */
function reception_get_email_verification_entry( $email_hash = '' ) {
	if ( ! $email_hash ) {
		return new WP_Error(
			'reception_email_empty_error',
			__( 'Désolé, aucune adresse e-mail fournie.', 'reception' )
		);
	}

	global $wpdb;
	$table = reception_get_email_verification_table_name();

	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email_hash = %s", $email_hash ) ); // phpcs:ignore
}

/**
 * Gets the verification entries according to specified arguments.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     The arguments used to query verification entries.
 *     @type array {
 *         @type integer        $per_page  Optional. The number of entries per page. Default: 20.
 *         @type integer        $page      Optional. The results page to get. Default: 1.
 *         @type string         $orderby   Optional. The way to sort results. Default: 'date_confirmed'.
 *                                         Accepts 'date_confirmed', 'id' or 'date_last_email_sent'.
 *         @type string         $order     Optional. The Descending or Ascending order. Default 'DESC'.
 *                                         Accepts 'DESC' or 'ASC'.
 *         @type string|boolean $confirmed Optional. Whether to get any, confirmed, or unconfirmed entries. Default 'any'.
 *                                         Accepts 'any', 'true' or 'false'.
 *         @type string|boolean $spammed   Optional. Whether to get any, spammed, or unspammed entries. Default 'any'.
 *                                         Accepts 'any', 'true' or 'false'.
 *         @type string         $email     Optional. the email to get information about.
 *     }
 * }
 * @return array The found verification entries.
 */
function reception_get_email_verification_entries( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'per_page'  => 20,
			'page'      => 1,
			'orderby'   => 'date_confirmed',
			'order'     => 'DESC',
			'confirmed' => 'any',
			'spammed'   => 'any',
			'email'     => '',
		)
	);

	$page = 1;
	if ( is_numeric( $args['page'] ) && 0 !== $args['page'] ) {
		$page = absint( $page );
	}

	$per_page = 20;
	if ( is_numeric( $args['per_page'] ) && 0 !== $args['per_page'] ) {
		$per_page = absint( $args['per_page'] );
	}

	// Sets the limits.
	$limits = sprintf(
		'LIMIT %1$s, %2$s',
		absint( ( $page - 1 ) * $per_page ),
		$per_page
	);

	$orderby = 'date_confirmed';
	if ( 'date_confirmed' !== $args['orderby'] && in_array( $args['orderby'], array( 'id', 'date_last_email_sent' ), true ) ) {
		$orderby = $args['orderby'];
	}

	$order = 'DESC';
	if ( 'ASC' === strtoupper( $args['order'] ) ) {
		$order = 'ASC';
	}

	// Sets the sort order.
	$sort = sprintf(
		'ORDER BY %1$s %2$s',
		$orderby,
		$order
	);

	global $wpdb;
	$table = reception_get_email_verification_table_name();

	// Builds the WHERE clauses.
	$where = array();

	if ( 'any' !== $args['confirmed'] ) {
		$confirmed          = (bool) $args['confirmed'];
		$where['confirmed'] = $wpdb->prepare( 'is_confirmed = %d', $confirmed );
	}

	if ( 'any' !== $args['spammed'] ) {
		$spammed          = (bool) $args['spammed'];
		$where['spammed'] = $wpdb->prepare( 'is_spam = %d', $spammed );
	}

	if ( '' !== $args['email'] ) {
		$email = is_email( $args['email'] );

		if ( $email ) {
			$email_hash          = wp_hash( $email );
			$where['email_hash'] = $wpdb->prepare( 'email_hash = %s', $email_hash );
		}
	}

	$sql_where = '';
	if ( $where ) {
		$sql_where = 'WHERE ' . implode( ' AND ', $where );
	}

	$found_entries = 0;
	$entries       = $wpdb->get_results( "SELECT * FROM {$table} {$sql_where} {$sort} {$limits}" ); // phpcs:ignore

	if ( ! is_null( $entries ) && count( $entries ) > 0 ) {
		$found_entries = (int) $wpdb->get_var( "SELECT count( * ) as found_entries FROM {$table} {$sql_where}" ); // phpcs:ignore
	}

	return array(
		'entries'       => array_map( 'reception_set_email_verification_entry', $entries ),
		'found_entries' => $found_entries,
	);
}

/**
 * Updates the last use date verification entry.
 *
 * @since 1.0.0
 *
 * @param integer $id The unique identifier of the verification entry.
 * @return WP_error|string An error if no rows were update.The last use date otherwise.
 */
function reception_update_last_use_date_email_verification_entry( $id = '' ) {
	global $wpdb;

	$last_use_date = current_time( 'mysql' );

	// Update the last use date.
	$updated = $wpdb->update( // phpcs:ignore
		reception_get_email_verification_table_name(),
		array(
			'date_last_email_sent' => $last_use_date,
		),
		array( 'id' => (int) $id ),
		'%s',
		'%d'
	);

	if ( 1 !== $updated ) {
		return new WP_Error(
			'reception_email_last_use_date_error',
			__( 'Désolé, une erreur est survenue lors de la mise à jour de la date d’utilisation de l’e-mail.', 'reception' )
		);
	}

	return $last_use_date;
}

/**
 * Gets the status of the verification for a given email.
 *
 * @since 1.0.0
 *
 * @param object|string $email The email verification object or the hash of the email.
 * @return WP_Error|string An error if no email was given.
 *                         The status of the verification otherwise.
 */
function reception_get_email_verification_status( $email = '' ) {
	if ( ! is_object( $email ) ) {
		$row = reception_get_email_verification_entry( $email );
	} else {
		$row = $email;
	}

	$retval = 'not_created';

	if ( is_null( $row ) ) {
		return $retval;
	}

	if ( isset( $row->confirmation_code ) && $row->confirmation_code ) {
		$retval = 'waiting_confirmation';
	}

	if ( isset( $row->is_confirmed ) && true === (bool) $row->is_confirmed ) {
		$retval = 'confirmed';
	}

	if ( isset( $row->is_spam ) && true === (bool) $row->is_spam ) {
		$retval = 'spammed';
	}

	return $retval;
}

/**
 * Inserts a new email to verify.
 *
 * @since 1.0.0
 *
 * @param string $email The email to verify.
 * @return WP_Error|array An error on failure.
 *                        An array containing the inserted ID, the email and the confirmation code otherwise.
 */
function reception_insert_email_to_verify( $email = '' ) {
	$email = is_email( $email );

	if ( ! $email ) {
		return new WP_Error(
			'reception_email_format_error',
			__( 'Désolé, l’e-mail fourni ne respecte pas le format d’une adresse e-mail.', 'reception' )
		);
	}

	$email_hash = wp_hash( $email );

	if ( 'not_created' !== reception_get_email_verification_status( $email_hash ) ) {
		return new WP_Error(
			'reception_email_already_exists',
			__( 'Désolé, l’e-mail fourni a déjà fait l’objet d’une demande de vérification.', 'reception' )
		);
	}

	global $wpdb;
	$confirmation_code = substr( md5( time() . wp_rand() . $email ), 0, 16 );

	// Insert the data.
	$inserted = $wpdb->insert( // phpcs:ignore
		reception_get_email_verification_table_name(),
		array(
			'email_hash'        => $email_hash,
			'confirmation_code' => $confirmation_code,
		),
		array( '%s', '%s' )
	);

	if ( 1 !== $inserted ) {
		return new WP_Error(
			'reception_email_insertion_error',
			__( 'Désolé, la demande de vérification de l’e-mail fourni n’a pu être créée.', 'reception' )
		);
	}

	return array(
		'id'                => $wpdb->insert_id,
		'email_hash'        => $email_hash,
		'confirmation_code' => $confirmation_code,
		'email'             => $email,
	);
}

/**
 * Validates an email to verify.
 *
 * @since 1.0.0
 *
 * @param string $email The email to verify.
 * @param string $code  The verification code.
 * @return WP_Error|array An error on failure. The updated object otherwise.
 */
function reception_validate_email_to_verify( $email = '', $code = '' ) {
	$email = is_email( $email );

	if ( ! $email || ! $code ) {
		return new WP_Error(
			'reception_email_or_code_error',
			__( 'Désolé, l’e-mail ou le code fourni ne respectent pas le format attendu.', 'reception' )
		);
	}

	$email_hash   = wp_hash( $email );
	$email_entry  = reception_get_email_verification_entry( $email_hash );
	$email_status = reception_get_email_verification_status( $email_entry );
	$email_error  = new WP_Error();

	if ( 'waiting_confirmation' !== $email_status ) {
		if ( 'not_created' === $email_status ) {
			$email_error->add(
				'reception_email_not_created_error',
				__( 'Désolé, cet e-mail n’a pas été soumis pour vérification.', 'reception' )
			);
		} elseif ( 'confirmed' === $email_status ) {
			$email_error->add(
				'reception_email_confirmed_error',
				__( 'Désolé, cet e-mail a déjà été vérifié.', 'reception' )
			);
		} else {
			$email_error->add(
				'reception_email_spammed_error',
				__( 'Désolé, cet e-mail a été maqué comme indésirable.', 'reception' )
			);
		}

		return $email_error;
	}

	if ( $code !== $email_entry->confirmation_code ) {
		$email_error->add(
			'reception_email_wrong_code_error',
			__( 'Désolé, le code fourni n’est pas valide.', 'reception' )
		);

		return $email_error;
	}

	global $wpdb;

	$update_params = array(
		'is_confirmed'   => true,
		'date_confirmed' => current_time( 'mysql' ),
	);

	// Update the data.
	$updated = $wpdb->update( // phpcs:ignore
		reception_get_email_verification_table_name(),
		$update_params,
		array( 'id' => $email_entry->id )
	);

	if ( 1 !== $updated ) {
		$email_error->add(
			'reception_email_verficiation_unknown_error',
			__( 'Désolé, nous ne sommes pas parvenu à valider votre e-mail.', 'reception' )
		);

		return $email_error;
	}

	return array_merge( (array) $email_entry, $update_params );
}

/**
 * Updates the spam status of an email verification entry.
 *
 * @since 1.0.0
 *
 * @param integer $id     The unique numerique identifier of the entry.
 * @param string  $status The spam status to apply.
 * @return boolean True on successful update. False otherwise.
 */
function reception_update_spam_status( $id = 0, $status = 'spam' ) {
	$stati = array(
		'spam'   => 1,
		'unspam' => 0,
	);

	if ( ! $id || ! isset( $stati[ $status ] ) ) {
		return false;
	}

	global $wpdb;

	// Update the spam status.
	$updated = $wpdb->update( // phpcs:ignore
		reception_get_email_verification_table_name(),
		array( 'is_spam' => $stati[ $status ] ),
		array( 'id' => $id )
	);

	if ( 1 !== $updated ) {
		return false;
	}

	return true;
}
