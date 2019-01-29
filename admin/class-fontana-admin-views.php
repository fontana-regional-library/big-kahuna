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
	 * Initialize the class and set its properties.
	 */
	public function __construct() {

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
    if($column_name === 'expire' && $hours > 60*4){
      echo $expiration;
    } elseif($column_name === 'expire' && $hours > 0){
      // $hours = floor(abs((strtotime($expiration)) - strtotime("now"))/3600);
      // $minutes = ceil(abs((strtotime($expiration)) - strtotime("now"))/60 - ($hours * 60));
      // $h = $hours === 1 ? $hours . " hour" : $hours > 1 ? $hours . " hours" : "";
      // $m = $minutes>=1 ? $minutes . " minutes" : "";
      echo $h>1?$h . " hours " : $h>0 ? $h . " hour " : "";
      echo $m>1 ?  $m . " minutes" : $m>0 ? $m . " minute" : "";
      //if()
    } else if($column_name === 'expire'){
      echo '<em>Expired</em>';
    }
    if($column_name === 'type'){
      the_field('notice_type', $post_id);
    }
  }


  function collection_item_custom_columns($column_name, $post_id){
    if($column_name === 'thumbnail'){
      if(has_post_thumbnail($post_id)){
        echo '<a href="' . get_post_meta($post_id, 'url', true) . '" target="_blank">';
        echo the_post_thumbnail( 'thumbnail' );
        echo '</a>';
      } else {
        echo '<a href="' . get_post_meta($post_id, 'url', true) . '" target="_blank">record</a>';
      }
    }
    if($column_name === 'verify'){
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
}