<?php
if ( class_exists( 'Masburti_Flickr_Photoset' ) ) {
	return;
}

include_once "class-masburti-flickr-object.php";

/**
 * Created by PhpStorm.
 * User: Filip Kula
 */
class Masburti_Flickr_Photoset extends Masburti_Flickr_Object {
	public $id;
	private $primary;
	private $secret;
	private $server;
	private $farm;
	public $photos = 0;
	private $videos = 0;
	private $title = array(
		'_content' => ''
	);
	private $description = array(
		'_content' => ''
	);
	public $cover;
	private $needs_interstitial;
	private $visibility_can_see_set;
	private $count_views;
	private $count_comments;
	private $can_comment;
	public $date_create;
	public $date_update;

	/**
	 * FlickrPhotoset constructor.
	 *
	 * @param $origin_array
	 */
	public function __construct( $origin_array ) {
		foreach ( $origin_array AS $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_title() {
		return $this->title['_content'];
	}

	/**
	 * @return array
	 */
	public function get_description() {
		return $this->description['_content'];
	}
}