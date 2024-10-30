<?php
/**
 * Media Feed
 *
 * @package    Media Feed
 * @subpackage MediaFeed Main Functions
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

$mediafeed = new MediaFeed();

/** ==================================================
 * Main Functions
 */
class MediaFeed {

	/** ==================================================
	 * Rss apply
	 *
	 * @var $rss_apply  rss_apply.
	 */
	private $rss_apply;

	/** ==================================================
	 * Rss text
	 *
	 * @var $rss_text  rss_text.
	 */
	private $rss_text;

	/** ==================================================
	 * Posts per rss
	 *
	 * @var $posts_per_rss  posts_per_rss.
	 */
	private $posts_per_rss;

	/** ==================================================
	 * Exclude id
	 *
	 * @var $exclude_id  exclude_id.
	 */
	private $exclude_id;

	/** ==================================================
	 * Term filter
	 *
	 * @var $term_filter  term_filter.
	 */
	private $term_filter;

	/** ===================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		$media_feed_options = get_option( 'media_feed_options' );
		$this->rss_apply = $media_feed_options['apply'];
		$this->rss_text = $media_feed_options['text'];
		$this->posts_per_rss = $media_feed_options['per_rss'];
		$this->exclude_id = $media_feed_options['exclude_id'];
		$this->term_filter = $media_feed_options['term_filter'];

		add_action( 'init', array( $this, 'add_custom_feed' ) );
		add_action( 'wp_head', array( $this, 'mediafeed_alternate' ) );
		add_shortcode( 'mediafeedlist', array( $this, 'mediafeedlist_func' ) );

		add_action( 'init', array( $this, 'php_block_init' ) );
		add_shortcode( 'mediafeedicon', array( $this, 'mediafeedicon_func' ) );
	}

	/** ===================================================
	 * Add feed
	 *
	 * @since 1.00
	 */
	public function add_custom_feed() {

		if ( $this->rss_apply['media'] ) {
			add_feed( 'media', array( $this, 'load_media_create_feed' ) );
		}
		if ( $this->rss_apply['image'] ) {
			add_feed( 'image', array( $this, 'load_image_create_feed' ) );
		}
		if ( $this->rss_apply['audio'] ) {
			add_feed( 'audio', array( $this, 'load_audio_create_feed' ) );
		}
		if ( $this->rss_apply['video'] ) {
			add_feed( 'video', array( $this, 'load_video_create_feed' ) );
		}
		if ( $this->rss_apply['misc'] ) {
			add_feed( 'misc', array( $this, 'load_misc_create_feed' ) );
		}
	}

	/** ===================================================
	 * Load media feed
	 *
	 * @since 1.00
	 */
	public function load_media_create_feed() {

		$attachments = $this->load_db( 'media' );
		$this->load_create_feed( 'media', $attachments );
	}

	/** ===================================================
	 * Load image feed
	 *
	 * @since 1.00
	 */
	public function load_image_create_feed() {

		$attachments = $this->load_db( 'image' );
		$this->load_create_feed( 'image', $attachments );
	}

	/** ===================================================
	 * Load audio feed
	 *
	 * @since 1.00
	 */
	public function load_audio_create_feed() {

		$attachments = $this->load_db( 'audio' );
		$this->load_create_feed( 'audio', $attachments );
	}

	/** ===================================================
	 * Load video feed
	 *
	 * @since 1.00
	 */
	public function load_video_create_feed() {

		$attachments = $this->load_db( 'video' );
		$this->load_create_feed( 'video', $attachments );
	}

	/** ===================================================
	 * Load misc feed
	 *
	 * @since 1.00
	 */
	public function load_misc_create_feed() {

		$attachments = $this->load_db( 'misc' );
		$this->load_create_feed( 'misc', $attachments );
	}

