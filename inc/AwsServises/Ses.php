<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 0.1.0
 */
declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\AwsServises;

// Define the plugin's main class
class Ses {

	public function init() {
		// Initialize the plugin
		add_action( 'thl_payment_success_after_update_status', array( $this, 'thl_send_email' ) );
        add_action( 'thl_payment_failed_after_update_status', array( $this, 'thl_send_email' ) );
        add_action( 'thl_payment_varification_failed_after_update_status', array( $this, 'thl_send_email' ) );
	}

	public function thl_send_email( $order_details ) {
        if ( ! function_exists( 'get_aws_ses_api_instance' ) ) {
			return;
		}

		$aws_ses_api = get_aws_ses_api_instance()->send_order_email( $order_details );

		if ( is_wp_error( $aws_ses_api ) ) {
			$mail_status = $aws_ses_api->get_error_message();
		} else {
			$mail_status = $aws_ses_api->get_data();
		}
		update_post_meta( $order_details['order_id'], 'mail_status', $mail_status );
	}
}
