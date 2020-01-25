<?php

/**
 
 * @link              https://wppb.me/
 * @since             1.0.0
 * @package           Openstreetmap_metabox
 *
 * @wordpress-plugin
 * Plugin Name:       Radio Free MapPress
 * Plugin URI:        
 * Description:       This plugin adds a section on the Document Settings for each post to add location data to the post. It then displays those posts on the frontend at radiofree.org/map/ when using our Chaplin Child Theme.
 * Version:           1.0.0
 * Author:            Chase Lang & Sunil Prajapati
 * Author URI:        http://llacuna.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       openstreetmap_metabox
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('OPENSTREETMAP_METABOX_VERSION', '1.0.0');
define('OPENSTREETMAP_METABOX_DIR', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-openstreetmap_metabox-activator.php
 */
function activate_openstreetmap_metabox() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-openstreetmap_metabox-activator.php';
    Openstreetmap_metabox_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-openstreetmap_metabox-deactivator.php
 */
function deactivate_openstreetmap_metabox() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-openstreetmap_metabox-deactivator.php';
    Openstreetmap_metabox_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_openstreetmap_metabox');
register_deactivation_hook(__FILE__, 'deactivate_openstreetmap_metabox');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-openstreetmap_metabox.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_openstreetmap_metabox() {

    $plugin = new Openstreetmap_metabox();
    $plugin->run();
}

run_openstreetmap_metabox();
