<?php

/**
 * The collection-specific functionality of the plugin.
 *
 *
 * @package    Fontana
 * @subpackage Fontana/collections
 * @author     Amy West <amybwest@gmail.com>
 */

class Fontana_Collection {
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
   * Variables used in collection item import processing.
   * 
   * @var array       $importers    DEPRECATED?? array of import ID's associated with collection items (must be manually set)
   */

  private $importers;

  public $catalogs = array('evergreen', 'overdrive');

  public $overdriveCatalogs;

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
    $this->overdriveCatalogs = get_option('fontana_overdrive_libraries');

    $options = get_option('fontana_collection_imports');
    $this->importers = maybe_unserialize($options);
    $this->load_dependencies();
    }

    private function load_dependencies(){
      $path = plugin_dir_path(__FILE__ );
      require_once $path . 'import-functions.php';
      require_once $path . 'class-fontana-import-collection-item.php';
      //require_once $path . 'import/class-fontana-process.php';
      require_once $path . 'class-fontana-process-evergreen.php';
      require_once $path . 'class-fontana-process-overdrive.php';
      require_once $path . 'api/class-fontana-response.php';
      require_once $path . 'api/class-fontana-evergreen.php';
      require_once $path . 'api/class-fontana-openlibrary.php';
      require_once $path . 'api/class-fontana-goodreads.php';
      require_once $path . 'api/class-fontana-omdb.php';
      require_once $path . 'api/class-fontana-overdrive.php';
    }

  /**
	 * Update existing collection items from WP All Import.
   * 
   * Auto-updating via WP All IMPORT (Restricting to update only the recordChange Date Field)
   * during testing resulted in all all auto-magically updated fields (taxonomy terms) being
   * emptied or deleted. 
   * 
   * To circumvent this behavior, turn off all updates via WPAI, and trigger process check
   * from here, rather than from pmxi_saved_post.
	 * 
   * DEPRECATED??? Checks import_id against imports identified in plugin settings as collection imports.
   * Now checks post type & collections
   * 
   * @see wp_all_import_is_post_to_update
   * 
   * @param   bool    $continue_import    - Default true value
   * @param   int     $post_id            - Post id
   * @param   array   $data               - An array holding values for the current record, If importing from XML, attributes can be accessed as SimpleXMLElement objects
   * @param   int     $import_id          - Import Id
   * 
   * @return  bool    (true = update, false = skip)
   *
	 */
  public function is_item_to_update($continue_import, $post_id, $data, $import_id){
    $post_type = get_post_type($post_id);
    $collection_type = get_post_meta($post_id, 'collection', true);

    if($post_type === "collection-item" && $collection_type === "evergreen"){   
      $import_item = new Fontana_Process_Evergreen($post_id, true, $data);
      $status = $import_item->is_update;
      $report = $import_item->report;
      $this->check_record_status($post_id, $status, 'evergreen', $report);
      return false;
    }

    if($post_type === "collection-item" && $collection_type === "overdrive"){
      $import_item = new Fontana_Process_Overdrive($post_id, true, $data);
      $status = $import_item->is_update;
      $report = $import_item->report;
      $this->check_record_status($post_id, $status, 'overdrive', $report);
      return false;
    } 
  // if(in_array($import_id, $this->importers) && isset($data["recordInfo"]["recordChangeDate"])){}
    return true;
}

  /**
	 * Gets post info to determine if processing is needed.
	 *
	 * @param   int     $id           the ID of the post
   * @param   object  $xml_node     the data that's imported
   * @param   bool    $is_update    whether the post previosuly existed (true) or is new (false)
	 */
  public function import_item($post_id, $xml_node, $is_update) { 
    $post_type = get_post_type($post_id);
    $catalog = get_post_meta($post_id, 'collection', true);

    if($post_type == "collection-item" ){

      if($catalog === 'overdrive'){
        $import_item = new Fontana_Process_Overdrive($post_id, $is_update, $xml_node);
      } elseif( $catalog === 'evergreen'){
        $import_item = new Fontana_Process_Evergreen($post_id, $is_update, $xml_node);
      } else {
        $import_item = new Fontana_Import_Collection_Item($post_id, $is_update, $xml_node, $catalog);
      }
      
      $status = $import_item->is_update;
      $catalog = $import_item->catalog;
      $report = $import_item->report;
      $this->check_record_status($post_id, $status, $catalog, $report);
    }
    //$import_id = ( isset( $_GET['id'] ) ? $_GET['id'] : ( isset( $_GET['import_id'] ) ? $_GET['import_id'] : 'new' ) );
    /* if(in_array($import_id,$this->importers)){ } */
  }

  public function process_imported_items($import_id){
    global $wpdb;
    $table = $wpdb->prefix . 'pmxi_imports';
    $data = $wpdb->get_row( 'SELECT * FROM `' . $table . '` WHERE `ID` = "' . $import_id . '"' );

    if(strpos($data->path, 'overdrive_library_download') !== false){
      preg_match("/\((.*?)\)/", $data->path, $import_info);
      $import_info = explode(",", $import_info[1]);
      $import_info = array_map('trim', $import_info);

      //// UPDATE THE OPTION TRACKING THE IMPORTS
      $count = $data->count;
      $lib_id = $import_info[0];

      if(!isset($import_info[2])){
        $results = get_option('overdrive_results_count_' . $lib_id);
        $results['offset'] += $count;
        update_option('overdrive_results_count_'. $lib_id, $results, false);
      } else {
        $results = get_option('new_overdrive_results_count_' . $lib_id);
        $results += $count;
        update_option('new_overdrive_results_count_' . $lib_id, $results, false);        
      }
    }
  }
