<?php
/*
 * Plugin Name: WP Adverts - WooCommerce Payments
 * Plugin URI: http://wpadverts.com/
 * Description: This module allows to accept payments using WooCommerce plugin.
 * Author: Greg Winiarski
 * Text Domain: wpadverts-wc
 * Version: 1.2.1
 * 
 * Adverts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Adverts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Adverts. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Adverts
 * @subpackage WooCommerce
 * @author Grzegorz Winiarski
 * @global $adverts_namespace array
 * @version 0.1
 */

add_action( 'plugins_loaded', 'adext_wc_payments_namespace' );
add_action( 'init', 'adext_wc_payments_init' );
add_action( 'init', 'adext_wc_payments_init_wc', 7 );

register_activation_hook( __FILE__, 'adext_wc_payments_activate' );

if(is_admin() ) {
    add_action( 'init', 'adext_wc_payments_init_admin' );
} else {
    add_action( 'init', 'adext_wc_payments_init_frontend' );
}

/**
 * Activation hook
 * 
 * This function is called on activation, the activation will fail if user
 * does not have WooCommerce 2.6.0 (or newer) and WPAdverts installed.
 * 
 * @global array $adverts_namespace
 * 
 * @access public
 * @since 1.0
 * @return void
 */
function adext_wc_payments_activate() {
    if( !class_exists( 'WooCommerce' ) ) {
        printf('You need to install WooCommerce plugin before activating WPAdverts WC Integration.');
        exit;
    }
    
    if( version_compare( WooCommerce::instance()->version, '2.6.0', '<' ) ) {
        printf('This plugins requires WooCommerce 2.6.0 or newer, you are using WC %s', WooCommerce::instance()->version );
        exit;
    }
    
    if( ! defined( "ADVERTS_PATH" ) ) {
        printf('You need to install WPAdverts plugin before activating WPAdverts WC Integration.');
        exit;
    }
}

/**
 * Adds default addon configuration to $adverts_namespace
 * 
 * @global array $adverts_namespace
 * 
 * @access public
 * @since 1.0
 * @return void
 */
function adext_wc_payments_namespace() {
    global $adverts_namespace;

    // Add WooCommerce to adverts_namespace, in order to store module options and default options
    $adverts_namespace['wc_payments'] = array(
        'option_name' => 'adext_wc_payments_config',
        'default' => array(
            'show_manage_adverts' => 0,
            'user_panel_label' => __("My Ads", "wpadverts-wc"),
            'user_panel_endpoint' => 'my-ads'
        )
    );
}

/**
 * Inits Adverts-WooCommerce global filters
 * 
 * This function executes actions/filters that need to be run with every request
 * when the integration is enabled.
 * 
 * @access public
 * @since 1.0
 * @return void
 */
