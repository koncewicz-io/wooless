<?php

require_once plugin_dir_path( __FILE__ ) . '../includes/api/class-furgonetka-cart.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/api/class-furgonetka-settings.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/api/class-furgonetka-order.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/api/class-furgonetka-map-settings.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://furgonetka.pl
 * @since      1.0.0
 *
 * @package    Furgonetka
 * @subpackage Furgonetka/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks
 *
 * @package    Furgonetka
 * @subpackage Furgonetka/public
 * @author     Furgonetka.pl <woocommerce@furgonetka.pl>
 */
class Furgonetka_Public
{
    const FURGONETKA_CHECKOUT_REPLACEMENT_URL = '#portmonetka-open-in-new-tab';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * Furgonetka_Public_View Class
     *
     * @var \Furgonetka_Public_View()
     */
    private $view;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private static $class_base = 'furgonetka-checkout-btn';
    /** construction classes */
    private static $base_class;
    private static $widget_class;
    private static $minicart_widget_class;
    private static $backend_class;
    private static $frontend_class;
    /** origin classes */
    private static $userdefined_class;
    private static $builder_class;
    /** page position classes */
    private static $product_class;
    private static $cart_class;
    private static $order_class;
    private static $minicart_class;
    /** hidden container class */
    private static $hidden_class = 'furgonetka-hidden-checkout-btn';

    /**
     * Point types
     */
    const POINT_TYPE_SERVICE_POINT  = 'service_point';
    const POINT_TYPE_PARCEL_MACHINE = 'parcel_machine';

    /**
     * Courier services
     */
    const SERVICE_INPOST         = 'inpost';
    const SERVICE_POCZTA         = 'poczta';
    const SERVICE_DPD            = 'dpd';
    const SERVICE_DHL            = 'dhl';
    const SERVICE_ORLEN          = 'orlen';
    const SERVICE_UPSACCESSPOINT = 'uap';
    const SERVICE_GLS            = 'gls';
    const SERVICE_FEDEX          = 'fedex';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        self::setup_classes();

        $this->include_view();
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        if ( ! is_checkout() ) {
            return;
        }

