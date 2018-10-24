<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://schoeyfield.com
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fontana
 * @subpackage Fontana/admin
 * @author     Michael Schofield <michael@schoeyfield.com>
 */
class Fontana_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fontana_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fontana_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fontana-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fontana_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fontana_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fontana-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function registerAudienceTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Audiences', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Audience', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Audiences', 'fontana' ),
            'all_items'                  => __( 'All Audiences', 'fontana' ),
            'parent_item'                => __( 'Parent Audience', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Audience:', 'fontana' ),
            'new_item_name'              => __( 'New Audience Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Audience', 'fontana' ),
            'edit_item'                  => __( 'Edit Audience', 'fontana' ),
            'update_item'                => __( 'Update Audience', 'fontana' ),
            'view_item'                  => __( 'View Audience', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate Audiences with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove Audiences', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Audiences', 'fontana' ),
            'search_items'               => __( 'Search Audiences', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No Audiences', 'fontana' ),
            'items_list'                 => __( 'Audiences list', 'fontana' ),
            'items_list_navigation'      => __( 'Audiences list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'audience',
        );
        register_taxonomy( 'audience', array( 'collection-item', 'post', 'page', 'resources', 'tribe-events', 'actions' ), $args );
    }

    public function registerCallToActionType() {

        $labels = array(
            'name'                  => _x( 'Calls to Action', 'Post Type General Name', 'fontana' ),
            'singular_name'         => _x( 'Call to Action', 'Post Type Singular Name', 'fontana' ),
            'menu_name'             => __( 'Calls to Action', 'fontana' ),
            'name_admin_bar'        => __( 'Call to Action', 'fontana' ),
            'archives'              => __( 'All Actions', 'fontana' ),
            'attributes'            => __( 'Action Attributes', 'fontana' ),
            'parent_item_colon'     => __( 'Parent Action:', 'fontana' ),
            'all_items'             => __( 'All Actions', 'fontana' ),
            'add_new_item'          => __( 'Add New Action', 'fontana' ),
            'add_new'               => __( 'Add New', 'fontana' ),
            'new_item'              => __( 'New Item', 'fontana' ),
            'edit_item'             => __( 'Edit Item', 'fontana' ),
            'update_item'           => __( 'Update Item', 'fontana' ),
            'view_item'             => __( 'View Item', 'fontana' ),
            'view_items'            => __( 'View Items', 'fontana' ),
            'search_items'          => __( 'Search Item', 'fontana' ),
            'not_found'             => __( 'Not found', 'fontana' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'fontana' ),
            'featured_image'        => __( 'Featured Image', 'fontana' ),
            'set_featured_image'    => __( 'Set featured image', 'fontana' ),
            'remove_featured_image' => __( 'Remove featured image', 'fontana' ),
            'use_featured_image'    => __( 'Use as featured image', 'fontana' ),
            'insert_into_item'      => __( 'Insert into item', 'fontana' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'fontana' ),
            'items_list'            => __( 'Items list', 'fontana' ),
            'items_list_navigation' => __( 'Items list navigation', 'fontana' ),
            'filter_items_list'     => __( 'Filter items list', 'fontana' ),
        );
        $args = array(
            'label'                 => __( 'Call to Action', 'fontana' ),
            'description'           => __( 'Calls to action are designed to compel folks to do just that.', 'fontana' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-megaphone',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'calls-to-action',
        );
        register_post_type( 'actions', $args );

    }

    public function registerFeaturedCollectionTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Featured Collections', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Featured Collection', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Featured Collections', 'fontana' ),
            'all_items'                  => __( 'All Featured Collections', 'fontana' ),
            'parent_item'                => __( 'Parent Featured Collection', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Featured Collection:', 'fontana' ),
            'new_item_name'              => __( 'New Featured Collection Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Featured Collection', 'fontana' ),
            'edit_item'                  => __( 'Edit Featured Collection', 'fontana' ),
            'update_item'                => __( 'Update Featured Collection', 'fontana' ),
            'view_item'                  => __( 'View Featured Collection', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate Featured Collection with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove Featured Collection', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Featured Collections', 'fontana' ),
            'search_items'               => __( 'Search Featured Collections', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No Featured Collections', 'fontana' ),
            'items_list'                 => __( 'Featured Collections list', 'fontana' ),
            'items_list_navigation'      => __( 'Featured Collections list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'featured-collections',
        );
        register_taxonomy( 'featured-collections', array( 'collection-item' ), $args );
    }

    public function registerGenreTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Genres', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Genre', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Genres', 'fontana' ),
            'all_items'                  => __( 'All Genres', 'fontana' ),
            'parent_item'                => __( 'Parent Genre', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Genre:', 'fontana' ),
            'new_item_name'              => __( 'New Genre Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Genre', 'fontana' ),
            'edit_item'                  => __( 'Edit Genre', 'fontana' ),
            'update_item'                => __( 'Update Genre', 'fontana' ),
            'view_item'                  => __( 'View Genre', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate Genres with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove Genres', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Genres', 'fontana' ),
            'search_items'               => __( 'Search Genres', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No Genres', 'fontana' ),
            'items_list'                 => __( 'Genres list', 'fontana' ),
            'items_list_navigation'      => __( 'Genres list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'genres',
        );
        register_taxonomy( 'genres', array( 'collection-item' ), $args );
    }

    public function registerResourceType() {

        $labels = array(
            'name'                  => _x( 'Resources', 'Post Type General Name', 'fontana' ),
            'singular_name'         => _x( 'Resource', 'Post Type Singular Name', 'fontana' ),
            'menu_name'             => __( 'Resource', 'fontana' ),
            'name_admin_bar'        => __( 'Resource', 'fontana' ),
            'archives'              => __( 'All Resources', 'fontana' ),
            'attributes'            => __( 'Resource Attributes', 'fontana' ),
            'parent_item_colon'     => __( 'Parent Resource:', 'fontana' ),
            'all_items'             => __( 'All Resource', 'fontana' ),
            'add_new_item'          => __( 'Add New Resource', 'fontana' ),
            'add_new'               => __( 'Add New', 'fontana' ),
            'new_item'              => __( 'New Item', 'fontana' ),
            'edit_item'             => __( 'Edit Item', 'fontana' ),
            'update_item'           => __( 'Update Item', 'fontana' ),
            'view_item'             => __( 'View Item', 'fontana' ),
            'view_items'            => __( 'View Items', 'fontana' ),
            'search_items'          => __( 'Search Item', 'fontana' ),
            'not_found'             => __( 'Not found', 'fontana' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'fontana' ),
            'featured_image'        => __( 'Featured Image', 'fontana' ),
            'set_featured_image'    => __( 'Set featured image', 'fontana' ),
            'remove_featured_image' => __( 'Remove featured image', 'fontana' ),
            'use_featured_image'    => __( 'Use as featured image', 'fontana' ),
            'insert_into_item'      => __( 'Insert into item', 'fontana' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'fontana' ),
            'items_list'            => __( 'Items list', 'fontana' ),
            'items_list_navigation' => __( 'Items list navigation', 'fontana' ),
            'filter_items_list'     => __( 'Filter items list', 'fontana' ),
        );
        $args = array(
            'label'                 => __( 'Resource', 'fontana' ),
            'description'           => __( 'Resources are electronic or physical resources the library provides.', 'fontana' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-admin-site',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'resources',
        );
        register_post_type( 'resources', $args );

    }

    public function registerLocationTaxonomy() {

        $labels = array(
            'name'                       => _x( 'Locations', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Locations', 'fontana' ),
            'all_items'                  => __( 'All Items', 'fontana' ),
            'parent_item'                => __( 'Parent Item', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Item:', 'fontana' ),
            'new_item_name'              => __( 'New Item Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Item', 'fontana' ),
            'edit_item'                  => __( 'Edit Item', 'fontana' ),
            'update_item'                => __( 'Update Item', 'fontana' ),
            'view_item'                  => __( 'View Item', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Items', 'fontana' ),
            'search_items'               => __( 'Search Items', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No items', 'fontana' ),
            'items_list'                 => __( 'Items list', 'fontana' ),
            'items_list_navigation'      => __( 'Items list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'locations',
        );
        register_taxonomy( 'location', array( 'post', 'page', 'actions', 'resources', 'tribe_events'), $args );

    }

    public function registerServicesTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Services', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Service', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Services', 'fontana' ),
            'all_items'                  => __( 'All Items', 'fontana' ),
            'parent_item'                => __( 'Parent Item', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Item:', 'fontana' ),
            'new_item_name'              => __( 'New Item Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Item', 'fontana' ),
            'edit_item'                  => __( 'Edit Item', 'fontana' ),
            'update_item'                => __( 'Update Item', 'fontana' ),
            'view_item'                  => __( 'View Item', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Items', 'fontana' ),
            'search_items'               => __( 'Search Items', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No items', 'fontana' ),
            'items_list'                 => __( 'Items list', 'fontana' ),
            'items_list_navigation'      => __( 'Items list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'services',
        );
        register_taxonomy( 'services', array( 'post', 'page', 'actions', 'resources', 'tribe_events'), $args );
    }

    public function registerSubjectsTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Subjects', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Subject', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Subjects', 'fontana' ),
            'all_items'                  => __( 'All Items', 'fontana' ),
            'parent_item'                => __( 'Parent Item', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Item:', 'fontana' ),
            'new_item_name'              => __( 'New Item Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Item', 'fontana' ),
            'edit_item'                  => __( 'Edit Item', 'fontana' ),
            'update_item'                => __( 'Update Item', 'fontana' ),
            'view_item'                  => __( 'View Item', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Items', 'fontana' ),
            'search_items'               => __( 'Search Items', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No items', 'fontana' ),
            'items_list'                 => __( 'Items list', 'fontana' ),
            'items_list_navigation'      => __( 'Items list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'subjects',
        );
        register_taxonomy( 'subjects', array( 'post', 'page', 'actions', 'resources', 'tribe_events'), $args );
    }

    public function registerVendorsTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Vendors', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Vendor', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Vendors', 'fontana' ),
            'all_items'                  => __( 'All Items', 'fontana' ),
            'parent_item'                => __( 'Parent Item', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Item:', 'fontana' ),
            'new_item_name'              => __( 'New Item Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Item', 'fontana' ),
            'edit_item'                  => __( 'Edit Item', 'fontana' ),
            'update_item'                => __( 'Update Item', 'fontana' ),
            'view_item'                  => __( 'View Item', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Items', 'fontana' ),
            'search_items'               => __( 'Search Items', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No items', 'fontana' ),
            'items_list'                 => __( 'Items list', 'fontana' ),
            'items_list_navigation'      => __( 'Items list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'subjects',
        );
        register_taxonomy( 'vendors', array( 'post', 'page', 'actions', 'resources', 'tribe_events'), $args );
    }

    public function registerResourceTypeTaxonomy()
    {
        $labels = array(
            'name'                       => _x( 'Resource Types', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Resource Type', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Resource Types', 'fontana' ),
            'all_items'                  => __( 'All Items', 'fontana' ),
            'parent_item'                => __( 'Parent Item', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Item:', 'fontana' ),
            'new_item_name'              => __( 'New Item Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Item', 'fontana' ),
            'edit_item'                  => __( 'Edit Item', 'fontana' ),
            'update_item'                => __( 'Update Item', 'fontana' ),
            'view_item'                  => __( 'View Item', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Items', 'fontana' ),
            'search_items'               => __( 'Search Items', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No items', 'fontana' ),
            'items_list'                 => __( 'Items list', 'fontana' ),
            'items_list_navigation'      => __( 'Items list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'subjects',
        );
        register_taxonomy( 'resource-types', array( 'post', 'page', 'actions', 'resources', 'tribe_events'), $args );
    }

    public function registerCollectionItemType() {

        $labels = array(
            'name'                  => _x( 'Collection Item', 'Post Type General Name', 'fontana' ),
            'singular_name'         => _x( 'Collection Item', 'Post Type Singular Name', 'fontana' ),
            'menu_name'             => __( 'Collection Item', 'fontana' ),
            'name_admin_bar'        => __( 'Collection Item', 'fontana' ),
            'archives'              => __( 'All Items', 'fontana' ),
            'attributes'            => __( 'Item Attributes', 'fontana' ),
            'parent_item_colon'     => __( 'Parent Item:', 'fontana' ),
            'all_items'             => __( 'All Items', 'fontana' ),
            'add_new_item'          => __( 'Add New Item', 'fontana' ),
            'add_new'               => __( 'Add New', 'fontana' ),
            'new_item'              => __( 'New Item', 'fontana' ),
            'edit_item'             => __( 'Edit Item', 'fontana' ),
            'update_item'           => __( 'Update Item', 'fontana' ),
            'view_item'             => __( 'View Item', 'fontana' ),
            'view_items'            => __( 'View Items', 'fontana' ),
            'search_items'          => __( 'Search Item', 'fontana' ),
            'not_found'             => __( 'Not found', 'fontana' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'fontana' ),
            'featured_image'        => __( 'Featured Image', 'fontana' ),
            'set_featured_image'    => __( 'Set featured image', 'fontana' ),
            'remove_featured_image' => __( 'Remove featured image', 'fontana' ),
            'use_featured_image'    => __( 'Use as featured image', 'fontana' ),
            'insert_into_item'      => __( 'Insert into item', 'fontana' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'fontana' ),
            'items_list'            => __( 'Items list', 'fontana' ),
            'items_list_navigation' => __( 'Items list navigation', 'fontana' ),
            'filter_items_list'     => __( 'Filter items list', 'fontana' ),
        );
        $args = array(
            'label'                 => __( 'Collection Item', 'fontana' ),
            'description'           => __( 'Collection Items are select books and media from the catalog', 'fontana' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'thumbnail' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-book-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'collection',
        );
        register_post_type( 'collection-item', $args );

    }
    public function registerTopicsTaxonomy() {
        $labels = array(
            'name'                       => _x( 'Topics', 'Taxonomy General Name', 'fontana' ),
            'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'fontana' ),
            'menu_name'                  => __( 'Topics', 'fontana' ),
            'all_items'                  => __( 'All Topics', 'fontana' ),
            'parent_item'                => __( 'Parent Topic', 'fontana' ),
            'parent_item_colon'          => __( 'Parent Topic:', 'fontana' ),
            'new_item_name'              => __( 'New Topic Name', 'fontana' ),
            'add_new_item'               => __( 'Add New Topic', 'fontana' ),
            'edit_item'                  => __( 'Edit Topic', 'fontana' ),
            'update_item'                => __( 'Update Topic', 'fontana' ),
            'view_item'                  => __( 'View Topic', 'fontana' ),
            'separate_items_with_commas' => __( 'Separate Topics with commas', 'fontana' ),
            'add_or_remove_items'        => __( 'Add or remove Topics', 'fontana' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'fontana' ),
            'popular_items'              => __( 'Popular Topics', 'fontana' ),
            'search_items'               => __( 'Search Topics', 'fontana' ),
            'not_found'                  => __( 'Not Found', 'fontana' ),
            'no_terms'                   => __( 'No Topics', 'fontana' ),
            'items_list'                 => __( 'Topics list', 'fontana' ),
            'items_list_navigation'      => __( 'Topics list navigation', 'fontana' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'topics',
        );
        register_taxonomy( 'topics', array( 'collection-item' ), $args );
    }
    /**
    *   Add Options Page
    **/
    public function fontana_add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            $this->plugin_name.' Settings', 
            'install_plugins', 
            'fontana-settings-admin', 
            array( $this, 'fontana_create_admin_page' )
        );
    }
    /**
     * Options page callback
     */
    public function fontana_create_admin_page() {
        require_once plugin_dir_path( __FILE__ ). 'partials/'.$this->plugin_name.'-admin-display.php';
    }
    /**
     * Register Settings
     */
    public function fontana_page_init() {
        register_setting(
            $this->plugin_name, // Option group
            $this->plugin_name, // Option name
            array( $this, 'fontana_sanitize' ) // Sanitize
        );
        add_settings_section(
            'dev_setting_section_id', // ID
            'Deverloper Settings', // Title
            array( $this, 'fontana_print_section_info' ), // Callback
            'fontana-settings-admin' // Page
        );  
        add_settings_field(
            'goodreads_APIKEY', // ID
            'Good Reads API', // Title 
            array( $this, 'goodreads_APIKEY_callback' ), // Callback
            'fontana-settings-admin', // Page
            'dev_setting_section_id' // Section           
        );  
        add_settings_field(
            'omdb_APIKEY', // ID
            'OMDB API', // Title 
            array( $this, 'omdb_APIKEY_callback' ), // Callback
            'fontana-settings-admin', // Page
            'dev_setting_section_id' // Section           
        );          
    }
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function fontana_sanitize( $input ) {
        $new_input = array();
        if( isset( $input['goodreads_APIKEY'] ) ) {
            $new_input['goodreads_APIKEY'] = sanitize_text_field( $input['goodreads_APIKEY'] );
        }
        if( isset( $input['omdb_APIKEY'] ) ) {
            $new_input['omdb_APIKEY'] = sanitize_text_field( $input['omdb_APIKEY'] );
        }
        return $new_input;
    }
    /** 
     * Print the Section text
     */
    public function fontana_print_section_info() {
        print 'Enter your settings below:';
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function goodreads_APIKEY_callback() {
        printf(
            '<input type="text" id="goodreads_APIKEY" name="'.$this->plugin_name.'[goodreads_APIKEY]" value="%s" />',
            isset( $this->options['goodreads_APIKEY'] ) ? esc_attr( $this->options['goodreads_APIKEY']) : ''
        );
    }
    public function omdb_APIKEY_callback() {
        printf(
            '<input type="text" id="omdb_APIKEY" name="'.$this->plugin_name.'[omdb_APIKEY]" value="%s" />',
            isset( $this->options['omdb_APIKEY'] ) ? esc_attr( $this->options['omdb_APIKEY']) : ''
        );
    }
}
