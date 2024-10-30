<?php
include_once 'PHPFlickr/class-masburti-flickr-access-token.php';
include_once 'PHPFlickr/class-masburti-flickr-object.php';
include_once 'PHPFlickr/class-masburti-flickr-photo.php';
include_once 'PHPFlickr/class-masburti-flickr-photoset.php';

if ( class_exists( 'Masburti_Flickr_API' ) ) {
	return;
}

/**
 * Created by PhpStorm.
 * User: Filip Kula
 */
class Masburti_Flickr_API {
	private $api_key = '';
	private $secret_key = '';
	private $format = '';
	private $api_url = 'https://api.flickr.com/services/rest/?';
	private $oauth_url = 'https://www.flickr.com/services/oauth/';
	private $access_token = '';
	const PHOTOS_PER_PAGE = 500;

	/**
	 * FlickrAPI constructor.
	 *
	 * @param $api_key - Flickr application key
	 * @param $secret_key - Flickr secret key
	 * @param Masburti_Flickr_Access_Token $access_key
	 */
	public function __construct( $api_key, $secret_key, $access_key ) {
		$this->api_key      = $api_key;
		$this->secret_key   = $secret_key;
		$this->format       = 'php_serial';
		$this->access_token = $access_key;
	}

	/**
	 * Function signing request
	 *
	 * @param $url
	 * @param $params
	 * @param $token_secret
	 *
	 * @return string
	 */
	public function sign( $url, $params, $token_secret ) {
		$key = $this->secret_key . '&' . $token_secret;

		ksort( $params );
		$encoded_params = array();
		foreach ( $params as $k => $v ) {
			$encoded_params[] = rawurlencode( $k ) . '=' . rawurlencode( $v );
		}

		$url = 'GET&' . rawurlencode( $url ) . '&' . rawurlencode( implode( '&', $encoded_params ) );

		return base64_encode( hash_hmac( 'sha1', $url, $key, true ) );
	}

	/**
	 * Function requesting Token
	 * @return array|bool
	 */
	public function get_request_token() {
		if ( $this->api_key == '' || $this->secret_key == '' ) {
			return false;
		}

		$params = array(
			'oauth_callback'         => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'oauth_consumer_key'     => $this->api_key,
			'oauth_nonce'            => base64_encode( time() * 100 + rand( 0, 99 ) ),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_version'          => '1.0',
		);

		$params['oauth_signature'] = $this->sign( $this->oauth_url . 'request_token/', $params, '' );

		$encoded_params = array();
		foreach ( $params as $k => $v ) {
			$encoded_params[] = urlencode( $k ) . '=' . urlencode( $v );
		}

		$url = $this->oauth_url . 'request_token/?' . implode( '&', $encoded_params );

		$rsp = file_get_contents( $url );

		if ( $http_response_header[0] == 'HTTP/1.0 401 Unauthorized' ) {
			return false;
		}

		$result = array();
		parse_str( $rsp, $result );

		return $result;
	}

	/**
	 * Function returning User Authorization URL
	 *
	 * @param $token
	 *
	 * @return string
	 */
	public function get_user_authorization_URL( $token ) {
		return $this->oauth_url . 'authorize?perms=read&oauth_token=' . $token;
	}

	/**
	 * Function returning access token using OAuth token and verifier
	 *
	 * @param $token OAuth token
	 * @param $verifier OAuth verifier
	 * @param $token_secret
	 *
	 * @return array|bool
	 */
	public function get_access_token( $token, $verifier, $token_secret ) {
		$params = array(
			'oauth_consumer_key'     => $this->api_key,
			'oauth_nonce'            => base64_encode( time() * 100 + rand( 0, 99 ) ),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $token,
			'oauth_verifier'         => $verifier,
			'oauth_version'          => '1.0',
		);

		$params['oauth_signature'] = $this->sign( $this->oauth_url . 'access_token/', $params, $token_secret );

		$encoded_params = array();
		foreach ( $params as $k => $v ) {
			$encoded_params[] = urlencode( $k ) . '=' . urlencode( $v );
		}

		$url = $this->oauth_url . 'access_token/?' . implode( '&', $encoded_params );

		$rsp = file_get_contents( $url );

		if ( $http_response_header[0] == 'HTTP/1.0 401 Unauthorized' ) {
			return false;
		}

		$result = array();
		parse_str( $rsp, $result );

		return $result;
	}

