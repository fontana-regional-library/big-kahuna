<?php 
/**
 * The settings functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/admin
 */

class Fontana_Admin_Views {
  /**
	 * User Groups
	 *
	 * @access   private
	 * @var      array    $user_groups  Array of grouped user positions and rows.
	 */
  private $user_groups = array(
    'supervisors'     => array(
      'Department Supervisor', 'Branch Librarian', 'Branch Supervisor', 'Asst. County Librarian'
      ),
    'managers'        => array(
      'County Librarian', 'Finance Officer', 'IT Officer', 'Regional Director'
      )
    );

  /**
   * Default Post Query Arguments
   * 
   * @access  private
   * @var     array   $post_args    array of default post query arguments.
   */
  private $post_args = array(
    'numberposts' => -1,
      'post_type'   => '',
      'meta_query'  => array(
      ),
    );

  /**
   * Location Terms
   * 
   * @access  private
   * @var   array   $locations  array of location taxonomy terms.
   */
  private $locations = array();


  /**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
    
  }

  public function fetch_locations(){
    $this->locations = get_terms( array(
      'taxonomy' => 'location',
      'hide_empty' => false,
    ));
  }
 /**
   * Adds custom columns to Keyword Taxonomy admin display page.
   * 
   * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$taxonomy_id_columns
   * @see $this->add_shelf_column_content();
   * 
   * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
   * 
   * @return  array   A modified array of column names and labels. 
   */

  public function add_keyword_columns($columns){
    $columns['genre'] = 'Genre';
    $columns['audience'] = 'Audience';
    unset($columns['posts']);
    unset($columns['slug']);
    
    return $columns;
  }
  /**
   * Adds custom columns to Shelf Taxonomy admin display page.
   * 
   * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$taxonomy_id_columns
   * @see $this->add_shelf_column_content();
   * 
   * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
   * 
   * @return  array   A modified array of column names and labels. 
   */

  public function add_shelf_columns($columns){
    $columns['location_id'] = 'Shelf ID';
    $columns['genre'] = 'Genre';
    $columns['audience'] = 'Audience';
    unset($columns['posts']);
    unset($columns['slug']);
    
    return $columns;
  }

  /**
   * Adds content to taxonomy custom columns when viewing the admin dashboard page for the taxonomy.
   * 
   * @link https://developer.wordpress.org/reference/hooks/manage_this-screen-taxonomy_custom_column/
   * 
   * @param             $content        Current content for column?
   * @param   string    $column_name    Content column name
   * @param   int       $term_id        Id of the current term
   * 
   * @return  string    content to display for the current column.
   */
  public function add_shelf_column_content($content,$column_name,$term_id){
    $term = get_term($term_id);
    switch ($column_name) {
        case 'genre':
            $genres = get_field('related_genres', $term->taxonomy."_".$term_id);
            if ($genres) {
            $content = implode(", ", array_column($genres, 'name'));
            }
            break;
        case 'audience':
            $audience = get_field('related_audience', $term->taxonomy."_".$term_id);
            if ($audience) {
            $content = implode(", ", array_column($audience, 'name'));
            }
            break;
        case 'location_id':
            $content = get_term_meta($term_id, 'shelf_location_id', true);
            
            break;
        default:
            break;
    }
    return $content;
  }
/**
 * Adds thumbnail to collection item post column listing.
 * 
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$post_type_posts_columns
 * 
 * @param   array   $columns   An array of column name ⇒ label. The name is passed to functions to identify the column.
 * 
 * @return  array   A modified array of column names and labels. 
 */
  function alert_columns($columns){
    $columns['start'] = 'Starts';
    $columns['expire'] = 'Expires';
    $columns['type'] = 'Type';

    return $columns;
  }

