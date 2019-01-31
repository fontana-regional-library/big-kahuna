<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://schoeyfield.com
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fontana
 * @subpackage Fontana/public
 * @author     Michael Schofield <michael@schoeyfield.com>
 */
class Fontana_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fontana-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fontana-public.js', array( 'jquery' ), $this->version, false );

	}

	public function registerMenusWithApi()
    {
        register_rest_route('menus/v1', '/menus', array(
            'methods' => 'GET',
            'callback' => [$this, 'getAllMenus']
        ));

        register_rest_route( 'menus/v1', '/menus/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => [$this, 'getMenuByLocation'],
        ) );
    }

    public function getAllMenus()
    {
        $menus = [];
        foreach (get_registered_nav_menus() as $slug => $description) {
            $object = new \stdClass();
            $object->slug = $slug;
            $object->description = $description;
            $menus[] = $object;
        }

        return $menus;
    }

    function getMenuByLocation ( $data ) {
        $menu = new stdClass;
        $menu->items = [];
        if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $data['id'] ] ) ) {
            $menu = get_term( $locations[ $data['id'] ] );
            $menu->items = wp_get_nav_menu_items($menu->term_id);
        }
        return $menu;
	}
	public function register_images_field() {
		register_rest_field( array('collection-item','post','tribe_events'), 'featured_image',
        array(
          'get_callback'    => array( $this, 'get_image_url_full'),
          'update_callback' => null,
          'schema'          => null,
        )
      );

      
       // Add 'featured_image_thumbnail'
      
      register_rest_field( array('collection-item','post'), 'featured_image_thumbnail',
         array(
           'get_callback'    => array( $this, 'get_image_url_thumb'),
           'update_callback' => null,
           'schema'          => null,
         )
       );
	}
	
	function get_image_url_thumb(){
		$url = $this->get_image('thumbnail');
		return $url;
	  }
	
	  
	   // Get Image: Full
	  function get_image_url_full(){
		$url = $this->get_image('full');
		return $url;
	  }
	
	  
	   // Get Image Helpers
	  function get_image($size) {
		$id = get_the_ID();
	
		if ( has_post_thumbnail( $id ) ){
			$img_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size );
			$url = $img_arr[0];
			return $url;
		} else {
			return "";
		}
	}
	public function add_tribe_event_data($data, $event) {	
		if (is_plugin_active('the-events-calendar/the-events-calendar.php')) {
			$event_id = $data['id'];	
			$services = get_the_terms( $event_id, 'services' );
			$locations = get_the_terms( $event_id, 'location' );
			$data["acf"]["services"] = $services;
			$data["acf"]["locations"] = $locations;
			}
			return $data;
    }
    /**
	 * 
	 * Sort collection items by record creation date and add filters for collection and type.
	 * 
	 * https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 * 
	 */
	function collection_api_newest($args, $request) {
		$meta_query = array();
		
		$args['orderby'] = 'meta_value';
		$args['order'] = 'DESC';
		$args['meta_key'] = 'record_creation_date';

    if($request['collection']) {
			$collectionQuery = array(
				'key'	=>	'collection',
				'value' => $request['collection'],
				'compare' => 'LIKE'
        );
      $meta_query[]=$collectionQuery;
		}

		if($request['form']) {
			$itemFormQuery = array(
        array(
          'relation' => 'OR',
          array(
            'key'	=>	'form',
            'value' => $request['form'],
            'compare' => 'LIKE'
          ),
          array(
            'key'	=>	'item_type',
            'value' => $request['form'],
            'compare' => 'LIKE'
          ),
        )
      );
			$meta_query[]=$itemFormQuery;
		}

		$args['meta_query'] = $meta_query; 
		return $args;
  }
  /**
	 * 
	 * Filter Alerts api.
	 * 
	 * https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 * 
	 */
	function alert_api($args, $request) {
    $meta_query = array();
    $today = date("Y-m-d H:i:s");

    $alertExpiredQuery = array(
			'key'	=>	'notice_expiration',
			'value' => $today,
      'compare' => '>=',
      'type'  => 'DATETIME'
		);

    $meta_query[] = $alertExpiredQuery;
    
		
		$args['orderby'] = array('notice_expiration' => 'ASC',);

    if($request['alerts']) {
      $meta_query[] = array(
        'key'	=>	'start_notification',
        'value' => $today,
        'compare' => '<='
      );

      $meta_query[] = array(
          'key'	=>	'notice_type',
          'value' => 'announcement',
          'compare' => '!='
      );
		}
		$args['meta_query'] = $meta_query; 
		return $args;
	}
}