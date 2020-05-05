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
	$js_base_url = trailingslashit( reception()->url ) . 'js/blocks/';

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
		$member_bio = sprintf(
			'<h3>%1$s</h3>%2$s<p>%3$s</p>',
			esc_html( $params['blockTitle'] ),
			"\n",
			$member_bio
		);
	} else {
		$member_bio = '<p>' . $member_bio . '</p>';
	}

	return sprintf( $container, $class, $member_bio );
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
