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
}
