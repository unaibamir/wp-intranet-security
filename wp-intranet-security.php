<?php

/**
 * @link              https://www.site.com
 * @since             1.0.0
 * @package           Wp_Intranet_Security
 *
 * @wordpress-plugin
 * Plugin Name:       WP Intranet Security
 * Plugin URI:        https://www.site.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            WP Plugin 
 * Author URI:        https://www.site.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-intranet-security
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
define( 'WPIS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'WPIS_PLUGIN_VERSION', '1.0.0' );
define( 'WPIS_LANG', 'wp-intranet-security' );
define( 'WPIS_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-intranet-security-activator.php
 */
function activate_wp_intranet_security() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-intranet-security-activator.php';
	Wp_Intranet_Security_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-intranet-security-deactivator.php
 */
function deactivate_wp_intranet_security() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-intranet-security-deactivator.php';
	Wp_Intranet_Security_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_intranet_security' );
register_deactivation_hook( __FILE__, 'deactivate_wp_intranet_security' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-intranet-security.php';



if( !function_exists( "dd" ) ) {
    function dd( $data, $exit_data = true) {
        echo '<pre>'.print_r($data, true).'</pre>';
        if($exit_data == false)
            echo '';
        else
            exit;
    }
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_intranet_security() {

	$plugin = new Wp_Intranet_Security();
	$plugin->run();

}
run_wp_intranet_security();
