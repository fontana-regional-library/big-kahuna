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
class Fontana_Import_Collection_Item {
  /**
   * Variables for processing item information.
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
  protected $acf_term_fields = array(
    'genre'             => 'genres',
    'target_readership' => 'audience'  
  );
  public $catalog;
  public $form;
  public $identifiers = array();
  public $is_update;
  protected $item_info = array();
  protected $item_record;
  public $item_type;
  public $node;
  protected $periodical_keywords = array(
    "periodical", "periodicals",
    "newspaper", "newspapers",
    "magazine", "magazines",
    "catalog", "catalogs", "adult magazine"
  );
  public $post_id;
  protected $post_meta;
  public $report = array(
    'cover' => false,
    'audience' => false,
    'genres'  => false
  );
  public $record_id;
  protected $subtopics = array(
    "Reading Level" => 'audience',
    "Sub-Genre" => 'genres',
  );
  public $set_terms = array(
    'audience' => array(),
    'genres'  =>  array(),
    'topics'  => array(),
    'location'  =>  array(),
    'shelf'     =>  array(),
  );
  public $term_keys = array();
  protected $term_list = array();
  public $title;

  
  

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($post_id, $is_update, $xml_node = null, $catalog = null) {
      $this->post_id = $post_id;
      $this->is_update = $is_update;
      $this->node = $xml_node;
      $this->catalog = $catalog;
      $this->init();
      $this->start_processing();
  }
/**
   * Sets some class variables to assist with classifying collection item.
   * 
   * Update the term list at the Fontana Settings page to refresh cached term list
   * 
   * @see class-fontana-settings.php collectionTermData().
   */

  public function init(){
    $this->post_meta = get_post_meta($this->post_id);

    if(is_array($this->post_meta) && array_key_exists('alternative_titles_0_alternative_title', $this->post_meta) && !empty($this->post_meta['alternative_titles_0_alternative_title'][0])){
      $this->title = $this->post_meta['alternative_titles_0_alternative_title'][0];
    } else {
      $this->title = get_the_title($this->post_id);
    } 
    
    if(is_array($this->post_meta) && array_key_exists('record_identifier', $this->post_meta) && !empty($this->post_meta['record_identifier'][0])){
      $this->record_id =  $this->post_meta['record_identifier'][0];
    }

    if(is_array($this->post_meta) &&  array_key_exists('term_keys', $this->post_meta) && !empty($this->post_meta['term_keys'][0])){
      $this->term_keys = maybe_unserialize($this->post_meta['term_keys'][0]);
    }

    if(is_array($this->post_meta) && array_key_exists('_thumbnail_id', $this->post_meta) && !empty($this->post_meta['_thumbnail_id'][0])){
      $this->report['cover'] = (int) $this->post_meta['_thumbnail_id'][0];
    }

    if(empty($this->catalog) && is_array($this->post_meta) &&  array_key_exists('collection', $this->post_meta)){
      $this->catalog = $this->post_meta['collection'][0];
    }
    if(is_array($this->post_meta) && array_key_exists('item_type', $this->post_meta)){
      $this->item_type = explode(",", $this->post_meta['item_type'][0]);
    }
    if(is_array($this->post_meta) && array_key_exists('form', $this->post_meta)){
      $this->form = explode(",", $this->post_meta['form'][0]);
    }
    if(is_array($this->post_meta) && array_key_exists('genre', $this->post_meta) && !empty($this->post_meta['genre'][0])){
      $acfGenre = maybe_unserialize($this->post_meta['genre'][0]);

      if(is_array($acfGenre) && !empty(array_filter($acfGenre))){
        $this->report['genres'] = $acfGenre;

        foreach($acfGenre as $tid){
          $this->set_terms['genres'][] = (int) $tid;
        }
      }
    } 
    
    if(is_array($this->post_meta) && array_key_exists('target_readership', $this->post_meta)){
      $acfAudience = maybe_unserialize($this->post_meta['target_readership'][0]);

      if(is_array($acfAudience) && !empty(array_filter($acfAudience))){
        $this->report['audience'] = $acfAudience;

        foreach($acfAudience as $tid){
          $this->set_terms['audience'][] = (int) $tid;
        }
      }
    } 

    

    if(false === ($term_list = get_option('collection_term_lists'))){
      do_action('fbk_update_collection_term_lists');
      $term_list = get_option('collection_term_lists');
    }

    $ids = $this->get_acf_repeater_values('identifiers', 'identifier');
    $types = $this->get_acf_repeater_values('identifiers', 'type');
    $identifiers = array_combine ($ids, $types);

    foreach($identifiers as $num => $string){

      if($string === 'isbn'){
        $isbn = preg_replace('/\D/', '', $num);

        if (strlen($isbn) === 9) {
          $isbn = $isbn . "X";
        }

        unset($identifiers[$num]);
        $identifiers[$isbn] = $string;
      }
    }

    $this->identifiers = $identifiers;

    $this->term_list = $term_list;
    $magazine = array_search('magazines', $this->term_list['genres_terms']);

    if(in_array($magazine, $this->set_terms['genres']) || in_array('journal', $this->term_keys['genres'])  ||
      (bool) array_intersect($this->periodical_keywords, $this->term_keys['topics']) ||
      (bool) array_intersect($this->periodical_keywords, $this->term_keys['genres_other'])) {
      $this->item_type = array("periodical");
    }
  }

