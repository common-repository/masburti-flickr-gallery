<?php
if ( class_exists( 'Masburti_Flickr_API' ) ) {
	return;
}

include_once "class-masburti-flickr-object.php";

/**
 * Created by PhpStorm.
 * User: Filip Kula
 */
class Masburti_Flickr_Photo extends Masburti_Flickr_Object {
	public $id;
	public $secret;
	public $server;
	public $farm;
	public $dateupload;
	private $isfavorite;
	private $license;
	private $safety_level;
	private $rotation;
	private $originalsecret;
	private $originalformat;
	private $owner = array(
		'nsid'       => '',
		'username'   => '',
		'realname'   => '',
		'location'   => '',
		'iconserver' => '',
		'iconfarm'   => '',
		'path_alias' => ''
	);
	private $title = array(
		'_content' => ''
	);
	private $description = array(
		'_content' => ''
	);
	private $visibility = array(
		'ispublic' => 0,
		'isfriend' => 0,
		'isfamily' => 0
	);
	public $dates = array(
		'posted'           => '',
		'taken'            => '',
		'takengranularity' => '',
		'takenunknown'     => '',
		'lastupdate'       => '',
	);
	private $permissions = array(
		'permcomment' => 0,
		'permaddmeta' => 0
	);
	private $views;
	private $editability = array(
		'cancomment' => 0,
		'canaddmeta' => 0
	);
	private $publiceditability = array(
		'cancomment' => 0,
		'canaddmeta' => 0
	);
	private $usage = array(
		'candownload' => 0,
		'canblog'     => 0,
		'canprint'    => 0,
		'canshare'    => 0
	);
	private $comments = array(
		'_content' => ''
	);
	private $notes = array(
		'note' => array()
	);
	private $people = array(
		'haspeople' => 0
	);
	private $tags = array(
		'tag' => array()
	);
	private $urls = array(
		'url' => array()
	);
	private $media;

	/**
	 * Photo sizes:
	 * z - cover of photoset on photosets list
	 * m - thumbnail on photoset photos list
	 * b/h - photo in lightbox / fancybox
	 */
	const SIZE_SMALL = 't';
	const SIZE_MEDIUM = 'm';
	const SIZE_NORMAL = 'n';
	const SIZE_LARGE = 'z';

	/**
	 * FlickrPhoto constructor.
	 *
	 * @param $originArray
	 */
	public function __construct( $originArray ) {
		foreach ( $originArray AS $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			} else {
				if ( substr( $key, 0, 4 ) == 'date' ) {
					$key = substr( $key, 4 );
					if ( array_key_exists( $key, $this->dates ) ) {
						$this->dates[ $key ] = $value;
					}
				}
			}
		}

		if ( is_numeric( $this->dateupload ) ) {
			$this->dateupload = date( 'Y-m-d H:i:s', $this->dateupload );
		}
	}

	/**
	 * Function return direct URL to photo (available without any limitations)
	 *
	 * @param string|int $size
	 *
	 * @return string
	 */
	public function get_photo_url( $size = self::SIZE_MEDIUM ) {
		if ( is_int( $size ) ) {
			if ( $size <= 100 ) {
				$size = self::SIZE_SMALL;
			} elseif ( $size <= 240 ) {
				$size = self::SIZE_MEDIUM;
			} elseif ( $size <= 320 ) {
				$size = self::SIZE_NORMAL;
			} elseif ( $size <= 640 ) {
				$size = self::SIZE_LARGE;
			}
		}

		return 'https://farm' . $this->farm . '.staticflickr.com/' . $this->server . '/' . $this->id . '_' . $this->secret . '_' . $size . '.jpg';
	}

	/**
	 * Function return direct URL to photo (available without any limitations)
	 *
	 * @param $id
	 * @param $farm
	 * @param $server
	 * @param $secret
	 * @param string $size
	 *
	 * @return string
	 */
	public static function get_url( $id, $farm, $server, $secret, $size = 'm' ) {
		if ( is_int( $size ) ) {
			if ( $size <= 100 ) {
				$size = self::SIZE_SMALL;
			} elseif ( $size <= 240 ) {
				$size = self::SIZE_MEDIUM;
			} elseif ( $size <= 320 ) {
				$size = self::SIZE_NORMAL;
			} elseif ( $size <= 500 ) {
				$size = '-';
			}
		}

		return 'https://farm' . $farm . '.staticflickr.com/' . $server . '/' . $id . '_' . $secret . '_' . $size . '.jpg';
	}

	/**
	 * @return array
	 */
	public function get_title() {
		return $this->title['_content'];
	}
}