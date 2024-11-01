<?php

class WC_Trackdesk_Hooks {
	private WC_Trackdesk_Client $trackdesk_client;

	private static $instance;

	protected function __construct() {
		$this->trackdesk_client = WC_Trackdesk_Client::instance();

		add_action( 'woocommerce_order_edit_status', array( $this, 'update_conversion_status' ), 10, 2 );

		if ( WC_Trackdesk_Utils::is_wc_subscription_activated() ) {
			add_filter( 'wcs_new_order_created', array( $this, 'add_readonly_action_on_wcs_new_order_created' ), 11, 2 );
		}
	}

	public function add_readonly_action_on_wcs_new_order_created( WC_Order $order, WC_Subscription $subscription ): WC_Order {
		self::create_conversion_on_subscription_renewal( $order, $subscription );

		return $order;
	}

	public function create_conversion_on_subscription_renewal( WC_Order $order, WC_Subscription $subscription ): void {
		$conversion_status = WC_Trackdesk_Client::convert_order_status_to_conversion_status( $order->get_status() );

		$tenant    = WC_Trackdesk_Settings::get_tenant();
		$tenant_id = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ID ];

		if ( empty( $tenant_id ) || ! WC_Trackdesk_Settings::get_tenant_conversion_on_subscription_renewal( $tenant ) ) {
			return;
		}

		$api_key              = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_API_KEY ];
		$revenue_origin_id    = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_REVENUE_ORIGIN_ID ];
		$conversion_type_code = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE ];
		$customer_id_filled   = WC_Trackdesk_Settings::get_tenant_customer_id_filled( $tenant );

		$order_id = strval( $order->get_id() );
		$body     = $this->trackdesk_client->prepare_body_create_conversion_by_external_cid(
			WC_Trackdesk_Utils::get_subscription_external_cid( $subscription->get_id() ),
			$revenue_origin_id,
			$conversion_type_code,
			$conversion_status,
			strval( WC_Trackdesk_Utils::get_conversion_amount_from_order( $order ) ),
			$order_id,
			$customer_id_filled ? $order->get_billing_email() : null
		);

		$request_log_id = WC_Trackdesk_Request_Logger::log_create_conversion_by_external_cid( $tenant_id, $order_id, $body );

		$response = $this->trackdesk_client->create_conversion_by_external_cid(
			$tenant_id,
			$api_key,
			$body,
		);

		WC_Trackdesk_Request_Logger::process_remote_response( $request_log_id, $response );
	}

	public function update_conversion_status( int $order_id, string $new_order_status ): void {
		$conversion_status = WC_Trackdesk_Client::convert_order_status_to_conversion_status( $new_order_status );

		if ( ! $conversion_status ) {
			return;
		}

		$tenant    = WC_Trackdesk_Settings::get_tenant();
		$tenant_id = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ID ];

		if ( empty( $tenant_id ) ) {
			return;
		}

		$api_key              = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_API_KEY ];
		$revenue_origin_id    = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_REVENUE_ORIGIN_ID ];
		$conversion_type_code = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE ];

		$order_id = strval( $order_id );
		$body     = $this->trackdesk_client->prepare_body_update_conversion_status(
			$revenue_origin_id,
			$order_id,
			$conversion_type_code,
			$conversion_status
		);

		$request_log_id = WC_Trackdesk_Request_Logger::log_update_conversion_status( $tenant_id, $order_id, $body );

		$response = $this->trackdesk_client->update_conversion_status(
			$tenant_id,
			$api_key,
			$body
		);

		WC_Trackdesk_Request_Logger::process_remote_response( $request_log_id, $response );
	}

	public static function instance(): WC_Trackdesk_Hooks {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