/**
 * Checks for a list of items possibly removed from the OverDrive Catalogs.
 * 
 * After a collection importer is run (as manually set in the Fontana Settings page)
 * Checks for the existence of a list of items to check for removal (set by OverDrive importer functions)
 * @see fontana-settings to trigger if not scheduled.
 * 
 */
  public function check_deleted(){
    //Schedule to run... hourly
    //get sort by.... recordChangeDate
    //$date = strtotime("-7 day");
    $date = date('Y-m-d H:i:s', strtotime("-7 days"));
    $overDrive = new Fontana_Overdrive_Api();

    foreach($this->overdriveCatalogs as $name => $libId){
      $imported = get_option('overdrive_results_count_' . $libId);
      if(array_key_exists('status', $imported) && $imported['status'] == 'completed'){
        $count_args = array(
          'posts_per_page'    => -1,
          'post_type'         => 'post',
          'post_status'       => 'publish',
          'meta_key'          => 'collection',
          'meta_value'        => 'overdrive',
          'tax_query'   => array(
            array(
              'taxonomy'=> 'shelf',
              'field'   => 'slug',
              'terms'   => $libId,
            ),
          ),
        );
        $posts_query = new WP_Query($count_args);
        $imported['items_imported'] = $posts_query->post_count;

        $total;
        $args = array(
          'sort'      => 'dateadded:desc',
          'minimum'   => 'true'
        );
        $results = $overDrive->getProducts($libId, $args);
        if(!empty($results)){
          $imported['total'] = (int) $results['totalItems'];
          update_option('overdrive_results_count_'.$libId, $imported);
        }
      }

      $overdrive_check_batch = get_posts(array(
        'numberposts' => 15,
        'post_type'   => 'collection-item',
        'orderby'     => 'meta_value',
        'meta_key'    => 'recordChangeDate',
        'order'       => 'ASC',
        'meta_query'  => array(
          array(
            'key'     => 'collection',
            'value'   => 'overdrive',
          ),
          array(
            'key'     => 'recordChangeDate',
            'value'   =>  $date,
            'compare' => "<",
            'type'    => 'DATETIME',
          )
          ),
        'tax_query'   => array(
          array(
            'taxonomy'=> 'shelf',
            'field'   => 'slug',
            'terms'   => $libId,
          ),
        ),
      ));
      if(empty($overdrive_check_batch)){
        continue;
      }
      $item_ids = array();
      foreach($overdrive_check_batch as $key => $item){
        $item_ids[$item->ID] = get_post_meta($item->ID, 'record_identifier', true);
      }
      $bulkMeta = $overDrive->getBulkMeta($libId, $item_ids);

      if(!empty($bulkMeta)){
        $holdingsRecords = $bulkMeta['metadata'];
        foreach($item_ids as $post_id => $record_id){
          if(false !== ($key = array_search($record_id, array_column($holdingsRecords, 'id')) ) ){
            $status = $this->check_batch_item($post_id, 'overdrive', $holdingsRecords[$key]);
          }
        }
      }
    }
    
    if (!wp_next_scheduled ( 'fbk_check_deleted' )) {
      wp_schedule_event(time() +(60*60), 'hourly', 'fbk_check_deleted');
    }
    if (current_action() == 'admin_post_import_overdrive'){
      wp_redirect(admin_url('admin.php?page=fontana-settings'));
      exit;
    }
  }
  /**
   * Checks for custom bulk actions and directs to appropriate function.
   * 
   * @param   string  $redirect_url The redirect URL
   * @param   string  $doaction     The action being taken
   * @param   array   $post_ids     The items to take the action on
   * 
   * @return  string  $redirect_url The redirect URL
   */
  function collection_bulk_actions( $redirect_url, $doaction, $post_ids ){
    if ( $doaction === 'check_holdings' ) {
      $redirect_url = $this->bulk_check_holdings($redirect_url, $post_ids);
    }

    if ( $doaction === 'verify_item_info' ) {
      $redirect_url = $this->bulk_verified_holdings($redirect_url, $post_ids);
    }

    return $redirect_url;
  }
  /**
   * Deletes post meta indicating what info should be reviewed and publishes collection-item.
   * 
   * @param   string    $redirect_url
   * @param   array     $post_ids
   * 
   * @return  string    redirect url
   */
  public function bulk_verified_holdings( $redirect_url, $post_ids ) {
    foreach( $post_ids as $post_id ){
      delete_post_meta($post_id, 'verify');
      wp_publish_post($post_id);
    }
    return $redirect_url;
  }
