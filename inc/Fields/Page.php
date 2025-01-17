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
class Page {

	public function init() {
		// Initialize the plugin
		add_action( 'add_meta_boxes', array( $this, 'add_page_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_page_meta_box' ) );
	}

	public function add_page_meta_box() {
		add_meta_box(
			'page_meta_box',
			'Page Details',
			array( $this, 'page_meta_box_callback' ),
			'page',
			'normal',
			'high'
		);
	}

	public function page_meta_box_callback() {
		$custom_fields = array(
			'thl_price'     => 'Price',
			'thl_pass_name' => 'Pass Name',
			'thl_valid_day' => 'Validity Days',
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
	
	public function save_page_meta_box( $post_id ) {
		$custom_fields = array(
			'thl_price',
			'thl_pass_name',
			'thl_valid_day',
		);
	
		foreach ($custom_fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($post_id, $field, $_POST[$field]);
			}
		}
	}
}