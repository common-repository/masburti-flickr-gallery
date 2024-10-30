<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Views' ) ) {
	return;
}

/**
 * Contains public methods returning view with passed content
 * Class Masburti_Flickr_Gallery_Views
 */
class Masburti_Flickr_Gallery_Views {
	/**
	 * Prints an error that the system requirements weren't met.
	 */
	public static function requirements_error() {
		global $wp_version;
		?>
        <div class="error">
            <p><?php echo MASBURTI_FGP_NAME; ?> error: Your environment doesn't meet all of the system requirements
                listed below.</p>

            <ul class="ul-disc">
                <li>
                    <strong>PHP <?php echo MASBURTI_FGP_REQUIRED_PHP_VERSION; ?>+</strong>
                    <em>(You're running version <?php echo PHP_VERSION; ?>)</em>
                </li>

                <li>
                    <strong>WordPress <?php echo MASBURTI_FGP_REQUIRED_WP_VERSION; ?>+</strong>
                    <em>(You're running version <?php echo esc_html( $wp_version ); ?>)</em>
                </li>
            </ul>

            <p>
                If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you
                need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the
                    Codex</a>.
            </p>
        </div>
		<?php
	}

	/**
	 * Returns HTML with rows containing cells with photoset
	 *
	 * @param $photosets - rows with photosets in single cells
	 *
	 * @return string HTML
	 */
	public static function display_photosets( $photosets ) {
		ob_start();
		?>
        <div class="masburti_photoset_display">
            <h2 class="masburti_photoset_title" style="display: none"></h2>
            <p class="masburti_photoset_back" style="display: none">
                &laquo;<?php _e( 'Back', 'masburti-flickr-gallery' ) ?></p>
            <div class="masburti_photoset_loading" style="display: none">
                <img src="<?php echo plugins_url( 'images/loading-ring.gif', dirname( __FILE__ ) ) ?>"
                     alt="<?php _e( 'Loading', 'masburti-flickr-gallery' ) ?>"/>
            </div>
            <table class="masburti_photoset_photos" style="display: none"></table>
            <p class="masburti_photoset_back" style="display: none">
                &laquo;<?php _e( 'Back', 'masburti-flickr-gallery' ) ?></p>
        </div>
        <table class="masburti_photosets">
			<?php foreach ( $photosets AS $photoset ): ?>
                <tr class="masburti_photoset_thumb_row">
					<?php foreach ( $photoset AS $item ):
						/** @var Masburti_Flickr_Photo $cover */
						$cover = $item['cover'];
						/** @var Masburti_Flickr_Photoset $photoset */
						$photoset_item = $item['photoset']; ?>
                        <td class="masburti_photoset_image" data-id="<?php echo $photoset_item->id ?>"
                            data-title="<?php echo $photoset_item->get_title() ?>">
                            <img src="<?php echo $cover->get_photo_url( 'z' ) ?>"
                                 alt="<?php echo $photoset_item->get_title() ?>"/>
                        </td>
					<?php endforeach; ?>
                </tr>
                <tr class="masburti_photoset_label_row">
					<?php foreach ( $photoset AS $item ):
						/** @var Masburti_Flickr_Photoset $photoset */
						$photoset_item = $item['photoset']; ?>
                        <td class="masburti_photoset_link" data-id="<?php echo $photoset_item->id ?>">
                            <h4><?php echo $photoset_item->get_title() ?></h4>
                        </td>
					<?php endforeach; ?>
                </tr>
			<?php endforeach; ?>
        </table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Returns HTML with rows containing cells with photoset
	 *
	 * @param array $photos - rows with photos in single cells
	 *
	 * @return string HTML
	 */
	public static function display_photos( $photos ) {
		ob_start();
		?>
        <table class="masburti_photoset_photos">
			<?php foreach ( $photos AS $photo_row ): ?>
                <tr class="masburti_thumbnails_row">
					<?php foreach ( $photo_row AS $photo ): ?>
                        <td class="masburti_photoset_photo">
                            <a href="<?php echo Masburti_Flickr_Photo::get_url( $photo->flickr_id, $photo->farm, $photo->server, $photo->secret, 'b' ) ?>"
                               id="<?php echo $photo->photoset_id ?>-<?php echo $photo->flickr_id ?>" class="colorbox"
                               rel="masburti_fancybox_<?php echo $photo->photoset_id ?>">
                                <img src="<?php echo Masburti_Flickr_Photo::get_url( $photo->flickr_id, $photo->farm, $photo->server, $photo->secret, 'm' ) ?>"
                                     id="masburti_thumbnail_<?php echo $photo->flickr_id ?>"/>
                            </a>
                        </td>
					<?php endforeach; ?>
                </tr>
			<?php endforeach; ?>
        </table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return HTML with single thumbnail
	 *
	 * @param $photo
	 * @param string $thumbnail_size
	 *
	 * @return string
	 */
	public static function display_photo( $photo, $thumbnail_size = Masburti_Flickr_Photo::SIZE_MEDIUM ) {
		ob_start();
		?>
        <a href="<?php echo Masburti_Flickr_Photo::get_url( $photo->flickr_id, $photo->farm, $photo->server, $photo->secret, 'b' ) ?>"
           id="<?php echo $photo->photoset_id ?>-<?php echo $photo->flickr_id ?>" class="colorbox"
           rel="masburti_fancybox_<?php echo $photo->photoset_id ?>">
            <img id="masburti_thumbnail_<?php echo $photo->flickr_id ?>"
                 src="<?php echo Masburti_Flickr_Photo::get_url( $photo->flickr_id, $photo->farm, $photo->server, $photo->secret, $thumbnail_size ) ?>"/>
        </a>
		<?php
		return ob_get_clean();
	}
}