function adext_wc_payments_init() {
    
    load_plugin_textdomain("wpadverts-wc", false, dirname(plugin_basename(__FILE__))."/languages/");
    
    if( !class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    include_once plugin_dir_path( __FILE__ ) . "/includes/class-wc-product-advert-single.php";
    include_once plugin_dir_path( __FILE__ ) . "/includes/class-wc-product-advert-renew.php";
    
    register_post_status( 'wc_pending', array(
        'label'        => _x( 'Pending', 'post' ),
        'public'       => is_admin() || current_user_can( "edit_pages" ),
        'label_count'  => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', "wpadverts-wc" )
     ) );
    
    add_action( "woocommerce_add_order_item_meta", "adext_wc_payments_order_item_meta", 10, 2 );
    add_action( 'woocommerce_order_status_processing', "adext_wc_payments_order_paid" );
    add_action( 'woocommerce_order_status_completed', "adext_wc_payments_order_paid" );
    
    wp_register_style( 'adverts-wc-payments-frontend', plugins_url( '/assets/css/wc-payments-frontend.css', __FILE__ ), array(), 1 );

}

/**
 * Init WooCommerce payments admin filters and actions
 * 
 * @since 1.0
 * @return void
 */
function adext_wc_payments_init_admin() {
    
    if( !class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    if( version_compare( WooCommerce::instance()->version, '2.6.0', '<' ) ) {
        return;
    }
    
    if( ! defined( "ADVERTS_PATH" ) ) {
        return;
    }
    
    include_once ADVERTS_PATH . 'includes/class-updates-manager.php';
    $manager = new Adverts_Updates_Manager(
        "wpadverts-wc/wpadverts-wc.php", 
        "wpadverts-wc", 
        "1.2.1"
    );
    $manager->connect();
    
    // delayed woocommerce install
    add_action( "init", "adext_wc_payments_install");
    
    add_filter( "product_type_selector", "adext_wc_payments_product_type" );
    add_action( 'woocommerce_process_product_meta', 'adext_wc_payments_save_product_data' );
    add_action( 'woocommerce_product_options_general_product_data', 'adext_wc_payments_product_data' );
    
    add_filter( 'display_post_states', 'adext_display_wc_pending_state' );
    add_action( 'admin_head', 'adext_wc_payments_admin_head' );
    
    include_once plugin_dir_path( __FILE__ ) . "/includes/admin-pages.php";
}

/**
 * Init WooCommerce payments frontend filters and actions
 * 
 * @since 1.0
 * @return void
 */
function adext_wc_payments_init_frontend() {

    if( !class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    add_filter("adverts_action", "adext_wc_payments_add_action_payment");
    add_filter("adverts_form_load", "adext_wc_payments_form_load");
    
    add_action( "template_redirect", "adext_wc_payments_cart_redirect" );
    add_action( "template_redirect", "adext_wc_payments_cart_redirect_renew" );

    add_action( "adverts_sh_manage_actions_more", "adext_wc_payments_action_renew" );  //SimplyWorld
    add_filter( "adverts_manage_action", "adext_wc_payments_manage_action" );
    add_filter( "adverts_manage_action_renew", "adext_wc_payments_manage_action_renew" );
    
    add_filter("adverts_sh_manage_list_statuses", "adext_wc_payments_sh_manage_list_statuses");
    add_action("adverts_sh_manage_list_status", "adext_wc_payments_sh_manage_list_status");
    
    wp_register_style( 'adverts-payments-frontend', ADVERTS_URL . '/addons/payments/assets/css/payments-frontend.css');
}

/**
 * Init WooCommerce payments frontend filters and actions
 * 
 * This function is executed by 'woocommerce_init' action
 * 
 * @since 1.0.3
 * @return void
 */
function adext_wc_payments_init_wc() {

    if( !class_exists( 'WooCommerce' ) || !defined( 'ADVERTS_PATH' ) ) {
        return;
    }
    
    if( get_current_user_id() && adverts_config( 'wc_payments.show_manage_adverts' ) != 0 ) {
        include_once plugin_dir_path( __FILE__ ) . "/includes/class-adverts-wc-user-panel.php";
        $wpadverts_wc_user_panel = new Adverts_WC_User_Panel();
    }
}

/**
 * Switch shortcode_adverts_add action to "wc_payment"
 * 
 * Function checks if next current action in shortcode shortcode_adverts_add
 * is "save" and if listing price is greater than 0. If so then current action 
 * is changed to "wc_payment".
 * 
 * @see shortcode_adverts_add()
 * @since 1.0
 * 
 * @param type $action
 * @return string
 */
function adext_wc_payments_add_action_payment( $action ) {
    if( $action != "save" ) {
        return $action;
    } else {
        return "wc_payments";
    }
}

/**
 * Redirect user to WooCommerce cart if user is in third step
 * 
 * This function checks if user is in third ad posting step, if so then his selected
 * listing is added to cart and user is redirected to WC cart
 * 
 * @see template_redirect
 * @since 1.0
 * @return void
 */
function adext_wc_payments_cart_redirect() {
    global $woocommerce;
    
    $action = adverts_request("_adverts_action");
    $post = get_post( adverts_request( "_post_id" ) );
    
    if( !is_object( $post ) ) {
        return;
    }
    
    $product = get_product( get_post_meta( $post->ID, "payments_listing_type", true ) );


    if($action != "save" || !$post ||  !$product instanceof WC_Product) {
        return;
    }

    if( $product->get_price() == 0) {
        
        $publish = current_time('mysql');
        $visible = absint( get_post_meta( $product->get_id(), '_advert_listing_duration', true ) );

        if( $visible < 1 ) {
            $visible = adverts_config( "visibility" );
        }
        
        $expiry = strtotime( $publish . " +$visible DAYS" );
        update_post_meta( $post->ID, "_expiration_date", $expiry );
        
        remove_filter("adverts_action", "adext_wc_payments_add_action_payment");
        
    } else {
    
        wp_update_post(array(
            "ID" => $post->ID,
            "post_status" => "wc_pending"
        ));

        if( !is_user_logged_in() && get_post_meta( $post->ID, "_adverts_account", true) == 1 ) {
            adverts_create_user_from_post_id( $post->ID, true );
        }

        $woocommerce->cart->add_to_cart( $product->get_id(), 1, '', '', array(
            'advert_id' => $post->ID
        ) );

        woocommerce_add_to_cart_message( $product->get_id() );

        // Redirect to checkout page
        wp_redirect( get_permalink( woocommerce_get_page_id( 'checkout' ) ) );
        exit;
        
    }
}

/**
 * Redirect user to WooCommerce cart if user is in third step
 * 
 * This function checks if user is in third ad posting step, if so then his selected
 * listing is added to cart and user is redirected to WC cart
 * 
 * @see template_redirect
 * @since 1.2
 * @return void
 */
function adext_wc_payments_cart_redirect_renew() {
    global $woocommerce;
    
    if( adverts_request( "_adverts_renew" ) != "1" ) {
        return;
    }
    
    $post = get_post( adverts_request( "advert_renew" ) );
      
    if( ! is_object( $post ) ) {
        return;
    }
    
    $product = get_product( adverts_request( "payments_listing_type" ) );

    if( ! $product || $product->get_price() == 0 ) {
        return;
    }

    $woocommerce->cart->add_to_cart( $product->get_id(), 1, '', '', array(
        'advert_id' => $post->ID
    ) );

    woocommerce_add_to_cart_message( $product->get_id() );

    // Redirect to checkout page
    wp_redirect( get_permalink( woocommerce_get_page_id( 'checkout' ) ) );
    exit;
}

/**
 * Returns list of Adverts products
 * 
 * This function is used to return or WooCommerce products that can be used
 * to with Adverts.
 * 
 * @access public
 * @since 1.0
 * @return array Array of Adverts products
 */
function adext_wc_payments_products( ) {

    return array( 
        "advert_single" => get_posts( apply_filters( "adext_wc_payments_products_new", array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => array( 'advert_single' )
                )
            ),
        ) ) ),
        "advert_renew" => get_posts( apply_filters( "adext_wc_payments_products_renew", array( 
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => array( 'advert_renew' )
                )
            ),
        ) ) )
    );
}



