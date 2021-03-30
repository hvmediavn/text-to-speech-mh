<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://dominhhai.com/
 * @since             1.0.0
 * @package           Text_To_Speech_Mh
 *
 * @wordpress-plugin
 * Plugin Name:       Text to Speech
 * Plugin URI:        text-to-speech
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Minh Hai
 * Author URI:        https://dominhhai.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       text-to-speech-mh
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
define( 'TEXT_TO_SPEECH_MH_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-text-to-speech-mh-activator.php
 */
function activate_text_to_speech_mh() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-text-to-speech-mh-activator.php';
	Text_To_Speech_Mh_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-text-to-speech-mh-deactivator.php
 */
function deactivate_text_to_speech_mh() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-text-to-speech-mh-deactivator.php';
	Text_To_Speech_Mh_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_text_to_speech_mh' );
register_deactivation_hook( __FILE__, 'deactivate_text_to_speech_mh' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-text-to-speech-mh.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_text_to_speech_mh() {

	$plugin = new Text_To_Speech_Mh();
	$plugin->run();

}
run_text_to_speech_mh();
