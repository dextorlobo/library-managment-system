<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\AjaxActions;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use WP_Query;

/**
 * The core plugin class.
 *
 * @since   1.0.0
 * @package img-lms-wp
 */
class CheckoutForm {
	public function init() {
		add_action( 'wp_ajax_nopriv_lms_checkout_validation', array( $this, 'lms_checkout_validation' ) );
		add_action( 'wp_ajax_nopriv_lms_create_pending_order', array( $this, 'lms_create_pending_order' ) );
		add_action( 'wp_ajax_nopriv_lms_generating_payment_link', array( $this, 'lms_generating_payment_link' ) );
		add_action( 'wp_ajax_nopriv_lms_get_avilable_seats', array( $this, 'lms_get_avilable_seats' ) );
	}

	public function lms_checkout_validation() {
		$jsonobj = $_POST['data'];

		$invalid = [
			'header_msg'   => 'Something went wrong. Please check below errors.',
			'fields_error' => [],
			'redirect'     => false
		];

		foreach ( $jsonobj as $key => $value ) {
			if ( $value['id'] == 'first_name' ) {
				$fname = $value['value'];
				if ( ! self::validateDataType( $value['value'] ) ) {
					$value['error'] = true;
					$value['msg'] = 'Only alphabets allowed.';
					$invalid['fields_error'][ $key ] = $value;
				}
			}

			if ( $value['id'] == 'last_name' ) {
				$lname = $value['value'];
				if ( ! self::validateDataType( $value['value'] ) ) {
					$value['error'] = true;
					$value['msg'] = 'Only alphabets allowed.';
					$invalid['fields_error'][ $key ] = $value;
				}
			}

			if ( $value['id'] == 'phone' ) {
				$phone = $value['value'];
				if ( ! self::validatePhone( $value['value'] ) ) {
					$value['error'] = true;
					$value['msg'] = 'Please enter valid phone number.';
					$invalid['fields_error'][ $key ] = $value;
				}
			}

			if ( $value['id'] == 'email' ) {
				$email = $value['value'];
				if ( ! self::validateEmail( $value['value'] ) ) {
					$value['error'] = true;
					$value['msg'] = 'Please enter valid email.';
					$invalid['fields_error'][ $key ] = $value;
				}
			}

			if ( $value['id'] == 'seat_no' ) {
				$seat_no = $value['value'];
				if ( ! self::isNumeric( $value['value'] ) ) {
					$value['error'] = true;
					$value['msg'] = 'Please valid seat number between 1-304.';
					$invalid['fields_error'][ $key ] = $value;
				}
			}
		}

		if ( ! empty( $invalid['fields_error'] ) ) {
			$invalid['fields_error'] = array_values( $invalid['fields_error'] );

			wp_send_json_error( $invalid, 200 );
		}

		wp_send_json_success( $jsonobj, 200 );
	}

	public static function validateDataType( $data ) {
		return preg_match( '/^[a-zA-Z]{2,20}$/', $data );
	}

	public static function isNumeric( $str ) {
		if ( filter_var( $str, FILTER_VALIDATE_INT ) === false ) {
			return false;
		}

		return true;
	}

	public static function validatePhone( $phone ) {
		try {
			$phone = PhoneNumber::parse( '+91' . $phone );
		} catch ( PhoneNumberParseException $e ) {
			return false;
		}

		if( ! $phone->isPossibleNumber() ) {
			return false;
		}

		if( ! $phone->isValidNumber() ) {
			return false;
		}

		return true;
	}

	public static function validateEmail( $email ) {
		$validator = new EmailValidator();

		return $validator->isValid( $email, new RFCValidation() );
	}

