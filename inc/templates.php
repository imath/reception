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
		array( trailingslashit( reception()->tpl_dir ) . 'buddypress' )
	);
}
add_filter( 'bp_get_template_stack', 'reception_template_stack', 10, 1 );

/**
 * Use a specific email template for Reception emails.
 *
 * @since 1.0.0
 *
 * @param array $templates The list of email templates.
 * @return array The list of email templates.
 */
function reception_set_email_template( $templates = array() ) {
	if ( is_array( $templates ) ) {
		array_unshift( $templates, 'assets/emails/single-reception-email.php' );
	}

	return $templates;
}

/**
 * Outputs the Member's front page content.
 *
 * @since 1.0.0
 */
function reception_content() {
	$reception_page_id = reception_get_member_front_id();
	$is_wp_5_5         = function_exists( 'wp_filter_content_tags' );
	$missing_template  = __( 'Le gabarit pour la page d’accueil personnalisée du membre est introuvable.', 'reception' );

	if ( ! $reception_page_id ) {
		printf( '<div class="reception-missing-template"><p>%s</p></div>', esc_html( $missing_template ) );

		return;
	}

	// Get the Réception template.
	$reception = get_post( $reception_page_id );

	if ( ! $reception->ID ) {
		printf( '<div class="reception-missing-template"><p>%s</p></div>', esc_html( $missing_template ) );

		return;
	}

	/**
	 * Preload common data by specifying an array of REST API paths that will be preloaded.
	 *
	 * Filters the array of paths that will be preloaded.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $preload_paths Array of paths to preload.
	 * @param WP_Post  $reception The reception front page content.
	 */
	$preload_paths = apply_filters( 'reception_blocks_preload_paths', array(), $reception );

	if ( $preload_paths ) {
		$preload_data = array_reduce(
			$preload_paths,
			'rest_preload_api_request',
			array()
		);

		wp_add_inline_script(
			'wp-api-fetch',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_data ) ),
			'after'
		);
	}

	add_filter( 'get_reception_content', 'do_blocks', 9 );
	add_filter( 'get_reception_content', 'wptexturize' );
	add_filter( 'get_reception_content', 'convert_smilies', 20 );
	add_filter( 'get_reception_content', 'shortcode_unautop' );
	add_filter( 'get_reception_content', 'prepend_attachment' );

	if ( $is_wp_5_5 ) {
		add_filter( 'get_reception_content', 'wp_filter_content_tags' );
	}

	/**
	 * Filter here to add sanitization filters to the front page content.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $reception_content The Member's front page content.
	 * @param WP_Post $reception         The Member's front page template object.
	 */
	$reception_content = apply_filters( 'get_reception_content', $reception->post_content );

	if ( ! trim( $reception_content, " \n\t" ) ) {
		printf(
			'<div class="reception-missing-blocks"><p>%s</p></div>',
			sprintf(
				/* translators: %s is the link to edit the Reception Blocks template */
				esc_html__( 'Le gabarit ne contient aucun élément à afficher. %s.', 'reception' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( reception_get_edit_template_link() ),
					esc_html__( 'Merci de le mettre à jour', 'reception' )
				)
			)
		);
	}

	remove_filter( 'get_reception_content', 'do_blocks', 9 );
	remove_filter( 'get_reception_content', 'wptexturize' );
	remove_filter( 'get_reception_content', 'convert_smilies', 20 );
	remove_filter( 'get_reception_content', 'shortcode_unautop' );
	remove_filter( 'get_reception_content', 'prepend_attachment' );

	if ( $is_wp_5_5 ) {
		remove_filter( 'get_reception_content', 'wp_filter_content_tags' );
	}

	/**
	 * Filter here to edit the Member's front page content once sanitized.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $reception_content The Member's front page content.
	 * @param WP_Post $reception         The Member's front page template object.
	 */
	echo apply_filters( 'reception_content', $reception_content, $reception ); // phpcs:ignore
}

/**
 * Returns the link to edit the Member's front Block template.
 *
 * @since 1.0.0
 *
 * @return string The link to edit the Member's front Block template.
 */
function reception_get_edit_template_link() {
	$reception_page_id = reception_get_member_front_id();

	return get_edit_post_link( $reception_page_id );
}

/**
 * Displays the link to edit the Member's front Block template.
 *
 * @since 1.0.0
 *
 * @param string $text   Optional. Anchor text. If null, default is 'Edit This'. Default null.
 * @param string $before Optional. Display before edit link. Default empty.
 * @param string $after  Optional. Display after edit link. Default empty.
 */
function reception_edit_post_link( $text = null, $before = '', $after = '' ) {
	$reception_page_id = reception_get_member_front_id();

	edit_post_link( $text, $before, $after, $reception_page_id );
}

/**
 * Loads a companion stylesheet if if exists into the theme.
 *
 * @since 1.0.0
 */
function reception_enqueue_companion_stylesheet() {
	if ( ! bp_is_user_front() ) {
		return;
	}

	$companion_style_path = get_theme_file_path( 'css/reception.css' );

	// Enqueues the companion stylesheet if available.
	if ( file_exists( $companion_style_path ) ) {
		wp_enqueue_style(
			'reception-companion-style',
			esc_url_raw( get_theme_file_uri( 'css/reception.css' ) ),
			array(),
			reception_get_version()
		);
	}
}
add_action( 'bp_enqueue_scripts', 'reception_enqueue_companion_stylesheet' );
