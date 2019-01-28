<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://schoeyfield.com
 * @since      1.0.0
 *
 * @package    Fontana
 * @subpackage Fontana/admin/partials
 */
$importList = implode(", ", $this->collectionImporters);
if(empty($this->apiSettings)){
  $this->apiSettings = array(
    'overdrive'=>'',
    'overdrive_client'=>'',
    'goodreads'=>'',
    'omdb'  => '',
  );
}
$now= new DateTime("now");
$check = wp_next_scheduled('fbk_check_deleted');
if ($check !== false){
  $til = new DateTime('@' . $check);
  $scheduled = 'Next Overdrive check: ' . $now->diff($til)->i . 'min" disabled'; 
} else {
  $scheduled ='Check OverDrive Records"';
}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    
        <h2>Fontana Settings Page</h2>
        <?php 
            if (false !== ($failed = get_option('failed_records_import'))){?>
            <div class="notice notice-large error">
              <p><?php _e('There are ' . $failed . ' failed items to process.', 'wordpress'); ?></p>
            </div>
            <?php } ?>
            <hr>
            <h3>Collection Imports</h3>
            <?php $checkFailed = wp_next_scheduled('fbk_check_failed');
              if($checkFailed !== false){
                $tilFailed = new DateTime('@' . $checkFailed);
                $scheduledCheck = 'Next Failed Import check: ' . $now->diff($tilFailed)->i. 'min';
              } else {
                $scheduledCheck = "";
              }
              $checkEvergreen = wp_next_scheduled('fbk_check_evergreen_holdings');
              if($checkEvergreen !== false){
                $tilEvergreen = new DateTime('@' . $checkEvergreen);
                $scheduledEvergreen = 'Next Evergreen Holdings check: ' . $now->diff($tilEvergreen)->i. 'min';
              } else{
                $scheduledEvergreen = '';
                }?>
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="update_terms">
              <input class="alignright button button-secondary button-large" type="submit" value="Update Cached Terms List">
              </form>
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="check_failed">
              <input class="button button-secondary button-large"  type="submit" value="Process Failed Imports"> <em> 
              <?php echo $scheduledCheck?></em><br/>
              </form><br/>
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="check_evergreen_holdings">
              <input class="button button-secondary button-large" type="submit" value="Check Evergreen Holdings"> <em><?php echo $scheduledEvergreen; ?></em><br/>
            </form> 
            <?php settings_errors('fontana_collection_imports'); ?>
            <form method="post" action="options.php">
            <?php settings_fields('fontana_collection_imports'); ?>
            <label for="fontana_collection_imports">List of Importer IDs for Evergreen Collection Imports</label> <input type ="text" name="fontana_collection_imports" value="<?php echo $importList; ?>"/>
            <br/><span class="description">comma separated integers- i.e. 123, 345, 567, etc.</span>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Importer IDs') ?>" />
            </p>
            </form><hr/>
            <?php settings_errors('fontana_overdrive_libraries'); ?>
            <h3>OverDrive Libraries</h3>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="import_overdrive">
              <input type="submit" value="<?php echo $scheduled; ?>> <?php ?>
            </form> 
            <form method="post" action="options.php">
            <?php settings_fields('fontana_overdrive_libraries'); ?>
            <table class="form-table">
                <tr valign="top"><th>Library</th><th >ID</th><th>Delete?</th><th>Results</th></tr>
                <?php 
                    if($this->overdriveLibraries && is_array($this->overdriveLibraries)){
                        foreach($this->overdriveLibraries as $lib => $id){
                          $imported ='';
                          if (false !== ($results = get_option('overdrive_results_count_' . $id))){
                            if(array_key_exists('items_imported', $results)){
                              $results['offset'] = $results['items_imported'];
                            } elseif(!array_key_exists('offset', $results)){
                              $results['offset'] = (int) 0;
                            }
                            $imported = $results['offset'] . " imported of " . $results['total'] . " items.";
                          }
                            echo 
                            '<tr>
                            <td>'. $lib .'</td>
                            <td><input type="text" name="fontana_overdrive_libraries['.$lib.']" value="'.$id.'" /></td>
                            <td><input type="checkbox" name="fontana_overdrive_libraries['.$lib.']" value="delete" /></td>
                            <td>'.$imported.'</td>
                            </tr>';
                        }
                    }
                ?>
                <tr>
                    <td><input type="text" name="fontana_overdrive_libraries[newLibrary]" value="" /></td>
                    <td><input type="text" name="fontana_overdrive_libraries[newId]" value="" /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save OverDrive Libraries') ?>" />
            </p>
            </form>
            <?php if($this->overdriveLibraries && is_array($this->overdriveLibraries)){ ?>
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <h4> Delete Results Counter</h4>
                <input type="hidden" name="action" value="delete_results">
                <?php foreach($this->overdriveLibraries as $lib => $id){
                  if(false !== ($results = get_option('overdrive_results_count_' . $id))){
                    echo $lib . ' <input type="checkbox" name="lib[]" value="'.$id .'" /><br/>';
                  }
                }?>
                <input type="submit" value="Delete Results">
                      </form> <?php } ?>
<hr>
<?php settings_errors('fontana_api_settings'); ?>
<h3>Developer Settings</h3>
            <form method="post" action="options.php">
            <?php settings_fields('fontana_api_settings'); ?>
            <table class="form-table">
                <tr valign="top"><th>Platform</th><th >Key</th><th>Delete?</th></tr>
                <?php 
                    if($this->apiSettings && is_array($this->apiSettings)){
                        foreach($this->apiSettings as $api => $val){
                            echo 
                            '<tr>
                            <td>'. $api .'</td>
                            <td><input type="text" name="fontana_api_settings['.$api.']" value="'.$val.'" /></td>
                            <td><input type="checkbox" name="fontana_api_settings['.$api.']" value="delete" /></td>
                            </tr>';
                        }
                    }
                ?>
                <tr>
                    <td><input type="text" name="fontana_api_settings[newApi]" value="" /></td>
                    <td><input type="text" name="fontana_api_settings[newKey]" value="" /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save APIs') ?>" />
            </p>
            </form>
            
            <hr>
            
    </div>