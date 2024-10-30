<?php
if ( class_exists( 'Masburti_Flickr_Access_Token' ) ) {
	return;
}

/**
 * Created by PhpStorm.
 * User: Filip Kula
 */
class Masburti_Flickr_Access_Token {
	public $fullname;
	public $oauth_token;
	public $oauth_token_secret;
	public $user_nsid;
	public $username;

	/**
	 * FlickrAccessToken constructor.
	 *
	 * @param $fullname
	 * @param $oauth_token
	 * @param $oauth_token_secret
	 * @param $user_nsid
	 * @param $username
	 */
	public function __construct( $fullname, $oauth_token, $oauth_token_secret, $user_nsid, $username ) {
		$this->fullname           = $fullname;
		$this->oauth_token        = $oauth_token;
		$this->oauth_token_secret = $oauth_token_secret;
		$this->user_nsid          = $user_nsid;
		$this->username           = $username;
	}
}