  /**
   * POST IMPORT UTILITY FUNCTIONS
   */
  public function start_processing(){
    if( $this->is_update === false || (($this->is_update === 'failed' || $this->is_update === 'check') && in_array(false, $this->report)) ){
      $info = $this->get_external_info();

      if(!empty($info)){
        $this->process_keyword_array($info);
      }

      $this->add_classifiers();
    }

    if(!is_int($this->report['cover']) && $this->report['cover'] !== false){
      $this->save_thumbnail_image($this->report['cover'], $this->title, $this->post_id);
    }
  }
/**
 * Adds keywords from meta and then sets taxonomy terms.
 */
  public function add_classifiers(){
    $this->add_exact_term();
    $this->find_terms_by_keyword();

    foreach($this->subtopics as $parent => $tax) {
      $this->add_subtopics($parent, $tax);
    }

    $this->set_terms['genres'] = $this->cleanup_genres();

    if(empty($this->set_terms['audience'])){
      $adult = array_search('adult', $this->term_list['audience_terms']);
      $this->set_terms['audience'][] = (int) $adult;
      $this->report['check audience'] = false;
    }

    foreach($this->set_terms as $taxonomy => $tids){
      if(!empty($tids)){
        if($taxonomy === 'genres' || $taxonomy === 'topics'){
        $this->report[$taxonomy] = wp_set_object_terms($this->post_id, $tids, $taxonomy, true);
        continue;
        }
        $this->report[$taxonomy] = wp_set_object_terms($this->post_id, $tids, $taxonomy, false);
      }
    }
    
    foreach($this->acf_term_fields as $field => $tax){
      if(!empty($this->set_terms[$tax])){
        update_field($field, $this->set_terms[$tax], $this->post_id);
      }
    }    
  }

