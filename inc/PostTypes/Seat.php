<?php
/**
 * Main plugin class.
 *
 * @package img-lms-wp
 * @since 0.1.0
 */
declare( strict_types = 1 );

namespace Imarun\LibraryManagmentSystem\PostTypes;

// Define the plugin's main class
class Seat {

	public function init() {
		// Initialize the plugin
		$this->register_seat_custom_post_type();
		add_filter( 'manage_edit-seat_columns', array( $this, 'add_custom_fields_to_seats_list' ) );
		add_action( 'manage_seat_posts_custom_column', array( $this, 'add_custom_fields_to_seats_list_data' ), 10, 2 );
	}

	public function register_seat_custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Seats', 'Post type general name', 'img-pps-wp' ),
			'singular_name'         => _x( 'Seat', 'Post type singular name', 'img-pps-wp' ),
			'menu_name'             => _x( 'Seats', 'Admin Menu text', 'img-pps-wp' ),
			'name_admin_bar'        => _x( 'Seat', 'Add New on Toolbar', 'img-pps-wp' ),
			'add_new'               => __( 'Add New', 'img-pps-wp' ),
			'add_new_item'          => __( 'Add New Seat', 'img-pps-wp' ),
			'new_item'              => __( 'New Seat', 'img-pps-wp' ),
			'edit_item'             => __( 'Edit Seat', 'img-pps-wp' ),
			'view_item'             => __( 'View Seat', 'img-pps-wp' ),
			'all_items'             => __( 'All Seats', 'img-pps-wp' ),
			'search_items'          => __( 'Search Seats', 'img-pps-wp' ),
			'parent_item_colon'     => __( 'Parent Seats:', 'img-pps-wp' ),
			'not_found'             => __( 'No books found.', 'img-pps-wp' ),
			'not_found_in_trash'    => __( 'No books found in Trash.', 'img-pps-wp' ),
			'featured_image'        => _x( 'Seat Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'archives'              => _x( 'Seat archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'img-pps-wp' ),
			'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'img-pps-wp' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'img-pps-wp' ),
			'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'img-pps-wp' ),
			'items_list_navigation' => _x( 'Seats list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'img-pps-wp' ),
			'items_list'            => _x( 'Seats list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'img-pps-wp' ),
		);
		register_post_type('seat',
			array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => false,
				'has_archive' => true,
				'supports' => array('title'),
				'description' => __('A custom post type for seats'),
				'menu_position' => 5,
				'menu_icon' => 'dashicons-cart',
				'capability_type' => 'post',
				'rewrite' => array('slug' => 'seats')
			)
		);
	}

	public function add_custom_fields_to_seats_list( $columns ) {
		$columns['seat_id'] = 'Seat ID';
		$columns['thl_available'] = 'Availablity';
		$columns['thl_expiration_date'] = 'Expiry Date';
		$date_column = $columns['date'];
		unset($columns['date']);
		$columns['date'] = $date_column;
	
		return $columns;
	}

	public function add_custom_fields_to_seats_list_data( $column, $post_id ) {
		if ($column == 'seat_id') {
			echo $post_id;
		}
		if ($column == 'thl_available') {
			echo get_post_meta( $post_id, 'thl_available', true );
		}
		if ($column == 'thl_expiration_date') {
			echo get_post_meta( $post_id, 'thl_expiration_date', true );
		}
	}
}