        $file_path = 'css/furgonetka-public.css';

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . $file_path,
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . $file_path ),
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $furgonetkaBaseUrl = Furgonetka_Admin::get_test_mode() ? 'https://sandbox.furgonetka.pl' : 'https://furgonetka.pl';

        if ( Furgonetka_Admin::is_checkout_active() ) {
            $checkout_file_path = 'js/woocommerce-checkout' . ( Furgonetka_Admin::get_test_mode()
                    ? '-sandbox' : '-prod' ) . '.js';
            wp_enqueue_script(
                "$this->plugin_name-checkout",
                plugin_dir_url( __FILE__ ) . $checkout_file_path,
                array(),
                filemtime( plugin_dir_path( __FILE__ ) . $checkout_file_path ),
                true
            );
            $cart_btn_position = Furgonetka_Admin::get_portmonetka_cart_button_position();
            $cart_btn_width = Furgonetka_Admin::get_portmonetka_cart_button_width();
            $cart_btn_css = Furgonetka_Admin::get_portmonetka_cart_button_css();

            wp_localize_script(
                "$this->plugin_name-checkout",
                'portmonetka_settings',
                array(
                    'portmonetka_uuid'   => Furgonetka_Admin::get_checkout_uuid(),
                    'is_test_mode'       => (int) Furgonetka_Admin::is_checkout_test_mode(),
                    'product_selector'   => Furgonetka_Admin::get_portmonetka_product_selector(),
                    'cart_selector'      => Furgonetka_Admin::get_portmonetka_cart_selector(),
                    'minicart_selector'  => Furgonetka_Admin::get_portmonetka_minicart_selector(),
                    'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                    'checkout_details'   => Furgonetka_Admin::get_checkout_details(),
                    'checkout_rest_urls' => Furgonetka_Admin::get_checkout_rest_urls(),
                    'cart_btn_position'  => $cart_btn_position ?? false,
                    'cart_btn_width'     => $cart_btn_width ?? false,
                    'cart_btn_css'       => $cart_btn_css ?? false,
                    'replace_native_checkout'    => Furgonetka_Admin::get_portmonetka_replace_native_checkout(),
                    'classes'            => [
                        'base_class'         => self::$base_class,
                        'widget_class'       => self::$widget_class,
                        'minicart_widget_class'       => self::$minicart_widget_class,
                        'backend_class'      => self::$backend_class,
                        'frontend_class'     => self::$frontend_class,
                        'userdefined_class'  => self::$userdefined_class,
                        'builder_class'      => self::$builder_class,
                        'product_class'      => self::$product_class,
                        'cart_class'         => self::$cart_class,
                        'order_class'        => self::$order_class,
                        'minicart_class'     => self::$minicart_class,
                        'hidden_class'       => self::$hidden_class
                    ],
                    'pages_urls' => [
                        'cart' => parse_url(wc_get_cart_url())['path'] ?? null,
                        'checkout' => wc_get_checkout_url()
                    ]
                )
            );
        }

        if ( ! is_checkout() ) {
            return;
        }

        $file_path = 'js/furgonetka-public.js';

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . $file_path,
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ) . $file_path ),
            false
        );

        wp_enqueue_script( 'furgonetka_map', $furgonetkaBaseUrl . '/js/dist/map/map.js"', array(), '1.0', true );

        wp_localize_script(
            $this->plugin_name,
            'settings',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }

    /**
     * Include view for pbulic
     */
    public function include_view()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/view/class-furgonetka-public-view.php';
        $this->view = new Furgonetka_Public_View();
    }

    /**
     * Add map script to shipping options and current selected point from session.
     * Dosen't load map if there is only virtual products in cart
     *
     * @since    1.1.0
     */
    public function furgonetka_totals_after_shipping()
    {
        $virtual_product = false;
        $normal_product  = false;
        foreach ( WC()->session->cart as $item ) {
            $product = wc_get_product( $item['product_id'] );
            if ( ! $product ) {
                continue;
            }
            $product->is_virtual() ? $virtual_product = true : $normal_product = true;
        }

        if (
            ! $virtual_product && $normal_product ||
            $virtual_product && $normal_product
        ) {
            $service = $this->get_selected_service_from_session();

            if ( $service ) {
                $selected_point = $this->get_selected_point_from_session(
                    $service,
                    ( WC()->session->get( 'chosen_payment_method' ) === 'cod' )
                );
            } else {
                $selected_point = $this->get_selected_point_from_session( '', false );
            }
            $this->view->render_map( $this->plugin_name, $selected_point );
        } else {
            return false;
        }
    }

    /**
     * Get assigned pickup point service for the current order based on shipping methods configuration
     *
     * @param \WC_Order $order
     * @return string|null
     */
    public function get_order_shipping_method_service( $order )
    {
        /**
         * Get shipping method ID
         */
        $shipping_methods = $order->get_shipping_methods();
        $shipping_method = reset( $shipping_methods );

        if ( ! $shipping_method ) {
            return null;
        }

        $shipping_method_id = $shipping_method->get_method_id() . ':' . $shipping_method->get_instance_id();

        /**
         * Get assigned delivery type for shipping method ID
         */
        return Furgonetka_Map::get_service_by_shipping_rate_id( $shipping_method_id );
    }

    /**
     * Save selected point to order.
     *
     * @param \WC_Order $order - WC_Order Class.
     * @param mixed     $posted - posted.
     *
     *  @since  1.0.0
     */
    public function save_point_to_order( $order, $posted )
    {
        /**
         * Remove point data when assigned service is invalid or empty
         */
        $order_service = $this->get_order_shipping_method_service( $order );
        $furgonetka_service = isset( $_POST['furgonetkaService'] ) ? sanitize_text_field( wp_unslash( $_POST['furgonetkaService'] ) ) : null;

        if ( $this->is_woocommerce_payments_express_checkout_request() ) {
            $furgonetka_service = $this->get_selected_service_from_session();
        }

        if ( $order_service !== $furgonetka_service ) {
            $order->delete_meta_data( '_furgonetkaPoint' );
            $order->delete_meta_data( '_furgonetkaPointName' );
            $order->delete_meta_data( '_furgonetkaService' );
            $order->delete_meta_data( '_furgonetkaServiceType' );

            return;
        }

        /**
         * Update order
         */
        if ( $this->is_woocommerce_payments_express_checkout_request() ) {
            $point = $this->get_selected_point_from_session( $order_service, false );

            $order->update_meta_data( '_furgonetkaPoint', $point[ 'code' ] );
            $order->update_meta_data( '_furgonetkaPointName', $point[ 'name' ] );
            $order->update_meta_data( '_furgonetkaService', $point[ 'service' ] );
            $order->update_meta_data( '_furgonetkaServiceType', $point[ 'service_type' ] );
        }

        //phpcs:ignore
        if ( isset( $_POST['furgonetkaPoint'] ) ) {
            //phpcs:ignore
            $order->update_meta_data(
                '_furgonetkaPoint',
                sanitize_text_field( wp_unslash( $_POST['furgonetkaPoint'] ) )
            );
        }
        //phpcs:ignore
        if ( isset( $_POST['furgonetkaPointName'] ) ) {
            //phpcs:ignore
            $order->update_meta_data(
                '_furgonetkaPointName',
                sanitize_text_field( wp_unslash( $_POST['furgonetkaPointName'] ) )
            );
        }
        //phpcs:ignore
        if ( isset( $_POST['furgonetkaService'] ) ) {
            //phpcs:ignore
            $order->update_meta_data(
                '_furgonetkaService',
                sanitize_text_field( wp_unslash( $_POST['furgonetkaService'] ) )
            );
        }
        //phpcs:ignore
        if ( isset( $_POST['furgonetkaServiceType'] ) ) {
            //phpcs:ignore
            $order->update_meta_data(
                '_furgonetkaServiceType',
                sanitize_text_field( wp_unslash( $_POST['furgonetkaServiceType'] ) )
            );
        }
    }

    /**
     * Save selected point to woocommerce session
     *
     * @since    1.0.0
     */
    public function save_point_to_session()
    {
        if ( ! check_ajax_referer( $this->plugin_name . '_setPointAction', 'security', false ) === false ) {
            $current_service = isset( $_POST['currentService'] ) ?
                sanitize_text_field( wp_unslash( $_POST['currentService'] ) ) : '';
            $service_type    = isset( $_POST['serviceType'] ) ?
                sanitize_text_field( wp_unslash( $_POST['serviceType'] ) ) : '';
            $name            = isset( $_POST['name'] ) ?
                sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
            $code            = isset( $_POST['code'] ) ?
                sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
            $cod             = isset( $_POST['cod'] ) ?
                sanitize_text_field( wp_unslash( $_POST['cod'] ) ) : '';
        } else {
            wp_send_json_error();
        }

        $this->save_point_to_session_internal( $current_service, $service_type, $code, $name, $cod === 'true' );

        wp_send_json_success();
    }

    /**
     * Save selected point to WooCommerce (internal)
     */
    public function save_point_to_session_internal( $current_service, $service_type, $code, $name, $cod )
    {
        $current_selection = WC()->session->get( $this->plugin_name . '_pointTo' );

        if ( $cod ) {
            $current_selection = WC()->session->get( $this->plugin_name . '_pointToCod' );
        }

        if ( ! $current_selection ) {
            $current_selection = array();
        }

        $current_selection[ $current_service ] = array(
            'service'      => $current_service,
            'service_type' => $service_type,
            'code'         => $code,
            'name'         => $name,
        );

        if ( $cod ) {
            WC()->session->set( $this->plugin_name . '_pointToCod', $current_selection );
        } else {
            WC()->session->set( $this->plugin_name . '_pointTo', $current_selection );
        }
    }

    /**
     *
     * Get selected point from woocommerce session
     */
    public function get_point_to_payment()
    {
        //phpcs:ignore
        if ( isset( $_POST['cod'] ) ) {
            $cod = sanitize_text_field( wp_unslash( $_POST['cod'] ) );
        }

        if ( check_ajax_referer( $this->plugin_name . '_setPointAction', 'security', false ) === false ) {
            wp_send_json_error();
        }

        $service = $this->get_selected_service_from_session();
        $selected_point = $this->get_selected_point_from_session( $service, 'true' === $cod );

        $data = array(
            'button' => $this->generate_delivery_button( $service, 'true' === $cod ),
            'code'   => $selected_point['code']
        );

        wp_send_json_success( $data );
    }

    /**
     * Add map to shipping option.
     *
     * @param mixed $method - method.
     * @param mixed $index - index.
     * @return void
     */
    public function after_shipping_rate( $method, $index )
    {
        if ( ! is_checkout() ) {
            return;
        }

        $chosen_method_array = WC()->session->get( 'chosen_shipping_methods' );

        if ( $chosen_method_array[0] !== $method->id ) {
            return;
        }

        $service = Furgonetka_Map::get_service_from_session();

        if ( $service ) {
            // all variables are escaped in generate_delivery_button method.
            //phpcs:ignore
            echo '<p id="select-point-container">' . $this->generate_delivery_button(
                    $service,
                    ( WC()->session->get( 'chosen_payment_method' ) === 'cod' )
                ) . '</p>';
        }
    }

    /**
     * Select Point button in delivery list
     *
     * @param string $service - method type.
     * @param mixed  $is_cod - check if is COD
     * @since    1.0.0
     * @return string
     */
    public function generate_delivery_button( $service, $is_cod )
    {
        $selected_point = $this->get_selected_point_from_session( $service, $is_cod );
        $customer       = WC()->session->get( 'customer' );
        $countryCode    = ! empty( $customer[ 'shipping_country' ] ) ? $customer[ 'shipping_country' ] : 'PL';
        $mapBounds      = strtolower( $customer[ 'shipping_country' ] ) === 'pl' ? 'pl' : 'eu';
        $change_point_label = __( 'Change point', 'furgonetka' );
        $label = empty($selected_point['name']) ? __( 'Select point', 'furgonetka' ) : $change_point_label;

        return sprintf(
            '<a id="select-point" href="#" onclick=\'openFurgonetkaMap("%1$s","%4$s","%5$s","%6$s","%7$s");return false\'>%2$s</a><span id="selected-point">%3$s</span>',
            esc_html( $service ),
            "<span id=\"select-point-label\" data-change-point-label=\"$change_point_label\">$label</span>",
            esc_html( $selected_point['name'] ),
            esc_html( $customer['shipping_city'] ),
            esc_html( $customer['shipping_address_1'] ) . ' ' . esc_html( $customer['shipping_address_2'] ),
            esc_html( $countryCode ),
            $mapBounds
        );
    }

    /**
     * Get selected point from woocommerce session
     *
     * @param mixed $service - name of services.
     * @param mixed $is_cod - checkif is cod.
     * @return string[]
     */
    public function get_selected_point_from_session( $service, $is_cod )
    {
        $return_selection  = array(
            'service' => '',
            'service_type' => '',
            'name'    => '',
            'code'    => '',
        );
        $current_selection = WC()->session->get( $this->plugin_name . '_pointTo' );
        if ( $is_cod ) {
            $current_selection = WC()->session->get( $this->plugin_name . '_pointToCod' );
        }

        if ( isset( $current_selection[ $service ] ) ) {
            return $current_selection[ $service ];
        }

        return $return_selection;
    }

    /**
     * Get currently selected service based on session and configuration
     *
     * @return string|null
     */
    public function get_selected_service_from_session()
    {
        return Furgonetka_Map::get_service_from_session();
    }

    /**
     * Validate point selection in php
     *
     * @since    1.0.7
     */
    public function woocommerce_checkout_process()
    {
        $service = $this->get_selected_service_from_session();

        if ( $service ) {
            //phpcs:ignore
            if ( isset(  $_POST['furgonetkaPoint'] ) ) {
                //phpcs:ignore
                $point = sanitize_text_field( wp_unslash( $_POST['furgonetkaPoint'] ) );
            }

            if ( $this->is_woocommerce_payments_express_checkout_request() ) {
                $point = $this->get_selected_point_from_session( $service, false )['code'];
            }

            if ( empty( $point ) && true === WC()->cart->needs_shipping() ) {
                wc_add_notice( __( 'Please select delivery point.', 'furgonetka' ), 'error' );
            }
        }
    }

    /**
     * Add point information to view
     *
     * @param WC_Order $order
     *
     * @since 1.4.6
     */
    public function add_point_information( $order )
    {
        $service      = esc_html( $order->get_meta( '_furgonetkaService' ) );
        $service_type = esc_html( $order->get_meta( '_furgonetkaServiceType' ) );
        $point        = esc_html( $order->get_meta( '_furgonetkaPoint' ) );
        $point_name   = esc_html( $order->get_meta( '_furgonetkaPointName' ) );

        if ( ! empty( $service ) ) {
            $service_name = self::get_service_name( $service, $service_type );
            $displayed_point_name = esc_html( $point_name ) ? esc_html( $point_name ) : esc_html( $point );
            $this->view->render_point_information( $service_name, $displayed_point_name );
        }
    }

    /**
     * Add tracking information
     *
     * @param WC_Order $order
     * @since 1.5.1
     */
    private function add_tracking_information( $order )
    {
        $tracking = $order->get_meta( 'tracking_info' );

        if ( ! empty( $tracking ) ) {
            foreach ( $tracking as $package_number => $details ) {
                $this->view->render_package_tracking_link( $package_number );
            }
        }
    }

    /**
     * Add order information (pickup point & tracking)
     *
     * @param WC_Order $order
     * @since 1.5.1
     */
    public function add_order_information( $order )
    {
        $this->add_point_information( $order );
        $this->add_tracking_information( $order );
    }

    public static function add_checkout_button_product()
    {
        if ( Furgonetka_Admin::is_checkout_active() === false  ||
            Furgonetka_Admin::is_product_page_button_visible() === false) {
            return;
        }

        $dataProductId = '';
        self::setup_classes();

        $classes = [
            self::$base_class,
            self::$backend_class
        ];

        if ( is_single() ) {
            $dataProductId = 'data-product-id="' . get_the_ID() . '"';
            $classes[] = self::$product_class;
        }

        echo '<div class="' . implode(' ', $classes) . '" ' . $dataProductId . '></div>';
    }

    public static function add_checkout_button_order()
    {
        if ( Furgonetka_Admin::is_checkout_active() === false) {
            return;
        }

        self::setup_classes();

        $classes = [
            self::$base_class,
            self::$backend_class,
            self::$order_class
        ];

        echo '<div class="' . implode(' ', $classes) . '"></div>';
    }

    public static function add_checkout_button_shopping_cart_widget( $args = null )
    {
        if ( Furgonetka_Admin::is_checkout_active() === false ) {
            return;
        }

        self::setup_classes();

        $classes = [
            self::$widget_class,
            self::$backend_class,
            self::$cart_class
        ];

        echo '<div class="' . implode(' ', $classes) . '"></div>';
    }

    public static function add_checkout_button_shopping_minicart_widget( $args = null )
    {
        if ( Furgonetka_Admin::is_checkout_active() === false ) {
            return;
        }

        self::setup_classes();

        $classes = [
            self::$minicart_widget_class,
            self::$backend_class,
            self::$minicart_class
        ];

        echo '<div class="' . implode(' ', $classes) . '"></div>';
    }

    public static function add_hidden_container_for_cart_widget()
    {
        if ( Furgonetka_Admin::is_checkout_active() === false ) {
            return;
        }

        self::setup_classes();

        $classes = [
            self::$hidden_class,
            self::$hidden_class . '__backend'
        ];

        echo '<div style="display:none;" class="' . implode(' ', $classes) . '">';
        echo '<script>window.dispatchEvent(new Event("furgonetka_checkout_shopping_cart_widget_ready"));</script>';
        echo '</div>';
    }

    public function clear_cart()
    {
        WC()->cart->empty_cart();
    }

    public function init()
    {
        //add_action( 'wp_loaded', array( $this, 'rest_api_includes' ), 5 );
        add_action( 'rest_api_init', array( $this, 'register_rest_api_endpoints' ) );
    }

    /**
     * Furgonetka permission callback
     *
     * @see \Furgonetka_Endpoint_Abstract permission_callback method
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function permission_callback( \WP_REST_Request $request )
    {
        // Auth header.
        if ( ! empty( $request->get_header( 'authorization' ) ) ) {

            $auth_data = str_replace( 'Basic ', '', $request->get_header( 'authorization' ) );
            //phpcs:ignore
            $auth_array = explode( ':', base64_decode( $auth_data ) );

            $key    = $auth_array[0];
            $secret = $auth_array[1];

            // Query params.
        } else {
            $request_params = $request->get_query_params();

            $key    = $request_params['consumer_key'];
            $secret = $request_params['consumer_secret'];
        }

        if ( Furgonetka_Admin::get_rest_customer_key() === $key
            && password_verify( $secret, Furgonetka_Admin::get_rest_customer_secret() )
        ) {
            return true;
        }

        return false;
    }

    public function permission_callback_furgonetka_rest_api(): bool
    {
        apply_filters( 'determine_current_user', get_current_user_id() );

        return Furgonetka_Capabilities::current_user_can_manage_furgonetka();
    }

    public function is_request_to_furgonetka_rest_api( $access_granted ): bool
    {
        /**
         * Pass already authorized user
         */
        if ( $access_granted ) {
            return true;
        }

        /**
         * Check access to Furgonetka API
         */
        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return false;
        }

        $rest_prefix = trailingslashit( rest_get_url_prefix() );
        $request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

        /**
         * Check supported endpoints
         */
        $supported_endpoints = array(
            'furgonetka/v1/map/',
        );

        foreach ( $supported_endpoints as $endpoint ) {
            if ( strpos( $request_uri, $rest_prefix . $endpoint ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Auth API permission callback
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function permission_callback_auth_api( $request )
    {
        $data = $request->get_json_params();

        if ( ! isset( $data[ 'user_id' ]) ) {
            return false;
        }

        return $this->verify_auth_api_nonce( $data[ 'user_id' ] );
    }

    /**
     * Verify & discard nonce with the saved one
     *
     * @param $nonce
     * @return bool
     */
    private function verify_auth_api_nonce( $nonce )
    {
        $stored_nonce = get_option( $this->plugin_name . '_auth_api_nonce' );

        if ( ! $stored_nonce ) {
            return false;
        }

        delete_option( $this->plugin_name . '_auth_api_nonce' );

        return $nonce === $stored_nonce;
    }

    /**
     * Get name of service
     *
     * @param string $service
     * @param string $service_type
     *
     * @return string
     */
    public static function get_service_name($service, $service_type)
    {
        return $service_type === self::POINT_TYPE_PARCEL_MACHINE ?
            self::get_service_name_for_parcel_machine($service) : self::get_service_name_for_service_point($service);
    }

    private static function get_service_name_for_parcel_machine( $service )
    {
        $result = '';

        switch ( $service ) {
            case self::SERVICE_INPOST:
                $result = 'Paczkomat® 24/7';
                break;
            case self::SERVICE_POCZTA:
                $result = 'Pocztex AUTOMAT';
                break;
            case self::SERVICE_DPD:
                $result = 'DPD Pickup Station';
                break;
            case self::SERVICE_DHL:
                $result = 'DHL BOX 24/7';
                break;
            case self::SERVICE_ORLEN:
                $result = 'ORLEN PACZKA Automat paczkowy';
                break;
            case 'kiosk':
                $result = 'ORLEN PACZKA Automat paczkowy';
                break;
        }

        return $result;
    }

    private static function get_service_name_for_service_point( $service )
    {
        $result = '';

        switch ( $service ) {
            case self::SERVICE_INPOST:
                $result = 'PaczkoPunkt';
                break;
            case self::SERVICE_POCZTA:
                $result = 'Pocztex';
                break;
            case self::SERVICE_DPD:
                $result = 'DPD Pickup';
                break;
            case self::SERVICE_DHL:
                $result = 'DHL POP';
                break;
            case self::SERVICE_ORLEN:
                $result = 'ORLEN PACZKA Punkt odbioru';
                break;
            case 'kiosk':
                $result = 'ORLEN PACZKA Punkt odbioru';
                break;
            case self::SERVICE_FEDEX:
                $result = 'FedEx Punkt';
                break;
            case self::SERVICE_UPSACCESSPOINT:
                $result = 'UPS Access Point';
                break;
            case self::SERVICE_GLS:
                $result = 'GLS Szybka Paczka';
                break;
        }

        return $result;
    }

    public function register_rest_api_endpoints()
    {/*
        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/all_in_one',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_all_in_one' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/cart',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_cart_items' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/shippings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_shipping' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/payments',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_payments' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/coupons',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_coupons' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/totals',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_totals' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/cart/shipping-method',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( new Furgonetka_Cart(), 'get_cart_shipping_method' ),
                'permission_callback' => array( $this, 'permission_callback' ),
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/cart/add_coupon',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( new Furgonetka_Cart(), 'maybe_add_coupon' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/cart/remove_coupons',
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( new Furgonetka_Cart(), 'remove_coupons' ),
                'permission_callback' => '__return_true',
            )
        );
*/
        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/checkout/settings',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( new Furgonetka_Settings(), 'updateSettings' ),
                'permission_callback' => array( $this, 'permission_callback' ),
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/authorize/callback',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( new Furgonetka_Settings(), 'authorize_callback' ),
                'permission_callback' => array( $this, 'permission_callback_auth_api' ),
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/map/zones',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( new Furgonetka_Map_Settings(), 'get_zones' ),
                'permission_callback' => array( $this, 'permission_callback_furgonetka_rest_api' ),
            )
        );

        register_rest_route(
            FURGONETKA_REST_NAMESPACE,
            '/map/configuration',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( new Furgonetka_Map_Settings(), 'get_configuration' ),
                    'permission_callback' => array( $this, 'permission_callback_furgonetka_rest_api' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( new Furgonetka_Map_Settings(), 'post_configuration' ),
                    'permission_callback' => array( $this, 'permission_callback_furgonetka_rest_api' ),
                    'args'                => array(
                        'configuration' => array(
                            'description'       => 'Map configuration',
                            'validate_callback' => array( new Furgonetka_Map_Settings(), 'validate_post_configuration' ),
                            'required'          => true,
                        ),
                    ),
                ),
            )
        );
    }

    function rest_api_includes()
    {
        if ( empty( WC()->cart ) ) {
            WC()->frontend_includes();
            wc_load_cart();
        }
    }

    /**
     * WooCommerce Payments (express checkout - Apple Pay & Google Pay) support
     */
    public function is_woocommerce_payments_express_checkout_request(): bool
    {
        return isset( $_POST[ 'wcpay-payment-method' ] );
    }

    public function woocommerce_get_checkout_url_filter( $url = null )
    {
        return self::FURGONETKA_CHECKOUT_REPLACEMENT_URL;
    }

    public static function setup_classes() {
        self::$base_class = self::$class_base . '-container';
        self::$widget_class = self::$class_base . '-container-widget';
        self::$minicart_widget_class = self::$class_base . '-container-minicart-widget';
        self::$backend_class = self::$class_base . '__wp';
        self::$frontend_class = self::$class_base . '__js';
        self::$userdefined_class = self::$class_base . '__user-defined';
        self::$builder_class = self::$class_base . '__builder';
        self::$product_class = self::$class_base . '__product';
        self::$cart_class = self::$class_base . '__cart';
        self::$minicart_class = self::$class_base . '__minicart';
        self::$order_class = self::$class_base . '__order';
    }
}
