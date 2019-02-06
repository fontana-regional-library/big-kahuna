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
    $this->define_collection_hooks();
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

    /**
		 * The class responsible for defining all actions that occur in the course 
     * of importing items
		 */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'import/class-fontana-collection.php';

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
      $this->loader->add_action( 'init', $plugin_admin, 'registerAudienceTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerGenreTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerCallToActionType' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerCollectionItemType' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerFeaturedCollectionTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerResourceType' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerLocationTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerResourceTypeTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerServicesTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerSubjectsTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerVendorsTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerTopicsTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerShelfLocationTaxonomy' );
      $this->loader->add_action( 'init', $plugin_admin, 'registerKeywordTaxonomy' );
        

				// Register hooks related to custom settings page
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fontana-settings.php';
		$plugin_settings = new Fontana_Settings_Page($this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'admin_menu', $plugin_settings, 'create_settings' );
      $this->loader->add_action( 'admin_init', $plugin_settings, 'register_settings' );
      $this->loader->add_filter( 'manage_collection-item_posts_columns', $plugin_settings, 'posts_columns' );
      $this->loader->add_action( 'manage_collection-item_posts_custom_column', $plugin_settings,  'posts_custom_columns', 10, 2 );
      $this->loader->add_action( 'manage_edit-shelf_columns', $plugin_settings, 'add_shelf_columns' );
      $this->loader->add_action( 'manage_shelf_custom_column', $plugin_settings, 'add_shelf_column_content', 10 , 3 );
      $this->loader->add_action( 'manage_edit-keyword_columns', $plugin_settings, 'add_keyword_columns' );
      $this->loader->add_action( 'manage_keyword_custom_column', $plugin_settings, 'add_shelf_column_content', 10 , 3 );
      $this->loader->add_filter( 'add_option_fontana_overdrive_libraries',$plugin_settings, 'update_overdrive_settings', 10, 2 );
      $this->loader->add_filter( 'update_option_fontana_overdrive_libraries',$plugin_settings, 'update_overdrive_settings', 10, 2 );
      $this->loader->add_action( 'bulk_actions-edit-collection-item', $plugin_settings, 'register_custom_bulk_actions' );
      $this->loader->add_action( 'admin_notices', $plugin_settings, 'bulk_check_admin_notice' );
      $this->loader->add_action( 'before_delete_post', $plugin_settings, 'delete_attachments' );
      //$this->loader->add_action( 'wp_handle_upload_prefilter', $plugin_settings, 'upload_directory' );
      //$this->loader->add_action( 'created_term', $plugin_settings, 'collectionTermData', 10, 3 );
      $this->loader->add_action( 'admin_post_update_terms', $plugin_settings, 'collectionTermData' );
      $this->loader->add_action( 'fbk_update_collection_term_lists', $plugin_settings, 'collectionTermData' );
      $this->loader->add_action( 'admin_post_delete_results', $plugin_settings, 'delete_counter_results' );
    }

	/**
	 * Register all of the hooks related to collections functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fontana_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'registerMenusWithApi' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_images_field' );
    $this->loader->add_filter( 'tribe_rest_event_data', $plugin_public, 'add_tribe_event_data', 10, 2 );
    $this->loader->add_filter( 'rest_collection-item_query',$plugin_public, 'collection_api_newest',10, 2 );
    $this->loader->add_filter( 'excerpt_more',$plugin_public, 'remove_read_more', 11);
		
		// Register hooks related to custom events api
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fontana-events-api.php';
		$events_api = new Fontana_Events_API($this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'rest_api_init', $events_api, 'register_api_fields' );
		$this->loader->add_filter( 'register_post_type_args',$events_api, 'events_api', 10, 2 );
		$this->loader->add_filter( 'rest_tribe_events_query',$events_api, 'events_api_upcoming',10, 2 );
		$this->loader->add_filter( 'rest_prepare_tribe_events',$events_api, 'events_api_response', 10, 3 );
  }
  
  /**
	 * Register all of the hooks related to import functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */

  private function define_collection_hooks() {
    $collection = new Fontana_Collection($this->get_plugin_name(), $this->get_version() );
    $this->loader->add_action( 'pmxi_saved_post', $collection, 'import_item', 10, 3 );
    $this->loader->add_action( 'wp_all_import_is_post_to_update', $collection, 'is_item_to_update', 10, 4 );
    $this->loader->add_action( 'admin_post_import_overdrive', $collection, 'check_deleted' );
    $this->loader->add_action( 'admin_post_check_failed', $collection, 'check_failed' );
    $this->loader->add_action( 'admin_post_check_evergreen_holdings', $collection, 'check_evergreen_holdings' );
    $this->loader->add_action( 'fbk_check_deleted', $collection, 'check_deleted' );
    $this->loader->add_action( 'fbk_check_failed', $collection, 'check_failed' );
    $this->loader->add_action( 'fbk_check_evergreen_holdings', $collection, 'check_evergreen_holdings' );
    $this->loader->add_filter( 'handle_bulk_actions-edit-collection-item', $collection, 'collection_bulk_actions', 10, 3 );
    $this->loader->add_action( 'pmxi_after_xml_import', $collection, 'process_imported_items' );
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