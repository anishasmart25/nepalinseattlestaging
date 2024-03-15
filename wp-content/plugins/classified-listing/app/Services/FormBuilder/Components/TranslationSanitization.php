<?php

namespace Rtcl\Services\FormBuilder\Components;

use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\ElementCustomization;

class TranslationSanitization {

	/**
	 * @var array|mixed
	 */
	private $translations;
	/**
	 * @var Form
	 */
	private $form;

	public function __construct( $form, $translations ) {
		$this->form         = $form;
		$this->translations = ! empty( $translations ) ? $translations : [];
	}


	public function get(): ?array {
		$translations = [];
		if ( ! empty( $this->translations )  ) {
			foreach ( $this->translations as $lngCode => $rawTranslations ) {
				if ( ! empty( $rawTranslations ) ) {
					foreach ( $rawTranslations as $fieldUuid => $_translations ) {
						if ( is_array( $_translations ) ) {
							foreach ( $_translations as $fieldKey => $_translation ) {
								if ( empty( $_translation ) ) {
									continue;
								}

								if ( $fieldKey === 'options' || $fieldKey === 'advanced_options' ) {
									if ( is_array( $_translation ) ) {
										$options = [];
										foreach ( $_translation as $index => $option ) {
											if ( !empty($option['label']) ) {
												$options[ $index ]['label'] = sanitize_text_field( $option['label'] );
											}
										}
										if ( ! empty( $options ) ) {
											$value = $options;
										}
									}
								} else if ( $fieldKey === 'validation' ) {
									if ( is_array( $_translation ) ) {
										$validation = [];
										foreach ( $_translation as $ruleKey => $_validation ) {
											if ( !empty($_validation['message']) ) {
												$validation[ $ruleKey ]['message'] = sanitize_text_field( $_validation['message'] );
											}
										}
										if ( ! empty( $validation ) ) {
											$value = $validation;
										}
									}
								} elseif ( $fieldKey === 'tnc_html' ) {
									$value = stripslashes( wp_kses( $_translation, ElementCustomization::allowedHtml( $fieldKey ) ) );
								} elseif ( in_array( $fieldKey, [ 'tnc_html', 'html_codes' ] ) ) {
									$value = stripslashes( wp_kses_post( $_translation ) );
								} elseif ( $fieldKey === 'help_message' ) {
									$value = sanitize_textarea_field( $_translation );
								} else {
									$value = sanitize_text_field( $_translation );
								}
								if ( ! empty( $value ) ) {
									$translations[ $lngCode ][ $fieldUuid ][ $fieldKey ] = $value;
								}
							}
						}
					}
				}
			}
		}

		return ! empty( $translations ) ? $translations : null;
	}

}