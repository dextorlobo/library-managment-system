<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 0.1.0
 */
declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\Fields;

// Define the plugin's main class
class Order {

	public function init() {
		// Initialize the plugin
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_order_meta_box' ) );
	}

	public function add_order_meta_box() {
		add_meta_box(
			'order_meta_box',
			'Order Details',
			array( $this, 'order_meta_box_callback' ),
			'order',
			'normal',
			'high'
		);
	}

	public function order_meta_box_callback() {
		$custom_fields = array(
			'wp_order_id'    => 'Order ID',
			'first_name'     => 'First Name',
			'last_name'      => 'Last Name',
			'email'          => 'Email',
			'phone'          => 'Phone',
			'seat_no'        => 'Seat #',
			'product_id'     => 'Product ID',
			'amount'         => 'Amount',
			'transaction_id' => 'Transaction ID',
			'status'         => 'Status',
			'order_date'     => 'Order Date',
            'admission_date' => 'Admission Date',
            'expiry_date'    => 'Expiry Date',
			'mail_status'    => 'Mail Status',
            'phonepre_res'   => 'Phonepre Res',
            'phonepre_headers' => "Phonepre Headers",
		);
	
		foreach ($custom_fields as $key => $field) {
			$value = get_post_meta(get_the_ID(), $key, true);
			?>
			<p>
				<label for="<?php echo $key; ?>"><?php echo $field; ?>:</label>
				<input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
			</p>
			<?php
		}
	}
	
	public function save_order_meta_box( $post_id ) {
		$custom_fields = array(
			'wp_order_id',
			'first_name',
			'last_name',
			'email',
			'phone',
			'seat_no',
			'product_id',
			'amount',
			'transaction_id',
			'status',
			'order_date',
            'admission_date',
            'expiry_date',
			'mail_status',
            'phonepre_res',
            'phonepre_headers',
		);
	
		foreach ($custom_fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($post_id, $field, $_POST[$field]);
			}
		}
	}
}