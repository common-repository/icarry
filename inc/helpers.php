<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


use ICarryShippingForWooCommerce;

function icarry_pr(...$data)
{
	if (!empty($data)) {
		foreach ($data as $key => $value) {
			echo '<pre data-key="' . esc_attr($key) . '" style="padding:1rem;margin:0;background-color:#333;color:#fff;max-height:90vh;overflow:auto;border-bottom:1px solid #666;">';
			echo esc_html(print_r($value, true));
			echo '</pre>';
		}
	}
}

function icarry_get_country_id_map() {
    return array(
	    'AF' => 1,
		'AX' => 2,
		'AL' => 3,
		'DZ' => 4,
		'AS' => 5,
		'AD' => 6,
		'AO' => 7,
		'AI' => 8,
		'AQ' => 9,
		'AG' => 10,
		'AR' => 11,
		'AM' => 12,
		'AW' => 13,
		'AU' => 14,
		'AT' => 15,
		'AZ' => 16,
		'BS' => 17,
		'BH' => 18,
		'BD' => 19,
		'BB' => 20,
		'BY' => 21,
		'BE' => 22,
		'BZ' => 23,
		'BJ' => 24,
		'BM' => 25,
		'BT' => 26,
		'BO' => 27,
		'BQ' => 28,
		'BA' => 29,
		'BW' => 30,
		'BV' => 31,
		'BR' => 32,
		'IO' => 33,
		'BN' => 34,
		'BG' => 35,
		'BF' => 36,
		'BI' => 37,
		'CV' => 38,
		'KH' => 39,
		'CM' => 40,
		'CA' => 41,
		'KY' => 42,
		'CF' => 43,
		'TD' => 44,
		'CL' => 45,
		'CN' => 46,
		'CX' => 47,
		'CC' => 48,
		'CO' => 49,
		'KM' => 50,
		'CG' => 51,
		'CD' => 52,
		'CK' => 53,
		'CR' => 54,
		'CI' => 55,
		'HR' => 56,
		'CU' => 57,
		'CW' => 58,
		'CY' => 59,
		'CZ' => 60,
		'DK' => 61,
		'DJ' => 62,
		'DM' => 63,
		'DO' => 64,
		'EC' => 65,
		'EG' => 66,
		'SV' => 67,
		'GQ' => 68,
		'ER' => 69,
		'EE' => 70,
		'SZ' => 71,
		'ET' => 72,
		'FK' => 73,
		'FO' => 74,
		'FJ' => 75,
		'FI' => 76,
		'FR' => 77,
		'GF' => 78,
		'PF' => 79,
		'TF' => 80,
		'GA' => 81,
		'GM' => 82,
		'GE' => 83,
		'DE' => 84,
		'GH' => 85,
		'GI' => 86,
		'GR' => 87,
		'GL' => 88,
		'GD' => 89,
		'GP' => 90,
		'GU' => 91,
		'GT' => 92,
		'GG' => 93,
		'GN' => 94,
		'GW' => 95,
		'GY' => 96,
		'HT' => 97,
		'HM' => 98,
		'VA' => 99,
		'HN' => 100,
		'HK' => 101,
		'HU' => 102,
		'IS' => 103,
		'IN' => 104,
		'ID' => 105,
		'IR' => 106,
		'IQ' => 107,
		'IE' => 108,
		'IM' => 109,
		'IL' => 110,
		'IT' => 111,
		'JM' => 112,
		'JP' => 113,
		'JE' => 114,
		'JO' => 115,
		'KZ' => 116,
		'KE' => 117,
		'KI' => 118,
		'KP' => 119,
		'KR' => 120,
		'KW' => 121,
		'KG' => 122,
		'LA' => 123,
		'LV' => 124,
		'LB' => 125,
		'LS' => 126,
		'LR' => 127,
		'LY' => 128,
		'LI' => 129,
		'LT' => 130,
		'LU' => 131,
		'MO' => 132,
		'MK' => 133,
		'MG' => 134,
		'MW' => 135,
		'MY' => 136,
		'MV' => 137,
		'ML' => 138,
		'MT' => 139,
		'MH' => 140,
		'MQ' => 141,
		'MR' => 142,
		'MU' => 143,
		'YT' => 144,
		'MX' => 145,
		'FM' => 146,
		'MD' => 147,
		'MC' => 148,
		'MN' => 149,
		'ME' => 150,
		'MS' => 151,
		'MA' => 152,
		'MZ' => 153,
		'MM' => 154,
		'NA' => 155,
		'NR' => 156,
		'NP' => 157,
		'NL' => 158,
		'NC' => 159,
		'NZ' => 160,
		'NI' => 161,
		'NE' => 162,
		'NG' => 163,
		'NU' => 164,
		'NF' => 165,
		'MP' => 166,
		'NO' => 167,
		'OM' => 168,
		'PK' => 169,
		'PW' => 170,
		'PS' => 171,
		'PA' => 172,
		'PG' => 173,
		'PY' => 174,
		'PE' => 175,
		'PH' => 176,
		'PN' => 177,
		'PL' => 178,
		'PT' => 179,
		'PR' => 180,
		'QA' => 181,
		'RE' => 182,
		'RO' => 183,
		'RU' => 184,
		'RW' => 185,
		'BL' => 186,
		'SH' => 187,
		'KN' => 188,
		'LC' => 189,
		'MF' => 190,
		'PM' => 191,
		'VC' => 192,
		'WS' => 193,
		'SP' => 194,
		'ST' => 195,
		'SA' => 196,
		'SN' => 197,
		'RS' => 198,
		'SC' => 199,
		'SL' => 200,
		'SG' => 201,
		'SX' => 202,
		'SK' => 203,
		'SI' => 204,
		'SB' => 205,
		'SO' => 206,
		'ZA' => 207,
		'GS' => 208,
		'SS' => 209,
		'ES' => 210,
		'LK' => 211,
		'SD' => 212,
		'SR' => 213,
		'SJ' => 214,
		'SE' => 215,
		'CH' => 216,
		'SY' => 217,
		'TW' => 218,
		'TJ' => 219,
		'TZ' => 220,
		'TH' => 221,
		'TL' => 222,
		'TG' => 223,
		'TK' => 224,
		'TO' => 225,
		'TT' => 226,
		'TN' => 227,
		'TR' => 228,
		'TM' => 229,
		'TC' => 230,
		'TV' => 231,
		'UG' => 232,
		'UA' => 233,
		'AE' => 234,
		'GB' => 235,
		'UM' => 236,
		'US' => 237,
		'UY' => 238,
		'UZ' => 239,
		'VU' => 240,
		'VE' => 241,
		'VN' => 242,
		'VG' => 243,
		'VI' => 244,
		'WF' => 245,
		'EH' => 246,
		'YE' => 247,
		'ZM' => 248,
		'ZW' => 249,
		'GR' => 250,
        // Add more mappings as needed
    );
}

