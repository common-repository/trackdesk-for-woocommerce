<?php

class WC_Trackdesk_Admin {
    const PAGE_ID = 'wc-trackdesk';

    private static $instance;

    protected function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_loaded', array( $this, 'do_page_action' ) );
    }

    public function admin_menu(): void {
        add_submenu_page(
                'woocommerce',
                __( 'Trackdesk for WooCommerce', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                __( 'Trackdesk', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'manage_woocommerce',
                self::PAGE_ID,
                [ $this, 'render' ],
                5
        );

        $this->connect_to_wc_admin();
    }

    public function render(): void {
        $current_tab = $this->get_current_tab();

        ?>
        <div class="wrap woocommerce">
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <?php
                foreach ( $this->get_tabs() as $name => $label ) {
                    echo '<a href="' . admin_url( 'admin.php?page=' . self::PAGE_ID . '&tab=' . $name ) . '" class="nav-tab ';
                    if ( $current_tab == $name ) {
                        echo 'nav-tab-active';
                    }
                    echo '">' . $label . '</a>';
                }
                ?>
            </nav>
            <?php $this->render_tab() ?>
        </div>
        <?php
    }

    private function render_tab(): void {
        $current_tab = $this->get_current_tab();

        switch ( $current_tab ) {
            case WC_Trackdesk_Admin_Settings::TAB_NAME:
                WC_Trackdesk_Admin_Settings::render();
                break;
            case WC_Trackdesk_Admin_Logs::TAB_NAME:
                WC_Trackdesk_Admin_Logs::render();
                break;
            default:
                // Do noting.
        }
    }

    public function do_page_action(): void {
        if ( ! WC_Trackdesk_Utils::is_current_page( self::PAGE_ID ) ) {
            return;
        }

        $current_tab = $this->get_current_tab();
        switch ( $current_tab ) {
            case WC_Trackdesk_Admin_Settings::TAB_NAME:
                WC_Trackdesk_Admin_Settings::save();
                break;
            case WC_Trackdesk_Admin_Logs::TAB_NAME:
                WC_Trackdesk_Admin_Logs::retry();
                break;
            default:
                // Do noting.
        }
    }

    public function get_tabs(): array {
        return array(
                'settings' => __( 'Settings', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                'logs'     => __( 'Remote request logs', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
        );
    }

    public function get_current_tab(): string {
        return WC_Trackdesk_Utils::get_requested_value( 'tab', 'settings' );
    }

    private function connect_to_wc_admin(): void {
        if ( ! is_callable( 'wc_admin_connect_page' ) ) {
            return;
        }

        wc_admin_connect_page(
                array(
                        'id'        => self::PAGE_ID,
                        'screen_id' => 'woocommerce_page_wc-trackdesk',
                        'path'      => add_query_arg( 'page', self::PAGE_ID, 'admin.php' ),
                        'title'     => array(
                                __( 'Trackdesk for WooCommerce', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                        ),
                )
        );
    }

    public static function instance(): WC_Trackdesk_Admin {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