	public static function lms_create_pending_order() {
		$formData = $_POST['data'];
		$invalid  = [
			'header_msg'   => '',
			'fields_error' => [],
			'redirect'     => false
		];

		$timezone = new \DateTimeZone( 'Asia/Kolkata' );

		foreach ( $formData as $key => $value ) {
			if ( $value['id'] == 'first_name' ) {
				$first_name = $value['value'];
			}

			if ( $value['id'] == 'last_name' ) {
				$last_name = $value['value'];
			}

			if ( $value['id'] == 'email' ) {
				$email = $value['value'];
			}

			if ( $value['id'] == 'phone' ) {
				$phone = $value['value'];
			}

			if ( $value['id'] == 'seat_no' ) {
				$seat_no = $value['value'];
			}

			if ( $value['id'] == 'product_id' ) {
				$product_id = $value['value'];
			}

			if ( $value['id'] == 'product_name' ) {
				$product_name = $value['value'];
			}

			if ( $value['id'] == 'admission_date' ) {
				$admission_date = wp_date( "Y-m-d", strtotime( $value['value'] ), $timezone );
			}
		}

		$order_date = wp_date( "Y-m-d H:i:s", null, $timezone );
		$status     = 'INITIATED';

		// Create a new post in the "Order" custom post type
		$post_id = wp_insert_post( array(
			'post_title'  => 'Order from ' . $first_name . ' ' . $last_name,
			'post_status' => 'publish',
			'post_type'   => 'order'
		) );

		if ( is_wp_error( $post_id ) ) {
			$invalid['header_msg'] = $post_id->get_error_message();
		}

		if ( ! empty( $invalid['header_msg'] ) ) {
			wp_send_json_error( $invalid, 200 );
		}

		$wp_order_id      = uniqid( $post_id . '-' );
		$amount           = get_post_meta( $product_id, 'thl_price', true );
		$valid_day        = get_post_meta( $product_id, 'thl_valid_day', true );

		if ( $valid_day ) {
			$expired_date = wp_date( "Y-m-d", strtotime( $admission_date . '+' . $valid_day . ' days' ), $timezone ); 
		}

		$first_name_meta     = update_post_meta( $post_id, 'first_name', $first_name );
		$last_name_meta      = update_post_meta( $post_id, 'last_name', $last_name );
		$email_meta          = update_post_meta( $post_id, 'email', $email );
		$phone_meta          = update_post_meta( $post_id, 'phone', $phone );
		$seat_no_meta        = update_post_meta( $post_id, 'seat_no', $seat_no );
		$product_id_meta     = update_post_meta( $post_id, 'product_id', $product_id );
		$amount_meta         = update_post_meta( $post_id, 'amount', $amount );
		$wp_order_id_meta    = update_post_meta( $post_id, 'wp_order_id', $wp_order_id );
		$status_meta         = update_post_meta( $post_id, 'status', $status );
		$order_date_meta     = update_post_meta( $post_id, 'order_date', $order_date );
		$admission_date_meta = update_post_meta( $post_id, 'admission_date', $admission_date );
		$expired_date_meta   = update_post_meta( $post_id, 'expiry_date', $expired_date );

		$payment_data = [
			'phone'       => $phone,
			'email'       => $email,
			'amount'      => $amount,
			'wp_order_id' => $wp_order_id
		];

		wp_send_json_success( $payment_data, 200 );
	}

	public static function lms_generating_payment_link() {
		$invalid  = [
			'header_msg'   => '',
			'fields_error' => [],
			'redirect'     => false
		];
		$data = $_POST['data'];
		$phone       = $data['phone'];
		$email       = $data['email'];
		$amount      = $data['amount'];
		$wp_order_id = $data['wp_order_id'];

		$PaymentPage = get_phonepe_api_instance()->createPaymentPage( [ 'phone' => $phone, 'email' => $email, 'amount' => $amount, 'wp_order_id' => $wp_order_id ] );

		if ( is_wp_error( $PaymentPage ) ) {
			$invalid['header_msg'] = $PaymentPage->get_error_code();
			wp_send_json_error( $invalid, 200 );
		}

		wp_send_json_success( [ 'payment_link' => $PaymentPage->get_data() ], 200 );
	}

	public function lms_get_avilable_seats() {
		$data = $_POST['data'];

		$invalid = [
			'header_msg'   => 'Something went wrong. Please check below errors.',
			'fields_error' => [],
			'redirect'     => false
		];

		if ( $data['selectedMonth'] < 10 ) {
			$data['selectedMonth'] = '0'.$data['selectedMonth'];
		}

		$args = array(
			'post_type' => 'seat', // assuming 'seat' is your custom post type
			'posts_per_page' => -1, // fetch all posts
			'order' => 'ASC',
			'orderby' => 'ID',
		);

		$meta_query_enabled = false;

		if ( $meta_query_enabled ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => 'thl_available',
					'value' => 'free',
					'compare' => '='
				),
				array(
					'relation' => 'OR',
					array(
						'key' => 'thl_expiration_date',
						'value' => '', // empty value
						'compare' => '='
					),
					array(
						'key' => 'thl_expiration_date',
						'value' => $data['selectedYear'].'-'.$data['selectedMonth'].'-'.$data['selectedDay'], // replace with the desired date (YYYY-MM-DD format)
						'compare' => '<'
					)
				)
			);
		}

		$seat_query  = new WP_Query( $args );

		$found_seats = $seat_query->found_posts;

		if ( empty( $found_seats ) || $found_seats == 0 ) {
			$invalid['header_msg'] = 'No seats available';
			
			wp_send_json_error( $invalid, 200 );
		}

		while ( $seat_query->have_posts() ) {
			$seat_query->the_post();
			$available_seats[] = [
				'id'    => get_the_ID(),
				'title' => get_the_title()
			];
		}
		wp_reset_postdata();

		wp_send_json_success( $available_seats, 200 );
	}
}
