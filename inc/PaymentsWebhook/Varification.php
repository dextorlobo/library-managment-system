<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 0.1.0
 */
declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\PaymentsWebhook;

// Define the plugin's main class
class Varification {

	public function init() {
		// Initialize the plugin
		add_action( 'thl_payment_varification_failed_callback', array( $this, 'thl_payment_varification_failed_method' ), 10, 2 );
	}

	public function thl_payment_varification_failed_method( $palyload, $headers ) {
		$merchantTransactionId = explode( '-', $palyload['data']['merchantTransactionId'] );
		update_post_meta( $merchantTransactionId[0], 'status', $palyload['data']['state'] );
		update_post_meta( $merchantTransactionId[0], 'transaction_id', $palyload['data']['transactionId'] );
		update_post_meta( $merchantTransactionId[0], 'phonepre_res', json_encode( $palyload ) );
		update_post_meta( $merchantTransactionId[0], 'phonepre_headers', json_encode( $headers ) );

		$order_details['order_id']       = $merchantTransactionId[0];
		$order_details['wp_order_id']    = get_post_meta( $merchantTransactionId[0], 'wp_order_id', true );
		$order_details['first_name']     = get_post_meta( $merchantTransactionId[0], 'first_name', true );
		$order_details['last_name']      = get_post_meta( $merchantTransactionId[0], 'last_name', true );
		$order_details['email']          = get_post_meta( $merchantTransactionId[0], 'email', true );
		$order_details['phone']          = get_post_meta( $merchantTransactionId[0], 'phone', true );
		$order_details['seat_no']        = get_post_meta( $merchantTransactionId[0], 'seat_no', true );
		$order_details['product_id']     = get_post_meta( $merchantTransactionId[0], 'product_id', true );
		$order_details['pass_name']      = get_post_meta( $order_details['product_id'], 'thl_pass_name', true );
		$order_details['amount']         = get_post_meta( $merchantTransactionId[0], 'amount', true );
		$order_details['transaction_id'] = get_post_meta( $merchantTransactionId[0], 'transaction_id', true );
		$order_details['status']         = get_post_meta( $merchantTransactionId[0], 'status', true );
		$order_details['order_date']     = get_post_meta( $merchantTransactionId[0], 'order_date', true );

		do_action( 'thl_payment_varification_failed_after_update_status', $order_details );
	}
}
