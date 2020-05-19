<?php
/**
 * Réception Functions tests.
 *
 * @package reception
 * @subpackage \tests\phpunit\testcases\testReceptionFunctions
 *
 * @since 1.0.0
 */

/**
 * @group functions
 */
class Reception_Functions_UnitTestCase extends BP_UnitTestCase {
	public function test_reception_insert_email_to_verify() {
		$email    = 'foo@bar.com';
		$inserted = reception_insert_email_to_verify( $email );

		$this->assertTrue( $email === $inserted['email'] );
	}

	public function test_reception_insert_email_to_verify_already_exists() {
		$email       = 'bar@foo.com';
		$inserted    = reception_insert_email_to_verify( $email );
		$re_inserted = reception_insert_email_to_verify( $email );

		$this->assertTrue( 'reception_email_already_exists' === $re_inserted->get_error_code() );
	}
}
