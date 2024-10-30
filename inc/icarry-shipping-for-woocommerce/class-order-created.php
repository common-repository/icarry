<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use ICarryShippingForWooCommerce\{ICARRY_Config, ICARRY_Logs, ICARRY_Helpers};

class ICARRY_OrderCreated {

	public function __construct() {
		add_action( 'woocommerce_new_order', array( $this, 'icarry_CreateOrderRequest' ), 10, 2 );
	}

	private function icarry_getShippingField( array $order_base_data, string $field ): string {
		if ( isset( $order_base_data['shipping'][ $field ] ) and ! empty( $order_base_data['shipping'][ $field ] ) ) {
			return $order_base_data['shipping'][ $field ];
		}
		if ( isset( $order_base_data['billing'][ $field ] ) and ! empty( $order_base_data['billing'][ $field ] ) ) {
			return $order_base_data['billing'][ $field ];
		}
		return '';
	}

	private function icarry_getParcelDimensionsList( $order_items ): array {
		//$logger = wc_get_logger();
		//$logger->info("To log attribute name : $attributes");
		$parcels = [];

		if ( isset( $order_items ) and ! empty( $order_items ) ) {
			foreach ( $order_items as $product_item ) {
				$parcel_info = array(
					'quantity' => 0,
					"weight"   => 0,
					"length"   => 0,
					"width"    => 0,
					"height"   => 0,
					"sku"   => null,
				);
				//Assign Info
				$product    = $product_item->get_product();
				$quantity = (int) $product_item->get_quantity();
				$width  = (float) $product->get_width();
				$length  = (float) $product->get_length();
				$height  = (float) $product->get_height();
				
				//Validate values
				$width   = empty( $width ) || ! is_numeric( $width ) ? 1 :  $width;
				$length  = empty( $length ) || ! is_numeric( $length ) ? 1 : $length;
				$height  = empty( $height ) || ! is_numeric( $height ) ? 1 : $height;
				
				//Convert to cm
				$width  = ICARRY_Helpers::icarry_convertDimensionToCm( $width, get_option( 'woocommerce_dimension_unit' ));
				$length = ICARRY_Helpers::icarry_convertDimensionToCm( $length, get_option( 'woocommerce_dimension_unit' ));
				$height = ICARRY_Helpers::icarry_convertDimensionToCm( $height, get_option( 'woocommerce_dimension_unit' ));

				//Get weight
				$weight = (float) $product->get_weight();
				$weight = ICARRY_Helpers::icarry_convertWeight(get_option( 'woocommerce_weight_unit' ), ICARRY_Config::WEIGHT_UNIT, $weight);
				
				// Assign and push to array
				$parcel_info['weight'] = $weight * $quantity;
				$parcel_info['quantity'] = $quantity;
				$parcel_info['width']  = $width;
				$parcel_info['length']  = $length;
				$parcel_info['height']  = $height;
				$parcel_info['sku']  = $product->get_sku();

				array_push($parcels, $parcel_info);
			}
		}
		
		return $parcels;
	}

	private function icarry_getTotalProductsPrice( array $order_items ) {
		$total_product_price = 0;

		foreach ($order_items as $order_item) {
			// Get the product price for the current item
			$product_price = $order_item->get_total(); // This may vary depending on your WooCommerce setup

			// Add the product price to the total
			$total_product_price += $product_price;
		}
		return $total_product_price;
	}


	private function icarry_getProductQuantity( array $order_items ) {
		$quantity = 0;

		foreach ($order_items as $order_item) {
			// Get the product price for the current item
			$product_quantity = $order_item->get_quantity();

			// Add the product price to the total
			$quantity += $product_quantity;
		}
		return $quantity;
	}

	private function icarry_getProductsDescription( $order ) {
		$order_items = $order->get_items();
		$description = "";
		foreach ($order_items as $order_item) {
			$product_quantity = $order_item->get_quantity();
			$product_name = $order_item->get_name();

			$description .= "$product_quantity X $product_name \n";
		}

		return $description;
	}

	private function icarry_getProcessOrder( $order ): bool {

		$total_shipping = $order->get_total_shipping();

		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );

		$process_order = ( isset( $icarry_options['process_order'] ) && ( $icarry_options['process_order'] === 'yes' ) ? true : false );

		if ( $total_shipping == 0 ) {
			$process_order = false;
		}

