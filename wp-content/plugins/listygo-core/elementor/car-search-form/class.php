<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/contact-box/class.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Listygo_Core;

if (!class_exists( 'RtclPro' )) return;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use radiustheme\listygo\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Rt_Car_Search_Form extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ){
		$this->rt_name = __( 'Car Search', 'listygo-core' );
		$this->rt_base = 'rt-car-search';
		parent::__construct( $data, $args );
	}

	public function rt_fields(){
		$fields = array(
			array(
				'id'      => 'sec_general',
				'mode'    => 'section_start',
				'label'   => __( 'Car Search', 'listygo-core' ),
			),
			array(
				'id'      => 'title',
				'type'    => Controls_Manager::TEXT,
				'label'   => __( 'Title', 'listygo-core' ),
				'default' => 'Let’s Discover This City',
				'label_block' => true,
			),
			array(
				'id'      => 'getListingTypes',
				'label'   => esc_html__( 'List By Title', 'listygo-core' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->rt_get_listing_types(),
				'multiple' => true,
				'label_block' => true,
			),
			array(
				'type'    => Controls_Manager::SELECT,
				'id'      => 'listing_banner_search_style',
				'label'   => esc_html__( 'Listing Search Type', 'listygo-core' ),
				'default' => 'standard',
				'options' => array(
					'popup'      => esc_html__( 'Popup', 'listygo' ),
                    'standard'   => esc_html__( 'Standard', 'listygo' ),
                    'suggestion' => esc_html__( 'Auto Suggestion', 'listygo' ),
                    'dependency' => esc_html__( 'Dependency Selection', 'listygo' ),
				),
			),
			array(
				'mode' => 'section_end',
			),

			/* = Item Styles
			==========================================*/

			// Title Styles
			array(
				'id'      => 'title_style',
				'mode'    => 'section_start',
				'tab'     => Controls_Manager::TAB_STYLE,
				'label'   => __( 'Title', 'listygo-core' ),
			),
			array(
				'id'      => 'title_color',
				'type'    => Controls_Manager::COLOR,
				'label'   => __( 'Color', 'listygo-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .hero-content__main-title.title-v1' => 'color: {{VALUE}}', 
					'{{WRAPPER}} .hero-content__main-title.title-v2' => 'color: {{VALUE}}', 
				),
			),
			array(
				'name'     => 'title_typo',
				'mode'     => 'group',
				'type'     => Group_Control_Typography::get_type(),
				'label'    => __( 'Typography', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-content__main-title.title-v1, {{WRAPPER}} .hero-content__main-title.title-v2',
			),
			array(
				'mode' => 'section_end',
			),
		);
		return $fields;
	}

	public function custom_fonts(){
		// wp_enqueue_style( 'custom-fonts' );
	}

	protected function render() {
		$data = $this->get_settings();

		$template = 'view';

		return $this->rt_template( $template, $data );
	}
}