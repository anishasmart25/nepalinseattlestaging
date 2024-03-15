<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Traits\SingletonTrait;
use WP_Error;

class FormBuilderAjax {

	use SingletonTrait;

	public function init(): void {
		add_action( 'wp_ajax_rtcl_fb_get_category', [ $this, 'get_category' ] );
		add_action( 'wp_ajax_rtcl_fb_get_location', [ $this, 'get_location' ] );

		add_action( 'wp_ajax_rtcl_fb_gallery_image_upload', [ $this, 'gallery_image_upload' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_update_as_feature', [ $this, 'gallery_image_update_as_feature' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_delete', [ $this, 'gallery_image_delete' ] );
		add_action( 'wp_ajax_rtcl_fb_gallery_image_update_order', [ $this, 'gallery_image_update_order' ] );
		add_action( 'wp_ajax_rtcl_fb_file_upload', [ $this, 'file_upload' ] );
		add_action( 'wp_ajax_rtcl_fb_file_delete', [ $this, 'file_delete' ] );

		add_action( 'wp_ajax_rtcl_fb_get_tags', [ $this, 'get_tags' ] );
		//add_action( 'wp_ajax_rtcl_fb_remove_tags', [ $this, 'remove_tags' ] );
		//add_action( 'wp_ajax_rtcl_fb_set_tags', [ $this, 'set_tags' ] );
		add_action( 'wp_ajax_rtcl_fb_add_new_tag', [ $this, 'add_new_tag' ] );

		add_action( "wp_ajax_rtcl_update_listing", [ $this, 'update_listing' ] );

		if ( !is_user_logged_in() && Functions::is_enable_post_for_unregister() ) {
			add_action( 'wp_ajax_nopriv_rtcl_fb_get_category', [ $this, 'get_category' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_get_location', [ $this, 'get_location' ] );

			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_upload', [ $this, 'gallery_image_upload' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_update_as_feature', [ $this, 'gallery_image_update_as_feature' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_gallery_image_delete', [ $this, 'gallery_image_delete' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_file_upload', [ $this, 'file_upload' ] );
			add_action( 'wp_ajax_nopriv_rtcl_fb_file_delete', [ $this, 'file_delete' ] );

			add_action( "wp_ajax_nopriv_rtcl_update_listing", [ $this, 'update_listing' ] );

			add_action( 'wp_ajax_nopriv_rtcl_fb_get_tags', [ $this, 'get_tags' ] );
		}

	}

	/**
	 * Update listing
	 * @method POST
	 */
	public static function update_listing(): void {
		Functions::clear_notices();// Clear previous notice

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$isAdminEnd = !empty( $_POST['isAdminEnd'] );
		$postingType = 'new';
		$listing_id = !empty( $_POST['listingId'] ) ? absint( $_POST['listingId'] ) : 0;
		$listing = null;
		if ( ( $listing_id && ( !( $listing = rtcl()->factory->get_listing( $listing_id ) ) || ( $isAdminEnd && !current_user_can( 'edit_rtcl_listing', $listing_id ) ) || ( !$isAdminEnd && !Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) ) ) || ( !is_user_logged_in() && !Functions::is_enable_post_for_unregister() ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', __( 'You do not have sufficient permissions to access this page.', 'classified-listing' ), $_REQUEST, 'permission_error' ) );

			return;
		}

		if ( !$listing || $listing->get_status() === 'rtcl-temp' ) {
			$form_id = !empty( $_POST['formId'] ) ? absint( $_POST['formId'] ) : 0;
		} else {
			$form_id = absint( get_post_meta( $listing_id, '_rtcl_form_id', true ) );
		}

		if ( ( $isAdminEnd || ( $listing && !$form_id ) ) && !empty( $_POST['formId'] ) ) {
			$form_id = absint( $_POST['formId'] );
		}

		if ( empty( $form_id ) || !$form = Form::query()->select( 'sections,fields,id,title,slug' )->find( $form_id ) ) {
			wp_send_json_error( apply_filters( 'rtcl_fb_not_found_error_message', esc_html__( "Form not found !!", "classified-listing" ) ), $_REQUEST );

			return;
		}

		if ( !empty( $_POST['formData'] ) ) {
			parse_str( $_POST['formData'], $formData );
		} else {
			$formData = [];
		}
		$sections = $form->sections;
		$fields = $form->fields;
		if ( empty( $sections ) || empty( $fields ) ) {
			wp_send_json_error( apply_filters( 'rtcl_error_update_listing', __( "Missing form field", 'classified-listing' ) ) );

			return;
		}
		$errors = FBHelper::formDataValidation( $formData, $form , $listing);

		if ( !empty( $errors ) ) {
			wp_send_json_error( apply_filters( 'rtcl_error_validation_update_listing', [ 'errors' => $errors ], $formData, $sections ) );

			return;
		}

		$extraErrors = new WP_Error();

		$extraErrors = apply_filters( 'rtcl_fb_extra_form_validation', $extraErrors, $form );

		if ( $extraErrors instanceof WP_Error && $extraErrors->has_errors() ) {
			wp_send_json_error( apply_filters( 'rtcl_error_validation_update_listing', [ 'extraErrors' => $extraErrors->errors ], $formData, $sections ) );

			return;
		}


		// Data prepare
		$user_id = get_current_user_id();
		$post_for_unregister = Functions::is_enable_post_for_unregister();
		if ( !is_user_logged_in() && $post_for_unregister ) {
			if ( empty( $formData['email'] ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'missing_required_email' => __( "Missing required email to register user", 'classified-listing' ) ] ) );

				return;
			}
			$new_user_id = Functions::do_registration_from_listing_form( [ 'email' => $formData['email'] ] );
			if ( $new_user_id && is_numeric( $new_user_id ) ) {
				$user_id = $new_user_id;
				Functions::add_notice( apply_filters( 'rtcl_listing_new_registration_success_message', sprintf( __( "A new account is registered, password is sent to your email(%s).", "classified-listing" ), $formData['email'] ), $formData['email'] ) );
			}
			$message = Functions::get_notices( 'error' );
			if ( $message ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'registration_error' => $message ] ) );

				return;
			}
		}