/**
 * Checks holdings for bulk selected Collection Items.
 * 
 * @param   string  $redirect_url The redirect URL
 * @param   array   $post_ids     The items to take the action on
 * 
 * @var     array   $results      Summary of process results
 * 
 * @return  string  $redirect_url The redirect URL
 */
function bulk_check_holdings( $redirect_url, $post_ids ) {
  if(!is_array($post_ids)){
    $post_ids = explode(",", $post_ids);
  }

  $item_batch = array();

  foreach ( $post_ids as $post_id ) {
    $catalog = get_post_meta($post_id, 'collection', true);

    if($catalog === 'overdrive'){
      $library = wp_get_object_terms($post_id, 'shelf', array('fields' => 'slugs'));
      $catalog = 'overdrive-'. $library[0];
    }

    $item_batch[$post_id] = $catalog;
  }

  $batches = $this->chunk_records_by_library($item_batch);

  $report = array();
  foreach($batches as $library => $groups) {
    foreach($groups as $batch){
      $results = $this->process_item_batch_records($batch, $library);
      $report = array_merge($report, $results);
    }
  }

  $results = array("checked" => count( $post_ids ),"update" => 0, "draft" => 0, "trash" => 0, "fail" => 0);
  $status = array_count_values($report);

  if(array_key_exists('failed', $status)){
    $results['fail'] = $status['failed'];
  }

  if(array_key_exists('check', $status)){
    $results['update'] = $status['check'];
  }

  if(array_key_exists('none', $status)){
    $results['draft'] = $status['none'];
  }

  if(array_key_exists('delete', $status)){
    $results['trash'] = $status['delete'];
  }

  $redirect_url = add_query_arg( 'bulk_checked_holdings', $results, $redirect_url );
  return $redirect_url; 
}
/**
 * Creates a batch of records for checking.
 * 
 * @param   array   $array    an array of collection item post ids as post_id => collection
 * 
 * @return  array   chunked array as $libraryId => array( array(batch of 10 ids), array(batch of 10 ids) ).
 */
