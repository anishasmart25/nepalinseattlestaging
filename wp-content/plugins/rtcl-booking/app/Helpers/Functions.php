<?php

namespace RtclBooking\Helpers;

use Rtcl\Helpers\Functions as RtclFunctions;

class Functions {

	public static function is_active_booking( $post_id = 0 ) {
		return (bool) self::get_booking_meta( $post_id, '_rtcl_booking_active' );
	}

	public static function is_enable_booking() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'booking_enable', false, 'checkbox' );
	}

	public static function is_disable_booking_event_type() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'disable_event_type', false, 'checkbox' );
	}

	public static function is_disable_booking_service_type() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'disable_service_type', false, 'checkbox' );
	}

	public static function is_disable_booking_pre_order_type() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'disable_pre_oder_type', false, 'checkbox' );
	}

	public static function is_disable_booking_rent_type() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'disable_rent_type', false, 'checkbox' );
	}

	public static function get_booking_type( $post_id = 0 ) {
		return self::get_booking_meta( $post_id, 'rtcl_listing_booking_type' );
	}

	public static function get_booked_ticket( $listing_id ) {
		global $wpdb;

		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";

		$results = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(quantity) FROM {$booking_info_table} WHERE listing_id = %d AND (status = %s OR status = %s)", [
			$listing_id,
			'pending',
			'approved'
		] ) );

		if ( ! empty( $results ) ) {
			return (int) $results;
		}

		return 0;

	}

	/*
	 * @since 1.0.0
	 *
	 * @param int $listing_id
	 * @param string $meta_key
	 *
	 * @return mixed array | string | null
	 */

	public static function get_booking_meta( $listing_id, $meta_key = '' ) {
		global $wpdb;

		$booking_meta_table = $wpdb->prefix . "rtcl_booking_meta";

		if ( empty( $meta_key ) ) {
			$data  = [];
			$query = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$booking_meta_table} WHERE listing_id = %d", [
				$listing_id
			] ), ARRAY_A );

			if ( ! empty( $query ) ) {
				foreach ( $query as $metas ) {
					if ( isset( $metas['meta_key'] ) && isset( $metas['meta_value'] ) ) {
						$data[ $metas['meta_key'] ] = $metas['meta_value'];
					}
				}
			}

			return $data;
		} else {
			return $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$booking_meta_table} WHERE listing_id = %d AND meta_key = %s", [
				$listing_id,
				$meta_key
			] ) );
		}
	}

	/*
	 * @since 1.0.0
	 *
	 * @param int $listing_id
	 * @param string $meta_key
	 * @param string | array $meta_value
	 *
	 * @return boolean
	 */

	public static function update_booking_meta( $listing_id, $meta_key, $meta_value ) {
		global $wpdb;

		$booking_meta_table = $wpdb->prefix . "rtcl_booking_meta";

		$listing_id = absint( $listing_id );

		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$booking_meta_table} WHERE meta_key = %s AND listing_id = %d", [
			$meta_key,
			$listing_id
		] ) );

		if ( empty( $meta_id ) ) {
			return self::add_booking_meta( $listing_id, $meta_key, $meta_value );
		}

		$meta_value = maybe_serialize( $meta_value );

		$where = [
			'listing_id' => $listing_id,
			'meta_key'   => $meta_key,
		];

		$data = [
			'meta_value' => $meta_value
		];

		$result = $wpdb->update( $booking_meta_table, $data, $where );

		if ( ! $result ) {
			return false;
		}

		return true;
	}

	/*
	 * @since 1.0.0
	 *
	 * @param int $listing_id
	 * @param string $meta_key
	 * @param string | array $meta_value
	 *
	 * @return boolean
	 */

	public static function add_booking_meta( $listing_id, $meta_key, $meta_value ) {
		global $wpdb;

		$booking_meta_table = $wpdb->prefix . "rtcl_booking_meta";

		$listing_id = absint( $listing_id );

		$result = $wpdb->insert(
			$booking_meta_table,
			[
				'listing_id' => $listing_id,
				'meta_key'   => $meta_key,
				'meta_value' => maybe_serialize( $meta_value ),
			]
		);

		if ( ! $result ) {
			return false;
		}

		$mid = (int) $wpdb->insert_id;

		return $mid;
	}

	/*
	 * @since 1.0.0
	 *
	 * @param int $listing_id
	 * @param string $meta_key
	 *
	 * @return boolean
	 */

	public static function delete_booking_meta( $listing_id, $meta_key ) {
		global $wpdb;

		$booking_meta_table = $wpdb->prefix . "rtcl_booking_meta";

		$listing_id = absint( $listing_id );

		$query = $wpdb->prepare( "SELECT id FROM {$booking_meta_table} WHERE meta_key = %s AND listing_id = %d", [
			$meta_key,
			$listing_id
		] );

		$meta_ids = $wpdb->get_col( $query );
		if ( ! count( $meta_ids ) ) {
			return false;
		}

		$query = "DELETE FROM $booking_meta_table WHERE id IN( " . implode( ',', $meta_ids ) . ' )';

		$count = $wpdb->query( $query );

		if ( ! $count ) {
			return false;
		}

		return true;

	}

	public static function get_my_bookings() {
		global $wpdb;

		$user_id            = get_current_user_id();
		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$booking_info_table} WHERE user_id = %d ORDER BY requested_at DESC", [
			$user_id
		] ) );

		return $results;
	}

	public static function get_days_from_date_range( $date_range ) {
		$days = 0;
		if ( ! empty( $date_range ) ) {
			$date  = explode( ' - ', $date_range );
			$start = date_create( $date[0] );
			$end   = date_create( $date[1] );
			$diff  = date_diff( $start, $end );
			$days  = $diff->format( '%a' );
		}

		return $days;
	}

	public static function get_my_booking_count() {
		global $wpdb;

		$user_id            = get_current_user_id();
		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$booking_info_table} WHERE user_id = %d ORDER BY requested_at DESC", [
			$user_id
		] ) );

		return count( $results );
	}

	public static function get_all_bookings( $status = '' ) {
		global $wpdb;

		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";
		$user_id            = get_current_user_id();

		$where      = "listing_owner_id='" . absint( $user_id ) . "'";
		$where_text = " WHERE " . $where;

		if ( ! empty( $status ) ) {
			$where_text .= " AND status='" . $status . "'";
		}

		$results = $wpdb->get_results( "SELECT * FROM {$booking_info_table} {$where_text} ORDER BY requested_at DESC" );

		return $results;
	}

	public static function get_booking_fee_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'booking_fee_label', esc_html__( 'Booking Fee', 'rtcl-booking' ), 'text' );
	}

	public static function get_available_ticket_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'avaiable_ticket_label', esc_html__( 'Available Tickets', 'rtcl-booking' ), 'text' );
	}

	public static function get_booking_user_details( $booking_id = 0 ) {
		global $wpdb;

		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT details FROM {$booking_info_table} WHERE id = %d", [
			$booking_id,
		] ) );

		if ( ! empty( $results ) ) {
			return $results;
		}

		return 0;
	}

	public static function get_rent_booked_date( $post_id, $status = 'approved' ) {
		global $wpdb;

		$booking_info_table = $wpdb->prefix . "rtcl_booking_info";
		$booking_type       = self::get_booking_meta( $post_id, 'rtcl_listing_booking_type' );

		$transient_key = 'rtcl_rent_booked_dates_' . $status . '_' . $post_id;
		//delete_transient( $transient_key );
		$dates = get_transient( $transient_key );

		if ( false === $dates ) {
			$dates = array();
			if ( 'rent' == $booking_type ) {
				$results
					= $wpdb->get_results( $wpdb->prepare( "SELECT listing_id, booking_date FROM {$booking_info_table} WHERE listing_id = %d and status = %s", [
					$post_id,
					$status
				] ) );

				foreach ( $results as $row ) {
					if ( $row->booking_date ) {
						$date       = explode( ' - ', $row->booking_date );
						$date_from  = new \DateTime( $date[0] );
						$date_to    = new \DateTime( $date[1] );
						$interval   = new \DateInterval( 'P1D' );
						$date_range = new \DatePeriod( $date_from, $interval, $date_to );
						foreach ( $date_range as $dr ) {
							$dates[] = $dr->format( "Y-m-d" );
						}

					}
				}
			}
			set_transient( $transient_key, $dates, DAY_IN_SECONDS );
		}

		return $dates;

	}

	public static function get_ticket_allowed_per_booking_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'allowed_per_booking_label',
			esc_html__( 'Tickets allowed per booking', 'rtcl-booking' ), 'text' );
	}

	public static function get_instant_booking_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'instant_booking_label', esc_html__( 'Instant Booking', 'rtcl-booking' ), 'text' );
	}

	public static function get_maximum_number_of_guest_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'max_guest_label', esc_html__( 'Maximum Number of Guests', 'rtcl-booking' ), 'text' );
	}

	public static function get_service_schedule_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'schedule_label', esc_html__( 'Schedule', 'rtcl-booking' ), 'text' );
	}

	public static function get_booking_confirmation_endpoint() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'booking_confirmation_endpoint', 'booking', 'text' );
	}

	public static function get_booking_button_text() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'booking_button_text', esc_html__( 'Book Now', 'rtcl-booking' ), 'text' );
	}

	public static function get_pre_order_availalbe_volumn_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'pre_order_available_volumn', esc_html__( 'Available Volumn', 'rtcl-booking' ),
			'text' );
	}

	public static function get_pre_order_availalbe_date_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'pre_order_available_date', esc_html__( 'Available From', 'rtcl-booking' ), 'text' );
	}

	public static function get_pre_order_date_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'pre_order_date_label', esc_html__( 'Pre-Order Date', 'rtcl-booking' ), 'text' );
	}

	public static function get_pre_order_per_order_label() {
		return RtclFunctions::get_option_item( 'rtcl_booking_settings', 'allowed_per_order_label', esc_html__( 'Maximum Order per booking', 'rtcl-booking' ),
			'text' );
	}

	public static function check_event_type_availability( $listing_id, $no_of_ticket = 0 ) {
		$ticketAllowedPerBooking = (int) self::get_booking_meta( $listing_id, '_rtcl_booking_allowed_ticket' );
		$ticketAllowedPerBooking = ! empty( $ticketAllowedPerBooking ) ? $ticketAllowedPerBooking : apply_filters( 'rtcl_booking_allowed_ticket', 5 );

		$msg   = '';
		$error = false;

		if ( $no_of_ticket > $ticketAllowedPerBooking ) {
			$msg   = apply_filters( 'rtcl_booking_not_available_ticket_message',
				sprintf( esc_html__( 'Maximum ticket allowed per booking %s', 'rtcl-booking' ), $ticketAllowedPerBooking ) );
			$error = true;
		} else {
			$ticket_booked    = self::get_booked_ticket( $listing_id );
			$available_ticket = (int) self::get_booking_meta( $listing_id, '_rtcl_available_tickets' );

			if ( $no_of_ticket ) {
				if ( $available_ticket <= $ticket_booked || ( $ticket_booked + $no_of_ticket ) > $available_ticket ) {
					$msg   = apply_filters( 'rtcl_booking_not_available_ticket_message', esc_html__( 'Ticket not available', 'rtcl-booking' ) );
					$error = true;
				}
			}
		}

		return array(
			'error'   => $error,
			'message' => $msg
		);
	}

	public static function check_services_type_availability( $listing_id, $booking_date, $time_slot, $no_of_ticket ) {
		$max_guest = (int) self::get_booking_meta( $listing_id, '_rtcl_booking_max_guest' );
		$msg       = '';
		$error     = false;

		if ( $no_of_ticket > $max_guest ) {
			$msg   = apply_filters( 'rtcl_booking_max_allowed_message', sprintf( esc_html__( 'Maximum allowed guest %s', 'rtcl-booking' ), $max_guest ) );
			$error = true;
		}

		return array(
			'error'   => $error,
			'message' => $msg
		);
	}

	public static function check_pre_order_type_availability( $listing_id, $no_of_ticket ) {
		$available_volumn = self::get_booking_meta( $listing_id, '_rtcl_booking_pre_order_available_volumn' );
		$ticket_booked    = self::get_booked_ticket( $listing_id );
		$msg              = '';
		$error            = false;

		if ( $no_of_ticket ) {
			if ( $available_volumn <= $ticket_booked || ( $ticket_booked + $no_of_ticket ) > $available_volumn ) {
				$msg   = apply_filters( 'rtcl_booking_not_available_ticket_message', esc_html__( 'Item does not available.', 'rtcl-booking' ) );
				$error = true;
			}
		}

		return array(
			'error'   => $error,
			'message' => $msg
		);
	}

	public static function get_day_index( $day ) {
		switch ( $day ) {
			case 'mon':
				$index = 1;
				break;
			case 'tue':
				$index = 2;
				break;
			case 'wed':
				$index = 3;
				break;
			case 'thu':
				$index = 4;
				break;
			case 'fri':
				$index = 5;
				break;
			case 'sat':
				$index = 6;
				break;
			default:
				$index = 0;

		}

		return $index;
	}

}