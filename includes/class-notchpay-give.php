<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package    Give
 * @subpackage Gateways
 * @author     Chapdel KAMGA <chapdel@notchpay.co>
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @link       https://notchpay.co
 * @since      1.0.0
 */

use Give\Framework\PaymentGateways\Commands\RespondToBrowser;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    NotchPay_Give
 * @subpackage NotchPay_Give/includes
 * @author     Notch Pay <hello@notchpay.co>
 */

class notchpay_give_plugin_tracker
{
    var $public_key;
    var $plugin_name;
    function __construct($plugin, $pk)
    {
        //configure plugin name
        //configure public key
        $this->plugin_name = $plugin;
        $this->public_key = $pk;
    }



    function log_transaction_success($trx_ref)
    {
        //send reference to logger along with plugin name
        $url = "https://api.notchpay.co/log/charge_success";

    
        
        $body = wp_json_encode( [
            'plugin_name'  => $this->plugin_name,
            'transaction_reference' => $trx_ref,
        ] );
        
        $options = [
            'body'        => $body,
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        ];
        
        wp_remote_post( $url, $options );
    }
}
class NotchPay_Give
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    NotchPay_Give_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    const API_QUERY_VAR = 'notchpay-give-api';

    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if (defined('NP_GIVE_PLUGIN_NAME_VERSION')) {
            $this->version = NP_GIVE_PLUGIN_NAME_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'notchpay-give';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - NotchPay_Give_Loader. Orchestrates the hooks of the plugin.
     * - NotchPay_Give_i18n. Defines internationalization functionality.
     * - NotchPay_Give_Admin. Defines all hooks for the admin area.
     * - NotchPay_Give_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-notchpay-give-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-notchpay-give-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin
         * area.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-notchpay-give-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-notchpay-give-public.php';

        $this->loader = new NotchPay_Give_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the NotchPay_Give_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function set_locale()
    {

        $plugin_i18n = new NotchPay_Give_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new NotchPay_Give_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Add menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

        // Add Settings link to the plugin
        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
        $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');

        /**
         * Register gateway so it shows up as an option in the Give gateway settings
         *
         * @param array $gateways
         *
         * @return array
         */
        function give_notchpay_register_gateway($gateways)
        {
            $gateways['notchpay'] = array(
                'admin_label' => esc_attr__('Notch Pay', 'notchpay-give'),
                'checkout_label' => esc_attr__('Notch Pay', 'notchpay-give'),
            );
            return $gateways;
        }

        add_filter('give_payment_gateways', 'give_notchpay_register_gateway', 1);

        /**
         * Filter the currencies
         * Note: you can register new currency by using this filter
         *
         * @since 1.8.15
         *
         * @param array $currencies
         */
        function give_notchpay_add_currencies($currencies)
        {
            return  $currencies;
        }

        add_filter('give_currencies', 'give_notchpay_add_currencies');

        add_action('parse_request', array($this, 'handle_api_requests'), 0);
    }

    public function handle_api_requests()
    {

        global $wp;
        if (!empty($_GET[NotchPay_Give::API_QUERY_VAR])) { // WPCS: input var okay, CSRF ok.
            $wp->query_vars[NotchPay_Give::API_QUERY_VAR] = sanitize_key(wp_unslash($_GET[NotchPay_Give::API_QUERY_VAR])); // WPCS: input var okay, CSRF ok.

            $key = $wp->query_vars[NotchPay_Give::API_QUERY_VAR];
            if ($key && ($key === 'verify') && isset($_GET['reference'])) {
                // handle verification here
                $this->verify_transaction();
                die();
            }
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_public_hooks()
    {

        $plugin_public = new NotchPay_Give_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        function give_notchpay_credit_card_form($form_id, $echo = true)
        {
            $billing_fields_enabled = give_get_option('notchpay_billing_details');

            if ($billing_fields_enabled == 'enabled') {
                do_action('give_after_cc_fields');
            } else {
                //Remove Address Fields if user has option enabled
                remove_action('give_after_cc_fields', 'give_default_cc_address_fields');
            }
            return $form_id;
        }
        add_action('give_notchpay_cc_form', 'give_notchpay_credit_card_form');

        /**
         * This action will run the function attached to it when it's time to process the donation
         * submission.
         **/
        function give_process_notchpay_purchase($purchase_data)
        {


            // Make sure we don't have any left over errors present.
            give_clear_errors();

            // Any errors?
            $errors = give_get_errors();
            if (!$errors) {

                $form_id         = intval($purchase_data['post_data']['give-form-id']);
                $price_id        = !empty($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : 0;
                $donation_amount = !empty($purchase_data['price']) ? $purchase_data['price'] : 0;

                $payment_data = array(
                    'price' => $donation_amount,
                    'give_form_title' => $purchase_data['post_data']['give-form-title'],
                    'give_form_id' => $form_id,
                    'give_price_id' => $price_id,
                    'date' => $purchase_data['date'],
                    'user_email' => $purchase_data['user_email'],
                    'purchase_key' => $purchase_data['purchase_key'],
                    'currency' => give_get_currency(),
                    'user_info' => $purchase_data['user_info'],
                    'status' => 'pending',
                    'gateway' => 'notchpay',
                );

                // Record the pending payment
                $payment = give_insert_payment($payment_data);

            
                if (!$payment) {
                    // Record the error

                    give_record_log(__('Payment Error', 'give'), sprintf(__('Payment creation failed before sending donor to Notch Pay. Payment data: %s', 'give'), json_encode($payment_data)), $payment);
                    // Problems? send back
                    give_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['give-gateway'] . "&message=-some weird error happened-&payment_id=" . json_encode($payment));
                } else {

                    
        

                    //Begin processing payment

                    if (give_is_test_mode()) {
                        $public_key = give_get_option('notchpay_test_public_key');
                    } else {
                        $public_key = give_get_option('notchpay_live_public_key');
                    }

                    $ref = $purchase_data['purchase_key']; // . '-' . time() . '-' . preg_replace("/[^0-9a-z_]/i", "_", $purchase_data['user_email']);
                    $currency = give_get_currency();

                    $verify_url = home_url() . '?' . http_build_query(
                        [
                            NotchPay_Give::API_QUERY_VAR => 'verify',
                            'reference' => $ref,
                        ]
                    );

                    //----------
                    $url = "https://api.notchpay.co/payments/initialize";    

                   
                    $body = wp_json_encode( [
                        'email' => $payment_data['user_email'],
                        'name' =>  isset($payment_data['user_info'])?  (isset($payment_data['user_info']['first_name']) ? $payment_data['user_info']['first_name'] : null ) : null,
                        'amount' => $payment_data['price'],
                        'reference' => $ref,
                        'callback' => $verify_url,
                        'currency' => $currency,

                    ] );
        
                    $args = [
                        'body'        => $body,
                        'headers'     => [
                            'Content-Type' => 'application/json',
                            'Authorization' => $public_key,
                        ],
                        'sslverify'   => false,
                        'timeout' => 60,
                    ];


                $request = wp_remote_post($url, $args);

                if (!is_wp_error($request) && 201 === wp_remote_retrieve_response_code($request)) {

                    $notchpay_response = json_decode(wp_remote_retrieve_body($request));

                    wp_redirect($notchpay_response->authorization_url);

                    exit;
                } else {
                    exit;
                }
                }
            } else {
                give_send_back_to_checkout('?payment-mode=notchpay' . '&errors=' . json_encode($errors));
            }
        }

        add_action('give_gateway_notchpay', 'give_process_notchpay_purchase');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @return string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  1.0.0
     * @return NotchPay_Give_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @return string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    public function Verify_transaction()
    {

        if (!isset($_GET['notchpay_trxref'], $_GET['reference'], $_GET['status'])) {
            die('not a valid response');
        }



        $give_ref = sanitize_text_field($_GET['notchpay_trxref']);
        $ref = sanitize_text_field($_GET['reference']);
        $status = sanitize_text_field($_GET['status']);
        $payment = give_get_payment_by('key', $give_ref);
        // die(json_encode($payment));

        if ($payment === false) {
            die('not a valid ref');
        }
        if (give_is_test_mode()) {
            $public_key = give_get_option('notchpay_test_public_key');
        } else {
            $public_key = give_get_option('notchpay_live_public_key');
        }

        $url = "https://api.notchpay.co/payments/" . $ref;

        $args = array(
            'headers' => array(
                'Authorization' =>  $public_key,
            ),
        );

        $request = wp_remote_get($url, $args);

        if (is_wp_error($request)) {
            return false; // Bail early
        }

        $body = wp_remote_retrieve_body($request);

        $result = json_decode($body);

        // var_dump($result);

        if ($result->transaction->status == 'complete') {


            //PSTK Logger
            if (give_is_test_mode()) {
                $pk = give_get_option('notchpay_test_public_key');
            } else {
                $pk = give_get_option('notchpay_live_public_key');
            }
            $pstk_logger =  new notchpay_give_plugin_tracker('give', $pk);
            $pstk_logger->log_transaction_success($ref);
            //


            // the transaction was successful, you can deliver value

            give_update_payment_status($payment->ID, 'complete');
            //             echo json_encode(
            //                 [
            //                     'url' => give_get_success_page_uri(),
            //                     'status' => 'given',
            //                 ]
            //             );
            wp_redirect(give_get_success_page_uri());
            exit;
        } else {

            switch ($result->transaction->status) {
                case 'rejected':
                    $error_message = "Payment rejected on Notch Pay";
                    break;
                case 'canceled':
                    $error_message = "Payment canceled on Notch Pay";
                    break;
                default:
                    $error_message = "Payment failed on Notch Pay";
                    break;
            }
            
            // the transaction was not successful, do not deliver value'
            give_update_payment_status($payment->ID, 'failed');
            give_insert_payment_note($payment, 'ERROR: ' . $error_message);

            wp_redirect(give_get_failed_transaction_uri());
            exit;
        }
    }
}
