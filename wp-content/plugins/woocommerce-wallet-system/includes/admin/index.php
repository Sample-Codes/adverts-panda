<?php

/*----------*/ /*---------->>> Exit if Accessed Directly <<<----------*/ /*----------*/
if(!defined('ABSPATH')){
	exit;
}

add_action('admin_menu', 'customer_menu');
function customer_menu(){
	add_menu_page('Customer Wallet', 'Customer Wallet', 'manage_options', 'customer_wallet', 'customer_wallet', 'dashicons-portfolio', 55);
}
function customer_wallet(){
	require_once('customer-wallet-amount.php');
}