<?php

/*----------*/ /*---------->>> Exit if Accessed Directly <<<----------*/ /*----------*/
if(!defined('ABSPATH')){
	exit;
}
if(isset($_REQUEST['add_wallet_money_button'])){
	if (isset( $_POST['wallet_amount'] ) && wp_verify_nonce($_POST['wallet_amount'], 'add-amount') ){
		update_post_meta($_POST['wallet_id'], '_price', $_POST['add_wallet_money']);
		WC()->cart->add_to_cart($_POST['wallet_id']);
		$url = wc_get_page_permalink('cart');
		wp_safe_redirect( $url );
		exit;
	}
}

	?>
	<div class="main-container">
		<div class="add-wallet-wrapper">
			<h4>Remaining Amount:- 
				<?php 
					$user_id = get_current_user_ID();
					$wallet_amount = get_user_meta($user_id, 'wallet-amount', true);
					if(empty($wallet_amount)){
						$wallet_amount = 0;
					}
					echo get_woocommerce_currency_symbol().$wallet_amount; 
				?>	
			</h4>
			<form class="wallet-money-form" action="" method="POST" enctype="multipart/form-data">
				<?php wp_nonce_field('add-amount','wallet_amount'); ?>
				<input type="text" id="add_wallet_money" class="add_wallet_money" name="add_wallet_money" />
				<label for="add_wallet_money">$</label>
				<?php $wallet = get_page_by_title( 'Wallet' , OBJECT, 'product' ); ?>
				<input type="hidden" name="wallet_id" value="<?php echo $wallet->ID; ?>"/>
				<input type="submit" value="Add to Wallet" class="add_wallet_money_button" name="add_wallet_money_button" />
				<?php 
				 ?>
			</form>
		</div>
		<div class="wallet-transactions-wrapper">
			<h4>Wallet Transactions</h4>
			<?php

				$my_orders_columns = apply_filters( 'woocommerce_my_account_my_orders_columns', array(
					'order-number'  => __( 'Order', 'woocommerce' ),
					'order-date'    => __( 'Date', 'woocommerce' ),
					'order-status'  => __( 'Status', 'woocommerce' ),
					'order-total'   => __( 'Total', 'woocommerce' ),
					'order-actions' => '&nbsp;',
				) );
				$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => get_current_user_id(),
					'post_type'   => wc_get_order_types( 'view-orders' ),
					'post_status' => array_keys( wc_get_order_statuses() ),
					'paginate' 	  => true
				) ) );

				if ( $customer_orders ) : ?>

					<table class="shop_table shop_table_responsive my_account_orders">

						<thead>
							<tr>
								<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
									<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
								<?php endforeach; ?>
							</tr>
						</thead>

						<tbody>
							<?php 

							foreach ( $customer_orders as $customer_order ) :
								$count 			= 0;
								$order      	= wc_get_order( $customer_order );

								// echo "<pre>";var_dump($order);
								// die;
								$item_count 	= $order->get_item_count();
								$page       	= get_page_by_title('Wallet', OBJECT, 'product');
								$wallet_id  	= $page->ID;
								$payment_method = get_post_meta($order->get_id(), '_payment_method', true);
								$line_items 	= $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
								$fees = $order->get_fees();
								foreach ($fees as $key => $value) {
									if($value['name'] == 'Wallet' && $value['type'] == 'fee'){
										$count++;
									}
								}
								foreach ( $line_items as $item_id => $item ){
									if($item->get_data()['product_id'] == $wallet_id || $payment_method == "wallet"){
										$count++;
									}
								}
								if($count > 0){
								?>
								<tr class="order">
									<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
										<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
											<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
												<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

											<?php elseif ( 'order-number' === $column_id ) : ?>
												<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
													<?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); ?>
												</a>

											<?php elseif ( 'order-date' === $column_id ) : ?>
												<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->get_date_created() ) ); ?>" title="<?php echo esc_attr( strtotime( $order->get_date_created() ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ); ?></time>

											<?php elseif ( 'order-status' === $column_id ) : ?>
												<?php echo wc_get_order_status_name( $order->get_status() ); ?>

											<?php elseif ( 'order-total' === $column_id ) : ?>
												<?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); ?>

											<?php elseif ( 'order-actions' === $column_id ) : ?>
												<?php
													$actions = array(
														'pay'    => array(
															'url'  => $order->get_checkout_payment_url(),
															'name' => __( 'Pay', 'woocommerce' )
														),
														'view'   => array(
															'url'  => $order->get_view_order_url(),
															'name' => __( 'View', 'woocommerce' )
														),
														'cancel' => array(
															'url'  => $order->get_cancel_order_url( wc_get_page_permalink( 'myaccount' ) ),
															'name' => __( 'Cancel', 'woocommerce' )
														)
													);

													if ( ! $order->needs_payment() ) {
														unset( $actions['pay'] );
													}

													if ( ! in_array( $order->get_status(), apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) ) {
														unset( $actions['cancel'] );
													}

													if ( $actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order ) ) {
														foreach ( $actions as $key => $action ) {
															echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
														}
													}
												?>
											<?php endif; ?>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php } endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
						<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
							<?php _e( 'Go Shop', 'woocommerce' ) ?>
						</a>
						<?php _e( 'No order has been made yet.', 'woocommerce' ); ?>
					</div>
				<?php endif; ?>
		</div>
	</div>
<?php