/**
 * Saves Adverts meta data when WooCommerce order is saved in DB
 * 
 * @param int $item_id Ordered item ID
 * @param array $values Ordered item additional data
 */
function adext_wc_payments_order_item_meta( $item_id, $values ) {
    // Add the fields
    //var_dump($values); die;
    if ( isset( $values['advert_id'] ) ) {
        $ad = get_post( absint( $values['advert_id'] ) );

        woocommerce_add_order_item_meta( $item_id, __( 'Ad', 'wpadverts-wc' ), $ad->post_title );
        woocommerce_add_order_item_meta( $item_id, '_advert_id', $values['advert_id'] );
    }
}

/**
 * Adds Listing Type field to Add Advert form.
 * 
 * This function is applied to "adverts_form_load" filter in Adverts_Form::load()
 * when Advert form is being loaded.
 * 
 * @since 1.0
 * @see Adverts_Form::load()
 * 
 * @param array $form
 * @return array
 */
function adext_wc_payments_form_load( $form ) {
    
    if($form["name"] != 'advert' || is_admin() || adverts_request( "advert_id" )) {
        return $form;
    }
    
    $form["field"][] = array(
        "name" => "_listing_information",
        "type" => "adverts_field_header",
        "order" => 10001,
        "label" => __( 'Listing Information', 'wpadverts-wc' )
    );
    
    $opts = array();
    $pricings = adext_wc_payments_products();

    adverts_form_add_field("adext_wc_payments_field_payment", array(
        "renderer" => "adext_wc_payments_field_payment",
        "callback_save" => "adverts_save_single",
        "callback_bind" => "adverts_bind_single",
    ) );
    
    foreach($pricings["advert_single"] as $data) {
        
        $product = get_product($data->ID );

        if($data->post_content) {
            $post_content = '<br/><small style="padding-left:25px">'.$data->post_content.'</small>' ;
        } else {
            $post_content = '';
        }
        
        $adverts_price = $product->get_price_html();
        
        
        $text = sprintf(
            __('<b>%1$s</b> - %2$s for %3$d days.%4$s', 'wpadverts-wc'), 
            $data->post_title, 
            $adverts_price, 
            get_post_meta( $data->ID, '_advert_listing_duration', true ),
            $post_content
        );
        $opts[] = array("value"=>$data->ID, "text"=>$text);
    }

    wp_enqueue_style( 'adverts-wc-payments-frontend' );
    
    $form["field"][] = array(
        "name" => "payments_listing_type",
        "type" => "adext_wc_payments_field_payment",
        "label" => __("Listing", "wpadverts-wc"),
        "order" => 10002,
        "is_required" => true,
        "empty_option" => true,
        "options" => $opts,
        "value" => null,
        "validator" => array(
            array( "name" => "is_required" ),
        )
    );
    
    
    return $form;
}

