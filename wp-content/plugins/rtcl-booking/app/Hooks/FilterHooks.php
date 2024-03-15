<?php

namespace RtclBooking\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Utility;
use Rtcl\Resources\Options;
use RtclBooking\Helpers\Functions as BookingFunctions;
use RtclBooking\Emails\BookingApprovedEmailToUser;
use RtclBooking\Emails\BookingRejectedEmailToUser;
use RtclBooking\Emails\BookingRequestEmailToOwner;

class FilterHooks {

	public static function init() {
		add_filter( 'postbox_classes_' . rtcl()->post_type . '_rtcl_booking', [
			__CLASS__,
			'add_meta_box_classes'
		] );
		add_filter( 'rtcl_account_menu_items', [ __CLASS__, 'add_bookings_menu_item_at_account_menu' ] );
		add_filter( 'rtcl_my_account_endpoint', [ __CLASS__, 'add_my_account_bookings_end_points' ] );
		if ( ! Functions::is_enable_business_hours() ) {
			add_filter( 'rtcl_sanitize', [ __CLASS__, 'sanitize_business_hours' ], 10, 3 );
		}
		add_filter( 'rtcl_email_services', [ __CLASS__, 'add_booking_email_services' ] );
		add_filter( 'body_class', [ __CLASS__, 'add_booking_class' ] );
		add_filter( 'rtcl_licenses', [ __CLASS__, 'license' ], 20 );
		add_filter( 'rtcl_listing_booking_types', [ __CLASS__, 'disable_types' ] );
	}

	public static function disable_types( $types ) {
		// remove event type
		if ( BookingFunctions::is_disable_booking_event_type() ) {
			unset( $types['event'] );
		}
		// remove service type
		if ( BookingFunctions::is_disable_booking_service_type() ) {
			unset( $types['services'] );
		}
		// remove pre-order type
		if ( BookingFunctions::is_disable_booking_pre_order_type() ) {
			unset( $types['pre_order'] );
		}
		// remove rent type
		if ( BookingFunctions::is_disable_booking_rent_type() ) {
			unset( $types['rent'] );
		}

		return $types;
	}

	/**
	 * @param array $classes
	 *
	 * @return array
	 */
	static function add_meta_box_classes( $classes = [] ) {
		array_push( $classes, sanitize_html_class( 'rtcl' ) );

		return $classes;
	}

	public static function add_bookings_menu_item_at_account_menu( $items ) {
		$position = array_search( 'edit-account', array_keys( $items ) );

		$booking = \RtclBooking\Helpers\Functions::get_all_bookings();

		$menu['my-bookings'] = apply_filters( 'rtcl_my_booking_title', esc_html__( 'My Bookings', 'rtcl-booking' ) );

		if ( ! empty( $booking ) ) {
			$menu['all-bookings'] = apply_filters( 'rtcl_all_booking_title', esc_html__( 'All Bookings', 'rtcl-booking' ) );
		}

		if ( $position > - 1 ) {
			Functions::array_insert( $items, $position, $menu );
		}

		return $items;
	}

	public static function add_my_account_bookings_end_points( $endpoints ) {
		$endpoints['my-bookings']  = Functions::get_option_item( 'rtcl_booking_settings', 'myaccount_booking_endpoint', 'my-bookings' );
		$endpoints['all-bookings'] = Functions::get_option_item( 'rtcl_booking_settings', 'myaccount_all_booking_endpoint', 'all-bookings' );

		return $endpoints;
	}

