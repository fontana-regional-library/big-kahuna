<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name:       Fontana Regional Library
 * Plugin URI:        https://fontanalib.org
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.9.7
 * Author:            Michael Schofield
 * Author URI:        https://schoeyfield.com
 * GitHub Plugin URI: https://github.com/fontana-regional-library/big-kahuna
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fontana
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fontana-activator.php
 */
function activate_fontana() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fontana-activator.php';
	Fontana_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fontana-deactivator.php
 */
function deactivate_fontana() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fontana-deactivator.php';
	Fontana_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fontana' );
register_deactivation_hook( __FILE__, 'deactivate_fontana' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fontana.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fontana() {

	$plugin = new Fontana();
	$plugin->run();

}
run_fontana();
