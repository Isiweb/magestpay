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

class Isiweb_Magestpay_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Config instance
     * @var Isiweb_Magestpay_Model_Config
     */
    protected $_config = null;

    /**
     * Config instance getter
     * @return Isiweb_Magestpay_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array(Isiweb_Magestpay_Model_Config::METHOD_GESTPAY);
            if ($store = Mage::app()->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = Mage::getModel('isiweb_magestpay/config', $params);
        }
        return $this->_config;
    }
	
    /**
     * Config instance setter
     * @return Isiweb_Magestpay_Model_Config
     */
    public function setConfigObject(Isiweb_Magestpay_Model_Config $object)
    {
        if (null === $this->_config) 
		{
            $this->_config = $object;
        }
        return $this->_config;
    }
	
    /**
     * Logs message to debug file 
     * @param $msg
     */
    public function log($msg) {
		if (filter_var($this->getConfig()->EnableLogging, FILTER_VALIDATE_BOOLEAN)) 
		{
			Mage::log($msg, null, 'Isiweb_Magestpay.log');
		}
    }
	
    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $order = $this->_getCheckoutSession()->getLastRealOrder();
        if ($order !== null && $order->getId()) {
            $quote = $this->_getQuote($order->getQuoteId());
            if ($quote !== null && $quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $this->_getCheckoutSession()
                    ->replaceQuote($quote)
                    ->unsLastRealOrderId();
                return true;
            }
        }
        return false;
    }
	
    /**
     * Return checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return sales quote instance for specified ID
     *
     * @param int $quoteId Quote identifier
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }	

}