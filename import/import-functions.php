<?php
//======================================
//  WP ALL IMPORT FUNCTIONS FOR EVENTS
//======================================

/**
 * Get Post ID for an item with matching Title.
 * 
 * @param   string    $name       The title to look for
 * @param   string    $post_type  The post type to match
 * 
 * @return  int       ID of the first post matching title and type
 */
  function fbkGetPostIdByTitle($name, $post_type) {
    $post = get_page_by_title($name, OBJECT, $post_type);
    return $tribePost->ID;	
  }
/**
 * Calculates the different between two times.
 * 
 * Provides duration of the event
 * 
 * @param   string  $datestart  The start date/time of the event
 * @param   string  $dateend    The end date/time of the event
 * 
 * @return  int     _EventDuration
 */
  function fbkCalcDuration($datestart, $dateend) {
    if(!empty($datestart) && !empty($dateend) && $datestart != $dateend){
      $datetime1 = new DateTime($datestart);
      $datetime2 = new DateTime($dateend);
      $interval = $datetime1->diff($datetime2);
      $interval = $interval->format('%f');
      return $interval;
    }
    return;
  }
//======================================
//  WP ALL IMPORT FUNCTIONS FOR UTILITY
//  AND COLLECTION ITEMS
//======================================

  /**
   * Returns a formatted date.
   * 
   * If null, will provide current date in 'Y-m-d' format. If string 'datetime' is passed,
   * will provide current time in 'Y-m-d H:i:s' format. Otherwise, will format the value 
   * to match a prescribed value based on the date formats from the evergreen catalog.
   * 
   * @param   string    $value    a date to be formatted or provided
   * 
   * @return  string    a formatted datestring
   */
  function fbkDates ($value = null) {
    
    if (!$value) { // return today's date if no value
      return date('Y-m-d', time());
    }
    if($value == 'datetime'){
      return date('Y-m-d H:i:s', time()); 
    }
    
    if (strlen($value) === 6) { // format 6 digit date - i.e. Record Change Date
      $year = substr($value,0,2);
      $month = substr($value,2,2);
      $day = substr($value,4,2);
      if ($year < 30) {
        $year = "20".$year;
      }else {
        $year = "19".$year;
      }
      $datestring = $year . "-" . $month . "-" . $day;
      return $datestring;
    }
    if(preg_match("/^\d{4}-\d{2}-\d{2}T/", $value)){
      $datestring = substr($value,0,4) . substr($value,5,2) . substr($value,8,2);
    }
    if(preg_match("/^\d{2}\/\d{2}\/\d{4}/", $value)){
      $datestring = substr($value,6,4) . "-" . substr($value,0,2) . "-" . substr($value,3,2);
      return $datestring;
    }

    //format dateTime string i.e. Record edit Date
    $datestring = substr($value,0,4) . "-" . substr($value,4,2) . "-" . substr($value,6,2)." " . substr($value,8,2) . ":" .substr($value,10,2). ":" . "00";
    return $datestring;
  }

/**
 * Formats the parts of a title to provide one string.
 * 
 * @param   string    $nonSort    Usually "A", "The", etc.
 * @param   string    $title      Title provided by Evergreen catalog (shorttitle)
 * @param   string    $subTitle   Sub-title
 * @param   string    $partNumber 
 * @param   string    $partName
 * 
 * @return  string    concatenated/formatted string of the title parts
 */

  function fbkFormatParts ($nonSort=null, $title=null, $subTitle=null, $partNumber=null, $partName=null) {
    $string = "";
    if ($nonSort) {
      $string = trim($nonSort) . " ";
    }
    if ($title) {
      $string .= trim($title);
    } 
    if ($subTitle) {
      $string .= ": " . $subTitle;
    } 
    if ($partNumber && !$partName) {
        $string .= " (" . $partNumber . ")";
    }
    if ($partNumber && $partName) {
        $string .= " (" . $partNumber . " - " . $partName .")";
    }
    if ($partName && !$partNumber) {
      $string .=  " (" . $partName . ")";
    }
    return $string;
  }
  /**
   * Appends a comma to a value, if it exists.
   * 
   * @param   $string   $value    the value to add a comma to
   * 
   * @return  $string   concatenated value with appended comma
   */

  function fbkAddComma($value) {
    if($value){
      return $value . ",";
    }
    return;
  }
  function fbkManipulate2($value, $prepend, $type) {
    if(stripos($value, "Fictitious character") !== false){
      return $prepend ."ficticious character|";
    }
    if($type === "termsOfAddress"){
      return;
    }
    if($type === "date"){
      return;
    }
    if($value){
      return $prepend . $value . "|";
    }
    return;
  }
  /**
   * Manipulates a string by adding/concatenating provided values.
   * 
   * @param   string    $value    The value of the field
   * @param   string    $prepend  The value to be prepended
   * @param   string    $append   The value to be appended
   * 
   * @return  string    concatenated result
   */
  function fbkManipulate($value, $prepend = null, $append = null) {
    if($value){
      return $prepend . $value . $append;
    }
    return;
  }

  /**
   * Removes extraneous punctuation.
   * 
   * @param   string    $text   the value
   * 
   * @return  string    the trimmed value
   */
  function fbkRemoveExtraPunc ($text) {
    return trim($text, " \t\n\r\0\x0B-.,;:\/\\");
  }
  /**
   * Formats the main title of the collection item post.
   * 
   * This is to circumvent some instances where a main title field is left blank,
   * replacing the blank main title with the provided secondary title
   * 
   * @param   string    $titleNonsortFirst    non-sort article of first provided title
   * @param   string    $titleFirst           first provided short title
   * @param   string    $titleNonsortSecond   non-sort article of second provided title
   * @param   string    $titleSecond          second provided short title
   * 
   * @return  string    formatted title string
   */
  function fbkFormatTitle ($titleNonsortFirst, $titleFirst, $titleNonsortSecond, $titleSecond) {
    
    if (!$titleFirst && $titleSecond) {
      if($titleNonsortSecond){
        $titleNonsortSecond = $titleNonsortSecond . " ";
      }
      return $titleNonsortSecond . $titleSecond;
    }
    if($titleNonsortFirst){
      $titleNonsortFirst = $titleNonsortFirst . " ";
    }
      return $titleNonsortFirst . $titleFirst;
  }
