<?php

class WC_Trackdesk_Admin_Logs_List_Table extends WP_List_Table {
    const QueryParamAction = 'action';
    const QueryParamStatus = 'status';

    public function __construct() {
        parent::__construct( array(
                'singular' => 'Remote request log',
                'plural'   => 'Remote request logs',
                'ajax'     => false
        ) );
    }

    function get_columns(): array {
        return array(
                'log_id'      => __( 'Log ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'status'      => __( 'Status', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'action'      => __( 'Action', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'order_id'    => __( 'Order ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'tenant_id'   => __( 'Tenant ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'created_at'  => __( 'Created', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'modified_at' => __( 'Last Updated', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
        );
    }

    protected function extra_tablenav( $which ): void {
        if ( $which !== 'top' ) {
            return;
        }

        echo '<div class="alignleft actions">';
        $this->action_dropdown();
        submit_button( __( 'Filter', WC_Trackdesk_Config::TRANSLATE_DOMAIN ), '', 'filter-action', false, array( 'id' => 'post-query-submit' ) );
        echo '</div>';
    }

    public function action_dropdown(): void {
        $actions = array(
                WC_Trackdesk_Request_Log::ACTION_CREATE_CONVERSION_BY_EXTERNAL_CID,
                WC_Trackdesk_Request_Log::ACTION_UPDATE_CONVERSION_STATUS,
        );

        $current_action = WC_Trackdesk_Utils::get_requested_value( self::QueryParamAction );
        ?>
        <label for="filter-by-action" class="screen-reader-text"><?php esc_html_e( 'Filter by action', WC_Trackdesk_Config::TRANSLATE_DOMAIN ); ?></label>
        <select name="<?php echo self::QueryParamAction ?>" id="filter-by-action">
            <option<?php selected( $current_action, '' ); ?> value=""><?php esc_html_e( 'All actions', WC_Trackdesk_Config::TRANSLATE_DOMAIN ); ?></option>
            <?php
            foreach ( $actions as $action ) {
                printf(
                        '<option%1$s value="%2$s">%3$s</option>',
                        selected( $current_action, $action, false ),
                        esc_attr( $action ),
                        esc_html( $action )
                );
            }
            ?>
        </select>
        <?php
    }

    protected function get_views(): array {
        $current_status = WC_Trackdesk_Utils::get_requested_value( self::QueryParamStatus );

        return array(
                'all' => $this->get_status_view( 'All', '', $current_status ),
                'err' => $this->get_status_view( 'Error', WC_Trackdesk_Request_Log::STATUS_ERROR, $current_status ),
                'suc' => $this->get_status_view( 'Success', WC_Trackdesk_Request_Log::STATUS_SUCCESS, $current_status ),
        );
    }

    private function get_status_view( string $label, string $status, string $currentStatus ): string {
        $url = $status
                ? add_query_arg( self::QueryParamStatus, $status )
                : remove_query_arg( self::QueryParamStatus );

        $class = $currentStatus == $status ? ' class="current"' : '';

        return "<a href='$url' $class >$label</a>";
    }

    public function prepare_items(): void {
        if ( ! empty( $_REQUEST['_wp_http_referer'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
            $url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            $url = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $url );

            wp_safe_redirect( $url );
            exit;
        }

        $list_args = array();

        if ( ! empty( $_REQUEST[ self::QueryParamAction ] ) ) {
            $list_args['action'] = $_REQUEST[ self::QueryParamAction ];
        }

        if ( ! empty( $_REQUEST[ self::QueryParamStatus ] ) ) {
            $list_args['status'] = $_REQUEST[ self::QueryParamStatus ];
        }

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = WC_Trackdesk_Database::count_request_logs( $list_args );

        $this->set_pagination_args( array(
                'total_items' => $total_items,
                'total_pages' => ceil( $total_items / $per_page ),
                'per_page'    => $per_page,
        ) );

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = array();
        $this->_column_headers = array( $columns, $hidden, $sortable );


        $list_args['limit']  = $per_page;
        $list_args['offset'] = ( $current_page - 1 ) * $per_page;

        $this->items = $total_items > 0 ? WC_Trackdesk_Database::list_request_logs( $list_args ) : array();
    }

    protected function column_default( $item, $column_name ) {
        return esc_html( $item->$column_name );
    }

    protected function column_log_id( object $item ): string {
        $actions = array(
                'detail' => sprintf(
                        '<a href="%s">%s</a>',
                        WC_Trackdesk_Admin_Logs::get_detail_url( $item->log_id ),
                        __( 'Detail', WC_Trackdesk_Config::TRANSLATE_DOMAIN )
                ),
                'retry'  => sprintf(
                        '<a href="%s">%s</a>',
                        WC_Trackdesk_Admin_Logs::get_retry_url( $item->log_id ),
                        __( 'Retry', WC_Trackdesk_Config::TRANSLATE_DOMAIN )
                ),
        );

        return sprintf( '%1$s %2$s', $item->log_id, $this->row_actions( $actions ) );
    }

    protected function column_status( $item ): string {
        switch ( $item->status ) {
            case WC_Trackdesk_Request_Log::STATUS_SUCCESS:
                return '<span style="color: #3d9970">' . esc_html( $item->status ) . '</span>';
            case WC_Trackdesk_Request_Log::STATUS_ERROR:
                return '<span style="color: #d63638">' . esc_html( $item->status ) . '</span>';
            default:
                return esc_html( $item->status );
        }
    }

    protected function column_order_id( $item ): string {
        return sprintf( '<a href="%s">%s</a>', WC_Trackdesk_Admin_Logs::get_order_url( $item->order_id ), $item->order_id );
    }

    protected function column_modified_at( $item ): string {
        return $item->created_at !== $item->modified_at ? $item->modified_at : '-';
    }
}
