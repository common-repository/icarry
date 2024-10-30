<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ICARRY_Config
{
	const VERSION          = '2.0';
	const DEVELOPMENT      = true;
	const GET_TOKEN_URL = 'api-frontend/Authenticate/GetTokenForCustomerApi';
	const GET_RATES_URL = 'api-frontend/SmartwareShipment/EstimateRatesByCOD';
	const CREATE_ORDER_URL = 'api-frontend/SmartwareShipment/CreateOrder';
	const DIMENSION_UNIT = 'cm';
	const WEIGHT_UNIT = 'kg';
	const LOGS_FOLDER_NAME = 'icarry-shipping-for-woocommerce';
	const DROP_OFF_STATE_CITY = 'city';
	const VW = 5000;
	const PLUGIN_DIR_PATH = ICARRY_PLUGIN_DIR_PATH;
    const PLUGIN_DIR_URL = ICARRY_PLUGIN_DIR_URL;
}
