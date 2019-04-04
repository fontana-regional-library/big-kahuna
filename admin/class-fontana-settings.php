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
   * Resets the offset / counter for the OverDrive Importer.
   */
  public function delete_counter_results(){
    if (isset($_REQUEST['lib'])){
      foreach($_REQUEST['lib'] as $key => $id){
        $results = get_option('overdrive_results_count_'.$id);
        $results['offset'] = 0;
        update_option('overdrive_results_count_' . $id, $results);
      }
    }
    if (current_action() == 'admin_post_delete_results'){
      wp_redirect(admin_url('admin.php?page=fontana-settings'));
      exit;
    }
  }
  /**
   * Cleans up multiple images for collection items.
   */
  public function cleanup_collection_multiple_images(){
    global $wpdb; 

    $attachments = $wpdb->get_results("SELECT  p1.ID, p1.post_parent
    FROM {$wpdb->prefix}posts p1 
    LEFT JOIN {$wpdb->prefix}posts p2 
    ON ( p1.post_parent = p2.ID AND p2.post_type = 'collection-item')
    LEFT JOIN {$wpdb->prefix}postmeta pm ON (p2.ID = pm.post_id and pm.meta_key='_thumbnail_id')
    WHERE p1.post_type =  'attachment' AND p1.ID != pm.meta_value");

    error_log(print_r($attachments, true));

    foreach($attachments as $attachment){
      wp_delete_post($attachment->ID);
    }

    if (current_action() == 'admin_post_delete_attachments'){
      wp_redirect(admin_url('admin.php?page=fontana-settings'));
      exit;
    }

  }
}
   