		$metaData = [];
		$taxonomy = [ 'category' => [], 'location' => [] ];
		$post_arg = [];
		$new_listing_status = Functions::get_option_item( 'rtcl_moderation_settings', 'new_listing_status', 'pending' );
		if ( $listing ) {
			if ( ( $listing->get_listing()->post_author > 0 && $listing->get_listing()->post_author == apply_filters( 'rtcl_listing_post_user_id', get_current_user_id() ) ) || ( $listing->get_listing()->post_author == 0 && $post_for_unregister ) ) {
				if ( $listing->get_listing()->post_status === "rtcl-temp" ) {
					$post_arg['post_status'] = $new_listing_status;
				} else {
					$postingType = 'update';
					$status_after_edit = Functions::get_option_item( 'rtcl_moderation_settings', 'edited_listing_status' );
					if ( "publish" === $listing->get_listing()->post_status && $status_after_edit && $listing->get_listing()->post_status !== $status_after_edit ) {
						$post_arg['post_status'] = $status_after_edit;
					}
				}

				if ( $listing->get_listing()->post_author == 0 && $post_for_unregister ) {
					$post_arg['post_author'] = $user_id;
				}
				$post_arg['ID'] = $listing->get_id();
			}
		} else {
			$post_arg = [
				'post_status' => $new_listing_status,
				'post_author' => $user_id,
				'post_type'   => rtcl()->post_type
			];
		}

