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

		return $retval;
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

		return $retval;
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

		return $retval;
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

		return $retval;
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

		return rest_ensure_response( $data );
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

		return $args;
	}

	/**
	 * Retrieves the query params for the verified emails collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		/**
		 * Filters the collection query params.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Query params.
		 */
		return apply_filters( 'reception_rest_collection_params', $params );
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

		return $this->add_additional_fields_schema( $this->schema );
	}
}
