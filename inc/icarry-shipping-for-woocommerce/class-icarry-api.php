<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use ICarryShippingForWooCommerce\{ICARRY_Config, ICARRY_Logs};

class ICARRY_ICarryAPI {

	private function icarry_call( string $url, array $parameters = array(), array $headers = array() ) {
		$default_headers = array(
			'Content-Type' => 'application/json',
		);
	    $merged_headers = array_merge( $default_headers, $headers );

		$args = array(
			'body'        => wp_json_encode( $parameters ),
			'headers'     => $merged_headers,
			'sslverify'   => false,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			ICARRY_Logs::icarry_log( 'ICARRY_ICarryAPI-Error', $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			ICARRY_Logs::icarry_log( 'ICARRY_ICarryAPI-Error', "Request failed with code $response_code: $response_message" );
			return false;
		}

		$decoded_response = json_decode( $response_body, true );

		return is_array( $decoded_response ) ? $decoded_response : $response_body;
	}

	public function icarry_getToken( string $email, string $password ): array {
		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );
		$response = $this->icarry_call(
			$icarry_options['store_url'] . ICARRY_Config::GET_TOKEN_URL,
			array(
				'Email'    => $email,
				'Password' => $password,
			)
		);

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response['token'] ) && ! empty( $response['token'] ) ) {
			return array(
				'type'    => 'success',
				'message' => $response['token'],
			);
		}

		ICARRY_Logs::icarry_log( 'ICARRY_ICarryAPI-Error-icarry_getToken', json_encode( $response ) );

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}

	public function icarry_getRates( string $token, array $request ): array {
		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );
		$response = $this->icarry_call(
			$icarry_options['store_url'] . ICARRY_Config::GET_RATES_URL,
			$request,
			array(
				'Authorization' => 'Bearer ' . $token
			)
		);

		ICARRY_Logs::icarry_log(
			'icarry_getRates-Request',
			json_encode( $request )
		);

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response[0]['Name'] ) && ! empty( $response[0]['Name'] ) ) {
			return array(
				'type'    => 'success',
				'message' => $response,
			);
		}

		ICARRY_Logs::icarry_log( 'ICARRY_ICarryAPI-Error-icarry_getRates', json_encode( $response ) );

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}

	public function icarry_createOrder( string $token, array $request ): array {
		ICARRY_Logs::icarry_log(
			'icarry_createOrder-Request',
			json_encode( $request )
		);
		$icarry_options = get_option( 'woocommerce_icarry_shipping_for_woocommerce_settings' );
		$response = $this->icarry_call(
			$icarry_options['store_url'] . ICARRY_Config::CREATE_ORDER_URL,
			$request,
			array(
				'Authorization' => 'Bearer ' . $token
			)
		);

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response['TrackingNumber'] ) && ! empty( $response['TrackingNumber'] ) ) {
			return array(
				'type'    => 'success',
				'message' => $response,
			);
		}

		ICARRY_Logs::icarry_log( 'ICARRY_ICarryAPI-Error-icarry_createOrder', json_encode( $response ) );

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}
}