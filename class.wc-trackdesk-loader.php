<?php

class WC_Trackdesk_Loader {
    private static $instance;

    private array $notices = array();


    protected function __construct() {
        register_activation_hook( WC_TRACKDESK_PLUGIN_ROOT_FILE, array( $this, 'activation_check' ) );

        add_action( 'admin_init', array( $this, 'check_environment' ) );

        add_action( 'admin_notices', array( $this, 'add_plugin_notices' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

        add_filter( 'plugin_action_links_' . plugin_basename( WC_TRACKDESK_PLUGIN_ROOT_FILE ), array( $this, 'plugin_action_links' ) );

        if ( $this->is_environment_compatible() ) {
            add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        }
    }

    public function __clone() {
        wc_doing_it_wrong(
                __FUNCTION__,
                sprintf( 'You cannot clone instances of %s.', get_class( $this ) ),
                WC_Trackdesk_Config::PLUGIN_VERSION
        );
    }

    public function __wakeup() {
        wc_doing_it_wrong(
                __FUNCTION__,
                sprintf( 'You cannot unserialize instances of %s.', get_class( $this ) ),
                WC_Trackdesk_Config::PLUGIN_VERSION
        );
    }


    public function init_plugin() {
        if ( ! $this->is_plugins_compatible() ) {
            return;
        }

        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }

        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-admin.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-admin-logs.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-admin-logs-list-table.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-admin-settings.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-database.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-client.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-hooks.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-request-log.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-request-logger.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-script.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-settings.php';
        require_once WC_TRACKDESK_PLUGIN_DIR . 'class.wc-trackdesk-utils.php';

        WC_Trackdesk_Admin::instance();
        WC_Trackdesk_Database::instance();
        WC_Trackdesk_Script::instance();
        WC_Trackdesk_Hooks::instance();
    }

    public function activation_check() {
        if ( ! $this->is_environment_compatible() ) {
            $this->deactivate_plugin();

            wp_die( WC_Trackdesk_Config::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message() );
        }
    }

    public function check_environment() {
        if ( ! $this->is_environment_compatible() && is_plugin_active( WC_TRACKDESK_PLUGIN_BASENAME ) ) {
            $this->deactivate_plugin();

            $this->add_admin_notice( 'bad_environment', 'error', WC_Trackdesk_Config::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message() );
        }
    }

    private function is_environment_compatible(): bool {
        return version_compare( PHP_VERSION, WC_Trackdesk_Config::MINIMUM_PHP_VERSION, '>=' );
    }

    private function get_environment_message(): string {
        return sprintf(
                'The minimum PHP version required for this plugin is %1$s. You are running %2$s.',
                WC_Trackdesk_Config::MINIMUM_PHP_VERSION,
                PHP_VERSION
        );
    }

    private function is_plugins_compatible(): bool {
        return $this->is_wp_compatible() && $this->is_wc_compatible();
    }

    private function is_wp_compatible(): bool {
        if ( ! WC_Trackdesk_Config::MINIMUM_WP_VERSION ) {
            return true;
        }

        return version_compare( get_bloginfo( 'version' ), WC_Trackdesk_Config::MINIMUM_WP_VERSION, '>=' );
    }

    private function is_wc_activated(): bool {
        return class_exists( 'WooCommerce' );
    }

    private function is_wc_installed(): bool {
        $plugin            = 'woocommerce/woocommerce.php';
        $installed_plugins = get_plugins();

        return isset( $installed_plugins[ $plugin ] );
    }

    private function is_wc_compatible(): bool {
        if ( ! WC_Trackdesk_Config::MINIMUM_WC_VERSION ) {
            return true;
        }

        return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WC_Trackdesk_Config::MINIMUM_WC_VERSION, '>=' );
    }

