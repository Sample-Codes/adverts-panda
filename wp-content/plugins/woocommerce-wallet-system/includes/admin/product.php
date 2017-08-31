<?php

/*----------*/ /*---------->>> Exit if Accessed Directly <<<----------*/ /*----------*/
if(!defined('ABSPATH')){
	exit;
}

add_action('admin_init', 'product_creation');
function product_creation(){
	global $product, $wpdb;
	$wallet = get_page_by_title( 'Wallet' , OBJECT, 'product' );
	$post = array(
				'post_author' => get_current_user_ID(),
				'post_content'=> '',
				'post_status' => "pending",
				'post_title'  => "Wallet",
				'post_parent' => '',
				'post_type'   => "product"
			);
	if(empty($wallet->post_title)){
		$post_id = wp_insert_post($post);
		wp_set_object_terms($post_id, 'simple', 'product_type');

		update_post_meta($post_id, '_regular_price', '100');
		update_post_meta($post_id, '_sku', '');
		update_post_meta($post_id, '_price', '200');
		update_post_meta($post_id, '_manage_stock', 'no');
		update_post_meta($post_id, '_stock_status', 'instock');
		update_post_meta($post_id, 'total_sales', '0');
		update_post_meta($post_id, '_downloadable', 'no');
		update_post_meta($post_id, '_virtual', 'yes');
		update_post_meta($post_id, '_purchase_note', '');
		update_post_meta($post_id, '_featured', 'no');
		update_post_meta($post_id, '_weight', '');
		update_post_meta($post_id, '_length', '');
		update_post_meta($post_id, '_width', '');
		update_post_meta($post_id, '_height', '');
		update_post_meta($post_id, '_product_attributes', '');
		update_post_meta($post_id, '_sale_price', '');
		update_post_meta($post_id, '_sale_price_dates_from', '');
		update_post_meta($post_id, '_sale_price_dates_to', '');
		update_post_meta($post_id, '_sold_individually', '');
		update_post_meta($post_id, '_manage_stock', 'no');
		update_post_meta($post_id, '_backorders', 'no');
		update_post_meta($post_id, '_stock', '');
		update_post_meta($post_id, '_upsell_ids', '');
		update_post_meta($post_id, '_crosssell_ids', '');
		update_post_meta($post_id, '_product_version', '2.6.11');
		update_post_meta($post_id, '_product_image_gallery', '');
		update_post_meta($post_id, '_stock', '');

		$wpdb->get_results("INSERT INTO {$wpdb->prefix}term_relationships ( object_id, term_taxonomy_id, term_order ) VALUES ($post_id, '6', '0'), ($post_id, '7', '0'), ($post_id, '14', '0') ");

		// $wpdb->get_results("UPDATE {$wpdb->prefix}term_taxonomy SET count = (CASE term_id WHEN '2' THEN $term_2_count WHEN '6' THEN $term_6_count WHEN '7' THEN $term_7_count WHEN '14' THEN $term_14_count END ) WHERE term_id IN ( '2', '6', '7', '14' ) ");
	}
}
