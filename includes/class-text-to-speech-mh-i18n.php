<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://dominhhai.com/
 * @since      1.0.0
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/includes
 * @author     Minh Hai <minhhai27121994@gmail.com>
 */
class Text_To_Speech_Mh_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'text-to-speech-mh',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
