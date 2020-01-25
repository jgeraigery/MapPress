<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wppb.me/
 * @since      1.0.0
 *
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Openstreetmap_metabox
 * @subpackage Openstreetmap_metabox/includes
 * @author     sunil <sunil@gmail.com>
 */
class Openstreetmap_metabox_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'openstreetmap_metabox',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