		foreach ( $fields as $fieldId => $field ) {
			$name = !empty( $field['name'] ) ? $field['name'] : '';
			$element = $field['element'];
			$rawValue = $formData[$name] ?? '';
			if ( isset( $field['preset'] ) && $field['preset'] == 1 ) {
				if ( 'title' === $element ) {
					if ( !$isAdminEnd ) {
						$post_arg['post_title'] = $rawValue;
					}
				} elseif ( 'description' === $element ) {
					if ( !$isAdminEnd ) {
						$post_arg['post_content'] = $rawValue;
					}
				} elseif ( 'listing_type' === $element ) {
					$metaData[] = [
						'name'  => 'ad_type',
						'field' => $field,
						'value' => $rawValue
					];
				} elseif ( 'excerpt' === $element ) {
					$post_arg['post_excerpt'] = $rawValue;
				} elseif ( 'category' === $element ) {
					$taxonomy['category'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return !empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'location' === $element ) {
					$taxonomy['location'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return !empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'tag' === $element ) {
					$taxonomy['tag'] = is_array( $rawValue ) ? array_filter( array_map( function ( $tag ) {
						return !empty( $tag['term_id'] ) ? absint( $tag['term_id'] ) : '';
					}, $rawValue ) ) : [];
				} elseif ( 'zipcode' === $element ) {
					$metaData[] = [
						'name'  => 'zipcode',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue )
					];
				} elseif ( 'view_count' === $element ) {
					$metaData[] = [
						'name'  => '_views',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue )
					];
				} elseif ( 'address' === $element ) {
					$metaData[] = [
						'name'  => 'address',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue, 'textarea' )
					];
				} elseif ( 'phone' === $element ) {
					$metaData[] = [
						'name'  => 'phone',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue )
					];
				} elseif ( 'whatsapp' === $element ) {
					$metaData[] = [
						'name'  => '_rtcl_whatsapp_number',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue )
					];
				} elseif ( 'email' === $element ) {
					$metaData[] = [
						'name'  => 'email',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue, 'email' )
					];
				} elseif ( 'website' === $element ) {
					$metaData[] = [
						'name'  => 'website',
						'field' => $field,
						'value' => Functions::sanitize( $rawValue, 'url' )
					];
				} elseif ( 'social_profiles' === $element ) {
					$metaData[] = [
						'name'  => '_rtcl_social_profiles',
						'field' => $field,
						'value' => FBHelper::sanitizeFieldValue( $rawValue, $field )
					];
				} elseif ( 'pricing' === $element ) {
					$pricing = $formData[$name];
					if ( !empty( $field['options'] ) && in_array( 'pricing_type', $field['options'] ) && isset( $pricing['pricing_type'] ) ) {
						$pricing_type = in_array( $pricing['pricing_type'], array_keys( Options::get_listing_pricing_types() ) ) ? $pricing['pricing_type'] : 'price';
						$metaData[] = [
							'name'  => '_rtcl_listing_pricing',
							'field' => $field,
							'value' => Functions::sanitize( $pricing_type )
						];
						if ( 'range' === $pricing_type && isset( $pricing['max_price'] ) ) {
							$metaData[] = [
								'name'  => '_rtcl_max_price',
								'field' => $field,
								'value' => Functions::format_decimal( $pricing['max_price'] )
							];
						}
					}

					if ( !empty( $field['options'] ) && in_array( 'price_type', $field['options'] ) && isset( $pricing['price_type'] ) ) {
						$metaData[] = [
							'name'  => 'price_type',
							'field' => $field,
							'value' => Functions::sanitize( $pricing['price_type'] )
						];
					}
					if ( !empty( $field['options'] ) && in_array( 'price_unit', $field['options'] ) && isset( $pricing['price_unit'] ) ) {
						$metaData[] = [
							'name'  => 'price_type',
							'field' => $field,
							'value' => Functions::sanitize( $pricing['price_unit'] )
						];
					}

					if ( isset( $pricing['price'] ) ) {
						$metaData[] = [
							'name'  => 'price',
							'field' => $field,
							'value' => Functions::format_decimal( $pricing['price'] )
						];
					}

					if ( isset( $pricing['price_unit'] ) ) {
						$metaData[] = [
							'name'  => '_rtcl_price_unit',
							'field' => $field,
							'value' => Functions::sanitize( $pricing['price_unit'] )
						];
					}
				} elseif ( 'map' === $element ) {
					$mapData = $formData[$name];
					$metaData[] = [
						'name'  => 'latitude',
						'field' => $field,
						'value' => isset( $mapData['latitude'] ) ? Functions::sanitize( $mapData['latitude'] ) : ''
					];
					$metaData[] = [
						'name'  => 'longitude',
						'field' => $field,
						'value' => isset( $mapData['longitude'] ) ? Functions::sanitize( $mapData['longitude'] ) : ''
					];
					$metaData[] = [
						'name'  => 'hide_map',
						'field' => $field,
						'value' => !empty( $mapData['hide_map'] ) ? 1 : null
					];
				} elseif ( 'terms_and_condition' === $element ) {
					if ( isset( $formData[$name] ) ) {
						$metaData[] = [
							'name'  => 'rtcl_agree',
							'field' => $field,
							'value' => !empty( $formData[$name] ) ? 1 : null
						];
					}
				} elseif ( 'business_hours' === $element ) {
					$bshValues = FBHelper::sanitizeFieldValue( $rawValue, $field );
					$metaData[] = [
						'name'  => '_rtcl_bhs',
						'field' => $field,
						'value' => $bshValues
					];
				}elseif ( 'video_urls' === $element ) {
					$videoUrls = FBHelper::sanitizeFieldValue( $rawValue, $field );
					$metaData[] = [
						'name'  => '_rtcl_video_urls',
						'field' => $field,
						'value' => $videoUrls
					];
				}
			} else {
				if ( 'file' !== $element ) {
					$sanitizedValue = FBHelper::sanitizeFieldValue( $rawValue, $field );
					$metaData[$name] = [
						'name'  => $name,
						'field' => $field,
						'value' => $sanitizedValue
					];
				}
			}
		}

		if ( $listing ) {
			if ( $listing->get_listing()->post_status === "rtcl-temp" && !empty( $post_arg['post_title'] ) ) {
				$post_arg['post_name'] = $post_arg['post_title'];
			}
			$listingUpdate = wp_update_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $postingType ) );
			if ( is_wp_error( $listingUpdate ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'wp_update_post_error' => $listingUpdate->get_error_message() ] ) );

				return;
			}
		} else {

			$listing_id = wp_insert_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $postingType ) );
			if ( is_wp_error( $listing_id ) ) {
				wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'wp_insert_post_error' => $listing_id->get_error_message() ] ) );

				return;
			}
		}

		$listing = rtcl()->factory->get_listing( $listing_id );
		$listing_id = $listing->get_id();

		$metaData[] = [
			'name'  => '_rtcl_form_id',
			'value' => $form_id
		];

		if ( !empty( $taxonomy['category'] ) && ( $isAdminEnd || $postingType === 'new' || ( $listing && $postingType === 'update' && empty( $listing->get_categories() ) ) ) ) {
			wp_set_object_terms( $listing_id, $taxonomy['category'], rtcl()->category );
		}

		if ( !empty( $taxonomy['location'] ) ) {
			wp_set_object_terms( $listing_id, $taxonomy['location'], rtcl()->location );
		}

		wp_set_object_terms( $listing_id, !empty( $taxonomy['tag'] ) ? $taxonomy['tag'] : null, rtcl()->tag );

		add_filter( 'rtcl_fb_metadata_fields_before_save', $metaData, $postingType );

		/* meta data */
		if ( !empty( $metaData ) ) {
			foreach ( $metaData as $metaItem ) {
				if ( !empty( $metaItem['name'] ) ) {
					$metaItemName = $metaItem['name'];
					if ( !$isAdminEnd && ( $postingType === 'update' && 'ad_type' === $metaItemName && $listing->get_ad_type() ) ) {
						continue;
					}
					$metaItemValue = $metaItem['value'];
					if ( !empty( $metaItem['field'] ) ) {
						if ( $metaItem['field']['element'] === 'date' ) {
							if ( is_array( $metaItemValue ) && !empty( $metaItemValue ) ) {
								foreach ( $metaItemValue as $key => $v ) {
									update_post_meta( $listing_id, $metaItemName . '_' . $key, $v );
								}
							} else {
								update_post_meta( $listing_id, $metaItemName, $metaItemValue );
							}
						} elseif ( $metaItem['field']['element'] === 'checkbox' ) {
							delete_post_meta( $listing_id, $metaItemName );
							if ( is_array( $metaItemValue ) && !empty( $metaItemValue ) ) {
								foreach ( $metaItemValue as $val ) {
									if ( $val ) {
										add_post_meta( $listing_id, $metaItemName, $val );
									}
								}
							}
						} elseif ( $metaItem['field']['element'] === 'social_profiles' ) {
							if ( !empty( $metaItemValue ) ) {
								update_post_meta( $listing->get_id(), '_rtcl_social_profiles', $metaItemValue );
							} else {
								delete_post_meta( $listing->get_id(), '_rtcl_social_profiles' );
							}
						} else {
							if ( $metaItemValue === null ) {
								delete_post_meta( $listing_id, $metaItemName );
							} else {
								update_post_meta( $listing_id, $metaItemName, $metaItemValue );
							}
						}
					} else {
						update_post_meta( $listing_id, $metaItemName, $metaItemValue );
					}
				}
			}
		}

		if ( $postingType == 'new' ) {
			update_post_meta( $listing_id, '_views', 0 );
			$current_user_id = get_current_user_id();
			$ads = absint( get_user_meta( $current_user_id, '_rtcl_ads', true ) );
			update_user_meta( $current_user_id, '_rtcl_ads', $ads + 1 );
			if ( 'publish' === $new_listing_status ) {
				Functions::add_default_expiry_date( $listing_id );
			}
			Functions::add_notice( apply_filters( 'rtcl_listing_success_message', esc_html__( "Thank you for submitting your ad!", "classified-listing" ), $listing_id, $postingType, $_REQUEST ) );
		} else {
			Functions::add_notice( apply_filters( 'rtcl_listing_success_message', esc_html__( "Successfully updated !!!", "classified-listing" ), $listing_id, $postingType, $_REQUEST ) );
		}

		do_action( 'rtcl_listing_form_after_save_or_update', $listing, $postingType, end( $taxonomy['category'] ), $new_listing_status, [
			'data'  => $_REQUEST,
			'files' => $_FILES
		] );

		$errorMessage = Functions::get_notices( 'error' );
		if ( $errorMessage ) {
			wp_send_json_error( apply_filters( 'rtcl_error_update_listing', [ 'common_error' => $errorMessage ] ) );

			return;
		}
		$message = Functions::get_notices( 'success' );
		Functions::clear_notices(); // Clear all notice created by checkin

		wp_send_json_success( apply_filters( 'rtcl_listing_form_after_save_or_update_responses', [
			'message'      => $message,
			'post_id'      => $listing_id,
			'listing_id'   => $listing_id,
			'posting_type' => $postingType,
			'redirect_url' => apply_filters(
				'rtcl_listing_form_after_update_responses_redirect_url',
				Functions::get_listing_redirect_url_after_edit_post( $postingType, $listing_id, true ),
				$postingType,
				$listing_id,
				true,
				$message
			)
		] ) );

	}

	public function gallery_image_delete() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$attach_id = isset( $_POST["attach_id"] ) ? absint( $_POST["attach_id"] ) : 0;
		$attach = get_post( $attach_id );
		if ( !$attach ) {
			wp_send_json_error( __( "Attachment does not exist.", "classified-listing" ) );

			return;
		}

		if ( $attach->post_parent != absint( Functions::request( "listingId" ) ) ) {
			wp_send_json_error( __( "Incorrect attachment ID.", "classified-listing" ) );

			return;
		}

		if ( !wp_delete_attachment( $attach_id ) ) {
			wp_send_json_error( __( "File could not be deleted.", "classified-listing" ) );

			return;
		}
		wp_send_json_success();

	}

	public function gallery_image_update_as_feature() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$attach_id = isset( $_POST["attach_id"] ) ? absint( $_POST["attach_id"] ) : 0;
		$attach = get_post( $attach_id );
		if ( !$attach ) {
			wp_send_json_error( __( "Attachment does not exist.", "classified-listing" ) );

			return;
		}

		$listingId = absint( Functions::request( "listingId" ) );

		if ( $attach->post_parent !== $listingId ) {
			wp_send_json_error( __( "Incorrect attachment ID.", "classified-listing" ) );

			return;
		}

		if ( get_post_thumbnail_id( $listingId ) === $attach_id ) {
			wp_send_json_error( __( "File is already as featured.", "classified-listing" ) );
		}

		if ( !set_post_thumbnail( $listingId, $attach_id ) ) {
			wp_send_json_error( __( "Error while making feature.", "classified-listing" ) );
		}

		wp_send_json_success( __( "Image successfully featured.", "classified-listing" ) );

	}


	public function gallery_image_update_order() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$listingId = intval( Functions::request( "listingId" ) );
		if ( ( !$listingId || !$listing = rtcl()->factory->get_listing( $listingId ) || !Functions::current_user_can( 'edit_' . rtcl()->post_type, $listingId ) ) || ( !is_user_logged_in() && !Functions::is_enable_post_for_unregister() ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to set.', 'classified-listing' ) );

			return;
		}
		$attachmentIds = !empty( $_POST["attachmentIds"] ) && is_array( $_POST["attachmentIds"] ) ? array_filter( array_map( 'absint', $_POST["attachmentIds"] ) ) : [];
		if ( empty( $attachmentIds ) ) {
			wp_send_json_error( __( "Attachment ids not exist.", "classified-listing" ) );

			return;
		}
		foreach ( $attachmentIds as $index => $attachment_id ) {
			wp_update_post( [
				'ID'         => $attachment_id,
				'menu_order' => $index
			] );
		}
		wp_send_json_success();
	}


	public function gallery_image_upload() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		if ( empty( $_FILES['image'] ) ) {
			wp_send_json_error( esc_html__( "Given file is empty to upload.", "classified-listing" ) );

			return;
		}

		Filters::beforeUpload();
		// you can use WP's wp_handle_upload() function:
		$status = wp_handle_upload( $_FILES['image'], [ 'test_form' => false ] );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $status['error'] ) ) {
			Filters::afterUpload();
			wp_send_json_error( $status['error'] );

			return;
		}

		// $filename should be the path to a file in the upload directory.
		$filename = $status['file'];

		// The ID of the post this attachment is for.
		$parent_post_id = isset( $_POST["listingId"] ) ? absint( $_POST["listingId"] ) : 0;

		// Check the type of tile. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ) );


		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'meta_input'     => [
				'_rtcl_attachment_type' => 'image'
			]
		];

		// Create post if does not exist
		if ( $parent_post_id < 1 ) {
			$oldAttachmentCount = [];
			add_filter( "post_type_link", "__return_empty_string" );

			$parent_post_id = wp_insert_post( apply_filters( "rtcl_insert_temp_post_for_image", [
				'post_title'      => __( 'RTCL Auto Temp', "classified-listing" ),
				'post_content'    => '',
				'post_status'     => Functions::get_temp_listing_status(),
				'post_author'     => wp_get_current_user()->ID,
				'post_type'       => rtcl()->post_type,
				'comments_status' => 'closed'
			] ) );

			remove_filter( "post_type_link", "__return_empty_string" );
		} else {
			$oldAttachmentIds = get_children( [
				'post_parent'    => $parent_post_id,
				'fields'         => 'ids',
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => [
					'relation' => 'OR',
					[
						'key'     => '_rtcl_attachment_type',
						'value'   => 'image',
						'compare' => '='
					],
					[
						'key'     => '_rtcl_attachment_type',
						'compare' => 'NOT EXISTS'
					]
				]
			] );
		}

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		if ( !is_wp_error( $attach_id ) ) {
			wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
			if ( !has_post_thumbnail( $parent_post_id ) ) {
				set_post_thumbnail( $parent_post_id, $attach_id );
			}
		}

		Filters::afterUpload();

		if ( !empty( $oldAttachmentIds ) ) {
			$oldAttachmentIds[] = $attach_id;
			foreach ( $oldAttachmentIds as $index => $attachment_id ) {
				wp_update_post( [
					'ID'         => $attachment_id,
					'menu_order' => $index
				] );
			}
		}
		wp_send_json_success( Functions::upload_item_data( $attach_id ) );
	}

	public function file_upload() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( esc_html__( "Given file is empty to upload.", "classified-listing" ) );

			return;
		}

		if ( empty( $_POST['form_id'] ) ) {
			wp_send_json_error( esc_html__( "From id is empty to upload.", "classified-listing" ) );

			return;
		}
		if ( empty( $_POST['field_uuid'] ) ) {
			wp_send_json_error( esc_html__( "Field id is empty to upload.", "classified-listing" ) );

			return;
		}
		$form_id = absint( $_POST['form_id'] );
		$field_uuid = Functions::request( 'field_uuid' );

		$field = FBHelper::getFormFieldByUuid( $field_uuid, '', $form_id );
		if ( empty( $field ) ) {
			wp_send_json_error( esc_html__( "No field found to upload.", "classified-listing" ) );

			return;
		}
		$fileMetaKey = !empty( $field['name'] ) ? $field['name'] : null;

		if ( empty( $fileMetaKey ) ) {
			wp_send_json_error( esc_html__( "Field name is empty.", "classified-listing" ) );

			return;
		}

		// The ID of the post this attachment is for.
		$listing_id = isset( $_POST["listingId"] ) ? absint( $_POST["listingId"] ) : 0;
		if ( $listing_id ) {
			$attachment_ids = get_post_meta( $listing_id, $fileMetaKey, true );
			$attachment_ids = !empty( $attachment_ids ) && is_array( $attachment_ids ) ? $attachment_ids : [];
			if ( !empty( $attachment_ids ) ) {
				$check_attachment_ids = get_children( [
					'fields'         => 'ids',
					'post_parent'    => $listing_id,
					'post_type'      => 'attachment',
					'post__in'       => $attachment_ids,
					'posts_per_page' => -1,
					'post_status'    => 'inherit',
					'meta_query'     => [
						[
							'key'     => '_rtcl_attachment_type',
							'value'   => 'file',
							'compare' => '='
						]
					]
				] );
				if ( $attachment_ids !== $check_attachment_ids ) {
					update_post_meta( $listing_id, $fileMetaKey, $check_attachment_ids );
					$attachment_ids = $check_attachment_ids;
				}
			}
		} else {
			$attachment_ids = [];
		}

		if ( !empty( $field['validation']['max_file_count']['value'] ) ) {
			$maxFileCount = absint( $field['validation']['max_file_count']['value'] );
			if ( $maxFileCount && count( $attachment_ids ) >= $maxFileCount ) {
				$message = !empty( $field['validation']['max_file_count']['message'] ) ? str_replace( '{value}', $maxFileCount, $field['validation']['max_file_count']['message'] ) : esc_html__( "Your file upload limit is over.", "classified-listing" );
				wp_send_json_error( $message );

				return;
			}
		}

		Filters::beforeUpload();
		// you can use WP's wp_handle_upload() function:
		$status = wp_handle_upload( $_FILES['file'], [ 'test_form' => false ] );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		if ( isset( $status['error'] ) ) {
			Filters::afterUpload();
			wp_send_json_error( $status['error'] );

			return;
		}

		// $filename should be the path to a file in the upload directory.
		$filename = $status['file'];

		// Check the type of tile. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ) );


		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'            => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type'  => $filetype['type'],
			'post_title'      => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'    => '',
			'post_status'     => 'inherit',
			'comments_status' => 'closed',
			'meta_input'      => [
				'_rtcl_attachment_type' => 'file'
			]
		];

		// Create post if does not exist
		if ( $listing_id < 1 ) {

			add_filter( "post_type_link", "__return_empty_string" );

			$listing_id = wp_insert_post( apply_filters( "rtcl_insert_temp_post_for_file", [
				'post_title'      => __( 'RTCL Auto Temp', "classified-listing" ),
				'post_content'    => '',
				'post_status'     => Functions::get_temp_listing_status(),
				'post_author'     => wp_get_current_user()->ID,
				'post_type'       => rtcl()->post_type,
				'comments_status' => 'closed'
			] ) );

			remove_filter( "post_type_link", "__return_empty_string" );
		}

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $listing_id );
		if ( !is_wp_error( $attach_id ) ) {
			wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
		}

		$attachment_ids[] = $attach_id;
		update_post_meta( $listing_id, $fileMetaKey, $attachment_ids );

		Filters::afterUpload();

		wp_send_json_success( FBHelper::getAttachmentFile( $attach_id ) );
	}

	public function file_delete() {
		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		if ( empty( $_POST['form_id'] ) ) {
			wp_send_json_error( esc_html__( "From id is empty to upload.", "classified-listing" ) );

			return;
		}
		if ( empty( $_POST['field_uuid'] ) ) {
			wp_send_json_error( esc_html__( "Field id is empty to upload.", "classified-listing" ) );

			return;
		}
		$form_id = absint( $_POST['form_id'] );
		$field_uuid = Functions::request( 'field_uuid' );

		$field = FBHelper::getFormFieldByUuid( $field_uuid, '', $form_id );
		if ( empty( $field ) ) {
			wp_send_json_error( esc_html__( "No field found to upload.", "classified-listing" ) );

			return;
		}
		$fileMetaKey = !empty( $field['name'] ) ? $field['name'] : null;

		if ( empty( $fileMetaKey ) ) {
			wp_send_json_error( esc_html__( "Field name is empty.", "classified-listing" ) );

			return;
		}

		$attach_id = isset( $_POST["attach_id"] ) ? absint( $_POST["attach_id"] ) : 0;
		$attach = get_post( $attach_id );
		if ( !$attach ) {
			wp_send_json_error( __( "Attachment does not exist.", "classified-listing" ) );

			return;
		}
		$listing_id = absint( Functions::request( "listingId" ) );

		if ( $attach->post_parent != $listing_id ) {
			wp_send_json_error( __( "Incorrect attachment ID.", "classified-listing" ) );

			return;
		}

		$attachment_ids = get_post_meta( $listing_id, $fileMetaKey, true );
		$attachment_ids = !empty( $attachment_ids ) && is_array( $attachment_ids ) ? $attachment_ids : [];
		if ( !empty( $attachment_ids ) ) {
			$check_attachment_ids = get_children( [
				'fields'         => 'ids',
				'post_parent'    => $listing_id,
				'post_type'      => 'attachment',
				'post__in'       => $attachment_ids,
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
				'meta_query'     => [
					[
						'key'     => '_rtcl_attachment_type',
						'value'   => 'file',
						'compare' => '='
					]
				]
			] );
			if ( $attachment_ids !== $check_attachment_ids ) {
				update_post_meta( $listing_id, $fileMetaKey, $check_attachment_ids );
				$attachment_ids = $check_attachment_ids;
			}
		}

		if ( empty( $attachment_ids ) || !in_array( $attach->ID, $attachment_ids ) ) {
			wp_send_json_error( __( "File file found to delete.", "classified-listing" ) );

			return;
		}

		if ( !wp_delete_attachment( $attach_id ) ) {
			wp_send_json_error( __( "File could not be deleted.", "classified-listing" ) );

			return;
		}

		wp_send_json_success();

	}

	public function get_category(): void {

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$parent_id = !empty( $_POST['parentId'] ) ? absint( $_POST['parentId'] ) : 0;
		$listingType = !empty( $_POST['listingType'] ) ? sanitize_text_field( $_POST['listingType'] ) : '';

		$categories = Functions::get_one_level_categories( $parent_id, $listingType );
		$data = [
			'success' => true,
			'message' => [],
			'cat_id'  => $parent_id
		];
		$response = apply_filters( 'rtcl_ajax_category_selection_before_post', $data );
		if ( empty( $response['success'] ) && !empty( $response['message'] ) ) {
			wp_send_json_error( $response['message'][0] );

			return;
		}

		wp_send_json_success( [
			'data' => $categories
		] );

	}

	public function get_tags(): void {

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$ids = !empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
		$excludeIds = !empty( $_POST['excludeIds'] ) && is_array( $_POST['excludeIds'] ) ? array_map( 'absint', $_POST['excludeIds'] ) : [];
		$q = !empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		$orderby = strtolower( Functions::get_option_item( 'rtcl_general_settings', 'taxonomy_orderby', 'name' ) );
		$order = strtoupper( Functions::get_option_item( 'rtcl_general_settings', 'taxonomy_order', 'DESC' ) );
		$args = [
			'hide_empty' => false,
			'orderby'    => $orderby,
			'order'      => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
			'taxonomy'   => rtcl()->tag,
			'pad_counts' => 1,
			'search'     => $q,
			'include'    => $ids,
			'exclude'    => $excludeIds
		];
		$data = [];
		$tags = get_terms( $args );
		if ( !is_wp_error( $tags ) ) {
			$data = $tags;
		}
		wp_send_json_success( [
			'data' => $data
		] );

	}

	public function add_new_tag(): void {

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$tagName = !empty( $_POST['tag_name'] ) ? sanitize_text_field( $_POST['tag_name'] ) : '';
		if ( empty( $tagName ) ) {
			wp_send_json_error( __( 'Tag name is required', 'classified-listing' ) );

			return;
		}


		$newTag = wp_create_term( $tagName, rtcl()->tag );
		if ( is_wp_error( $newTag ) ) {
			wp_send_json_error( __( 'Error while creating new tag.', 'classified-listing' ) );

			return;
		}
		$term = get_term( $newTag['term_id'], rtcl()->tag );

		if ( !$term || is_wp_error( $newTag ) ) {
			wp_send_json_error( __( 'Error while creating new tag.', 'classified-listing' ) );

			return;
		}

		wp_send_json_success( [
			'data' => $term
		] );

	}

