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
class Order {

	public function init() {
		// Initialize the plugin
		$this->register_order_custom_post_type();
		add_filter( 'manage_edit-order_columns', array( $this, 'add_custom_fields_to_orders_list' ) );
		add_action( 'manage_order_posts_custom_column', array( $this, 'add_custom_fields_to_orders_list_data' ), 10, 2 );
	}

	public function register_order_custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Orders', 'Post type general name', 'img-pps-wp' ),
			'singular_name'         => _x( 'Order', 'Post type singular name', 'img-pps-wp' ),
			'menu_name'             => _x( 'Orders', 'Admin Menu text', 'img-pps-wp' ),
			'name_admin_bar'        => _x( 'Order', 'Add New on Toolbar', 'img-pps-wp' ),
			'add_new'               => __( 'Add New', 'img-pps-wp' ),
			'add_new_item'          => __( 'Add New Order', 'img-pps-wp' ),
			'new_item'              => __( 'New Order', 'img-pps-wp' ),
			'edit_item'             => __( 'Edit Order', 'img-pps-wp' ),
			'view_item'             => __( 'View Order', 'img-pps-wp' ),
			'all_items'             => __( 'All Orders', 'img-pps-wp' ),
			'search_items'          => __( 'Search Orders', 'img-pps-wp' ),
			'parent_item_colon'     => __( 'Parent Orders:', 'img-pps-wp' ),
			'not_found'             => __( 'No books found.', 'img-pps-wp' ),
			'not_found_in_trash'    => __( 'No books found in Trash.', 'img-pps-wp' ),
			'featured_image'        => _x( 'Order Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'img-pps-wp' ),
			'archives'              => _x( 'Order archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'img-pps-wp' ),
			'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'img-pps-wp' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'img-pps-wp' ),
			'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'img-pps-wp' ),
			'items_list_navigation' => _x( 'Orders list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'img-pps-wp' ),
			'items_list'            => _x( 'Orders list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'img-pps-wp' ),
		);
		register_post_type('order',
			array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => false,
				'has_archive' => true,
				'supports' => array('title'),
				'description' => __('A custom post type for orders'),
				'menu_position' => 5,
				'menu_icon' => 'dashicons-cart',
				'capability_type' => 'post',
				'rewrite' => array('slug' => 'orders')
			)
		);
	}

	public function add_custom_fields_to_orders_list( $columns ) {
		$columns['wp_order_id'] = 'Order ID';
		$columns['phone'] = 'Phone';
		$columns['pass_name'] = 'Pass Name';
		$columns['seat_no'] = 'Seat #';
		$columns['payment_status'] = 'Payment Status';
		$date_column = $columns['date'];
		unset($columns['date']);
		$columns['date'] = $date_column;
	
		return $columns;
	}

	public function add_custom_fields_to_orders_list_data( $column, $post_id ) {
		$product_id = get_post_meta( $post_id, 'product_id', true );
		if ($column == 'phone') {
			echo get_post_meta( $post_id, 'phone', true );
		}
		if ($column == 'wp_order_id') {
			echo get_post_meta( $post_id, 'wp_order_id', true );
		}
		if ($column == 'pass_name') {
			echo get_post_meta( $product_id, 'thl_pass_name', true );
		}
		if ($column == 'seat_no') {
			echo get_post_meta( $post_id, 'seat_no', true );
		}
		if ($column == 'payment_status') {
			echo get_post_meta( $post_id, 'status', true );
		}
	}
}