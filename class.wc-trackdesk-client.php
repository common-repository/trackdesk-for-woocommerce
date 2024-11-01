<?php

class WC_Trackdesk_Client {
	const CONVERSION_STATUS_APPROVED = 'CONVERSION_STATUS_APPROVED';
	const CONVERSION_STATUS_ON_HOLD = 'CONVERSION_STATUS_ON_HOLD';
	const CONVERSION_STATUS_PENDING = 'CONVERSION_STATUS_PENDING';
	const CONVERSION_STATUS_REFUNDED = 'CONVERSION_STATUS_REFUNDED';
	const CONVERSION_STATUS_REJECTED = 'CONVERSION_STATUS_REJECTED';

	private string $api_domain_root;

	private string $api_request_cookie;

	private bool $api_ssl_verified;

	private static $instance;

	protected function __construct() {
		$general = WC_Trackdesk_Settings::get_general();

		$this->api_domain_root    = $general[ WC_Trackdesk_Settings::GENERAL_SETTINGS_KEY_API_DOMAIN_ROOT ];
		$this->api_request_cookie = $general[ WC_Trackdesk_Settings::GENERAL_SETTINGS_KEY_API_REQUEST_COOKIE ];
		$this->api_ssl_verified   = $general[ WC_Trackdesk_Settings::GENERAL_SETTINGS_KEY_API_SSL_VERIFIED ];
	}

	/***
	 * @param   string  $tenant_id
	 * @param   string  $api_key
	 * @param   string  $body
	 *
	 * @return array|WP_Error
	 */
	public function create_conversion_by_external_cid(
		string $tenant_id,
		string $api_key,
		string $body
	) {
		$api_domain = $this->api_domain( $tenant_id );
		$url        = "https://{$api_domain}/tracking/conversion/v1";
		$args       = [
			'method'    => 'POST',
			'sslverify' => $this->api_ssl_verified,
			'headers'   => [
				'content-type' => 'application/json',
				'x-api-key'    => $api_key,
			],
			'body'      => $body,
		];

		if ( $this->api_request_cookie !== '' ) {
			$args['cookies'] = [
				new WP_Http_Cookie( $this->api_request_cookie ),
			];
		}

		return wp_remote_request( $url, $args );
	}

	public function prepare_body_create_conversion_by_external_cid(
		string $external_cid,
		string $revenue_origin_id,
		string $conversion_type_code,
		string $status,
		string $amount,
		string $external_id,
		?string $customer_id
	): string {
		return wp_json_encode( [
			'external_cid'         => $external_cid,
			'revenue_origin_id'    => $revenue_origin_id,
			'conversion_type_code' => $conversion_type_code,
			'status'               => $status,
			'amount'               => [
				"value" => $amount
			],
			'external_id'          => $external_id,
			"customer_id"          => $customer_id
		] );
	}

	/***
	 * @param   string  $tenant_id
	 * @param   string  $api_key
	 * @param   string  $body
	 *
	 * @return array|WP_Error
	 */
	public function update_conversion_status(
		string $tenant_id,
		string $api_key,
		string $body
	) {
		$api_domain = $this->api_domain( $tenant_id );
		$url        = "https://{$api_domain}/api/node/conversions/v1/update-status-by-external-id";
		$args       = [
			'method'    => 'POST',
			'sslverify' => $this->api_ssl_verified,
			'headers'   => [
				'content-type' => 'application/json',
				'x-api-key'    => $api_key,
			],
			'body'      => $body,
		];

		if ( $this->api_request_cookie !== '' ) {
			$args['cookies'] = [
				new WP_Http_Cookie( $this->api_request_cookie ),
			];
		}

		return wp_remote_request( $url, $args );
	}

	public function prepare_body_update_conversion_status(
		string $revenue_origin_id,
		string $external_id,
		string $conversion_type_code,
		string $status
	): string {
		return wp_json_encode( [
			'revenue_origin_id'    => $revenue_origin_id,
			'external_id'          => $external_id,
			'conversion_type_code' => $conversion_type_code,
			'status'               => $status,
		] );
	}

	public function api_domain( string $tenant_id ): string {
		return "{$tenant_id}.{$this->api_domain_root}";
	}

	public static function convert_order_status_to_conversion_status( string $order_status ): ?string {
		$tenant     = WC_Trackdesk_Settings::get_tenant();
		$status_map = WC_Trackdesk_Settings::get_tenant_order_status_to_conversion_status_map( $tenant );

		return array_key_exists( $order_status, $status_map ) ? $status_map[ $order_status ] : null;
	}

	public static function instance(): WC_Trackdesk_Client {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