    private function deactivate_plugin() {
        deactivate_plugins( WC_TRACKDESK_PLUGIN_BASENAME );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

    public function add_plugin_notices() {
        if ( ! $this->is_wp_compatible() ) {
            if ( current_user_can( 'update_core' ) ) {
                $this->add_admin_notice(
                        'update_wordpress',
                        'error',
                        sprintf(
                                esc_html__( '%1$s requires WordPress version %2$s or higher. Please %3$supdate WordPress &raquo;%4$s', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                                '<strong>' . WC_Trackdesk_Config::PLUGIN_NAME . '</strong>',
                                WC_Trackdesk_Config::MINIMUM_WP_VERSION,
                                '<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">',
                                '</a>'
                        )
                );
            }
        }

        // Notices to install and activate or update WooCommerce.
        $screen = get_current_screen();
        if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
            return; // Do not display the install/update/activate notice in the update plugin screen.
        }

        $plugin = 'woocommerce/woocommerce.php';
        if ( ! $this->is_wc_activated() ) {
            if ( $this->is_wc_installed() ) {
                // WooCommerce is installed but not activated. Ask the user to activate WooCommerce.
                if ( current_user_can( 'activate_plugins' ) ) {
                    $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
                    $message        = sprintf(
                            esc_html__( '%1$s requires WooCommerce to be activated. Please %2$sactivate WooCommerce%3$s.', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                            '<strong>' . WC_Trackdesk_Config::PLUGIN_NAME . '</strong>',
                            '<a href="' . esc_url( $activation_url ) . '">',
                            '</a>'
                    );
                    $this->add_admin_notice(
                            'activate_woocommerce',
                            'error',
                            $message
                    );
                }
            } else {
                // WooCommerce is not installed. Ask the user to install WooCommerce.
                if ( current_user_can( 'install_plugins' ) ) {
                    $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
                    $message     = sprintf(
                            esc_html__( '%1$s requires WooCommerce to be installed and activated. Please %2$sinstall WooCommerce%3$s.', WC_Trackdesk_Config::TRANSLATE_DOMAIN ),
                            '<strong>' . WC_Trackdesk_Config::PLUGIN_NAME . '</strong>',
                            '<a href="' . esc_url( $install_url ) . '">',
                            '</a>'
                    );
                    $this->add_admin_notice(
                            'install_woocommerce',
                            'error',
                            $message
                    );
                }
            }
        } elseif ( ! $this->is_wc_compatible() ) {
            // If WooCommerce is activated, check for the version.
            if ( current_user_can( 'update_plugins' ) ) {
                $update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin, 'upgrade-plugin_' . $plugin );
                $this->add_admin_notice(
                        'update_woocommerce',
                        'error',
                        sprintf(
                                esc_html__(
                                        '%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
                                        WC_Trackdesk_Config::TRANSLATE_DOMAIN
                                ),
                                '<strong>' . WC_Trackdesk_Config::PLUGIN_NAME . '</strong>',
                                WC_Trackdesk_Config::MINIMUM_WC_VERSION,
                                '<a href="' . esc_url( $update_url ) . '">',
                                '</a>',
                                '<a href="' . esc_url( 'https://downloads.wordpress.org/plugin/woocommerce.' . WC_Trackdesk_Config::MINIMUM_WC_VERSION . '.zip' ) . '">',
                                '</a>'
                        )
                );
            }
        }
    }

    private function add_admin_notice( $slug, $class, $message ) {
        $this->notices[ $slug ] = array(
                'class'   => $class,
                'message' => $message,
        );
    }

    public function admin_notices() {
        foreach ( $this->notices as $notice ) {

            ?>
            <div class="<?php echo esc_attr( $notice['class'] ); ?>">
                <p>
                    <?php
                    echo wp_kses(
                            $notice['message'],
                            array(
                                    'a'      => array(
                                            'href' => array(),
                                    ),
                                    'strong' => array(),
                            )
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    public static function plugin_action_links( $links ): array {
        $action_links = array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=wc-trackdesk' ) . '">' . esc_html__( 'Settings', WC_Trackdesk_Config::TRANSLATE_DOMAIN ) . '</a>',
        );

        return array_merge( $action_links, $links );
    }

    public static function instance(): WC_Trackdesk_Loader {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
