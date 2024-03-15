<?php

namespace Rtcl\Services\FormBuilder\Components;

use Rtcl\Services\FormBuilder\AvailableFields;
use Rtcl\Services\FormBuilder\ElementCustomization;

class FieldSanitization {

	public $fields = [];

	public function __construct( $fields ) {
		$this->fields = !empty( $fields ) ? $fields : [];
	}

	public function validated() {
		return $this->fields;
	}

	public function get(): array {
		if ( !empty( $this->fields ) ) {
			$fields = [];
			foreach ( $this->fields as $fieldId => $field ) {
				if ( empty( $field['element'] ) ) {
					continue;
				}
				$sanitizeField = $this->sanitizeField( $field );
				if ( !empty( $sanitizeField ) ) {
					$fields[$fieldId] = $sanitizeField;
				}

			}
			$this->fields = $fields;
		}

		return $this->fields;
	}

	private function sanitizeField( $rawField ): array {

		$availableFields = AvailableFields::get();
		$defaultValues = !empty( $availableFields[$rawField['element']] ) ? $availableFields[$rawField['element']] : null;
		if ( empty( $defaultValues ) ) {
			return [];
		}
		$field = wp_parse_args( $rawField, $defaultValues );
		if ( isset( $defaultValues['validation'] ) ) {
			$tmpValidationRules = wp_parse_args( $field['validation'], $defaultValues['validation'] );
			$validationRules = [];
			foreach ( $tmpValidationRules as $ruleKey => $ruleValues ) {
				if ( $ruleKey === 'required' ) {
					$ruleValues['value'] = is_string( $ruleValues['value'] ) ? $ruleValues['value'] === 'true' : $ruleValues['value'];
				} else if ( $ruleKey === 'max_file_count' ) {
					$ruleValues['value'] = isset( $ruleValues['value'] ) && $ruleValues['value'] !== '' ? absint( $ruleValues['value'] ) : '';
				} else if ( $ruleKey === 'max_file_size' ) {
					$ruleValues['value'] = isset( $ruleValues['value'] ) && $ruleValues['value'] !== '' ? (int)$ruleValues['value'] : '';
				}
				$validationRules[$ruleKey] = $ruleValues;
			}

			$field['validation'] = $validationRules;
		}


		foreach ( $field as $fieldKey => $value ) {
			if ( $fieldKey === 'tnc_html' ) {
				$field[$fieldKey] = stripslashes( wp_kses( $value, ElementCustomization::allowedHtml( $fieldKey ) ) );
			} else if ( $fieldKey === 'html_codes' ) {
				$field[$fieldKey] = stripslashes( wp_kses_post( $value ) );
			} else if ( $fieldKey === 'logics' ) {
				if ( isset( $value['status'] ) && in_array( $value['status'], [ 'true', 'false' ], true ) ) {
					if ( $value['status'] === 'true' ) {
						$value['status'] = true;
					} else {
						$value = '';
					}
				}
				if ( !empty( $value['status'] ) && !empty( $value['conditions'] ) ) {
					$conditions = [];
					foreach ( $value['conditions'] as $condition ) {
						if ( !empty( $condition['fieldId'] ) && !empty( $condition['operator'] ) ) {
							$conditions[] = $condition;
						}
					}
					if ( empty( $conditions ) ) {
						$value = '';
					}
				}
				$field[$fieldKey] = $value;
			} else {
				if ( in_array( $value, [ 'true', 'false' ], true ) ) {
					$field[$fieldKey] = $value === 'true';
				}
			}
		}

		return $field;
	}

}