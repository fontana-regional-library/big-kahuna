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
        register_rest_route($this->plugin_name . '/v1', '/menus', array(
            'methods' => 'GET',
            'callback' => [$this, 'getAllMenus']
        ));

        register_rest_route( $this->plugin_name . '/v1', '/menus/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => [$this, 'getMenuByLocation'],
        ) );

        // Register Search API endpoint
        register_rest_route( $this->plugin_name . '/v1', '/search/', array(
          'methods' => 'GET',
          'callback' => [$this, 'fbk_get_search'],
          'args' => array(
            'per_page' => array(
              'description'       => 'Maxiumum number of items to show per page.',
              'type'              => 'integer',
              'validate_callback' => function( $param, $request, $key ) {
                return is_numeric( $param );
               },
              'sanitize_callback' => 'absint',
            ),
            'page' =>  array(
              'description'       => 'Current page of the collection.',
              'type'              => 'integer',
              'validate_callback' => function( $param, $request, $key ) {
                return is_numeric( $param );
               },
              'sanitize_callback' => 'absint'
            ),
            'category' =>  array(
              'description'       => 'Get a category from the collection.',
              'type'              => 'integer',
              'validate_callback' => function( $param, $request, $key ) {
                return is_numeric( $param );
               },
              'sanitize_callback' => 'absint'
            ),
            'tag' =>  array(
              'description'       => 'Get a tag from the collection.',
              'type'              => 'integer',
              'validate_callback' => function( $param, $request, $key ) {
                return is_numeric( $param );
               },
              'sanitize_callback' => 'absint'
            ),
            'content' =>  array(
              'description'       => 'Hide or show the_content from the collection.',
              'type'              => 'boolean',
              'validate_callback' => function( $param, $request, $key ) {
                if ( $param == 'true' || $param == 'TRUE' ) {
                  $param = true;
                } else if( $param == 'false' || $param == 'FALSE') {
                  $param = false;
                }
                return is_bool( $param );
               }
            ),
            'search' =>  array(
              'description'       => 'The search term used to fetch the collection.',
              'type'              => 'string',
              'required'          => true,
              'validate_callback' => function($param, $request, $key) {
                  return is_string( $param );
                },
              'sanitize_callback' => 'sanitize_text_field'
            ),
          ),
        ) );

        /**
         * Register Custom Fields
         */
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

       // Add Content counts by post-type for taxonomies
       $taxonomies = get_taxonomies(array(
        'show_in_rest' => true
      ));
       register_rest_field( array_values($taxonomies), 'count_by_type',
          array(
            'get_callback'    => array( $this, 'add_term_post_counts'),
            'update_callback' => null,
            'schema'          => null,
          )
        ); 
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

