<?php
/**
 * Plugin Name: Shipping Contact for WooCommerce
 * Plugin URI: https://gist.github.com/helgatheviking/c3381322bade0227d762ba1cf429271e
 * Description: Add a shipping email field to checkout and notify of new orders
 * Version: 1.1.0
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * WC requires at least: 3.5.0
 * WC tested up to: 4.5
 *
 * Text Domain: wc-shipping-contact
 * Domain Path: /languages/
 *
 * Copyright: © 2020 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

namespace WC_Shipping_Contact;

/**
 * Add hooks and filters
 */
function add_hooks_and_filters() {

	// Add fields to checkout.
	add_filter( 'woocommerce_shipping_fields' , __NAMESPACE__ . '\add_shipping_fields' );
	// Add emails to admin order view.
	add_filter( 'woocommerce_admin_shipping_fields' , __NAMESPACE__ . '\admin_shipping_fields' );

	// Add recipient to specific emails.
	add_filter( 'woocommerce_email_recipient_customer_processing_order' , __NAMESPACE__ . '\add_recipient', 20, 2 );
	add_filter( 'woocommerce_email_recipient_customer_completed_order' , __NAMESPACE__ . '\add_recipient', 20, 2 );
	add_filter( 'woocommerce_email_recipient_customer_note' , __NAMESPACE__ . '\add_recipient', 20, 2 );

	// Display meta key in order overview.
	add_action( 'woocommerce_order_details_after_customer_details' , __NAMESPACE__ . '\after_customer_details' );

	// Display meta key in email.
	add_action( 'woocommerce_email_customer_details' , __NAMESPACE__ . '\email_after_customer_details', 15, 3 );
}

add_action( 'woocommerce_loaded', __NAMESPACE__ . '\add_hooks_and_filters' );

/*-----------------------------------------------------------------------------------*/
/* Plugin Functions */
/*-----------------------------------------------------------------------------------*/

/**
 * Add email to front-end shipping fields
 *
 * @param  array $fields
 * @return  array
 */
function add_shipping_fields( $fields ) {
	$fields['shipping_email'] = array(
		'label' 		=> esc_html__( 'Shipping Email', 'wc-shipping-contact' ),
		'required' 		=> true,
		'class' 		=> array( 'form-row-first' ),
		'validate'		=> array( 'email' ),
	);
	$fields['shipping_phone'] = array(
		'label' 		=> esc_html__( 'Shipping Phone', 'wc-shipping-contact' ),
		'required' 		=> false,
		'type'		=> 'tel',
		'class'    	=> array( 'form-row-last' ),
		'clear'    	=> true,
		'validate' 	=> array( 'phone' ),
	);
	return $fields;
}

/**
 * Add email to Admin Order overview
 *
 * @param  array $fields
 * @return  array
 */
function admin_shipping_fields( $fields ) {
	$fields['email'] = array(
		'label' 		=> esc_html__( 'Shipping Email', 'wc-shipping-contact' )
	);
	$fields['phone'] = array(
		'label' 		=> esc_html__( 'Shipping Phone', 'wc-shipping-contact' ),
		'wrapper_class' => '_shipping_state_field' // Borrow a class from WC that will float it right
	);
	return $fields;
}

/**
 * Add recipient to emails
 *
 * @param  str $email, comma-delimited list of addresses
 * @param  obj WC_Order $order
 * @return  str
 */
function add_recipient( $email, $order ) {

	$additional_email = $order->get_meta( '_shipping_email', true );

	if ( $additional_email && is_email( $additional_email )) {
		$email = explode( ',', $email );
		array_push( $email, $additional_email );
		$email = implode(",", $email);
	}
	return $email;
}

/**
 * Display meta in my-account area Order overview
 *
 * @param WC_Order $order
 */

function after_customer_details( $order ) {
	
	$email = $order->get_meta( '_shipping_email', true );
	
	if ( $email ) {
		echo '<dt>' . esc_html__( 'Shipping Email', 'wc-shipping-contact' ) . ':</dt><dd>' . $email . '</dd>';
	}

	$phone = $order->get_meta( '_shipping_phone', true );
	
	if ( $phone ) {
		echo '<dt>' . esc_html__( 'Shipping Phone', 'wc-shipping-contact' ) . ':</dt><dd>' . $phone . '</dd>';
	}

}

/**
 * Display meta in my-account area Order overview
 *
 * @param WC_Order $order
 * @param bool $sent_to_admin
 * @param bool $plain_text
 */

function email_after_customer_details( $order, $sent_to_admin = false, $plain_text = false ) {
	$email = $order->get_meta( '_shipping_email', true );
	$phone = $order->get_meta( '_shipping_phone', true );

	if ( $plain_text ) { 

		if ( $email ) {
			echo esc_html__ ( 'Shipping Email', 'wc-shipping-contact' ) . ': ' . wp_kses_post( $email ) . "\n";
		}
	
		if ( $phone ) {
			echo esc_html__ ( 'Shipping Email', 'wc-shipping-contact' ) . ': ' . wp_kses_post( $phone ) . "\n";
		}

	} else {
		if ( $email ) {
			echo '<p><strong>' . esc_html__ ( 'Shipping Email', 'wc-shipping-contact' ) . ':<strong>' . wp_kses_post( $email ) . '</p>';
		}
	
		if ( $phone ) {
			echo '<p><strong>' . esc_html__ ( 'Shipping Phone', 'wc-shipping-contact' ) . ':<strong>' . wp_kses_post( $phone ) . '</p>';
		}
	}

}



