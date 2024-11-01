<?php

class WC_Trackdesk_Database {
	const ANY = 'any';

	private static $instance;

	protected function __construct() {
		self::create_table_request_logs();
	}

	public static function create_request_log(
		string $tenant_id,
		string $order_id,
		string $action,
		string $body,
		string $status,
		string $status_message
	): int {
		global $wpdb;

		$query_data   = array(
			'tenant_id'      => $tenant_id,
			'order_id'       => $order_id,
			'action'         => $action,
			'body'           => $body,
			'status'         => $status,
			'status_message' => $status_message,
		);
		$query_format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		$wpdb->insert(
			self::get_table_name_request_logs(),
			$query_data,
			$query_format,
		);

		return absint( $wpdb->insert_id );
	}

	public static function update_request_log_status(
		string $log_id,
		string $status,
		string $status_message
	): int {
		global $wpdb;

		$query_data = array(
			'status'         => $status,
			'status_message' => $status_message,
		);

		$query_format = array(
			'%s',
			'%s',
		);

		$wpdb->update(
			self::get_table_name_request_logs(),
			$query_data,
			array( 'log_id' => $log_id ),
			$query_format,
			array( '%d' ),
		);

		return $log_id;
	}

	public static function get_request_log( int $log_id ): ?object {
		global $wpdb;

		$table_name = self::get_table_name_request_logs();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE log_id = %d LIMIT 1",
				$log_id,
			)
		);
	}

	public static function list_request_logs( $args = array() ): ?array {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'order_by' => 'modified_at',
			'order'    => 'DESC',
			'limit'    => - 1,
			'offset'   => - 1,
		) );

		$table_name = self::get_table_name_request_logs();
		$where      = self::prepare_conditions_list_request_logs( $args );
		$order_by   = sprintf( ' ORDER BY %s', sanitize_sql_orderby( "{$args['order_by']} {$args['order']}" ) );
		$limit      = ( $args['limit'] > 0 ) ? $wpdb->prepare( ' LIMIT %d', $args['limit'] ) : '';
		$offset     = ( $args['offset'] > 0 ) ? $wpdb->prepare( ' OFFSET %d', $args['offset'] ) : '';

		return $wpdb->get_results( "SELECT * FROM $table_name $where $order_by $limit $offset" );
	}

	public static function count_request_logs( $args = array() ): int {
		global $wpdb;

		$table_name = self::get_table_name_request_logs();
		$where      = self::prepare_conditions_list_request_logs( $args );

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
	}

	private static function prepare_conditions_list_request_logs( array $args ): string {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'order_id' => self::ANY,
			'action'   => self::ANY,
			'status'   => self::ANY,
			'order_by' => 'created_at',
			'order'    => 'DESC',
			'limit'    => - 1,
		) );

		$where = ' WHERE 1=1';

		if ( self::ANY !== $args['order_id'] ) {
			$where .= $wpdb->prepare(
				' AND order_id = %s',
				$args['order_id'],
			);
		}

		if ( self::ANY !== $args['action'] ) {
			$where .= $wpdb->prepare(
				' AND action = %s',
				$args['action'],
			);
		}

		if ( self::ANY !== $args['status'] ) {
			$where .= $wpdb->prepare(
				' AND status = %s',
				$args['status'],
			);
		}

		return $where;
	}

	public static function get_table_name_request_logs(): string {
		global $wpdb;

		return $wpdb->prefix . 'trackdesk_request_logs';
	}

	private static function create_table_request_logs(): void {
		global $wpdb;

		$table_name = self::get_table_name_request_logs();
		$charset    = $wpdb->get_charset_collate();

		$query = "CREATE TABLE $table_name (
    		log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    		tenant_id VARCHAR(255) NOT NULL,
    		order_id VARCHAR(255) NOT NULL,
    		action VARCHAR(50) NOT NULL,
    		body TEXT NOT NULL,
    		status VARCHAR(50) NOT NULL,
    		status_message TEXT NOT NULL,
    		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    		modified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    		PRIMARY KEY  (log_id)
    	) $charset;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $query );
	}

	public static function instance(): WC_Trackdesk_Database {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