/**
 * Get the featured image
 */
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
    if($request['new']) {
			$itemDateQuery =
          array(
            'key'	=>	'record_creation_date',
            'value' => '',
            'compare' => '>=',
            'type'  => 'DATE'
          );
          if($request['new'] === 'this-week'){
            $itemDateQuery['value'] = date("y-m-d", strtotime('-7 days'));
          }elseif($request['new'] === 'this-month'){
            $itemDateQuery['value'] = date("y-m-d", strtotime('-30 days'));
          }else{
            $itemDateQuery['value'] = $request['new'];
          }
			$meta_query[]=$itemDateQuery;
		}

		$args['meta_query'] = $meta_query; 
		return $args;
  }

  /**

   * Removes the "continue reading" links from post excerpts.
   */
  function remove_read_more($more){
    return;
  }

  /*
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

  /**
   * Construct response for Search API endpoint
   */
  function fbk_get_search( WP_REST_Request $request ) {
    // check for params
    $posts_per_page = $request['per_page']?: '10';
    $page = $request['page']?: '1';
    $category = $request['category']?: null;
    $tag = $request['tag']?: null;
    $content = $request['content'];
    $show_content = filter_var($content, FILTER_VALIDATE_BOOLEAN);
    $search = $request['search']?: null;
    // WP_Query arguments
    $args = array(
      'nopaging'               => false,
      'posts_per_page'         => $posts_per_page,
      'paged'                  => $page,
      'cat'                    => $category,
      'tag_id'                 => $tag,
      's'                      => $search
    );
    // The Query
    $query = new WP_Query( $args );
    // Setup Posts Array
    $posts = array();
    // The Loop
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();
        global $post;
        // For Headers
        $total = $query->found_posts;
        $pages = $query->max_num_pages;
        // post object
        $fbk_post = new stdClass();
        // get post data
        $permalink = get_permalink();
        $fbk_post->id = get_the_ID();
        $fbk_post->title = get_the_title();
        $fbk_post->type = 'tribe_events'===($type = get_post_type()) ? 'event' : $type;

        $fbk_post->slug = $post->post_name;
        $fbk_post->permalink = $permalink;
        $fbk_post->date = get_the_date('c');
        $fbk_post->date_modified = get_the_modified_date('c');
        $fbk_post->excerpt = get_the_excerpt();
        $fbk_post->content = apply_filters('the_content', get_the_content());
        
        $fbk_post->author = esc_html__(get_the_author(), 'text_domain');
        $fbk_post->author_id = get_the_author_meta('ID');

        // add tribe_event fields
        if($fbk_post->type === 'event'){
          $meta = tribe_get_event_meta();
          
          $fbk_post->start_date = $meta["_EventStartDate"][0];
          $time = strtotime( $fbk_post->start_date );
          $fbk_post->start_date_details = array(
                        'year'    => date( 'Y', $time ),
                        'month'   => date( 'm', $time ),
                        'day'     => date( 'd', $time ),
                        'hour'    => date( 'H', $time ),
                        'minutes' => date( 'i', $time ),
                        'seconds' => date( 's', $time ),
                      );
          $fbk_post->end_date = $meta["_EventEndDate"][0];
          $time = strtotime( $fbk_post->end_date );
          $fbk_post->end_date_details = array(
                        'year'    => date( 'Y', $time ),
                        'month'   => date( 'm', $time ),
                        'day'     => date( 'd', $time ),
                        'hour'    => date( 'H', $time ),
                        'minutes' => date( 'i', $time ),
                        'seconds' => date( 's', $time ),
                      );
          $fbk_post->organizer_ids = $meta["_EventOrganizerID"];
          $fbk_post->venue_ids = $meta["_EventVenueID"];
          
        }
        /*
         *
         * return acf fields if they exist and depending on query string
         *
         */
        
        $fbk_post->acf = $this->fbk_get_acf();

        
        /*
         *
         * get featured image
         *
         */
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
          
        if($image){
          $fbk_post->featured_thumbnail = $image[0];
        }
        
        // Push the post to the main $post array
        array_push($posts, $fbk_post);
      }
      //$count = $this->fbk_content_count($search);
      $results=array(
        'total' => (int) $total,
        'total_pages' => (int) $pages,
        'count' => $this->fbk_content_count($search),
        'content' => $posts
      );
      // return the post array
      $response = rest_ensure_response( $results );
      $response->header( 'X-WP-Total', (int) $total );
      $response->header( 'X-WP-TotalPages', (int) $pages );
      return $response;
    } else {
      // return empty posts array if no posts
      return $posts;
    }
    // Restore original Post Data
    wp_reset_postdata();
  }
  /**
   * Get the ACF content for a post.
   */
  function fbk_get_acf() {
  
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    // check if acf is active before doing anything
     if( is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php') ) {
       // get fields
       $acf_fields = get_fields();
       // if we have fields
       if( $acf_fields ) {
         return $acf_fields;
       }
     } else {
       // no acf, return false
       return false;
     }
  }
  
  /**
   * Constructs container for post-type counts for search API.
   */
  function fbk_content_count($search){
    $post_types = get_post_types(array(
      '_builtin'  => false,
      'public'    => true,
      'publicly_queryable'  => true
    ));
    array_push($post_types, 'post', 'page');
    $results = array();
    foreach($post_types as $type){
      if($type === 'tribe_events'){
        $results['events'] = $this->get_post_count_by_type($search, 'tribe_events');
      } else{
      $results[$type] = $this->get_post_count_by_type($search, $type);
      }
    }

    return $results;
  }
  /**
   * Get post-type counts for search API.
   */
  function get_post_count_by_type($search, $postType){
    $query = new WP_Query([
      'posts_per_page' => 1,
      'post_type' => $postType,
      's' => $search
    ]);

    return (int) $query->found_posts;
  }
/**
 * Get post-type counts for Terms.
 */
  function get_term_post_count_by_type($postType, $taxonomy, $term){
    $query = new WP_Query([
      'posts_per_page' => 1,
      'post_type' => $postType,
      'tax_query' => array(
        array(
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => $term
        )
      )
    ]);
    
    return $query->found_posts; 
  }
  /**
   * Construct container for post type counts to be included in taxonomy rest response.
   */
  function add_term_post_counts($object, $field_name, $request, $object_type){
    $post_types = get_post_types(array(
      '_builtin'  => false,
      'public'    => true,
      'publicly_queryable'  => true
    ));
    array_push($post_types, 'post', 'page');

    $results = array();
    foreach($post_types as $type){
      if($type === 'tribe_events'){
        $results['events'] = $this->get_term_post_count_by_type('tribe_events', $object['taxonomy'], $object['slug']);
      } else{
        $results[$type] = $this->get_term_post_count_by_type($type, $object['taxonomy'], $object['slug']);
      }
    }

    return $results;
  }
}