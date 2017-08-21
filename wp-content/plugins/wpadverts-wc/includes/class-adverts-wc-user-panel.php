<?php

/**
 * Adds "My Ads" panel to WC user panel.
 * 
 * This class allows user to manage his ads directly from WooCommerce user panel
 * 
 * @package Adverts
 * @subpackage WCPayments
 * @since 1.0.3
 */
class Adverts_WC_User_Panel {
    
    /**
     * Custom endpoint name.
     *
     * @var string
     */
    protected $_endpoint = null;

    /**
     * Register Plugin actions.
     * 
     * @since 1.0
     */
    public function __construct() {
        
        $endpoint = adverts_config( 'wc_payments.user_panel_endpoint' );

        // Actions used to insert a new endpoint in the WordPress.
        $this->_endpoint = apply_filters( "wadverts_wc_endpoint_name", $endpoint, "user-panel");
        
        add_action( 'init', array( $this, 'add_endpoints' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

        // Change the My Accout page title.
        add_filter( 'the_title', array( $this, 'endpoint_title' ) );

        // Insering your new tab/page into the My Account page.
        add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
        add_action( 'woocommerce_account_' . $this->_endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
    }

    /**
     * Register new endpoint to use inside My Account page.
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
     * 
     * @since 1.0.3
     * @return void
     */
    public function add_endpoints() {
        add_rewrite_endpoint( $this->_endpoint, EP_ROOT | EP_PAGES );
    }

    /**
     * Add new query var.
     *
     * Executed by query_vars filter
     * 
     * @param array $vars
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = $this->_endpoint;

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

        $is_endpoint = isset( $wp_query->query_vars[ $this->_endpoint ] );

        if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
            // New page title.
            $title = adverts_config( 'wc_payments.user_panel_label' );
            
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
        $items[ $this->_endpoint ] = adverts_config( 'wc_payments.user_panel_label' );

        // Insert back the logout item.
        $items['customer-logout'] = $logout;

        return $items;
    }

    /**
     * Endpoint HTML content.
     * 
     * Displays [adverts_manage] shortcode in WC My Account page.
     * 
     * @since 1.0.3
     * @return void
     */
    public function endpoint_content() {

        add_filter( "adverts_manage_baseurl", array($this, "baseurl") );
        echo shortcode_adverts_manage(array());
        remove_action( "adverts_manage_baseurl", array($this, "baseurl") );
    }

    /**
     * Retuns URL to My Account home
     * 
     * This function is executed by "adverts_manage_baseurl" to set a base url
     * for [adverts_manage] shortcode
     * 
     * @since 1.0.3
     * @return string URL to WooCommerce My Account page
     */
    public function baseurl() {
        $link = get_permalink( get_option('woocommerce_myaccount_page_id') );
        
        if(get_option('permalink_structure')) {
            return $link . "/" . $this->_endpoint . "/";
        } else {
            return add_query_arg( $this->_endpoint, "", $link );
        }
        
    }
    
}
