<?php
/**
 * Plugin Name:       Paddle payment gateway for WooCommerce.
 * Plugin URI:        https://exapmle.com
 * Description:       Paddle payment gateway for WooCommerce help to sell digital product with WooCommerce.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.3
 * Author:            Justin
 * Author URI:        https://exapmle.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pfwoo
 */


// Checking if the plugin is not accessed from outside WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Defining global constants for our .
define( 'PADDLE_WOO_VERSION', '1.0.0' );
define( 'PADDLE_WOO_URL', plugin_dir_url( __FILE__ ) );
define( 'PADDLE_WOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'PADDLE_WOO_INC', PADDLE_WOO_PATH . 'includes/' );

// Requiring the composer autoload file.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * Plugin activate Hook callback.
 *
 * @return void
 */
function plugin_new_activate() {
	\Pdfw\Classes\Activate::activate();
}
register_activation_hook( __FILE__, 'plugin_new_activate' );


 new \Pdfw\Init();



