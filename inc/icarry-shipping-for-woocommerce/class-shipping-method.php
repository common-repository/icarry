<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use ICarryShippingForWooCommerce\{ICARRY_Config, ICARRY_ICarryAPI, ICARRY_Logs, ICARRY_Helpers};

class ICARRY_ShippingMethod extends \WC_Shipping_Method {


	public function __construct( int $instance_id = 0 ) {
		$this->instance_id = absint( $instance_id );

		$this->id                 = 'icarry_shipping_for_woocommerce';
		$this->method_title       = __( 'iCarry Configuration', 'icarry-shipping-for-woocommerce' );
		$this->title              = $this->method_title;
		$this->method_description = __( 'Get iCarry Courier rates for each order based on your shipping and customer pin code. Using this app you can display iCarry courier serviceability and Estimated Date of Delivery (EDD) on your Product and Checkout page. By enabling this iCarry will update your Products and Checkout Page.', 'icarry-shipping-for-woocommerce' );
		$this->option_key         = $this->id . '_shipping_method';
		$this->enabled            = 'yes';
		$this->icarry_init();

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'icarry_shipping_method_discount' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	private function icarry_init() {
		$this->icarry_init_form_fields();
		$this->init_settings();

		$this->title = $this->method_title;
	}

	public function icarry_init_form_fields() {
		$this->form_fields = array(
			'store_url'   => array(
				'title'   => __( 'Store', 'icarry-shipping-for-woocommerce' ),
				'type'    => 'select',
				'options' => array(
					'https://lb.icarry.com/' => __( 'Lebanon store', 'icarry-shipping-for-woocommerce' ),
					'https://uae.icarry.com/' => __( 'United Arab Emirates Store', 'icarry-shipping-for-woocommerce' ),
					'https://kw.icarry.com/' => __( 'Kuwait Store', 'icarry-shipping-for-woocommerce' ),
				),
			),
			'email'         => array(
				'title' => __( 'Email', 'icarry-shipping-for-woocommerce' ),
				'type'  => 'email',
			),
			'password'      => array(
				'title' => __( 'Password', 'icarry-shipping-for-woocommerce' ),
				'type'  => 'password',
			),
			'google_map_key' => array(
				'title'   => __( 'Google Maps Api Key', 'icarry-shipping-for-woocommerce' ),
				'type'    => 'text',
				'label'   => __( 'Google Maps Api Key', 'icarry-shipping-for-woocommerce' )
			),
			'is_rate_provider' => array(
				'title'   => __( 'Show Rates', 'icarry-shipping-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Show rates in the checkout page', 'icarry-shipping-for-woocommerce' ),
				'default' => 'no',
			),
		);
	}

	private function icarry_geoCode( string $street, string $city ): array {
		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );
		$google_api_response = wp_remote_get(
			'https://maps.googleapis.com/maps/api/geoCode/json?' . http_build_query(
				array(
					'address' => str_replace( ' ', '+', $street ) . ',' . str_replace( ' ', '+', $city ),
					'key'     => $icarry_options['google_map_key'],
				)
			)
		);

		$results             = json_decode( $google_api_response['body'] );
		$results             = (array) $results;
		$location_all_fields = ( isset( $results['results'][0] ) ) ? (array) $results['results'][0] : array();
		$location_geometry   = ( isset( $location_all_fields['geometry'] ) ) ? (array) $location_all_fields['geometry'] : array();
		$location_lat_long   = ( isset( $location_geometry['location'] ) ) ? (array) $location_geometry['location'] : array();

		$latitude  = '';
		$longitude = '';

		if ( isset($results['status']) && $results['status'] == 'OK' ) {
			$latitude  = isset( $location_lat_long['lat'] ) ? $location_lat_long['lat'] : '';
			$longitude = isset( $location_lat_long['lng'] ) ? $location_lat_long['lng'] : '';
		}

		return array(
			'latitude'  => $latitude,
			'longitude' => $longitude,
		);
	}

	private function icarry_getParcelDimensionsList( $package = array() ): array {
		//$logger = wc_get_logger();
		//$logger->info("To log attribute name : $attributes");
		$parcels = [];

		if ( isset( $package['contents'] ) and ! empty( $package['contents'] ) ) {
			foreach ( $package['contents'] as $product ) {
				$parcel_info = array(
					'quantity' => 0,
					"weight"   => 0,
					"length"   => 0,
					"width"    => 0,
					"height"   => 0,
				);
				//Assign Info
				$quantity = (int) $product['quantity'];
				$width  = (float) $product['data']->get_width();
				$length  = (float) $product['data']->get_length();
				$height  = (float) $product['data']->get_height();
				
				//Validate values
				$width   = empty( $width ) || ! is_numeric( $width ) ? 1 :  $width;
				$length  = empty( $length ) || ! is_numeric( $length ) ? 1 : $length;
				$height  = empty( $height ) || ! is_numeric( $height ) ? 1 : $height;
				
				//Convert to cm
				$width  = ICARRY_Helpers::icarry_convertDimensionToCm( $width, get_option( 'woocommerce_dimension_unit' ));
				$length = ICARRY_Helpers::icarry_convertDimensionToCm( $length, get_option( 'woocommerce_dimension_unit' ));
				$height = ICARRY_Helpers::icarry_convertDimensionToCm( $height, get_option( 'woocommerce_dimension_unit' ));

				//Get weight
				$weight = (float) $product['data']->get_weight();
				$weight = ICARRY_Helpers::icarry_convertWeight(get_option( 'woocommerce_weight_unit' ), ICARRY_Config::WEIGHT_UNIT, $weight );
				
				// Assign and push to array
				$parcel_info['weight'] = $weight * $quantity;
				$parcel_info['quantity'] = $quantity;
				$parcel_info['width']  = $width;
				$parcel_info['length']  = $length;
				$parcel_info['height']  = $height;

				array_push($parcels, $parcel_info);
			}
		}

		return $parcels;
	}


