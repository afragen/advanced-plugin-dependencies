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
 * Description:  Add plugin install dependencies tab, support for non dot org plugin cards, and information about dependencies.
 * Author: Andy Fragen, Colin Stewart, Paul Biron
 * Version: 0.11.0
 * License: MIT
 * Text Domain: advanced-plugin-dependencies
 * Network: true
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * GitHub Plugin URI: https://github.com/afragen/advanced-plugin-dependencies
 * Primary Branch: main
 */

namespace Advanced_Plugin_Dependencies;

const PLUGIN_DIR = __DIR__;

bootstrap();

/**
 * Initialize, load filters, and get started.
 *
 * @return void
 */
function bootstrap() {
	add_filter( 'install_plugins_tabs', __NAMESPACE__ . '\\add_install_tab', 10, 1 );
	add_filter( 'install_plugins_table_api_args_dependencies', __NAMESPACE__ . '\\add_install_dependency_args', 10, 1 );

	add_action( 'init', __NAMESPACE__ . '\\init' );
	add_action( 'install_plugins_dependencies', 'display_plugins_table' );
	add_action(
		'install_plugins_table_header',
		static function () {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tab = isset( $_GET['tab'] ) ? sanitize_title_with_dashes( wp_unslash( $_GET['tab'] ) ) : '';
			if ( 'dependencies' === $tab ) {
				echo '<p>' . esc_html__( 'These suggestions are based on dependencies required by installed plugins.', 'advanced-plugin-dependencies' ) . '</p>';
			}
		}
	);
}

/**
 * Initialize.
 *
 * @return void
 */
function init(): void {
	require_once PLUGIN_DIR . '/src/advanced-plugin-dependencies.php';
}

/**
 * Add 'Dependencies' tab to 'Plugin > Add New'.
 *
 * @param array $tabs Array of plugin install tabs.
 * @return array
 */
function add_install_tab( $tabs ) {
	$tabs['dependencies'] = esc_html_x( 'Dependencies', 'Plugin Installer', 'advanced-plugin-dependencies' );

	return $tabs;
}

/**
 * Add args to plugins_api().
 *
 * @param array $args Array of arguments to plugins_api().
 * @return array
 */
function add_install_dependency_args( $args ) {
	$args = array(
		'page'     => 1,
		'per_page' => 36,
		'locale'   => get_user_locale(),
		'browse'   => 'dependencies',
	);

	return $args;
}
