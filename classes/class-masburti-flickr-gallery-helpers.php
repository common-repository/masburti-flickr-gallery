<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Helpers' ) ) {
	return;
}

/**
 * Contains helper methods used in this plugin
 * Class Masburti_Flickr_Gallery_Helpers
 */
class Masburti_Flickr_Gallery_Helpers {
	/**
	 * TODO later: refactor all SQL queries to queries with placeholders
	 * https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#formatting-sql-statements
	 */
	/**
	 * Gets Photosets table name with wpdb prefix
	 * @return string
	 */
	public static function get_photosets_table_name() {
		global $wpdb;

		return $wpdb->prefix . Masburti_Flickr_Gallery_Plugin::PHOTOSETS_TABLE_NAME;
	}

	/**
	 * Gets Photos table name with wpdb prefix
	 * @return string
	 */
	public static function get_photos_table_name() {
		global $wpdb;

		return $wpdb->prefix . Masburti_Flickr_Gallery_Plugin::PHOTOS_TABLE_NAME;
	}

	/**
	 * Checks if the system requirements are met
	 * @return bool True if system requirements are met, false if not
	 */
	public static function requirements_met() {
		global $wp_version;

		if ( version_compare( PHP_VERSION, MASBURTI_FGP_REQUIRED_PHP_VERSION, '<' ) ) {
			return false;
		}
		if ( version_compare( $wp_version, MASBURTI_FGP_REQUIRED_WP_VERSION, '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param bool $raw_with_count
	 *
	 * @return array|null|object
	 */
	public static function get_photosets_from_db( $raw_with_count = false ) {
		global $wpdb;

		if ( $raw_with_count ) {
			return $wpdb->get_results( "SELECT ps.*, count(ph.id) AS photos_count 
				FROM " . self::get_photosets_table_name() . " ps
				LEFT JOIN " . self::get_photos_table_name() . " ph ON ph.photoset_id = ps.flickr_id
				GROUP BY ps.flickr_id" );
		} else {
			return $wpdb->get_results( "SELECT ps.serialized AS photoset, ph.serialized AS cover
				FROM " . self::get_photosets_table_name() . " ps 
				LEFT OUTER JOIN " . self::get_photos_table_name() . " ph ON ps.cover_flickr_id = ph.flickr_id 
				ORDER BY ps.date_start DESC" );
		}
	}

	/**
	 * @param $album_id
	 *
	 * @return null|string
	 */
	public static function get_photoset_flickr_id_from_db( $album_id ) {
		global $wpdb;

		return $wpdb->get_var( "SELECT flickr_id FROM " . self::get_photosets_table_name() . " WHERE id = $album_id" );
	}

	/**
	 * @param $photoset_flickr_id
	 *
	 * @return null|string
	 */
	public static function get_photoset_id_from_db( $photoset_flickr_id ) {
		global $wpdb;

		return $wpdb->get_var( "SELECT id FROM " . self::get_photosets_table_name() . " WHERE flickr_id = $photoset_flickr_id" );
	}

	/**
	 * @param $photoset_flickr_id
	 *
	 * @return array|null|object
	 */
	public static function get_photos_from_db( $photoset_flickr_id ) {
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM " . self::get_photos_table_name() . " WHERE photoset_id = '$photoset_flickr_id'" );
	}

	/**
	 * @param null $photo_db_id
	 * @param null $photo_flickr_id
	 *
	 * @return array|null|object
	 */
	public static function get_photo_from_db( $photo_db_id = null, $photo_flickr_id = null ) {
		global $wpdb;

		if ( ! is_null( $photo_db_id ) ) {
			return $wpdb->get_row( "SELECT * FROM " . self::get_photos_table_name() . " WHERE id = '$photo_db_id'" );
		} elseif ( ! is_null( $photo_flickr_id ) ) {
			return $wpdb->get_row( "SELECT * FROM " . self::get_photos_table_name() . " WHERE flickr_id = '$photo_flickr_id'" );
		} else {
			return null;
		}
	}

	/**
	 * @param $photoset_id
	 * @param $date_start
	 * @param $date_end
	 */
	public static function set_photoset_date_range( $photoset_id, $date_start, $date_end ) {
		global $wpdb;

		$wpdb->update( self::get_photosets_table_name(), array(
			'date_start' => date( 'Y-m-d H:i:s', $date_start ),
			'date_end'   => date( 'Y-m-d H:i:s', $date_end )
		), array( 'id' => $photoset_id ) );
	}

	/**
	 * @param $photoset
	 *
	 * @return int
	 */
	public static function add_photoset_to_db( $photoset ) {
		global $wpdb;
		$wpdb->insert(
			self::get_photosets_table_name(),
			array(
				'flickr_id'       => $photoset->id,
				'photos'          => $photoset->photos,
				'cover_flickr_id' => $photoset->cover,
				'date_create'     => date( 'Y-m-d H:i:s', $photoset->date_create ),
				'date_update'     => date( 'Y-m-d H:i:s', $photoset->date_update ),
				'serialized'      => serialize( $photoset )
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 * @param Masburti_Flickr_API $flickr_api
	 * @param $photoset
	 * @param $photoset_db_id
	 */
	public static function fetch_photoset_photos_to_db( $flickr_api, $photoset, $photoset_db_id ) {
		//Gets photoset photos
		$photos           = $flickr_api->get_photoset_photos( $photoset );
		$photos_to_insert = array();
		//Add photoset photos to database
		if ( $photos ) {
			$date_start = time();
			$date_end   = 1;
			foreach ( $photos AS $photo ) {
				$photo = new Masburti_Flickr_Photo( $photo );

				$photo_time_taken = strtotime( $photo->dates['taken'] );
				if ( $photo_time_taken > 0 ) {
					if ( $date_start > $photo_time_taken ) {
						$date_start = $photo_time_taken;
					}
					if ( $date_end < $photo_time_taken ) {
						$date_end = $photo_time_taken;
					}
				}
				$photos_to_insert[] = $photo;

				if ( sizeof( $photos_to_insert ) >= 250 ) {
					self::add_photos_to_db( $photoset->id, $photos_to_insert );
					$photos_to_insert = array();
				}
			}
			if ( sizeof( $photos_to_insert ) > 0 ) {
				self::add_photos_to_db( $photoset->id, $photos_to_insert );
			}
			self::set_photoset_date_range( $photoset_db_id, $date_start, $date_end );
		}
	}

	/**
	 * @param $photoset_flickr_id
	 * @param $photos
	 */
	public static function add_photos_to_db( $photoset_flickr_id, $photos ) {
		global $wpdb;

		$sql_query = "INSERT INTO " . self::get_photos_table_name() . " (id, flickr_id, photoset_id, secret, server, farm, date_taken, serialized) VALUES ";

		foreach ( $photos as $photo ) {
			$sql_query .= "(NULL, '$photo->id', '$photoset_flickr_id', '$photo->secret', '$photo->server', '$photo->farm', '" . date( 'Y-m-d H:i:s', strtotime( $photo->dates['taken'] ) ) . "', '" . serialize( $photo ) . "'), ";
		}

		$wpdb->query( rtrim( $sql_query, ', ' ) );
	}

	/**
	 * @param $photoset_flickr_id
	 * @param bool $delete_photos
	 */
	public static function delete_photoset_from_db( $photoset_flickr_id, $delete_photos = false ) {
		global $wpdb;
		$wpdb->delete( self::get_photosets_table_name(), array( 'flickr_id' => $photoset_flickr_id ) );
		if ( $delete_photos ) {
			self::delete_photos_from_db( $photoset_flickr_id );
		}
	}

	/**
	 * @param $photoset_flickr_id
	 */
	public static function delete_photos_from_db( $photoset_flickr_id ) {
		global $wpdb;
		$wpdb->delete( self::get_photos_table_name(), array( 'photoset_id' => $photoset_flickr_id ) );
	}

	/**
	 * @param $photoset_flickr_id
	 * @param $cover_photo_flickr_id
	 */
	public static function set_photoset_cover_photo( $photoset_flickr_id, $cover_photo_flickr_id ) {
		global $wpdb;

		$wpdb->update( self::get_photosets_table_name(), array(
			'cover_flickr_id' => $cover_photo_flickr_id,
		), array( 'flickr_id' => $photoset_flickr_id ) );
	}
}