<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use ICarryShippingForWooCommerce\{ICARRY_Config};

class ICARRY_Logs {

	public static function icarry_log( string $file_name, string $data ) {

		if ( ICARRY_Config::DEVELOPMENT !== true ) {
            return false;
        }

        // Use the uploads directory for logs
        $upload_dir = wp_upload_dir();
        $directory = $upload_dir['basedir'] . '/' . ICARRY_Config::LOGS_FOLDER_NAME . '/';

        if ( ! is_dir( $directory ) ) {
            wp_mkdir_p( $directory );
        }

        ob_start();

        echo PHP_EOL;

        print_r( $data );

        $html = ob_get_clean();

        file_put_contents( $directory . $file_name . '-[' . date( 'Y-m-d' ) . '].log', $html, FILE_APPEND );
    }
}
