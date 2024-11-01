<?php

class WC_Trackdesk_Request_Logger {
	/**
	 * @param   string  $tenant_id
	 * @param   string  $order_id
	 * @param   string  $body
	 *
	 * @return int Request log ID
	 */
	public static function log_create_conversion_by_external_cid(
		string $tenant_id,
		string $order_id,
		string $body
	): int {
		return WC_Trackdesk_Database::create_request_log(
			$tenant_id,
			$order_id,
			WC_Trackdesk_Request_Log::ACTION_CREATE_CONVERSION_BY_EXTERNAL_CID,
			$body,
			WC_Trackdesk_Request_Log::STATUS_IN_PROGRESS,
			'',
		);
	}

	/**
	 * @param   string  $tenant_id
	 * @param   string  $order_id
	 * @param   string  $body
	 *
	 * @return int Request log ID
	 */
	public static function log_update_conversion_status(
		string $tenant_id,
		string $order_id,
		string $body
	): int {
		return WC_Trackdesk_Database::create_request_log(
			$tenant_id,
			$order_id,
			WC_Trackdesk_Request_Log::ACTION_UPDATE_CONVERSION_STATUS,
			$body,
			WC_Trackdesk_Request_Log::STATUS_IN_PROGRESS,
			'',
		);
	}

	/***
	 * @param   int             $log_id
	 * @param   array|WP_Error  $response
	 *
	 * @return void
	 */
	public static function process_remote_response( int $log_id, $response ): void {
		if ( is_wp_error( $response ) ) {
			if ( $response->has_errors() ) {
				self::change_status_to_error( $log_id, wp_json_encode( $response->errors ) );

				return;
			}
		}

		if ( ! isset( $response['response']['code'] ) || $response['response']['code'] !== 200 ) {
			self::change_status_to_error( $log_id, wp_json_encode( $response ) );

			return;
		}

		self::change_status_to_ok( $log_id );
	}

	private static function change_status_to_ok( int $log_id ): void {
		WC_Trackdesk_Database::update_request_log_status(
			$log_id,
			WC_Trackdesk_Request_Log::STATUS_SUCCESS,
			'',
		);
	}

	private static function change_status_to_error( int $log_id, string $status_message ): void {
		WC_Trackdesk_Database::update_request_log_status(
			$log_id,
			WC_Trackdesk_Request_Log::STATUS_ERROR,
			$status_message,
		);
	}

	public static function get_request_log( int $id ): ?WC_Trackdesk_Request_Log {
		$row = WC_Trackdesk_Database::get_request_log( $id );

		if ( ! $row ) {
			return null;
		}

		return WC_Trackdesk_Request_Log::new_from_db_row( $row );
	}
}