public function chunk_records_by_library($array){
  $batch = array();

  $libraries = array_unique($array);

  foreach($libraries as $key => $library){
    $batch[$library] = array_chunk(array_keys($array, $library), 10, false);
  }
  return $batch;
}
  /**
   * Check for a list of items that returned failed record checks.
   * 
   * A record that has failed on import will schedule this function to run.
   * Checks 10 records at a time.
   */
  public function check_failed(){
    $report = array();
    foreach($this->catalogs as $catalog){

      $args = array(
        'numberposts' => 10,
        'post_type'   => 'collection-item',
        'orderby'     => 'meta_value',
        'meta_key'    => 'recordChangeDate',
        'order'       => 'ASC',
        'post_status' => 'any',
        'meta_query'  => array(
          'relation' => 'AND',
          array(
            'key'     => 'collection',
            'value'   => $catalog
          ),
          array(
            'key'     => 'check_fail_count',
            'compare' => 'EXISTS',
          )
        )
      );

      if($catalog === 'overdrive'){
        foreach($this->overdriveCatalogs as $name => $libId){
          $args['tax_query'] = array(
            array(
              'taxonomy'=> 'shelf',
              'field'   => 'slug',
              'terms'   => $libId,
            ),
          );

          $failed_batch = get_posts($args);
          $library = $catalog. '-' . $libId;

          if(!empty($failed_batch) && !is_wp_error($failed_batch)){
            $batch = array_column($failed_batch, 'ID');
            $results = $this->process_item_batch_records($batch, $library);
            $report = array_merge($report, $results);
          }

        }
      } else {
        $failed_batch = get_posts($args);

        if(!empty($failed_batch) && !is_wp_error($failed_batch)){
          $batch = array_column($failed_batch, 'ID');
          $results = $this->process_item_batch_records($batch, $catalog);
          $report = array_merge($report, $results);
        }
      }
    }

    $args = array(
      'posts_per_page' => -1,
      'post_type' => 'collection-item',
      'meta_key' => 'check_fail_count',
      'meta_compare' => 'EXISTS'
    );

    $fail_check = new WP_Query($args);
    $fail_count = $fail_check->post_count;
    

    if($fail_count == 0){
      delete_option('failed_records_import');
    } else {
      update_option('failed_records_import', $fail_count);
    }

    if($fail_count == 0 && false !== ($scheduled = wp_next_scheduled('fbk_check_failed'))){
      wp_unschedule_event($scheduled, 'fbk_check_failed');
    }
    if(!empty($fail_count) && empty($scheduled)){
      wp_schedule_event(time() + (60*60), 'hourly', 'fbk_check_failed');
    }
    array_filter($report);
    $status = array_count_values($report);

    $results = array(
      "status" => "error", 
      "count" => $fail_count, 
      "checked" => $status
    );

    if (current_action() == 'admin_post_check_failed'){
      if(count($status) === 1 && array_key_exists('failed', $status) || empty($status)){
        $redirect_url = add_query_arg( 'checking_failed_items', $results, admin_url('admin.php?page=fontana-settings') );
      } else {
        $results["status"] = "success";
        $redirect_url = add_query_arg( 'checking_failed_items', $results, admin_url('admin.php?page=fontana-settings') );
      }
      
      wp_redirect( $redirect_url );
      exit;
    }
  }
  /**
   * Process an array of collection item post ids to check item record status.
   * 
   * @param   array   $batch    array of post ids
   * @param   string  $catalog  catalog name
   * 
   * @return  array   an array of returned item statuses
   */
  public function process_item_batch_records($batch, $catalog){
    $query = array();

    foreach($batch as $id){
      $query[$id] = get_post_meta($id, 'record_identifier', true);
    }
    
    $holdingsRecords = $this->get_bulk_records($query, $catalog);
    $report = array();

    foreach($query as $pid => $record_id){
      if(is_array($holdingsRecords) && array_key_exists($pid, $holdingsRecords)){
        $report[] = $this->check_batch_item($pid, $catalog, $holdingsRecords[$pid]);
      } elseif(is_array($holdingsRecords) && ( false !== ( $key = array_search($record_id, array_column($holdingsRecords, 'id')) ) ) ){
        $report[] = $this->check_batch_item($pid, $catalog, $holdingsRecords[$key]);
      } else {
        $this->check_record_status($pid, 'failed', $catalog);  
        $report[] = 'failed'; 
      }
    }
    return $report;
  }
