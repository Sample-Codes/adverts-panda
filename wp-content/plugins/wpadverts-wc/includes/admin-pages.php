<?php
/**
 * WooCommerce Integration Admin Pages
 * 
 * This file contains function to handle WooCommerce config logic in wp-admin 
 * and config form.
 *
 * @package     Adverts
 * @subpackage  WCPayments
 * @copyright   Copyright (c) 2015, Grzegorz Winiarski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/**
 * Renders WooCommerce Integration config form.
 * 
 * The page is rendered in wp-admin / Classifieds / Options / WooCommerce Payments panel
 * 
 * @global $wp_rewrite WP_Rewrite
 * @since 0.1
 * @return void
 */
function adext_wc_payments_page_options() {
    global $wp_rewrite;
    
    wp_enqueue_style( 'adverts-admin' );
    $flash = Adverts_Flash::instance();
    $error = array();
    
    $scheme = Adverts::instance()->get("form_wc_payments_config");
    $form = new Adverts_Form( $scheme );
    $form->bind( get_option ( "adext_wc_payments_config", array() ) );
    
    $button_text = __("Update Options", "adverts");
    
    if(isset($_POST) && !empty($_POST)) {
        $form->bind( $_POST );
        $valid = $form->validate();

        if($valid) {

            update_option("adext_wc_payments_config", $form->get_values());
            $wp_rewrite->flush_rules();
            $flash->add_info( __("Settings updated.", "adverts") );
        } else {
            $flash->add_error( __("There are errors in your form.", "adverts") );
        }
    }
    
    include dirname( ADVERTS_PATH ) . '/wpadverts-wc/admin/options.php';
}

// PayPal Standard config form
Adverts::instance()->set("form_wc_payments_config", array(
    "name" => "",
    "action" => "",
    "field" => array(
        array(
            "name" => "show_manage_adverts",
            "type" => "adverts_field_checkbox",
            "label" => __("Manage", "wpadverts-wc"),
            "order" => 10,
            "options" => array(
                array(
                    "value" => "1", 
                    "text" => __( "Show 'My Ads' panel  in WooCommerce user panel.", "wpadverts-wc" )
                ),
            )
        ),
        array(
            "name" => "user_panel_label",
            "type" => "adverts_field_text",
            "label" => __("'My Ads' Panel Title", "wpadverts-wc"),
            "order" => 20,
            "placeholder" => __("My Ads", "wpadverts-wc"),
        ),
        array(
            "name" => "user_panel_endpoint",
            "type" => "adverts_field_text",
            "label" => __("'My Ads' Panel Slug", "wpadverts-wc"),
            "order" => 30,
            "placeholder" => "my-ads",
        )
    )
));

/**
 * Creates advert_single term if not exists.
 * 
 * This function creates advert_single term for product_type taxonomy
 * used by WooCommerce products
 * 
 * @since 1.0
 * @access public
 * @return void
 */
function adext_wc_payments_install() {
    if ( ! get_term_by( 'slug', sanitize_title( 'advert_single' ), 'product_type' ) ) {
        wp_insert_term( 'advert_single', 'product_type' );
    }
    if ( ! get_term_by( 'slug', sanitize_title( 'advert_renew' ), 'product_type' ) ) {
        wp_insert_term( 'advert_renew', 'product_type' );
    }
}

/**
 * Adds new product type to WooCommerce product types list.
 * 
 * This function hooks into product_type_selector filter in order to
 * add new product type
 * 
 * @param array $arr List of WooCommerce product types
 * @return array Updated list of WooCommerce product types
 */
function adext_wc_payments_product_type( $arr ) {
    $arr["advert_single"] = __( "Single Listing (WPAdverts)", "wpadverts-wc" );
    $arr["advert_renew"] = __( "Renew Listing (WPAdverts)", "wpadverts-wc" );
    return $arr;
}

/**
 * Saves Advert product meta data
 * 
 * This functions hooks into woocommerce_process_product_meta filter to save
 * meta data when product is being saved.
 * 
 * @global wpdb $wpdb
 * 
 * @access public
 * @param int $post_id ID of a product being saved
 * @return void
 */
