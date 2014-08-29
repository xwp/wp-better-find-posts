<?php
/**
 * Plugin Name: Better Find Posts
 * Description: Better API than the WordPress 3.1 find_posts_div and findPosts. Open find dialog in modal or inline. Uses Backbone.
 * Author: Weston Ruter, X-Team WP
 * Author URI: https://x-team-wp.com/
 * Version: 0.1
 * License: GPLv2+
 *
 * Copyright (c) 2014 X-Team (http://x-team.com/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class Better_Find_Posts {

	const AJAX_ACTION = 'better_find_posts';

	/**
	 * @var Better_Find_Posts
	 */
	private static $instance;

	/**
	 * @return Better_Find_Posts
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @var array
	 */
	public $config = array();

	/**
	 * Add plugin hooks.
	 */
	protected function __construct() {
		$this->config = array(
			'default_post_types' => array(), // i.e. all public
			'max_posts_per_page' => 100,
		);
		$this->config = apply_filters( 'better_find_posts_plugin_config', $this->config );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'wp_ajax_better_find_posts' ) );
	}

	/**
	 * Version of plugin_dir_url() which works for plugins installed in the plugins directory,
	 * and for plugins bundled with themes.
	 *
	 * @param string $path Relative to content directory
	 * @return bool|string Returns false if the location could not be resolved.
	 */
	public function get_plugin_dir_url( $path = '' ) {
		// Make sure that we can reliably get the relative path inside of the content directory
		$content_dir = trailingslashit( WP_CONTENT_DIR );
		if ( 0 !== strpos( __DIR__, $content_dir ) ) {
			return false;
		}

		$content_sub_path = substr( __DIR__, strlen( $content_dir ) );

		$url = content_url( trailingslashit( $content_sub_path ) . ltrim( $path, '/' ) );
		return $url;
	}

	/**
	 * Export the frontend config to JS.
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_scripts() {
		global $wp_scripts;

		$src = $this->get_plugin_dir_url( 'better-find-posts.js' );
		$handle = 'better-find-posts';
		$deps = array( 'wp-util', 'underscore', 'backbone', 'jquery' );
		$ver = false;
		$in_footer = 1;
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

		$exported_data = array(
			'nonce' => wp_create_nonce( 'better-find-posts' ),
			'ajax_action' => self::AJAX_ACTION,
		);

		$exported_name = '_BetterFindPosts_exports';
		$exported_data = apply_filters( 'better_find_posts_js_data', $exported_data, $this );
		$serialized = json_encode( $exported_data );
		if ( false === $serialized ) {
			trigger_error( "Could not serialize script export data for $exported_name: $exported_data", E_USER_WARNING );
		} else {
			$data = sprintf( 'var %s = %s;', $exported_name, $serialized );
			$wp_scripts->add_data( $handle, 'data', $data );
		}
	}

	/**
	 * @return array
	 */
	protected function get_request_args() {
		// Posts count
		$posts_per_page = 50;
		if ( isset( $_GET['posts_per_page'] ) && intval( $_GET['posts_per_page'] ) > 0 ) {
			$posts_per_page = intval( $_GET['posts_per_page'] ); // input var okay
		}
		$posts_per_page = min( $posts_per_page, $this->config['max_posts_per_page'] );

		// Post types
		$post_type = array();
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = (array) wp_unslash( $_GET['post_type'] ); // input var okay
		}
		if ( empty( $post_type ) ) {
			$post_type = get_post_types( array( 'public' => true ), 'names' );
			$post_type = wp_list_filter( $post_type, array( 'attachment' ), 'NOT' ); // attachment posts excludd by default
		}

		// Filter out post types which do not exist or which the user cannot read
		$post_type = array_filter(
			$post_type,
			function ( $_post_type ) {
				$post_type_obj = get_post_type_object( $_post_type );
				if ( ! $post_type_obj ) {
					return false;
				}
				return current_user_can( $post_type_obj->cap->read );
			}
		);

		// Post status
		$post_status = array();
		if ( isset( $_GET['post_status'] ) ) {
			$post_status = (array) wp_unslash( $_GET['post_status'] ); // input var okay
			$post_status = array_filter(
				$post_status,
				function ( $_post_status ) {
					return (bool) get_post_status_object( $_post_status );
				}
			);

		}
		if ( empty( $post_status ) ) {
			$post_status = array( 'any' );
		}

		// Search
		$s = null;
		if ( isset( $_GET['s'] ) ) {
			$s = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // input var okay
		}

		$args = compact( 'post_type', 'posts_per_page', 'post_status', 's' );
		$args = apply_filters( 'better_find_posts_query_args', $args, $this );
		return $args;
	}

	/**
	 * Ajax handler for finding posts.
	 */
	function wp_ajax_better_find_posts() {

		try {
			if ( ! check_ajax_referer( 'better-find-posts', 'nonce', false ) ) {
				throw new Better_Find_Posts_Exception( 'nonce-fail' );
			}

			$args = $this->get_request_args();
			if ( empty( $args['post_type'] ) ) {
				throw new Better_Find_Posts_Exception( 'no-queryable-post-types' );
			}

			$post_objects = get_posts( $args );
			$post_fields = array( 'ID', 'post_title', 'post_type', 'post_status', 'post_date_gmt', 'post_author' );
			$posts = array();
			foreach ( $post_objects as $post_obj ) {
				$post_status_obj = get_post_status_object( $post_obj->post_status );
				$post = wp_array_slice_assoc( $post_obj->to_array(), $post_fields );
				$post['post_title_filtered'] = html_entity_decode( get_the_title( $post_obj->ID ), ENT_HTML5 );
				$post['post_status_label'] = $post_status_obj ? $post_status_obj->label : null;
				$post['post_type_label'] = get_post_type_object( $post['post_type'] )->label;
				if ( '0000-00-00 00:00:00' !== $post_obj->post_date_gmt ) {
					$post['post_date_timestamp'] = get_date_from_gmt( $post_obj->post_date_gmt, 'U' );
					$post['post_date_iso'] = get_date_from_gmt( $post_obj->post_date_gmt, 'c' );
				} else {
					$post['post_date_timestamp'] = null;
					$post['post_date_iso'] = null;
				}
				$posts[] = $post;
			}

			$posts = apply_filters( 'better_find_posts_results', $posts, $args, $this );

			wp_send_json_success( $posts );
		}
		catch ( Exception $e ) {
			$message = 'error';
			if ( $e instanceof Better_Find_Posts_Exception ) {
				$message = $e->getMessage();
			}
			wp_send_json_error( $message );
		}
	}

}

class Better_Find_Posts_Exception extends Exception{}

return Better_Find_Posts::instance();
