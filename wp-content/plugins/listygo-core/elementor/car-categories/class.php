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
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;
use radiustheme\listygo\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

class Rt_Car_Categories extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ){
		$this->rt_name = __( 'Car Categories', 'listygo-core' );
		$this->rt_base = 'rt-car-categories';
		parent::__construct( $data, $args );
	}

	public function rt_fields(){
		$fields = array(
			array(
				'id'      => 'sec_general',
				'mode'    => 'section_start',
				'label'   => __( 'Car Categories', 'listygo-core' ),
			),
			array(
				'id'      => 'title',
				'type'    => Controls_Manager::TEXT,
				'label'   => __( 'Title', 'listygo-core' ),
				'default' => 'Let’s Discover This City',
				'label_block' => true,
			),
			array(
				'id'      => 'car_categories',
				'label'   => esc_html__( 'Car Categories', 'listygo-core' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => Helper::car_categories_slug(),
				'multiple' => true,
				'label_block' => true,
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
					'{{WRAPPER}} .hero-categories--title' => 'color: {{VALUE}}',
				),
			),
			array(
				'name'     => 'title_typo',
				'mode'     => 'group',
				'type'     => Group_Control_Typography::get_type(),
				'label'    => __( 'Typography', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categories--title',
			),
			array(
				'mode' => 'section_end',
			),

			// Category Styles
			array(
				'id'      => 'category_style',
				'mode'    => 'section_start',
				'tab'     => Controls_Manager::TAB_STYLE,
				'label'   => esc_html__( 'Category', 'listygo-core' ),
			),
			array(
				'id'        => 'cats_color',
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'listygo-core' ),
				'selectors' => array(
					'{{WRAPPER}} .hero-categoriesBlock--style2' => 'color: {{VALUE}}',
				),
			),
			array(
				'name'     => 'icon_color',
				'mode'     => 'group',
				'type'     => Group_Control_Css_Filter::get_type(),
				'label'    => esc_html__( 'Icon Color Filter', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2 img, {{WRAPPER}} .hero-categoriesBlock--style2 svg',
			),
			array(
				'name'     => 'cats_typo',
				'mode'     => 'group',
				'type'     => Group_Control_Typography::get_type(),
				'label'    => esc_html__( 'Typography', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2',
			),
			array(
				'name'      => 'cat_bg_color',
				'mode'    => 'group',
				'type'    => Group_Control_Background::get_type(),
				'types' => [ 'classic', 'gradient', 'video' ],
				'label'   => esc_html__( 'Background', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2',
				'fields_options' => [
		            'background' => [
		                'label' => esc_html__('Category Background', 'listygo-core'),
		                'default' => 'classic',
		            ]
		        ],
			),
			array(
				'name'     => 'cat_border',
				'mode'     => 'group',
				'type'     => Group_Control_Border::get_type(),
				'label'    => __( 'Border', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2',
			),
			// Hover
			array(
				'id'      => 'border_h_color',
				'type'    => Controls_Manager::COLOR,
				'label'   => esc_html__( 'Hover Border Color', 'listygo-core' ),
				'selectors' => array( 
					'{{WRAPPER}} .hero-categoriesBlock--style2:hover' => 'border-color: {{VALUE}}', 
				),
			),
			array(
				'name'     => 'icon_h_color',
				'mode'     => 'group',
				'type'     => Group_Control_Css_Filter::get_type(),
				'label'    => esc_html__( 'Hover Icon Color', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2:hover img, {{WRAPPER}} .hero-categoriesBlock--style2:hover svg',
			),
			array(
				'name'      => 'cat_hbg_color',
				'mode'    => 'group',
				'type'    => Group_Control_Background::get_type(),
				'types' => [ 'classic', 'gradient', 'video' ],
				'label'   => esc_html__( 'Background', 'listygo-core' ),
				'selector' => '{{WRAPPER}} .hero-categoriesBlock--style2:hover',
				'fields_options' => [
		            'background' => [
		                'label' => esc_html__('Category Hover Background', 'listygo-core'),
		                'default' => 'classic',
		            ]
		        ],
			),
			array(
				'mode' => 'section_end',
			),
		);
		return $fields;
	}

	public function custom_fonts(){
		wp_enqueue_style( 'custom-fonts' );
	}

	protected function render() {
		$data = $this->get_settings();
		$this->custom_fonts();

		$template = 'view-1';

		return $this->rt_template( $template, $data );
	}
}