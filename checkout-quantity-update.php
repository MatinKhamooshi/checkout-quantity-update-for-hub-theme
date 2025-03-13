<?php
/*
Plugin Name: Checkout Quantity Update for Hub wp theme
Plugin URI: https://github.com/MatinKhamooshi/checkout-quantity-update-for-hub-theme/
Description: Adds a quantity input beside the product name on the WooCommerce checkout page and recalculates totals when the quantity changes this is worked just on hub wp theme.
Version: 1.1
Author: Matin Khamooshi
Author URI: https://matinkhamooshi.ir
License: GPL2
Text Domain: checkout-quantity-update
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Quantity Input Beside Product Name on Checkout Page.
 *
 * This filter replaces the default display of product quantity on the checkout page
 * with an input field that allows users to change the quantity.
 *
 * @param string $product_quantity The HTML output for product quantity.
 * @param array  $cart_item        The cart item data.
 * @param string $cart_item_key    The cart item key.
 * @return string Modified HTML output with a quantity input field.
 */
add_filter( 'woocommerce_checkout_cart_item_quantity', 'checkout_item_quantity_input', 9999, 3 );
function checkout_item_quantity_input( $product_quantity, $cart_item, $cart_item_key ) {
    // Retrieve the product object.
    $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    // Retrieve the product ID.
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
    
    // If the product is not sold individually, display a quantity input.
    if ( ! $product->is_sold_individually() ) {
        $product_quantity = woocommerce_quantity_input( array(
            'input_name'  => 'shipping_method_qty_' . $product_id,
            'input_value' => $cart_item['quantity'],
            'max_value'   => $product->get_max_purchase_quantity(),
            'min_value'   => 1, // تغییر به 1
        ), $product, false );
        // Append a hidden input to store the cart item key.
        $product_quantity .= '<input type="hidden" name="product_key_' . $product_id . '" value="' . $cart_item_key . '">';
    }
    return $product_quantity;
}

/**
 * Detect Quantity Change and Recalculate Cart Totals on Checkout.
 *
 * This action hook processes the serialized POST data from the checkout form when quantities are changed.
 * It updates the cart quantities accordingly and recalculates the totals.
 *
 * @param string $post_data Serialized POST data from the checkout form.
 */
add_action( 'woocommerce_checkout_update_order_review', 'update_item_quantity_checkout' );
function update_item_quantity_checkout( $post_data ) {
    parse_str( $post_data, $post_data_array );
    $updated_qty = false;
    
    foreach ( $post_data_array as $key => $value ) {   
        if ( substr( $key, 0, 20 ) === 'shipping_method_qty_' ) {         
            $id = substr( $key, 20 );
            if ( isset( $post_data_array['product_key_' . $id] ) ) {
                // Change false to true for auto update.
                WC()->cart->set_quantity( $post_data_array['product_key_' . $id], $post_data_array[$key], true );
                $updated_qty = true;
            }
        }     
    }  
    if ( $updated_qty ) {
        WC()->cart->calculate_totals();
    }
}

/**
 * Enqueue assets for checkout quantity update.
**/
 
function cqu_enqueue_assets() {
    if ( is_checkout() ) {
        // Enqueue our custom JS file located inside the plugin's "assets/js" folder.
        wp_enqueue_script(
            'checkout-quantity-update-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js',
            array(),
            '1.1',
            true
        );
        
        wp_enqueue_style(
            'cqu-style', // handle/style name
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            array(),
            '1.0.0',
            'all'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'cqu_enqueue_assets', 99 );