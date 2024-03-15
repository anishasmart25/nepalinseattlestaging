<?php

namespace Rtcl\Services\FormBuilder;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;
use WP_Term;

class FBField {

	const PRESET = 'preset';
	const CUSTOM = 'custom';
	const SECTIONS = 'sections';

	protected $listing_id;

	protected $_field;

	protected $_uuid;
	protected $_is_custom;
	protected $_element;
	protected $_options;
	protected $_value;
	protected $_name;
	protected $_label;
	protected $_isFilterable;
	protected $_isListable;
	protected $_logics;

	public function __construct( array $field ) {
		$this->_field        = $field;
		$this->_element      = ! empty( $field['element'] ) ? $field['element'] : '';
		$this->_uuid         = ! empty( $field['uuid'] ) ? $field['uuid'] : '';
		$this->_options      = ! empty( $field['options'] ) ? $field['options'] : '';
		$this->_is_custom    = empty( $field['preset'] );
		$this->_name         = ! empty( $field['name'] ) ? $field['name'] : '';
		$this->_value        = ! empty( $field['value'] ) ? $field['value'] : '';
		$this->_label        = ! empty( $field['label'] ) ? $field['label'] : '';
		$this->_isFilterable = ! empty( $field['filterable'] );
		$this->_isListable   = ! empty( $field['listable'] );
		$this->_logics       = ! empty( $field['logics'] ) ? $field['logics'] : '';
	}

	public function getField() {
		return $this->_field;
	}

	/**
	 * @return mixed|string
	 */
	public function getElement() {
		return $this->_element;
	}


	/**
	 * @return mixed|array
	 */
	public function getOptions() {
		return $this->_options;
	}


	/**
	 * @return mixed|array
	 */
	public function getData( $key, $default = null ) {
		return isset( $field[ $key ] ) ? $field['value'] : $default;
	}

	/**
	 * @return mixed
	 */
	public function getMetaKey() {
		if ( $this->_is_custom ) {
			return $this->_name;
		}
//		$element = $this->getElement();
		//if( $element === '' ){
		return '';//$this->_meta_key;

	}

	/**
	 * @return mixed
	 */
	public function getLabel() {
		return $this->_label;
	}

	/**
	 * @return bool
	 */
	public function getNofollow() {
		return ! empty( $field['nofollow'] );
	}

	/**
	 * @return mixed
	 */
	public function getTarget() {
		return ! empty( $field['target'] ) ? $field['target'] : '';
	}

	/**
	 * @return string|array|null
	 */
	public function getDefaultValue() {
		if ( $this->_element == 'checkbox' ) {
			return ! empty( $this->_value ) && is_array( $this->_value ) ? array_map( 'trim', $this->_value ) : [];
		} else {
			return ! empty( $this->_value ) ? trim( $this->_value ) : null;
		}
	}


	/**
	 * @param $listing_id
	 *
	 * @return array|mixed
	 */
	public function getValue( $listing_id ) {
		$element = $this->getElement();
		$metaKey = $this->getMetaKey();
		if ( ! Functions::meta_exist( $listing_id, $this->getMetaKey() ) && $element != 'date' ) {
			$value = $this->getDefaultValue();
		} else {
			if ( $element == 'checkbox' ) {
				$value = get_post_meta( $listing_id, $this->getMetaKey() );
			} elseif ( $element == 'date' ) {
				$dateType   = ! empty( $this->field['date_type'] ) ? $this->field['date_type'] : 'single';
				$dateFormat = ! empty( $this->field['date_format'] ) ? $this->field['date_format'] : 'Y-d-m H:i';

				if ( 'range' === $dateType ) {
					$value = [
						'start' => get_post_meta( $listing_id, $metaKey . '_' . 'start', true ),
						'end'   => get_post_meta( $listing_id, $metaKey . '_' . 'end', true )
					];

					$value['start'] = ! empty( $value['start'] ) ? date( $dateFormat, strtotime( $value['start'] ) ) : null;
					$value['end']   = ! empty( $value['end'] ) ? date( $dateFormat, strtotime( $value['end'] ) ) : null;
				} else {
					$value = get_post_meta( $listing_id, $metaKey, true );
					$value = ! empty( $value ) ? date( $dateFormat, strtotime( $value ) ) : '';
				}
			} elseif ( $element == 'file' ) {
				$value = FBHelper::getFieldAttachmentFiles( $listing_id, $this->_field );
			} else {
				if ( empty( $this->field['multiple'] ) ) {
					$value = get_post_meta( $listing_id, $metaKey, true );
				} else {
					$value = get_post_meta( $listing_id, $metaKey );
				}
			}
		}

		return $value;
	}