/**
 * HTML for Listing Type field in [adverts_add]
 * 
 * This function echos HTML for "Listing Type" field in [adverts_add], it is being 
 * regstered as a new field and called in adext_wc_payments_form_load() function.
 * 
 * @see adext_wc_payments_form_load()
 * 
 * @since 1.0.3
 * @access public
 * @param array $field      Form field
 * @return void
 */
function adext_wc_payments_field_payment( $field ) {
    
    ob_start();
    
    echo '<div class="adverts-pricings-list">';
    
    foreach( $field["options"] as $option ) {
    
        $post_id = $option["value"];
        $post = get_post( $post_id );
        $product = get_product( $post_id );
        $adverts_price = $product->get_price_html();
        $visible = get_post_meta( $post_id, '_advert_listing_duration', true );
        
        ?>

        <div class="adverts-listing-type-x">

            <label class="adverts-cute-input adverts-cute-radio " for="<?php echo esc_attr( $field["name"] . "_" . $option["value"] ) ?>">
                <input name="<?php echo esc_attr( $field["name"] ) ?>" class="adverts-listing-type-input" id="<?php echo esc_attr( $field["name"] . "_" . $option["value"] ) ?>" type="radio" value="<?php echo $post->ID ?>" <?php checked($post->ID, $field["value"]) ?> />
                <div class="adverts-cute-input-indicator"></div>
            </label>

            <div class="adverts-listing-type-field">
                <div class="adverts-listing-type-name">
                    <span class="adverts-listing-type-title"><?php echo esc_html( $post->post_title ) ?></span>

                </div>

                <div class="adverts-listing-type-features">
                    <span class="adverts-listing-type-feature-duration">
                        <span class="adverts-listing-type-icon adverts-icon-clock"></span>
                        <?php printf( _n("Visible 1 day", "Visible %d days", $visible, "wpadverts-wc"), $visible) ?>
                    </span>

                    <?php do_action("adverts_payments_features", $post->ID ) ?>
                </div>

                <?php if($post->post_excerpt): ?>
                <div class="adverts-listing-type-features adverts-listing-type-icon adverts-icon-info">
                    <?php echo $post->post_excerpt ?>
                </div>
                <?php endif; ?>
            </div>

            <span class="adverts-listing-type-cost">
                <?php echo $adverts_price ?>
            </span>
        </div>

        <?php
    }
    
    echo '</div>';
    echo ob_get_clean();
}


/**
 * Return WC Order ID associated with given advert ID
 * 
 * @global wpdb $wpdb
 * 
 * @param int $advert_id Advert ID
 * @since 1.0
 * @return int Order ID
 */
function adext_wc_payments_get_advert_order_id( $advert_id ) {
    global $wpdb;
    
    $pfix = $wpdb->prefix;
    
    
    $v = $wpdb->get_var( 
        $wpdb->prepare(
            "SELECT `order_id` FROM `{$pfix}woocommerce_order_itemmeta` INNER JOIN `{$pfix}woocommerce_order_items` USING(`order_item_id`)  
             WHERE meta_key = '_advert_id' AND meta_value = %d", 
            $advert_id
        )
    );
            
    return $v;
}

/**
 * Executes some action when order is marked as completed.
 * 
 * This function publishes a pending Ad or renews an item (depending on purchased
 * product).
 * 
 * @since 1.0
 * @param int $order_id Proccessed order ID
 * @return void
 */
