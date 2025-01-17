<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem;
use Imarun\LibraryManagmentSystem\Admin\Settings as AdminSettings;
use Imarun\LibraryManagmentSystem\PostTypes\Seat;
use Imarun\LibraryManagmentSystem\PostTypes\Order;
use Imarun\LibraryManagmentSystem\Fields\Order as OrderFields;
use Imarun\LibraryManagmentSystem\Fields\Page as PageFields;
use Imarun\LibraryManagmentSystem\Fields\Seat as SeatFields;
use Imarun\LibraryManagmentSystem\Shortcodes\CheckoutForm;
use Imarun\LibraryManagmentSystem\Shortcodes\OrderDetails;
use Imarun\LibraryManagmentSystem\AjaxActions\CheckoutForm as AjaxCheckoutForm;
use Imarun\LibraryManagmentSystem\PaymentsWebhook\Success;
use Imarun\LibraryManagmentSystem\PaymentsWebhook\Failed;
use Imarun\LibraryManagmentSystem\PaymentsWebhook\Varification;
use Imarun\LibraryManagmentSystem\AwsServises\Ses;

/**
 * The core plugin class.
 *
 * @since   1.0.0
 * @package img-lms-wp
 */
class Plugin {
	public function init() {
		/**
		 * Load hooks after setup theme.
		 */
		add_action( 'init', array( $this, 'lms_fire_init_methods' ) );
		add_action( 'after_setup_theme', array( $this, 'lms_fire_after_setup_theme_methods' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'lms_enqueue_scripts_styles' ) );
	}

	public function lms_fire_init_methods() {
		( new Seat() )->init();
		( new Order() )->init();
		( new OrderFields() )->init();
		( new PageFields() )->init();
		( new SeatFields() )->init();
		( new Success() )->init();
		( new Failed() )->init();
		( new Varification() )->init();
		( new Ses() )->init();
	}

	/**
	* Settings Page.
	*/
	public function lms_fire_after_setup_theme_methods() {
		( new AdminSettings() )->init(); // Settings Page
		( new CheckoutForm() )->init(); // Shortcode
		( new OrderDetails() )->init(); // Shortcode
		( new AjaxCheckoutForm() )->init(); // Ajax for checkout form
	}

	/**
	 * Enqueues styles and scripts for the plugin.
	 *
	 * Registers a custom print stylesheet for the gallery component.
	 *
	 * @since 1.0.0
	 */

	function lms_enqueue_scripts_styles() {
		wp_enqueue_style( 'lms-styles', IMG_LMS_PLUGIN_URL . '/assets/css/styles.css', array(), IMG_LMS_PLUGIN_VERSION );
		wp_enqueue_script( 'lms-scripts', IMG_LMS_PLUGIN_URL . '/assets/js/scripts.js', array( 'jquery' ), IMG_LMS_PLUGIN_VERSION );
		wp_localize_script(
			'lms-scripts',
			'lms_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'restURL'  => rest_url(),
			)
		);

		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );  
	}
}
