<?php

/**
 * Wrapper class for advert_single WooCommerce product.
 * 
 * @package Adverts
 * @subpackage WCPayments
 * @since 1.0
 */
class WC_Product_Advert_Single extends WC_Product {

    /**
     * Constructor
     */
    public function __construct( $product ) {
        $this->product_type = 'advert_single';
        parent::__construct( $product );
    }

    /**
     * We want to sell ads one at a time
     * 
     * @since 1.0
     * @return boolean
     */
    public function is_sold_individually() {
        return apply_filters( 'wcpl_' . $this->product_type . '_is_sold_individually', true );
    }

    /**
     * Get the add to url used mainly in loops.
     *
     * @since 1.0
     * @access public
     * @return string
     */
    public function add_to_cart_url() {
        $url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

        return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
    }

    /**
     * Get the add to cart button text
     *
     * @since 1.0
     * @access public
     * @return string
     */
    public function add_to_cart_text() {
        $text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Add to cart', 'wpadverts-wc' ) : __( 'Read More', 'wpadverts-wc' );

        return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
    }

    /**
     * Ads can always be purchased regardless of price.
     * 
     * @since 1.0
     * @access public
     * @return boolean
     */
    public function is_purchasable() {
        return true;
    }

    /**
     * Ads are always virtual
     * 
     * @since 1.0
     * @access public
     * @return boolean
     */
    public function is_virtual() {
        return true;
    }

    /**
     * Return ad duration granted
     * 
     * @since 1.0
     * @access public
     * @return int
     */
    public function get_duration() {
        if ( $this->_advert_listing_duration ) {
            return $this->_advert_listing_duration;
        } else {
            return adverts_config( "visibility" );
        }
    }

    /**
     * Get product id
     * 
     * @since 1.0
     * @access public
     * @return int
     */
    public function get_product_id() {
        return $this->id;
    }
}