	/** ===================================================
	 * Load db
	 *
	 * @param string $slug  slug.
	 * @return object $attachments
	 * @since 1.00
	 */
	private function load_db( $slug ) {

		global $wpdb;

		$limit = $this->posts_per_rss[ $slug ];

		if ( ! empty( $this->term_filter ) ) {
			if ( 'media' === $slug ) {
				$attachments = $wpdb->get_results(
					$wpdb->prepare(
						"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							LEFT JOIN {$wpdb->prefix}term_relationships ON({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)
							LEFT JOIN {$wpdb->prefix}term_taxonomy ON({$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id)
							LEFT JOIN {$wpdb->prefix}terms ON({$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id)
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									AND FIND_IN_SET( {$wpdb->prefix}terms.name, %s )
									ORDER BY post_date DESC
									LIMIT %d
						",
						$this->exclude_id,
						$this->term_filter,
						$limit
					)
				);
			} else if ( 'misc' === $slug ) {
				$attachments = $wpdb->get_results(
					$wpdb->prepare(
						"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							LEFT JOIN {$wpdb->prefix}term_relationships ON({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)
							LEFT JOIN {$wpdb->prefix}term_taxonomy ON({$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id)
							LEFT JOIN {$wpdb->prefix}terms ON({$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id)
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									AND FIND_IN_SET( {$wpdb->prefix}terms.name, %s )
									AND post_mime_type NOT LIKE %s
									AND post_mime_type NOT LIKE %s
									AND post_mime_type NOT LIKE %s
									ORDER BY post_date DESC
									LIMIT %d
						",
						$this->exclude_id,
						$this->term_filter,
						'image%',
						'audio%',
						'video%',
						$limit
					)
				);
			} else {
				$attachments = $wpdb->get_results(
					$wpdb->prepare(
						"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							LEFT JOIN {$wpdb->prefix}term_relationships ON({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)
							LEFT JOIN {$wpdb->prefix}term_taxonomy ON({$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id)
							LEFT JOIN {$wpdb->prefix}terms ON({$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id)
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									AND FIND_IN_SET( {$wpdb->prefix}terms.name, %s )
									AND post_mime_type LIKE %s
									ORDER BY post_date DESC
									LIMIT %d
						",
						$this->exclude_id,
						$this->term_filter,
						$wpdb->esc_like( $slug ) . '%',
						$limit
					)
				);
			}
		} elseif ( 'media' === $slug ) {
				$attachments = $wpdb->get_results(
					$wpdb->prepare(
						"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									ORDER BY post_date DESC
									LIMIT %d
						",
						$this->exclude_id,
						$limit
					)
				);
		} else if ( 'misc' === $slug ) {
			$attachments = $wpdb->get_results(
				$wpdb->prepare(
					"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									AND post_mime_type NOT LIKE %s
									AND post_mime_type NOT LIKE %s
									AND post_mime_type NOT LIKE %s
									ORDER BY post_date DESC
									LIMIT %d
						",
					$this->exclude_id,
					'image%',
					'audio%',
					'video%',
					$limit
				)
			);
		} else {
			$attachments = $wpdb->get_results(
				$wpdb->prepare(
					"
							SELECT	ID, post_date, post_title, post_mime_type
							FROM	{$wpdb->prefix}posts
							WHERE	post_type = 'attachment'
									AND NOT FIND_IN_SET( ID, %s )
									AND post_mime_type LIKE %s
									ORDER BY post_date DESC
									LIMIT %d
						",
					$this->exclude_id,
					$wpdb->esc_like( $slug ) . '%',
					$limit
				)
			);
		}

		return $attachments;
	}

	/** ===================================================
	 * Load create feed
	 *
	 * @param string $slug  slug.
	 * @param array  $attachments  attachments.
	 * @since 1.00
	 */
	private function load_create_feed( $slug, $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		$feed_title = $this->rss_text[ $slug ];
		if ( function_exists( 'wp_date' ) ) {
			$diff_time  = wp_date( 'O' );
		} else {
			$diff_time  = date_i18n( 'O' );
		}

		$duration = 'hourly';
		$update_period = apply_filters( 'rss_update_period', $duration );

		$frequency = '1';
		$update_frequency = apply_filters( 'rss_update_frequency', $frequency );

		header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

		?><?php echo '<?'; ?>xml version="1.0" encoding="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>"<?php echo '?>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
	<channel>
	<title><?php echo esc_html( $feed_title . ' &#187; ' . get_bloginfo( 'name' ) ); ?></title>
	<atom:link href="<?php echo esc_url( get_feed_link( $slug ) ); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo esc_url( get_feed_link( $slug ) ); ?></link>
	<description><?php echo esc_html( $feed_title ); ?></description>
	<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s ', $attachments[0]->post_date, false ) . $diff_time ); ?></lastBuildDate>
	<language><?php echo esc_html( get_bloginfo( 'language' ) ); ?></language>
	<sy:updatePeriod><?php echo esc_html( $update_period ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo esc_html( $update_frequency ); ?></sy:updateFrequency>
<?php
		foreach ( $attachments as $attachment ) {
			$this->xml_item( $slug, $attachment->ID, $attachment->post_date, $attachment->post_title, $attachment->post_mime_type );
		}
?>
</channel>
</rss>
<?php
	}

	/** ===================================================
	 * Xml item
	 *
	 * @param string $type  type.
	 * @param int    $pid  pid.
	 * @param string $datetime  datetime.
	 * @param string $title  title.
	 * @param string $mime_type  mime_type.
	 * @since 1.00
	 */
	private function xml_item( $type, $pid, $datetime, $title, $mime_type ) {

		$link_url = wp_get_attachment_url( $pid );

		$attachment_metadata = array();
		$attachment_metadata = get_post_meta( $pid, '_wp_attachment_metadata', true );
		if ( isset( $attachment_metadata['filesize'] ) ) {
			$file_size = $attachment_metadata['filesize'];
		} else {
			$file_size = filesize( get_attached_file( $pid ) );
		}
		if ( 'media' === $type ) {
			$filetype = wp_check_filetype( get_attached_file( $pid ) );
			$type = wp_ext2type( $filetype['ext'] );
			if ( 'image' <> $type && 'audio' <> $type && 'video' <> $type ) {
				$type = 'misc';
			}
		}
		unset( $attachment_metadata );

		$thumblink = null;
		if ( 'image' === $type ) {
			$thumb_src = wp_get_attachment_image_src( $pid, 'thumbnail', false );
		} else {
			$thumb_src = wp_get_attachment_image_src( $pid, 'thumbnail', true );
		}
		$thumblink = $thumb_src[0];
		unset( $thumb_src );

		if ( function_exists( 'wp_date' ) ) {
			$diff_time  = wp_date( 'O' );
		} else {
			$diff_time  = date_i18n( 'O' );
		}

?>
<item>
<title><?php echo esc_html( $title ); ?></title>
<link><?php echo esc_url( $link_url ); ?></link>
<?php
		if ( 'audio' === $type || 'video' === $type ) {
?>
<enclosure url="<?php echo esc_url( $link_url ); ?>" length="<?php echo esc_attr( $file_size ); ?>" type="<?php echo esc_attr( $mime_type ); ?>" />
<?php
		}
		if ( ! empty( $thumblink ) ) {
?>
<description><![CDATA[<a href="<?php echo esc_url( $link_url ); ?>"><img src = "<?php echo esc_url( $thumblink ); ?>"></a>]]></description>
<?php
		}
?>
<guid><?php echo esc_url( $link_url ); ?></guid>
<pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s ', $datetime, false ) . $diff_time ); ?></pubDate>
</item>
<?php
	}

	/** ===================================================
	 * List item
	 *
	 * @param string $type  type.
	 * @param int    $pid  pid.
	 * @param string $datetime  datetime.
	 * @param string $title  title.
	 * @param string $mime_type  mime_type.
	 * @return string $listitem
	 * @since 1.00
	 */
	private function list_item( $type, $pid, $datetime, $title, $mime_type ) {

		$link_url = wp_get_attachment_url( $pid );

		$attachment_metadata = array();
		$attachment_metadata = get_post_meta( $pid, '_wp_attachment_metadata', true );
		if ( isset( $attachment_metadata['filesize'] ) ) {
			$file_size = $attachment_metadata['filesize'];
		} else {
			$file_size = filesize( get_attached_file( $pid ) );
		}
		unset( $attachment_metadata );

		$thumblink = null;
		if ( 'image' === $type ) {
			$thumb_src = wp_get_attachment_image_src( $pid, 'thumbnail', false );
		} else {
			$thumb_src = wp_get_attachment_image_src( $pid, 'thumbnail', true );
		}
		$thumblink = $thumb_src[0];
		unset( $thumb_src );

		$listitem = null;
		if ( ! empty( $thumblink ) ) {
			$listitem = '<div style="padding: 10px 10px;"><a style="text-decoration: none;" href="' . $link_url . '"><img style="float: left; margin-right:10px;" src = "' . $thumblink . '"><div>' . $title . '</div></a><div>[' . $datetime . '] [' . size_format( $file_size, 2 ) . '] [' . $mime_type . ']</div></div><div style="clear: both;"></div>';
		}

		return $listitem;
	}

	/** ===================================================
	 * Feed alternate
	 *
	 * @since 1.00
	 */
	public function mediafeed_alternate() {

		if ( $this->rss_apply['media'] ) {
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( $this->rss_text['media'] ) . ' ' . esc_html__( 'Feed', 'media-feed' ) . ' &raquo; ' . esc_attr( get_bloginfo( 'name' ) ) . '" href="' . esc_url( get_feed_link( 'media' ) ) . "\" />\n";
		}
		if ( $this->rss_apply['image'] ) {
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( $this->rss_text['image'] ) . ' ' . esc_html__( 'Feed', 'media-feed' ) . ' &raquo; ' . esc_attr( get_bloginfo( 'name' ) ) . '" href="' . esc_url( get_feed_link( 'image' ) ) . "\" />\n";
		}
		if ( $this->rss_apply['audio'] ) {
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( $this->rss_text['audio'] ) . ' ' . esc_html__( 'Feed', 'media-feed' ) . ' &raquo; ' . esc_attr( get_bloginfo( 'name' ) ) . '" href="' . esc_url( get_feed_link( 'audio' ) ) . "\" />\n";
		}
		if ( $this->rss_apply['video'] ) {
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( $this->rss_text['video'] ) . ' ' . esc_html__( 'Feed', 'media-feed' ) . ' &raquo; ' . esc_attr( get_bloginfo( 'name' ) ) . '" href="' . esc_url( get_feed_link( 'video' ) ) . "\" />\n";
		}
		if ( $this->rss_apply['misc'] ) {
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( $this->rss_text['misc'] ) . ' ' . esc_html__( 'Feed', 'media-feed' ) . ' &raquo; ' . esc_attr( get_bloginfo( 'name' ) ) . '" href="' . esc_url( get_feed_link( 'misc' ) ) . "\" />\n";
		}
	}

	/** ===================================================
	 * short code
	 *
	 * @param array  $atts  atts.
	 * @param string $html  html.
	 * @return string $html
	 */
	public function mediafeedlist_func( $atts, $html = null ) {

		$a = shortcode_atts(
			array(
				'slug' => '',
			),
			$atts
		);

		$slug = $a['slug'];

		$attachments = $this->load_db( $slug );

		if ( empty( $attachments ) ) {
			return $html;
		}

		$feedtitle = $this->rss_text[ $slug ];

		$ids = null;
		$listitem_media_misc = null;
		foreach ( $attachments as $attachment ) {
			/* for image,audio,video */
			$ids .= $attachment->ID . ',';

			/* for media,misc */
			$listitem_media_misc .= $this->list_item( $slug, $attachment->ID, $attachment->post_date, $attachment->post_title, $attachment->post_mime_type );

		}
		$ids = rtrim( $ids, ',' );

		$list = null;
		if ( 'audio' === $slug || 'video' === $slug ) {
			if ( ! empty( $ids ) ) {
				$shortcode = '[playlist type="' . $slug . '" ids="' . $ids . '"]';
				$list = do_shortcode( $shortcode );
			}
		} else if ( 'image' === $slug ) {
			if ( ! empty( $ids ) ) {
				$shortcode = '[gallery include="' . $ids . '"]';
				$list = do_shortcode( $shortcode );
			}
		} else {
			$list = $listitem_media_misc;
		}

		$iconlink = null;
		if ( $this->rss_apply[ $slug ] ) {
			$iconlink = do_shortcode( '[mediafeedicon ' . $slug . '=true align="right"]' );
		}

		$html = $list . $iconlink;

		return $html;
	}

	/** ==================================================
	 * Media feed icon block & short code
	 *
	 * @since 1.17
	 */
	public function php_block_init() {

		$media_feed_options = get_option( 'media_feed_options' );
		register_block_type(
			plugin_dir_path( __DIR__ ) . 'block/build',
			array(
				'attributes'      => array(
					'media' => array(
						'type'    => 'boolean',
						'default' => $media_feed_options['apply']['media'],
					),
					'image' => array(
						'type'    => 'boolean',
						'default' => $media_feed_options['apply']['image'],
					),
					'audio' => array(
						'type'    => 'boolean',
						'default' => $media_feed_options['apply']['audio'],
					),
					'video' => array(
						'type'    => 'boolean',
						'default' => $media_feed_options['apply']['video'],
					),
					'misc' => array(
						'type'    => 'boolean',
						'default' => $media_feed_options['apply']['misc'],
					),
					'align' => array(
						'type'    => 'string',
						'default' => 'left',
					),
				),
				'render_callback' => array( $this, 'mediafeedicon_func' ),
				'title' => _x( 'Media Feed Icon', 'block title', 'media-feed' ),
				'description' => _x( 'Links to feeds are displayed as icons.', 'block description', 'media-feed' ),
				'keywords' => array(
					_x( 'feed', 'block keyword', 'media-feed' ),
					_x( 'media', 'block keyword', 'media-feed' ),
					_x( 'image', 'block keyword', 'media-feed' ),
					_x( 'audio', 'block keyword', 'media-feed' ),
					_x( 'video', 'block keyword', 'media-feed' ),
					_x( 'misc', 'block keyword', 'media-feed' ),
					'rss',
				),
			)
		);

		$script_handle = generate_block_asset_handle( 'media-feed/media-feed-icon-block', 'editorScript' );
		wp_set_script_translations( $script_handle, 'media-feed' );
	}

	/** ==================================================
	 * Media feed icon short code
	 *
	 * @param array $atts  atts.
	 * @since 1.17
	 */
	public function mediafeedicon_func( $atts ) {

		$a = shortcode_atts(
			array(
				'media' => false,
				'image' => false,
				'audio' => false,
				'video' => false,
				'misc' => false,
				'align' => '',
			),
			$atts
		);
		$slugs = array();

		$slugs['media'] = $a['media'];
		$slugs['image'] = $a['image'];
		$slugs['audio'] = $a['audio'];
		$slugs['video'] = $a['video'];
		$slugs['misc'] = $a['misc'];

		$content = null;
		$div_align = null;
		if ( ! empty( $a['align'] ) ) {
			$div_align = ' align="' . esc_attr( $a['align'] ) . '"';
		}
		foreach ( $slugs as $slug => $value ) {
			if ( $value ) {
				$attachments = $this->load_db( $slug );
				if ( ! empty( $attachments ) ) {
					$feedtitle = $this->rss_text[ $slug ];
					if ( $this->rss_apply[ $slug ] ) {
						$content .= '<div' . $div_align . '><a href="' . esc_url( get_feed_link( $slug ) ) . '" style="text-decoration: none; word-break: break-all;" title="' . esc_attr( $feedtitle ) . '"><span class="dashicons dashicons-rss" style="vertical-align: middle;"></span>' . esc_html( $feedtitle ) . '</a></div>';
					}
				}
			}
		}
		if ( is_null( $content ) ) {
			if ( is_user_logged_in() ) {
				$content = '<div>';
				$content .= '<span style="font-weight: bold;">Media Feed : </span>' . esc_html__( 'There are no feeds available for viewing.', 'media-feed' );
				$content .= '</div>';
			}
		}

		return $content;
	}
}