/**
 * Implodes array of values with a slash.
 * 
 * @param   string|array  $value    the value(s) to separate with slashes
 * 
 * @return  string        string of values separated with a forward slash
 */
  function fbkConcatText($value) {
    $array=array();
    if (!is_array($value)) {
      $array = explode(',', $value);
    } else {
      $array = $value;
    }

    $stringValue = implode(" / ", $array);
    return " " . $stringValue;
  }

  /**
   * Creates a string formatted to represent a taxonomical hierarchy.
   * 
   * @param   string    $value    the field value
   * @param   string    $prepend  any parent terms (include a trailing '>')
   * @param   string    $append   any appending terms (include a leading '>')
   * 
   * @return  string    a hierarchical string with trailing |  
   */
  function fbkCreateHierarchy($value, $prepend = null, $append = null, $explode = null) {
    if($value){
      if($explode){
        return  $prepend . $value . $append . "|";
      }
      $subjectList = explode(",",$value);
      $subjectList = array_filter($subjectList);
      foreach ($subjectList as &$subject) {
          $subject=trim($subject);
      }
      $subjects = implode(">", $subjectList);
      return  $prepend . $subjects . $append . "|";
    }
    return;
  }
  
  /**
   * Processes array of strings to aid in comparing values.
   * 
   * References to this function were removed due to issues in bulk processing
   * in WP ALL IMPORT.
   * 
   * @param   array   $array    array of strings
   * 
   * @return  array   array of strings trimmed of extra punctuation and all lowercase
   */
  function fbkCleanArrayToCompare ($array){
    $clean = array_map("fbkRemoveExtraPunc", $array);
    $clean = array_map('strtolower', $clean);
    return array_values(array_unique($clean));
  }
  /**
   * Serializes values for storage.
   * 
   * @param   array|string  $value    an array or string of comma separated values
   * 
   * @return  string        serialization of the values
   */
  function fbkInputToArray($value){
    if(!is_array($value)){
      $value = explode(",",$value);
    }
    if(empty($value)){
      return;
    }
    return serialize($value);
  }
  /**
   * Returns a comma separated string of term ids.
   * 
   * @param   string    $names    a comma separated list of values to lookup
   * @param   string    $tax      the taxonomy to search
   * @param   string    $by       how to search (name, slug, etc)
   * @param   string    $return   what to return (if 'array' returns serialized array of ids, otherwise a string)
   * 
   * @return  string    comma separated or serialized string of term ids
   */
  
  function fbkTermToId($names, $tax, $by, $return=null){
    if(empty($names)){
      return;
    }
    $terms = explode(",", $names);
    foreach ($terms as &$val){
      $term = get_term_by($by, $val, $tax);
      if($term) {
        $val = $term->term_id;
      }else {
        $val = '';
      }
    }
    $terms = array_filter($terms);
    
    if($return = 'array' && !empty($terms)){
      return serialize($terms);
    }
    return implode(",",$terms);
  }

