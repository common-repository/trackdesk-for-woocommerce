<?php

use JetBrains\PhpStorm\NoReturn;

class WC_Trackdesk_Admin_Logs {
    const TAB_NAME = 'logs';

    const LINK_ACTION_RETRY = 'retry_remote_request';

    public static function render(): void {
        $log_id = filter_var( WC_Trackdesk_Utils::get_requested_value( 'log_id' ), FILTER_VALIDATE_INT );

        if ( ! $log_id ) {
            self::render_list();
        } else {
            self::render_detail( $log_id );
        }
    }


    public static function render_list(): void {
        $table  = new WC_Trackdesk_Admin_Logs_List_Table();
        $status = WC_Trackdesk_Utils::get_requested_value( WC_Trackdesk_Admin_Logs_List_Table::QueryParamStatus );

        ?>
        <div class="wrap">
            <?php
            $table->views();
            $table->prepare_items();
            ?>
            <form id="logs-filter" method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>"/>
                <input type="hidden" name="tab" value="<?php echo esc_attr( $_REQUEST['tab'] ) ?>"/>
                <?php
                if ( $status !== '' ) {
                    echo sprintf(
                            '<input type="hidden" name="%s" value="%s" />',
                            WC_Trackdesk_Admin_Logs_List_Table::QueryParamStatus,
                            esc_attr( $status )
                    );
                }
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public static function render_detail( int $log_id ): void {
        $log = WC_Trackdesk_Request_Logger::get_request_log( $log_id );

        if ( ! $log ) {
            $url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            $url = remove_query_arg( 'log_id', $url );

            wp_safe_redirect( $url );
            exit;
        }

        ?>
        <div class="wrap">
            <table class="form-table">
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Log ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php echo esc_html( $log->get_log_id() ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Status', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php
                        switch ( $log->get_status() ) {
                            case WC_Trackdesk_Request_Log::STATUS_SUCCESS:
                                echo '<span style="color: #3d9970">' . esc_html( $log->get_status() ) . '</span>';
                                break;
                            case WC_Trackdesk_Request_Log::STATUS_ERROR:
                                echo '<span style="color: #d63638">' . esc_html( $log->get_status() ) . '</span>';
                                break;
                            default:
                                echo esc_html( $log->get_status() );
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Action', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php echo esc_html( $log->get_action() ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Order ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <a href="<?php echo self::get_order_url( $log->get_order_id() ); ?>"><?php echo esc_html( $log->get_order_id() ); ?></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Tenant ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php echo esc_html( $log->get_tenant_id() ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Body', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <pre><?php echo json_encode( json_decode( $log->get_body() ), JSON_PRETTY_PRINT ); ?></pre>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Status Message', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php
                        if ( $log->get_status_message() !== '' ) {
                            $status_message_json = json_decode( $log->get_status_message() );

                            if ( $status_message_json ) {
                                echo '<pre>' . json_encode( json_decode( $log->get_status_message() ), JSON_PRETTY_PRINT ) . '</pre>';
                            } else {
                                echo esc_html( $log->get_status_message() );
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Created', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php echo esc_html( $log->get_created_at() ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <?php echo esc_html__( 'Last Updated', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                    </th>
                    <td>
                        <?php echo esc_html( $log->get_created_at() !== $log->get_modified_at() ? $log->get_modified_at() : '-' ); ?>
                    </td>
                </tr>
            </table>
            <p>
                <a class="button" href="<?php echo self::get_retry_url( $log->get_log_id() ) ?>">
                    <?php echo esc_html__( 'Retry', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?>
                </a>
            </p>
        </div>
        <?php
    }

    public static function retry(): void {
        if ( WC_Trackdesk_Utils::get_requested_value( 'link_action', '' ) !== self::LINK_ACTION_RETRY ) {
            return;
        }

        $log_id = filter_var( WC_Trackdesk_Utils::get_requested_value( 'log_id', '' ), FILTER_VALIDATE_INT );

        if ( ! $log_id ) {
            self::redirect_to_list();
        }

        $log = WC_Trackdesk_Request_Logger::get_request_log( $log_id );

        if ( ! $log ) {
            self::redirect_to_list();
        }

        $tenant    = WC_Trackdesk_Settings::get_tenant();
        $tenant_id = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ID ];

        if ( empty( $tenant_id ) || $tenant_id !== $log->get_tenant_id() ) {
            self::redirect_to_list();
        }

        $api_key = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_API_KEY ];

        switch ( $log->get_action() ) {
            case WC_Trackdesk_Request_Log::ACTION_CREATE_CONVERSION_BY_EXTERNAL_CID:
                $response = WC_Trackdesk_Client::instance()->create_conversion_by_external_cid(
                        $tenant_id,
                        $api_key,
                        $log->get_body()
                );
                break;
            case WC_Trackdesk_Request_Log::ACTION_UPDATE_CONVERSION_STATUS:
                $response = WC_Trackdesk_Client::instance()->update_conversion_status(
                        $tenant_id,
                        $api_key,
                        $log->get_body()
                );
                break;
            default:
                return;
        }

        WC_Trackdesk_Request_Logger::process_remote_response( $log->get_log_id(), $response );
        self::redirect_to_list();
    }

    public static function get_detail_url( string $log_id ): string {
        return sprintf(
                '?page=%s&tab=%s&log_id=%s',
                WC_Trackdesk_Admin::PAGE_ID,
                self::TAB_NAME,
                $log_id,
        );
    }

    public static function get_retry_url( string $log_id ): string {
        return sprintf(
                '?page=%s&tab=%s&link_action=%s&log_id=%s',
                WC_Trackdesk_Admin::PAGE_ID,
                self::TAB_NAME,
                WC_Trackdesk_Admin_Logs::LINK_ACTION_RETRY,
                $log_id,
        );
    }

    public static function get_order_url( string $order_id ): string {
        return sprintf( '?page=wc-orders&action=edit&id=%s', $order_id );
    }

    private static function redirect_to_list(): void {
        $url = admin_url( 'admin.php?page=' . WC_Trackdesk_Admin::PAGE_ID . '&tab=' . self::TAB_NAME );

        wp_safe_redirect( $url );
        exit;
    }
}
