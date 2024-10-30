<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Admin' ) ) {
	return;
}

/**
 * Class Masburti_Flickr_Gallery_Admin
 */
class Masburti_Flickr_Gallery_Admin {
	/**
	 * Finishes OAuth process - work with callback from Flickr Authentication service
	 */
	private static function settings_finish_oauth() {
		if ( isset( $_GET['oauth_token'] ) && get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN ) == '' ) {
			$oauth_token        = $_GET['oauth_token'];
			$oauth_verifier     = $_GET['oauth_verifier'];
			$oauth_token_secret = get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_OAUTH_TOKEN_SECRET );
			$flickr_api         = new Masburti_Flickr_API( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY ), get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY ), new Masburti_Flickr_Access_Token( '', '', '', '', '' ) );

			$access_token = $flickr_api->get_access_token( $oauth_token, $oauth_verifier, $oauth_token_secret );
			if ( $access_token ) {
				$new_access_token = new Masburti_Flickr_Access_Token( $access_token['fullname'], $access_token['oauth_token'], $access_token['oauth_token_secret'], $access_token['user_nsid'], $access_token['username'] );

				if ( $new_access_token->fullname != '' ) {
					update_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN, serialize( $new_access_token ) );
				}
			}
		}
	}

	/**
	 * Works with POST data sent during settings process
	 */
	private static function settings_post() {
		if ( isset( $_POST['form-name'] ) ) {
			if ( $_POST['form-name'] == 'set_api_keys' ) {
				check_admin_referer( 'set_api_keys' );

				update_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY, $_POST['key'] );
				update_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY, $_POST['secret'] );
			} elseif ( $_POST['form-name'] == 'revoke_authentication' ) {
				check_admin_referer( 'revoke_authentication' );

				update_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN, '' );
			}
		}
	}

	/**
	 * Controller for plugin settings page
	 */
	public static function settings() {
		self::settings_post();
		self::settings_finish_oauth();

		$oauth_url    = null;
		$access_token = null;

		if ( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN ) == '' ) {
			$flickr_api = new Masburti_Flickr_API( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY ), get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY ), new Masburti_Flickr_Access_Token( '', '', '', '', '' ) );
			$oauth      = $flickr_api->get_request_token();

			if ( $oauth ) {
				update_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_OAUTH_TOKEN_SECRET, $oauth['oauth_token_secret'] );
				$oauth_url = $flickr_api->get_user_authorization_URL( $oauth['oauth_token'] );
			}
		} else {
			/* @var Masburti_Flickr_Access_Token $access_token */
			$access_token = unserialize( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN, new Masburti_Flickr_Access_Token( '', '', '', '', '' ) ) );

		}

		Masburti_Flickr_Gallery_Admin_Views::display_settings_api();
		if ( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN ) == '' ) {
			Masburti_Flickr_Gallery_Admin_Views::display_settings_auth( $oauth_url );
		} else {
			Masburti_Flickr_Gallery_Admin_Views::display_settings_revoke_auth( $access_token );
		}
		Masburti_Flickr_Gallery_Admin_Views::display_flickr_notice();
		Masburti_Flickr_Gallery_Admin_Views::display_donate_button();
	}

	//ToDO later: transform fetching from Flickr into asynchronous

	/**
	 * Imports all photosets with given ids
	 *
	 * @param Masburti_Flickr_API $flickr_api
	 * @param array $photosets_flickr_ids
	 */
	private static function management_import_photosets( $flickr_api, $photosets_flickr_ids ) {
		foreach ( $photosets_flickr_ids AS $photoset_flickr_id ) {  //ToDO later: limit to adding 10k photos at once
			$photoset = $flickr_api->get_photoset_info( $photoset_flickr_id );
			if ( $photoset ) {
				//Delete photoset if exists in database
				Masburti_Flickr_Gallery_Helpers::delete_photoset_from_db( $photoset->id );

				//Adds photoset to database
				$photoset_insert_id = Masburti_Flickr_Gallery_Helpers::add_photoset_to_db( $photoset );

				if ( $photoset_insert_id ) {
					Masburti_Flickr_Gallery_Helpers::fetch_photoset_photos_to_db( $flickr_api, $photoset, $photoset_insert_id );
				}
			}
		}
	}

	/**
	 * Refresh given photosets - refresh cover photo and re-import all photos
	 *
	 * @param Masburti_Flickr_API $flickr_api
	 * @param array $photosets_flickr_ids
	 */
	private static function management_refresh_photosets( $flickr_api, $photosets_flickr_ids ) {
		foreach ( $photosets_flickr_ids AS $photoset_flickr_id ) {
			$photoset       = $flickr_api->get_photoset_info( $photoset_flickr_id );
			$photoset_db_id = Masburti_Flickr_Gallery_Helpers::get_photoset_id_from_db( $photoset_flickr_id );
			if ( $photoset ) {
				//Refresh photoset cover photo
				Masburti_Flickr_Gallery_Helpers::set_photoset_cover_photo( $photoset_flickr_id, $photoset->cover );

				//Delete photoset photos
				Masburti_Flickr_Gallery_Helpers::delete_photos_from_db( $photoset_flickr_id );

				//Fetch photoset photos
				Masburti_Flickr_Gallery_Helpers::fetch_photoset_photos_to_db( $flickr_api, $photoset, $photoset_db_id );
			}
		}
	}

	/**
	 * Remove all photosets with given ids
	 *
	 * @param array $photosets_flickr_ids - photosets id
	 */
	private static function management_remove_photosets( $photosets_flickr_ids ) {
		foreach ( $photosets_flickr_ids AS $photoset_flickr_id ) {
			Masburti_Flickr_Gallery_Helpers::delete_photoset_from_db( $photoset_flickr_id, true );
		}
	}

	/**
	 * Works with POST data sent during management process
	 *
	 * @param Masburti_Flickr_API $flickr_api
	 */
	private static function management_post( $flickr_api ) {
		if ( isset( $_POST['form-name'] ) ) {
			if ( $_POST['form-name'] == 'photosets_form' ) {
				check_admin_referer( 'photosets_form' );

				if ( $_POST['action2'] == 'import' ) {
					self::management_import_photosets( $flickr_api, $_POST['photosets'] );
				} elseif ( $_POST['action2'] == 'remove' ) {
					self::management_remove_photosets( $_POST['photosets'] );
				} elseif ( $_POST['action2'] == 'refresh' ) {
					self::management_refresh_photosets( $flickr_api, $_POST['photosets'] );
				}
			}
		}
	}

	/**
	 * Works with GET data sent during management process
	 *
	 * @param Masburti_Flickr_API $flickr_api
	 */
	private static function management_get( $flickr_api ) {
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		$pid    = isset( $_GET['pid'] ) ? $_GET['pid'] : '';
		if ( $action != '' && $pid != '' ) {
			check_admin_referer( $action . '-pid_' . $pid );

			if ( $action == 'import' ) {
				self::management_import_photosets( $flickr_api, array( $pid ) );
			} elseif ( $action == 'remove' ) {
				self::management_remove_photosets( array( $pid ) );
			} elseif ( $action == 'refresh' ) {
				self::management_refresh_photosets( $flickr_api, array( $pid ) );
			}
		}
	}

	/**
	 * Controller for plugin management page
	 */
	public static function management() {
		/** @var Masburti_Flickr_Access_Token $access_token */
		$access_token = unserialize( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN, new Masburti_Flickr_Access_Token( '', '', '', '', '' ) ) );
		if ( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_ACCESS_TOKEN ) != '' && $access_token->user_nsid != '' ) {
			$flickr_api = new Masburti_Flickr_API( get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY ), get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY ), $access_token );

			self::management_post( $flickr_api );
			self::management_get( $flickr_api );

			$photosets_from_flickr = $flickr_api->get_photosets_list();
			$photosets_from_db     = array();

			$photosets_in_db = Masburti_Flickr_Gallery_Helpers::get_photosets_from_db( true );

			foreach ( $photosets_in_db AS $single ) {
				$photosets_from_db[ $single->flickr_id ] = $single;
			}

			Masburti_Flickr_Gallery_Admin_Views::display_management( $photosets_from_flickr, $photosets_from_db );
		} else {
			Masburti_Flickr_Gallery_Admin_Views::display_management_authentication_warning();
		}
		Masburti_Flickr_Gallery_Admin_Views::display_flickr_notice();
		Masburti_Flickr_Gallery_Admin_Views::display_donate_button();
		Masburti_Flickr_Gallery_Admin_Views::display_shortcodes_info();
	}
}