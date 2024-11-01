<?php

class WC_Trackdesk_Script {
    private static $instance;

    protected function __construct() {
        add_action( "wp_head", array( $this, 'header_script_init' ), 20 );
    }

    public function header_script_init(): void {
        $general = WC_Trackdesk_Settings::get_general();
        $tenant  = WC_Trackdesk_Settings::get_tenant();

        if ( ! $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ENABLED ] ) {
            return;
        }

        $tenant_id                           = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ID ];
        $order_received_conversion_type_code = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE ];
        $revenue_origin_id                   = $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_REVENUE_ORIGIN_ID ];
        $customer_id_filled                  = WC_Trackdesk_Settings::get_tenant_customer_id_filled( $tenant );
        $conversion_on_subscription_renewal  = WC_Trackdesk_Settings::get_tenant_conversion_on_subscription_renewal( $tenant );

        ?>
        <script async src="<?php echo esc_url( $general[ WC_Trackdesk_Settings::GENERAL_SETTINGS_KEY_TRACKING_SCRIPT_URL ] ) ?>"></script>
        <script type="text/javascript">
            (function (t, d, k) {
                (t[k] = t[k] || []).push(d);
                t[d] = t[d] || t[k].f || function () {
                    (t[d].q = t[d].q || []).push(arguments)
                }
            })(window, "trackdesk", "TrackdeskObject");

            trackdesk('<?php echo esc_js( $tenant_id ) ?>', 'click');
        </script>
        <?php
        // Send conversion only on thank you page
        if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && isset( $GLOBALS['order-received'] ) ) {
            $order = wc_get_order( $GLOBALS['order-received'] );

            if ( $order instanceof WC_Order ) {
                $conversion_status = WC_Trackdesk_Client::convert_order_status_to_conversion_status( $order->get_status() )

                ?>
                <script type="text/javascript">
                    trackdesk('<?php echo esc_js( $tenant_id ) ?>', 'conversion', {
                        conversionType: '<?php echo esc_js( $order_received_conversion_type_code ) ?>',
                        status: <?php echo $conversion_status ? "'" . esc_js( $conversion_status ) . "'" : 'null' ?>,
                        amount: {
                            value: '<?php echo esc_js( WC_Trackdesk_Utils::get_conversion_amount_from_order( $order ) ) ?>'
                        },
                        revenueOriginId: '<?php echo esc_js( $revenue_origin_id ) ?>',
                        externalId: '<?php echo esc_js( $order->get_id() ) ?>',
                        customerId: <?php echo $customer_id_filled ? "'" . esc_js( $order->get_billing_email() ) . "'" : 'null' ?>,
                    });
                </script>
                <?php

            }

            if ( $conversion_on_subscription_renewal && WC_Trackdesk_Utils::is_wc_subscription_activated() ) {
                $subscriptions = wcs_get_subscriptions_for_order( $order->get_id(), [ 'order_type' => 'any' ] );

                foreach ( $subscriptions as $subscription ) {
                    ?>
                    <script type="text/javascript">
                        trackdesk('<?php echo esc_js( $tenant_id ) ?>', "externalCid", {
                            externalCid: '<?php echo esc_js( WC_Trackdesk_Utils::get_subscription_external_cid( $subscription->get_id() ) ) ?>',
                            revenueOriginId: '<?php echo esc_js( $revenue_origin_id ) ?>',
                        });
                    </script>
                    <?php
                }
            }
        }
    }

    public static function instance(): WC_Trackdesk_Script {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
