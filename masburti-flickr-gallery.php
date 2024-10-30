<?php
/*
Plugin Name: Masburti Flickr Gallery
Description: Integrate your Flickr account and displays selected photosets
Version: 1.1
Author: Filip Kula
Author URI: http://fkula.pl
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: masburti-flickr-gallery
Domain Path: /languages
*/

define( 'MASBURTI_FGP_NAME', 'Masburti Flickr Gallery' ); //plugin name
define( 'MASBURTI_FGP_REQUIRED_PHP_VERSION', '5.3' );   //plugin required PHP version
define( 'MASBURTI_FGP_REQUIRED_WP_VERSION', '3.5' );    //plugin minimal WordPress version

require_once "init.php";

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met.
 * Otherwise older PHP installations could crash when trying to parse it.
 */
if ( Masburti_Flickr_Gallery_Helpers::requirements_met() ) {
	register_activation_hook( __FILE__, 'Masburti_Flickr_Gallery_Plugin::install' );    //PHP 5.2.3
	register_activation_hook( __FILE__, 'Masburti_Flickr_Gallery_Plugin::install_data' );

	new Masburti_Flickr_Gallery_Plugin();
} else {
	add_action( 'admin_notices', 'Masburti_Flickr_Gallery_Views::requirements_error' );
}