/**
 * Provides a serialized array of keywords for evergreen items.
 * 
 * @param   string    $marcGenre      a comma separated string of 'marc authority' genre terms
 * @param   string    $otherGenre     a comma separated string of other genre keywords
 * @param   string    $marcAudience   a comma separated string of 'marc authority' audience terms
 * @param   string    $otherAudience  a comma separated string of other audience keywords
 * @param   string    $topic          a comma separated string of topic keywords
 * @param   string    $ddc            the dewey decimal designation
 * @param   string    $lang           the item's language indicator (short code)
 * 
 * @return  string    serialized array of keywords to aid in matching genres and audiences, tagging for topics
 */
  function fbkTermKeyArray($marcGenre = null, $otherGenre = null, $marcAudience = null, $otherAudience = null, $topic = null, $ddc = null, $seriesLabel) {
    $termArray = array(
      'genres'       => explode(",",$marcGenre), 
      'audience'    => explode(",", $marcAudience),
      'topics'           => explode(",", $topic),
      'dewey'            => array(
                              'value' => $ddc,
      ),
      'genres_other'      => explode(",", $otherGenre),
      'audience_other'    => explode(",", $otherAudience)
    );
    
    $termArray['topics'][]=$seriesLabel;

    foreach($termArray as $key => &$array) {
      foreach($array as $k => &$v){
        if(stripos($v, 'etc.') !== false && strlen($v) < 7){
          unset($array[$k]);
        }
        $v = trim($v, " \t\n\r\0\x0B-.,;:\/\\");
      }
      $array = array_map('strtolower', $array);
      $array = array_values(array_unique($array));
    }

    if(!empty($ddc)){
      $dNum = trim(preg_replace('/[^ .0-9]/', '', $ddc));
      $termArray['dewey']['numeric'] = (float) trim(ltrim($dNum, '0'),".");
      $dText = preg_replace('/[\d\/\\.\[\]]/', ' ', $ddc);
      $termArray['dewey']['text'] = strtolower(trim($dText));
    }
    $termArray = array_map('array_filter', $termArray);
    return serialize($termArray);
  }

/**
 * Provides a serialized array of keywords for overdrive items.
 * 
 * @param   string    $subjects     a comma separated string of subject keywords
 * @param   string    $interest     a comma separated string of interest level indicators
 * @param   string    $keywords     a comma separated string of item keywords
 * @param   string    $lang         a comma separated string of language indicators
 * @param   string    $grade        a comma separated string of grade levels
 * @param   string    $atos         the ATOS score 
 * @param   string    $lexile       the LEXILE score
 * 
 * @return  string    serialized array of keywords to aid in matching genres and audiences, tagging for topics
 */
  function fbkOdTermKeyArray($subjects = null, $interest = null, $keywords = null, $grade = null, $atos =null, $lexile=null) {
    $termArray = array(
      'genres'        => array(),
      'audience'      => explode(",", $interest),
      'topics'        => explode(",", $keywords),
      'genres_other'  => explode(",",$subjects), 
      'audience_other'=> explode(",", $grade)
    );

    //$audiences = fbkCleanArrayToCompare(explode(",", $grade));
    if($interest){
      $interests = explode(",", $interest);
      foreach($interests as $level){
        $termArray['audience_other'][] = "Interest Level: " . $level;
      }
    }
    if($lexile){
      $termArray['audience_other'][] = $lexile . "L Lexile ";
    }
    if($atos){
      $termArray['audience_other'][] = "ATOS: " . $atos;
    }
    
    foreach($termArray as $key => &$array) {
      //$array = fbkCleanArrayToCompare($array);
      foreach($array as $k => &$v){
        $v = trim($v, " \t\n\r\0\x0B-.,;:\/\\");
      }
      $array = array_map('strtolower', $array);
      $array = array_values(array_unique($array));
    }    

    $termArray = array_map('array_filter', $termArray);
    return serialize($termArray);
  }


  /**
   * Attempts to retrieve to field key for a given ACF field.
   * 
   * @param   string    $fieldName    the name of the field
   * @param   int       $parentId     the post ID of the parent field
   * 
   * @return  string    the unique key for the specified ACF field
   */
  function getAcfFieldKey($fieldName, $parentId){
    if(empty($value)){
      return;
    }
    $acfField = new WP_Query( array(
      "post_type" => "acf-field",
      "s" => $fieldName,
      "post_status" => "publish",
      "post_parent" => $parentId,
    ));
    return $acfField->post->post_name;

  }

//=======================================
//  WP ALL IMPORT FUNCTIONS FOR OVERDRIVE
//  To retrieve records for import
//=======================================