  public function cleanup_genres(){ 
    $nonfiction = array_search('nonfiction',  $this->term_list['genres_terms']);

    if(empty($this->set_terms['genres'])){

      if(is_array($this->term_keys) && array_key_exists('dewey', $this->term_keys) && array_key_exists('numeric', $this->term_keys['dewey'])){
        $dewey = (float) $this->term_keys['dewey']['numeric'];

        if($dewey < 742 && $dewey > 739){
          return (int) array_search('graphic novel',  $this->term_list['genres_terms']);
        } elseif(($dewey > 919 && $dewey < 921) || ($dewey > 758 && $dewey < 760) || ($dewey > 708 && $dewey < 710) || ($dewey > 608 && $dewey < 610) || ($dewey > 508 && $dewey < 510) || ($dewey > 408 && $dewey < 410) || ($dewey > 269 && $dewey < 271) || ($dewey > 108 && $dewey < 110)){
          return (int) array_search('biography',  $this->term_list['genres_terms']);
        } elseif( ($dewey > 810 && $dewey < 812) || ($dewey > 820 && $dewey < 822) || ($dewey > 830 && $dewey < 832) || ($dewey > 840 && $dewey < 842) || ($dewey > 850 && $dewey < 852) || ($dewey > 860 && $dewey < 862) || ($dewey > 870 && $dewey < 875)  || ($dewey > 880 && $dewey < 885)){
          return (int) array_search('poetry',  $this->term_list['genres_terms']);
        } elseif($dewey < 770 || $dewey >= 900){
          return (int) $nonfiction;
        }
      }
    }

    $puzzle = array_search('puzzle', $this->term_list['genres_terms']);
    if(in_array("puzzle", $this->form) || (in_array('three dimensional object', $this->item_type)  && in_array($puzzle, $this->set_terms['genres']))){
      return (int) $puzzle;
    }

    $magazine = array_search('magazines',$this->term_list['genres_terms']);
    if (in_array($magazine, $this->set_terms['genres'])){
      return (int) $magazine;
    }


    $video = array_search('video', $this->term_list['genres_terms']);
    
    //$nonFicCheck = array_intersect($this->term_list['children_list'][$nonfiction], $this->set_terms['genres']);
    $nonFicCheck = array();
    foreach($this->term_list['children_list'][$nonfiction] as $nf_id){
      if(in_array($nf_id, $this->set_terms['genres'])){
        $nonFicCheck[] = (int) $nf_id;
      }
    }

    if( in_array('moving image', $this->item_type) || in_array('video', $this->item_type) ){
      $video_terms = array((int) $video);
      //$video_terms = array_intersect($this->term_list['children_list'][$video], $this->set_terms['genres']);
      foreach($this->term_list['children_list'][$video] as $vid_id){
        if(in_array($vid_id, $this->set_terms['genres']) && !in_array($vid_id, $video_terms)){
          $video_terms[] = (int) $vid_id;
        }
      }
      $documentary = array_search('documentary',  $this->term_list['genres_terms']);
      if(!empty($nonFicCheck)){
        $video_terms[] = (int) $documentary;
      }
      return $video_terms;
    }
    
    
    //$term_list = array_diff($term_list, $this->term_list['children_list'][$video]);
    $music = array_search('music', $this->term_list['genres_terms']);
    $fiction = array_search('fiction',  $this->term_list['genres_terms']);

    //$fictionCheck = array_intersect($this->term_list['children_list'][$fiction], $genre_list);
    $fictionCheck =array();
    foreach($this->term_list['children_list'][$fiction] as $fic_id){
      if(in_array($fic_id, $this->set_terms['genres'])){
        $fictionCheck[] = (int) $fic_id;
      }
    }

    $genres = array();
    foreach($this->set_terms['genres'] as $term_id){
      if($term_id != $puzzle && !in_array($term_id, $this->term_list['children_list'][$video])){
        if(in_array('text', $this->item_type) && $term_id == $music){
          continue;
        }
        $genres[] = (int) $term_id;
      }
    }

    $tids = array();
    if(count($fictionCheck) > count($nonFicCheck)){
      //error_log("classifying as fiction....");
      foreach($genres as $genre){
        if(!in_array($genre, $this->term_list['children_list'][$nonfiction])){
          $tids[] = (int) $genre;
        }
      }
      return $tids;
    }

    foreach($genres as $genre){
      if(!in_array($genre, $this->term_list['children_list'][$fiction])){
        $tids[] = (int) $genre;
      }
    }

    return $tids;
  }
/**
 * Checks array of values and adds to list of terms to be added to the post.
 * 
 * @param   string  $taxonomy   name of taxonomy for which terms should be added
 * @param   array   $values     array of terms ids
 */
  public function add_to_term_list($taxonomy, $values){
    if( is_array($values) && !empty($values) && array_key_exists($taxonomy, $values)){
      foreach($values[$taxonomy] as $value){
        if(!in_array($value, $this->set_terms[$taxonomy]) && !empty($value) && is_numeric($value)){
          $this->set_terms[$taxonomy][] = (int) $value;
        }
      }
    }
  }
  /**
   * Adds exact terms to terms list.
   * 
   * @param   string    $tax    the taxonomy name to check
   * 
   * @return  bool      true if match was found, false if not
   */
  public function add_exact_term() {
    $nonFiction = array_search('nonfiction', $this->term_list['genres_terms']);
    $term_list = $this->term_keys;
    
    foreach($term_list as $sub => $terms){
      foreach($terms as $key => $term){
        $genreMatch = array_search($term, $this->term_list['genres_terms']);
        $audienceMatch = array_search($term, $this->term_list['audience_terms']);

        if(!empty($genreMatch)){
          if(!in_array($genreMatch, $this->set_terms['genres'])){
            $this->set_terms['genres'][] = (int) $genreMatch; 
          }
          unset($this->term_keys[$sub][$key]);
        }

        if(!empty($audienceMatch)){
          if(!in_array($audienceMatch, $this->set_terms['audience'])){
            $this->set_terms['audience'][] = (int) $audienceMatch;
          }
          unset($this->term_keys[$sub][$key]);
        }
      }
    }
  }  

