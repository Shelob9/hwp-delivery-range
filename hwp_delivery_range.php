<?php
/**
 * Plugin Name: HWP Delivery Range
 * Plugin URI:  http://wordpress.org/plugins
 * Description: Delivery range calculator plugin
 * Version:     0.0.1
 * Author:      Josh Pollock
 * Author URI:  http://JoshPress.net
 * License:     GPLv2+
 * Text Domain: hwp_dr
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Josh Pollock (email : Josh@JoshPress.net)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using grunt-wp-plugin
 * Copyright (c) 2013 10up, LLC
 * https://github.com/10up/grunt-wp-plugin
 */

// Useful global constants
define( 'HWP_DR_VERSION', '0.0.1' );
define( 'HWP_DR_URL',     plugin_dir_url( __FILE__ ) );
define( 'HWP_DR_PATH',    dirname( __FILE__ ) . '/' );

/**
 * Default initialization for the plugin:
 * - Registers the default textdomain.
 */
function hwp_dr_init() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'hwp_dr' );
	load_textdomain( 'hwp_dr', WP_LANG_DIR . '/hwp_dr/hwp_dr-' . $locale . '.mo' );
	load_plugin_textdomain( 'hwp_dr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Load classes
 */
function hwp_dr_classes() {

	$classes_dir = trailingslashit( HWP_DR_PATH ).'includes/classes';
	include_once( trailingslashit( $classes_dir ) .'hwp_dr_locate.php' );
	include_once ( trailingslashit( $classes_dir) .'hwp_dr_geocode.php' );


	$utilities_dir =  trailingslashit( HWP_DR_PATH ).'includes/utility/';
	if ( defined( 'PODS_VERSION' ) ) {
		include_once(  $utilities_dir .'pods_utilities.php' );
	}

	include_once(  $utilities_dir .'general.php' );
	$class = hwp_dr_locate::init();
	$GLOBALS[ 'hwp_dr_locate' ] = $class;

	if ( is_admin() ) {
		include_once ( trailingslashit( $classes_dir ) .'hwp_dr_admin.php' );
		$class = hwp_dr_admin::init();
		$GLOBALS[ 'hwp_admin' ] = $class;
	}
	else {
		include_once ( trailingslashit( $classes_dir ) .'hwp_dr_front_end.php' );
		$class = hwp_dr_front_end::init();
		$GLOBALS[ 'hwp_front_end' ] = $class;
	}

}

/**
 * Activate the plugin
 */
function hwp_dr_activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	hwp_dr_init();

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hwp_dr_activate' );

/**
 * Deactivate the plugin
 * Uninstall routines should be in uninstall.php
 */
function hwp_dr_deactivate() {

}
register_deactivation_hook( __FILE__, 'hwp_dr_deactivate' );

// Wireup actions
add_action( 'init', 'hwp_dr_init' );
add_action( 'init', 'hwp_dr_classes' );

//for testing purposes
//@todo loose this
$GLOBALS[ 'test_lat_long' ] = array( 'lat' => 30.4751980, 'long' => -84.3052860 );
