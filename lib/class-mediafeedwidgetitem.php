<?php
/**
 * Media Feed
 *
 * @package    MediaFeed
 * @subpackage MediaFeed Widget
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

add_action(
	'widgets_init',
	function () {
		register_widget( 'MediaFeedWidgetItem' );
	}
);

/** ==================================================
 * Widget
 *
 * @since 1.00
 */
class MediaFeedWidgetItem extends WP_Widget {

	/** ==================================================
	 * Rss apply
	 *
	 * @var $rss_apply  rss_apply.
	 */
	private $rss_apply;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		parent::__construct(
			'MediaFeedWidgetItem', /* Base ID */
			'Media Feed', /* Name */
			array( 'description' => __( 'Entries of RSS feed from "Media Feed".', 'media-feed' ) ) /* Args */
		);

		$media_feed_options = get_option( 'media_feed_options' );
		$this->rss_apply = $media_feed_options['apply'];
		$this->rss_text = $media_feed_options['text'];
	}

	/** ==================================================
	 * Widget
	 *
	 * @param array $args args.
	 * @param array $instance instance.
	 * @since 1.00
	 */
	public function widget( $args, $instance ) {

		$before_widget = $args['before_widget'];
		$before_title  = $args['before_title'];
		$after_title   = $args['after_title'];
		$after_widget  = $args['after_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( $title ) {

			echo wp_kses_post( $before_widget );
			echo wp_kses_post( $before_title . esc_html( $title ) . $after_title );

			$feedwidget_tbl = get_option( 'media_feed_widget' );
			$slugs = array();
			foreach ( $feedwidget_tbl as $key => $xmlurl ) {
				if ( $this->rss_apply[ $key ] ) {
					if ( isset( $instance[ $key ] ) && $instance[ $key ] ) {
						$checkbox[ $key ] = apply_filters( 'widget_checkbox', $instance[ $key ] );
					}
					if ( isset( $checkbox[ $key ] ) && $checkbox[ $key ] ) {
						$slugs[] = $key;
					}
				}
			}
			if ( ! empty( $slugs ) ) {
				foreach ( $slugs as $value ) {
					echo do_shortcode( '[mediafeedicon ' . $value . '=true]' );
				}
			}
			echo wp_kses_post( $after_widget );
		}
	}

	/** ==================================================
	 * Update
	 *
	 * @param array $new_instance new_instance.
	 * @param array $old_instance old_instance.
	 * @since 1.00
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		$feedwidget_tbl = get_option( 'media_feed_widget' );
		foreach ( $feedwidget_tbl as $key => $xmlurl ) {
			if ( $this->rss_apply[ $key ] ) {
				$instance[ $key ] = wp_strip_all_tags( $new_instance[ $key ] );
			}
		}
		return $instance;
	}

	/** ==================================================
	 * Form
	 *
	 * @param array $instance instance.
	 * @since 1.00
	 */
	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = null;
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<table>
		<?php

		$feedwidget_tbl = get_option( 'media_feed_widget' );
		foreach ( $feedwidget_tbl as $key => $xmlurl ) {
			if ( $this->rss_apply[ $key ] ) {
				if ( isset( $instance[ $key ] ) ) {
					$checkbox[ $key ] = esc_attr( $instance[ $key ] );
				} else {
					$checkbox[ $key ] = null;
				}
				$feedtitle = $this->rss_text[ $key ];
				?>
				<tr>
				<td align="left" valign="middle" nowrap>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?> ">
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="checkbox"<?php checked( $key, $checkbox[ $key ] ); ?> value="<?php echo esc_attr( $key ); ?>" />
					<?php echo esc_html( $feedtitle ); ?></label>
				</td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
	}
}


