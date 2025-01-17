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
class Seat {

	public function init() {
		// Initialize the plugin
		add_action( 'add_meta_boxes', array( $this, 'add_seat_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_seat_meta_box' ) );
	}

	public function add_seat_meta_box() {
		add_meta_box(
			'seat_meta_box',
			'Seat Details',
			array( $this, 'seat_meta_box_callback' ),
			'seat',
			'normal',
			'high'
		);
	}

	public function seat_meta_box_callback() {
		$custom_fields = array(
			'thl_available' => 'Availablity',
			'thl_expiration_date' => 'Expiry Date',
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
	
	public function save_seat_meta_box( $post_id ) {
		$custom_fields = array(
			'thl_available',
			'thl_expiration_date',
		);
	
		foreach ($custom_fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($post_id, $field, $_POST[$field]);
			}
		}
	}
}