		return $process_order;
	}

	
	private function icarry_getSystemShipmentProvider( $order ): string {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = reset( $shipping_methods );
		return $shipping_method->get_meta( 'SystemShipmentProvider' );
	}

	private function icarry_getMethodName( $order ): string {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = reset( $shipping_methods );
		return $shipping_method->get_meta( 'MethodName' );
	}

	private function icarry_getMethodDescription( $order ): string {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = reset( $shipping_methods );
		return $shipping_method->get_meta( 'MethodDescription' );
	}

	private function icarry_getShippingCountry( $order_base_data ): string {
		$shipping_country_code = $this->icarry_getShippingField( $order_base_data, 'country' );
		$countries             = WC()->countries->countries;
		if ( isset( $countries[ $shipping_country_code ] ) and ! empty( $countries[ $shipping_country_code ] ) ) {
			return $countries[ $shipping_country_code ];
		}
		return '';
	}

	private function icarry_orderDataToICarryRequest( $order ) {
		$orderId = $order->get_id();
		$order_items     = $order->get_items();
		$order_base_data = $order->get_base_data();
		$codValue = ( WC()->session->get('chosen_payment_method') === 'cod' ) ? $order->get_total() : 0;
		$codCurrency = ( WC()->session->get('chosen_payment_method') === 'cod' ) ? get_woocommerce_currency() : "";
		
		$parcelDescription = $this->icarry_getProductsDescription( $order );
		$parcelValue = $this->icarry_getTotalProductsPrice( $order_items );
		//$parcelQuantity = $this->icarry_getProductQuantity( $order_items );

		$site_url = site_url(); // Get the full site URL
		$site_domain = parse_url($site_url, PHP_URL_HOST); // Extract the domain

		$parcel_dimensions_list = $this->icarry_getParcelDimensionsList( $order_items );
		$actualWeight = 0;
		foreach ($parcel_dimensions_list as $parcel) {
			if (isset($parcel["weight"])) {
				$actualWeight += $parcel["weight"];
			}
		}
		$i_carry_request = array(
			'ExternalId'        => $site_domain . "__$orderId",
			'ProcessOrder'           => false,
			'dropOffAddress'         => array(
				'FirstName'     => $this->icarry_getShippingField( $order_base_data, 'first_name' ),
				'LastName'      => $this->icarry_getShippingField( $order_base_data, 'last_name' ),
				'Email'         => $this->icarry_getShippingField( $order_base_data, 'email' ),
				'PhoneNumber'   => $this->icarry_getShippingField( $order_base_data, 'phone' ),
				'Country'       => $this->icarry_getShippingCountry( $order_base_data ),
				'City'          => $this->icarry_getShippingField( $order_base_data, ICARRY_Config::DROP_OFF_STATE_CITY ),
				'Address1'      => $this->icarry_getShippingField( $order_base_data, 'address_1' ),
				'Address2'      => $this->icarry_getShippingField( $order_base_data, 'address_2' ),
				'ZipPostalCode' => $this->icarry_getShippingField( $order_base_data, 'postcode' ),
			),
			'CODAmount'              => $codValue,
			'COdCurrency'            => $codCurrency,
			'ActualWeight'           => $actualWeight,
			'PackageType'            => 'Parcel',
			'Length'                 => 0,
			'Width'                  => 0,
			'Height'                 => 0,
			'Notes'                  => $order->get_customer_note(),
			'SystemShipmentProvider' => $this->icarry_getSystemShipmentProvider( $order ),
			'Price'                  => $order->get_total_shipping(),
			'MethodName'			 => $this->icarry_getMethodName($order)  ,
			'MethodDescription'      => $this->icarry_getMethodDescription($order),
			"ParcelPackageValue"	 => $parcelValue,
			"ParcelPackageCurrency"  => get_woocommerce_currency(),
			"ParcelDescription"		 => $parcelDescription,
			"ParcelQuantity"		 => 0,
			'ParcelDimensionsList'   => $parcel_dimensions_list

		);

		ICARRY_Logs::icarry_log(
			'icarry_orderDataToICarryRequest',
			json_encode( $i_carry_request )
		);

		return $i_carry_request;
	}


	private function icarry_addICarryOrderInformationToWooCommerceOrder( $order_id, $icarry_create_order_response ): void {
		update_post_meta( $order_id, 'icarry_order', json_encode( $icarry_create_order_response ) );
	}

	public function icarry_CreateOrderRequest( $order_id, $order ) {
		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );

		$ICARRY_ICarryAPI = new ICARRY_ICarryAPI();

		$response = $ICARRY_ICarryAPI->icarry_getToken( $icarry_options['email'], $icarry_options['password'] );

		if ( $response['type'] == 'success' ) {

			$response = $ICARRY_ICarryAPI->icarry_createOrder( $response['message'], $this->icarry_orderDataToICarryRequest( $order ) );

			if ( $response['type'] == 'success' ) {

				if ( isset( $response['message'] ) and ! empty( $response['message'] ) ) {
					$this->icarry_addICarryOrderInformationToWooCommerceOrder( $order_id, $response['message'] );
				}

				ICARRY_Logs::icarry_log(
					'iCarryCreateOrderRequest-Success',
					json_encode( $response )
				);
				return true;
			}

			ICARRY_Logs::icarry_log(
				'iCarryCreateOrderRequest-Error',
				json_encode( $response )
			);

			return false;
		}

		return false;
	}
}
