<?php
/**
 * Advanced Plugin Dependencies
 *
 * @author  Andy Fragen
 * @license MIT
 * @link    https://github.com/afragen/advanced-plugin-dependencies
 * @package advanced-plugin-dependencies
 */

/**
 * Plugin Name: Advanced Plugin Dependencies
 * Plugin URI:  https://wordpress.org/plugins/advanced-plugin-dependencies
 * Description: Add plugin install dependencies tab and information about dependencies.
 * Author: Andy Fragen, Colin Stewart, Paul Biron
 * Version: 0.1.0
 * License: MIT
 * Network: true
 * Requires at least: 6.0
 * Requires PHP: 7.0
 * GitHub Plugin URI: https://github.com/WordPress/advanced-plugin-dependencies
 * Primary Branch: main
 */

namespace Advanced_Plugin_Dependencies;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Init
 */
class Init {

	/**
	 * Initialize, load filters, and get started.
	 *
	 * @return void
	 */
	public function __construct() {
		require_once __DIR__ . '/src/advanced-plugin-dependencies.php';

		add_filter( 'install_plugins_tabs', array( $this, 'add_install_tab' ), 10, 1 );
		add_filter( 'install_plugins_table_api_args_dependencies', array( $this, 'add_install_dependency_args' ), 10, 1 );

		add_action( 'install_plugins_dependencies', 'display_plugins_table' );
		add_action(
			'install_plugins_table_header',
			static function() {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tab = isset( $_GET['tab'] ) ? sanitize_title_with_dashes( wp_unslash( $_GET['tab'] ) ) : '';
				if ( 'dependencies' === $tab ) {
					echo '<p>' . esc_html__( 'These suggestions are based on dependencies required by installed plugins.' ) . '</p>';
				}
			}
		);
	}

	/**
	 * Add 'Dependencies' tab to 'Plugin > Add New'.
	 *
	 * @param array $tabs Array of plugin install tabs.
	 *
	 * @return array
	 */
	public function add_install_tab( $tabs ) {
		$tabs['dependencies'] = _x( 'Dependencies', 'Plugin Installer' );

		return $tabs;
	}

	/**
	 * Add args to plugins_api().
	 *
	 * @param array $args Array of arguments to plugins_api().
	 *
	 * @return array
	 */
	public function add_install_dependency_args( $args ) {
		$args = array(
			'page'     => 1,
			'per_page' => 36,
			'locale'   => get_user_locale(),
			'browse'   => 'dependencies',
		);

		return $args;
	}
}

add_action(
	'plugins_loaded',
	function() {
		new Init();
	}
);
