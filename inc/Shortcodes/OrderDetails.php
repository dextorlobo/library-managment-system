<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 0.1.0
 */
declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\Shortcodes;

// Define the plugin's main class
class OrderDetails {

	public function init() {
		// Add the plugin's shortcode
		add_shortcode( 'lms_order_details', array($this, 'render_order_details'));
	}

	public function render_order_details() {
		if ( ! isset( $_GET['token'] ) || empty( $_GET['token'] ) ) {
			return '<p>Invalid Token</p>';
		}

		$merchantTransactionId = explode( '-', base64_decode( $_GET['token'] ) );

		if ( ! is_array( $merchantTransactionId ) ) {
			return '<p>Invalid Token ID</p>';
		}

		if ( ! get_post_status( $merchantTransactionId[0] ) ) {
			return '<p>Order doesn\'t exist</p>';
		}

		$wp_order_id = get_post_meta( $merchantTransactionId[0], 'wp_order_id', true );
		$first_name = get_post_meta( $merchantTransactionId[0], 'first_name', true );
		$last_name = get_post_meta( $merchantTransactionId[0], 'last_name', true );
		$email = get_post_meta( $merchantTransactionId[0], 'email', true );
		$phone = get_post_meta( $merchantTransactionId[0], 'phone', true );
		$seat_no = get_the_title( get_post_meta( $merchantTransactionId[0], 'seat_no', true ) );
		$product_id = get_post_meta( $merchantTransactionId[0], 'product_id', true );
		$pass_name = get_post_meta( $product_id, 'thl_pass_name', true );
		$amount = get_post_meta( $merchantTransactionId[0], 'amount', true );
		$transaction_id = get_post_meta( $merchantTransactionId[0], 'transaction_id', true );
		$status = strtoupper( get_post_meta( $merchantTransactionId[0], 'status', true ) );
		$order_date = get_post_meta( $merchantTransactionId[0], 'order_date', true );
		$order_date_formatted = date('l jS F Y h:i A', strtotime( $order_date ) );

		if ( $status == 'COMPLETED' ) {
			$color = 'success';
		} elseif ( $status == 'PENDING' ) {
			$color = 'warning';
		} elseif ( $status == 'FAILED' ) {
			$color = 'error';
		} else {
			$color = 'info';
		}

		$html = '<div class="order-form-wrap lms-table-wrap">
			<table border="1" cellpadding="5" cellspacing="0">
				<tr>
					<th colspan="2">Customer Information</th>
				</tr>
				<tr>
					<td>Name</td>
					<td>'. $first_name .' '. $last_name .'</td>
				</tr>
				<tr>
					<td>Email</td>
					<td><a href="mailto:'. $email .'">'. $email .'</a></td>
				</tr>
				<tr>
					<td>Phone</td>
					<td>'. $phone.'</td>
				</tr>
				<tr>
					<th colspan="2">Order Information</th>
				</tr>
				<tr>
					<td>Order ID</td>
					<td>'. $wp_order_id .'</td>
				</tr>
				<tr>
					<td>Pass Name</td>
					<td>'. $pass_name .'</td>
				</tr>
				<tr>
					<td>Price</td>
					<td>₹ '. $amount .'</td>
				</tr>
				<tr>
					<td>Seat #</td>
					<td>'. $seat_no .'</td>
				</tr>
				<tr>
					<td>Transaction ID</td>
					<td>'. $transaction_id .'</td>
				</tr>
				<tr>
					<td>Payment Status</td>
					<td><span class="lms-message '. $color .'">'. strtoupper( $status ) .'</span></td>
				</tr>
				<tr>
					<td>Order Date</td>
					<td><span class="order-date" abbr title="'. $order_date_formatted .'">'. $order_date .'</span</td>
				</tr>
			</table>
		</div>';

		return $html;
	}
}