	/**
	 * Function calling passed method with data encryption
	 *
	 * @param $method
	 * @param $token
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function call( $method, $token, $params = array() ) {
		$oauth_params = array(
			'oauth_consumer_key'     => $this->api_key,
			'oauth_nonce'            => base64_encode( time() * 100 + rand( 0, 99 ) ),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $token,
			'oauth_version'          => '1.0',
		);

		$params['method']  = $method;
		$params['api_key'] = $this->api_key;
		$params['format']  = $this->format;

		$params = array_merge( $params, $oauth_params );

		$encoded_params = array();
		foreach ( $params as $k => $v ) {
			$encoded_params[] = urlencode( $k ) . '=' . urlencode( $v );
		}

		$url = $this->api_url . implode( '&', $encoded_params );
		$rsp = @file_get_contents( $url );

		if ( $rsp === false ) {
			return null;
		}

		return unserialize( $rsp );
	}

	/**
	 * Gets photosets list from Flickr service
	 */
	public function get_photosets_list() {
		$params = array(
			'user_id' => $this->access_token->user_nsid
		);
		$result = $this->call( 'flickr.photosets.getList', $this->access_token->oauth_token, $params );

		if ( ! isset( $result['photosets']['photoset'] ) ) {
			return array();
		}

		$photosets = array();
		foreach ( $result['photosets']['photoset'] AS $r ) {
			$photosets[] = new Masburti_Flickr_Photoset( $r );
		}

		return $photosets;
	}

	/**
	 * Gets information about single photoset
	 *
	 * @param int $photoset_flickr_id
	 *
	 * @return bool|Masburti_Flickr_Photoset
	 */
	public function get_photoset_info( $photoset_flickr_id ) {
		$params = array(
			'photoset_id' => $photoset_flickr_id,
			'user_id'     => $this->access_token->user_nsid
		);
		$result = $this->call( 'flickr.photosets.getInfo', $this->access_token->oauth_token, $params );

		if ( isset( $result['photoset'] ) ) {
			$photoset = new Masburti_Flickr_Photoset( $result['photoset'] );

			$params = array(
				'photoset_id'    => $photoset_flickr_id,
				'user_id'        => $this->access_token->user_nsid,
				'privacy_filter' => 5,
				'per_page'       => 1,
				'page'           => 1
			);
			$result = $this->call( 'flickr.photosets.getPhotos', $this->access_token->oauth_token, $params );
			if ( isset( $result['photoset'] ) && isset( $result['stat'] ) ) {
				$photoset->cover = $result['photoset']['primary'];
			}

			return $photoset;
		}

		return false;
	}

	/**
	 * Gets all photos from single photoset
	 *
	 * @param Masburti_Flickr_Photoset $photoset
	 *
	 * @return array
	 */
	public function get_photoset_photos( $photoset ) {
		$photos = array();
		$page   = 1;
		while ( $photoset->photos > 0 ) {
			$params = array(
				'photoset_id' => $photoset->id,
				'user_id'     => $this->access_token->user_nsid,
				'per_page'    => self::PHOTOS_PER_PAGE,
				'page'        => $page,
				'extras'      => 'date_upload,date_taken'
			);
			$result = $this->call( 'flickr.photosets.getPhotos', $this->access_token->oauth_token, $params );

			if ( isset( $result['photoset'] ) && isset( $result['stat'] ) ) {
				if ( isset( $result['photoset']['photo'] ) && $result['stat'] == 'ok' ) {
					$photos = array_merge( $photos, $result['photoset']['photo'] );
					$page ++;
					$photoset->photos -= self::PHOTOS_PER_PAGE;
				} else {
					break;
				}
			} else {
				break;
			}
		}

		return $photos;
	}

	/**
	 * Generate static url for specified photo
	 *
	 * @param $photo
	 * @param string $size
	 *
	 * @return string
	 */
	public function get_photo_url( $photo, $size = 'm' ) {
		if ( is_int( $size ) ) {
			if ( $size <= 100 ) {
				$size = 't';
			} elseif ( $size <= 240 ) {
				$size = 'm';
			} elseif ( $size <= 320 ) {
				$size = 'n';
			} elseif ( $size <= 500 ) {
				$size = '-';
			}
		}
		$url = 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_' . $size . '.jpg';

		return $url;
	}
}