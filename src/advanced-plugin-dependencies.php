<?php
/**
 * WordPress Plugin Administration API: WP_Plugin_Dependencies class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 6.5.0
 */

/**
 * Child class for installing plugin dependencies.
 *
 * It is designed to add plugin dependencies as designated
 * to a new view in the plugins install page.
 */
class Advanced_Plugin_Dependencies extends WP_Plugin_Dependencies {

	/**
	 * Holds associative array of slug|endpoint, if present.
	 *
	 * @var array
	 */
	protected static $api_endpoints = array();

	/**
	 * Holds $args from `plugins_api_result` hook.
	 *
	 * @var stdClass
	 */
	private static $args;

	/**
	 * Holds non-WordPress.org dependency slugs.
	 *
	 * @var string[]
	 */
	private static $non_dotorg_dependency_slugs = array();

	/**
	 * Check for heartbeat.
	 *
	 * @return bool
	 */
	private static function is_heartbeat() {
		if ( isset( $_POST['action'], $_POST['_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_nonce'] ) ), 'heartbeat-nonce' ) ) {
			return 'heartbeat' === $_POST['action'];
		}
			return false;
	}

	/**
	 * Initialize, load filters, and get started.
	 *
	 * @return void
	 */
	public static function initialize() {
		if ( is_admin() && ! self::is_heartbeat() ) {
			add_filter( 'plugins_api_result', array( __CLASS__, 'plugins_api_result' ), 10, 3 );
			add_filter( 'upgrader_source_selection', array( __CLASS__, 'fix_upgrader_source_selection' ), 10, 4 );
			add_filter( 'wp_admin_notice_markup', array( __CLASS__, 'dependency_notice_with_link' ), 10, 1 );
			add_filter( 'wp_plugin_dependencies_slug', array( __CLASS__, 'split_slug' ), 10, 1 );

			parent::read_dependencies_from_plugin_headers();
			parent::get_dependency_api_data();
			self::detect_non_dotorg_dependencies();
			self::add_non_dotorg_dependency_api_data();
		}
	}

	/**
	 * Detects non-WordPress.org plugin dependencies which have the format "slug|endpoint".
	 *
	 * @return void
	 */
	protected static function detect_non_dotorg_dependencies() {
		foreach ( self::$plugins as $plugin => $data ) {
			// Skip plugins with no dependencies or no non-dotorg dependencies.
			if ( empty( $data['RequiresPlugins'] ) || ! str_contains( $data['RequiresPlugins'], '|' ) ) {
				continue;
			}

			$dependencies = array_map( 'trim', explode( ',', $data['RequiresPlugins'] ) );

			foreach ( $dependencies as $dependency ) {
				// Skip invalid formats.
				if ( ! str_contains( $dependency, '|' ) || str_starts_with( $dependency, '|' ) || str_ends_with( $dependency, '|' ) ) {
					continue;
				}

				list( $slug, $endpoint ) = array_map( 'trim', explode( '|', $dependency ) );

				if ( str_contains( $slug, '|' ) || str_contains( '|', $endpoint ) ) {
					continue;
				}

				if ( isset( self::$dependencies[ $plugin ] ) && in_array( $slug, self::$dependencies[ $plugin ], true ) ) {
					self::$dependencies[ $plugin ][]     = $slug;
					self::$dependency_slugs[]            = $slug;
					self::$dependent_slugs[ $plugin ]    = str_contains( $plugin, '/' ) ? dirname( $plugin ) : $plugin;
					self::$non_dotorg_dependency_slugs[] = $slug;
				}

				// Handle local JSON files.
				if ( ! str_starts_with( $endpoint, 'http' ) && str_ends_with( $endpoint, '.json' ) ) {
					$endpoint = plugin_dir_url( $plugin ) . $endpoint;
				}

				self::$api_endpoints[ $slug ] = $endpoint;
			}
		}
	}

	/**
	 * Adds non-WordPress.org dependency API data to `self::$dependency_api_data`.
	 *
	 * @return void
	 */
	protected static function add_non_dotorg_dependency_api_data() {
		$short_description = esc_html__( "You will need to manually install this dependency. Please contact the plugin's developer and ask them to add plugin dependencies support and for information on how to install the this dependency.", 'advanced-plugin-dependencies' );
		foreach ( self::$dependency_slugs as $slug ) {
			if ( is_array( self::$dependency_api_data ) && ! array_key_exists( $slug, self::$dependency_api_data ) ) {
				self::$non_dotorg_dependency_slugs[] = $slug;
				self::$dependency_api_data[ $slug ]  = self::get_empty_plugins_api_response( $slug );
			}
		}
		foreach ( self::$non_dotorg_dependency_slugs as $slug ) {
			$dependency_data = array();
			$dependency_data = (array) self::fetch_non_dotorg_dependency_data( $slug );
			if ( ! isset( $dependency_data['name'] ) ) {
				continue;
			}
			$dependency_data['Name'] = $dependency_data['name'];

			if ( ! isset( $dependency_data['short_description'] ) ) {
				if ( isset( $dependency_data['sections']['description'] ) ) {
					$dependency_data['short_description'] = substr( $dependency_data['sections']['description'], 0, 150 ) . '...';
				} else {
					$dependency_data['short_description'] = $short_description;
				}
			}
			$dependency_data['download_link']   = sanitize_url( $dependency_data['download_link'] );
			self::$dependency_api_data[ $slug ] = $dependency_data;
		}
		// Set transient for WP_Plugin_Dependencies.
		set_site_transient( 'wp_plugin_dependencies_plugin_data', self::$dependency_api_data, 0 );
	}

	/**
	 * Fetches non-WordPress.org dependency data from their designated endpoints.
	 *
	 * @param string $dependency The dependency's slug.
	 * @return array|\WP_Error
	 */
	protected static function fetch_non_dotorg_dependency_data( $dependency ) {
		// Get cached data.
		$response = get_site_transient( "non_dot_org_dependency_data_{$dependency}" );
		if ( $response ) {
			return $response;
		}

		/**
		 * Filter the REST enpoints used for lookup of plugins API data.
		 *
		 * @param array
		 */
		$rest_endpoints = array_merge( self::$api_endpoints, apply_filters( 'plugin_dependency_endpoints', array() ) );

		// Ensure dependency has REST endpoint.
		if ( isset( $rest_endpoints[ $dependency ] ) ) {

			// Get local JSON endpoint.
			$response = wp_remote_get( $rest_endpoints[ $dependency ] );

			// Convert response to associative array.
			$response = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( null === $response || isset( $response['error'] ) || isset( $response['code'] ) ) {
				$message  = isset( $response['error'] ) ? $response['error'] : '';
				$response = new WP_Error( 'error', 'Error retrieving plugin data.', $message );
			}
			if ( is_wp_error( $response ) ) {
				return $response;
			}
		}

		// Cache data for 12 hours.
		set_site_transient( "non_dot_org_dependency_data_{$dependency}", $response, 12 * HOUR_IN_SECONDS );

		// Add slug to hook_extra.
		add_filter( 'upgrader_package_options', array( __CLASS__, 'upgrader_package_options' ), 10, 1 );

		return $response;
	}

	/**
	 * Modify plugins_api() response.
	 *
	 * @param stdClass $res    Object of results.
	 * @param string   $action Variable for plugins_api().
	 * @param stdClass $args   Object of plugins_api() args.
	 * @return stdClass
	 */
	public static function plugins_api_result( $res, $action, $args ) {
		if ( property_exists( $args, 'browse' ) && 'dependencies' === $args->browse ) {
			$res->info = array(
				'page'    => 1,
				'pages'   => 1,
				'results' => count( (array) self::$dependency_api_data ),
			);

			$res->plugins = self::$dependency_api_data;
		}

		if ( is_wp_error( $res ) && isset( self::$dependency_api_data[ $args->slug ] ) ) {
			self::$args = $args;
			$res        = (object) self::$dependency_api_data[ $args->slug ];
		}
		return $res;
	}

	/**
	 * Switch admin notice markup with markup including link to Dependencies tab.
	 *
	 * @global $pagenow Current page.
	 *
	 * @param string $markup The HTML markup for the admin notice.
	 * @return string
	 */
	public static function dependency_notice_with_link( $markup ) {
		global $pagenow;

		if ( 'plugin-install.php' === $pagenow
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& ( isset( $_GET['tab'] ) && 'dependencies' === sanitize_title_with_dashes( wp_unslash( $_GET['tab'] ) ) )
		) {
			return $markup;
		}

		$message = __( 'Some required plugins are missing or inactive.', 'advanced-plugin-dependencies' );

		/* translators: s: link to Dependencies install page */
		$link_message = sprintf( __( 'Go to the %s install page.', 'advanced-plugin-dependencies' ), self::get_dependency_link() );

		if ( str_contains( $markup, $message ) && ! str_contains( $markup, $link_message ) ) {
			$markup = str_replace( $message, "$message $link_message", $markup );
		}

		return wp_kses_post( $markup ); }

	/**
	 * Get Dependencies link.
	 *
	 * @return string
	 */
	private static function get_dependency_link() {
		$link = sprintf(
			'<a href=' . network_admin_url( 'plugin-install.php?tab=dependencies' ) . ' aria-label="' . __( 'Go to Dependencies tab of Add Plugins page.', 'advanced-plugin-dependencies' ) . '">%s</a>',
			__( 'Dependencies', 'advanced-plugin-dependencies' )
		);

		return wp_kses_post( $link );
	}

	/**
	 * Return empty plugins_api() response.
	 *
	 * @param string $slug Plugin slug.
	 * @param array  $args Array of plugin.
	 * @return array
	 */
	private static function get_empty_plugins_api_response( $slug, $args = array() ) {
		$defaults     = array(
			'Name'        => $slug,
			'Version'     => '',
			'Author'      => '',
			'Description' => '',
			'RequiresWP'  => '',
			'RequiresPHP' => '',
			'PluginURI'   => '',
		);
		$args         = array_merge( $defaults, $args );
		$dependencies = self::get_dependency_filepaths();
		if ( ! isset( $dependencies[ $slug ] ) ) {
			$file = array_filter(
				array_keys( self::$plugins ),
				function ( $file ) use ( $slug ) {
					return str_contains( $file, $slug );
				}
			);
			$file = array_pop( $file );
		} else {
			$file = $dependencies[ $slug ];
		}
		$args              = $file ? self::$plugins[ $file ] : $args;
		$short_description = esc_html__( "You will need to manually install this dependency. Please contact the plugin's developer and ask them to add plugin dependencies support and for information on how to install this dependency.", 'advanced-plugin-dependencies' );
		$dependencies      = isset( self::$plugin_dirnames[ $slug ] ) && ! empty( self::$plugins[ self::$plugin_dirnames[ $slug ] ]['RequiresPlugins'] )
			? self::$plugins[ self::$plugin_dirnames[ $slug ] ]['RequiresPlugins'] : array();
		$response          = array(
			'name'              => $args['Name'],
			'Name'              => $args['Name'],
			'slug'              => $slug,
			'version'           => $args['Version'],
			'author'            => $args['Author'],
			'contributors'      => array(),
			'requires'          => $args['RequiresWP'],
			'tested'            => '',
			'requires_php'      => $args['RequiresPHP'],
			'requires_plugins'  => is_array( $dependencies ) ? $dependencies : explode( ',', $dependencies ),
			'sections'          => array(
				'description'  => $args['Description'],
				'installation' => esc_html__( 'Ask the plugin developer where to download and install this plugin dependency.', 'advanced-plugin-dependencies' ),
			),
			'short_description' => $short_description,
			'download_link'     => '',
			'banners'           => array(),
			'icons'             => array( 'default' => "https://s.w.org/plugins/geopattern-icon/{$slug}.svg" ),
			'last_updated'      => '',
			'num_ratings'       => 0,
			'rating'            => 0,
			'active_installs'   => 0,
			'homepage'          => $args['PluginURI'],
			'external'          => 'xxx',
		);

		return $response;
	}

	/**
	 * Split slug into slug and endpoint.
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	public static function split_slug( $slug ) {
		if ( ! str_contains( $slug, '|' ) || str_starts_with( $slug, '|' ) || str_ends_with( $slug, '|' ) ) {
			return $slug;
		}

		$original_slug           = $slug;
		list( $slug, $endpoint ) = explode( '|', $slug );
		$slug                    = trim( $slug );
		$endpoint                = trim( $endpoint );

		if ( '' === $slug || '' === $endpoint ) {
			return $original_slug;
		}

		if ( ! isset( self::$api_endpoints[ $slug ] ) ) {
			self::$api_endpoints[ $slug ] = sanitize_url( $endpoint );
		}

		return $slug;
	}

	/**
	 * Add slug to hook_extra.
	 *
	 * @see WP_Upgrader::run() for $options details.
	 *
	 * @param array $options Array of options.
	 * @return array
	 */
	public static function upgrader_package_options( $options ) {
		if ( isset( $options['hook_extra']['temp_backup'] ) ) {
			$options['hook_extra']['slug'] = $options['hook_extra']['temp_backup']['slug'];
		} elseif ( isset( self::$args->slug ) ) {
			$options['hook_extra']['slug'] = self::$args->slug;
		}
		remove_filter( 'upgrader_package_options', array( __CLASS__, 'upgrader_package_options' ), 10 );

		return $options;
	}

	/**
	 * Fix $source for non-dot org plugins.
	 *
	 * @param string       $source          File path of $ource.
	 * @param string       $remote_source   File path of $remote_source.
	 * @param Plugin|Theme $upgrader_object An Upgrader object.
	 * @param array        $hook_extra      Array of $hook_extra data.
	 * @return string
	 */
	public static function fix_upgrader_source_selection( $source, $remote_source, $upgrader_object, $hook_extra ) {
		if ( isset( $hook_extra['slug'] ) ) {
			$new_source = trailingslashit( $remote_source ) . $hook_extra['slug'] . '/';

			$from = untrailingslashit( $source );
			$to   = $new_source;

			if ( trailingslashit( strtolower( $from ) ) !== trailingslashit( strtolower( $to ) ) ) {
				move_dir( $from, $to, true );
			}

			return $new_source;
		}

		return $source;
	}
}

Advanced_Plugin_Dependencies::initialize();
