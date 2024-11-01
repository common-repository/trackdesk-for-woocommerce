<?php

class WC_Trackdesk_Settings {
	const WP_OPTION_NAME_GENERAL = 'wc_trackdesk_general';
	const WP_OPTION_NAME_TENANTS = 'wc_trackdesk_tenants';

	const GENERAL_SETTINGS_KEY_API_DOMAIN_ROOT = 'api_domain_root';
	const GENERAL_SETTINGS_KEY_API_REQUEST_COOKIE = 'api_request_cookie';
	const GENERAL_SETTINGS_KEY_API_SSL_VERIFIED = 'api_ssl_verified';
	const GENERAL_SETTINGS_KEY_TRACKING_SCRIPT_URL = 'tracking_script_url';

	const TENANT_SETTING_KEY_API_KEY = 'api_key';
	const TENANT_SETTING_KEY_CONVERSION_ON_SUBSCRIPTION_RENEWAL = 'conversion_on_subscription_renewal';
	const TENANT_SETTING_KEY_CUSTOMER_ID_FILLED = 'customer_id_filled';
	const TENANT_SETTING_KEY_ENABLED = 'enabled';
	const TENANT_SETTING_KEY_ID = 'id';
	const TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE = 'order_received_conversion_type_code';
	const TENANT_SETTING_KEY_ORDER_STATUS_TO_CONVERSION_STATUS_MAP = 'order_status_to_conversion_status_map';
	const TENANT_SETTING_KEY_REVENUE_ORIGIN_ID = 'revenue_origin_id';

	public static function get_general(): array {
		return get_option( self::WP_OPTION_NAME_GENERAL, [
			self::GENERAL_SETTINGS_KEY_API_DOMAIN_ROOT     => WC_Trackdesk_Config::DEFAULT_API_DOMAIN_ROOT,
			self::GENERAL_SETTINGS_KEY_API_REQUEST_COOKIE  => '',
			self::GENERAL_SETTINGS_KEY_API_SSL_VERIFIED    => WC_Trackdesk_Config::DEFAULT_API_SSL_VERIFIED,
			self::GENERAL_SETTINGS_KEY_TRACKING_SCRIPT_URL => WC_Trackdesk_Config::DEFAULT_TRACKING_SCRIPT_URL
		] );
	}

	public static function update_general( array $data ): void {
		update_option( self::WP_OPTION_NAME_GENERAL, $data );
	}

	public static function new_general(
		string $api_domain_root,
		string $api_request_cookie,
		bool $api_ssl_verified,
		string $tracking_script_url
	): array {
		return [
			self::GENERAL_SETTINGS_KEY_API_DOMAIN_ROOT     => $api_domain_root,
			self::GENERAL_SETTINGS_KEY_API_REQUEST_COOKIE  => $api_request_cookie,
			self::GENERAL_SETTINGS_KEY_API_SSL_VERIFIED    => $api_ssl_verified,
			self::GENERAL_SETTINGS_KEY_TRACKING_SCRIPT_URL => $tracking_script_url,
		];
	}

	private static function get_tenants(): array {
		return get_option( self::WP_OPTION_NAME_TENANTS, [] );
	}

	public static function update_tenants( array $data ): void {
		update_option( self::WP_OPTION_NAME_TENANTS, $data );
	}

	public static function get_tenant(): array {
		return self::get_tenants()[0] ?? self::new_empty_tenant();
	}

	public static function update_tenant( array $data ): void {
		self::update_tenants( [ $data ] );
	}

	public static function new_tenant(
		string $id,
		string $api_key,
		string $sale_conversion_type_code,
		string $revenue_origin_id,
		array $order_status_to_conversion_status_map,
		bool $customer_id_filled,
		bool $conversion_on_subscription_renewal,
		bool $enabled
	): array {
		return [
			self::TENANT_SETTING_KEY_ID                                    => $id,
			self::TENANT_SETTING_KEY_API_KEY                               => $api_key,
			self::TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE   => $sale_conversion_type_code,
			self::TENANT_SETTING_KEY_REVENUE_ORIGIN_ID                     => $revenue_origin_id,
			self::TENANT_SETTING_KEY_ORDER_STATUS_TO_CONVERSION_STATUS_MAP => $order_status_to_conversion_status_map,
			self::TENANT_SETTING_KEY_CUSTOMER_ID_FILLED                    => $customer_id_filled,
			self::TENANT_SETTING_KEY_CONVERSION_ON_SUBSCRIPTION_RENEWAL    => $conversion_on_subscription_renewal,
			self::TENANT_SETTING_KEY_ENABLED                               => $enabled,
		];
	}

	public static function new_empty_tenant(): array {
		return self::new_tenant(
			'',
			'',
			'',
			'',
			self::get_default_order_status_to_conversion_status_map(),
			false,
			false,
			false,
		);
	}

	public static function get_tenant_customer_id_filled( array $tenant ): bool {
		return WC_Trackdesk_Utils::get_array_value(
			$tenant,
			WC_Trackdesk_Settings::TENANT_SETTING_KEY_CUSTOMER_ID_FILLED,
			false
		);
	}

	public static function get_tenant_conversion_on_subscription_renewal( array $tenant ): bool {
		return WC_Trackdesk_Utils::get_array_value(
			$tenant,
			WC_Trackdesk_Settings::TENANT_SETTING_KEY_CONVERSION_ON_SUBSCRIPTION_RENEWAL,
			false
		);
	}

	public static function get_tenant_order_status_to_conversion_status_map( array $tenant ): array {
		return WC_Trackdesk_Utils::get_array_value(
			$tenant,
			WC_Trackdesk_Settings::TENANT_SETTING_KEY_ORDER_STATUS_TO_CONVERSION_STATUS_MAP,
			self::get_default_order_status_to_conversion_status_map()
		);
	}

	public static function get_default_order_status_to_conversion_status_map(): array {
		return [
			'pending'    => WC_Trackdesk_Client::CONVERSION_STATUS_PENDING,
			'processing' => WC_Trackdesk_Client::CONVERSION_STATUS_PENDING,
			'on-hold'    => WC_Trackdesk_Client::CONVERSION_STATUS_ON_HOLD,
			'completed'  => WC_Trackdesk_Client::CONVERSION_STATUS_APPROVED,
			'cancelled'  => WC_Trackdesk_Client::CONVERSION_STATUS_REJECTED,
			'failed'     => WC_Trackdesk_Client::CONVERSION_STATUS_REJECTED,
			'refunded'   => WC_Trackdesk_Client::CONVERSION_STATUS_REFUNDED,
		];
	}
}
