<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://schoeyfield.com
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Fontana
 * @subpackage Fontana/includes
 * @author     Michael Schofield <michael@schoeyfield.com>
 */
class Fontana {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Fontana_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'fontana';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fontana_Loader. Orchestrates the hooks of the plugin.
	 * - Fontana_i18n. Defines internationalization functionality.
	 * - Fontana_Admin. Defines all hooks for the admin area.
	 * - Fontana_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontana-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontana-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fontana-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fontana-public.php';

		$this->loader = new Fontana_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fontana_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fontana_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Fontana_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action('init', $plugin_admin, 'registerAudienceTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerGenreTaxonomy');
		$this->loader->add_action('init', $plugin_admin, 'registerCallToActionType');
        $this->loader->add_action('init', $plugin_admin, 'registerCollectionItemType');
        $this->loader->add_action('init', $plugin_admin, 'registerFeaturedCollectionTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerResourceType');
        $this->loader->add_action('init', $plugin_admin, 'registerLocationTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerResourceTypeTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerServicesTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerSubjectsTaxonomy');
        $this->loader->add_action('init', $plugin_admin, 'registerVendorsTaxonomy');

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fontana_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action('rest_api_init', $plugin_public, 'registerMenusWithApi');
		$this->loader->add_action('rest_api_init', $plugin_public, 'register_images_field' );
		$this->loader->add_filter('tribe_rest_event_data', $plugin_public, 'add_tribe_event_data', 10, 2);
		
		// Register hooks related to custom events api
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fontana-events-api.php';
		$events_api = new Fontana_Events_API($this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('rest_api_init', $events_api, 'register_api_fields' );
		$this->loader->add_filter( 'register_post_type_args', $events_api, 'events_api', 10, 2 );
		$this->loader->add_filter( 'rest_tribe_events_query', $events_api, 'events_api_upcoming',10, 2);
		$this->loader->add_filter( 'rest_prepare_tribe_events', $events_api, 'events_api_response', 10, 3);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Fontana_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
