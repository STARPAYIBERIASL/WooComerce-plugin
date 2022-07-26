<?php

class StarPayConfig
{
    private static $_instance = NULL;
    private $VERSION = '1.0';
    private $ACCESS_ID = '';
    private $MCH_ACCESS_NUMBER = '';
    private $SECRET_KEY = '';
    private $API_URL = 'https://api.starpayes.com/aps-gateway/entry.do';

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return string
     */
    public function getSECRETKEY()
    {
        return $this->SECRET_KEY;
    }

    /**
     * @param string $SECRET_KEY
     */
    public function setSECRETKEY($SECRET_KEY)
    {
        $this->SECRET_KEY = $SECRET_KEY;
    }

    /**
     * @return string
     */
    public function getMCHACCESSNUMBER()
    {
        return $this->MCH_ACCESS_NUMBER;
    }

    /**
     * @param string $MCH_ACCESS_NUMBER
     */
    public function setMCHACCESSNUMBER($MCH_ACCESS_NUMBER)
    {
        $this->MCH_ACCESS_NUMBER = $MCH_ACCESS_NUMBER;
    }

    /**
     * @return string
     */
    public function getACCESSID()
    {
        return $this->ACCESS_ID;
    }

    /**
     * @param string $ACCESS_ID
     */
    public function setACCESSID($ACCESS_ID)
    {
        $this->ACCESS_ID = $ACCESS_ID;
    }

    /**
     * @return string
     */
    public function getVERSION()
    {
        return $this->VERSION;
    }

    /**
     * @param string $VERSION
     */
    public function setVERSION($VERSION)
    {
        $this->VERSION = $VERSION;
    }

    /**
     * @return string
     */
    public function getAPIURL(): string
    {
        return $this->API_URL;
    }

    /**
     * @param string $API_URL
     */
    public function setAPIURL(string $API_URL): void
    {
        $this->API_URL = $API_URL;
    }
}