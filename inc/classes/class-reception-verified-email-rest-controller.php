<?php
/**
 * Réception Verified Email REST Controller.
 *
 * @package reception
 * @subpackage \inc\classes\class-reception-email-rest-controller
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Réception Verified Email REST Controller Class.
 *
 * @since 1.0.0
 */
class Reception_Verified_Email_REST_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = 'reception/v1';
		$this->rest_base = 'email';
	}

	/**
	 * Registers the routes for the verified email objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Un identifiant numérique unique pour l’e-mail vérifié.', 'reception' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Register the email's verified check route.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/check/(?P<email>[\S]+)',
			array(
				'args'   => array(
					'email' => array(
						'description' => __( 'L’e-mail du visiteur à vérifier.', 'reception' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'check_item' ),
					'permission_callback' => array( $this, 'check_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Register the email's verified validate route.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/validate/(?P<email>[\S]+)',
			array(
				'args'   => array(
					'email' => array(
						'description' => __( 'L’e-mail du visiteur à valider.', 'reception' ),
						'type'        => 'string',
						'required'    => true,
					),
					'code'  => array(
						'description' => __( 'Le code de validation à vérifier.', 'reception' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'validate_item' ),
					'permission_callback' => array( $this, 'validate_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'edit' ) ),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Register the email's verified validate route.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/send/(?P<member_id>[\d]+)',
			array(
				'args'   => array(
					'message'      => array(
						'description' => __( 'Le message à envoyer.', 'reception' ),
						'type'        => 'string',
						'required'    => true,
					),
					'current_user' => array(
						'description' => __( 'L’identifiant numérique unique de l’utilisateur connecté.', 'reception' ),
						'type'        => 'integer',
						'default'     => 0,
					),
					'situation'    => array(
						'description' => __( 'L’identifiant unique de la situation de l’e-mail.', 'reception' ),
						'type'        => 'string',
						'default'     => 'reception-contact-member',
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_item' ),
					'permission_callback' => array( $this, 'send_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Checks if a given request has access to verified emails.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$retval = true;

		// Capability check.
		if ( ! current_user_can( 'edit_users' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à lister les emails vérfiés.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `get_items` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_get_verified_emails_rest_permissions_check', $retval, $request );
	}

	/**
	 * Retrieves verified emails.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response List of verified emails response data.
	 */
	public function get_items( $request ) {
		// Retrieve the list of registered collection query parameters.
		$registered    = $this->get_collection_params();
		$prepared_args = array();

		foreach ( $registered as $param_key => $param_property ) {
			$requested = $request->get_param( $param_key );

			if ( is_null( $requested ) ) {
				$requested = $param_property['default'];
			}

			if ( 'order' === $param_key ) {
				$requested = strtoupper( $requested );
			}

			if ( in_array( $param_key, array( 'confirmed', 'spammed' ), true ) && 'any' !== $requested ) {
				$requested = rest_sanitize_boolean( $requested );
			}

			$prepared_args[ $param_key ] = $requested;
		}

		$results = reception_get_email_verification_entries( $prepared_args );
		$entries = array();

		foreach ( $results['entries'] as $entry ) {
			$data      = $this->prepare_item_for_response( (array) $entry, $request );
			$entries[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $entries );
		$response = bp_rest_response_add_total_headers( $response, $results['found_entries'], $prepared_args['per_page'] );

		// Return the response.
		return $response;
	}

	/**
	 * Checks if a given request has access to a verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$retval = true;

		// Capability check.
		if ( ! current_user_can( 'edit_users' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à lister les emails vérfiés.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `get_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_get_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Retrieves a verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response List of verified emails response data.
	 */
	public function get_item( $request ) {
		$response = rest_ensure_response( array() );

		return $response;
	}

	/**
	 * Checks if the user can create a new verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		$retval = true;

		// Every visitor can submit their email for verification.
		if ( ! current_user_can( 'exist' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à soumettre votre e-mail pour vérification.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `create_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_create_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Creates a verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$email     = $request->get_param( 'email' );
		$name      = $request->get_param( 'name' );
		$member_id = $request->get_param( 'member_id' );

		// Get the member the visitor wants to contact.
		$member = bp_rest_get_user( $member_id );

		if ( ! $member ) {
			// Return an error.
			return new WP_Error(
				'reception_create_verified_email_unknown_member',
				__( 'Désolé, la personne que vous souhaitez contacter ne semble pas être un membre du site.', 'reception' ),
				array(
					'status' => 500,
				)
			);
		}

		// Get the member url.
		$member_url = bp_core_get_userlink( $member->ID, false, true );
		$inserted   = reception_insert_email_to_verify( $email );

		if ( is_wp_error( $inserted ) ) {
			$inserted->add_data(
				array(
					'status' => 500,
				)
			);

			return $inserted;
		}

		$notify = bp_send_email(
			'reception-verify-visitor',
			$inserted['email'],
			array(
				'tokens' => array(
					'reception.visitorname' => esc_html( $name ),
					'reception.membername'  => esc_html( $member->display_name ),
					'reception.code'        => $inserted['confirmation_code'],
					'reception.memberurl'   => esc_url_raw( $member_url ),
				),
			)
		);

		if ( is_wp_error( $notify ) ) {
			return new WP_Error(
				'reception_create_verified_email_failed',
				__( 'Désolé, nous ne sommes pas parvenus à vous envoyer le code de confirmation.', 'reception' ),
				array(
					'status' => 500,
				)
			);
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response(
			wp_parse_args(
				$inserted,
				array(
					'id'                   => 0,
					'email_hash'           => '',
					'confirmation_code'    => '',
					'is_confirmed'         => false,
					'is_spam'              => false,
					'date_confirmed'       => '0000-00-00 00:00:00',
					'date_last_email_sent' => '0000-00-00 00:00:00',
				)
			),
			$request
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if the user can delete a verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$retval = true;

		// Capability check.
		if ( ! current_user_can( 'delete_users' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à supprimer cet e-mail des e-mails vérifiés.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `delete_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_delete_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Deletes a verified email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		// Return an error.
		return new WP_Error(
			'reception_delete_verified_email_failed',
			__( 'Désolé, l’e-mail n’a pu être supprimé des emails vérifiés.', 'reception' ),
			array(
				'status' => 500,
			)
		);
	}

	/**
	 * Checks if the user can get the verification entry of their email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function check_item_permissions_check( $request ) {
		$retval = true;

		// Every visitor can get the verification entry of their email.
		if ( ! current_user_can( 'exist' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à vérifier si votre e-mail a été validé.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `check_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_check_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Returns the verification entry of the visitor's email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function check_item( $request ) {
		$email      = $request->get_param( 'email' );
		$email_hash = wp_hash( $email );

		// Get the verification entry for the visitor's email.
		$entry = reception_get_email_verification_entry( $email_hash );

		$response = $this->prepare_item_for_response(
			wp_parse_args(
				$entry,
				array(
					'id'                   => 0,
					'email_hash'           => '',
					'confirmation_code'    => '',
					'is_confirmed'         => false,
					'is_spam'              => false,
					'date_confirmed'       => '0000-00-00 00:00:00',
					'date_last_email_sent' => '0000-00-00 00:00:00',
				)
			),
			$request
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if the user can check his email has been verified.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function validate_item_permissions_check( $request ) {
		$retval = true;

		// Every visitor can validate their email.
		if ( ! current_user_can( 'exist' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à valider cet e-mail.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `validate_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_validate_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Returns the verification status of the visitor's email.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function validate_item( $request ) {
		$email = $request->get_param( 'email' );
		$code  = $request->get_param( 'code' );

		// Verify and validate the email.
		$validated = reception_validate_email_to_verify( $email, $code );

		if ( is_wp_error( $validated ) ) {
			$validated->add_data(
				array(
					'status' => 500,
				)
			);

			return $validated;
		}

		$response = $this->prepare_item_for_response( $validated, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if the user can send an email to a member.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function send_item_permissions_check( $request ) {
		$retval = true;

		// Every visitor can send their email.
		if ( ! current_user_can( 'exist' ) ) {
			$retval = new WP_Error(
				'reception_rest_authorization_required',
				__( 'Désolé, vous n’êtes pas autorisé·e à envoyer un e-mail à ce membre.', 'reception' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the verified email `send_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'reception_send_verified_email_rest_permissions_check', $retval, $request );
	}

	/**
	 * Sends an email to the member or let a member reply to a visitor.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function send_item( $request ) {
		$member_id           = $request->get_param( 'member_id' );
		$current_user_id     = $request->get_param( 'current_user' );
		$email               = $request->get_param( 'email' );
		$name                = $request->get_param( 'name' );
		$message             = $request->get_param( 'message' );
		$requested_situation = $request->get_param( 'situation' );
		$update_last_email   = false;

		// Get the member the visitor wants to contact.
		$member  = bp_rest_get_user( $member_id );
		$is_self = (int) get_current_user_id() === (int) $member_id;

		if ( ! $member ) {
			// Return an error.
			return new WP_Error(
				'reception_create_verified_email_unknown_member',
				__( 'Désolé, la personne que vous souhaitez contacter ne semble pas être un membre du site.', 'reception' ),
				array(
					'status' => 500,
				)
			);
		}

		if ( ! $current_user_id || 0 === $current_user_id ) {
			// Hash the email.
			$email_hash = wp_hash( $email );

			// Get the member url and the verified visitor entry.
			$member_url   = bp_core_get_userlink( $member->ID, false, true );
			$email_entry  = reception_get_email_verification_entry( $email_hash );
			$email_status = reception_get_email_verification_status( $email_entry );

			if ( is_wp_error( $email_entry ) ) {
				$verified->add_data(
					array(
						'status' => 500,
					)
				);

				return $email_entry;
			}

			if ( 'confirmed' !== $email_status ) {
				$error_message = __( 'Désolé, vous devez valider votre e-mail avant de pouvoir contacter un membre.', 'reception' );
				if ( $is_self ) {
					$error_message = __( 'Désolé, ce visiteur n’a pas validé son e-mail, il n’est pas possible d’utiliser le site pour le contacter.', 'reception' );
				}

				// Return an error.
				return new WP_Error(
					'reception_email_confirmed_error',
					$error_message,
					array(
						'status' => 500,
					)
				);
			}
		} else {
			$email_entry = array();
		}

		if ( $is_self ) {
			/**
			 * Filters to edit the situation for a member replying to a visitor.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value The situation.
			 * @param WP_REST_Request $request Full details about the request.
			 */
			$situation = apply_filters( 'reception_member_reply_verified_email_situation', 'reception-reply-visitor', $request );

			/**
			 * Filters to edit the tokens for a member replying to a visitor.
			 *
			 * @since 1.0.0
			 *
			 * @param array $value The tokens.
			 * @param WP_REST_Request $request Full details about the request.
			 */
			$tokens = apply_filters(
				'reception_member_reply_verified_email_tokens',
				array(
					'tokens' => array(
						'reception.membername' => esc_html( $member->display_name ),
						'reception.content'    => wp_kses(
							$message,
							array(
								'p' => true,
								'a' => true,
							)
						),
						'reception.memberurl'  => esc_url_raw( $member_url ),
					),
				),
				$request
			);

			$sent = bp_send_email( $situation, $email, $tokens );

		} elseif ( $current_user_id && 0 !== $current_user_id && ! $is_self ) {
			/**
			 * Filters to edit the situation for 2 site members sending message to each others.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value The situation.
			 * @param WP_REST_Request $request Full details about the request.
			 */
			$situation = apply_filters( 'reception_members_message_verified_email_situation', 'reception-members-message', $request );

			/**
			 * Filters to edit the tokens for 2 site members sending message to each others.
			 *
			 * @since 1.0.0
			 *
			 * @param array $value The tokens.
			 * @param WP_REST_Request $request Full details about the request.
			 */
			$tokens = apply_filters(
				'reception_members_message_verified_email_tokens',
				array(
					'tokens' => array(
						'reception.membername' => esc_html( $name ),
						'reception.content'    => wp_kses(
							$message,
							array(
								'p' => true,
								'a' => true,
							)
						),
						'reception.memberurl'  => esc_url_raw( bp_core_get_userlink( $current_user_id, false, true ) ),
					),
				),
				$request
			);

			$sent = bp_send_email( $situation, $member, $tokens );

		} else {
			$situation = 'reception-contact-member';
			if ( $requested_situation ) {
				$situation = sanitize_key( $requested_situation );
			}

			/**
			 * Filters to edit the tokens for a visitor contacting a site member.
			 *
			 * @since 1.0.0
			 *
			 * @param array $value The tokens.
			 * @param string $situation The email situation.
			 * @param WP_REST_Request $request Full details about the request.
			 */
			$tokens = apply_filters(
				'reception_visitor_contact_member_verified_email_tokens',
				array(
					'tokens' => array(
						'reception.visitorname'  => esc_html( $name ),
						'reception.visitoremail' => $email,
						'reception.membername'   => esc_html( $member->display_name ),
						'reception.content'      => wp_kses(
							$message,
							array(
								'p' => true,
								'a' => true,
							)
						),
						'reception.memberurl'    => esc_url_raw( $member_url ),
					),
				),
				$situation,
				$request
			);

			$sent = bp_send_email( $situation, $member, $tokens );

			$update_last_email = true;
		}

		if ( is_wp_error( $sent ) ) {
			return new WP_Error(
				'reception_send_email_failed',
				__( 'Désolé, nous ne sommes pas parvenus à envoyer le message.', 'reception' ),
				array(
					'status' => 500,
				)
			);
		}

		// Update the last use date.
		if ( $update_last_email ) {
			$email_entry->date_last_email_sent = reception_update_last_use_date_email_verification_entry( $email_entry->id );
		}

		$request->set_param( 'context', 'edit' );

		$verified_email = $this->prepare_item_for_response(
			wp_parse_args(
				(array) $email_entry,
				array(
					'id'                   => 0,
					'email_hash'           => '',
					'confirmation_code'    => '',
					'is_confirmed'         => false,
					'is_spam'              => false,
					'date_confirmed'       => '0000-00-00 00:00:00',
					'date_last_email_sent' => '0000-00-00 00:00:00',
				)
			),
			$request
		);
		$verified_email = rest_ensure_response( $verified_email );
		$response       = new WP_REST_Response();
		$response->set_data(
			array(
				'sent'          => true,
				'verifiedEmail' => $verified_email->get_data(),
			)
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Prepares verified email data for the response.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $email Verified email data.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $email, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( in_array( 'id', $fields, true ) ) {
			$data['id'] = (int) $email['id'];
		}

		if ( in_array( 'email', $fields, true ) ) {
			$data['email'] = $email['email_hash'];
		}

		if ( in_array( 'code', $fields, true ) ) {
			$data['code'] = $email['confirmation_code'];
		}

		if ( in_array( 'confirmed', $fields, true ) ) {
			$data['confirmed'] = (bool) $email['is_confirmed'];
		}

		if ( in_array( 'spam', $fields, true ) ) {
			$data['spam'] = (bool) $email['is_spam'];
		}

		if ( in_array( 'confirmation_date', $fields, true ) ) {
			$data['confirmation_date'] = bp_rest_prepare_date_response( $email['date_confirmed'] );
		}

		if ( in_array( 'last_use_date', $fields, true ) ) {
			$data['last_use_date'] = bp_rest_prepare_date_response( $email['date_last_email_sent'] );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		/**
		 * Filter the verified email `check_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Response $response The preprared verified email for response.
		 * @param WP_REST_Response $request The request sent to the API.
		 */
		return apply_filters( 'reception_prepare_verified_email_for_response', $response, $request );
	}

	/**
	 * Set specific arguments for the CREATABLE method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method Optional. HTTP method of the request.
	 * @return array Endpoint arguments.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$args = WP_REST_Controller::get_endpoint_args_for_item_schema( $method );

		if ( WP_REST_Server::CREATABLE === $method ) {
			$args['context']['default'] = 'edit';
			$args['email']              = array(
				'description' => __( 'L’adresse e-mail du visiteur.', 'reception' ),
				'type'        => 'string',
				'format'      => 'email',
				'context'     => array( 'edit' ),
				'required'    => true,
			);
			$args['name']               = array(
				'description' => __( 'Le nom du visiteur souhaitant contacter le membre.', 'reception' ),
				'type'        => 'string',
				'context'     => array( 'edit' ),
				'required'    => true,
			);
			$args['member_id']          = array(
				'description' => __( 'L’identifiant numérique unique du membre à contacter.', 'reception' ),
				'type'        => 'integer',
				'context'     => array( 'edit' ),
				'required'    => true,
			);
		}

		/**
		 * Filters the endpoint specific arguments for item schema.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The endpoint specific arguments for item schema.
		 * @param string $method HTTP method of the request.
		 */
		return apply_filters( 'reception_rest_endpoint_args_for_verified_email_schema', $args, $method );
	}

	/**
	 * Retrieves the query params for the verified emails collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params                        = parent::get_collection_params();
		$params['context']['default']  = 'edit';
		$params['per_page']['default'] = 20;

		unset( $params['search'] );

		$params['orderby'] = array(
			'default'     => 'date_confirmed',
			'description' => __( 'Ordre de récupération des entrées.', 'reception' ),
			'enum'        => array( 'date_confirmed', 'date_last_email_sent', 'id' ),
			'type'        => 'string',
		);

		$params['order'] = array(
			'description' => __( 'Attribut de l’ordre de récupération des entrées : ascendant ou descendant.', 'reception' ),
			'default'     => 'desc',
			'type'        => 'string',
			'enum'        => array( 'asc', 'desc' ),
		);

		$params['confirmed'] = array(
			'description' => __( 'Attribut de filtrage des résultats selon que l’e-mail a été vérifié ou non.', 'reception' ),
			'default'     => 'any',
			'type'        => 'string',
			'enum'        => array( 'any', 'true', 'false' ),
		);

		$params['spammed'] = array(
			'description' => __( 'Attribut de filtrage des résultats selon que l’e-mail ait été marqué comme spam ou non.', 'reception' ),
			'default'     => 'any',
			'type'        => 'string',
			'enum'        => array( 'any', 'true', 'false' ),
		);

		$params['email'] = array(
			'description' => __( 'E-mail à rechercher dans le jeu de résultats.', 'reception' ),
			'default'     => '',
			'type'        => 'string',
		);

		/**
		 * Filters the collection query params.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Query params.
		 */
		return apply_filters( 'reception_rest_verified_emails_params', $params );
	}

	/**
	 * Retrieves the verified email's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( ! isset( $this->schema ) ) {
			$this->schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'Verified Email',
				'type'       => 'object',
				// Base properties for every verified email.
				'properties' => array(
					'id'                => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Un identifiant numérique unique pour l’e-mail vérifié.', 'reception' ),
						'readonly'    => true,
						'type'        => 'integer',
					),
					'email'             => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'La version hachée de l’email vérifié.', 'reception' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
						'default'           => '',
					),
					'code'              => array(
						'context'           => array(), // Code is never displayed.
						'description'       => __( 'Le code de validation génére pour la vérification de l’email.', 'reception' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
						'default'           => '',
					),
					'confirmed'         => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Informe si l’e-mail a été vérifié.', 'reception' ),
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
						'default'           => false,
					),
					'spam'              => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Informe si l’e-mail a été marqué comme spam.', 'reception' ),
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
						'default'           => false,
					),
					'confirmation_date' => array(
						'description' => __( 'Date à laquelle l’e-mail a été vérifié.', 'reception' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
					'last_use_date'     => array(
						'description' => __( 'Date à laquelle l’e-mail a été utilisé pour la dernière fois.', 'reception' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'edit' ),
						'readonly'    => true,
					),
				),
			);
		}

		$shema = $this->add_additional_fields_schema( $this->schema );

		/**
		 * Filters the verified email's schema.
		 *
		 * @since 1.0.0
		 *
		 * @param array $shema the verified email's schema.
		 */
		return apply_filters( 'reception_rest_verified_email_schema', $shema );
	}
}
