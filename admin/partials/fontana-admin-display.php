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
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
// Set class property
$this->options = get_option( $this->plugin_name ); ?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

        <div class="wrap">
            <h1>My Settings</h1>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
  <input type="hidden" name="action" value="collection_check">
  <input type="submit" value="Update Collection Items">
</form>
<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
  <input type="hidden" name="action" value="collection_publish">
  <input type="submit" value="Publish Checked Collection Items">
</form>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields($this->plugin_name );
                do_settings_sections( 'fontana-settings-admin' );
                submit_button();
            ?>
            </form>
        </div>