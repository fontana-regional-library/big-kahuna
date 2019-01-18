<?php
/**
 * Parent class to provide basic framework for retrieving data from external APIs.
 */
class Fontana_Response {
  /**
   * Public Variables used in fetching or passing data from external apis.
   * 
   * @access  public
   * @var   array         $args         arguments passed for wp_remote_get
   * @var   array|object  $data         an array or object representing the API results to our query
   * @var   object        $response     the response to wp_remote_get
   * @var   int           $responseCode the response code from our wp_remote_get
   * @var   string        $url          the url to fetch data from
   * 
   */
 
  public $args = array();
  public $data;
  public $response;
  public $responseCode;
  public $url;

  /**
   * Initialize the class and set its properties.
   *
   *  Code	Message	Description
   *   200	  OK	                    The data was received and the operation was performed.
   *   201	  Created	                The data was received and a new resource was created. The response needs to return the data in the payload.
   *   204	  No content	            The operation was successful but no data is returned in the response body. This is useful when deleting a resource.
   *   301	  Moved permanently	      This and all the future requests should be redirected to a new URL.
   *   302	  Moved temporarily	      The resource moved temporarily to another URL.
   *   400	  Bad request	            The server cannot accept the request because something is wrong with the client or the request that it sent.
   *   403	  Forbidden	              The client is not allowed to use the resource.
   *   404	  Not found	              The computer is not able to find the resource.
   *   405	  Method not allowed	    For example, when sending a DELETE request to a server that doesn't support the method.
   *   500	  Internal server error	  Service unavailable due to error on the server side.
   * 
   * @param      string   $url    url to be fetched
   * @param      array    $args   additional aruguments to pass to wp_remote_get
   */

  public function __construct($url, $args = array()) {
    $this->url = $url;
    $this->args = $args;
    $this->response = $this->fetch();
  }

  /**
   * Fetches and returns response from provided url.
   */

  public function fetch () {
    if(empty($this->args)){
      $response = wp_remote_get($this->url);
    } else{
      $response = wp_remote_get($this->url, $this->args);
    }

    if ( is_wp_error($response) ) {
      $this->responseCode = '800';
      return null; 
    }
    $this->responseCode = wp_remote_retrieve_response_code( $response );
    return $response;
 }
   /**
    * Get the body of the response and return for use as array or object based on response type.
    *
    * Default return is json array
    *
    * @param    string    $type   the type of response provided by the API e.g. xml, json, json-obj etc.
    */
  public function getData($type) {
    if($this->responseCode >= 400 || $this->response === null){
      return array();
    }
    if ($type === 'xml'){
      return simplexml_load_string(wp_remote_retrieve_body( $this->response ));
    }
    if ($type === 'json-obj'){
      return json_decode(wp_remote_retrieve_body( $this->response ));
    }
    return json_decode(wp_remote_retrieve_body( $this->response ), true); 
  }

  /**
   * Returns specified headers from the response.
   * 
   * if array of header values is passed, an array of values is returned.
   * 
   * @param   array|string    $headers  headers requested from the response
   */
  public function getHeaders($headers){
    if(is_array($headers)){
      $value = array();
      foreach($headers as $header) {
        $value[$header] = wp_remote_retrieve_header( $this->response, $header );
      }
      return $value;
    }
    return wp_remote_retrieve_header( $this->response, $headers);
  }

  /**
   * Returns a string of query parameters.
   * 
   * @param   array   $query_params   associative array of key/values
   * 
   * @return  string  string of query params separated by & and pairs formatted as key=value
   */
  public function stringify_url_params($query_params){
    $parameters = "";
    foreach($query_params as $key => $value) {
      $parameters .= "&" . $key . "=" . $value;
    }
    return $parameters;
  }

}