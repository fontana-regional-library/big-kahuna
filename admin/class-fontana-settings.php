<?php 
/**
 * The settings functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/admin
 */

class Fontana_Settings_Page {

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
	 * The api keys and providers
	 *
	 * @access   private
	 * @var      array    $apiSettings  Array of api providers and keys stored in Options table
	 */
  private $apiSettings;

  /**
	 * The Overdrive Libraries and IDs
	 *
	 * @access   public
	 * @var      array    $overdriveLibraries  Array of overdrive library names and IDs stored in Options table
	 */
  public $overdriveLibraries;

  /**
	 * The Overdrive Libraries and IDs.
   * 
   * Generated / fetched from OverDrive API when the Overdrive Library Settings are updated
   * Stored in options as: 
   *    ID ⇒ array(
   *      name              ⇒ user friendly name,
   *      products          ⇒ products link,  
   *      weblink           ⇒ library url,   // example: https://link.overdrive.com/?websiteID=867
   *      collection_token  ⇒ collection id
   *    );
	 *
	 * @access   public
	 * @var      array    $overdriveSettings  Array of overdrive library information 
	 */
  public $overdriveSettings;

  protected $collectionImporters;

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
    $this->apiSettings = get_option('fontana_api_settings');
    $this->overdriveLibraries = get_option('fontana_overdrive_libraries');
    $this->overdriveSettings = get_option('fontana_overdrive_settings');
    $this->collectionImporters = get_option('fontana_collection_imports');
	}

  /**
   * Creates the settings page and adds menu item.
   * 
   */
	public function create_settings() {
		$page_title = 'Fontana Settings Page';
		$menu_title = 'Fontana Settings';
		$capability = 'install_plugins';
    $slug = 'fontana-settings';
    $icon = 'dashicons-album';
    $callback = array($this, 'settings_page_content');
    $position = 99;
   
    add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
  }
  
  /**
   * Adds content to the settings page.
   * 
   * @see partials/fontana-admin-display.php
   */
	public function settings_page_content() { 
    $collectionImporterOptions = get_option('fontana_collection_imports');
    include_once plugin_dir_path( __FILE__ ). 'partials/fontana-admin-display.php';
  }

  /**
   * Registers the settings for storage in options table.
   */
  public function register_settings() {
    register_setting(
      'fontana_api_settings', // Option group
      'fontana_api_settings', // Option name
      array( $this, 'validateApi' ) // Sanitize
    );
    register_setting(
      'fontana_overdrive_libraries', 
      'fontana_overdrive_libraries', 
      array( $this, 'validateLibrary' ) 
    );
    register_setting(
      'fontana_collection_imports',
      'fontana_collection_imports',
      array($this, 'validateImporter')
    );
  }

  /**
   * Basic sanitization/validation of user input for Api Keys
   * 
   * 
   * @param   array   $input    Input from Fontana Settings
   * 
   * @return  array   returns array of validated input
   */
  public function validateApi($input) {
    $valid = array();
    $newApi='';
    $newKey='';
    if(empty($this->apiSettings)){
      $this->apiSettings = array(
        'overdrive'=>'',
        'overdrive_client'=>'',
        'goodreads'=>'',
        'omdb'  => '',
      );
    }

    foreach ($this->apiSettings as $api => $val){
      if( isset( $input[$api] ) &&  $input[$api] !== 'delete') {
        $valid[$api] = sanitize_text_field( $input[$api] );
      }
    }

      if(isset($input['newApi'])){
        $newApi = sanitize_text_field($input['newApi']);
      }
      if(isset($input['newKey'])){
        $newKey = sanitize_text_field($input['newKey']);
      }
      if ((strlen($newApi) > 0 || strlen($newKey) > 0) && (strlen($newApi) == 0 || strlen($newKey) == 0)) {
        add_settings_error(
                'fontana_api_settings',                     // Setting title
                'fontana_api_error',            // Error ID
                'Please enter both a new platform name and a new key',     // Error message
                'error'                         // Type of message
        );
      } elseif (strlen($newApi) > 0 && strlen($newKey) > 0) {
        $valid[$newApi] = $newKey;
      }


    $this->apiSettings = $valid;
    return $valid;
  }
  /**
   * Basic sanitization/validation of user input for OverDrive Libraries.
   * 
   * 
   * @param   array   $input    Input from Fontana Settings
   * 
   * @return  array   returns array of validated input
   */
  public function validateLibrary($input) {
    $valid = array();
    $newLibrary = '';
    $newId = '';

    if(is_array($this->overdriveLibraries)){
      foreach ($this->overdriveLibraries as $library => $id){
        if( isset( $input[$library] ) &&  $input[$library] !== 'delete') {
          $valid[$library] = sanitize_text_field( $input[$library] );
        }
      }
    }

    if(isset($input['newLibrary'])){
      $newLibrary = sanitize_text_field($input['newLibrary']);
    }
    if(isset($input['newId'])){
    $newId = sanitize_text_field($input['newId']);
    }
    if ((strlen($newLibrary) > 0 || strlen($newId) > 0) && (strlen($newLibrary) == 0 || strlen($newId) == 0)) {
      add_settings_error(
              'fontana_overdrive_libraries',                     // Setting title
              'fontana_library_error',            // Error ID
              'Please enter both a new library name and a new id',     // Error message
              'error'                         // Type of message
      );
    } elseif (strlen($newLibrary) > 0 && strlen($newId) > 0) {
      $valid[$newLibrary] = $newId;
    }

    $this->overdriveLibraries = $valid;
    return $valid;
  }

  /**
   * Verifies that the importer id input is a valid import id.
   * 
   * @param   string    $input    a comma separated list of import ids that user has input into Fontana Settings
   * 
   * @return  string  returns an array of valid import ids
   */
  public function validateImporter($input){
    global $wpdb;
    $valid = array();

    $importers = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}pmxi_imports");
    $sanitize = sanitize_text_field($input);
    $collectionList = explode(",", $sanitize);

    if ($importers) {
      foreach($collectionList as $import){
        $id = (int) trim($import);
        if (in_array($id, $importers)){
          $valid[] = $id;
        }
      }
    }
    $this->collectionImporters = $valid;
    return $valid;
  }
  /**
   * Retrieves additional information about OverDrive libraries when added.
   * 
   * When the settings where overdrive libraries (name and id) are updated, this function 
   * fetches additional information about the overdrive libraries and stores in in the
   * fontana_overdrive_settings option
   * 
   * @param   array   $old_value  The old value of the fontana_overdrive_libraries option
   * @param   array   $new_value  The updated value of the fontana_overdrive_libraries option
   */
  public function update_overdrive_settings($old_value, $new_value){
    $overDriveApi = new Fontana_Overdrive_Api();

    $valid = array();

    if (!is_array($this->overdriveSettings)){
      $this->overdriveSettings = array();
    }

    foreach($this->overdriveSettings as $id => $info){
      if(in_array($id, $new_value)){
        $valid[$id] = $info;
      }
    }

    foreach($new_value as $library => $id){
      if(!array_key_exists( $id, $valid)){
        $valid[$id]['name'] = $library;
        $info = $overDriveApi->overdriveLibraryAccount($id);
        
        if($info){
          $valid[$id]['products'] = $info['products'];
          $valid[$id]['weblink'] = $info['weblink'];
          $valid[$id]['collection_token'] = $info['collection_token'];
        }

      } else {

        if(!array_key_exists('products', $valid[$id])){
          
          $info = $overDriveApi->overdriveLibraryAccount($id); //$overDriveApi->getData('overdriveLibraryAccount', $id);
          
          if($info){
            $valid[$id]['products'] = $info['products'];
            $valid[$id]['weblink'] = $info['weblink'];
            $valid[$id]['collection_token'] = $info['collection_token'];
          }
        }
      }
    }
    $this->overdriveSettings = $valid;
    update_option('fontana_overdrive_settings', $valid);
  }
  /**
   * Adds custom columns to Shelf Taxonomy admin display page.
   * 
   * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$taxonomy_id_columns
   * @see $this->add_shelf_column_content();
   * 
   * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
   * 
   * @return  array   A modified array of column names and labels. 
   */

  public function add_shelf_columns($columns){
    $columns['location_id'] = 'Shelf ID';
    $columns['genre'] = 'Genre';
    $columns['audience'] = 'Audience';
    unset($columns['posts']);
    unset($columns['slug']);
    
    return $columns;
  }

  /**
   * Adds custom columns to Keyword Taxonomy admin display page.
   * 
   * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$taxonomy_id_columns
   * @see $this->add_shelf_column_content();
   * 
   * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
   * 
   * @return  array   A modified array of column names and labels. 
   */

  public function add_keyword_columns($columns){
    $columns['genre'] = 'Genre';
    $columns['audience'] = 'Audience';
    unset($columns['posts']);
    unset($columns['slug']);
    
    return $columns;
  }

  /**
   * Adds content to taxonomy custom columns when viewing the admin dashboard page for the taxonomy.
   * 
   * @link https://developer.wordpress.org/reference/hooks/manage_this-screen-taxonomy_custom_column/
   * 
   * @param             $content        Current content for column?
   * @param   string    $column_name    Content column name
   * @param   int       $term_id        Id of the current term
   * 
   * @return  string    content to display for the current column.
   */
  public function add_shelf_column_content($content,$column_name,$term_id){
    $term = get_term($term_id);
    switch ($column_name) {
        case 'genre':
            $genres = get_field('related_genres', $term->taxonomy."_".$term_id);
            if ($genres) {
            $content = implode(", ", array_column($genres, 'name'));
            }
            break;
        case 'audience':
            $audience = get_field('related_audience', $term->taxonomy."_".$term_id);
            if ($audience) {
            $content = implode(", ", array_column($audience, 'name'));
            }
            break;
        case 'location_id':
            $content = get_term_meta($term_id, 'shelf_location_id', true);
            
            break;
        default:
            break;
    }
    return $content;

  }

