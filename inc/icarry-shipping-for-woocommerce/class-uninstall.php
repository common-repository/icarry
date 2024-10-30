<?php
namespace ICarryShippingForWooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ICARRY_Uninstall {

	public static function icarry_removeLogs() {
	    // Use the uploads directory for logs
        $upload_dir = wp_upload_dir();
        $logs_directory = $upload_dir['basedir'] . '/' . ICARRY_Config::LOGS_FOLDER_NAME;

		// Check if directory exists and is writable
		if ( is_dir( $logs_directory ) && is_writable( $logs_directory ) ) {
			// Use WordPress filesystem API for secure file operations
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				$wp_filesystem = WP_Filesystem();
			}

			// Attempt to delete directory and its contents
			if ( $wp_filesystem ) {
				$wp_filesystem->rmdir( $logs_directory, true );
			} else {
				error_log( 'Unable to initialize WP_Filesystem.' );
			}
		} else {
			error_log( 'ICARRY_Logs directory does not exist or is not writable.' );
		}
	}

	public static function uninstall() {
		self::icarry_removeLogs();
	}
}