  function collection_item_columns($columns){
    $columns['verify'] = 'Review';
    $columns['thumbnail'] = 'Thumbnail';
    
    return $columns;
  }
  

/**
* Adds content to custom columns.
* 
* Add a thumbnail image to the collection item post display for quick checking.
* @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_$post_type_posts_custom_column
* 
* @param   string    $column_name    The name of the column to display.
* @param   int       $post_id        The ID of the current post.
* 
*/
function alert_custom_columns($column_name, $post_id){
    $expiration = get_field('notice_expiration');
    $ex = date_create($expiration, timezone_open('America/New_York'));
    $now = date_create(null, timezone_open('America/New_York'));
    $diff = date_diff($now, $ex, FALSE);
    
    $hours = $diff->format("%R%i");
    $h = $diff->h;
    $m = $diff->i;

    if($column_name === 'start' && strtotime($expiration) > strtotime('now')){
      the_field('start_notification', $post_id);
    }
    if( $column_name === 'expire' && $hours > 60*4 ){
      echo $expiration;
    } elseif( $column_name === 'expire' && $hours > 0 ){
      echo $h>1?$h . " hours " : $h>0 ? $h . " hour " : "";
      echo $m>1 ?  $m . " minutes" : $m>0 ? $m . " minute" : "";
    } elseif( $column_name === 'expire' ){
      echo '<em>Expired</em>';
    }
    if( $column_name === 'type' ){
      the_field('notice_type', $post_id);
    }
  }


  function collection_item_custom_columns($column_name, $post_id){
    if( $column_name === 'thumbnail' ){
      if(has_post_thumbnail($post_id)){
        echo '<a href="' . get_post_meta($post_id, 'url', true) . '" target="_blank">';
        echo the_post_thumbnail( 'thumbnail' );
        echo '</a>';
      } else {
        echo '<a href="' . get_post_meta($post_id, 'url', true) . '" target="_blank">record</a>';
      }
    }
    if( $column_name === 'verify' ){
      $fail = get_post_meta($post_id, 'check_fail_count', true);
      $message = get_post_meta($post_id, 'verify', true);
      if(!empty($fail)){
        echo "<strong>Record retrieval failed " . $fail . " times.</strong><br/>";
      }
      if(empty($message)){
        $message ='';
      } else{
        $list = explode(";", $message);
        $message = "Verify:<ol>";
        foreach($list as $item){
          $message .= "<li>".$item."</li>";
        }
        $message .= "</ol>";
      }
      echo $message;
    }
  }

 /**
   * Tags alert post types with appropriate location on save.
   * 
   * @param int   $post_id  The post ID      
   */
  function save_alert_data ( $post_id ) { 
    // bail early if not alert
    if(get_post_type($post_id) !== "alert"){
      return;
    }
    $title = get_field('short_title');
    
    $locations = wp_list_pluck($this->locations, 'slug');
    
    $affected = get_field('affected_location');

    if(in_array('all-locations', $affected)){
      wp_set_object_terms($post_id, $locations, 'location', false);
      $title .= " - " . "All locations";
    }
    if(in_array('all-locations', $affected) && count($affected) > 1){
      $set[] = 'all-locations';
      update_field('affected_location', $set, $post_id);
    }

    if(!in_array('all-locations', $affected) && !empty($affected)){
      wp_set_object_terms($post_id, $affected, 'location', false);
    }
    wp_update_post(array('ID'=>$post_id, 'post_title'=>$title));
  }

  /**
   * Add a new item into the Bulk Actions Dropdown.
   * 
   * @param   array   $bulk_actions   array of actions in the bulk actions menu
   * 
   * @return  array   $bulk_actions
   */
  function register_custom_bulk_actions( $bulk_actions ) {
    $bulk_actions['check_holdings'] = __( 'Check Holdings', 'wordpress' );
    $bulk_actions['verify_item_info'] = __( 'Verify and Publish', 'wordpress' );
    return $bulk_actions;
  }

