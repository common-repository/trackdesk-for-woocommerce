<?php

class WC_Trackdesk_Admin_Settings {
    const TAB_NAME = 'settings';
    const FORM_ACTION = 'update_tenant';

    public static function render(): void {
        $tenant                                = WC_Trackdesk_Settings::get_tenant();
        $order_status_to_conversion_status_map = self::format_order_status_to_conversion_status_map(
                WC_Trackdesk_Settings::get_tenant_order_status_to_conversion_status_map( $tenant )
        );

        ?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_id"><?php echo esc_html__( 'Tenant ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input
                                id="wc_trackdesk_tenant_id"
                                name="wc_trackdesk_tenant_id"
                                type="text"
                                value="<?php echo esc_attr( $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ID ] ); ?>"
                                required
                        />
                        <p class="description">Please, fill in your tenant ID, a.k.a. the subdomain on which your program is running.</p>
                        <p class="description">In the case of https://<b>my-awesome-affiliate-system</b>.trackdesk.com, your tenant ID is <b>my-awesome-affiliate-system</b>.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_api_key"><?php echo esc_html__( 'API Key', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input
                                id="wc_trackdesk_tenant_api_key"
                                name="wc_trackdesk_tenant_api_key"
                                type="text"
                                value="<?php echo esc_attr( $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_API_KEY ] ); ?>"
                                required
                        />
                        <p class="description">Managed in your tenant trackdesk platform - Settings > Personal tokens.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_order_received_conversion_type_code"><?php echo esc_html__( 'Order Received Conversion Type Code', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input
                                id="wc_trackdesk_tenant_order_received_conversion_type_code"
                                name="wc_trackdesk_tenant_order_received_conversion_type_code"
                                type="text"
                                value="<?php echo esc_attr( $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ORDER_RECEIVED_CONVERSION_TYPE_CODE ] ?: WC_Trackdesk_Config::DEFAULT_ORDER_RECEIVED_CONVERSION_TYPE_CODE ); ?>"
                                required
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_revenue_origin_id"><?php echo esc_html__( 'Revenue Origin ID', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input
                                id="wc_trackdesk_tenant_revenue_origin_id"
                                name="wc_trackdesk_tenant_revenue_origin_id"
                                type="text"
                                value="<?php echo esc_attr( $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_REVENUE_ORIGIN_ID ] ); ?>"
                                required
                        />
                        <p class="description">Could be found in your tenant trackdesk platform - Offers > Offer detail > Tracking methods >
                            WooCommerce.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_order_status_to_conversion_status_map"><?php echo esc_html__( 'Order Status To Conversion Status Mapping', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <textarea
                                id="wc_trackdesk_tenant_order_status_to_conversion_status_map"
                                name="wc_trackdesk_tenant_order_status_to_conversion_status_map"
                                cols="50"
                                rows="5"
                        ><?php echo esc_html( $order_status_to_conversion_status_map ); ?></textarea>
                        <p class="description">Advanced functionality to change status mapping when your WooCommerce is using custom statuses.</p>
                        <p class="description">Set one status pair per line in format <i>woo_commerce_order_status:trackdesk_conversion_status</i>. Incorrectly
                            set and duplicate lines will be removed.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_customer_id_filled"><?php echo esc_html__( 'Customer ID Filled', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input
                                id="wc_trackdesk_tenant_customer_id_filled"
                                name="wc_trackdesk_tenant_customer_id_filled"
                                type="checkbox"
                                value="1"
                                <?php echo checked( WC_Trackdesk_Settings::get_tenant_customer_id_filled( $tenant ) ); ?>
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_conversion_on_subscription_renewal"><?php echo esc_html__( 'Create Conversion On Subscription Renewal', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input
                                id="wc_trackdesk_tenant_conversion_on_subscription_renewal"
                                name="wc_trackdesk_tenant_conversion_on_subscription_renewal"
                                type="checkbox"
                                value="1"
                                <?php echo checked( WC_Trackdesk_Utils::is_wc_subscription_activated() && WC_Trackdesk_Settings::get_tenant_conversion_on_subscription_renewal( $tenant ) ); ?>
                                <?php echo disabled( ! WC_Trackdesk_Utils::is_wc_subscription_activated() ); ?>
                        />
                        <?php if ( ! WC_Trackdesk_Utils::is_wc_subscription_activated() ) { ?>
                            <p class="description"><a href="https://woo.com/products/woocommerce-subscriptions/" target="_blank">Woo Subscriptions</a>
                                plugin has to be activated to enable this feature.</p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="wc_trackdesk_tenant_enabled"><?php echo esc_html__( 'Enabled', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input
                                id="wc_trackdesk_tenant_enabled"
                                name="wc_trackdesk_tenant_enabled"
                                type="checkbox"
                                value="1"
                                <?php echo checked( $tenant[ WC_Trackdesk_Settings::TENANT_SETTING_KEY_ENABLED ] ); ?>
                        />
                    </td>
                </tr>
            </table>
            <p class="submit">
                <?php submit_button( __( 'Save changes', WC_Trackdesk_Config::TRANSLATE_DOMAIN ), 'primary', 'update_tenant', false ); ?>
                <input name="wc_trackdesk_form_action" type="hidden" value="<?php echo self::FORM_ACTION ?>"/>
            </p>
        </form>
        <?php
    }

    public static function save() {
        if ( ! WC_Trackdesk_Utils::is_current_form_action( self::FORM_ACTION ) ) {
            return;
        }

        $id                                    = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_id' );
        $api_key                               = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_api_key' );
        $order_received_conversion_type_code   = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_order_received_conversion_type_code' );
        $revenue_origin_id                     = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_revenue_origin_id' );
        $order_status_to_conversion_status_map = self::parse_order_status_to_conversion_status_map(
                WC_Trackdesk_Utils::get_requested_textarea_value( 'wc_trackdesk_tenant_order_status_to_conversion_status_map' )
        );
        $customer_id_filled                    = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_customer_id_filled' ) === '1';
        $conversion_on_subscription_renewal    = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_conversion_on_subscription_renewal' ) === '1';
        $enabled                               = WC_Trackdesk_Utils::get_requested_value( 'wc_trackdesk_tenant_enabled' ) === '1';

        $tenant = WC_Trackdesk_Settings::new_tenant(
                $id,
                $api_key,
                $order_received_conversion_type_code,
                $revenue_origin_id,
                $order_status_to_conversion_status_map,
                $customer_id_filled,
                $conversion_on_subscription_renewal,
                $enabled
        );

        WC_Trackdesk_Settings::update_tenant( $tenant );
    }

    public static function parse_order_status_to_conversion_status_map( string $formated_map ): array {
        $map = [];

        $lines = explode( PHP_EOL, trim( $formated_map ) );

        foreach ( $lines as $line ) {
            $rows = explode( ':', $line );

            if ( count( $rows ) !== 2 ) {
                continue;
            }

            $wc_status        = trim( $rows[0] );
            $trackdesk_status = trim( $rows[1] );

            if ( strlen( $wc_status ) === 0 ) {
                continue;
            }

            if ( strlen( $trackdesk_status ) === 0 ) {
                continue;
            }

            $map[ $wc_status ] = $trackdesk_status;
        }

        return $map;
    }

    public static function format_order_status_to_conversion_status_map( array $map ): string {
        $lines = [];

        foreach ( $map as $wc_status => $trackdesk_status ) {
            $lines[] = implode( ':', [ $wc_status, $trackdesk_status ] );
        }

        return implode( PHP_EOL, $lines );
    }
}
