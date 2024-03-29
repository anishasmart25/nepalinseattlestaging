<?php
/**
 * Created by PhpStorm.
 * User: mahbubur
 * Date: 8/9/18
 * Time: 4:54 PM
 */

namespace RtclStore\Controllers\Ajax;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;

class LoadMore {

	static function init() {
		add_action( 'wp_ajax_rtcl_store_ad_load_more', [ __CLASS__, 'rtcl_store_ad_load_more' ] );
		add_action( 'wp_ajax_nopriv_rtcl_store_ad_load_more', [ __CLASS__, 'rtcl_store_ad_load_more' ] );
		add_action( 'wp_ajax_rtcl_store_load_more_store', [ __CLASS__, 'store_load_more' ] );
		add_action( 'wp_ajax_nopriv_rtcl_store_load_more_store', [ __CLASS__, 'store_load_more' ] );
	}

	static function rtcl_store_ad_load_more() {
		$complete       = false;
		$html           = '';
		$current_page   = isset( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : 0;
		$max_num_pages  = isset( $_POST['max_num_pages'] ) ? absint( $_POST['max_num_pages'] ) : 0;
		$store_id       = isset( $_POST['store_id'] ) ? absint( $_POST['store_id'] ) : 0;
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : - 1;

		if ( $current_page && $max_num_pages && $store_id && $max_num_pages > $current_page ) {
			$current_page ++;
			$complete        = true;
			$args            = array(
				'post_type'      => rtcl()->post_type,
				'post_status'    => 'publish',
				'posts_per_page' => $posts_per_page ? $posts_per_page : - 1,
				'author'         => get_post_meta( $store_id, 'store_owner_id', true ),
				'paged'          => $current_page,
			);
			$store_ads_query = new \WP_Query( $args );

			if ( ! empty( $store_ads_query->posts ) ) {
				$global_listing        = null;
				$GLOBALS['rtclIsAjax'] = true;
				if ( isset( $GLOBALS['listing'] ) ) {
					$global_listing = $GLOBALS['listing'];
				}
				foreach ( $store_ads_query->posts as $post_id ) {
					$GLOBALS['listing'] = rtcl()->factory->get_listing( $post_id );
					ob_start();
					Functions::get_template_part( 'content', 'listing' );
					$html .= ob_get_clean();
				}
				if ( $global_listing ) {
					$GLOBALS['listing'] = $global_listing;
				} else {
					unset( $GLOBALS['listing'] );
				}
				unset( $GLOBALS['rtclIsAjax'] );
			}
		} else {
			$current_page = $max_num_pages;
		}

		wp_send_json( array(
			'complete'     => $complete,
			'current_page' => $current_page,
			'html'         => $html
		) );
	}

	static function store_load_more() {

		$offset        = $_POST["offset"];
		$data          = $_POST["queryArg"];
		$options       = $_POST["display"];
		$post_per_page = $_POST["post_per_page"];
		$layout        = $_POST["layout"];

		$args = [
			'post_type'           => 'store',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => $post_per_page,
			'offset'              => $offset,
		];

		// Taxonomy
		if ( ! empty( $data['store_cat'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'store_category',
					'field'    => 'term_id',
					'terms'    => $data['store_cat'],
				],
			];
		}

		$args['orderby'] = $data['orderby'];
		$args['order']   = $data['order'];

		$post = new \WP_Query( $args );

		$layout = ( $layout == 'list' ) ? 'store-list-item' : 'store-grid-item';

		while ( $post->have_posts() ) {
			$post->the_post();
			$data = [
				'id'       => get_the_ID(),
				'instance' => $options,
			];
			Functions::get_template( "elementor/{$layout}", $data, '', rtclStore()->get_plugin_template_path() );
		}

		wp_reset_postdata();

		die();
	}

}