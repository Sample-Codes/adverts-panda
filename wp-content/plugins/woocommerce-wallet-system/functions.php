<?php
/**
 * Plugin Name: Wordpress Woocommerce Wallet System
 * Plugin URI: https://webkul.com
 * Description: Wordpress Woocommerce Wallet System Plugin helps in integrating wallet payment method.
 * Version: 1.0.0
 * Author: Webkul
 * Author URI: https://webkul.com
 **/

/*----------*/ /*---------->>> Exit if Accessed Directly <<<----------*/ /*----------*/
if(!defined('ABSPATH')){
	exit;
}

if(!class_exists('Wallet_System')){
	class Wallet_System{			
		public static $endpoint = 'wallet';	
		public function __construct() {
			ob_start();
			$this->include_backend();
			add_action('plugins_loaded', array($this, 'init'), 0);
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'add_endpoints' ) );				
			// Insering your new tab/page into the My Account page.				
			add_action('woocommerce_order_status_completed', array($this, 'after_order_completed'), 10, 1);
			add_action( 'woocommerce_order_status_processing', array($this, 'order_processing'), 10, 1 );
			add_action('wp_enqueue_scripts', array($this, 'assets_enqueue'));	
			add_action('wp_footer', array($this, 'footer'));		
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
			add_action( 'woocommerce_checkout_order_review', array($this, 'woocommerce_wallet_payment'), 20 );
			add_action( 'wp_ajax_nopriv_ajax_wallet_check', array($this, 'ajax_wallet_check'));	
			add_action( 'wp_ajax_ajax_wallet_check', array($this, 'ajax_wallet_check'));
			add_action('woocommerce_cart_calculate_fees', array($this, 'woo_add_cart_fee'));
			add_action( 'template_redirect', array($this, 'wallet_template_redirect') );


			// Change the My Accout page title.				
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );						
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );	
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );						
			add_filter( 'woocommerce_before_cart', array($this, 'check_product_in_cart') ,10, 1);
			add_filter( 'woocommerce_available_payment_gateways', array($this, 'payment_gateway_disable'), 10, 1 );
		}

		public function init(){
			require_once('includes/gateways/wallet/class-wc-gateway-wallet.php');
		}
		function payment_gateway_disable( $available_gateways ) {
			global $woocommerce;
			$count = 0;
			$get_cart = WC()->cart->cart_contents;
			$arrayKeys = array_keys($available_gateways);
			$page = get_page_by_title('Wallet', OBJECT, 'product');
			$wallet_id = $page->ID;
			if(!empty($get_cart)){
				foreach($get_cart as $key => $value ){
					$product_id = $value['product_id'];
					if($product_id == $wallet_id){
						$count = 1;
					}
				}
			}
			if(is_user_logged_in()){
				$user_id = get_current_user_ID();
				$wallet_amount = get_user_meta($user_id, 'wallet-amount', true);
				if(isset($_SESSION['val']) && $wallet_amount >= ($_SESSION['val'] + $woocommerce->cart->total) ){
					foreach ($arrayKeys as $key => $value) {
			    		if($value == "wallet"){

			    		}else{
			    			unset($available_gateways[$value]);
			    		}
			    	}
				}
			    else if($wallet_amount >= $woocommerce->cart->total && !isset($_SESSION['val']) && $count==0){
			    }
			    else {
			    	unset( $available_gateways['wallet'] );
			    }
			}
			else{
				unset( $available_gateways['wallet'] );
			}
			return $available_gateways;
		}
		/**			 
		* Enqueue Assets.			 
		*			 
		* @param string $title			 
		* @return string			 
		*/
		public function wallet_template_redirect(){
			global $woocommerce;
			$page = get_page_by_title('Wallet', OBJECT, 'product');
			$wallet_id = $page->ID;
			if(is_shop() || (get_post_type() == 'product'  && is_single())){
				$get_cart = WC()->cart->cart_contents;
				if(!empty($get_cart)){
					foreach($get_cart as $key => $value ){
						$product_id = $value['product_id'];
						if($product_id == $wallet_id){
							wc_add_notice( sprintf( 'Cannot add new product now. Either empty cart or process it first.</p>'));
						}
					}
				}
			}
		}
	    public function check_product_in_cart($cart_item_data) {
		    global $woocommerce;
			$page = get_page_by_title('Wallet', OBJECT, 'product');
			$wallet_id = $page->ID;
			$cart = WC()->cart;
			$get_cart = WC()->cart->cart_contents;
			if(!empty($get_cart)){
				foreach($get_cart as $key => $value ){
					$product_id = $value['product_id'];
					if($product_id == $wallet_id){
						$woocommerce->cart->empty_cart();
						WC()->cart->add_to_cart($wallet_id);
					}
				}
				return WC()->cart->cart_contents;
			}
		}
		public function assets_enqueue(){
			wp_enqueue_script('jq', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js');
			wp_enqueue_style('css', plugin_dir_url(__FILE__).'/assets/css/style.css');
		}
		public function footer(){
			wp_enqueue_script('pluginjs', plugin_dir_url(__FILE__).'/assets/js/plugin.js', array());
			wp_localize_script( 'pluginjs', 'wallet_ajax', array( 'ajaxurl' =>   admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce('ajaxnonce') ) );
		}


		function woo_add_cart_fee() {
			if(is_page('checkout')){
			    session_start();
			    $user_id = get_current_user_ID();
			    $wallet_amount = get_user_meta($user_id, 'wallet-amount', true);
			    if(!empty($wallet_amount) && isset($_SESSION['val'])){
			    	$amount = $_SESSION['val'];
			    	$extracost =  (-1)*$amount;
			    	WC()->cart->add_fee('Wallet', $extracost, true, '');
			    }
			}
		}
 		public function ajax_wallet_check(){
 			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajaxnonce' ) )
        		die ( 'Busted!');
 			global $woocommerce;
 			$check = intval($_POST['check']);
 			if($check == 1){
 				$user_id = get_current_user_ID();
				$total = $woocommerce->cart->total;
	 			$wallet_amount = get_user_meta($user_id, 'wallet-amount', true);
	 			if(!empty($wallet_amount)){
	 				if($wallet_amount >= $total){
	 					
	 				}
	 				else{
	 					session_start();
			        	$_SESSION['val'] = $wallet_amount;

	 				}
	 			}	
 			}
 			else{
 				session_start();
 				unset($_SESSION['val']);
 			}

 			die;
 		}
		/**			 
		* Register new endpoint to use inside My Account page.			 
		*			 
		*/			
		public function add_endpoints() {				
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );			
		}					
		/**			 
		* Add new query var.			 
		*			 
		* @param array $vars			 
		* @return array			 
		*/			
		public function add_query_vars( $vars ) {				
			$vars[] = self::$endpoint;						
			return $vars;			
		}					
		/**			 
		* Set endpoint title.			 
		*			 
		* @param string $title			 
		* @return string			 
		*/			
		public function endpoint_title( $title ) {				
			global $wp_query;						
			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );						
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {					
				// New page title.					
				$title = __( 'My Wallet', 'woocommerce' );							
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );				
			}						
			return $title;			
		}					
		/**			 
		* Insert the new endpoint into the My Account menu.			 
		*			 
		* @param array $items			 
		* @return array			 
		*/			
		public function new_menu_items( $items ) {				
			// Remove the logout menu item.				
			$logout = $items['customer-logout'];				
			unset( $items['customer-logout'] );						
			// Insert your custom endpoint.				
			$items[ self::$endpoint ] = __( 'My Wallet', 'woocommerce' );						
			// Insert back the logout item.				
			$items['customer-logout'] = $logout;						
			return $items;			
		}					
		/**			 
		* Endpoint HTML content.			 
		*/			
		public function endpoint_content() {
			include_once('includes/front/wallet.php');			
		}					
		/**			 
		* Plugin install action.			 
		* Flush rewrite rules to make our custom endpoint available.			 
		*/			
		public static function install() {				
			flush_rewrite_rules();			
		}
		public function woocommerce_wallet_payment(){
			global $woocommerce;
			$total = $woocommerce->cart->total;
			if(is_user_logged_in()){
				$user_id = get_current_user_ID();
				$wallet_money = get_user_meta($user_id, 'wallet-amount', true);
				$count = 0;
				$get_cart = $woocommerce->cart->cart_contents;
				$page = get_page_by_title('Wallet', OBJECT, 'product');
				$wallet_id = $page->ID;
				if(!empty($get_cart)){
					foreach($get_cart as $key => $value ){
						$product_id = $value['product_id'];
						if($product_id == $wallet_id){
							$count = 1;
						}
					}
				}
				if(!empty($_SESSION['val'])){
					$cart_total = $total+$_SESSION['val'];
				}else{
					$cart_total = $total;
				}
				if(!empty($wallet_money) && $wallet_money > 0 && $wallet_money < $cart_total && $count==0){
					if(is_page('checkout')){
					?>
						<div class="wallet-checkout">
							<input type="checkbox" name="wallet-checkout-payment" id="wallet-checkout-payment"  <?php if(!empty($_SESSION['val'])) echo "checked='checked'" ?>/>
							<label for="wallet-checkout-payment">Pay via Wallet</label>
						</div>
					<?php
					}
				}
			}
		}
		private function include_backend(){
			if(is_admin()){
				require_once('includes/admin/index.php');
				require_once('includes/admin/product.php');
			}
		}
		public function order_processing($order_id){
			session_start();
			if(isset($_SESSION['val'])){
 				unset($_SESSION['val']);
			}
		}
		public function after_order_completed($order_id){
			$page = get_page_by_title('Wallet', OBJECT, 'product');
			$wallet_id = $page->ID;

			$order = new WC_Order($order_id);
			$user_id = (int)$order->user_id;
			$payment_method = get_post_meta($order_id, '_payment_method', true);
			$order_total = get_post_meta($order_id, '_order_total', true);
			$wallet_amount = get_user_meta($user_id, 'wallet-amount', true);
			$line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			$fees = $order->get_fees();
			$to = get_user_meta($user_id, 'billing_email', true);
			$subject = 'Automated mail for wallet based transaction.';

			foreach ($fees as $key => $value) {
				if($value->get_data()['name'] == 'Wallet'){
					$fees = $value->get_data()['total'];
					$wallet_amount = $wallet_amount+$fees;

					$message .= 'Order No. : '.$order_id.'  ';
					$message .= 'Wallet Transaction : '.$fees.'  ';
					$message .= 'Remaining Amount : '.$wallet_amount;
					
					wp_mail($to, $subject, $message);
					update_user_meta($user_id, 'wallet-amount', $wallet_amount);
				}
			}
			foreach ( $line_items as $item_id => $item ){
				if($item->get_data()['product_id'] == $wallet_id){
					if(!empty($wallet_amount)){
						$wallet_amount = $wallet_amount+(int)$order_total;
					}
					else{
						$wallet_amount = (int)$order_total;
					}

					$message .= 'Order No. : '.$order_id.' ';
					$message .= 'Wallet Transaction : '.$order_total.'  ';
					$message .= 'Remaining Amount : '.$wallet_amount;

					wp_mail($to, $subject, $message);
					update_user_meta($user_id, 'wallet-amount', $wallet_amount);
				}
			}
			if($payment_method == 'wallet'){
				$wallet_amount=$wallet_amount-(int)$order_total;

				$message .= 'Order No. : '.$order_id.'  ';
				$message .= 'Wallet Transaction : '.$order_total.'  ';
				$message .= 'Remaining Amount : '.$wallet_amount;

				wp_mail($to, $subject, $message);
				update_user_meta($user_id, 'wallet-amount', $wallet_amount);
			}
		}		
	}
}				
new Wallet_System();				
// Flush rewrite rules on plugin activation.		
register_activation_hook( __FILE__, array( 'Wallet_System', 'install' ) );