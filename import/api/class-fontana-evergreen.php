<?php
class Fontana_Evergreen_API extends Fontana_Response {
  /**
   * Variables from the Parent Class.
   * 
   * @access  public
   * @var   array         $args         arguments passed for wp_remote_get
   * @var   array|object  $data         an array or object representing the API results to our query
   * @var   object        $response     the response to wp_remote_get
   * @var   int           $responseCode the response code from our wp_remote_get
   * @var   string        $url          the url to fetch data from
   * 
   * @see class-fontana-response.php
   */

  /**
   * The protected variables used in fetching or passing item data.
   * 
   * @access  protected
   * @var   array         $defaults   the default arguments used when constructing our search url
   * 
   */
  protected $defaults = array(
    'type' => 'search',
    'format' => 'holdings_xml',
    'org_unit' => 'FONTANA',
    'record_type' => 'record',
    'class_name' => 'bre',
    'includes' => '{holdings_xml,acn,acp,mra}',
    'result_count' => 50,
    'result_page' => 1,
    'query_params'=> array(),
  );
  protected $query;

  /**
   * The public variables used in fetching or passing item data.
   * 
   * @access  public
   * @var   array         $queryArguments
   * @var   string        $type             the type of search  
   * 
   */

  public $type;
  public $queryArguments;

  //public $shelfLocation = array();
  //public $url;

  /**
	 * Initialize the class and set its properties.
	 *
	 * @param      string   $query            what to search for (TCN, Title, etc)
	 * @param      array    $queryArguments   additional query parameters.
	 */

  public function __construct($query, $queryArguments = array()){
    $url = $this->getUrl($query, $queryArguments);
    parent::__construct($url, null);
    $this->data = $this->getData('xml');
  }

  
  

  /**
   * Contructs Url to query Evergreen/NC Cardinal API.
   * 
   * Query can be further specified by supplying $args parameter when instatiating this class.
   * Args will be wp_parse_args against $this->defaults
   * 
   * @see $this->defaults
   *  
   * @param   string    $query      What to search for (goodreads id, title, isbn, etc)
   * @param   array     $arguments  Further specifies search criteria
   */
  public function getUrl($query, $arguments) {

    $parameters = '';
    $args = wp_parse_args( $arguments, $this->defaults );

    $this->type = $args['type'];

    if(!empty($args['query_params'])) {
        $parameters = $this->stringify_url_params($args['query_params']);
    }
    if(is_array($query)){
      $this->query = $query;
      $query=implode(",", $query);
    }
    /**
     * Evergreen Record API: http://docs.evergreen-ils.org/3.1/_records.html
     * Evergreen UnAPI: http://docs.evergreen-ils.org/3.1/_using_unapi.html
     * Evergreen OpenSearch: http://docs.evergreen-ils.org/3.1/_using_opensearch_as_a_developer.html
    */
    switch($this->type){
        case 'opensearch':  return "http://www.nccardinal.org/opac/extras/opensearch/1.1/" . $args['org_unit'] . "/" . $args['format'] . $query;
        case 'supercat':    return "http://www.nccardinal.org/opac/extras/supercat/retrieve/" . $args['format'] . "/" . $args['record_type'] . "/" . $query;
        case 'item-age':    return "http://nccardinal.org/opac/extras/browse/" . $args['format'] . "\/item-age/" . $args['org_unit'] . "/". $args['result_page'] . "/" . $args['result_count'] . $parameters;
        default:            return "http://nccardinal.org/opac/extras/unapi?format=" . $args['format'] . ";id=tag::U2@" . $args['class_name'] . "/" . $query . $args['includes']. "/" . $args['org_unit'];
    }
  }

  /**
   * Gets the holdings data from a default search.
   * 
   * @return    array   Returns an array of holdings information including library, sheld, barcode, etc.
   */
  public function getHoldings(){
    $info = array();
    if(!empty($this->data)){
      $itemRecord = $this->data;
      
      $info['count'] = array(
          'FRL' => (int) $itemRecord->counts->count[1]->attributes()->count,
          'NC' => (int) $itemRecord->counts->count[0]->attributes()->count,
        );
      
      if($info['count']['NC'] === 0){
        return "delete";
      }
      if(!$info['count']['FRL'] || $info['count']['FRL']<1){
        return "none";
      }
      
      foreach($itemRecord->volumes->volume as $volume){
        foreach($volume->copies->copy as $copy){
          $info['holdings'][] = array(
            "library" => (string) $copy->circ_lib->attributes()->shortname,
            "shelf"   => (int) $copy->location->attributes()->ident,
            "barcode" => (string) $copy->attributes()->barcode,
            "copy_id" => (string) $copy->attributes()->copy_id,
            "status"  => (int) $copy->status->attributes()->ident,
            "deleted" => (bool) $copy->attributes()->deleted,
          );
        }
      }
    }
    return $info;
  }
  public function getBulkHoldings(){
    $info = array();

    if(!empty($this->data)){
      $itemRecord = $this->data;
      $ids = array_keys($this->query);

      foreach($itemRecord->holdings as $key => $item){
          $volumeRecord = array();
          $volumeRecord['count'] = array(
            'FRL' => (int) $item->counts->count[1]->attributes()->count,
            'NC' => (int) $item->counts->count[0]->attributes()->count,
          );
        if($volumeRecord['count']['NC'] === 0){
          $info[] = 'delete';
        } elseif (!$volumeRecord['count']['FRL'] || $volumeRecord['count']['FRL']<1){
          $info[] = "none";
        } else {
          foreach($item->volumes->volume as $volume){
            if(is_array($volume->copies->copy)){
              foreach($volume->copies->copy as $copy){
                $volumeRecord['holdings'][] = array(
                  "library" => (string) $copy->circ_lib->attributes()->shortname,
                  "shelf"   => (int) $copy->location->attributes()->ident,
                  "barcode" => (string) $copy->attributes()->barcode,
                  "copy_id" => (string) $copy->attributes()->copy_id,
                  "status"  => (int) $copy->status->attributes()->ident,
                  "deleted" => (bool) $copy->attributes()->deleted,
                );
              }
            } else {
              $volumeRecord['holdings'][] = array(
                "library" => (string) $volume->copies->copy->circ_lib->attributes()->shortname,
                "shelf"   => (int) $volume->copies->copy->location->attributes()->ident,
                "barcode" => (string) $volume->copies->copy->attributes()->barcode,
                "copy_id" => (string) $volume->copies->copy->attributes()->copy_id,
                "status"  => (int) $volume->copies->copy->status->attributes()->ident,
                "deleted" => (bool) $volume->copies->copy->attributes()->deleted,
              );
            }
          }
          $info[] = $volumeRecord;
        }
      }
      $info = array_combine( $ids, $info );
    }
    return $info;
  }

}