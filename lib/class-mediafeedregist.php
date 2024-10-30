<?php
/**
 * Media Feed
 *
 * @package    MediaFeed
 * @subpackage MediaFeed registered in the database
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

$mediafeedregist = new MediaFeedRegist();

/** ==================================================
 * Registered in the database
 */
class MediaFeedRegist {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.07
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( ! get_option( 'media_feed_options' ) ) {
			$media_feed_options = array(
				'apply'     => array(
					'media' => true,
					'image' => true,
					'audio' => true,
					'video' => true,
					'misc' => true,
				),
				'text'      => array(
					'media' => __( 'Media' ),
					'image' => __( 'Image' ),
					'audio' => __( 'Audio' ),
					'video' => __( 'Video' ),
					'misc' => __( 'Misc', 'media-feed' ),
				),
				'per_rss'   => array(
					'media' => intval( get_option( 'posts_per_rss' ) ),
					'image' => intval( get_option( 'posts_per_rss' ) ),
					'audio' => intval( get_option( 'posts_per_rss' ) ),
					'video' => intval( get_option( 'posts_per_rss' ) ),
					'misc' => intval( get_option( 'posts_per_rss' ) ),
				),
				'exclude_id' => null,
				'term_filter' => null,
			);
			update_option( 'media_feed_options', $media_feed_options );
		}

		if ( ! get_option( 'media_feed_widget' ) ) {
			$media_feed_widget = array(
				'media' => get_feed_link( 'media' ),
				'image' => get_feed_link( 'image' ),
				'audio' => get_feed_link( 'audio' ),
				'video' => get_feed_link( 'video' ),
				'misc' => get_feed_link( 'misc' ),
			);
			update_option( 'media_feed_widget', $media_feed_widget );
		}
	}
}
