<?php
if ( class_exists( 'Masburti_Flickr_Gallery_Admin_Views' ) ) {
	return;
}

/**
 * Class Masburti_Flickr_Gallery_Admin_Views
 */
class Masburti_Flickr_Gallery_Admin_Views {

	/**
	 * Prints plugin settings page in admin view
	 */
	public static function display_settings_api() {
		ob_start();
		?>
        <h1><?php _e( 'Flickr API keys', 'masburti-flickr-gallery' ) ?></h1>
        <form method="POST" id="api_keys">
            <input type="hidden" name="form-name" value="set_api_keys"/>
			<?php wp_nonce_field( 'set_api_keys' ); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="key">
							<?php _e( 'Key', 'masburti-flickr-gallery' ) ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" id="key" name="key"
                               value="<?php echo get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_API_KEY ) ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="secret">
							<?php _e( 'Secret', 'masburti-flickr-gallery' ) ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" id="secret" name="secret"
                               value="<?php echo get_option( Masburti_Flickr_Gallery_Plugin::OPTION_FLICKR_SECRET_KEY ) ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="secret">
							<?php _e( 'How to obtain API keys?', 'masburti-flickr-gallery' ) ?>
                        </label>
                    </th>
                    <td>
                        <p class="description">
							<?php _e( 'Go to Flickr The App Garden site and create an app to request an API key.', 'masburti-flickr-gallery' ) ?>
                            <br/>
							<?php _e( 'Click', 'masburti-flickr-gallery' ) ?> <a
                                    href="https://www.flickr.com/services/apps/create/apply/"
                                    target="_blank"><?php _e( 'here', 'masburti-flickr-gallery' ) ?></a> <?php _e( 'and apply for key', 'masburti-flickr-gallery' ) ?>
                            .
							<?php _e( 'Enter app name, describe what will you be doing with this plugin, agree necessary terms and submit.', 'masburti-flickr-gallery' ) ?>
                            <br/>
							<?php _e( 'Paste your API key and secret in boxes above and save.', 'masburti-flickr-gallery' ) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary"
                       value="<?php _e( 'Save', 'masburti-flickr-gallery' ) ?>"/>
            </p>
        </form>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints view for user authentication in Flickr service
	 *
	 * @param $oauth_url
	 */
	public static function display_settings_auth( $oauth_url ) {
		ob_start();
		?>
        <h1><?php _e( 'Flickr user authentication', 'masburti-flickr-gallery' ) ?></h1>
		<?php if ( is_null( $oauth_url ) ): ?>
			<?php _e( 'You need to save correct Flickr API keys first!', 'masburti-flickr-gallery' ) ?>
		<?php else: ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <b>Continue</b>
                    </th>
                    <td>
                        <a href="<?php echo $oauth_url ?>"
                           class="button button-primary"><?php _e( 'Authorize on Flickr', 'masburti-flickr-gallery' ) ?></a>
                        <p class="description">
							<?php _e( 'Click button and you will be moved to page to authorize your app to use your Flickr data. After approving you will automatically return to this website.', 'masburti-flickr-gallery' ) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
		<?php endif; ?>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints details about authenticated (on Flickr) user
	 *
	 * @param $access_token
	 */
	public static function display_settings_revoke_auth( $access_token ) {
		ob_start();
		?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><b>User name</b></th>
                <td>
					<?php echo $access_token->username ?>
                </td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>
                    <form method="POST" id="revoke_auth">
                        <input type="hidden" name="form-name" value="revoke_authentication"/>
						<?php wp_nonce_field( 'revoke_authentication' ); ?>
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-primary"
                                   value="<?php _e( 'Revoke user authentication', 'masburti-flickr-gallery' ) ?>"/>
                        </p>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints authentication warning in admin view
	 */
	public static function display_management_authentication_warning() {
		ob_start();
		?>
		<?php _e( 'You need to save correct Flickr API keys and user authenticate first!', 'masburti-flickr-gallery' ) ?>
        <br/>
		<?php _e( 'Please go to', 'masburti-flickr-gallery' ) ?> <a
                href="<?php echo admin_url( 'options-general.php?page=masburti-flickr-gallery-settings' ) ?>"><?php _e( 'settings', 'masburti-flickr-gallery' ) ?></a>.
		<?php
		ob_end_flush();
	}

	/**
	 * Prints table for photosets management in admin view
	 *
	 * @param array $photosets_from_flickr - Array with photosets downloaded by API from Flickr (for authenticated user)
	 * @param array $photosets_from_db - Array with photosets existing in database
	 */
	public static function display_management( $photosets_from_flickr, $photosets_from_db ) {
		ob_start();
		?>
        <h1><?php _e( 'Importing from Flickr', 'masburti-flickr-gallery' ) ?></h1>
        <form method="post" action="<?php echo menu_page_url( 'masburti-flickr-gallery-management', false ) ?>">
            <input type="hidden" name="form-name" value="photosets_form"/>
			<?php wp_nonce_field( 'photosets_form' ); ?>
            <table class="wp-list-table widefat striped" id="photosets">
                <thead>
                <tr>
                    <td id="cb" class="column-cb check-column">
                        <label class="screen-reader-text"
                               for="cb-select-all-1"><?php _e( 'Select all', 'masburti-flickr-gallery' ) ?></label>
                        <input id="cb-select-all-1" type="checkbox"/>
                    </td>
                    <th scope="col" class="column-primary"><?php _e( 'Id', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Flickr ID', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Title', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary">&nbsp;</th>
                    <th scope="col" class="column-primary"><?php _e( 'Photos count', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col"
                        class="column-primary"><?php _e( 'Imported photos', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Created', 'masburti-flickr-gallery' ) ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td id="cb" class="column-cb check-column">
                        <label class="screen-reader-text"
                               for="cb-select-all-2"><?php _e( 'Select all', 'masburti-flickr-gallery' ) ?></label>
                        <input id="cb-select-all-2" type="checkbox"/>
                    </td>
                    <th scope="col" class="column-primary"><?php _e( 'Id', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Flickr ID', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Title', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary">&nbsp;</th>
                    <th scope="col" class="column-primary"><?php _e( 'Photos count', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col"
                        class="column-primary"><?php _e( 'Imported photos', 'masburti-flickr-gallery' ) ?></th>
                    <th scope="col" class="column-primary"><?php _e( 'Created', 'masburti-flickr-gallery' ) ?></th>
                </tr>
                </tfoot>
                <tbody>
				<?php foreach ( $photosets_from_flickr AS $flickr_photoset ):
					/* @var $flickr_photoset Masburti_Flickr_Photoset */ ?>
                    <tr id="photoset-<?php echo $flickr_photoset->id ?>">
                        <th scope="row" class="check-column">
                            <label class="screen-reader-text" for="photoset_<?php echo $flickr_photoset->id ?>">
								<?php _e( 'Select', 'masburti-flickr-gallery' ) ?><?php echo $flickr_photoset->get_title() ?>
                            </label>
                            <input id="photoset_<?php echo $flickr_photoset->id ?>" type="checkbox" name="photosets[]"
                                   value="<?php echo $flickr_photoset->id ?>"/>
                        </th>
                        <td>
							<?php if ( isset( $photosets_from_db[ $flickr_photoset->id ] ) ): ?>
								<?php echo $photosets_from_db[ $flickr_photoset->id ]->id ?>
							<?php else: ?>
                                -
							<?php endif; ?>
                        </td>
                        <td><?php echo $flickr_photoset->id ?></td>
                        <td><?php echo $flickr_photoset->get_title() ?></td>
                        <td>
							<?php if ( isset( $photosets_from_db[ $flickr_photoset->id ] ) ): ?>
                                <a href="<?php echo wp_nonce_url( menu_page_url( 'masburti-flickr-gallery-management', false ) . '&action=refresh&pid=' . $flickr_photoset->id, 'refresh-pid_' . $flickr_photoset->id ) ?>"
                                   title="<?php _e( 'Refresh', 'masburti-flickr-gallery' ) ?>">
                                    <span class="dashicons dashicons-update"></span>
                                </a>
                                <a href="<?php echo wp_nonce_url( menu_page_url( 'masburti-flickr-gallery-management', false ) . '&action=remove&pid=' . $flickr_photoset->id, 'remove-pid_' . $flickr_photoset->id ) ?>"
                                   title="<?php _e( 'Remove', 'masburti-flickr-gallery' ) ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
							<?php else: ?>
                                <a href="<?php echo wp_nonce_url( menu_page_url( 'masburti-flickr-gallery-management', false ) . '&action=import&pid=' . $flickr_photoset->id, 'import-pid_' . $flickr_photoset->id ) ?>"
                                   title="<?php _e( 'Import', 'masburti-flickr-gallery' ) ?>">
                                    <span class="dashicons dashicons-download"></span>
                                </a>
							<?php endif; ?>
                        </td>
                        <td><?php echo $flickr_photoset->photos ?></td>
                        <td>
							<?php if ( isset( $photosets_from_db[ $flickr_photoset->id ] ) ): ?>
								<?php echo $photosets_from_db[ $flickr_photoset->id ]->photos_count ?>
							<?php else: ?>
                                0
							<?php endif; ?>
                        </td>
                        <td><?php echo date( 'd-m-Y', $flickr_photoset->date_create ) ?></td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom"
                           class="screen-reader-text"><?php _e( 'Select bulk action', 'masburti-flickr-gallery' ) ?></label>
                    <select name="action2" id="bulk-action-selector-bottom">
                        <option value="-1"><?php _e( 'Bulk actions', 'masburti-flickr-gallery' ) ?></option>
                        <option value="import"><?php _e( 'Import', 'masburti-flickr-gallery' ) ?></option>
                        <option value="refresh"><?php _e( 'Refresh', 'masburti-flickr-gallery' ) ?></option>
                        <option value="remove"><?php _e( 'Remove', 'masburti-flickr-gallery' ) ?></option>
                    </select>
                    <input type="submit" id="doaction2" class="button action"
                           value="<?php _e( 'Do action', 'masburti-flickr-gallery' ) ?>">
                </div>
            </div>
        </form>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints notice required by Flickr API conditions
	 */
	public static function display_flickr_notice() {
		ob_start();
		?>
        <p><?php _e( 'This product uses the Flickr API but is not endorsed or certified by Flickr.', 'masburti-flickr-gallery' ) ?></p>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints donate button ;)
	 */
	public static function display_donate_button() {
		ob_start();
		?>
        <div class="masburti_flickr_gallery_donate"
             style="display: block; text-align: center; position: absolute; top: 10px; right: 10px;">
            <a href="https://paypal.me/masburti/5usd" title="Donate developer" target="_blank"
               style="display: inline-block; font-weight: bold; line-height: 1.7rem; text-decoration: none; transition: opacity 0.3s ease-in-out;">
				<?php _e( 'Donate plugin developer', 'masburti-flickr-gallery' ) ?> ;)<br/>
                <img src="<?php echo plugins_url( 'images/paypal.png', dirname( __FILE__ ) ) ?>"
                     alt="<?php _e( 'Donate on PayPal', 'masburti-flickr-gallery' ) ?>"
                     style="box-shadow: 0 1px 2px rgba(0,0,0,0.2); transition: box-shadow 0.3s ease-out; border-radius: 5px;"/>
            </a>
        </div>
		<?php
		ob_end_flush();
	}

	/**
	 * Prints information about shortcodes usage
	 */
	public static function display_shortcodes_info() {
		ob_start();
		?>
        <h1><?php _e( 'Available shortcode', 'masburti-flickr-gallery' ) ?></h1>
        <p>
            <code>[masburti_flickr_gallery
				<?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_TYPE ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSETS_LIST ?>
                &quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_COLUMNS ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::DEFAULT_COLS_IN_LIST ?>
                &quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_PHOTOS_COLUMNS ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::DEFAULT_COLS_IN_PHOTOS ?>&quot;]</code> - displays
            all photosets thumbnails list and displays all photos from selected photoset (album)
        </p>
        <p>
            <code>[masburti_flickr_gallery
				<?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_TYPE ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSET_PHOTOS ?>
                &quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_ID ?>
                =&quot;1&quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_FLICKR_ID ?>=&quot;72157661875736968&quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_PHOTOS_COLUMNS ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::DEFAULT_COLS_IN_PHOTOS ?>&quot; ]</code> - displays
            all photos from selected photoset (album)
        </p>
        <p>
            <code>[masburti_flickr_gallery <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_TYPE ?>
                =&quot;<?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_SINGLE_PHOTO ?>
                &quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_ID ?>
                =&quot;1&quot; <?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_FLICKR_ID ?>=&quot;37929929461&quot;
                ]</code> - displays selected photo
        </p>
        <h3>Shortcode parameters</h3>
        <ul style="list-style: disc inside;">
            <li>
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_TYPE ?></code> - type of displayed
                content; current available:
                <ul style="list-style: disc inside; margin-left: 20px; margin-top: 5px;">
                    <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSETS_LIST ?></code> - list of all
                        imported photosets
                    <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSET_PHOTOS ?></code> - all photos
                        from signle photoset
                    </li>
                    <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_PHOTOS_COLUMNS ?></code> - single
                        photo
                    </li>
                </ul>
            </li>
            <li>
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_COLUMNS ?></code> - quantity of columns on
                photosets list
            </li>
            <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_PHOTOS_COLUMNS ?></code> - quantity of
                columns on single photoset
            </li>
            <li>
                One of below is required when
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_TYPE ?></code> is
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::TYPE_PHOTOSET_PHOTOS ?></code> or
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_PHOTOS_COLUMNS ?></code>:
                <ul style="list-style: disc inside; margin-left: 20px; margin-top: 5px;">
                    <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_ID ?></code> - item ID from list
                        above (may change after re-import)
                    <li><code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_FLICKR_ID ?></code> - item ID from
                        Flickr
                    </li>
                </ul>
                When bot attributes are passed,
                <code><?php echo Masburti_Flickr_Gallery_Shortcode::ATTRIBUTE_FLICKR_ID ?></code> is ignored.
            </li>
        </ul>
		<?php
		ob_end_flush();
	}
}