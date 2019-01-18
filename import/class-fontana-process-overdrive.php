<?php
/**
 * The collection-specific functionality of the plugin.
 *
 * @link       http://fontanalib.org
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/collections
 */

/**
 * The collection-specific functionality of the plugin.
 *
 *
 * @package    Fontana
 * @subpackage Fontana/collections
 * @author     Amy West <amybwest@gmail.com>
 */

 class Fontana_Process_Overdrive extends Fontana_Import_Collection_Item {
  /**
   * Variable from Parent Class
   * 
   * @see class-fontana-import-collection-item.php
   * 
   * @var array       protected   $acf_term_fields  associative array of acf term fields names => associated taxonomy
   * @var string      public      $catalog          Post meta indicating catalog/collection (ACF - select: overdrive, evergreen, kanopy, rbdigital)
   * @var array       public      $form             Post meta indicating item form
   * @var array       public      $identifiers      associative array of identifiers (id => type)
   * @var bool|string public      $is_update        boolean provided by importer to indicate if item exists (converted to string for indicate deletion needed)
   * @var array       protected   $item_info        array of info from external APIs for items to add as meta data    
   * @var object      protected   $item_record      Item record results from catalog API call
   * @var array       public      $item_type        Post meta indicating item type keywords
   * @var object      public      $node             The import record
   * @var array       protected   $periodical_keywords  array of keywords that might indicate item is a periodical
   * @var int         public      $post_id          The post ID
   * @var array       protected   $post_meta        The post meta
   * @var array       public      $report           status indicator for record completeness / info that needs to be checked or verified
   * @var string      public      $record_id        The item identifier for requesting record from API
   * @var array       public      $set_terms        Container for the terms to be added to the collection-item post
   * @var array       protected   $subtopics        associative array of subtopics as Parent => taxonomy to check
   * @var array       public      $term_keys        Post meta containing keywords to match for genre, audience, etc.
   * @var array       protected   $term_list        The cached list of major taxonomy terms (genre, audience, parent keywords)
   * @var string      public      $title            The title of the item (used mainly for searching external APIs, naming imported images)
   */
  /**
   * Variable for processing Overdrive collection items.
   * 
   * @var string  public    $library    the ID of the overdrive library/collection this item is in
   */
public $library;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
  public function __construct($post_id, $is_update, $xml_node = null, $catalog = 'overdrive') {
    $this->post_id = $post_id;
    $this->is_update = $is_update;
    $library = wp_get_object_terms($this->post_id, 'shelf', array('fields' => 'slugs'));
    $this->library = $library[0];
    $this->item_record = $xml_node;

    $this->init();

    $recordCheck = $this->get_overdrive_record();
    $this->is_update = $recordCheck;
    $this->start_processing();
  }

  public function get_overdrive_record(){
    if(empty($this->item_record) || (is_array($this->item_record) && !array_key_exists('isOwnedByCollections', $this->item_record)) ){
      
      $overdriveMeta = new Fontana_Overdrive_Api();
      
      $this->item_record = $overdriveMeta->getMetaData($this->library, $this->record_id);
      if(is_wp_error($overdriveMeta->response) || $overdriveMeta->responseCode >= 400 || !isset($overdriveMeta->responseCode)){
        return 'failed';
      }
    }
    
    $recordCheck = $this->item_record['isOwnedByCollections'];

    if($recordCheck === false){
      return 'delete';
    }
    return $this->is_update;
  }

}