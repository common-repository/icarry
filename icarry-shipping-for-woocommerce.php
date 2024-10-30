<?php
/*
* Plugin Name: iCARRY
* Description: Adds new iCARRY shipping options.
* Version: 1.0
* Requires at least: 6.0
* Requires PHP: 7.4
* Author: iCARRY
* Author URI: https://icarry.com/
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: icarry-shipping-for-woocommerce
* Requires Plugins: woocommerce > 
*/
 
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'ICARRY_PLUGIN_DIR_PATH' ) ) {
	define( 'ICARRY_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ICARRY_PLUGIN_DIR_URL' ) ) {
	define( 'ICARRY_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

use ICarryShippingForWooCommerce\{ICARRY_Config, ICARRY_Dependencies, ICARRY_ShippingMethod, ICARRY_OrderCreated};

// Include necessary files
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/helpers.php';
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-logs.php';
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-config.php';
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-helpers.php';
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-dependencies.php';

add_action('plugins_loaded', 'ICarryShippingForWooCommerce\icarry_init', 20);
function icarry_init() {
    // Check if WooCommerce is active
    if (!ICARRY_Dependencies::icarry_ifWooCommerceIsActive()) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>' . esc_html__('WooCommerce is not active', 'icarry-shipping-for-woocommerce') . '</p></div>';
        });
        return;
    }

    // Include the rest of necessary files and initialize plugin code
    require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-icarry-api.php';
    require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-shipping-method.php';
    require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-order-created.php';
    require_once ICARRY_PLUGIN_DIR_PATH . 'inc/icarry-shipping-for-woocommerce/class-uninstall.php';

    // Load your plugin functionality
    add_action('woocommerce_shipping_init', function () {
        new ICARRY_ShippingMethod();
    });

    add_filter('woocommerce_shipping_methods', function ($methods) {
        $methods['icarry_shipping_for_woocommerce'] = 'ICarryShippingForWooCommerce\ICARRY_ShippingMethod';
        return $methods;
    });

    add_action('wp_enqueue_scripts', 'ICarryShippingForWooCommerce\icarry_enqueue_scripts');
    function icarry_enqueue_scripts() {
        wp_register_script(
            'icarry-frontend-js',
            ICARRY_PLUGIN_DIR_URL . 'assets/js/js.min.js',
            array(),
            ICARRY_Config::DEVELOPMENT ? time() : filemtime(ICARRY_PLUGIN_DIR_PATH . 'assets/js/js.min.js'),
            true
        );
        wp_enqueue_script('icarry-frontend-js');

        if (is_checkout()) {
            wp_register_script(
                'icarry-checkout-js',
                ICARRY_PLUGIN_DIR_URL . 'assets/js/checkout.js',
                array(),
                ICARRY_Config::DEVELOPMENT ? time() : filemtime(ICARRY_PLUGIN_DIR_PATH . 'assets/js/checkout.js'),
                true
            );
        }
        wp_enqueue_script('icarry-checkout-js');

        wp_localize_script('icarry-frontend-js', 'icarry_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ICarryShippingForWooCommerce')
        ));
    }

    add_filter('woocommerce_checkout_fields', 'ICarryShippingForWooCommerce\icarry_billing_city_to_dropdown');
    function icarry_billing_city_to_dropdown($fields) {
        $fields['billing']['billing_city'] = array(
            'type'        => 'select',
            'label'       => __('City', 'woocommerce'),
            'options'     => array('' => __('Select a city', 'woocommerce')),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 70,
        );

        $fields['shipping']['shipping_city'] = array(
            'type'        => 'select',
            'label'       => __('City', 'woocommerce'),
            'options'     => array('' => __('Select a city', 'woocommerce')),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 70,
        );

        return $fields;
    }

    // Initialize necessary classes and functionalities
    new ICARRY_OrderCreated();
}

// Include the settings page file
require_once ICARRY_PLUGIN_DIR_PATH . 'inc/settings-page.php';

// Register uninstall hook
register_uninstall_hook(__FILE__, 'ICarryShippingForWooCommerce\ICARRY_Uninstall::uninstall');