/**
 * Adds thumbnail to collection item post column listing.
 * 
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$post_type_posts_columns
 * 
 * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
 * 
 * @return  array   A modified array of column names and labels. 
 */
  function posts_columns($columns){
    $columns['verify'] = 'Review';
    $columns['thumbnail'] = 'Thumbnail';
    
    return $columns;
  }

/**
 * Adds content to custom columns.
 * 
 * Add a thumbnail image to the collection item post display for quick checking.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_$post_type_posts_custom_column
 * 
 * @param   string    $column_name    The name of the column to display.
 * @param   int       $post_id        The ID of the current post.
 * 
 */
  function posts_custom_columns($column_name, $post_id){
    if($column_name === 'thumbnail'){
      echo '<a href="' . get_edit_post_link() . '">';
      echo the_post_thumbnail( 'thumbnail' );
      echo '</a>';
    }
    if($column_name === 'verify'){
      $fail = get_post_meta($post_id, 'check_fail_count', true);
      $message = get_post_meta($post_id, 'verify', true);
      if(!empty($fail)){
        echo "<strong>Record retrieval failed " . $fail . " times.</strong><br/>";
      }
      if(empty($message)){
        $message ='';
      } else{
        $list = explode(";", $message);
        $message = "Verify:<ol>";
        foreach($list as $item){
          $message .= "<li>".$item."</li>";
        }
        $message .= "</ol>";
      }
      echo $message;
    }
  }

