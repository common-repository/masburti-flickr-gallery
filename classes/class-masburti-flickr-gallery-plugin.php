<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Plugin' ) ) {
	return;
}

/**
 * Class Masburti_Flickr_Gallery_Plugin
 */
class Masburti_Flickr_Gallery_Plugin {
	const DB_VERSION = '1.1';   //a version number for database table structure

	const PHOTOSETS_TABLE_NAME = 'masburti_flickr_gallery_photosets';
	const PHOTOS_TABLE_NAME = 'masburti_flickr_gallery_photos';

	const OPTION_FLICKR_API_KEY = 'masburti_flickr_gallery_api_key';
	const OPTION_FLICKR_SECRET_KEY = 'masburti_flickr_gallery_secret_key';
	const OPTION_FLICKR_OAUTH_TOKEN_SECRET = 'masburti_flickr_gallery_oauth_token_secret';
	const OPTION_FLICKR_ACCESS_TOKEN = 'masburti_flickr_gallery_access_token';
	const OPTION_DB_VERSION = 'masburti_flickr_gallery_db_version';

	/**
	 * Do everything, what plugin need to do to work - add actions, filters, shortcodes
	 * Masburti_Flickr_Gallery_Plugin constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', 'Masburti_Flickr_Gallery_Plugin::init' );
		add_action( 'wp_enqueue_scripts', 'Masburti_Flickr_Gallery_Plugin::enqueue_scripts' );
		add_action( 'admin_menu', 'Masburti_Flickr_Gallery_Plugin::add_pages' );

		$prefix   = is_network_admin() ? 'network_admin_' : '';
		$basename = plugin_basename( dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
		add_filter( $prefix . 'plugin_action_links_' . $basename, 'Masburti_Flickr_Gallery_Plugin::add_action_links', 10, 4 );

		$shortcode = new Masburti_Flickr_Gallery_Shortcode();
		add_shortcode( 'masburti_flickr_gallery', array( $shortcode, 'init' ) );
		add_shortcode( 'masburti_flickr_gallery_single', array( $shortcode, 'deprecated' ) );

		add_action( 'wp_ajax_mfg_photoset_show', 'Masburti_Flickr_Gallery_Plugin::shortcode_ajax' );
		add_action( 'wp_ajax_nopriv_mfg_photoset_show', 'Masburti_Flickr_Gallery_Plugin::shortcode_ajax' );
	}

	/**
	 * Initialize plugin    (action: plugins_loaded)
	 */
	public static function init() {
		$plugin_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages';
		load_plugin_textdomain( 'masburti-flickr-gallery', false, $plugin_dir );
	}

	/**
	 * Create plugin database structure
	 */
	public static function install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql_query = "CREATE TABLE IF NOT EXISTS " . Masburti_Flickr_Gallery_Helpers::get_photosets_table_name() . " (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`flickr_id` varchar(22) NOT NULL,
			`photos` int(11) NOT NULL,
			`cover_flickr_id` varchar(22) NOT NULL, 
			`date_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			`date_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			`date_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			`date_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			`serialized` text NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE (`flickr_id`)
	) $charset_collate";
		dbDelta( $sql_query );

		$sql_query = "CREATE TABLE IF NOT EXISTS " . Masburti_Flickr_Gallery_Helpers::get_photos_table_name() . " (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`flickr_id` varchar(22) NOT NULL,
			`photoset_id` varchar(22) NOT NULL,
			`secret` varchar(20) NOT NULL,
			`server` varchar(10) NOT NULL,
			`farm` varchar(10) NOT NULL,
			`date_taken` DATETIME NOT NULL,
			`serialized` text NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE (`flickr_id`)
	) $charset_collate";
		dbDelta( $sql_query );

		add_option( self::OPTION_DB_VERSION, self::DB_VERSION );
	}

	/**
	 * Add all new options to database
	 */
	public static function install_data() {
		add_option( self::OPTION_FLICKR_API_KEY, '' );
		add_option( self::OPTION_FLICKR_SECRET_KEY, '' );

		add_option( self::OPTION_FLICKR_OAUTH_TOKEN_SECRET, '' );
		add_option( self::OPTION_FLICKR_ACCESS_TOKEN, '' );
	}

	/**
	 * Add action links to plugin details on plugins list
	 *
	 * @param $actions
	 *
	 * @param $plugin_file
	 *
	 * @param $plugin_data
	 * @param $context
	 *
	 * @return array
	 */
	public static function add_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		$custom_actions = array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=masburti-flickr-gallery-settings' ) . '">' . __( 'Settings', 'masburti-flickr-gallery' ) . '</a>',
			'import'   => '<a href="' . admin_url( 'tools.php?page=masburti-flickr-gallery-management' ) . '">' . __( 'Import', 'masburti-flickr-gallery' ) . '</a>',
		);

		return array_merge( $actions, $custom_actions );
	}

	/**
	 * Enqueue plugin script
	 */
	public static function enqueue_scripts() {
		wp_register_style( 'masburti-flickr-gallery-style', plugins_url( 'include/style.css', dirname( __FILE__ ) ), array(), '1.1.0', 'all' );
		wp_register_script( 'masburti-flickr-gallery-gallery_script', plugins_url( 'include/gallery_script.js', dirname( __FILE__ ) ), array(), '1.1.0' );
		wp_register_script( 'masburti-flickr-gallery-single_script', plugins_url( 'include/single_script.js', dirname( __FILE__ ) ), array(), '1.1.0' );
		wp_register_style( 'masburti-flickr-gallery-style-colorbox', plugins_url( 'include/colorbox.css', dirname( __FILE__ ) ), array(), '1.0.0', 'all' );
		wp_register_script( 'masburti-flickr-gallery-script-colorbox', plugins_url( 'include/jquery.colorbox-min.js', dirname( __FILE__ ) ), array(), '1.0.0' );
	}

	/**
	 * Add WordPress admin panel pages
	 */
	public static function add_pages() {
		add_management_page( __( 'Masburti Flickr Gallery importer', 'masburti-flickr-gallery' ), __( 'Masburti Flickr Gallery importer', 'masburti-flickr-gallery' ), 'manage_options', 'masburti-flickr-gallery-management', 'Masburti_Flickr_Gallery_Admin::management' );
		add_options_page( __( 'Masburti Flickr Gallery settings', 'masburti-flickr-gallery' ), __( 'Masburti Flickr Gallery', 'masburti-flickr-gallery' ), 'manage_options', 'masburti-flickr-gallery-settings', 'Masburti_Flickr_Gallery_Admin::settings' );
	}

	public static function shortcode_ajax() {
		$photoset_flickr_id = isset( $_GET['photoset_flickr_id'] ) ? $_GET['photoset_flickr_id'] : - 1;
		$photos_columns     = isset( $_GET['thumbnails_cols'] ) ? $_GET['thumbnails_cols'] : 8;

		$shortcode = new Masburti_Flickr_Gallery_Shortcode();
		echo $shortcode->init( array(
			'type'        => Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSET_PHOTOS,
			'flickr_id'   => $photoset_flickr_id,
			'photos_cols' => $photos_columns
		) );

		wp_die();
	}
}