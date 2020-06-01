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
	/**
	 * @group insert_entry
	 */
	public function test_reception_insert_email_to_verify() {
		$email    = 'foo@bar.com';
		$inserted = reception_insert_email_to_verify( $email );

		$this->assertTrue( $email === $inserted['email'] );
	}

	/**
	 * @group insert_entry
	 */
	public function test_reception_insert_email_to_verify_already_exists() {
		$email       = 'bar@foo.com';
		$inserted    = reception_insert_email_to_verify( $email );
		$re_inserted = reception_insert_email_to_verify( $email );

		$this->assertTrue( 'reception_email_already_exists' === $re_inserted->get_error_code() );
	}

	/**
	 * @group spam_entry
	 */
	public function test_reception_update_spam_status() {
		$email    = 'spam@spam.com';
		$inserted = reception_insert_email_to_verify( $email );

		$this->assertTrue( reception_update_spam_status( $inserted['id'], 'spam' ) );
	}

	/**
	 * @group spam_entry
	 */
	public function test_reception_update_unspam_status() {
		$email    = 'unspam@unspam.com';
		$inserted = reception_insert_email_to_verify( $email );

		$this->assertFalse( reception_update_spam_status( $inserted['id'], 'unspam' ) );
	}

	/**
	 * @group get_entries
	 */
	public function test_reception_get_email_verification_entries() {
		$i1 = reception_insert_email_to_verify( 'unconfirmed@test.com' );

		$i2 = reception_insert_email_to_verify( 'spammed@test.com' );
		reception_update_spam_status( $i2['id'], 'spam' );

		$i3    = reception_insert_email_to_verify( 'confirmed@test.com' );
		$entry = reception_get_email_verification_entry( $i3['email_hash'] );
		reception_validate_email_to_verify( $i3['email'], $entry->confirmation_code );

		$all = reception_get_email_verification_entries();

		$this->assertTrue( 3 === $all['found_entries'] );

		$unconfirmed = wp_filter_object_list( $all['entries'], array( 'is_confirmed' => false, 'is_spam' => false ), 'and', 'id' );
		$unconfirmed = reset( $unconfirmed );

		$this->assertSame( $unconfirmed, $i1['id'] );

		$spammed = wp_filter_object_list( $all['entries'], array( 'is_spam' => true ), 'and', 'id' );
		$spammed = reset( $spammed );

		$this->assertSame( $spammed, $i2['id'] );

		$confirmed = wp_filter_object_list( $all['entries'], array( 'is_confirmed' => true, 'is_spam' => false ), 'and', 'id' );
		$confirmed = reset( $confirmed );

		$this->assertSame( $confirmed, $i3['id'] );

		$two_of_three = reception_get_email_verification_entries( array( 'per_page' => 2 ) );
		$this->assertTrue( 3 === $two_of_three['found_entries'] );
		$this->assertTrue( 2 === count( $two_of_three['entries'] ) );

		$not_spam = reception_get_email_verification_entries( array( 'spammed' => false ) );
		$this->assertTrue( 2 === $not_spam['found_entries'] );
		$this->assertFalse( in_array( $i2['id'], wp_list_pluck( $two_of_three['entries'], 'id' ), true ) );

		$not_confirmed = reception_get_email_verification_entries( array( 'spammed' => false, 'confirmed' => false ) );
		$this->assertTrue( 1 === $not_confirmed['found_entries'] );
		$this->assertTrue( in_array( $i1['id'], wp_list_pluck( $not_confirmed['entries'], 'id' ), true ) );

		$search_email = reception_get_email_verification_entries( array( 'email' => 'confirmed@test.com' ) );
		$this->assertTrue( 1 === $search_email['found_entries'] );
		$this->assertTrue( in_array( $i3['id'], wp_list_pluck( $search_email['entries'], 'id' ), true ) );
	}
}
