<?php
/**
 * Provides interface to fetch data from the Goodreads API.
 * 
 * API key is required.
 * 
 * @see class-fontana-response.php
 */
class Fontana_Goodreads_API extends Fontana_Response {
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
   * The API key for accessing the external endpoints
   * 
   * @access  private
   * @var     string    $api    The API Key for accessing the Goodreads API
   */
  private $api;

  /**
   * The public variables used in fetching or passing item data.
   * 
   * @access  public
   * @var   array         $defaults   the default arguments used when constructing our search url
   * @var   array         $queryArguments
   * @var   array|object  $record
   * @var   string        $type       the type of search 
   * 
   */
  public $defaults = array(
    'type' => 'search',
    'query_params'=> array(),
  );
  // protected $partial_matches = array(
  //   '2017-reads',
  //   '2017-books-read',
  //   '2018-reads',
  //   '2018-books-read',
  //   '2019-reads',
  //   '2019-books-read',
  //   '2020-reads',
  //   '2020-books-read',
  //   '2021-reads',
  //   '2021-books-read',
  //   'books-i-own',
  //   'books-i-purchased',
  //   'books-i-want-to-read-in-2017',
  //   'books-i-want-to-read-in-2018',
  //   'books-i-want-to-read-in-2019',
  //   'books-i-want-to-read-in-2020',
  //   'books-i-want-to-read-in-2021',
  //   'books-i-want-to-read-in-2022',
  //   'read-in-2017',
  //   'read-in-2018',
  //   'read-in-2019',
  //   'read-in-2020',
  //   'read-in-2021',
  //   'read-in-2022',
  //   'to-buy',
  //   'to-read',
  //   'to-read-fiction',
  //   'to-re-read',
  //   'to-reread',
  //   'audible',
  //   'audible-com',
  //   'favorite-authors',
  //   'favorite-books',
  //   'favorites',
  //   'favourites',
  //   'my-books',
  //   'my-library',
  //   'need-to-buy',
  //   'netgalley',
  //   'library',
  //   'library-returned',
  //   'home-library',
  //   'next-to-read',
  //   'recommended',
  //   'reviewed',
  //   'review-books-read',
  //   'review-copies',
  // );
  // protected $filter_keywords = array(
  //   'arc',
  //   'at-home',
  //   'audio',
  //   'audio-books',
  //   'audio-book',
  //   'audiobooks',
  //   'audiobook',
  //   'books',
  //   'books-that-were-won',
  //   'coming-soon',
  //   'cover-love',
  //   'currently-reading',
  //   'default',
  //   'did-not-finish',
  //   'e-book',
  //   'e-books',
  //   'ebook',
  //   'ebooks',
  //   'eventually',
  //   'for-review',
  //   'for-sale',
  //   'hardcover',
  //   'have',
  //   'holds',
  //   'i-own',
  //   'kindle',
  //   'maybe',
  //   'not-read',
  //   'on-hold',
  //   'own-it',
  //   'owned',
  //   'owned-books',
  //   'on-my-bookshelf',
  //   'paperback',
  //   're-read',
  //   'tbr',
  //   'unfinished',
  //   'wish-list'
  // );
  protected $filter_keywords = array(
    '2017-reads',
    '2017-books-read',
    '2018-reads',
    '2018-books-read',
    '2019-reads',
    '2019-books-read',
    '2020-reads',
    '2020-books-read',
    '2021-reads',
    '2021-books-read',
    'abandoned',
    'arc',
    'at-home',
    'audible',
    'audible-com',
    'audio',
    'audio-books',
    'audio-book',
    'audiobooks',
    'audiobook',
    'books',
    'books-i-own',
    'books-i-purchased',
    'books-i-want-to-read-in-2017',
    'books-i-want-to-read-in-2018',
    'books-i-want-to-read-in-2019',
    'books-i-want-to-read-in-2020',
    'books-i-want-to-read-in-2021',
    'books-i-want-to-read-in-2022',
    'books-that-were-won',
    'coming-soon',
    'cover-love',
    'currently-reading',
    'default',
    'did-not-finish',
    'e-book',
    'e-books',
    'ebook',
    'ebooks',
    'eventually',
    'favorite-authors',
    'favorite-books',
    'favorites',
    'favourites',
    'fiction',
    'for-review',
    'for-sale',
    'hardcover',
    'have',
    'holds',
    'home-library',
    'i-own',
    'kindle',
    'library',
    'library-returned',
    'maybe',
    'my-books',
    'my-library',
    'need-to-buy',
    'net-galley',
    'netgalley',
    'next-to-read',
    'not-read',
    'on-hold',
    'own-it',
    'owned',
    'owned-books',
    'on-my-bookshelf',
    'paperback',
    're-read',
    'read-in-2017',
    'read-in-2018',
    'read-in-2019',
    'read-in-2020',
    'read-in-2021',
    'read-in-2022',
    'recommended',
    'reviewed',
    'review-books-read',
    'review-copies',
    'tbr',
    'to-buy',
    'to-read',
    'to-read-fiction',
    'to-re-read',
    'to-reread',
    'unfinished',
    'wish-list'
  );
  public $queryArguments;
  public $record;
  public $type;