	/**
	 * @param int $listing_id Listing id
	 *
	 * @return array|mixed|string|null
	 */
	public function getFormattedCustomFieldValue( int $listing_id ) {

		$value = $this->getValue( $listing_id );
		if ( 'url' == $this->getElement() && filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$value = esc_url( $value );
		} else if ( in_array( $this->getElement(), [ 'select', 'radio' ] ) ) {
			$options = $this->getOptions();
			$_value  = [];
			if ( ! empty( $options ) ) {
				foreach ( $options as $option ) {
					if ( ! empty( $option['value'] ) && $option['value'] === $value ) {
						$_value = $option;
					}
				}
			}
			$value = $_value;
		} else if ( 'checkbox' == $this->getElement() && is_array( $value ) && ! empty( $value ) ) {
			$options = $this->getOptions();
			$_value  = [];
			if ( ! empty( $options ) ) {
				foreach ( $options as $option ) {
					if ( ! empty( $option['value'] ) && in_array( $option['value'], $value ) ) {
						$_value[] = $option;
					}
				}
			}
			$value = $_value;
		} else if ( 'date' == $this->getElement() ) {
			if ( 'range' === $this->getDateType() ) {
				$start = ! empty( $value['start'] ) ? $value['start'] : null;
				$end   = ! empty( $value['end'] ) ? $value['end'] : null;
				$value = $end ? $start . " - " . $end : $start;
			}
		}

		return apply_filters( 'rtcl_fb_formatted_custom_field_value', $value, $this );
	}

	public function getDateType(): string {
		return ! empty( $this->field['date_type'] ) && $this->field['date_type'] === 'range' ? 'range' : 'single';
	}

	public function getDateFormat() {
		return ! empty( $this->field['date_format'] ) ? $this->field['date_format'] : 'Y-d-m H:i';
	}

	public function getDateFormatType(): string {
		$dateFormat = $this->getDateFormat();
		if ( in_array( $dateFormat, [ 'h:i A', 'H:i' ] ) ) {
			return 'TIME';
		}
		if ( strpos( $dateFormat, 'h:i A' ) !== false || strpos( $dateFormat, 'H:i' ) !== false ) {
			return 'DATETIME';
		}

		return 'DATE';
	}

	public function getDateFilterDateType(): string {
		return ! empty( $this->field['filter_date_type'] ) && $this->field['filter_date_type'] === 'range' ? 'range' : 'single';
	}

	/**
	 * @return bool
	 */
	public function isFilterable(): bool {
		return $this->_isFilterable;
	}

	/**
	 * @return bool
	 */
	public function isListable(): bool {
		return $this->_isListable;
	}


	public function getDateFieldOptions( $data = [] ) {
		$dateType   = ! empty( $this->field['date_type'] ) ? $this->field['date_type'] : 'single';
		$dateFormat = ! empty( $this->field['date_format'] ) ? $this->field['date_format'] : 'Y-d-m H:i';
		$js_options = Options::get_date_js_format_placeholder();
		$find       = array_keys( $js_options );
		$replace    = array_values( $js_options );
		$format     = str_replace( $find, $replace, $dateFormat );
		$options    = wp_parse_args( $data, [
			'singleDatePicker' => $dateType === 'single',
			'showDropdowns'    => true,
			'timePicker'       => false !== strpos( $dateFormat, 'h:i A' ) || false !== strpos( $dateFormat, 'H:i' ),
			'timePicker24Hour' => false !== strpos( $dateFormat, 'H:i' ),
			'locale'           => [
				'format' => $format
			]
		] );

		return apply_filters( 'rtcl_custom_field_date_options', $options, $this );
	}

	/**
	 * @param object $currentTerm Current category
	 * @param array  $data        All from data fields
	 *
	 * @return boolean
	 */
	public function isValidCategoryCondition( $currentTerm, array &$data ) {

		$presetFields = ! empty( $data[ FBField::PRESET ] ) ? $data[ FBField::PRESET ] : [];

		// check is validate for section condition
		$sections = ! empty( $data[ FBField::SECTIONS ] ) ? $data[ FBField::SECTIONS ] : [];
		if ( ! empty( $sections ) ) {
			foreach ( $sections as $sectionIndex => $section ) {

				if ( empty( $section['logics']['status'] ) || empty( $section['logics']['conditions'] ) ) {
					continue;
				}

				// Casing loop
				if ( isset( $data[ FBField::SECTIONS ][ $sectionIndex ]['fieldsIds'] ) ) {
					$fieldsIds = $data[ FBField::SECTIONS ][ $sectionIndex ]['fieldsIds'];
				} else {
					$fieldsIds = [];
					if ( ! empty( $section['columns'] ) ) {
						foreach ( $section['columns'] as $column ) {
							if ( ! empty( $column['fields'] ) && is_array( $column['fields'] ) ) {
								$fieldsIds = array_merge( $fieldsIds, $column['fields'] );
							}
						}
					}
					$data[ FBField::SECTIONS ][ $sectionIndex ]['fieldsIds'] = $fieldsIds;
				}

				if ( ! in_array( $this->_uuid, $fieldsIds ) ) {
					continue;
				}

				// Casing loop
				if ( isset( $data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] ) ) {
					if ( $data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] === true ) {
						continue;
					}
					if ( $data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] === false ) {
						return false;
					}

				}

