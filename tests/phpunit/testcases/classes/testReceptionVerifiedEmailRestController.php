<?php
/**
 * Réception Verified Email REST Controller tests.
 *
 * @package reception
 * @subpackage \tests\phpunit\testcases\classes\testReceptionVerifiedEmailRestController
 *
 * @since 1.0.0
 */

/**
 * @group rest
 */
class Reception_Verified_Email_REST_controller_UnitTestCase extends WP_Test_REST_Controller_Testcase {
	protected $current_user;
	protected $endpoint;
	protected $endpoint_url;
	protected $admin_user;
	protected $member;
	public $server;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once trailingslashit( buddypress()->plugin_dir ) . 'bp-core/admin/bp-core-admin-schema.php';

		bp_core_install_emails();
	}

	public function setUp() {
		parent::setUp();

		$this->endpoint     = new Reception_Verified_Email_REST_Controller();
		$this->endpoint_url = '/reception/v1/email';

		$this->admin_user = $this->factory->user->create( array(
			'role'       => 'administrator',
			'user_email' => 'admin@example.com',
		) );

		$this->member = $this->factory->user->create( array(
			'role'       => 'subscriber',
			'user_email' => 'subscriber@example.com',
		) );

		if ( ! $this->server ) {
			$this->server = rest_get_server();
		}

		$this->current_user = get_current_user_id();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @group rest_register_routes
	 */
	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( $this->endpoint_url, $routes );
		$this->assertCount( 2, $routes[ $this->endpoint_url ] );

		$this->assertArrayHasKey( $this->endpoint_url . '/(?P<id>[\d]+)', $routes );
		$this->assertCount( 3, $routes[$this->endpoint_url . '/(?P<id>[\d]+)'] );

		$this->assertArrayHasKey( $this->endpoint_url . '/check/(?P<email>[\S]+)', $routes );
		$this->assertCount( 1, $routes[$this->endpoint_url . '/check/(?P<email>[\S]+)'] );

		$this->assertArrayHasKey( $this->endpoint_url . '/validate/(?P<email>[\S]+)', $routes );
		$this->assertCount( 1, $routes[$this->endpoint_url . '/validate/(?P<email>[\S]+)'] );
	}

	/**
	 * @group rest_context_param
	 */
	public function test_context_param() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_get_items
	 */
	public function test_get_items() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_get_item
	 */
	public function test_get_item() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_create_item
	 */
	public function test_create_item() {
		$request = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( array(
			'email'     => 'foo@bar.com',
			'name'      => 'Foo Bar',
			'member_id' => $this->member,
		) );

		$response = $this->server->dispatch( $request );
		$get_data = $response->get_data();

		$this->assertTrue( hash_equals( $get_data['email'], wp_hash( 'foo@bar.com' ) ) );
	}

	/**
	 * @group rest_update_item
	 */
	public function test_update_item() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_delete_item
	 */
	public function test_delete_item() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_check_item
	 */
	public function test_check_item() {
		$request  = new WP_REST_Request( 'GET', $this->endpoint_url . sprintf( '/check/%s', 'check@mail.com' ) );
		$response = $this->server->dispatch( $request );
		$get_data = $response->get_data();

		$this->assertEmpty( $get_data['id'] );
	}

	/**
	 * @group rest_check_item
	 */
	public function test_check_item_not_confirmed() {
		$inserted = reception_insert_email_to_verify( 'check2@email.com' );
		$request  = new WP_REST_Request( 'GET', $this->endpoint_url . sprintf( '/check/%s', $inserted['email'] ) );
		$response = $this->server->dispatch( $request );
		$get_data = $response->get_data();

		$this->assertNotEmpty( $get_data['id'] );
		$this->assertFalse( $get_data['confirmed'] );
	}

	/**
	 * @group rest_check_item
	 */
	public function test_check_item_confirmed() {
		$inserted = reception_insert_email_to_verify( 'check3@email.com' );
		$entry    = reception_get_email_verification_entry( $inserted['email_hash'] );
		$verified = reception_validate_email_to_verify( $inserted['email'], $entry->confirmation_code );

		$request  = new WP_REST_Request( 'GET', $this->endpoint_url . sprintf( '/check/%s', $inserted['email'] ) );
		$response = $this->server->dispatch( $request );
		$get_data = $response->get_data();

		$this->assertNotEmpty( $get_data['id'] );
		$this->assertTrue( $get_data['confirmed'] );
	}

	/**
	 * @group rest_validate_item
	 */
	public function test_validate_item() {
		$inserted = reception_insert_email_to_verify( 'validate@email.com' );
		$entry    = reception_get_email_verification_entry( $inserted['email_hash'] );

		$request  = new WP_REST_Request( 'PUT', $this->endpoint_url . sprintf( '/validate/%s', $inserted['email'] ) );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'code' => $entry->confirmation_code,
		) ) );
		$request->set_param( 'context', 'edit' );

		$response = $this->server->dispatch( $request );
		$get_data = $response->get_data();

		$this->assertNotEmpty( $get_data['id'] );
		$this->assertTrue( $get_data['confirmed'] );
	}

	/**
	 * @group rest_validate_item
	 */
	public function test_validate_item_not_created() {
		$request  = new WP_REST_Request( 'PUT', $this->endpoint_url . sprintf( '/validate/%s', 'validate2@email.com' ) );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'code' => 'random',
		) ) );
		$request->set_param( 'context', 'edit' );

		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'reception_email_not_created_error', $response, 500 );
	}

	/**
	 * @group rest_validate_item
	 */
	public function test_validate_item_wrong_code() {
		$inserted = reception_insert_email_to_verify( 'validate3@email.com' );

		$request  = new WP_REST_Request( 'PUT', $this->endpoint_url . sprintf( '/validate/%s', $inserted['email'] ) );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'code' => 'random',
		) ) );
		$request->set_param( 'context', 'edit' );

		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'reception_email_wrong_code_error', $response, 500 );
	}

	/**
	 * @group rest_prepare_item
	 */
	public function test_prepare_item() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_item_schema
	 */
	public function test_get_item_schema() {
		$this->markTestSkipped();
	}
}