	private function icarry_makeIdFromName( $string ) {
		$string = mb_convert_case( trim( $string ), MB_CASE_LOWER, 'UTF-8' );
		$string = str_replace( ' ', '-', $string );
		$string = str_replace( '.', '-', $string );
		$string = str_replace( '_', '-', $string );
		return mb_convert_case( trim( $string ), MB_CASE_LOWER, 'UTF-8' );
	}

	private function icarry_getIsRateProvider(): bool {

		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );

		ICARRY_Logs::icarry_log(
			'icarry_getRates-Request [option] is rate provider',
			$icarry_options['is_rate_provider']
		);
		$is_rate_provider = ( isset( $icarry_options['is_rate_provider'] ) && ( $icarry_options['is_rate_provider'] === 'yes' ) ? true : false );

		return $is_rate_provider ?? false;
	}

	public function icarry_getShippingRates( array $icarry_request ) {
		$shipping_rates = array();

		
		$is_rate_provider = $this->icarry_getIsRateProvider();

		if (!$is_rate_provider) {
			return $shipping_rates;
		}

		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );

		$ICARRY_ICarryAPI = new ICARRY_ICarryAPI();

		$response = $ICARRY_ICarryAPI->icarry_getToken( $icarry_options['email'], $icarry_options['password'] );

		if ( $response['type'] == 'success' ) {

			$shipping_rates_hash = hash( 'md5', wp_json_encode( $icarry_request ) );
			$previous_shipping_rates_hash  = WC()->session->get( 'icswc_shipping_rates_hash' );
			$icswc_shipping_rates_response = WC()->session->get( 'icswc_shipping_rates_response' );

			if ( $shipping_rates_hash === $previous_shipping_rates_hash && isset( $icswc_shipping_rates_response ) && ! empty( $icswc_shipping_rates_response ) ) {
				$response = $icswc_shipping_rates_response;
			} else {
				WC()->session->set( 'icswc_shipping_rates_hash', $shipping_rates_hash );

				$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );

				$response = $ICARRY_ICarryAPI->icarry_getRates( $response['message'], $icarry_request );
				ICARRY_Logs::icarry_log( 'icarry_getRates', json_encode( $response ) );
				WC()->session->set( 'icswc_shipping_rates_response', $response );

			}

			if ( $response['type'] == 'success' and is_array( $response['message'] ) and ! empty( $response['message'] ) ) {
				foreach ( $response['message'] as $key => $icarry_shipping_option ) { 
					$id = $icarry_shipping_option['MethodId']; 
					$label = $icarry_shipping_option['Name'] . ' - ' . $icarry_shipping_option['MethodName'];
					if (empty($id)) {
					  $id = $icarry_shipping_option['Name'];
					  $label = $icarry_shipping_option['Name'];
					}
					$shipping_rates[ $key ] = array(
						'id'             => $this->icarry_makeIdFromName( $id ),
						'label'        =>  $label,
						'cost'           => (float) trim(preg_replace("/[^0-9.]/", '', $icarry_shipping_option['Price'])),
						'taxes'          => '',
						'calc_tax'       => 'per_order',
						'meta_data'      => array(
							'SystemShipmentProvider' => $icarry_shipping_option['CarrierModel']['SystemName'],
							'MethodName' => $icarry_shipping_option['MethodId'], 
							'MethodDescription' => $icarry_shipping_option['Description']
						),
						'package'        => false,
						'price_decimals' => wc_get_price_decimals(),
					);
				}
			}
		}

		return $shipping_rates;
	}


	private function icarry_getDropOffAddress( $package = array() ): array {
		$drop_of_address = array(
			'address'  => ( isset( $package['destination']['address'] ) ? $package['destination']['address'] : '' ),
			'city'     => ( isset( $package['destination']['city'] ) ? $package['destination']['city'] : '' ),
			'state'     => ( isset( $package['destination']['state'] ) ? $package['destination']['state'] : '' ),
			'postcode' => ( isset( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : '' ),
			'country'  => ( isset( $package['destination']['country'] ) ? $package['destination']['country'] : '' ),
		);

		$drop_of_hash = hash( 'md5', wp_json_encode( $drop_of_address ) );

		$previous_drop_of_hash = WC()->session->get( 'icswc_drop_of_hash' );

		$icswc_drop_of_coordinates = WC()->session->get( 'icswc_drop_of_coordinates' );

		if ( $drop_of_hash === $previous_drop_of_hash && isset( $icswc_drop_of_coordinates ) && ! empty( $icswc_drop_of_coordinates ) ) {
			$drop_of_address['coordinates'] = $icswc_drop_of_coordinates;
		} else {
			WC()->session->set( 'icswc_drop_of_hash', $drop_of_hash );
			$drop_of_address['coordinates'] = $this->icarry_geoCode( $drop_of_address['address'], $drop_of_address['city'] );

			ICARRY_Logs::icarry_log(
				'icarry_getDropOffAddress-icarry_geoCode',
				json_encode( [$drop_of_address, $drop_of_address['coordinates']] )
			);

			WC()->session->set( 'icswc_drop_of_coordinates', $drop_of_address['coordinates'] );
		}

		return $drop_of_address;
	}

	private function icarry_getPickUpAddress( $package = array() ): array {
		$pick_up_address = array(
			'address'  => get_option( 'woocommerce_store_address' ),
			'city'     => get_option( 'woocommerce_store_city' ),
			'postcode' => get_option( 'woocommerce_store_postcode' ),
			'country'  => get_option( 'woocommerce_default_country' ),
		);

		$pick_up_hash = hash( 'md5', wp_json_encode( $pick_up_address ) );

		$previous_pick_up_hash = WC()->session->get( 'icswc_pick_up_hash' );

		$icswc_pic_kup_coordinates = WC()->session->get( 'icswc_pic_kup_coordinates' );

		if ( $pick_up_hash === $previous_pick_up_hash && isset( $icswc_pic_kup_coordinates ) && ! empty( $icswc_pic_kup_coordinates ) ) {
			$pick_up_address['coordinates'] = $icswc_pic_kup_coordinates;
		} else {
			WC()->session->set( 'icswc_pick_up_hash', $pick_up_hash );
			$pick_up_address['coordinates'] = $this->icarry_geoCode( $pick_up_address['address'], $pick_up_address['city'] );
			WC()->session->set( 'icswc_pic_kup_coordinates', $pick_up_address['coordinates'] );
		}

		return $pick_up_address;
	}


    public function calculate_shipping( $package = array() ) {
        $this->icarry_calculate_shipping($package);
    }
    
	public function icarry_calculate_shipping( $package = array() ) {
		// Ensure this function runs only on the checkout page
        if ( ! is_checkout() ) {
            return;
        }
		if ( is_admin() && defined( 'DOING_AJAX' ) ) {
			return false;
		}

		$pick_up_address = $this->icarry_getPickUpAddress( $package );

		$drop_of_address = $this->icarry_getDropOffAddress( $package );

		//$order_total_params = $this->getOrderTotalParameters( $package );
		$parcel_dimensions_list = $this->icarry_getParcelDimensionsList( $package );

		$codAmount = isset( $package['cart_subtotal'] ) ? $package['cart_subtotal'] : 0 ;

		$actualWeight = 0;
		foreach ($parcel_dimensions_list as $parcel) {
			if (isset($parcel["weight"])) {
				$actualWeight += $parcel["weight"];
			}
		}


		$shipping_rates = $this->icarry_getShippingRates( array(
			'incluedShippingCost' => ( WC()->session->get('chosen_payment_method') === 'cod' ) ? true : false,
			'CODAmount'           => ( WC()->session->get('chosen_payment_method') === 'cod' ) ? $codAmount : null,
			'COdCurrency'         => ( WC()->session->get('chosen_payment_method') === 'cod' ) ? get_woocommerce_currency() : null ,
			'DropOffLocation'     => $drop_of_address[ICARRY_Config::DROP_OFF_STATE_CITY],
			'ToLongitude'         => $drop_of_address['coordinates']['longitude'],
			'ToLatitude'          => $drop_of_address['coordinates']['latitude'],
			'ActualWeight'        => $actualWeight,
			'Dimensions'          => array(
				'Length' => 0,
				'Width'  => 0,
				'Height' => 0,
				'Unit'   => ICARRY_Config::DIMENSION_UNIT,
			),
			'PackageType'         => 'Parcel',
			'DropAddress'         => array(
				'CountryCode' => $drop_of_address['country'],
				'City'        => $drop_of_address[ICARRY_Config::DROP_OFF_STATE_CITY],
				'ZipPostCode'      => $drop_of_address['postcode'],
			),
			'IsVendor'            => true,
			'ParcelDimensionsList' => $parcel_dimensions_list
		) );

		if ( isset( $shipping_rates ) and ! empty( $shipping_rates ) ) {
			foreach ( $shipping_rates as $shipping_rate ) {
				$this->add_rate( $shipping_rate );
			}
		}
	}



	public function icarry_shipping_method_discount( $cart_object ) {
		// Ensure this function runs only on the checkout page
        if ( ! is_checkout() ) {
            return;
        }

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return false;
		}
	}
}
