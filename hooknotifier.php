<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://twitter.com/illuday
 * @since             2.0.0
 * @package           Hooknotifier
 *
 * @wordpress-plugin
 * Plugin Name:       Hook.Notifier
 * Plugin URI:        https://hooknotifier.com
 * Description:       Hook.Notifier is a notification collector, it allows you to send, store and organize useful notifications on your phone from various services.
 * Version:           2.0.0
 * Author:            illuday
 * Author URI:        https://twitter.com/illuday
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hooknotifier
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'HOOKNOTIFIER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hooknotifier-activator.php
 */
function activate_hooknotifier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hooknotifier-activator.php';
	Hooknotifier_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hooknotifier-deactivator.php
 */
function deactivate_hooknotifier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hooknotifier-deactivator.php';
	Hooknotifier_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_hooknotifier' );
register_deactivation_hook( __FILE__, 'deactivate_hooknotifier' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-hooknotifier.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_hooknotifier() {

	$plugin = new Hooknotifier();
	$plugin->run();

}
run_hooknotifier();
