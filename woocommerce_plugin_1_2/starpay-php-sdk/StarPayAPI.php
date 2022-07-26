<?php
require_once('StarPayConfig.php');
require_once('StarPayUtils.php');

class StarPayAPI
{
    private $config;
    private $api;

    public function __construct()
    {
        $this->config = StarPayConfig::getInstance();
        $this->api = new StarPayUtils();
    }

    public function scanToPay(string $orderNO, float $orderAmt, string $subject, string $bgRetUrl, string $currency = 'EUR')
    {
        $resultContent = array();
        $request_type = '2010';
        $orderAmt = (int)($orderAmt * 100);
        $content = array(
            'orderNo' => $orderNO,
            'orderAmt' => $orderAmt,
            'subject' => $subject,
            'currency' => $currency,
            'bgRetUrl' => $bgRetUrl,
        );
        $result = $this->api->request($request_type, $content);
        if (array_key_exists('content', $result))
            $result['content'] = json_decode($result['content'], true);
        return $result;
    }

    public function webOnlinePay(string $orderNO, float $orderAmt, string $subject,
                                 string $bgRetUrl,String $retUrl,String $channelType,String $osType, String $storeNo = '000', string $currency = 'EUR')
    {
        $resultContent = array();
        $request_type = '2013';
        $orderAmt = (int)($orderAmt * 100);
        $content = array(
            'orderNo' => $orderNO,
            'orderAmt' => $orderAmt,
            'subject' => $subject,
            'currency' => $currency,
            'storeNo' => $storeNo,
            'bgRetUrl' => $bgRetUrl,
            'retUrl' => $retUrl,
            'channelType' => $channelType,
            'osType' => $osType,
        );
        $result = $this->api->request($request_type, $content);
        if (array_key_exists('content', $result))
            $result['content'] = json_decode($result['content'], true);
        return $result;
    }

    public function isPaymentCallback(array $post)
    {
        if (empty($post)) {
            return false;
        } elseif (!isset($post['type'])) {
            return false;
        } elseif (strcmp($post['type'], '2005')) {
            return false;
        } elseif (!isset($post['content'])) {
            return false;
        }
        return true;
    }

    public function verifyCallback(array $post, string $orderNO, string $orderAmt)
    {
        // todo verify

        return $post['content'];
    }
}