function icarry_ajax_get_states_by_country() {
   	// Check the nonce field for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ICarryShippingForWooCommerce')) {
        wp_send_json(array(
			'type' => 'error',
			'message' => 'WordPress security error',
		));
        return;
    }
   	$iso_code = isset($_POST['countryIsoCode']) ? sanitize_text_field(wp_unslash($_POST['countryIsoCode'])) : '';
    $country_id_map = icarry_get_country_id_map();

    if (!array_key_exists($iso_code, $country_id_map)) {
        wp_send_json_error('Country code not recognized.');
        return;
    }

    $country_id = $country_id_map[$iso_code];
    $api_url = "https://lb.icarry.com/country/getstatesbycountryid?countryId={$country_id}&addSelectStateItem=true";

    $response = wp_remote_get($api_url);
    $response_code = wp_remote_retrieve_response_code($response);

    if (is_wp_error($response) || $response_code != 200) {
        wp_send_json_error('Error fetching states: ' . (is_wp_error($response) ? $response->get_error_message() : 'Response code: ' . $response_code));
        return;
    }

    $states = wp_remote_retrieve_body($response);
    wp_send_json_success(json_decode($states, true));
}

add_action('wp_ajax_icarry_get_states_by_country', '\ICarryShippingForWooCommerce\icarry_ajax_get_states_by_country');
add_action('wp_ajax_nopriv_icarry_get_states_by_country', '\ICarryShippingForWooCommerce\icarry_ajax_get_states_by_country');

function icarry_vd(...$data)
{
	if (!empty($data)) {
		foreach ($data as $key => $value) {
			echo '<pre data-key="' . esc_attr($key) . '" style="padding:10px;margin:0;background-color:#333;color:#fff;max-height:90vh;overflow:auto;border-bottom:1px solid #666;">';
			echo esc_html(var_export($value, true));
			echo '</pre>';
		}
	}
}