//	public function set_tags(): void {
//
//		if ( !Functions::verify_nonce() ) {
//			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
//
//			return;
//		}
//
//		$ids = !empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
//		if ( empty( $ids ) ) {
//			wp_send_json_error( __( 'No tag found to set', 'classified-listing' ) );
//
//			return;
//		}
//
//		$listing_id = isset( $_POST["listingId"] ) ? absint( $_POST["listingId"] ) : 0;
//
//		if ( ( !$listing_id || !$listing = rtcl()->factory->get_listing( $listing_id ) || !Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) || ( !is_user_logged_in() && !Functions::is_enable_post_for_unregister() ) ) {
//			wp_send_json_error( __( 'You do not have sufficient permissions to set.', 'classified-listing' ) );
//
//			return;
//		}
//
//		$existingTagsIds = wp_get_post_terms( $listing_id, rtcl()->tag, [ 'fields' => 'ids' ] );
//		
//		$newTagsIds = array_unique(array_merge($existingTagsIds, $ids));
//		$update = wp_set_object_terms( $listing_id, $newTagsIds, rtcl()->tag );
//		if ( is_wp_error( $update ) ) {
//			wp_send_json_error(__( 'Error while adding tag.', 'classified-listing' ));
//
//			return;
//		} 
//		wp_send_json_success( [
//			'data' => wp_get_object_terms( $listing_id, rtcl()->tag )
//		] );
//
//	}
//
//	public function remove_tags(): void {
//
//		if ( !Functions::verify_nonce() ) {
//			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
//
//			return;
//		}
//
//		$ids = !empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];
//		if ( empty( $ids ) ) {
//			wp_send_json_error( __( 'No tag found to remove', 'classified-listing' ) );
//
//			return;
//		}
//
//		$listing_id = isset( $_POST["listingId"] ) ? absint( $_POST["listingId"] ) : 0;
//
//		if ( ( !$listing_id || !$listing = rtcl()->factory->get_listing( $listing_id ) || !Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing_id ) ) || ( !is_user_logged_in() && !Functions::is_enable_post_for_unregister() ) ) {
//			wp_send_json_error( __( 'You do not have sufficient permissions to remove.', 'classified-listing' ) );
//
//			return;
//		}
//
//
//		$remove = wp_remove_object_terms( $listing_id, $ids, rtcl()->tag );
//		if ( is_wp_error( $remove ) ) {
//			wp_send_json_error( __( 'Error while removing tag.', 'classified-listing' ) );
//
//			return;
//		}
//		wp_send_json_success( [
//			'data' => __( 'Successfully removed', 'classified-listing' )
//		] );
//
//	}

	public function get_location(): void {

		if ( !Functions::verify_nonce() ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}

		$parent_id = !empty( $_POST['parentId'] ) ? absint( $_POST['parentId'] ) : 0;

		$locations = Functions::get_one_level_locations( $parent_id );
		wp_send_json_success( [
			'data' => $locations
		] );

	}
}