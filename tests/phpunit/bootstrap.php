<?php
/**
 * Réception PHPUnit bootstrap file
 *
 * @package reception
 * @subpackage \tests\phpunit\bootstrap
 *
 * @since 1.0.0
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php." . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

if ( ! defined( 'BP_TESTS_DIR' ) ) {
	$bp_tests_dir = getenv( 'BP_TESTS_DIR' );
	if ( $bp_tests_dir ) {
		define( 'BP_TESTS_DIR', $bp_tests_dir );
	} else {
		define( 'BP_TESTS_DIR', dirname( dirname( __FILE__ ) ) . '/../../buddypress/tests/phpunit' );
	}
}

if ( ! defined( 'RECEPTION_TESTS_DIR' ) ) {
	$r_tests_dir = getenv( 'RECEPTION_TESTS_DIR' );
	if ( $r_tests_dir ) {
		define( 'RECEPTION_TESTS_DIR', $r_tests_dir );
	} else {
		define( 'RECEPTION_TESTS_DIR', dirname( __FILE__ ) );
	}
}

/**
 * Load the Reception plugin.
 *
 * @since 1.0.0
 */
function _load_reception_plugin() {
	add_filter( 'bp_rest_api_is_available', '__return_false' );

	// Make sure BP is installed and loaded first.
	require BP_TESTS_DIR . '/includes/loader.php';

	// Load our plugin.
	require_once dirname( __FILE__ ) . '/../../class-reception.php';

	// Set version.
	bp_update_option( '_reception_version', RECEPTION_VERSION );

	// Hook to init to install the plugin.
	add_action( 'init', '_install_reception', 20 );
}
tests_add_filter( 'muplugins_loaded', '_load_reception_plugin' );

function _install_reception() {
	require_once dirname( __FILE__ ) . '/../../inc/admin.php';

	echo "Installing Réception...\n";

	global $wpdb;

	// Drop Réception tables.
	foreach ( $wpdb->get_col( "SHOW TABLES LIKE '" . $wpdb->prefix . "reception%'" ) as $reception_table ) {
		$wpdb->query( "DROP TABLE {$reception_table}" );
	}

	reception_admin_install();
}

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

// Load the REST controllers.
require_once $_tests_dir . '/includes/testcase-rest-controller.php';

// Load the BP test files.
echo "Loading BuddyPress testcase...\n";
require_once BP_TESTS_DIR . '/includes/testcase.php';