function adext_wc_payments_order_paid( $order_id ) {
    // Get the order
    $order = new WC_Order( $order_id );

    if ( get_post_meta( $order_id, 'adverts_wc_payment_processed', true ) ) {
        return;
    }
    
    foreach ( $order->get_items() as $item ) {
        $product = get_product( $item['product_id'] );

        if ( $product->is_type( 'advert_single' ) ) {
            //for ( $i = 0; $i < $item['qty']; $i ++ ) {
                //$user_package_id = wc_paid_listings_give_user_package( $order->customer_user, $product->id );
            //}

            if( $item->get_meta( "_advert_id" ) ) {
                $advert = get_post( $item->get_meta( "_advert_id" ) );

                if ( in_array( $advert->post_status, array( 'wc_pending' ) ) ) {

                    $duration = absint(get_post_meta( $item->get_product_id(), '_advert_listing_duration', true ));
                    $featured = get_post_meta( $item->get_product_id(), '_advert_listing_featured', true );
                    
                    $update                  = array();
                    $update['ID']            = $advert->ID;
                    $update['post_status']   = 'publish';
                    $update['post_date']     = current_time( 'mysql' );
                    $update['post_date_gmt'] = current_time( 'mysql', 1 );

                    if( $featured == "yes" ) {
                        $update['menu_order'] = 1;
                    }
                    
                    wp_update_post( $update );
                    update_post_meta( $advert->ID, "_expiration_date", strtotime( current_time('mysql') . " +$duration DAYS" ) );
                    
                    do_action( "adext_wc_payments_order_paid", $order_id, $advert->ID);
                }
            }
        }
        
        if ( $product->is_type( 'advert_renew' ) ) {
            //for ( $i = 0; $i < $item['qty']; $i ++ ) {
                //$user_package_id = wc_paid_listings_give_user_package( $order->customer_user, $product->id );
            //}

            if( $item->get_meta( "_advert_id" ) ) {
                $advert = get_post( $item->get_meta( "_advert_id" ) );

                if ( in_array( $advert->post_status, array( 'wc_pending', 'publish', 'expired' ) ) ) {

                    $duration = absint(get_post_meta( $item->get_product_id(), '_advert_listing_duration', true ));
                    $featured = get_post_meta( $item->get_product_id(), '_advert_listing_featured', true );
                    
                    $update                  = array();
                    $update['ID']            = $advert->ID;
                    $update['post_status']   = 'publish';

                    if( $featured == "yes" ) {
                        $update['menu_order'] = 1;
                    } else {
                        $update['menu_order'] = 0;
                    }
                    
                    wp_update_post( $update );

                    $expires = get_post_meta( $advert->ID, "_expiration_date", true );

                    // Update Ad expiration date if the expiration date is set
                    if( $expires ) {
                        // Udpdate expiration date if the Ad expires
                        if( $expires > current_time('timestamp') ) {
                            $publish = date( "Y-m-d H:i:s", $expires );
                        } else {
                            $publish = current_time('mysql');
                        }

                        if( $duration > 0) {
                            $expiry = strtotime( $publish . " +$duration DAYS" );
                            update_post_meta( $advert->ID, "_expiration_date", $expiry );
                        } else {
                            delete_post_meta( $advert->ID, "_expiration_date" );
                        }
                    }

                    do_action( "adext_wc_payments_order_paid", $order_id, $advert->ID);
                }
            }
        }
    }

    update_post_meta( $order_id, 'adverts_wc_payment_processed', true );
}


/**
 * Adds wc_pending status to list of post_statuses in [adverts_manage] shortcode
 * 
 * This function adds new status to the array so in [adverts_manage] panel Ads that
 * are pending payment can be displayed with apprioprate icon.
 * 
 * @since 1.0
 * @access public
 * @param array $statuses
 * @return array Updates list of statuses
 */
function adext_wc_payments_sh_manage_list_statuses( $statuses ) {
    $statuses[] = "wc_pending";
    return $statuses;

}


/**
 * Adds additional icon in [adverts_manage] shortcode
 * 
 * This function adds additional icon in [adverts_manage] shortcode on the Ads list. The icon 
 * informs user that *this* ad is Pending Payment (if post_status == wc_pending)
 * 
 * @since 1.0
 * @access public
 * @param WP_Post $post
 * @return string Post status HTML to display
 */
function adext_wc_payments_sh_manage_list_status( $post ) {
    if($post->post_status != "wc_pending") {
        return;
    }
    
    include_once ADVERTS_PATH . "/includes/class-html.php";
    
    $html = new Adverts_Html("span", array(
        "class" => "adverts-inline-icon adverts-inline-icon-warn adverts-icon-credit-card",
        "title" => __("Inactive — Waiting for payment.", "wpadverts-wc")
    ));
    $html->forceLongClosing();
    
    echo "&nbsp;".$html->render();
}

