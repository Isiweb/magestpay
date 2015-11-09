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

class Isiweb_Magestpay_GestpayController extends Mage_Core_Controller_Front_Action 
{

    public function redirectAction()
	{
	
		$translator   = Mage::helper('checkout');
		$paymentModel = Mage::getModel('isiweb_magestpay/gestpay');
		
		if(!$paymentModel->canCheckOut()){
			$paymentModel->getHelper()->log('Trying to access Gestpay gateway without any valid order.');
            $this->norouteAction();
            return;
		}
		
		$session = $paymentModel->getSession();
		$order   = $session->getOrder();
		$paymentModel->getHelper()->log('Trying to access Gestpay gateway for order # '.$order->getIncrementId());

		// Output the redirect page or trap any error.
        try
		{
			$this->getResponse()->setBody($this->getLayout()->createBlock('isiweb_magestpay/redirect')->toHtml());

        } catch (Exception $e) {
		
			// Any error may occur here is related to Magento itself or due to
			// bad generation of the encrypted string. Redirect to failure checkout.
			$paymentModel->getHelper()->log('Got error : ' . $e->getMessage());
			$paymentModel->getCheckout()->addError($translator->__('We have experienced an error while redirecting you to the payment page.'));
			$paymentModel->getCheckout()->addNotice($translator->__('Your items are back in cart.'));
			$paymentModel->getHelper()->restoreQuote();
            $this->_redirect('checkout/cart');
            return;
        }

    }

    public function resultAction()
	{

		$paymentModel = Mage::getModel('isiweb_magestpay/gestpay');
		
		// Retrieve parameters from request received
        $a = $this->getRequest()->getParam('a',false);
        $b = $this->getRequest()->getParam('b',false);

		// If missing parameters then noroute
        if(!$a || !$b){
            $paymentModel->getHelper()->log('Access to result page forbidden. Missing input parameters A and B');
            $this->norouteAction();
            return;
        }

		// Save current store
		$currentStore =  Mage::app()->getStore();
		
		// Get original store where the order has been placed
		$order        = $paymentModel->getSession()->getOrder();
		$orderStore   = $order->getStore();
		
		// Activate order locale if different from current store
		// Thanks to http://www.kennydeckers.com/programatically-set-locale-language-magento/
		if($currentStore->getId() != $orderStore->getId()) {
			Mage::app()->setCurrentStore($orderStore->getId());
			$orderLocale = Mage::getModel('core/locale')->getLocaleCode();
			Mage::app()->getLocale()->setLocale($orderLocale);
			Mage::getSingleton('core/translate')->setLocale($orderLocale)->init('frontend', true);
		}
		
		// Load translator
		$translator   = Mage::helper('checkout');

		// Process result data
		$result = $paymentModel->setCallBackTransaction($a, $b)->getCallBackOutcome();
		if($result)
		{
			$paymentModel->getHelper()->log('Return from Gestpay with successful transaction.');
			$redirect = 'checkout/onepage/success';
		} 
		else
		{
			$paymentModel->getHelper()->log('Return from Gestpay with negative transaction.');
			$paymentModel->getHelper()->log('Restoring cart items.');
			$paymentModel->getCheckout()->addNotice($translator->__('Payment procedure has not been completed.'));
			$paymentModel->getCheckout()->addNotice($translator->__('Your items are back in cart.'));
			$paymentModel->getHelper()->restoreQuote();
			$redirect = 'checkout/cart';
		}

		// Retrieve order from payment session to
		// $order = $paymentModel->getSession()->getOrder();
		// $store = $order->getStore();
		$url = Mage::getUrl($redirect, array(
			    '_use_rewrite' => false,
                '_store' => $orderStore,
                '_store_to_url' => true,
                '_secure' => $currentStore->isCurrentlySecure()
		));
		$this->getResponse()->setRedirect($url);

    }

    public function s2sAction()
	{
    	
		$paymentModel = Mage::getModel('isiweb_magestpay/gestpay');
		
		// Retrieve parameters from request received
        $a = $this->getRequest()->getParam('a',false);
        $b = $this->getRequest()->getParam('b',false);

		// If missing parameters then noroute
        if(!$a || !$b){
            $paymentModel->getHelper()->log('Server to server access denied. Missing input parameters.');
            $this->norouteAction();
            return;
        }

		// Process result data
		$result = $paymentModel->setServerTransaction($a, $b)->getServerOutcome();
	
		// Render a correct page
        $this->getResponse()
			->setHeader('HTTP/1.1','200',true)
            ->setBody('Ok');
			
        return;
    }


}