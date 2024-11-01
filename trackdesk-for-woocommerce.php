<?php
/**
 * Plugin Name: Trackdesk for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/trackdesk-for-woocommerce/
 * Description: Integrating the trackdesk tracking code into your WordPress website is simple and quick.
 * Author: Trackdesk
 * Author URI: https://www.trackdesk.com
 * Version: 1.2.1
 * Requires at least: 4.4
 * WC requires at least: 5.3
 * Requires PHP: 7.4
 * License: GPL2
 */

defined( 'ABSPATH' ) || exit;

const WC_TRACKDESK_PLUGIN_ROOT_FILE = __FILE__;

define( 'WC_TRACKDESK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC_TRACKDESK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-loader.php';

class WC_Trackdesk_Config {
	const PLUGIN_VERSION = '1.2.1';

	const MINIMUM_PHP_VERSION = '7.4.0';

	const MINIMUM_WP_VERSION = '4.4';

	const MINIMUM_WC_VERSION = '5.3';

	const PLUGIN_NAME = 'Trackdesk for WooCommerce';

	const TRANSLATE_DOMAIN = 'trackdesk-for-woocommerce';

	const DEFAULT_API_DOMAIN_ROOT = 'trackdesk.com';

	const DEFAULT_API_SSL_VERIFIED = true;

	const DEFAULT_TRACKING_SCRIPT_URL = '//cdn.trackdesk.com/tracking.js';

	const DEFAULT_ORDER_RECEIVED_CONVERSION_TYPE_CODE = 'sale';
}

WC_Trackdesk_Loader::instance();