  /**
   * Finds matching terms by keywords.
   */
  public function find_terms_by_keyword(){
    $audienceCheck = $this->set_terms_by_keyword('audience');
    $genreCheck = $this->set_terms_by_keyword('genres');
    

    if(array_key_exists('dewey', $this->term_keys) && array_key_exists('text', $this->term_keys['dewey']) && (!array_key_exists('numeric', $this->term_keys['dewey']) || empty($this->term_keys['dewey']['numeric']) )){
      $deweyParent = array_search('Dewey Key', $this->term_list['parent_keywords']);
      $this->set_terms_by_keyword('dewey', null, $deweyParent);
    }

    if(empty($this->set_terms['audience']) || empty($this->set_terms['genres'])){
      $marcParent = array_search('Marc Terms', $this->term_list['parent_keywords']);
      $this->set_terms_by_keyword('topics', $marcParent);
    }

    if(!empty($this->item_type)){
      $relatedItemTypeTerms = $this->find_related_terms_by_keyword($this->item_type);
      $this->add_to_term_list('genres', $relatedItemTypeTerms);
      if(empty($this->set_terms['audience'])){
        $this->add_to_term_list('audience', $relatedItemTypeTerms);
      }
    }
  }
/**
 * Sets terms by keywords
 *
 * @param string  $taxonomy    the term_keys main-key/'taxonomy' to check
 * @param int     $exclude     the term id of the keyword tree to exclude
 * @param int     $parent      the parent term ID to search
 */
  public function set_terms_by_keyword($taxonomy, $exclude = null, $parent = null){
    $term_list = array();
  
    foreach($this->term_keys as $tax => $keywords){
      if(stripos($tax, $taxonomy) !== false && !empty($keywords)){
        $term_list = $this->find_related_terms_by_keyword($keywords, $exclude, $parent);
      }
    }
    if($taxonomy === 'genres' || $taxonomy === 'audience'){
      $this->add_to_term_list('audience', $term_list);
      $this->add_to_term_list('genres', $term_list);
    } else {
      if(empty($this->set_terms['audience'])){
        $this->add_to_term_list('audience', $term_list);
      }
      $this->add_to_term_list('genres', $term_list);
    }
  }
  
/**
 * Gets a term/terms based on term meta.
 * 
 * This function will retrieves a term based on value of meta data 
 * (e.g. Retrieves Shelf Location Term in Wordpress based on the "shelf_location_id" term meta from an Evergreen Record)
 *
 *
 * @param string  $keyword      the keyword to check
 * @param int     $exclude      the term id of the keyword tree to exclude
 * @param int     $parent       the parent term ID to search
 * 
 * @return array  $related     the Results / array of term ids of related terms
 * 
 */