/**
 * Add a new item into the Bulk Actions Dropdown.
 * 
 * @param   array   $bulk_actions   array of actions in the bulk actions menu
 * 
 * @return  array   $bulk_actions
 */
  function register_custom_bulk_actions( $bulk_actions ) {
    $bulk_actions['check_holdings'] = __( 'Check Holdings', 'wordpress' );
    $bulk_actions['verify_item_info'] = __( 'Verify and Publish', 'wordpress' );
    return $bulk_actions;
  }

  /**
   * Add an admin notice after Bulk Check Collection Item Action and after checking failed records.
   */
  function bulk_check_admin_notice() {
    if ( ! empty( $_REQUEST['bulk_checked_holdings'] ) ) {
      $class = 'notice notice-info is-dismissible';
	    
      $checked_count = (int) $_REQUEST['bulk_checked_holdings']["checked"];
      $updated_count = (int) $_REQUEST['bulk_checked_holdings']["update"];
      $draft_count = (int) $_REQUEST['bulk_checked_holdings']["draft"];
      $trash_count = (int) $_REQUEST['bulk_checked_holdings']["trash"];
      $fail_count = (int) $_REQUEST['bulk_checked_holdings']["fail"];

      $message1 = __( $checked_count . ' collection items have been checked. ', 'wordpress' );
      $message2 = $updated_count > 0 ? $updated_count . " items updated. " : "";
      $message3 = $draft_count > 0 ? $draft_count . " items moved to draft. " : "";
      $message4 = $trash_count > 0 ? $trash_count . " items moved to trash. " : "";
      $message5 = $fail_count > 0 ? $fail_count . " items failed and will be added to failed check list. " : "";

      printf( '<div class="%1$s"><p>%2$s%3$s%4$s%5$s%6$s</p></div>', esc_attr( $class ), esc_html( $message1 ),esc_html( $message2 ),esc_html( $message3 ),esc_html( $message4 ),esc_html( $message5 ) );
    }
    if ( ! empty( $_REQUEST['checking_failed_items'] ) ) {
      $status = $_REQUEST['checking_failed_items']['status'];
      $class = 'notice notice-'. $status.' is-dismissible';
      $count = $_REQUEST['checking_failed_items']['count'];
      $report = $_REQUEST['checking_failed_items']['checked'];

      $message2 = $count > 0 ? "<p>" .  $count . " items are waiting to be checked.</p>" : "";
      $message3 = '';
      if(!empty($report)){
        $message3 .= '<ul>';
        foreach($report as $s => $num){
          $message3 .="<li>". $num . " items returned '" . $s ."'</li>"; 
        }
        $message3 .= '</ul>';
      }

      if($status === 'success'){
        $message = __( 'A batch of failed items was successfully checked.', 'wordpress' );
      } else{
        $message = __( 'There was an error checking failed items. No items were updated.', 'wordpress' );
      }

      printf( '<div class="%1$s"><p>%2$s</p>%3$s%4$s</div>', esc_attr( $class ), esc_html( $message ), $message2 , balanceTags($message3) );
    }
  }

  /**
   * Deletes attachments when a collection item is deleted.
   * 
   * @param   int   $post_id    The ID of the post being deleted.
   */
  public function delete_attachments($post_id){
    global $post_type;   
    if ( $post_type === 'collection-item' ){
      $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'post_parent'    => $post_id,
        )
      );
      foreach ( $attachments as $attachment ) {
          wp_delete_attachment( $attachment->ID );
      }
    }
  }

  /**
   * Sets term list option for collection item processing.
   * 
   * Can be set to run on term creation (commented out) or on admin-post (without parameters)
   * from the Fontana Settings page.
   * 
   * @param   int     $term_id  the id of the created term
   * @param   int     $tt_id    Term taxonomy ID.
   * @param   string  $taxonomy Taxonomy slug 
   */
  function collectionTermData($term_id = null, $tt_id = null, $taxonomy = 'audience'){
    //if($taxonomy === "genres" || $taxonomy === "audience" ){
      $option = array(
        'genres_terms' => array(),
        'audience_terms' => array(),
        'parent_keywords' => array()
      );
      $genres = get_terms( array(
        'taxonomy'  => 'genres',
        'hide_empty'  => 0,
      ));
      
      foreach($genres as $genre){
        $name = explode(" &amp; ", strtolower($genre->name));
        $option['genres_terms'][$genre->term_id] = $name[0];
        if(count($name) > 1 && !in_array($name[1], $option['genres_terms'])){
          $option['genres_terms'][$genre->term_id .".0"] = $name[1];
        }
        if($genre->parent >= 1){
          $option['children_list'][$genre->parent][] = $genre->term_id;
          if(!in_array($genre->parent, $option['children_list'][$genre->parent])){
            $option['children_list'][$genre->parent][] = $genre->parent;
          }
        }
      }

      $parents = get_terms( array(
        'taxonomy'  => 'keyword',
        'hide_empty'  => 0,
        'parent'  => 0
      ));
      foreach($parents as $parent){
        $option['parent_keywords'][$parent->term_id] = $parent->name;
      }
      $audiences = get_terms( array(
        'taxonomy'  => 'audience',
        'hide_empty'  => 0,
      ));
      foreach($audiences as $audience){
        $option['audience_terms'][$audience->term_id] = strtolower($audience->name);
      }

      update_option('collection_term_lists', $option, true);
    //}
    if (current_action() == 'admin_post_update_terms'){
      wp_redirect(admin_url('admin.php?page=fontana-settings'));
    }
  }
  /**
   * Adds callback filter to customize the upload directory.
   * 
   * @see $this->customize_upload_directory();
   * 
   * @param   array   $file   An array of data for a single file.
   * 
   * @return  array   A modified array of data for a single file. 
   */
  function upload_directory( $file ) {
    add_filter( 'upload_dir', array($this, 'customize_upload_directory') );
    return $file;
}
  /**
   * Customizes the upload directory for custom post type media attachments.
   * 
   * @param   array   $param    parameter array
   * 
   * @return  array   returns modified parameter array
   */
  function customize_upload_directory( $param ) {
      $id = $_REQUEST['post_id'];
      $parent = get_post( $id )->post_parent;
      if( "collection-item" == get_post_type( $id ) || "collection-item" == get_post_type( $parent ) ) {
          $mydir         = '/collection';
          $param['path'] = $param['basedir'] . $mydir;
          $param['url']  = $param['baseurl'] . $mydir;
      }
      return $param;
  }
}
   