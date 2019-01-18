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
class Fontana_Process_Evergreen extends Fontana_Import_Collection_Item {
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
   * Variables for processing Evergreen Item Records.
   * 
   * @var array         $available    array of item status codes that indicate an item is publicly available
   * @var array|string  $holdings     array of item holdings information, or string indicating status ('delete' if 0 copies in consortium, 'none' if 0 locally held.)
   * @var array         $status       array of copy status codes
   * @var array         $statusCodes  associative array of Evergreen Copy Status codes as codeId => codeName       
   */
   protected $available = array (0,1,5,6,7,8,9,11,15,103,104,105,107,108,109);
   public $holdings;
   public $status = array();
   public $statusCodes = array(
    0 => "Available",
    1 => "Checked out",
    2 => "Bindery",
    3	=> "Lost",
    4	=> "Missing",
    5	=> "In process",
    6	=> "In transit",
    7	=> "Reshelving",
    8	=> "On holds shelf",
    9	=> "On order",
    10 => "ILL",
    11 => "Cataloging",
    12  => "Reserves",
    13  => "Discard/Weed",
    14  => "Damaged",
    15 =>	"On reservation shelf",
    16 =>	"Long Overdue",
    17 =>	"Lost and Paid",
    18 =>	"Canceled Transit",
    101 => "Never Returned",
    102 => "Claimed Lost",
    103 => "Storage",
    104 => "On Display",
    105 => "In Transit",
    106 => "Repair",
    107 => "At Children's Desk",
    108 => "At Circulation Desk",
    109 => "In Use for Programs",
    110 => "Noncirculating",
    134 => "Digitization in Process",
  );



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($post_id, $is_update, $xml_node = null, $catalog ='evergreen') {
    $this->is_update = $is_update;
    $this->post_id = $post_id;  
    $this->catalog = $catalog;  
    if(!is_bool($this->is_update)){
      $this->holdings = $xml_node;
    } else{
      $this->node = $xml_node;
    }
    
    $this->init();
    if($this->is_update === true){
      $this->is_update = $this->update_record();
    } 
    
    if($this->is_update !== true){
      $this->is_update = $this->get_evergreen_record();
    }

    $this->start_processing();
  }

  /**
   * Retrieves record for this collection item and catalogs holdings.
   * 
   * Checks if the item is still owned. Returns status of item if no holdings available.
   */
  public function get_evergreen_record(){
    $check = $this->is_update;
    if(empty($this->holdings)){
      $this->item_record = new Fontana_Evergreen_API($this->record_id);
      if( $this->item_record->responseCode >= 400 ){
        return 'failed';
      }
      $this->holdings = $this->item_record->getHoldings();
    }

    if($this->holdings === 'delete'){
      return "delete";
    }

    $this->delete_acf_rows('holdings');

    if($this->holdings === 'none'){
      return "none";
    }

    foreach( $this->holdings['holdings'] as $holding){
      $this->status[] = $holding['status'];
    
      $holding_row = $this->construct_holding($holding);
      
      if(!empty($holding_row)){
        if( !array_key_exists('location', $this->set_terms) || !in_array($holding_row['location'], $this->set_terms['location']) ){
          $this->set_terms['location'][] = (string) $holding_row['location'];
        }
        if( !array_key_exists('shelf', $this->set_terms) || !in_array($holding_row['shelving_location'], $this->set_terms['shelf']) ){
          $this->set_terms['shelf'][] = (string) $holding_row['shelving_location'];
        }
        add_row('holdings', $holding_row, $this->post_id);
      }
    }
    
    if(empty($this->set_terms['location'])){
      return 'none';
    }
    
    return $check;
  }
  /**
   * Creates array of item holdings information to add via ACF and retrieves related terms.
   * 
   * @param   array   $holding    array of holding information
   * 
   * @return  array   an array / row of data for ACF 'holdings'
   */
  public function construct_holding($holding){
    if(in_array($holding['status'], $this->available)){
      $shelf = $this->get_term_from_meta($holding['shelf'], 'shelf_location_id' , 'shelf');
      $library = $this->get_term_from_meta($holding['library'], 'shortcode', 'location');

      $shelfMeta = get_term_meta($shelf[0]->term_id);

      $shelfGenre = array();
      if(is_array($shelfMeta) && array_key_exists('related_genres', $shelfMeta)){
        $terms = maybe_unserialize($shelfMeta['related_genres']);
        if(is_array($terms) && !empty($terms)){
          $shelfGenre = $terms;
        }
      }

      $shelfAudience = array();
      if(is_array($shelfMeta) && array_key_exists('related_audience', $shelfMeta)){
        $terms = maybe_unserialize($shelfMeta['related_audience']);
        if(is_array($terms) && !empty($terms)){
          $shelfAudience = $terms;
        }
      }  

     foreach($shelfGenre as $genId){
        if(!in_array($genId,  $this->set_terms['genres']) && $genId > 1){
          $this->set_terms['genres'][] = (int) $genId;
        }
      }
      foreach($shelfAudience as $audId){
        if(!in_array($audId, $this->set_terms['audience']) && $audId > 1){
          $this->set_terms['audience'][] = (int) $audId;
        }
      }
      $row = array(
        'barcode'	=> $holding['barcode'],
        'shelving_location'	=> $shelf[0]->slug,
        'location'	=> $library[0]->slug
      );
      return $row;
    }
    return null;
  }
  /**
   * Checks edit date to see if it has changed since last import.
   * 
   * @return bool|string    returns true if nothing date is same, returns check if something has changed since last import.
   */
  public function update_record(){
    if($this->is_update == true && isset($this->node["recordInfo"]["recordChangeDate"])){
      $edit_date = $this->post_meta->recordChangeDate[0];
      $recordChangeDate = $this->node["recordInfo"]["recordChangeDate"];

      $new_edit_date = fbkDates($recordChangeDate);

      if($edit_date == $new_edit_date){
        return true;
      }
      update_post_meta( $post_id, 'recordChangeDate', $new_edit_date); 
    }
    return 'check';
  }
}