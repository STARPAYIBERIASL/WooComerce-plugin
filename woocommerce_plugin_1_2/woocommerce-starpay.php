<?php
/*
 * Plugin Name: StarPay Payment for WooCommerce
 * Plugin URI: https://starpay.es
 * Description: StarPay Payment for WooCommerce
 * Version: 1.2.0
 * Author: StarPay
 * Author URI: https://starpay.es
 * Text Domain: StarPay Payment for WooCommerce
 * WC tested up to: 9.9.9
 */

if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly

define('WC_StarPay_ID', 'wc-starpay-payment');
define('WC_StarPay_URL', plugins_url('', __FILE__));

add_action('plugins_loaded', 'starpay_wc_payment_gateway_init');
function starpay_wc_payment_gateway_init()
{
    // Use PHP Composer
    require __DIR__ . '/vendor/autoload.php';
    Sentry\init(['dsn' => 'https://36a67ca3d1fb4ad0a5000397b43856a5@o386986.ingest.sentry.io/5221808']);

    require_once 'class-wc-gateway-starpay.php';
    $wc_gateway_starpay = new WC_Gateway_StarPay();

    Sentry\init(['dsn' => 'https://36a67ca3d1fb4ad0a5000397b43856a5@o386986.ingest.sentry.io/5221808']);

    add_action('woocommerce_update_options_payment_gateways_' . $wc_gateway_starpay->id, array($wc_gateway_starpay, 'process_admin_options'));
    add_action('woocommerce_receipt_' . $wc_gateway_starpay->id, array($wc_gateway_starpay, 'receipt_page_qc_code'));
    add_action('wp_ajax_nopriv_starpay_payment_get_order', array($wc_gateway_starpay, "get_order_status"));
    add_action('wp_enqueue_scripts', array($wc_gateway_starpay, 'wp_enqueue_scripts'));
}

add_filter('woocommerce_payment_gateways', 'woocommerce_starpay_add_gateway');
function woocommerce_starpay_add_gateway($methods)
{
    $methods[] = 'WC_Gateway_StarPay';
    return $methods;
}

add_action('woocommerce_api_' . WC_StarPay_ID, 'starpay_notify');
function starpay_notify()
{
    $gateway = new WC_Gateway_StarPay();
    if ($gateway->check_response($_POST)) {
        die("SUCCESS");
    } else {
        Sentry\captureMessage('Callback Failed');
        die("FAIL");
    }
}

add_action('woocommerce_api_' . WC_StarPay_ID . '-payment-status', 'starpay_query');
function starpay_query()
{
    if (isset($_POST['orderId'])) {
        $orderId = $_POST['orderId'];
        $order = new WC_Order ($orderId);
        $isPaid = !$order->needs_payment();

        die(json_encode(
            array(
                'status' => $isPaid ? 'paid' : 'unpaid',
                'url' => $order->get_checkout_order_received_url()
            )
        ));
    } else {
        Sentry\captureMessage('Check Payment Status Failed');
        die("ERROR");
    }
}
