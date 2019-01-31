<?php 
/**
 * The Notifications functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/admin
 */

class Fontana_Notifications {
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
   * Email template Variables.
   */
  protected $class;
  protected $email_to;
  protected $image;
  protected $link;
  protected $link_text;
  protected $list;
  protected $paragraphs;
  protected $subject;
  protected $tagline;
  

  public function __construct(){
    $this->class = "";
    $this->email_to = array();
    $this->image = "";
    $this->link = "";
    $this->link_text = "";
    $this->list = "";
    $this->paragraphs = array();
    $this->subject = "";
    $this->tagline = "";
  }


  public function fetch_locations(){
    $this->locations = get_terms( array(
      'taxonomy' => 'location',
      'hide_empty' => false,
    ));
  }
  function html_mail_content_type() {
    return "text/html";
  }

  function set_mail_from() {
      return "activity.assessments@fontanalib.org";
  }

  function set_mail_from_name() {
      return "Staff Activity Portal";
  }

  /**
   * Sends email to event coordinators if closing alert overlaps with events.
   * 
   * @param   int     $post_id    The post id of the saved alert.
   * @param   object  $post       WP_POST
   * @param   bool    $update     Whether this is an existing post being updated or not
   */
  public function alert_notifications( $post_id, $post, $update ){
    // bail early if auto-draft
    if ( wp_is_post_revision( $post_id) || wp_is_post_autosave( $post_id ) ){
      return;
    }
    
    $meta = get_post_meta($post_id);
    // bail early - no meta
    if(!is_array($meta) || !array_key_exists('notice_type', $meta) || !array_key_exists('affected_location', $meta)){
      return;
    }

    $type = $meta['notice_type'][0];
    // bail early - not closed
    if($type === 'announcement'){
      return;
    }

    $affected = maybe_unserialize($meta['affected_location'][0]);
    $key = $type . " - " . implode(",", $affected) . " - " . $meta['start_notification'][0] . " - " . $meta['notice_expiration'][0];
    // bail early - email sent, no changes
    if(array_key_exists('email_tracker', $meta) && $meta['email_tracker'][0] === $key){
      return;
    }
    if(!array_key_exists('email_tracker', $meta)){
      $this->subject = "New ";
    } else {
      $this->subject = "Updated ";
    }
    
    $this->alert_notice_data($post_id, $key);
    
    $this->paragraphs[] = "The public will be alerted beginning " . date('D, M j \a\t g:ia', strtotime($meta['start_notification'][0])) .
                          ". The alert will end " . date('D, M j \a\t g:ia', strtotime($meta['notice_expiration'][0]));
    $body = get_post_field('post_content', $post_id);
    $this->paragraphs[] = "<table class='callout'>
                            <tbody><tr>
                              <th class='callout-inner secondary'>".
                              $body . 
                              "</th></tr></tbody></table>";

    ob_start();
      include_once plugin_dir_path( __FILE__ ). "partials/staff-email-header.php";
      include_once plugin_dir_path( __FILE__ ). "partials/basic-email.php";
      include_once plugin_dir_path( __FILE__ ). "partials/staff-email-footer.php";
      $message = ob_get_contents();
    ob_end_clean();
      
    if(!empty($email_to)){
      wp_mail($email_to, $this->subject, $message, $headers);
    }
    update_post_meta($post_id, 'email_tracker', $key);
  }
  /**
   * Prepares alert data for email notification
   * 
   * @param   int     $post_id
   * @param   string  $key
   */
  private function alert_notice_data($post_id, $key){
    $vars = array();
    $meta = explode(" - ", $key);

    $vars['type'] = $meta[0];
    $vars['affected'] = explode(",", $meta[1]);
    $vars['start_date'] = $meta[2];
    $vars['end_date'] = $meta[3];
    $vars['locations'] = array();

    if(in_array('all-locations', $vars['affected'])){
      $location = 'all library locations';
      $vars['locations'] = array_combine(wp_list_pluck($this->locations, 'term_id'), wp_list_pluck($this->locations, 'name'));
    }

    if(!in_array('all-locations', $vars['affected'])){      
      foreach($this->locations as $term){
        if(in_array($term->slug, $vars['affected'])){
          $vars['locations'][$term->term_id] = $term->name; // array_keys === user_locs
        }
      }
      $location = join(' and ', array_filter(array_merge(array(join(', ', array_slice($vars['locations'], 0, -1))), array_slice($vars['locations'], -1)), 'strlen'));
    }

    $this->email_to = $this->get_user_group('managers');
    $this->image = get_the_post_thumbnail($post_id,'medium', ['class' => 'float-center', 'align' => 'center']);
    $this->link = "https://fontana.librarians.design/wp-admin/post.php?post=" . $post_id . "&action=edit";
    $this->link_text = "Edit this Alert";
    
    $this->tagline = get_the_title($post_id);
    
    if($vars['type'] === 'planned' || $vars['type'] === 'unscheduled'){
      $this->subject .= '"' . $vars['type'] . ' closing" alert posted'; 
      $this->alert_conflicting_event_notice($post_id, $vars, $location);
      return;
    }

    $this->subject .= $vars['type'] . " posted";
    $this->paragraphs[] = "A notice/alert has been posted which affects " . $location .".";
  }
  /**
   * Gets data for alert / event conflict email notification
   * 
   * @param   int     $post_id
   * @param   array   $affected
   * @param   string  $key
   */