/**
 * Renders Pending Payment post status
 * 
 * This function is executed by admin_head action, it adds 'pending payment' 
 * status in Advert edition panel
 * 
 * @see admin_head action
 * 
 * @global string $post_type
 * @global WP_Post $post
 * @since 1.0
 * @return void
 */
function adext_wc_payments_admin_head() {
    global $post_type, $post;

    // Make sure this is Adverts post type
    if ( $post_type == 'advert' && $post && $post->post_status == 'wc_pending' ):
    ?>

    <script type="text/javascript">
        jQuery(function($) {
            $("select#post_status").append($("<option></option>")
                .attr("id", "wpadverts-wc-payments-pending-payment")
                .attr("value", "wc_pending")
                .addClass("adverts-post-status")
                .css("display", "none")
                .html("&nbsp;" + "<?php _e( "Pending Payment", "wpadverts-wc" ) ?>")
            );
                
            $("#wpadverts-wc-payments-pending-payment").prop("selected", true).attr("selected", "selected");
            $("input#publish").val("<?php _e("Update", "wpadverts-wc") ?>");
            var x = 0;
        });
    </script>
    <?php 
    
    endif; 
}

/**
 * Displays "Renew Ad" button in [adverts_manage].
 * 
 * This function is executed by adverts_sh_manage_actions_more filter, so
 * it will be displayed after clicking "More".
 * 
 * @see adverts_sh_manage_actions_more
 * 
 * @since 1.2.0
 * @param   int     $post_id    Post ID
 * @return  void
 */ // SimplyWorld

function adext_wc_payments_action_renew( $post_id ) {

    $pricings = adext_wc_payments_products();
    $renewals = $pricings["advert_renew"];

    $renewals = apply_filters( "wpadverts_filter_renewals", $renewals, $post_id );

    if( empty( $renewals ) ) {
        return;
    }

    include_once ADVERTS_PATH . "/includes/class-html.php";

    $span = '<span class="adverts-icon-arrows-cw"></span>';
    $a = new Adverts_Html("a", array(
        "href" => add_query_arg( "advert_renew", $post_id ),
        "class" => "adverts-manage-action",
        "id" => "wpadverts-wc", //SimplyWorld
    ), $span . " " . __("Renew Ad", "wpadverts-wc") );

    echo $a->render();
}

/**
 * Switch shortcode_adverts_manage action to "renew"
 * 
 * Function checks if $_GET param advert_renew is set and if it is an ID
 * of existing Advert owned by current user, if it is then action "renew"
 * is enqueued.
 * 
 * @see shortcode_adverts_manage()
 * @since 1.2.0
 * 
 * @param   string  $action     Current action to execute
 * @return  string
 */

function adext_wc_payments_manage_action( $action ) {
        
    // continue if there is advert_renew param
    if( ! adverts_request( "advert_renew" ) ) {
        return $action;
    }
    
    $advert = get_post( adverts_request( "advert_renew" ) );
    
    if( ! $advert instanceof WP_Post ) {
        return $action;
    }
    
    if( $advert->post_type != "advert" ) {
        return $action;
    }
    
    if( $advert->post_author != get_current_user_id() ) {
        return $action;
    }
    
    $action = "renew";
    
    return $action;
}

/**
 * Renders a form which allows to renew an Advert
 * 
 * This function is executed using adverts_manage_action_renew filter.
 * 
 * @see     adverts_manage_action_renew
 * @since   1.1.0
 * 
 * @param   string    $content  Content generated form [adverts_manage]
 * @param   array     $atts     [adverts_manage] params
 * @return  string              HTML for form which will allow to renew an Ad
 */
