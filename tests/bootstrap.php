<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Advanced_Plugin_Dependencies
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

// Need to declare WP_Plugin_Dependencies while not in core.
if ( ! class_exists( 'WP_Plugin_Dependencies' ) ) {
	require_once 'load-wpplugindependencies.php';
}

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/src/advanced-plugin-dependencies.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
