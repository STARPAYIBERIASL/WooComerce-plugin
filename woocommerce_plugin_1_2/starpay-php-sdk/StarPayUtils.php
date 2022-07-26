<?php
require_once('StarPayConfig.php');

class StarPayUtils
{
    private $config;
    public static $charset = 'UTF-8';

    public function __construct()
    {
        $this->config = StarPayConfig::getInstance();
    }

    public function request(string $request_type = '1004', array $content = array(), string $url = null)
    {
        $now = new DateTime();
        $timestamp = $now->format('Y-m-d H:i:s');
        $params = array(
            'access_id' => $this->config->getACCESSID(),
            'version' => $this->config->getVERSION(),
            'timestamp' => $timestamp,
            'type' => $request_type,
            'content' => $content,
            'format' => 'JSON',
        );
        self::reduceParams($params);
        $params['sign'] = self::signData($params);
        $headers = array('content-type: application/x-www-form-urlencoded;charset=' . self::$charset . ';boundary=' . self::getMillisecond());
        $data = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, empty($url) ? $this->config->getAPIURL() : $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function signData(array $paramsData)
    {
        $signature = '';
        $paramsData['content'] = md5($paramsData['content']);
        $data = self::buildQuery($paramsData);
        $secret_key = $this->config->getSECRETKEY();
        #$key_id = openssl_pkey_get_private($secret_key);
        #openssl_sign($data, $signature, $key_id, OPENSSL_ALGO_SHA256);
        openssl_sign($data, $signature, $secret_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function reduceParams(array &$paramsData)
    {
        $paramsData['content']['merchantAccessNo'] = $this->config->getMCHACCESSNUMBER();
        $paramsData['content'] = json_encode($paramsData['content']);
        $paramsData = array_filter($paramsData);
        ksort($paramsData);
    }

    private static function buildQuery(array $paramsData)
    {
        $params = array();
        foreach ($paramsData as $key => $value) {
            if (!empty($value))
                $params[] = $key . '=' . $value;
        }
        $data = implode('&', $params);
        return $data;
    }

    private static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}