/**
 * Provides file for WP All Import OverDrive API results.
 * 
 * Enter into the URL box for downloading import: [overdrive_library_download(9999)]
 * $new parameter default is false. If numeric, will download the "page" specified. Otherwise, if non-numeric &
 * non-false value is passed for $new, will attempt to calculate how many new items need to be imported based on
 * the total results from prior imports and the total items already imported. If that number is greater than the
 * $limit, an option will be saved in the database to preserve the offset for the next import.  
 * 
 * @param   int       $libraryId    the Overdrive Library ID to query
 * @param   int       $limit        the number of records to retrieve (max is 300)
 * @param   bool|int  $new          if numeric, the page to start import of new items ((page-1) * limit = offset)
 * 
 * @return  string    returns file location for result batch or null if all imported
 */
function overdrive_library_download( $libraryId, $limit = 200, $new = false ){
  if(empty($new) || $new == 'false'){
    $new = false;
  }
  if(empty($limit)){
    $limit = 200;
  }
  if($limit > 300){
    $limit = 300;
  }
  if($limit < 1){
    $limit = 10;
  }
  /**
   * Setup files for saving results. 
   */
  $uploads = wp_upload_dir();
  $filename = $uploads['basedir'] . '/wpallimport/files/' . strtok(basename($libraryId), "?") . '.json';

  if (file_exists($filename)){
    @unlink($filename);
  }

  /**
   * Try to retrieve results counter to continue from last import. 
   */

    if(false === ($results = get_option('overdrive_results_count_' . $libraryId)) || empty($results)){
      $results = array(
        'offset' => (int) 0,
        'total'  => (int) 0,
        'status'    => 'in progress'
      );
      $option = $results;
    } elseif (ceil($results['offset']/$limit) == ceil($results['total']/$limit) && $new === false){
      $results['status'] = 'completed';
      update_option('overdrive_results_count_' . $libraryId, $results, false);
      return null;
    } 

    $offset = $results['offset'];
    $total = $results['total'];
  /**
   * @var   array   $products   contains data for all products imported.
   * @var   array   $diff       contains ids for all existing products that have not yet been imported in this round.
   */
  $products = array();

  $overDriveApi = new Fontana_Overdrive_Api();
  
  /**
   * @var  array   $imported   contains ids for all products imported. 
   */
   $imported = array();
  

  //query for Overdrive products request.
  if(!$new){
    $query = array(
      'limit'     => $limit,
      'offset'    => $offset,
      'sort'      => 'dateadded:asc',
    );
  } elseif (is_numeric($new)){
    // if a numeric number is passed for $new, we'll page the results based on $limit
    $offset = ($new-1)*$limit;
    $query = array(
      'limit'     => $limit,
      'offset'    => $offset,
      'sort'      => 'dateadded:desc',
    );
  } else {
    //set default check to max
    $new_items_to_import = $limit;

    if(array_key_exists('items_imported', $results)){
      //if we have an item count from database, calculate the number of new items that need to be imported.
      $new_items_to_import = $total - $results['items_imported']; 
    }

    if(false === ($offset = get_option('new_overdrive_results_count_' . $libraryId))){
      $offset = 0;
    }
    
    if ($new_items_to_import <= $limit){
      delete_option('new_overdrive_results_count_' . $libraryId);
    } 
    
    $query = array(
      'limit'     => $limit,
      'offset'    => $offset,
      'sort'      => 'dateadded:desc',
    );
  }


  $response = $overDriveApi->getProducts($libraryId, $query);
  if(!isset($response['totalItems'])){
    return null;
  }


  $results['total'] = $response['totalItems'];
  update_option('overdrive_results_count_' . $libraryId, $results, false);


  foreach($response['products'] as $product){
    $imported[] = $product['id'];
  }

  $metadata = array();

  // Chunk imported ids into groups of 25, max amount for bulk meta requests.
  $chunkIds = array_chunk($imported, 25);
  foreach($chunkIds as $key => $val){
    $metaRecords = $overDriveApi->getBulkMeta($libraryId, $val);
    $metadata = array_merge($metadata, $metaRecords['metadata']);
  }

  //Merge records into a single record.
  $products = fbkMergeRecords($metadata, $response['products']);


  if(!empty($products) && is_array($products)){
    file_put_contents($filename, json_encode($products));
    return str_replace($uploads['basedir'], $uploads['baseurl'], $filename); 
  }
}
/**
 * Combines record arrays from 2 results together, merging based on record id.
 * 
 * @param   array   $metaRecords    results from OverDrive metarecord query
 * @param   array   $products       results from OverDrive product query
 * 
 * @return  array   $array          combined records array
 */

function fbkMergeRecords($metaRecords, $products){
  $combined = array();
  foreach($products as $key => $val){
    $combined[$val['id']] = $val; 
  }
  
  foreach($metaRecords as $key => $val){
    $combined[$val['id']] += $val;
  }

  $array = array();
  foreach($combined as $id => $record){
    $array[] = $record;
  }

  return $array;
 }

  