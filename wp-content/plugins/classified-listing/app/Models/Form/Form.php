<?php

namespace Rtcl\Models\Form;

use Rtcl\Database\Eloquent\Model;
use Rtcl\Services\FormBuilder\ElementCustomization;
use Rtcl\Services\FormBuilder\FBField;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $status
 * @property string|null $appearance_settings
 * @property array|null $sections
 * @property object|null $fields
 * @property array|null $translations
 * @property string $type
 * @property object|null $conditions
 * @property string|null $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Form extends Model {

	protected $timestamps = false;

	protected $table = 'rtcl_forms';

	protected $casts = [
		'id'           => 'absint',
		'default'      => 'boolean',
		'settings'     => 'object',
		'sections'     => 'array',
		'fields'       => 'array',
		'translations' => 'array'
	];

	/**
	 * @param string $type enum[ 'name', 'uuid']
	 * @param string $value
	 *
	 * @return mixed|null
	 */
	public function getFieldBy( $type, $value ) {
		$type = in_array( $type, [ 'name', 'uuid', 'element', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}

		if ( $type === 'uuid' ) {
			return !empty( $this->fields[$value] ) ? $this->fields[$value] : null;
		}
		$fields = $this->fields;
		if ( !empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( !empty( $field[$type] ) && $field[$type] === $value ) {
					return $field;
				}
			}
		}

		return null;
	}


	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function getFieldByName( $name ) {
		return $this->getFieldBy( 'name', $name );
	}


	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getFieldByUuid( $uuid ) {
		return $this->getFieldBy( 'uuid', $uuid );
	}

	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getFieldById( $uuid ) {
		return $this->getFieldBy( 'id', $uuid );
	}


	/**
	 * @param string $element
	 *
	 * @return mixed|null
	 */
	public function getFieldByElement( $element ) {
		return $this->getFieldBy( 'element', $element );
	}

	public function getFields() {
		return $this->fields;
	}

	public function getSections() {
		return $this->sections;
	}

	/**
	 * @param string $uuid
	 *
	 * @return mixed|null
	 */
	public function getSectionByUuid( $uuid ) {
		return $this->getSectionBy( 'uuid', $uuid );
	}


	/**
	 * @param string $id
	 *
	 * @return mixed|null
	 */
	public function getSectionById( $id ) {
		return $this->getSectionBy( 'id', $id );
	}

	/**
	 * @param string $type enum[ 'id', 'uuid']
	 * @param string $value
	 *
	 * @return mixed|null
	 */
	public function getSectionBy( $type, $value ) {
		$type = in_array( $type, [ 'uuid', 'id' ] ) ? $type : 'uuid';
		if ( empty( $value ) ) {
			return null;
		}
		if ( $type === 'uuid' ) {
			return !empty( $this->sections[$value] ) ? $this->sections[$value] : null;
		}
		$sections = $this->sections;
		if ( !empty( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( !empty( $section[$type] ) && $section[$type] === $value ) {
					return $section;
				}
			}
		}

		return null;
	}

	/**
	 * @param string $type can be preset, custom
	 * @return array|array[]|mixed
	 */
	public function getFieldAsGroup( $type = '' ) {
		$data = [ FBField::PRESET => [], FBField::CUSTOM => [] ];
		$fields = $this->fields;
		if ( !empty( $fields ) ) {
			foreach ( $fields as $fieldId => $field ) {
				$name = !empty( $field['name'] ) ? $field['name'] : '';
				if ( !$name ) {
					continue;
				}
				if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
					$data['preset'][$name] = $field;
				} else {
					$data['custom'][$name] = $field;
				}
			}
		}

		if ( in_array( $type, [ FBField::PRESET, FBField::CUSTOM, FBField::SECTIONS ] ) ) {
			return $data[$type];
		}

		return $data;
	}

	/**
	 * @return array|array[]|mixed
	 */
	public function getListableFields() {
		$fields = $this->getFieldAsGroup( FBField::CUSTOM );
		$listableFields = [];
		if ( !empty( $fields ) ) {
			$listableFields = array_filter( $fields, function ( $field ) {
				return !empty($field['listable']);
			} );
		}

		return $listableFields;
	}

	/**
	 * @param string $language_code language code to translate
	 *
	 * @return void
	 */
	public function translatedForm( $language_code ) {
		if ( !empty( $this->translations[$language_code] ) ) {
			$translation = $this->translations[$language_code];
			$tempTranslation = $translation;
			$sections = $this->sections;
			if ( !empty( $sections ) ) {
				foreach ( $sections as $sectionIndex => $section ) {
					if ( !empty( $section['uuid'] ) && !empty( $translation[$section['uuid']] ) && is_array( $translation[$section['uuid']] ) ) {
						$sections[$sectionIndex] = $this->getTranslatedField( $translation[$section['uuid']], $section );
						unset( $tempTranslation[$section['uuid']] );
					}
				}
			}
			$this->sections = $sections;

			$formFields = $this->fields;
			if ( !empty( $formFields ) && !empty( $tempTranslation ) ) {
				foreach ( $tempTranslation as $translationKey => $translation ) {
					if ( !empty( $formFields[$translationKey] ) ) {
						$formFields[$translationKey] = $this->getTranslatedField( $translation, $formFields[$translationKey] );
					}
				}
			}
			$this->fields = $formFields;
		}
	}

	/**
	 * @param array $translations
	 * @param array $field
	 *
	 * @return array
	 */
	private function getTranslatedField( $translations, $field ) {

		foreach ( $translations as $fieldKey => $_translation ) {
			if ( isset( $field[$fieldKey] ) && !empty( $_translation ) ) {
				if ( $fieldKey === 'options' || $fieldKey === 'advanced_options' ) {
					if ( is_array( $_translation ) ) {
						foreach ( $_translation as $index => $option ) {
							if ( !empty( $option['label'] ) ) {
								$field[$fieldKey][$index]['label'] = sanitize_text_field( $option['label'] );
							}
						}

					}
				} else if ( $fieldKey === 'validation' ) {
					if ( is_array( $_translation ) ) {
						foreach ( $_translation as $ruleKey => $_validation ) {
							if ( !empty( $_validation['message'] ) ) {
								$field[$fieldKey][$ruleKey]['message'] = sanitize_text_field( $_validation['message'] );
							}
						}
					}
				} elseif ( $fieldKey === 'tnc_html' ) {
					$field[$fieldKey] = stripslashes( wp_kses( $_translation, ElementCustomization::allowedHtml( $fieldKey ) ) );
				} elseif ( in_array( $fieldKey, [ 'tnc_html', 'html_codes' ] ) ) {
					$field[$fieldKey] = stripslashes( wp_kses_post( $_translation ) );
				} elseif ( $fieldKey === 'help_message' ) {
					$field[$fieldKey] = sanitize_textarea_field( $_translation );
				} else {
					$field[$fieldKey] = sanitize_text_field( $_translation );
				}
			}
		}


		return $field;
	}

}