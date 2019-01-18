<?php
/**
 * Provides interface to fetch data from the Open Library API.
 * 
 * @see class-fontana-response.php
 */

class Fontana_Open_Library_API extends Fontana_Response {
   /**
   * Variables from the Parent Class.
   * 
   * @access  public
   * @var     array         $args         arguments passed for wp_remote_get
   * @var     array|object  $data         an array or object representing the API results to our query
   * @var     object        $response     the response to wp_remote_get
   * @var     int           $responseCode the response code from our wp_remote_get
   * @var     string        $url          the url to fetch data from
   * 
   * @see class-fontana-response.php
   */

  /**
   * Identifiers being queried.
   * 
   * @access  public
   * @var     array   $identifiers  ISBNs or other IDs identifying a book
   */
  public $identifiers;
  protected $openLibraryTypes = array('isbn', 'oclc', 'lccn', 'olid');
  protected $filter_keywords = array(
    'Accessible book',
    'In library',
    'Large type books',
    'Open Library Staff Picks',
    'OverDrive',
    'Popular Print Disabled Books',
    'Protected DAISY',
  );


  /**
	 * Initialize the class and set its properties.
	 *
	 * @param      string|array   $identifiers    array or comma separated list of identifiers formatted as "type:identifier"
	 * @param      array          $queryArguments additional query parameters.
	 */
  public function __construct($query, $queryArguments = array()){
    if(!is_array($query)){
      $this->identifiers = explode(",", $query);
    }else {
      $identifiers = array();
      foreach($query as $identifier => $type){
        if(in_array($type, $this->openLibraryTypes)){
          $identifiers[] = $type . ":" . $identifier;
        }
      }
      $this->identifiers = $identifiers;
    }
    
    $url = "http://openlibrary.org/api/books?bibkeys=" . implode(",", $this->identifiers) . "&jscmd=data&format=json";
    parent::__construct($url);
    
    $this->data = $this->getData('json');
  }

  /**
   * Gets the cover Image url from Open Library API Response.
   * 
   * Returns the largest cover image available
   */
  public function get_cover_image() {
    if(!is_array($this->identifiers)){
      $this->identifiers = explode(",", $this->identifiers);
    }
    if(!empty($this->data)){
      $size = array('large', 'medium', 'small');
      foreach($size as $val){
        foreach($this->identifiers as $id){
          $cover = $this->data[$id]['cover'][$val];
          if($cover){
            return $cover;
          }
        }
      }
    }
    return false;
  }


  /**
   * Gets other info/identifiers for a book record.
   * 
   * @param     string    $field    The field to return - if null, returns identifiers.
   * 
   * @return    array     array of field values or associative array of identifiers
   */
  public function get_info($field = null){
    $cs = $field ? $field : "identifiers array";
    $info = array();

    if(!empty($this->data)){
      foreach($this->identifiers as $id){

        if(!empty($field)){
          $info[] = $this->data[$id][$field];
        } else{
          if(!isset($info['openlibrary_id']) && isset($this->data[$id]['identifiers']['openlibrary'])){
          $info['openlibrary_id'] = (string) $this->data[$id]['identifiers']['openlibrary'][0];
          }
          if(!isset($info['goodreads_id']) && isset($this->data[$id]['identifiers']['goodreads'])){
          $info['goodreads_id'] = (string) $this->data[$id]['identifiers']['goodreads'][0];
          }
          if(!isset($info['google_book_id']) && isset($this->data[$id]['identifiers']['google'])){
          $info['google_book_id'] = (string) $this->data[$id]['identifiers']['google'][0];
          }
        }
      }
    }
    return $info;
  }

  public function get_keywords(){
    $keywords =array();
    if(!empty($this->data)){
      if(array_key_exists("subjects", $this->data)){
        foreach($this->data["subjects"] as $key => $data){
          if(!in_array($data['name'], $this->filter_keywords)){
            $keywords['topics'][] = $data["name"];
          }
        }
      }
      if(array_key_exists("subject_places", $this->data)){
        $vals = array("parent" => "Geographics", 'values' => array());
        foreach($this->data["subject_places"] as $key => $data){
          if(!in_array($data["name"], $this->filter_keywords)){
            $vals['values'][]  = $data["name"];
          }
        }
        $keywords['subTopics'][] = $vals;
      }
      if(array_key_exists("subject_people", $this->data)){
        $vals = array("parent" => "Entities>Personal", 'values' => array());
        foreach($this->data["subject_people"] as $key => $data){
          if(!in_array($data["name"], $this->filter_keywords)){
            $vals['values'][]  = $data["name"];
          }
        }
        $keywords['subTopics'][] = $vals;
      }
      if(array_key_exists("subject_times", $this->data)){
        $vals = array("parent" => "Temporal", 'values' => array());
        foreach($this->data["subject_times"] as $key => $data){
          if(!in_array($data["name"], $this->filter_keywords)){
            $vals['values'][]  = $data["name"];
          }
        }
        $keywords['subTopics'][] = $vals;
      }
    }
    return $keywords;
  }
}