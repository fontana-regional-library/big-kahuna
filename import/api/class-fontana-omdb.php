<?php
/**
 * Provides interface to fetch data from the OmDb API.
 * 
 * API key is required.
 * 
 * @see class-fontana-response.php
 */
class Fontana_Omdb_API extends Fontana_Response {
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

  public $url;
  public $data;
  public $args;

  public $reponse;
  public $responseCode;

  public $record;

  public $defaults = array(
    'type' => 'search',
    'query_params'=> array(),
    'format' => 'movie', 
  );
  public $queryArguments;
  private $api;
  public $type;

  public function __construct($query, $args = array()){
    $this->api = esc_attr( get_option( 'fontana_api_settings' )['omdb'] );
    $this->queryArguments = $args;
    $this->checkResults($query);    
  }

  
  /**
   * OMDb API.
   * 
   * @param array $queryArguments type = movies, series, episode | query params : y = year of release, t = title, s=search
   * 
   * @link http://www.omdbapi.com/
   * 
   */

  public function getUrl($query) {

    $parameters = '';
    $args = wp_parse_args( $this->queryArguments, $this->defaults );

    $this->type = $args['type'];

    if(!empty($args['query_params'])) {
      $parameters = $this->stringify_url_params($args['query_params']);

    }
    
    switch($this->type) {
        case 'search': return  "http://www.omdbapi.com/?s=" . $query . "&type=" . $args['format'] . $parameters. "&apikey=" . $this->api;
        case 'title': return  "http://www.omdbapi.com/?t=" . $query  . "&type=" . $args['format'] . $parameters. "&apikey=" . $this->api;
        case 'imdb': return "http://www.omdbapi.com/?i=" . $query . $parameters. "&apikey=" . $this->api;
    }
  }

  public function get_info($field){
    if($this->type == 'search') {
      $value = $this->data['Search'][0][$field];
    } else {
      $value = $this->data[$field];
    }

    if($value === "N/A" || !$value){
      return false;
    }

    return $value;
    }

  /**
   * Checks the query to see if it is array.
   * 
   * If the query is an array, this function searches the goodreads api until 
   * a response is returned, then continues processing the data
   * 
   * @param   array|string  $query    What to search for
   */

  public function checkResults($query) {
    if (is_array($query)){
      foreach($query as $search){
        $this->url = $this->getUrl($search);
        $this->response = $this->fetch();
        $this->data = $this->getData('json');
        
        if(!isset($this->data["Error"])){
          break;
        }

        if(is_array($this->queryArguments) && array_key_exists('query_params', $this->queryArguments) && array_key_exists('y', $this->queryArguments['query_params'])){
          for($i=1; $i<3; $i++){
            $this->queryArguments['query_params']['y'] = $this->queryArguments['query_params']['y'] - $i;
            $this->url = $this->getUrl($search);
            $this->response = $this->fetch();
            $this->data = $this->getData('json');

            if(!isset($this->data["Error"])){
              break 2;
            }
          }
          $this->queryArguments['query_params']['y'] = $this->queryArguments['query_params']['y'] + 2;
        }
      }
    } else {
      $url = $this->getUrl($query);
      parent::__construct($url, null);
      $this->data = $this->getData('json');
    }
  }
  
}