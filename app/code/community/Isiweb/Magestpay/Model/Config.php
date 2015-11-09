<?php
/**
 * Isiweb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@isiweb.it so we can send you a copy immediately.
 *
 * @category    Isiweb
 * @package     Isiweb_Magestpay
 * @copyright   Copyright (c) 2015 Isiweb S.r.l. (http://www.isiweb.it)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Isiweb_Magestpay_Model_Config
{

	const METHOD_GESTPAY 				= 'gestpay';
	const GESTPAY_PROTOCOL				= 'https';
	const GESTPAY_HOST					= 'ecomm.sella.it';
	const GESTPAY_HOST_TEST				= 'testecomm.sella.it';
	const GESTPAY_PATH					= '/pagam/pagam.aspx';
	const GESTPAY_WSDL_HOST				= 'ecomms2s.sella.it';
	const GESTPAY_WSDL_PATH				= '/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL';
    const TRANSACTION_RESULT_OK 		= 'OK';
    const TRANSACTION_RESULT_KO 		= 'KO';
    const TRANSACTION_RESULT_PENDING 	= 'XX';
	const GESTPAY_CURRENCIES			= '{"USD":"1","GBP":"2","CHF":"3","DKK":"7","NOK":"8","SEK":"9","CAD":"12","ITL":"18","JPY":"71","HKD":"103","BRL":"234","EUR":"242"}';
	
	
    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId = null;

	/**
     * Set method and store id, if specified
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            if ($params) {
                $storeId = array_shift($params);
                $this->setStoreId($storeId);
            }
        }
    }
	
    /**
     * Method code setter
     *
     * @param string|Mage_Payment_Model_Method_Abstract $method
     * @return Isiweb_Magestpay_Model_Config
     */
    public function setMethod($method)
    {
        if ($method instanceof Mage_Payment_Model_Method_Abstract) {
            $this->_methodCode = $method->getCode();
        } elseif (is_string($method)) {
            $this->_methodCode = $method;
        }
        return $this;
    }
	
    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Store ID setter
     *
     * @param int $storeId
     * @return Isiweb_Magestpay_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }
	
    /**
     * Check whether method active in configuration
     *
     * @param string $method Method code
     * @return bool
     */
    public function isMethodActive($method)
    {
        if ( Mage::getStoreConfigFlag("payment/{$method}/active", $this->_storeId)) 
		{
            return true;
        }
        return false;
    }

    /**
     * Check whether method available for checkout or not
     * Logic based configuration data and extension (SOAP)
     *
     * @param string $method Method code
     * @return bool
     */
    public function isMethodAvailable($methodCode = null)
    {
        if ($methodCode === null) {
            $methodCode = $this->getMethodCode();
        }

        $result = true;

        if (!$this->isMethodActive($methodCode) || !extension_loaded('soap')) 
		{
            $result = false;
        }

        return $result;
    }

    /**
     * Check whether method available for checkout in
	 * requested currency code
     *
     * @param string $method Currency Code
     * @return bool
     */
	public function isCurrencySupported($currencyIsoCode)
	{
		return ($this->getUicCodeFromCurrency($currencyIsoCode) == '' ? false : true);
	}

    /**
     * Returns the proper UicCode from given Currency Code
     *
     * @param string $method Currency Code
     * @return mixed
     */
	public function getUicCodeFromCurrency($currencyIsoCode)
	{
		// $currencies = array (
		// "USD" => '1',
		// "GBP" => '2',
		// "CHF" => '3',
		// "DKK" => '7',
		// "NOK" => '8',
		// "SEK" => '9',
		// "CAD" => '12',
		// "ITL" => '18',
		// "JPY" => '71',
		// "HKD" => '103',
		// "BRL" => '234',
		// "EUR" => '242'
		// );
		$currencies = json_decode(self::GESTPAY_CURRENCIES, true);
		if(isset($currencies[$currencyIsoCode]))
			{return $currencies[$currencyIsoCode];}
		return '';
		
	}

	
    /**
     * Config field magic getter
     * The specified key can be either in camelCase or under_score format
     * Tries to map specified value according to set payment method code, into the configuration value
     * Sets the values into public class parameters, to avoid redundant calls of this method
     *
     * @param string $key
     * @return string|null
     */
    public function __get($key)
    {
        $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
        $value = Mage::getStoreConfig($this->_mapMethodFieldset($underscored), $this->_storeId);
        $this->$key = $value;
        $this->$underscored = $value;
        return $value;
    }
	
    /**
     * Map General Settings
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapMethodFieldset($fieldName)
    {
        if (!$this->_methodCode) {
            return null;
        }
		return "payment/{$this->_methodCode}/{$fieldName}";
		
    }
	
	
}