				$relation  = ! empty( $section['logics']['relation'] ) && $section['logics']['relation'] === 'and' ? 'and' : 'or';
				$validate  = [];
				$cacheCats = [];
				foreach ( $section['logics']['conditions'] as $condition ) {
					if ( empty( $condition['fieldId'] ) || empty( $condition['operator'] ) || empty( $presetFields[ $condition['fieldId'] ] ) || 'category' !== $presetFields[ $condition['fieldId'] ]['element'] ) {
						continue;
					}
					$value  = absint( $condition['value'] );
					$catIds = [];
					if ( $value ) {
						if ( ! isset( $cacheCats[ $value ] ) ) {
							$childTerms          = get_term_children( $value, rtcl()->category );
							$catIds              = ! is_wp_error( $childTerms ) ? $childTerms : [];
							$catIds[]            = $value;
							$cacheCats[ $value ] = $catIds;
						} else {
							$catIds = $cacheCats[ $value ];
						}
					}
					if ( $condition['operator'] === 'empty' ) {
						$validate[] = ! is_a( $currentTerm, WP_Term::class ) || ! rtcl()->category === $currentTerm->taxonomy;
					} else if ( $condition['operator'] === 'notEmpty' ) {
						$validate[] = is_a( $currentTerm, WP_Term::class ) && rtcl()->category === $currentTerm->taxonomy;
					} else if ( in_array( $condition['operator'], [ 'contains', '=' ] ) ) {
						$validate[] = empty( $value ) || is_a( $currentTerm, WP_Term::class ) && rtcl()->category === $currentTerm->taxonomy && in_array( $currentTerm->term_id, $catIds );
					} else if ( in_array( $condition['operator'], [ 'doNotContains', '!=' ] ) ) {
						$validate[] = empty( $value ) || ! is_a( $currentTerm, WP_Term::class ) || ! rtcl()->category === $currentTerm->taxonomy || ! in_array( $currentTerm->term_id, $catIds );
					}
				}

				if ( empty( $validate ) ) {
					$data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] = true;
					continue;
				}

				if ( $relation === 'and' && in_array( false, $validate, true ) ) {
					$data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] = false;

					return false;
				}
				if ( $relation === 'or' && ! in_array( true, $validate, true ) ) {
					$data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] = false;

					return false;
				}
				$data[ FBField::SECTIONS ][ $sectionIndex ]['catValidation'] = true;
			}
		}


		if ( ! empty( $this->_logics['status'] ) && ! empty( $this->_logics['conditions'] ) ) {
			$relation  = ! empty( $this->_logics['relation'] ) && $this->_logics['relation'] === 'and' ? 'and' : 'or';
			$validate  = [];
			$cacheCats = [];
			foreach ( $this->_logics['conditions'] as $condition ) {

				if ( empty( $condition['fieldId'] ) || empty( $condition['operator'] ) || empty( $presetFields[ $condition['fieldId'] ] ) || 'category' !== $presetFields[ $condition['fieldId'] ]['element'] ) {
					continue;
				}
				$value  = absint( $condition['value'] );
				$catIds = [];
				if ( $value ) {
					if ( ! isset( $cacheCats[ $value ] ) ) {
						$childTerms          = get_term_children( $value, rtcl()->category );
						$catIds              = ! is_wp_error( $childTerms ) ? $childTerms : [];
						$catIds[]            = $value;
						$cacheCats[ $value ] = $catIds;
					} else {
						$catIds = $cacheCats[ $value ];
					}
				}
				if ( $condition['operator'] === 'empty' ) {
					$validate[] = ! is_a( $currentTerm, WP_Term::class ) || ! rtcl()->category === $currentTerm->taxonomy;
				} else if ( $condition['operator'] === 'notEmpty' ) {
					$validate[] = is_a( $currentTerm, WP_Term::class ) && rtcl()->category === $currentTerm->taxonomy;
				} else if ( in_array( $condition['operator'], [ 'contains', '=' ] ) ) {
					$validate[] = empty( $value ) || is_a( $currentTerm, WP_Term::class ) && rtcl()->category === $currentTerm->taxonomy && in_array( $currentTerm->term_id, $catIds );
				} else if ( in_array( $condition['operator'], [ 'doNotContains', '!=' ] ) ) {
					$validate[] = empty( $value ) || ! is_a( $currentTerm, WP_Term::class ) || ! rtcl()->category === $currentTerm->taxonomy || ! in_array( $currentTerm->term_id, $catIds );
				}

			}

			if ( empty( $validate ) ) {
				return true;
			}

			if ( $relation === 'and' ) {
				return ! in_array( false, $validate, true );
			} else {
				return in_array( true, $validate, true );
			}
		}


		return true;
	}
}