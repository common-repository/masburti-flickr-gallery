<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once 'classes/class-masburti-flickr-gallery-plugin.php';

$option_names = array(
	Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY,
	Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY,
	Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_OAUTH_TOKEN_SECRET,
	Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN,
	Masburti_Flickr_Gallery_Plugin::OPTION_DB_VERSION
);

foreach ( $option_names AS $option_name ) {
	delete_option( $option_name );
	delete_site_option( $option_name );// for site options in Multisite
}

// drop a custom database table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . Masburti_Flickr_Gallery_Plugin::PHOTOSETS_TABLE_NAME );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . Masburti_Flickr_Gallery_Plugin::PHOTOS_TABLE_NAME );