  /**
	 * Initialize the class and set its properties.
	 *
	 * @param      string|array   $query   (array) ISBNs, (string) Goodreads ID, or (string) title and author
	 * @param      array          $args    additional query parameters.
	 */
  public function __construct($query, $args = array()){
    $this->api = esc_attr( get_option( 'fontana_api_settings' )['goodreads'] );
    $this->queryArguments = $args;

    $this->checkResults($query);

    $this->data = $this->getData('xml');
  }

  
  /**
   * Contructs Url to query GoodReads API.
   * 
   * Query can be further specified by supplying $args parameter when instatiating this class.
   * Args will be wp_parse_args against $this->defaults
   * 
   * 
   * @param   string    $query    What to search for (goodreads id, title, isbn, etc)
   * 
   * @see $this->defaults
   * 
   * @link https://www.goodreads.com/api
   * 
   */

  public function getUrl($query) {
    $parameters = '';
    
    if(!empty($args['query_params'])) {
      $parameters = $this->stringify_url_params($args['query_params']);
    } 
    
    switch($this->type) {
        case 'search': return "https://www.goodreads.com/search/index.xml?key=". $this->api . "&q=" . $query;
        case 'isbn': return "https://www.goodreads.com/book/isbn/" . $query . "?key=" . $this->api;
        case 'goodreads': return "https://www.goodreads.com/book/show/". $query . ".xml?key=" . $this->api;
    }
  }

  /**
   * Checks the query to see if it is array.
   * 
   * If the query is an array, this function searches the goodreads api until 
   * a response is returned, then continues processing the data
   * 
   * @param   array|string  $query    What to search for (isbns, title, etc.)
   */

  public function checkResults($query) {
    $args = wp_parse_args( $this->queryArguments, $this->defaults );
    $this->type = $args['type'];
    
    if (is_array($query)){
      foreach($query as $key => $item){
        if($this->type == 'isbn' && $item === 'isbn' && !empty($key)){
          $this->url = $this->getUrl($key);
          $this->response = $this->fetch();
          $responsecode = wp_remote_retrieve_response_code( $this->response );
          if(!empty($responsecode) && !is_wp_error($responsecode) && $responsecode < 400){
            return;
          }
        }
        if($this->type !== 'isbn'){
          $this->url = $this->getUrl($item);
          $this->response = $this->fetch();
          $responsecode = wp_remote_retrieve_response_code( $this->response );
          if(!empty($responsecode) && !is_wp_error($responsecode) && $reponsecode < 400){
            return;
          }
        }
      }
    }else{
    $url = $this->getUrl($query);
    parent::__construct($url, null);
    }
    return;
  }

  /**
   * Returns the value of a particular field from the Goodreads API data returned.
   * 
   * This function also checks that the cover url retrieved is valid and not a placeholder image
   * 
   * @param   string    $field    the name of the key/field for which to return data.
   */

  public function get_info($field){
    $result ='';

    if($this->type == "search" && !empty($this->data) && !empty($this->data->search->results)){

      if (isset($this->data->search->results)){
        $result = (string) $this->data->search->results->work[0]->best_book->$field[0][0];

        if(empty($result)){
          $result = (string) $this->data->search->results->work[0]->$field[0][0]; 
        }
      }
    } 
    
    if($this->type !== "search" && !empty($this->data)){
      $result = (string) $this->data->book->$field[0][0];

      if(empty($result)){
        $result = (string) $this->data->book->$field[0][0];
      }
    }

    // GoodReads supplies cover even if actual cover not available
      // Filter out 'no photo available' images from import
    if (empty($result) || ($field = 'image_url' && stripos($result, "nophoto") !== false)) {
      return false;
    }

    return $result;  
  }

  public function get_keywords($goodreadsId = null){
    $keywords = array();
    if($this->type === "search"){
      if(!empty($goodreadsId)){
        $this->queryArguments = array(
          'type' => 'goodreads',
        );
        $this->url = $this->getUrl($goodreadsId);
        $this->response = $this->fetch();
        $this->data = $this->getData('xml');
      } else {
        return $keywords;
      }
    }

    if(empty($this->data)){
      return $keywords;
    }

    $result = $this->data->book->popular_shelves->shelf;
    $count = count($result);

    if($count < 1){
      return $keywords;
    }
    $i = 0;
    $popularity = 0;

    do{
      $keyword = (string) $this->data->book->popular_shelves->shelf[$i]->attributes()->name;
      $popularity = (int) $this->data->book->popular_shelves->shelf[$i]->attributes()->count;
      $i++;

      if(!in_array($keyword, $this->filter_keywords)){
        $keywords[] = $keyword; 
      }

    } while(count($keywords) < 9 && $popularity > 4 && $i < $count);
  
    return $keywords;
  }

}