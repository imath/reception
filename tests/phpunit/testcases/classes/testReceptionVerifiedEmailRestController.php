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