  private function alert_conflicting_event_notice($post_id, $vars, $location){
    $args = $this->post_args;
    $args['orderby'] = '_EventStartDate';
    $args['order'] = "ASC";
    $args['post_type'] = 'tribe_events';
    $args['meta_query'][] = array(
      'key'     => '_EventStartDate',
      'value'   => $vars['end_date'],
      'compare' =>  "<=",
      'type'    => 'DATETIME',
    );
    $args['meta_query'][] = array(
      'key'     => '_EventEndDate',
      'value'   => $vars['start_date'],
      'compare' => ">=",
      'type'    => 'DATETIME',
    );
    
    if(!in_array('all-locations', $vars['affected'])){
      $args['tax_query'] = array(
        array(
          'taxonomy'=> 'location',
          'field'   => 'slug',
          'terms'   => $vars['affected'],
        ),
      );
    }
    
    // The following events are scheduled during this alert / closing:
    $events = get_posts($args);

    // Alert Schedule
    $start = strtotime($vars['start_date']);
    $end = strtotime($vars['end_date']);

    $this->class= 'warning';

    $this->paragraphs[] = "This notice is to alert you that a library closing has been posted for " . $location .".";

    if(!empty($events)){
      $this->email_to = $this->get_user_group('supervisors', array_keys($vars['locations']), $this->email_to);
      $list = $this->get_post_list($events);
      $this->email_to = $this->get_author_list($events, $this->email_to);
      $this->list = '<p class="underline">The following events are also scheduled during this closing:</p>' . $list;
    }
      
  }

  /**
   * Get email list of authors;
   * 
   * @param   array   $posts      array of post objects
   * @param   array   $email_to   array of email addresses
   * 
   * @return  array   array of author emails merge with supplied list
   */
  private function get_author_list($posts, $email_to = array()){
    foreach($posts as $post){
      $author = get_userdata($post->post_author);

      if(!in_array($author->user_email, $email_to)){
        $email_to[] = $author->user_email;
      }
    }
    return $email_to;
  }

  /**
   * Creates an html list of post items.
   * 
   * @param   array   $posts    array of post objects
   * 
   * @return  string  an html list of linked post items
   */
  private function get_post_list($posts){
    if(empty($posts)){
      return '';
    }
    $anc = "<li><a href='https://fontanalib.org/";
    
    
    $list = "<ul>";

    foreach($posts as $post){
      $list .= $anc;
      $link = $post->post_name . "'>" . $post->post_title . "</a>";
      
      if($post->post_type === 'tribe_events'){
        $string = tribe_get_venue( $post->ID );
        $expr = '/(?<=\s|^)[a-z]/i';
        preg_match_all($expr, $string, $matches);

        $list .= "events/" . $link. " <small> | " . strtoupper(implode('', $matches[0])) . " - " . tribe_get_start_date($post->ID, true, 'M. j, ga') . '</small>';
      }

      $list .= "</li>";
    }

    $list .= "</ul>";
    return $list;
}

/**
 * Returns list of users by position.
 * 
 * @param   array   $location   array of location term ids
 * @param   array   $email_to   array of email addresses
 * 
 * @return  array   array of user emails merge with supplied list
 */
  private function get_user_group($group, $location = array(), $email_to = array() ){
    $args = array(
      'order' => 'ASC',
      'orderby' => 'display_name',
      'meta_query' => array(
        'relation' => 'AND',
        array(
          'key'  => 'position',
          'value' => $this->user_groups[$group],
          'compare'   => 'IN'
        ),
    ));  

    if(!empty($location)){
      $user_args['meta_query'][] = array(
        'key' => 'location', 
        'value' => $location,
        'compare'   => 'IN',
      );
    }

    $user_query = new WP_User_Query( $args );
    $notify = $user_query->get_results();

    if(!empty($notify)){
      foreach($notify as $user){
        if(!in_array($user->user_email, $email_to)){
          $email_to[] = $user->user_email;
        }
      }
    }
    return $email_to;
  }
}