  public function find_related_terms_by_keyword($keyword, $exclude=null, $parent=null){
    $related_terms = array();
    if(empty($keyword)){
      return $related_terms;
    }

    $args = array(
      'name'  => $keyword,
      'taxonomy'  => 'keyword',
      'hide_empty'=> false,
      'fields'  => 'ids',
      'childless' => true,
    );

    if(!empty($exclude)){
      $args['exclude_tree'] = (int) $exclude;
    }
    if(!empty($parent)){
      $args['parents'] = (int) $parent;
    }
    $terms = get_terms($args);

    if(is_array($terms)){
      foreach($terms as $term){
        $term_meta = get_term_meta($term);
        
        if(is_array($term_meta) && array_key_exists('related_genres', $term_meta)){
          $tids = maybe_unserialize($term_meta['related_genres'][0]);

          if(is_array($tids) && !empty($tids)){

            if(!array_key_exists('genres', $related_terms)){
              $related_terms['genres'] = $tids;
            }

            $related_terms['genres'] += $tids;
          }
        }

        if(is_array($term_meta) && array_key_exists('related_audience', $term_meta)){
          $tids = maybe_unserialize($term_meta['related_audience'][0]);

          if(is_array($tids) && !empty($tids)){

            if(!array_key_exists('audience', $related_terms)){
              $related_terms['audience'] = $tids;
            }

            $related_terms['audience'] += $tids;
          }
        }
      } 
    } 
    return $related_terms;
  }

/**
 * Gets a term/terms based on term meta.
 * 
 * This function will retrieves a term based on value of meta data 
 * (e.g. Retrieves Shelf Location Term in Wordpress based on the "shelf_location_id" term meta from an Evergreen Record)
 *
 *
 * @param string  $value       the meta_value to search for
 * @param string  $field       the meta_key to search for
 * @param string  $tax         the taxonomy to search in
 * 
 * @return object $theTerm     the Results / array of terms 
 * 
 */
  public function get_term_from_meta ($value, $field, $tax) {
    if($value) {
      $query = array(
        'hide_empty'  => 0,
        'taxonomy'    => $tax,
        'meta_query' => array(
          array(
          'key'    => $field,
          'value'  => $value,
          ),
        ),
      );

      $theTerm = get_terms($query);
      return $theTerm;
    }
    return '';
  }


/**
 * Preserves / assigns alternative or secondary audience and genre indicators via Topics Taxonomy
 * 
 * This function will automatically set a parent taxonomy term and child term (if that term doesn't exist in audience or genre terms).
 *
 *
 * @param string|int  $parent      The Parent Term to set
 * @param string      $tax         The field/sub-array to be checked in the term_keys array
 * 
 */
  public function add_subtopics($parent, $tax, $values = null){
    $container_name = $tax . "_other";
    
    $subTopics = array();

    if(array_key_exists($tax, $this->term_keys) && !empty($this->term_keys[$tax])){ 
      $subTopics += $this->term_keys[$tax];
    }

    if(array_key_exists($container_name, $this->term_keys) && !empty($this->term_keys[$container_name])){
      $subTopics += $this->term_keys[$container_name];
    }

    if(!empty($values)){
      $subTopics += $values;
    }

    $subTopics = array_map('strtolower', $subTopics);
    //$subTopics = array_unique($subTopics);

    //$subTopics = array_diff($subTopics, $this->term_list['genres_terms'], $this->term_list['audience_terms']);
    $terms = array();
    foreach($subTopics as $topic){
      if(!in_array($topic, $this->term_list['genres_terms']) && !in_array($topic, $this->term_list['audience_terms']) && !in_array($topic, $terms) && !empty($topic)){
        $terms[] = $topic;
      }
    }

    $subTopics = $this->insert_hierarchical_term($parent, 'topics', $terms);
    $this->set_terms['topics'] += $subTopics;
  }
/**
 * Retrieves or adds a term with hierarchy.
 * 
 * @param   string|int    $parent       the parent term name or id
 * @param   string        $tax          the taxonomy
 * @param   string|array        $term         the term to check or add
 * @param   int           $grandparent  the id of the parents parent term
 */
  public function insert_hierarchical_term($parent, $tax, $term, $grandparent = 0) {
    $term_ids = array();

    if(is_numeric($parent) || $parent === 0){
      $parentId = (int) $parent;
    } else {
      $parentTerm = term_exists($parent, $tax, $grandparent);
      
      if(!$parentTerm){
        $parentTerm = wp_insert_term($parent, $tax, array('parent' => $grandparent) );
      }  

      $parentId = (int) $parentTerm['term_id'];
    }


    if(!is_array($term) && !empty($term)){
      $theTerm = term_exists($term, $tax, $parentId);

      if(!$theTerm){
        $theTerm = wp_insert_term($term, $tax, array('parent' => $parentId) );
      }

      if(!is_wp_error($theTerm) || !empty($theTerm)){
        $term_ids[] = (int) $theTerm['term_id'];
      }

    } elseif(!empty($term)) {

      foreach($term as $key => $t){
        $theTerm = term_exists($t, $tax, $parentId);

        if(!$theTerm){
          $theTerm = wp_insert_term($t, $tax, array('parent' => $parentId) );
        }

        if(!is_wp_error($theTerm) || !empty($theTerm)){
          $term_ids[] = (int) $theTerm['term_id'];
        }
      }
    }
    return $term_ids;
  }
/**
 * Preps nested terms and adds as hierarchical.
 * 
 * @param   string      $tax          the taxonomy
 * @param   array       $values       array of keywords to be added
 * @param   int|string  $grandparent  the grandparent term
 * 
 * @return  array   array of added term ids
 */
  public function add_nested_terms($tax, $values, $grandparent = null){
    $term_ids = array();

    if(!empty($grandparent) && is_int($grandparent)){
      $grandparentId = $grandparent;
    } else {
      
      $gParents = explode(">", $grandparent);
      $grandparentId = (int) 0;
      
      foreach($gParents as $key => $gPa) {
        $term = $this->insert_hierarchical_term($grandparentId, $tax, $gPa);
        if(!empty($term)){
          $grandparentId = $term;
        }
      }
    }

    foreach($values as $value){
      if(strpos($value,":::") === false){
        $terms = $this->insert_hierarchical_term($grandparentId, 'topics', $value);
        if(!empty($terms)){
          $term_ids[] = (int) $terms;
        }
        continue;
      }

      $vals = explode(":::", $value);
      $parent = $grandparentId;
      foreach($vals as $key => $val){
        if(!empty($val)){
          $parent = $this->insert_hierarchical_term($parent, 'topics', $val);
          $term_ids[] = (int) $parent;
        }
      }
    }
    return $term_ids;
  }
  /**
   * Gets the post meta for repeating ACF fields.
   * 
   * @param   string    $parentField      Name of ACF 'parent' field
   * @param   string    $field            Name of ACF subfield to retrieve
   * 
   * @return  array     array of values for the subfield.
   */
  protected function get_acf_repeater_values($parentField, $field){
    $valueArray = array();
    $numberOfEntries = 0;

    if(is_array($this->post_meta) && array_key_exists($parentField, $this->post_meta)){
      $numberOfEntries = (int) $this->post_meta[$parentField][0];
    }
  
    for($i=0;$i<$numberOfEntries;$i++){
      $meta_key = $parentField ."_". $i . "_" . $field;
      $valueArray[] = $this->post_meta[$meta_key][0];
    }
    return $valueArray;
  }
/**
 * Removes all rows for a repeating ACF field.
 * 
 * @param   string    $parentField    the name of the parent field (where row count is stored)
 */
  public function delete_acf_rows($parentField){
    $totalRows = 0;
    if(is_array($this->post_meta) && array_key_exists($parentField, $this->post_meta)){
      $totalRows = $this->post_meta[$parentField][0];
    }
    for($row=$totalRows;$row>0;$row--){
      delete_row($parentField, $row, $this->post_id);
    }
  }
/**
 * Adds data from external sources to the item record for processing.
 * 
 * Data from external sources is added to an array - if it should be merged with the
 * term_keys variable, then formatting/naming should match $this->term_keys:
 *    array('genres' => array(keywords), 
 *          'genres_other' => array(keywords),
 *          'audience' => array(),
 *          'audience_other' => array(),
 *          'topics' =>array());
 * 
 * @see import-functions.php fbkTermKeyArray() & fbkOdTermKeyArray()
 * 
 * If keywords from external resources should be added as 'SubTopics', the key should
 * be one of $this->sub_topics and value should be array of terms to add (other general
 * topics should be added in array value of 'subTopics' key)
 * 
 * @param   array   $values     an associative/multidimensional array of info.
 */
  public function process_keyword_array($values){
    $term_key_list = $this->term_keys;

    foreach($term_key_list as $sub => $keywords){
      if(is_array($term_key_list) && array_key_exists($sub, $values) && !empty($values[$sub])){
        foreach($values[$sub] as $keyword){
          if(!empty($keyword)){
            $this->term_keys[$sub][] = strtolower($keyword);
          }
        }
      }
    }

    foreach($this->subtopics as $parent => $tax){
      if(is_array($values) && array_key_exists($parent, $values) && !empty($values[$parent])){
        $terms = $this->insert_hierarchical_term($parent, 'topics', $values[$parent]); 
        $this->set_terms['topics'] += $terms;
      }
    }

    if(is_array($values) && array_key_exists('subTopics', $values)){
      foreach($values['subTopics'] as $subTopics){
        $terms = $this->add_nested_terms("topics", $array['values'], $array['parent']);
        if(!empty($terms)){
          $this->set_terms['topics'] += $terms;
        } 
      }
    }
  }
  /**
   * Creates array of info from external resources.
   * 
   * @see $this->process_keyword_array()
   * 
   * @return    array   $info     array of keywords/terms from external sources.
   */
  protected function get_external_info(){
    $info = array();
    
    // If there is no thumbnail already available and this item is a Video, check the OmDb API for coverUrl
    if(in_array('moving image', $this->item_type) || in_array('video', $this->item_type) ){
      $data = $this->check_omdb();
      $info = $data['keywords'];
    }

    // If this item is a book, check the Open Library API and GoodReads API
    if( in_array('text', $this->item_type) || in_array('sound recording-nonmusical', $this->item_type) || in_array('ebook', $this->item_type) || in_array('audiobook', $this->item_type) ){
      $info = $this->check_open_library();
      $goodreads = $this->check_goodreads();
      if(!empty($info) && !empty($goodreads)){
        $info['topics'] += $goodreads['keywords']['topics'];
      } elseif(!empty($goodreads)){
        $info = $goodreads['keywords'];
      }
    }

    //Save additional meta info, if available
    if(!empty($this->item_info)){
      foreach($this->item_info as $field => $value){
        if(!empty($value)){
        update_field($field, $value, $this->post_id);
        }
      }
    }
    return $info;
  }