function adext_wc_payments_save_product_data( $post_id ) {
    global $wpdb;

    // Save meta
    $meta_to_save = array(
        '_advert_listing_duration' => '',
        '_advert_listing_featured' => 'yesno'
    );

    foreach ( $meta_to_save as $meta_key => $sanitize ) {
        $value = ! empty( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : '';
        switch ( $sanitize ) {
            case 'int' :
                $value = absint( $value );
                break;
            case 'float' :
                $value = floatval( $value );
                break;
            case 'yesno' :
                $value = $value == 'yes' ? 'yes' : 'no';
                break;
            default :
                $value = sanitize_text_field( $value );
        }
        update_post_meta( $post_id, $meta_key, $value );
    }
}

/**
 * Display 'Pending' state on Classifieds list
 * 
 * This functions shows Expired state in the wp-admin / Classifieds panel
 * 
 * @global WP_Post $post
 * @param array $states
 * @return array
 */
function adext_display_wc_pending_state( $states ) {
    global $post;
    $arg = get_query_var( 'post_status' );
     
    if($arg == 'wc_pending'){
        return $states;
    }
     
    if($post->post_status != 'wc_pending'){
        return $states;
    }
     
    $id = adext_wc_payments_get_advert_order_id( $post->ID );
    $order_link = null;
    
    if($id !== null) {
        $span = new Adverts_Html("span", array(
            "class" => "dashicons dashicons-cart",
            "style" => "font-size: 18px"
        ));
        $span->forceLongClosing(true);
        
        $order_link = new Adverts_Html("a", array(
            "href" => admin_url("post.php?post=$id&action=edit"),
            "title" => __("View order", "wpadverts-wc")
        ), $span->render());
    } else {
        $span = new Adverts_Html("span", array(
            "class" => "dashicons dashicons-info",
            "title" => __( 'Cart Abandoned', 'wpadverts-wc' ),
            "style" => "font-size: 18px"
        ));
        $span->forceLongClosing(true);
        
        $order_link = $span->render();
    }
    
    return array( __( 'Pending Payment', 'wpadverts-wc' ) . $order_link );

    return $states;
}


/**
 * Disaplys additional form fields for Adverts products
 * 
 * This function creates additional fields for Adverts products, it is using
 * woocommerce_product_options_general_product_data filter to push the fields
 * 
 * @global WP_Post $post Currently edited product
 * 
 * @access public
 * @since 1.0
 * @return void
 */
function adext_wc_payments_product_data() {
    global $post;
    $post_id = $post->ID;
    ?>
    <div class="options_group show_if_advert_single show_if_advert_renew show_if_advert_subscription">

	<?php woocommerce_wp_text_input( array( 
            'id' => '_advert_listing_duration', 
            'label' => __( 'Ad listing duration', 'wpadverts-wc' ), 
            'description' => __( 'The number of days that the ad will be active.', 'wpadverts-wc' ), 
            'value' => get_post_meta( $post_id, '_advert_listing_duration', true ), 
            'placeholder' => adverts_config( "visibility" ), 
            'desc_tip' => true, 
            'type' => 'number', 
            'custom_attributes' => array(
		'min'   => '',
		'step' 	=> '1'
	) ) ); ?>
        
        <?php if(array_key_exists( "featured", adverts_config( 'module' ) ) ): ?>
	<?php woocommerce_wp_checkbox( array( 
            'id' => '_advert_listing_featured', 
            'label' => __( 'Feature ad listings?', 'wpadverts-wc' ), 
            'description' => __( 'Feature this ad - it will be styled differently and sticky.', 'wpadverts-wc' ), 
            'value' => get_post_meta( $post_id, '_advert_listing_featured', true 
        ) ) ); ?>
        <?php endif; ?>
        
	<script type="text/javascript">
            jQuery(function(){
                jQuery('.pricing').addClass( 'show_if_advert_single' );
                jQuery('.pricing').addClass( 'show_if_advert_renew' );
                jQuery('._tax_status_field').closest('div').addClass( 'show_if_advert_single' );
                jQuery('._tax_status_field').closest('div').addClass( 'show_if_advert_renew' );
                jQuery('.show_if_subscription, .grouping').addClass( 'show_if_advert_subscription' );
                jQuery(".form-field._advert_listing_featured_field span.description").css("display", "inline-block");
                jQuery('#product-type').change();
            });
	</script>
    </div>
    <?php
}


