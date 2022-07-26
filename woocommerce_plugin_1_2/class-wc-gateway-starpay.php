<?php
if (!defined('ABSPATH'))
    exit (); // Exit if accessed directly

class WC_Gateway_StarPay extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = WC_StarPay_ID;
        $this->icon = WC_StarPay_URL . '/images/logo.png';
        $this->has_fields = false;

        $this->method_title = 'WeChat & Alipay+ Pay via StarPay';
        $this->method_description = 'Take payments in WeChat via StarPay';

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');

        $lib = 'starpay-php-sdk/';
        if (!class_exists('StarPayAPI')) {
            include_once($lib . 'StarPayAPI.php');
        } elseif (!class_exists('StarPayConfig')) {
            include_once($lib . 'StarPayConfig.php');
        }

        $config = StarPayConfig::getInstance();
        $config->setACCESSID($this->get_option('starpay_access_id'));
        $config->setMCHACCESSNUMBER($this->get_option('starpay_merchant_number'));
        $config->setSECRETKEY($this->get_option('starpay_secret_key'));
    }

    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable'),
                'type' => 'checkbox',
                'label' => __('Enable WeChat & Alipay+ Payment via StarPay'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.'),
                'default' => __('WeChat & Alipay+ Pay'),
                'css' => 'width:400px'
            ),
            'description' => array(
                'title' => __('Description'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.'),
                'default' => __('Take payments in WeChat'),
                //'desc_tip' => true ,
                'css' => 'width:400px'
            ),
            'starpay_access_id' => array(
                'title' => __('StarPay Access ID'),
                'type' => 'text',
                'description' => __('Please enter the StarPay Access ID,If you don\'t have one, <a href="https://portal.starpayes.com/" target="_blank">click here</a> to get.'),
                'css' => 'width:400px'
            ),
            'starpay_merchant_number' => array(
                'title' => __('StarPay Merchant Number'),
                'type' => 'text',
                'description' => __('Please enter the Merchant Number,If you don\'t have one, <a href="https://portal.starpayes.com/" target="_blank">click here</a> to get.'),
                'css' => 'width:400px'
            ),
            'starpay_secret_key' => array(
                'title' => __('StarPay Secret Key'),
                'type' => 'textarea',
                'description' => __('Please enter your StarPay Secret Key. This is needed in order to take payment.'),
                'css' => 'width:400px'
            )
        );
    }

    public function process_payment($order_id)
    {
        $order = new WC_Order ($order_id);
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    public function check_response($data)
    {
        $api = new StarPayAPI();
        if (!$api->isPaymentCallback($data)) {
            return false;
        }

        $content = json_decode(stripslashes($data['content']), true);
        $orderNoArray = explode('-', $content['merOrderNo']);
        $orderNo = $orderNoArray[0];
        $order = new WC_Order($orderNo);
        if ($order->needs_payment()) {
            $order->payment_complete();
        }
        return true;
    }

    function wp_enqueue_scripts()
    {
        $orderId = get_query_var('order-pay');
        $order = new WC_Order ($orderId);
        $payment_method = $order->get_payment_method();
        if ($this->id == $payment_method &&
            is_checkout_pay_page() &&
            !isset ($_GET ['pay_for_order'])) {
            wp_enqueue_script('STARPAY_JS_CHECKOUT', WC_StarPay_URL . '/js/checkout.js');
            wp_localize_script('STARPAY_JS_CHECKOUT', 'wc_checkout_starpay_params', array(
                'ajax_url' => add_query_arg('wc-api', WC_StarPay_ID . '-payment-status', home_url('/')),
                'order_id' => $orderId
            ));
        }
    }

    public function get_order_status()
    {
        $order_id = isset($_POST ['orderId']) ? $_POST ['orderId'] : '';
        $order = new WC_Order ($order_id);
        $isPaid = !$order->needs_payment();

        die(json_encode(
            array(
                'status' => $isPaid ? 'paid' : 'unpaid',
                'url' => $this->get_return_url($order)
            )
        ));
    }

    public function receipt_page_qc_code($order_id)
    {
        $order = new WC_Order($order_id);
        if (!$order || $order->is_paid()) {
            return;
        }

        $date = new DateTime();
        $order_id_with_time = $order_id . '-' . $date->getTimestamp();
        $title = $this->get_order_title($order_id);
        $total = $order->get_total();

        $error_msg = null;
        $qr_code = null;
        $callback_url = add_query_arg('wc-api', WC_StarPay_ID, home_url('/'));
        $api = new StarPayAPI();
        $result = $api->scanToPay($order_id_with_time, $total, $title, $callback_url, $order->get_currency());
        if ($result['error'])
            $error_msg = $result['error'];
        else
            $qr_code = $result['content']['coreImgForBase64']
        ?>
        <style type="text/css">

            .pay-weixin-design {
                display: block;
                background: #fff; /*padding:100px;*/
                overflow: hidden;
                text-align: center;
            }

            .page-wrap {
                padding: 50px 0;
                min-height: auto !important;
            }

            .pay-weixin-design #WxQRCode {
                width: 196px;
                height: auto
            }

            .pay-weixin-design .p-w-center {
                display: block;
                overflow: hidden;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .pay-weixin-design .p-w-center h3 {
                font-family: Arial, 微软雅黑;
                margin: 0 auto 10px;
                display: block;
                overflow: hidden;
            }

            .pay-weixin-design .p-w-center h3 font {
                display: block;
                font-size: 14px;
                font-weight: bold;
                float: left;
                margin: 10px 10px 0 0;
            }

            .pay-weixin-design .p-w-center h3 strong {
                position: relative;
                text-align: center;
                line-height: 40px;
                border: 2px solid #3879d1;
                display: block;
                font-weight: normal;
                width: 130px;
                height: 44px;
                float: left;
            }

            .pay-weixin-design .p-w-center h3 strong #img1 {
                margin-top: 10px;
                display: inline-block;
                width: 22px;
                vertical-align: top;
            }

            .pay-weixin-design .p-w-center h3 strong span {
                display: inline-block;
                font-size: 14px;
                vertical-align: top;
            }

            .pay-weixin-design .p-w-center h3 strong #img2 {
                position: absolute;
                right: 0;
                bottom: 0;
            }

            .pay-weixin-design .p-w-center h4 {
                font-family: Arial, 微软雅黑;
                margin: 0;
                font-size: 14px;
                color: #666;
            }

            .pay-weixin-design .p-w-left {
                display: block;
                overflow: hidden;
                float: left;
                margin-left: 100px;
            }

            .pay-weixin-design .p-w-left p {
                display: block;
                width: 196px;
                background: #00c800;
                color: #fff;
                text-align: center;
                line-height: 2.4em;
                font-size: 12px;
            }

            .pay-weixin-design .p-w-left img {
                margin-bottom: 10px;
            }

            .pay-weixin-design .p-w-right {
                margin-right: 100px;
                display: block;
                float: right;
            }
        </style>
        <div class="pay-weixin-design">
            <div class="p-w-center">
                <h3>
                    <font>支付方式已选择微信支付</font>
                    <strong>
                        <img id="img1" src="<?php print WC_StarPay_URL ?>/images/weixin.png">
                        <span>微信支付</span>
                        <img id="img2" src="<?php print WC_StarPay_URL ?>/images/ep_new_sprites1.png">
                    </strong>
                </h3>
                <h4>通过微信首页右上角扫一扫，或者在“发现-扫一扫”扫描二维码支付。本页面将在支付完成后自动刷新。</h4>
                <span style="color:red;"><?php print $error_msg ?></span>
            </div>
            <div class="p-w-left">
                <div id="starpay-payment-pay-img"
                     style="width:200px;height:200px;padding:10px;">
                    <img id="img-qr-code" src="data:image/png;base64, <?php echo $qr_code; ?>">
                </div>
                <p>使用微信扫描二维码进行支付</p>
            </div>
            <div class="p-w-right">
                <img src="<?php print WC_StarPay_URL ?>/images/ep_sys_wx_tip.jpg">
            </div>
        </div>
        <?php
    }

    private function get_order_title($order_id)
    {
        $name = get_bloginfo('name');
        $title = $name . " | " . "#{$order_id}";
        $limit = 250;
        $title = mb_strimwidth($title, 0, $limit, "utf-8");
        return $title;
    }
}