  /**
   * Check the OpenLibrary API for cover image and additional IDs
   * 
   * Adds external identifiers, if available, to $this->item_info (goodreads ID, google books ID, etc.)
   * and retrieves cover url if available.
   * @see class-fontana-openlibrary.php
   * 
   * @return  array   array of keywords/data to add to record.
   */
  protected function check_open_library() {
    $data = array();
    if(!empty($this->identifiers)){
      $openLibraryRecord = new Fontana_Open_Library_API($this->identifiers);
      if($this->report['cover'] === false){
        $this->report['cover'] = $openLibraryRecord->get_cover_image();
      }
      $item_info = $openLibraryRecord->get_info();

      foreach($item_info as $key => $id){
        if(!empty($id)){
          $this->item_info[$key] = $id;
        }
      }
      $data = $openLibraryRecord->get_keywords();
    }
    return $data;
  }

  /**
   * Check the GoodReads API for other info as needed.
   * 
   * If goodreads ID is available, pulls item record from ID, otherwise - tries to search
   * by ISBN or by title + author as last resort.
   * retrieves and add cover-image url if available; attempts to retrieve goodreads id
   * and add to $this->item_info if needed/available. Returns keywords if available.
   * @see class-fontana-goodreads.php
   * 
   * @return  array   array of keywords/data to add to record.
   */
  protected function check_goodreads() {
    $data = array(
      'keywords'  => array(),
    );

    //if item has a goodreads id, retrieve goodreads record by that ID
    if(isset($this->item_info['goodreads_id']) && !empty($this->item_info['goodreads_id'])) {
      $goodreadsRecord = new Fontana_Goodreads_API ($this->item_info['goodreads_id'], array('type'=>'goodreads'));

      if($this->report['cover'] === false){
        $this->report['cover'] = $goodreadsRecord->get_info('image_url');
      }
      $rating = $goodreadsRecord->get_info('average_rating');
      $keywords = $goodreadsRecord->get_keywords($this->item_info['goodreads_id']);
      if(!empty($rating)){
        $this->item_info['reader_rating'] = $rating;
      }
      if(!empty($keywords)){
        $data['keywords']['topics'] = $keywords;
      }
      return $data;
    }

    //ISBN search is option for goodreads
    //attempt to search goodreads by item's isbn, if available
    if(in_array('isbn', $this->identifiers)) { 
      $args = array(
        'type' => 'isbn',
      );

      $goodreadsRecord = new Fontana_Goodreads_API($this->identifiers, $args);

      if($this->report['cover'] === false){
        $this->report['cover'] = $goodreadsRecord->get_info('image_url');
      }

      $gr_id = $goodreadsRecord->get_info('id');
      if(!empty($gr_id)){
        $this->item_info['goodreads_id'] = $gr_id;
      }

      $rating = $goodreadsRecord->get_info('average_rating');
      $keywords = $goodreadsRecord->get_keywords($gr_id);

      if(!empty($rating)){
        $this->item_info['reader_rating'] = $rating;
      }
      
      if(!empty($keywords)){
        $data['keywords']['topics'] = $keywords;
      }
    }

    //otherwise, attempt the retrieve info from goodreads by title and author search
    if(!array_key_exists('reader_rating', $this->item_info)) {      
      $bookAuthor =  '';

      if(is_array($this->post_meta) && array_key_exists('creators_0_name', $this->post_meta)){
        $bookAuthor = $this->post_meta['creators_0_name'][0];
      }

      if (strpos($bookAuthor, ",") !== FALSE && substr(strtolower($bookAuthor), -5) !== ", inc") {
        $authorNameArray = explode(", ", $bookAuthor);
        $authorSearch = $authorNameArray[0];
        $notLast = explode(" ", $authorNameArray[1]);
        $authorSearch = $authorSearch . "+" . $notLast[0];
      } else {
        $bookAuthor  = preg_replace("/\([^)]+\)/", "", $bookAuthor);
        $authorNameArray = explode(",", $bookAuthor);
        $authorSearch = $authorNameArray[0];
      }

      $title = preg_replace('/(\[|\()[^)]+(\]|\))/', "", $this->title);
      $query = rawurlencode($title) . "+" . rawurlencode($authorSearch);
      $goodreadsRecord = new Fontana_Goodreads_API($query);

      if($this->report['cover'] === false) {
        $this->report['cover image'] = false;
        $this->report['cover'] = $goodreadsRecord->get_info('image_url');
      }

      $gr_id = $goodreadsRecord->get_info('id');
      if(!empty($gr_id)){
        $this->report['goodreads ID'] = false;
        $this->item_info['goodreads_id'] = $gr_id;
      }

      $rating = $goodreadsRecord->get_info('average_rating');
      $keywords = $goodreadsRecord->get_keywords($gr_id);

      if(!empty($rating)){
        $this->report['rating'] = false;
        $this->item_info['reader_rating'] = $rating;
      }

      if(!empty($keywords)){
        $this->report['terms and keywords'] = false;
        $data['keywords']['topics'] = $keywords;
      }
    }

    return $data;
  }

