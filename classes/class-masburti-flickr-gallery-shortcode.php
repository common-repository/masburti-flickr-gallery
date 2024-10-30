<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Shortcode' ) ) {
	return;
}

/**
 * Class Masburti_Flickr_Gallery_Shortcode
 */
class Masburti_Flickr_Gallery_Shortcode {
	const ATTRIBUTE_TYPE = 'type';
	const ATTRIBUTE_ID = 'id';
	const ATTRIBUTE_FLICKR_ID = 'flickr_id';
	const ATTRIBUTE_COLUMNS = 'cols';
	const ATTRIBUTE_PHOTOS_COLUMNS = 'photos_cols';

	const TYPE_PHOTOSETS_LIST = 'albums-list';
	const TYPE_PHOTOSET_PHOTOS = 'album-photos';
	const TYPE_SINGLE_PHOTO = 'photo';

	const DEFAULT_COLS_IN_LIST = 2;
	const DEFAULT_COLS_IN_PHOTOS = 4;

	/**
	 * Masburti_Flickr_Gallery_Shortcode constructor.
	 */
	public function __construct() {

	}

	/**
	 * Plugin shortcode - only one, but with many options
	 *
	 * @param $attributes
	 *
	 * @return string HTML
	 */
	public function init( $attributes ) {
		$attributes = array_change_key_case( (array) $attributes, CASE_LOWER );    // normalize attribute keys, lowercase

		$args = shortcode_atts( array(
			self::ATTRIBUTE_TYPE           => self::TYPE_PHOTOSETS_LIST,
			self::ATTRIBUTE_ID             => null,
			self::ATTRIBUTE_FLICKR_ID      => null,
			self::ATTRIBUTE_COLUMNS        => self::DEFAULT_COLS_IN_LIST,
			self::ATTRIBUTE_PHOTOS_COLUMNS => self::DEFAULT_COLS_IN_PHOTOS
		), $attributes );

		switch ( $args[ self::ATTRIBUTE_TYPE ] ) {
			case self::TYPE_PHOTOSETS_LIST:
				return $this->get_photosets_list( $args[ self::ATTRIBUTE_COLUMNS ], $args[ self::ATTRIBUTE_PHOTOS_COLUMNS ] );
			case self::TYPE_PHOTOSET_PHOTOS:
				return $this->get_photoset_photos( $args[ self::ATTRIBUTE_ID ], $args[ self::ATTRIBUTE_FLICKR_ID ], $args[ self::ATTRIBUTE_PHOTOS_COLUMNS ] );
			case self::TYPE_SINGLE_PHOTO:
				return $this->get_photo( $args[ self::ATTRIBUTE_ID ], $args[ self::ATTRIBUTE_FLICKR_ID ] );
		}

		return null;
	}

	/**
	 * Plugin DEPRECATED shortcode
	 * @deprecated
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function deprecated( $attributes ) {
		$attributes = array_change_key_case( (array) $attributes, CASE_LOWER );    // normalize attribute keys, lowercase

		$args = shortcode_atts( array(
			'id'   => null,
			'cols' => 4
		), $attributes );

		return $this->get_photoset_photos( $args['id'], null, $args['cols'] );
	}

	/**
	 * Enqueue scripts for shortcodes
	 *
	 * @param bool $photosets - Enqueue scripts for photosets?
	 * @param array $additional_script_parameters - Additional parameters to localize gallery script
	 */
	private function enqueue_scripts( $photosets, $additional_script_parameters = array() ) {
		wp_enqueue_style( 'masburti-flickr-gallery-style' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'masburti-flickr-gallery-style-colorbox' );
		wp_enqueue_script( 'masburti-flickr-gallery-script-colorbox' );
		$script_for_localize = 'masburti-flickr-gallery-single_script';
		if ( $photosets ) {
			wp_enqueue_script( 'masburti-flickr-gallery-gallery_script' );
			$script_for_localize = 'masburti-flickr-gallery-gallery_script';
		} else {
			wp_enqueue_script( 'masburti-flickr-gallery-single_script' );
		}
		wp_localize_script( $script_for_localize, 'ajax_object', array_merge( array(
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'current_image'   => __( 'image', 'masburti-flickr-gallery' ),
			'current_of'      => __( 'of', 'masburti-flickr-gallery' ),
			'previous'        => __( 'previous', 'masburti-flickr-gallery' ),
			'next'            => __( 'next', 'masburti-flickr-gallery' ),
			'slideshowStart'  => __( 'start slideshow', 'masburti-flickr-gallery' ),
			'slideshowStop'   => __( 'stop slideshow', 'masburti-flickr-gallery' ),
			'close'           => __( 'close', 'masburti-flickr-gallery' ),
			'xhrError'        => __( 'This content failed to load.', 'masburti-flickr-gallery' ),
			'imgError'        => __( 'This image failed to load.', 'masburti-flickr-gallery' ),
			'thumbnails_cols' => 7
		), $additional_script_parameters ) );
	}