function adext_wc_payments_manage_action_renew( $content, $atts = array() ) {

    $error = null;
    $info = null;
    
    $baseurl = apply_filters( "adverts_manage_baseurl", get_the_permalink() );
    
    wp_enqueue_style( 'adverts-wc-payments-frontend' );
    
    $adverts_flash = array( "error" => array(), "info" => array() );
    $post = get_post( adverts_request( "advert_renew" ) );
    
    if( ! in_array( $post->post_status, array( 'publish', 'expired' ) ) ) {
        $format = __( 'Cannot renew Ads with status \'pending\', <a href="%s">cancel and go back</a>.', "wpadverts-wc" );
        $adverts_flash["error"][] = sprintf( $format, $baseurl );
        ob_start();
        adverts_flash( $adverts_flash );
        return ob_get_clean();
    }
    
    $form["field"][] = array(
        "name" => "_listing_information",
        "type" => "adverts_field_header",
        "order" => 1000,
        "label" => __( 'Listing Information', 'wpadverts-wc' )
    );
    
    $opts = array();
    $pricings = adext_wc_payments_products();
    $pricings = $pricings["advert_renew"];
    $pricings = apply_filters( "wpadverts_filter_renewals", $pricings, $post->ID );
    
    adverts_form_add_field("adext_wc_payments_field_payment", array(
        "renderer" => "adext_wc_payments_field_payment",
        "callback_save" => "adverts_save_single",
        "callback_bind" => "adverts_bind_single",
    ) );
    
    foreach($pricings as $data) {
        
        $product = get_product($data->ID );

        if($data->post_content) {
            $post_content = '<br/><small style="padding-left:25px">'.$data->post_content.'</small>' ;
        } else {
            $post_content = '';
        }
        
        $adverts_price = $product->get_price_html();


        $text = sprintf(
            __('<b>%1$s</b> - %2$s for %3$d days.%4$s', 'wpadverts-wc'), 
            $data->post_title, 
            $adverts_price, 
            get_post_meta( $data->ID, '_advert_listing_duration', true ),
            $post_content
        );
        $opts[] = array("value"=>$data->ID, "text"=>$text);
    }

    $form = array(
        "name" => "advert-renew",
        "field" => array(
            array(
                "name" => "_adverts_renew",
                "type" => "adverts_field_hidden",
                "value" => "1",
                "order" => 1000
            ),
            array(
                "name" => "payments_listing_type",
                "type" => "adext_wc_payments_field_payment",
                "label" => null,
                "order" => 1001,
                "empty_option" => true,
                "options" => $opts,
                "value" => "",
                "validator" => array(
                    array( "name" => "is_required" )
                )
            )
        )
    );

    include_once ADVERTS_PATH . 'includes/class-html.php';
    include_once ADVERTS_PATH . 'includes/class-form.php';
    
    $form_scheme = apply_filters( "adverts_form_scheme", $form, null );
    $form = new Adverts_Form( $form_scheme );
    $form_label_placement = "adverts-form-stacked";
    $buttons = array(
        array(
            "html" => "",
            "tag" => "input",
            "type" => "submit",
            "value" => __( "Renew", "wpadverts-wc" ),
            "style" => "font-size:1.2em"
        )
    );
    
    if( isset( $_POST ) && ! empty( $_POST ) ) {
        $form->bind( stripslashes_deep( $_POST ) );
        $valid = $form->validate();


        if( $valid ) {

            wp_enqueue_script( 'adext-payments' );
            wp_enqueue_script( 'adverts-frontend' );
            $product = get_product( $form->get_value( "payments_listing_type" ) );

//            if( $product->get_price() == 0 ) {

                $m = __( 'Ad <strong>%s</strong> renewed. <a href="%s">Go back to Ads list</a>.', 'wpadverts-wc');
                $adverts_flash["info"][] = sprintf( $m, $post->post_title, $baseurl );
                $moderate = apply_filters( "adverts_manage_moderate", false );
                $post_id = wp_update_post( array(
                    "ID" => $post->ID,
                    "post_status" => $moderate == "1" ? 'pending' : 'publish',
                ));

                $duration = absint( get_post_meta( $product->id, '_advert_listing_duration', true ) );
                $time = strtotime( current_time('mysql') . " +" . $duration . " DAYS" );
                update_post_meta( $post_id, "_expiration_date", $time );

                ob_start();

                // wpadverts/templates/add-payment.php
                include ADVERTS_PATH . '/templates/add-save.php';

                return ob_get_clean();
//            }

        }

    } 

    $m1 = __( 'Renew <strong>%s</strong> or <a href="%s">cancel and go back</a>.', 'wpadverts-wc');
    $m2 = __( 'Select renewal option and click "Renew" button.', 'wpadverts-wc');
    $adverts_flash["info"][] = sprintf( $m1, $post->post_title, $baseurl ) . "<br/>" . sprintf( $m2, $baseurl );
    
    ob_start();

    // adverts/templates/form.php
    include apply_filters( "adverts_template_load", ADVERTS_PATH . 'templates/form.php' );
    return ob_get_clean();
}
