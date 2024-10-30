<?php
/**
 * Media Feed
 *
 * @package    Media Feed
 * @subpackage MediaFeedAdmin Management screen
/*
	Copyright (c) 2017- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$mediafeedadmin = new MediaFeedAdmin();

/** ==================================================
 * Management screen
 */
class MediaFeedAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.07
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
		add_filter( 'manage_media_columns', array( $this, 'posts_columns_attachment_id' ), 1 );
		add_action( 'manage_media_custom_column', array( $this, 'posts_custom_columns_attachment_id' ), 1, 2 );

		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10, 1 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'media-feed/mediafeed.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'upload.php?page=mediafeed-settings' ) . '">' . esc_html__( 'Settings' ) . '</a>';
		}
		return $links;
	}

	/** ==================================================
	 * Add Menu page
	 *
	 * @since 1.14
	 */
	public function plugin_menu() {

		add_media_page(
			__( 'Feed', 'media-feed' ),
			__( 'Feed', 'media-feed' ),
			'manage_options',
			'mediafeed-settings',
			array( $this, 'plugin_options_gutenberg' )
		);
	}

	/** ==================================================
	 * Settings page for Gutenberg > wp5
	 *
	 * @since 2.00
	 */
	public function plugin_options_gutenberg() {

		echo '<div id="mediafeedadmin"></div>';
	}

	/** ==================================================
	 * Load script > wp5
	 *
	 * @param string $hook_suffix  hook_suffix.
	 * @since 2.00
	 */
	public function admin_scripts( $hook_suffix ) {

		if ( 'media_page_mediafeed-settings' !== $hook_suffix ) {
			return;
		}

		$asset_file = include plugin_dir_path( __DIR__ ) . 'guten/dist/media-feed-admin.asset.php';

		wp_enqueue_style(
			'mediafeedadmin-style',
			plugin_dir_url( __DIR__ ) . 'guten/dist/media-feed-admin.css',
			array( 'wp-components' ),
			'1.0.0',
		);

		wp_enqueue_script(
			'mediafeedadmin',
			plugin_dir_url( __DIR__ ) . 'guten/dist/media-feed-admin.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script(
			'mediafeedadmin',
			'mediafeedadmin_text',
			array(
				'settings' => __( 'Settings' ),
				'apply' => __( 'Apply' ),
				'numfeeds' => __( 'Number of feeds', 'media-feed' ),
				'feed' => __( 'Feed', 'media-feed' ),
				'feedname' => __( 'Feed name', 'media-feed' ),
				'feedrecent' => __( 'Syndication feeds show the most recent' ),
				'permlink_description' => __( 'If Feed is not displayed, save "Permalink Settings" again.', 'media-feed' ),
				'permlink' => admin_url( 'options-permalink.php' ),
				'exclude' => __( 'Exclude', 'media-feed' ),
				'exclude_description1' => __( 'Specifies a comma-separated list to exclusion media ID:', 'media-feed' ),
				'exclude_description2' => __( 'When you activate this plugin, will be displayed ID is in the column of the "Media Library".', 'media-feed' ),
				'medialibrary' => admin_url( 'upload.php' ),
				'termlist' => __( 'Term filter list', 'media-feed' ),
				'termlist_description' => __( 'Specifies a comma-separated list to filter by terms:', 'media-feed' ),
				'shortcode' => __( 'Shortcode', 'media-feed' ),
				'shortcode_description1' => __( 'Insert feed playlist and icon.', 'media-feed' ),
				'shortcode_description2' => __( 'Example of shortcode', 'media-feed' ),
				'shortcode_description3' => __( 'Specify the slug of the following feed.', 'media-feed' ),
				'media' => __( 'Media' ),
				'image' => __( 'Image' ),
				'audio' => __( 'Audio' ),
				'video' => __( 'Video' ),
				'misc' => __( 'Misc', 'media-feed' ),
			)
		);

		$feed_link = array();
		$feed_link[0] = get_feed_link( 'media' );
		$feed_link[1] = get_feed_link( 'image' );
		$feed_link[2] = get_feed_link( 'audio' );
		$feed_link[3] = get_feed_link( 'video' );
		$feed_link[4] = get_feed_link( 'misc' );

		$media_feed_options = get_option( 'media_feed_options' );

		wp_localize_script(
			'mediafeedadmin',
			'mediafeedadmin_data',
			array(
				'links' => wp_json_encode( $feed_link, JSON_UNESCAPED_SLASHES ),
				'options' => wp_json_encode( $media_feed_options, JSON_UNESCAPED_SLASHES ),
			)
		);

		$this->credit_gutenberg( 'mediafeedadmin' );
	}

	/** ==================================================
	 * Register Rest API
	 *
	 * @since 2.00
	 */
	public function register_rest() {

		register_rest_route(
			'rf/mediafeed_api',
			'/token',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'api_save' ),
				'permission_callback' => array( $this, 'rest_permission' ),
			),
		);
	}

	/** ==================================================
	 * Rest Permission
	 *
	 * @since 2.00
	 */
	public function rest_permission() {

		return current_user_can( 'manage_options' );
	}

	/** ==================================================
	 * Rest API save
	 *
	 * @param object $request  changed data.
	 * @since 2.00
	 */
	public function api_save( $request ) {

		$args = json_decode( $request->get_body(), true );

		update_option( 'media_feed_options', $args );

		return new WP_REST_Response( $args, 200 );
	}

	/** ==================================================
	 * Credit for Gutenberg
	 *
	 * @param string $handle  handle.
	 * @since 2.00
	 */
	private function credit_gutenberg( $handle ) {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}

		wp_localize_script(
			$handle,
			'credit',
			array(
				'links'          => __( 'Various links of this plugin', 'media-feed' ),
				'plugin_version' => __( 'Version:' ) . ' ' . $plugin_ver_num,
				/* translators: FAQ Link & Slug */
				'faq'            => sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'media-feed' ), $slug ),
				'support'        => 'https://wordpress.org/support/plugin/' . $slug,
				'review'         => 'https://wordpress.org/support/view/plugin-reviews/' . $slug,
				'translate'      => 'https://translate.wordpress.org/projects/wp-plugins/' . $slug,
				/* translators: Plugin translation link */
				'translate_text' => sprintf( __( 'Translations for %s' ), $plugin_name ),
				'facebook'       => 'https://www.facebook.com/katsushikawamori/',
				'twitter'        => 'https://twitter.com/dodesyo312',
				'youtube'        => 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w',
				'donate'         => __( 'https://shop.riverforest-wp.info/donate/', 'media-feed' ),
				'donate_text'    => __( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'media-feed' ),
				'donate_button'  => __( 'Donate to this plugin &#187;' ),
			)
		);
	}

	/** ==================================================
	 * Posts columuns id
	 *
	 * @param array $defaults  defaults.
	 * @since 1.00
	 */
	public function posts_columns_attachment_id( $defaults ) {
		global $pagenow;
		if ( 'upload.php' == $pagenow ) {
			$defaults['mediafeed_post_attachments_id'] = 'ID';
		}
		return $defaults;
	}

	/** ==================================================
	 * Posts custom columuns id
	 *
	 * @param string $column_name  column_name.
	 * @param int    $id  id.
	 * @since 1.00
	 */
	public function posts_custom_columns_attachment_id( $column_name, $id ) {
		if ( 'mediafeed_post_attachments_id' === $column_name ) {
			echo esc_html( $id );
		}
	}
}