  /**
   * Add an admin notice after Bulk Check Collection Item Action and after checking failed records.
   */
  function bulk_check_admin_notice() {
    if ( ! empty( $_REQUEST['bulk_checked_holdings'] ) ) {
      $class = 'notice notice-info is-dismissible';
      
      $checked_count = (int) $_REQUEST['bulk_checked_holdings']["checked"];
      $updated_count = (int) $_REQUEST['bulk_checked_holdings']["update"];
      $draft_count = (int) $_REQUEST['bulk_checked_holdings']["draft"];
      $trash_count = (int) $_REQUEST['bulk_checked_holdings']["trash"];
      $fail_count = (int) $_REQUEST['bulk_checked_holdings']["fail"];

      $message1 = __( $checked_count . ' collection items have been checked. ', 'wordpress' );
      $message2 = $updated_count > 0 ? $updated_count . " items updated. " : "";
      $message3 = $draft_count > 0 ? $draft_count . " items moved to draft. " : "";
      $message4 = $trash_count > 0 ? $trash_count . " items moved to trash. " : "";
      $message5 = $fail_count > 0 ? $fail_count . " items failed and will be added to failed check list. " : "";

      printf( '<div class="%1$s"><p>%2$s%3$s%4$s%5$s%6$s</p></div>', esc_attr( $class ), esc_html( $message1 ),esc_html( $message2 ),esc_html( $message3 ),esc_html( $message4 ),esc_html( $message5 ) );
    }
    if ( ! empty( $_REQUEST['checking_failed_items'] ) ) {
      $status = $_REQUEST['checking_failed_items']['status'];
      $class = 'notice notice-'. $status.' is-dismissible';
      $count = $_REQUEST['checking_failed_items']['count'];
      $report = $_REQUEST['checking_failed_items']['checked'];

      $message2 = $count > 0 ? "<p>" .  $count . " items are waiting to be checked.</p>" : "";
      $message3 = '';
      if(!empty($report)){
        $message3 .= '<ul>';
        foreach($report as $s => $num){
          $message3 .="<li>". $num . " items returned '" . $s ."'</li>"; 
        }
        $message3 .= '</ul>';
      }

      if($status === 'success'){
        $message = __( 'A batch of failed items was successfully checked.', 'wordpress' );
      } else{
        $message = __( 'There was an error checking failed items. No items were updated.', 'wordpress' );
      }

      printf( '<div class="%1$s"><p>%2$s</p>%3$s%4$s</div>', esc_attr( $class ), esc_html( $message ), $message2 , balanceTags($message3) );
    }
  }
  /**
   * Deletes attachments when a collection item is deleted.
   * 
   * @param   int   $post_id    The ID of the post being deleted.
   */
  public function delete_attachments($post_id){
    global $post_type;   
    if ( $post_type === 'collection-item' ){
      $attachments = get_posts(
        array(
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'post_parent'    => $post_id,
        )
      );
      foreach ( $attachments as $attachment ) {
          wp_delete_attachment( $attachment->ID );
      }
    }
  }
  /**
   * Adds callback filter to customize the upload directory.
   * 
   * @see $this->customize_upload_directory();
   * 
   * @param   array   $file   An array of data for a single file.
   * 
   * @return  array   A modified array of data for a single file. 
   */
  function upload_directory( $file ) {
    add_filter( 'upload_dir', array($this, 'customize_upload_directory') );
    return $file;
  }
  /**
   * Customizes the upload directory for custom post type media attachments.
   * 
   * @param   array   $param    parameter array
   * 
   * @return  array   returns modified parameter array
   */
  function customize_upload_directory( $param ) {
    $id = $_REQUEST['post_id'];
    $parent = get_post( $id )->post_parent;
    if( "collection-item" == get_post_type( $id ) || "collection-item" == get_post_type( $parent ) ) {
        $mydir         = '/collection';
        $param['path'] = $param['basedir'] . $mydir;
        $param['url']  = $param['baseurl'] . $mydir;
    }
    return $param;
  }

  /**
   * Sends email to event coordinators if closing alert overlaps with events.
   * 
   * @param   int   $post_id    The post id of the saved alert.
   */
  public function alerts_notifications( $post_id ){
    $this->locations = get_terms('location', array(
      'hide_empty' => false,
    ));
    
    error_log("ALERTS NOTIFICATIONS....");
    // bail early if not alert
    if(get_post_type($post_id) !== "alert"){
      //error_log("bailing post_type");
      return;
    }
    $meta = get_post_meta($post_id);
    //error_log(print_r($meta, true));
    if(!is_array($meta) || !array_key_exists('notice_type', $meta) || !array_key_exists('affected_location', $meta)){
      //error_log("bailing no meta");
      return;
    }
    if($meta['notice_type'][0] !== 'planned' && $meta['notice_type'][0] !== 'unplanned'){
      //error_log("bailing not closed");
      return;
    }
    $affected = maybe_unserialize($meta['affected_location'][0]);
    //start_notification
    $key = "closed - " . implode(",", $affected) . " - " . $meta['start_notification'][0] . " - " . $meta['notice_expiration'][0];
    if(array_key_exists('email_tracker', $meta) && $meta['email_tracker'][0] === $key){
      //error_log("bailing email sent");
      return;
    }
    $supervisor_roles = array();
    foreach($this->user_groups as $k => $group){
      $supervisor_roles = array_merge($supervisor_roles, $group);
    }
    //error_log($meta['notice_expiration'][0]);
    $location = 'all library locations';
    $args = $this->post_args;
    $args['post_type'] = 'tribe_events';
    $args['meta_query'][] = array(
      'key'     => '_EventStartDate',
      'value'   => $meta['notice_expiration'][0],
      'compare' =>  "<=",
      'type'    => 'DATETIME',
    );
    $args['meta_query'][] = array(
      'key'     => '_EventEndDate',
      'value'   => $meta['start_notification'][0],
      'compare' => ">=",
      'type'    => 'DATETIME',
    );
    

    $user_args = array(
      'order' => 'ASC',
      'orderby' => 'display_name',
      'meta_query' => array(
        'relation' => 'AND',
        // array(
        //   'key'  => 'position',
        //   'compare'   => 'EXISTS'
        // ),
        array(
          'key'  => 'position',
          'value' => $supervisor_roles,
          'compare'   => 'IN'
        ),
    ));  


    if(!in_array('all-locations', $affected)){      
      $locs = array();
      $user_locs = array();
      
      foreach($this->locations as $term){
        if(in_array($term->slug, $affected)){
          $locs[] = $term->name;
          $user_locs[] = $term->term_id;
        }
      }
      $location = join(' and ', array_filter(array_merge(array(join(', ', array_slice($locs, 0, -1))), array_slice($locs, -1)), 'strlen'));
      //error_log("Location : " . $location);

      $args['tax_query'] = array(
        array(
          'taxonomy'=> 'location',
          'field'   => 'slug',
          'terms'   => $affected,
        ),
      );
      $user_args['meta_query'][] = array(
        'key' => 'location', 
        'value' => $user_locs,
        'compare'   => 'IN',
      );
    }
    
    
    //The following events are scheduled during this alert / closing:
    $events = get_posts($args);
    //error_log("EVENTS are : " . print_r($events, true));

    if(!empty($events)){
      $subject = 'New Closing Scheduled';
      //The following users should be notified
      $email_to = array();
      $list = "<ul>";

      $user_query = new WP_User_Query( $user_args );
      $notify = $user_query->get_results();
      //error_log("NOTIFY : " . print_r($notify, true));

      if(!empty($notify)){
        foreach($notify as $user){
          if(!in_array($user->user_email, $email_to)){
            $email_to[] = $user->user_email;
          }
        }
      }
      //error_log("EMAIL TO : " . print_r($email_to, true));

      foreach($events as $event){
        $author = get_userdata($event->post_author);

        if(!in_array($author->user_email, $email_to)){
          $email_to[] = $author->user_email;
        }

        $list .= "<li><a href='https://fontanalib.org/events/" . $event->post_name . "'>" . $event->post_title . "</a> - " . tribe_get_venue ($event->ID ) ."</li>";
      }
      //error_log("EMAIL TO : " . print_r($email_to, true));
     // error_log("LIST: " . print_r($list, true));

      $list .= "</ul>";
      ob_start();
      include_once plugin_dir_path( __FILE__ ). "partials/email-header.php";
      ?>
      <p>This notice is to alert you that a library closing has been posted for <?php echo $location ?>. 
      The following events are also scheduled during this closing:</p>
      <p>
          <?php echo $list ?>
      </p>
      <?php
      include_once plugin_dir_path( __FILE__ ). "partials/email-footer.php";
      $message = ob_get_contents();
      ob_end_clean();
      //error_log("EMAIL TO : " . print_r($email_to, true));
      wp_mail('awest@fontanalib.org', $subject, $message);
      update_post_meta($post_id, 'email_tracker', $key);
    }
  }
}