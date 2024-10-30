<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use ICarryShippingForWooCommerce\{ICARRY_Config, ICARRY_ICarryAPI};

add_action(
	'admin_enqueue_scripts',
	function ($hook_suffix) {

		if ('woocommerce_page_wc-settings' === $hook_suffix && isset($_GET['tab']) && $_GET['tab'] === 'shipping' && isset($_GET['section']) && $_GET['section'] === 'icarry_shipping_for_woocommerce') {

			wp_register_style(
                'icarry-shipping-for-woocommerce',
                ICARRY_Config::PLUGIN_DIR_URL . 'assets/css/styles.css',
                array(),
                ICARRY_Config::DEVELOPMENT ? time() : filemtime(ICARRY_Config::PLUGIN_DIR_PATH . 'assets/css/styles.css')
            );
            wp_enqueue_style('icarry-shipping-for-woocommerce');
            
            wp_register_script(
                'icarry-shipping-for-woocommerce',
                ICARRY_Config::PLUGIN_DIR_URL . 'assets/js/js.min.js',
                array(),
                ICARRY_Config::DEVELOPMENT ? time() : filemtime(ICARRY_Config::PLUGIN_DIR_PATH . 'assets/js/js.min.js'),
                true
            );
            wp_enqueue_script('icarry-shipping-for-woocommerce');

			wp_localize_script(
				'icarry-shipping-for-woocommerce',
				'ICarryShippingForWooCommerceVariables',
				array(
					'AjaxUrl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ICarryShippingForWooCommerce'),
				)
			);
		}
	},
	20
);



function icarry_shipping_for_woocommerce_check_connectivity_fetch_request()
{
	// Check the nonce field for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ICarryShippingForWooCommerce')) {
        wp_send_json(array(
			'type' => 'error',
			'message' => 'WordPress security error',
		));
        return;
    }

	// Validate and sanitize input data
	$email = isset( $_POST['email'] ) && ! empty( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) && ! empty( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';


	if ( empty( $email ) || empty( $password ) ) {
		wp_send_json( array(
			'type'    => 'error',
			'message' => 'Email and password are required',
		) );
		return;
	}

	$ICARRY_ICarryAPI = new ICARRY_ICarryAPI();

	$response = $ICARRY_ICarryAPI->icarry_getToken($email, $password);

	if ($response['type'] == 'success') {
		$response['token'] = $response['message'];
	}

	return wp_send_json($response);
}

add_action('wp_ajax_icarry_shipping_for_woocommerce_check_connectivity_fetch_request', '\ICarryShippingForWooCommerce\icarry_shipping_for_woocommerce_check_connectivity_fetch_request');
add_action('wp_ajax_nopriv_icarry_shipping_for_woocommerce_check_connectivity_fetch_request', '\ICarryShippingForWooCommerce\icarry_shipping_for_woocommerce_check_connectivity_fetch_request');


