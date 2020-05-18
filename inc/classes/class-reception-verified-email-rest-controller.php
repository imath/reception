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

		// Every visitor can submit his email for verification.
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

		// Return an error.
		return new WP_Error(
			'reception_create_verified_email_failed',
			__( 'Désolé, votre e-mail n’a pu être soumis pour vérification.', 'reception' ),
			array(
				'status' => 500,
			)
		);
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
	 * Prepares verified email data for the response.
	 *
	 * @since 1.0.0
	 *
	 * @param object          $email Verified email object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $email, $request ) {
		$data    = get_object_vars( $email );
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		return $response;
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
						'context'           => array( 'view', 'edit' ),
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
