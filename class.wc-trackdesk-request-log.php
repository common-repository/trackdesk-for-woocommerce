<?php

class WC_Trackdesk_Request_Log {
	const ACTION_CREATE_CONVERSION_BY_EXTERNAL_CID = 'CREATE_CONVERSION_BY_EXTERNAL_CID';
	const ACTION_UPDATE_CONVERSION_STATUS = 'UPDATE_CONVERSION_STATUS';

	const STATUS_ERROR = 'ERROR';
	const STATUS_IN_PROGRESS = 'IN_PROGRESS';
	const STATUS_SUCCESS = 'SUCCESS';

	protected string $log_id;
	protected string $tenant_id;
	protected string $order_id;
	protected string $action;
	protected string $body;
	protected string $status;
	protected string $status_message;
	protected string $created_at;
	protected string $modified_at;

	protected function __construct(
		string $log_id,
		string $tenant_id,
		string $order_id,
		string $action,
		string $body,
		string $status,
		string $status_message,
		string $created_at,
		string $modified_at
	) {
		$this->log_id         = $log_id;
		$this->tenant_id      = $tenant_id;
		$this->order_id       = $order_id;
		$this->action         = $action;
		$this->body           = $body;
		$this->status         = $status;
		$this->status_message = $status_message;
		$this->created_at     = $created_at;
		$this->modified_at    = $modified_at;
	}

	public function get_log_id(): string {
		return $this->log_id;
	}

	public function get_tenant_id(): string {
		return $this->tenant_id;
	}

	public function get_order_id(): string {
		return $this->order_id;
	}

	public function get_action(): string {
		return $this->action;
	}

	public function get_body(): string {
		return $this->body;
	}

	public function get_status(): string {
		return $this->status;
	}

	public function get_status_message(): string {
		return $this->status_message;
	}

	public function get_created_at(): string {
		return $this->created_at;
	}

	public function get_modified_at(): string {
		return $this->modified_at;
	}

	public static function new_from_db_row( $row ): WC_Trackdesk_Request_Log {
		return new self(
			$row->log_id,
			$row->tenant_id,
			$row->order_id,
			$row->action,
			$row->body,
			$row->status,
			$row->status_message,
			$row->created_at,
			$row->modified_at,
		);
	}
}
