=== Masburti Flickr Gallery ===
Contributors: fifi6262
Donate link: https://paypal.me/masburti/5usd
Tags: gallery,flickr,images,album
Requires PHP: 5.3
Requires at least: 3.5.0
Tested up to: 4.9.1
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your photos from Flickr account directly on your website. Compatible with Flickr API and allows to display: list of all (selected) albums, all pictures from album and single picture.

== Description ==

Do you want to display your photos from Flickr directly on your WordPress website? Are you looking for safe, convenient,and comfortable tool? Masburti Flickr Gallery is a plugin, which allows you to display all your albums and pictures directly on website. Event private content from your Flick account.

Features:

*   Import selected albums to your WordPress instance
*   Display all (or selected) albums on single page
*   Slideshow of album photos on single page without reloading
*   Shortcodes easy to use everywhere (albums list, albums photos, single photo)
*   Use safe OAuth to authorize this plugin on Flickr. No credentials saved on WordPress instance - only API keys
*   One step revoke authorization (both on plugin settings and on Flickr account site)

This product uses the Flickr API but is not endorsed or certified by Flickr.

(Sorry for shoddy banner - maybe somebody have an idea to improve it?)

= Localization =
* English (default) - always included
* Polish - always included
* *Your translation? - [Just send it in](mailto:wordpress@fkula.pl)*

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/masburti-flickr-gallery` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to the Settings->Masburti Flickr Gallery to connect with Flickr.
    * Obtain Flickr API keys (detailed description on Settings page), write them into appropriate inputs and save.
    * Authenticate your Flickr account by clicking "Authorize on Flickr" button.
1. Go to Tools -> Masburti Flickr Gallery importer to import/synchronize/delete local copies of your Flickr account albums and photos.


== Frequently Asked Questions ==

= How to connect with my Flickr account? =

Go to the Settings->Masburti Flickr Gallery page. Firstly obtain Flickr API keys and secondly authorize on Flickr.

= Why do I need double grand access on Flickr? =

Not exactly. Firstly you create your own application on Flickr. You use this application API keys to access everything on Flickr. Then you authorize this your own application on Flickr to have full **read** access to your Flickr account.

= Why importing albums from Flickr is slow? =

Your website need to connect with Flickr API servers. It takes some time. Especially when you are trying to import big amount of albums with many photos.

= What data from Flickr does this plugin use? =
Masburti Flick Gallery plugin use only data about your albums and photos inside. It doesn't fetch any other data from Flickr as well as it can **only read** this data. Plugin is unable to upload, change or delete something from your Flickr account.

= I've changed some albums on Flickr, but on website there are still like old one =

This plugin for better performance import once from Flickr all data about every album (photoset), which is importing to your website. Once you change something on Flickr, you need to synchronize this album on  Tools -> Masburti Flickr Gallery importer page.

= If you miss some features in this plugin =

This is the first version of Masburti Flickr Gallery plugin. It will be upgraded and some features will be added.
If you have any ideas of possible features

== Screenshots ==

1. Setting page - connected with user Flickr account
2. Tools -> Import page with available albums list
3. Example of albums list on page
5. Loading single album from albums list (asynchronous)
5. Example of photos from single album

== Changelog ==
= 1.1 =
* Initial release

== Upgrade Notice ==
= 1.1 =
Initial release

== Translations ==
* English - default, always included
* Polish: Polski - język autora ;)

*Note*: Plugin is localized by default. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful.