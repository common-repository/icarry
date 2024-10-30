<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ICARRY_Dependencies {

	public static function icarry_ifWooCommerceIsActive() {
		return ( class_exists( 'WooCommerce' ) && class_exists( 'WC_Shipping_Method' ) );
	}
	
}