	/**
	 * Return HTML width thumbnails of photosets
	 *
	 * @param $photosets_in_row - quantity of photosets columns in single row
	 * @param $photos_in_row - quantity of photos columns in single row
	 *
	 * @return string HTML
	 */
	private function get_photosets_list( $photosets_in_row, $photos_in_row ) {
		$this->enqueue_scripts( true, array( 'thumbnails_cols' => $photos_in_row ) );

		$photosets = Masburti_Flickr_Gallery_Helpers::get_photosets_from_db();

		$display_photosets = array();
		$col_number        = 0;
		$row_number        = 0;
		foreach ( $photosets AS $single ) {
			/** @var Masburti_Flickr_Photoset $photoset */
			$photoset = unserialize( $single->photoset );
			/** @var Masburti_Flickr_Photo $cover */
			$cover = unserialize( $single->cover );

			if ( $col_number ++ >= $photosets_in_row ) {
				$col_number = 1;
				$row_number ++;
			}

			if ( ! isset( $display_photosets[ $row_number ] ) ) {
				$display_photosets[] = array();
			}

			$display_photosets[ $row_number ][] = array(
				'photoset' => $photoset,
				'cover'    => $cover
			);
		}

		return Masburti_Flickr_Gallery_Views::display_photosets( $display_photosets );
	}

	/**
	 * Return HTML width thumbnails of given photoset
	 *
	 * @param $album_id - photoset ID in database
	 * @param $photoset_flickr_id - photoset ID on Flickr
	 * @param $photos_columns - quantity of columns in one row
	 *
	 * @return string
	 */
	private function get_photoset_photos( $album_id, $photoset_flickr_id, $photos_columns ) {
		$this->enqueue_scripts( false );

		if ( is_null( $photoset_flickr_id ) ) {
			$photoset_flickr_id = Masburti_Flickr_Gallery_Helpers::get_photoset_flickr_id_from_db( $album_id );
		}
		$photos = Masburti_Flickr_Gallery_Helpers::get_photos_from_db( $photoset_flickr_id );

		if ( $photos ) {
			$display_photos = array();
			$col_number     = 0;
			$row_number     = 0;
			foreach ( $photos AS $photo ) {
				if ( $col_number ++ >= $photos_columns ) {
					$col_number = 1;
					$row_number ++;
				}

				if ( ! isset( $display_photos[ $row_number ] ) ) {
					$display_photos[] = array();
				}

				$display_photos[ $row_number ][] = $photo;
			}

			return Masburti_Flickr_Gallery_Views::display_photos( $display_photos );
		}

		return 'no photos';
	}

	/**
	 * Return HTML with single thumbnail
	 *
	 * @param $id - Photo ID in database
	 * @param $flickr_id - Photo ID on Flickr
	 *
	 * @return string
	 */
	private function get_photo( $id, $flickr_id ) {
		$this->enqueue_scripts( false );

		$photo = Masburti_Flickr_Gallery_Helpers::get_photo_from_db( $id, $flickr_id );

		if ( ! is_null( $photo ) ) {
			return Masburti_Flickr_Gallery_Views::display_photo( $photo );
		}

		return 'no photo';
	}
}