/**
 * Retrieves bulk item records from an API based on catalog.
 * 
 * @param   array   $query    an array of item record identifiers
 * @param   string  $catalog  the catalog from which to retrieve records
 * 
 * @return  array   an array of results from the catalog record query
 */
public function get_bulk_records($query, $catalog){
  if($catalog === 'evergreen'){
    $args = array(
      'class_name' => 'biblio_record_entry_feed',
    );
    $recordCheck = new Fontana_Evergreen_API ($query, $args);
    return $recordCheck->getBulkHoldings();
  }
  
  if(stripos($catalog, 'overdrive') !== false){
    $libInfo = explode('-', $catalog);
    $libId = $libInfo[1];
    $recordCheck = new Fontana_Overdrive_Api();
    $records = $recordCheck->getBulkMeta($libId, $query);
    if(isset($records['metadata'])){
      return $records['metadata'];
    }
    return $records;
  }
}
/**
 * Checks list of Evergreen Catalog Items for holdings. 
 */
public function check_evergreen_holdings(){
  $date = date('Y-m-d', strtotime("-30 days"));
  $evergreen_check_batch = get_posts(array(
    'numberposts' => 10,
    'post_type'   => 'collection-item',
    'orderby'     => 'meta_value',
    'meta_key'    => 'recordChangeDate',
    'order'       => 'ASC',
    'meta_query'  => array(
      array(
        'key'     => 'collection',
        'value'   => 'evergreen',
      ),
      array(
        'key'     => 'active_date',
        'value'   =>  $date,
        'compare' => "<",
        'type'    => 'DATETIME',
      )
    )
  ));
  if(empty($evergreen_check_batch)){
    return;
  }
  $item_ids = array();
  $args = array(
    'class_name' => 'biblio_record_entry_feed',
  );
  
  foreach($evergreen_check_batch as $key => $item){
    $item_ids[$item->ID] = get_post_meta($item->ID, 'record_identifier', true);
  }
  $recordCheck = new Fontana_Evergreen_API (implode(",", $item_ids), $args);
  
  $holdingsRecords = $recordCheck->getBulkHoldings();
  if(!empty($holdingsRecords)){
    foreach($item_ids as $post_id => $record_id){
      $status = $this->check_batch_item($post_id, 'evergreen', $holdingsRecords[$post_id]);
    }
  }

  if (!wp_next_scheduled ( 'fbk_check_evergreen_holdings' )) {
    wp_schedule_event(time() + (60*60), 'twicedaily', 'fbk_check_evergreen_holdings');
  }
  if (current_action() == 'admin_post_check_evergreen_holdings'){
    wp_redirect(admin_url('admin.php?page=fontana-settings'));
    exit;
  }
}


  /**
   * Fetches an item record for an existing catalog item, based on collection/catalog.
   * 
   * @param   int     $id         The post ID
   * @param   string  $catalog    the collection the item belongs to
   * 
   * @return  string|bool         returns the item's status after check.
   */
  public function check_batch_item($id, $catalog, $record = null){
    if(stripos($catalog, 'overdrive') !== false){
      $item = new Fontana_Process_Overdrive($id, 'check', $record);
      $status = $item->is_update;
      $report = $item->report;
      $this->check_record_status($id, $status, 'overdrive', $report);
      return $status;
    }

    if($catalog === 'evergreen'){
      $item = new Fontana_Process_Evergreen($id, 'check', $record);
      $status = $item->is_update;
      $report = $item->report;
      $this->check_record_status($id, $status, 'evergreen', $report);
      return $status;
    }
    
    $item = new Fontana_Import_Collection_Item($id, 'check', $record, $catalog);
    $status = $item->is_update;
    $report = $item->report;
    $this->check_record_status($id, $status, $catalog, $report);
    return $status;
  }


  /**
   * Applies action to be taken depending on record status.
   * 
   * @param   int         $id           the post ID
   * @param   string|bool $status       status returned from record check (is_update)
   * @param   string      $collection   the collection name
   */
  public function check_record_status($id, $status, $collection = null, $report = null){
    if($status === "failed"){
      $check = get_post_meta($id, 'check_fail', true);
      
      if(!empty($check)){
        $check = maybe_unserialize($check);
        $checkDate = new DateTime($check['time']);
        $now = new DateTime("now");
        $diff = $now->diff($checkDate)->i;
      } else{
        $diff = 10;
      }
      
      if( $diff > 3) {
        $checkCount = (int) get_post_meta($id, 'check_fail_count', true);
        $data = array(
          'time' => fbkDates('datetime'),
          'response'  =>  'batch error');

        update_post_meta($id, 'check_fail', maybe_serialize($data));
        update_post_meta($id, 'check_fail_count', $checkCount+1);
      }
    } else {
      delete_post_meta($id, 'check_fail');
      delete_post_meta($id, 'check_fail_count');
    }

    if($status === "delete"){
      $this->remove_item($id, $collection, 'trash');
    }

    if($status === 'none'){
      $this->remove_item($id, $collection, 'draft');
    }

    if(($status === true || $status === 'check') && stripos($collection, 'overdrive') !== false){
      $date = fbkDates('datetime');
      update_post_meta($id, 'recordChangeDate', $date);
    }

    if(($status === true || $status === 'check')  && $collection === 'evergreen'){
      $date = fbkDates('');
      update_post_meta($id, 'active_date', $date);
    }

    if($report !== null){
      if(!in_array(false, $report)){
        $success = wp_publish_post($id);
        delete_post_meta($id, 'verify');
      } else{
        $message = "";
        foreach($report as $key => $val){
          if($val === false){
            $message .= $key . ";";
          }
        }
        $message = trim($message,";");
        wp_update_post(array(
          'ID'    =>  $id,
          'post_status'   =>  'pending'
          ));
        update_post_meta($id, 'verify', $message);
      }
    }
  }
  /**
   * Check for the existence of a post by meta query.
   * 
   * @param   string    $meta         - The meta key to query
   * @param   string    $value        - The value of the meta key to query
   * @param   string    $collection   - The collection the item is in.
   * 
   * @return  array     - List of WP_Post objects / 1 post that met search criteria.
   */

  public function check_existence($meta, $value, $collection){
    $args = array(
    'numberposts' => 1,
    'post_type'   => 'collection-item',
    'meta_query'  => array(
      array(
        'key'     =>  $meta,
        'value'   =>  $value,
        'compare' => '=',
        ),
      array(
        'key'     => 'collection',
        'value'   => $collection,
        'compare' => '=',
        ) 
      )
    );
    $post = get_posts($args);
    if(empty($post)){
      return false;
    }
    return $post;
  }

  /**
   * Updates post status and post title to identify old items.
   * 
   * @param   int     $post_id      The post ID
   * @param   string  $identifier   Something to id the item in new title
   * @param   string  $postStatus   What the status of the post should be
   */
  public function remove_item($post_id, $identifier, $postStatus){
    $item = array(
      'ID'          => $post_id,
      'post_title'  => "NO HOLDINGS -". $identifier,
      'post_status' => $postStatus,
    );
    wp_update_post($item);
  }

}