  /**
   * Check OmDb API to retrieve cover image for videos.
   * 
   * @see class-fontana-omdb.php
   */
  protected function check_omdb(){
    $data = array(
      'keywords' => array(),
    );
    $check = false;
    $topics = array();

    foreach($this->term_keys as $key => $keywords){
      $topics += $keywords;
    }

    //Check topics for "Television" keyword indicators 
    foreach($topics as $topic) {
      switch ($check) {
          case false: $check = strpos(strtolower($topic), 'television');
          case true:  break 2;
      }
      switch ($check) {
          case false: $check = strpos(strtolower($topic), 'series');
          case true:  break 2;
      }
    }

    $part = '';
    if(is_array($this->post_meta) && array_key_exists('partNumber', $this->post_meta) && !empty($this->post_meta['partNumber'][0])){
      $part = strtolower($this->post_meta['partNumber'][0]);
    }
    
    //if there's not an indication this is a TV series, do a movie title search
    if ($check === false && stripos($part, 'season ') === false) {
      $args = array(
        'type' => 'title',
      );
      $titlesArray = $this->get_acf_repeater_values('alternative_titles','alternative_title');

      foreach($titlesArray as $key => &$title){
        $title = preg_replace('/(\[|\()[^)]+(\]|\))/', "", $title);
      }

      if(is_array($this->post_meta) && array_key_exists('date_issued', $this->post_meta) && !empty($this->post_meta['date_issued'][0])){
        $args['query_params']['y'] = preg_replace('/[^\d]/',"", $this->post_meta['date_issued'][0]);
      }
      
      $omdbRecord = new Fontana_Omdb_API($titlesArray, $args);
    }

    // Do a series search by Title.
    else {
      $shorttitle = get_the_title($this->post_id);
      $shorttitle = preg_replace('/(\[|\()[^)]+(\]|\))/', "", $shorttitle);

      $args = array(
        'format' => 'series'
      );
      $title = rawurlencode($shorttitle);
      $omdbRecord = new Fontana_Omdb_API($title, $args);
    }

    if(!empty($omdbRecord)) {
      if($this->report['cover'] === false){
        $this->report['cover'] = $omdbRecord->get_info("Poster");
        $this->report['verify cover'] = false;
      }
      $this->item_info['imdb_id'] = $omdbRecord->get_info("imdbID");
      $rating = $omdbRecord->get_info("imdbRating");
      if(!empty($rating)){
        $this->item_info['reader_rating'] = $rating/2;
      }
      $genre = $omdbRecord->get_info("Genre");
      $audience = $omdbRecord->get_info("Rated");
      if(!empty($genre)){
        $data['keywords']['Sub-Genre'] = explode(",", $genre);
      }
      if(!empty($audience)){
        $audience = explode(";", $audience);
        $data['keywords']['audience_other'] = $audience[0];
      }
    }
    return $data;
  }