	public static function sanitize_business_hours( $sanitize_value, $raw_ohs, $type ) {

		if ( in_array( $type, [ 'business_hours', 'special_business_hours' ] ) ) {
			$new_bhs = [];
			if ( is_array( $raw_ohs ) && ! empty( $raw_ohs ) ) {
				if ( "business_hours" === $type ) {
					foreach ( Options::get_week_days() as $day_key => $day ) {
						if ( ! empty( $raw_ohs[ $day_key ] ) ) {
							$bh = $raw_ohs[ $day_key ];
							if ( ! empty( $bh['open'] ) ) {
								$new_bhs[ $day_key ]['open'] = true;
								if ( isset( $bh['times'] ) && is_array( $bh['times'] ) && ! empty( $bh['times'] ) ) {
									$new_times = [];
									foreach ( $bh['times'] as $time ) {
										if ( ! empty( $time['start'] ) && ! empty( $time['end'] ) ) {
											$start = Utility::formatTime( $time['start'], 'H:i' );
											$end   = Utility::formatTime( $time['end'], 'H:i' );
											if ( $start && $end ) {
												$new_times[] = [ 'start' => $start, 'end' => $end ];
											}
										}
									}
									if ( ! empty( $new_times ) ) {
										$new_bhs[ $day_key ]['times'] = $new_times;
									}
								}
							} else {
								$new_bhs[ $day_key ]['open'] = false;
							}
						}
					}
				} else if ( "special_business_hours" === $type ) {
					$temp_count = 0;
					$temp_keys  = [];
					foreach ( $raw_ohs as $sh_key => $sbh ) {
						if ( ! empty( $sbh['date'] ) && ! isset( $temp_keys[ $sbh['date'] ] ) && $date = Utility::formatDate( $sbh['date'], 'Y-m-d' ) ) {
							$temp_keys[] = $new_bhs[ $temp_count ]['date'] = $date;
							if ( ! empty( $sbh['open'] ) ) {
								$new_bhs[ $temp_count ]['open'] = true;
								if ( isset( $sbh['times'] ) && is_array( $sbh['times'] ) && ! empty( $sbh['times'] ) ) {
									$new_times = [];
									foreach ( $sbh['times'] as $time ) {
										if ( ! empty( $time['start'] ) && ! empty( $time['end'] ) ) {
											$start = Utility::formatTime( $time['start'], 'H:i' );
											$end   = Utility::formatTime( $time['end'], 'H:i' );
											if ( $start && $end ) {
												$new_times[] = [ 'start' => $start, 'end' => $end ];
											}
										}
									}
									if ( ! empty( $new_times ) ) {
										$new_bhs[ $temp_count ]['times'] = $new_times;
									}
								}
							} else {
								$new_bhs[ $temp_count ]['open'] = false;
							}
						}
						$temp_count ++;
					}
				}
			}

			$sanitize_value = $new_bhs;
		}

		return $sanitize_value;
	}

	public static function add_booking_class( $classes ) {
		global $wp;

		if ( Functions::is_listing() ) {
			$booking_type = BookingFunctions::get_booking_meta( get_the_ID(), 'rtcl_listing_booking_type' );
			if ( 'rent' === $booking_type ) {
				$classes[] = 'rent-type-booking';
			}
		}

		if ( isset( $wp->query_vars['my-bookings'] ) || isset( $wp->query_vars['rtcl-my-bookings'] ) ) {
			$classes[] = 'my-bookings';
		}

		if ( isset( $wp->query_vars['all-bookings'] ) || isset( $wp->query_vars['rtcl-all-bookings'] ) ) {
			$classes[] = 'all-bookings';
		}

		return $classes;
	}

	public static function add_booking_email_services( $services ) {
		$services['Booking_Approved_Email'] = new BookingApprovedEmailToUser();
		$services['Booking_Rejected_Email'] = new BookingRejectedEmailToUser();
		$services['Booking_Request_Email']  = new BookingRequestEmailToOwner();

		return $services;
	}

	public static function license( $licenses ) {
		$licenses[] = [
			'plugin_file' => RTCL_BOOKING_PLUGIN_FILE,
			'api_data'    => [
				'key_name'    => 'booking_license_key',
				'status_name' => 'booking_license_status',
				'action_name' => 'rtcl_manage_booking_licensing',
				'product_id'  => 195735,
				'version'     => RTCL_BOOKING_VERSION,
			],
			'settings'    => [
				'title' => esc_html__( 'Booking addon license key', 'rtcl-booking' ),
			],
		];

		return $licenses;
	}

}