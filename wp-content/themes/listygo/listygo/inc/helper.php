<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.1
 */

namespace radiustheme\listygo;

use \WP_Query;
use radiustheme\listygo\IconTrait;
use radiustheme\listygo\CustomQueryTrait;
use radiustheme\listygo\ResourceLoadTrait;
use radiustheme\listygo\DataTrait;
use radiustheme\listygo\LayoutTrait;
use radiustheme\listygo\SocialShares;

class Helper {
  	use IconTrait;   
  	use CustomQueryTrait;   
  	use ResourceLoadTrait;    
  	use DataTrait;   
  	use LayoutTrait;   
  	use SocialShares; 
  	use SvgTrait;
	   
	public static function rt_the_logo_light() {
		if ( has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo_light = wp_get_attachment_image( $custom_logo_id, 'full' );
		} else { 
			if (!empty( RDTListygo::$options['logo'] )) {
				$logo_light = wp_get_attachment_image( RDTListygo::$options['logo'], 'full' );
			} else {
				$logo_light = '';
			}
		}
		return $logo_light;
	}
	public static function rt_the_logo_dark(){
		if ( has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo_dark = wp_get_attachment_image( $custom_logo_id, 'full' );
		} else { 
			if (!empty( RDTListygo::$options['logo_dark'] )) {
				$logo_dark = wp_get_attachment_image( RDTListygo::$options['logo_dark'], 'full' );
			} else {
				$logo_dark = '';
			}
		}
		return $logo_dark;
	}

	public static function listygo_excerpt( $limit ) {
		if (!empty($limit)) {
			$limit = $limit;
		} else {
			$limit = 0;
		}
	    $excerpt = explode(' ', get_the_excerpt(), $limit);
	    if (count($excerpt)>=$limit) {
	        array_pop($excerpt);
	        $excerpt = implode(" ",$excerpt).'';
	    } else {
	        $excerpt = implode(" ",$excerpt);
	    }
	    $excerpt = preg_replace('`[[^]]*]`','',$excerpt);

		return $excerpt;
	}

	public static function custom_sidebar_fields() {
		$listygo = LISTYGO_THEME_PREFIX_VAR;
		$sidebar_fields = array();

		$sidebar_fields['sidebar'] = esc_html__( 'Sidebar', 'listygo' );
		$sidebar_fields['sidebar-project'] = esc_html__( 'Project Sidebar ', 'listygo' );

		$sidebars = get_option( "{$listygo}_custom_sidebars", array() );
		if ( $sidebars ) {
			foreach ( $sidebars as $sidebar ) {
				$sidebar_fields[$sidebar['id']] = $sidebar['name'];
			}
		}

		return $sidebar_fields;
	}

	public static function pagination( $max_num_pages = false ) {
		global $wp_query;
		$max = $max_num_pages ? $max_num_pages : $wp_query->max_num_pages;
		$max = intval( $max );

		/** Stop execution if there's only 1 page */
		if( $max <= 1 ) return;

		$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

		/**	Add current page to the array */
		if ( $paged >= 1 )
			$links[] = $paged;

		/**	Add the pages around the current page to the array */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}
		include LISTYGO_THEME_VIEW_DIR . 'pagination.php';
	}

	public static function comments_callback( $comment, $args, $depth ){
		include LISTYGO_THEME_VIEW_DIR . 'comments-callback.php';
	}

	public static function nav_menu_args(){
		$listygo  = LISTYGO_THEME_PREFIX_VAR;
		$nav_menu_args = array( 
			'theme_location' => 'primary', 
			'container' => 'ul',
			'menu_class' => 'main-menu',
		);
		return $nav_menu_args;
	}

	public static function copyright_menu_args(){			
		$nav_menu_args = array(     
			'theme_location'  => 'crmenu',
			'depth'           => 1,
			'container'       => 'ul',
			'menu_class'      => 'footer-bottom-link',
		);	
		return $nav_menu_args;
	}

	public static function requires( $filename, $dir = false ){
		if ( $dir) {
			$child_file = get_stylesheet_directory() . '/' . $dir . '/' . $filename;
			if ( file_exists( $child_file ) ) {
				$file = $child_file;
			}
			else {
				$file = get_template_directory() . '/' . $dir . '/' . $filename;
			}
		}
		else {
			$child_file = get_stylesheet_directory() . '/inc/' . $filename;
			if ( file_exists( $child_file ) ) {
				$file = $child_file;
			}
			else {
				$file = LISTYGO_THEME_INC_DIR . $filename;
			}
		}

		require_once $file;
	}

	/**
	 * Classified Listing Plugin
	 *
	 */
	public static function get_custom_listing_template( $template, $echo = true, $args = [] ) {
		$template = 'classified-listing/custom/' . $template;
		if ( $echo ) {
			self::get_template_part( $template, $args );
		} else {
			$template .= '.php';
			return $template;
		}
	}

	/**
	 * Custom store template
	 *
	 * @param $template
	 * @param $echo
	 * @param $args
	 *
	 * @return string|void
	 */
	public static function get_custom_store_template( $template, $echo = true, $args = [] ) {
		$template = 'classified-listing/store/custom/' . $template;
		if ( $echo ) {
			self::get_template_part( $template, $args );
		} else {
			$template .= '.php';

			return $template;
		}
	}

	public static function listygo_base_color() {
		return apply_filters( 'listygo_base_color', RDTListygo::$options['listygo_base_color'] );
	}

	public static function listygo_body_color() {
		return apply_filters( 'listygo_body_color', RDTListygo::$options['listygo_body_color'] );
	}

	public static function listygo_heading_color() {
		return apply_filters( 'listygo_heading_color', RDTListygo::$options['listygo_heading_color'] );
	}
	
}