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
class CheckoutForm {

	public function init() {
		// Add the plugin's shortcode
		add_shortcode( 'lms_checkout_form', array($this, 'render_checkout_form'));
	}

	public function render_checkout_form( $atts ) {
		$atts = shortcode_atts( array(
			'product_id' => '',
			'pass_name'  => '',
		), $atts, 'lms_checkout_form' );

		$product_id   = $atts['product_id'] ?: get_the_ID();
		$product_name = $atts['pass_name'] ?: get_post_meta( $product_id, 'thl_pass_name', true );
		$amount       = get_post_meta( $product_id, 'thl_price', true );

		if ( empty( $product_id ) || empty( $amount ) || empty( $product_name ) ) {
			return '<h2>Invalid product ID, price or name.</h2>';
		}

		$html = '<div class="checkout-form-wrap lms-table-wrap" style="position: relative;">
			<div id="lms-message" class="lms-message hide"></div>
			<form id="checkout_form" class="checkout-form" method="post">
				<table border="1" cellpadding="5" cellspacing="0">
					<tr>
						<th colspan="2">Product Information</th>
					</tr>
					<tr>
						<td>Pass Name</td>
						<td>'. $product_name .'</td>
					</tr>
					<tr>
						<td>Price</td>
						<td>₹ '. $amount .'</td>
					</tr>
					<tr>
						<th colspan="2">Customer Information</th>
					</tr>
					<tr>
						<td>First Name</td>
						<td><input type="text" id="first_name" class="buy-now-fields-product-'. $product_id .'" "name="first_name" required1><span class="inline-error-message"></span></td>
					</tr>
					<tr>
						<td>Last Name</td>
						<td><input type="text" id="last_name" class="buy-now-fields-product-'. $product_id .'" name="last_name" required1><span class="inline-error-message"></span></td>
					</tr>
					<tr>
						<td>Email</td>
						<td><input type="email" id="email" class="buy-now-fields-product-'. $product_id .'" name="email" required1><span class="inline-error-message"></span></td>
					</tr>
					<tr>
						<td>Phone</td>
						<td><input type="tel" id="phone" class="buy-now-fields-product-'. $product_id .'" name="phone" required1><span class="inline-error-message"></span></td>
					</tr>
					<tr>
						<td>Admission Date</td>
						<td><input type="text" id="admission_date" class="buy-now-fields-product-'. $product_id .' lms-start-date" name="admission_date" required1><span class="inline-error-message"></span></td>
					</tr>
					<tr>
						<td>Available Seats</td>
						<td class="available-seats-wrap">Please select admission date.</td>
					</tr>
					<tr>
						<input type="hidden" id="product_id" class="buy-now-fields-product-'. $product_id .'" name="product_id" value="'. $product_id .'">
						<input type="hidden" id="product_name" class="buy-now-fields-product-'. $product_id .'" name="product_name" value="'. $product_name .'">
						<td colspan="2" style="text-align: center;"><input type="submit" class="lms-checkout-submit" data-product-id="'. $product_id .'" value="Pay & Reserve"></td>
					</tr>
				</table>
			</form>
			<div class="loader-overlay hide">
				<div class="loader"></div>
			</div>
		</div>';

		return $html;
	}
}