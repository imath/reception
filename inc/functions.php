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
				'view_items'             => _x( 'Afficher les pages de réception', 'Label for the new item page title', 'reception' ),
				'search_items'           => _x( 'Rechercher des pages de réception', 'Label for searching plural items', 'reception' ),
				'not_found'              => _x( 'Aucune page de réception trouvée', 'Label used when no items are found', 'reception' ),
				'all_items'              => _x( 'Toutes les pages de réception', 'Label to signify all items in a submenu lin', 'reception' ),
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
}
add_action( 'bp_init', 'reception_init' );

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
			'style'              => 'reception-block-member-contact-form',
			'style_url'          => $css_base_url . 'block-member-contact-form.css',
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

function reception_register_scripts() {
	if ( ! bp_is_my_profile() ) {
		return;
	}

	$js_base_url  = trailingslashit( reception()->url ) . 'js/scripts/';
	$css_base_url = trailingslashit( reception()->url ) . 'assets/css/';
	$version      = reception_get_version();

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

	$member_contact_form = sprintf(
		'<label for="reception-email">%1$s</label><input type="email" name="reception-email" id="reception-email">%2$s
		<label for="reception-name">%3$s</label><input type="text" name="reception-name" id="reception-name">%2$s
		<label for="reception-message">%4$s</label><textarea name="reception-message" id="reception-message"></textarea>',
		esc_html__( 'Votre e-mail', 'reception' ),
		"\n",
		esc_html__( 'Votre nom', 'reception' ),
		esc_html__( 'Votre message', 'reception' )
	);

	if ( $params['blockTitle'] ) {
		$block_title = str_replace( '{{member.name}}', bp_core_get_user_displayname( $user_id ), $params['blockTitle'] );
		$member_contact_form  = sprintf(
			'<h3>%1$s</h3>%2$s<p class="reception-member-contact-form-content">%3$s</p>',
			esc_html( $block_title ),
			"\n",
			$member_contact_form
		);
	} else {
		$member_contact_form = '<p class="reception-member-contact-form-content">' . $member_contact_form . '</p>';
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
 * Get email templates
 *
 * @since 1.0.0
 *
 * @return array An associative array containing the email type and the email template data.
 */
function reception_get_email_templates() {
	return apply_filters( 'reception_get_email_templates', array(
		'reception-verify-visitor' => array(
			'description' => _x( 'Vérification de l’e-mail d’un visiteur du site', 'BP Email message description', 'reception' ),
			'term_id'     => 0,
			'post_title'   => _x( '[{{{site.name}}}] Code de validation de votre e-mail', 'BP Email message object', 'reception' ),
			'post_content' => _x( "Bonjour {{reception.visitorname}},\n\nVous souhaitez contacter {{reception.membername}}. Afin de nous assurer de la validité de votre adresse e-mail, merci de saisir ce code {{{reception.code}}} dans le champ \"Code de validation\" du formulaire de contact de {{reception.membername}}.\n\nPour accéder à nouveau au formulaire de contact, vous pouvez cliquer sur ce lien : <a href=\"{{{reception.memberurl}}}\">poursuivre mon message à {{reception.membername}}</a>.\n\nCette opération de validation est nécessaire lors de votre première prise de contact via notre site, merci de votre compréhension.", 'BP Email message html content', 'reception' ),
			'post_excerpt' => _x( "Bonjour {{reception.visitorname}},\n\nVous souhaitez contacter {{reception.membername}}. Afin de nous assurer de la validité de votre adresse e-mail, merci de saisir ce code {{{reception.code}}} dans le champ \"Code de validation\" du formulaire de contact de {{reception.membername}}.\n\nPour accéder à nouveau au formulaire de contact, vous pouvez cliquer sur ce lien :\n\n{{{reception.memberurl}}}.\n\nCette opération de validation est nécessaire lors de votre première prise de contact via notre site, merci de votre compréhension.", 'BP Email message text content', 'reception' ),
		),
		'reception-confirm-visitor' => array(
			'description' => _x( 'Confirmation de l’envoi d’un e-mail par un visiteur du site', 'BP Email message description', 'reception' ),
			'term_id'     => 0,
			'post_title'   => _x( '[{{{site.name}}}] Confirmation de l’envoi de votre message', 'BP Email message object', 'reception' ),
			'post_content' => _x( "{{reception.visitorname}},\n\nNous avons bien envoyé votre message à {{reception.membername}}. Il vous répondra dans les meilleurs délais.\n\nBonne journée.", 'BP Email message html content', 'reception' ),
			'post_excerpt' => _x( "{{reception.visitorname}},\n\nNous avons bien envoyé votre message à {{reception.membername}}. Il vous répondra dans les meilleurs délais.\n\nBonne journée.", 'BP Email message text content', 'reception' ),
		),
		'reception-contact-member' => array(
			'description' => _x( 'Un visiteur du site contacte un membre', 'BP Email message description', 'reception' ),
			'term_id'     => 0,
			'post_title'   => _x( '[{{{site.name}}}] Un visiteur du site vous contacte', 'BP Email message object', 'reception' ),
			'post_content' => _x( "{{reception.visitorname}} ({{reception.visitoremail}}) vous a contacté. Voici son message :\n\n{{{reception.content}}}\n\nVous pouvez lui <a href=\"mailto:{{{reception.visitoremail}}}\">répondre directement</a> ou utiliser le site afin d’éviter de lui communiquer votre adresse e-mail : <a href=\"{{{reception.memberurl}}}\">répondre via le site</a>.", 'BP Email message html content', 'reception' ),
			'post_excerpt' => _x( "{{reception.visitorname}} <{{reception.visitoremail}}> vous a contacté. Voici son message :\n\n{{reception.content}}\n\nVous pouvez lui répondre directement en utilisant son email <{{reception.visitoremail}}> ou utiliser le site afin d’éviter de lui communiquer votre adresse e-mail :\n\n{{{reception.memberurl}}}", 'BP Email message text content', 'reception' ),
		),
		'reception-reply-visitor' => array(
			'description' => _x( 'Un membre du site répond à un visiteur', 'BP Email message description', 'reception' ),
			'term_id'     => 0,
			'post_title'   => _x( '[{{{site.name}}}] {{reception.membername}} vous a répondu', 'BP Email message object', 'reception' ),
			'post_content' => _x( "Bonjour,\n\nVoici cette réponse :\n\n{{{reception.content}}}\n\nPour contacter à nouveau {{reception.membername}}, vous pouvez utiliser son <a href=\"{{{reception.memberurl}}}\">formulaire de contact depuis notre site</a>.", 'BP Email message html content', 'reception' ),
			'post_excerpt' => _x( "Bonjour,\n\nVoici cette réponse :\n\n{{reception.content}}\n\nPour contacter à nouveau {{reception.membername}}, vous pouvez utiliser son formulaire de contact depuis notre site :\n\n{{{reception.memberurl}}}", 'BP Email message text content', 'reception' ),
		),
	) );
}