  /**
   * Saves the an image as the post thumbnail / featured image.
   * 
   * @param   string    $fileUrl
   * @param   string    $imageTitle
   * @param   int       $post_id
   */
  function save_thumbnail_image($fileUrl, $imageTitle, $post_id) {
    $image_data       = file_get_contents($fileUrl); // Get image data
    
    if($image_data !== false && !empty($image_data)){
      $imageTitle = preg_replace('/[[:punct:]]/', "", $imageTitle);
      str_replace(" ", "-", $imageTitle);
      $image_name       = $imageTitle . ".jpg";
      $upload_dir       = wp_upload_dir();
      //$directory        = $upload_dir['basedir'] . "/collection";
      //$unique_file_name = wp_unique_filename( $directory, $image_name ); // Generate unique name
      $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
      $filename         = basename( $unique_file_name ); // Create image file name
      
      // Check folder permission and define file location
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
      $file = $upload_dir['path'] . '/' . $filename;
    } else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
      // Create the image  file on the server
      file_put_contents( $file, $image_data );

      // Check image file type
      $wp_filetype = wp_check_filetype( $filename, null );

      // Set attachment data
      $attachment = array(
              'post_mime_type' => $wp_filetype['type'],
              'post_title'     => sanitize_file_name( $filename ),
              'post_content'   => '',
              'post_status'    => 'inherit'
      );

      // Create the attachment
      $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

      // Include image.php
      require_once(ABSPATH . 'wp-admin/includes/image.php');

      // Define attachment metadata
      $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

      // Assign metadata to attachment
      wp_update_attachment_metadata( $attach_id, $attach_data );

      // And finally assign featured image to post
      set_post_thumbnail( $post_id, $attach_id );
      $this->report['cover'] = (int) $attach_id;
    }
  }
}