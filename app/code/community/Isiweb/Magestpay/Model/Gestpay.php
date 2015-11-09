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

class Isiweb_Magestpay_Model_Gestpay extends Mage_Payment_Model_Method_Abstract {

    /**
     * Constants
     */
	protected $_code  = Isiweb_Magestpay_Model_Config::METHOD_GESTPAY;

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_isInitializeNeeded      = true;

    /**
     * Block paths
     */
	protected $_formBlockType = 'isiweb_magestpay/form';
	protected $_infoBlockType = 'isiweb_magestpay/info';
	
    /**
     * Config instance
     * @var Isiweb_Magestpay_Model_Config
     */
    protected $_config = null;
	
    /**
     * Helper instance
     * @var Isiweb_Magestpay_Helper_Data
     */
    protected $_helper = null;


	/**
     * Get reserved method session namespace
     *
     * @return Mage_Magestpay_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton("isiweb_magestpay/session");
    }	

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Config instance getter
     * @return Isiweb_Magestpay_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
			if($store = $this->getStore()) {
				$params[] = is_object($store) ? $store->getId() : $store;
			}
            $this->_config = Mage::getModel('isiweb_magestpay/config', $params);
        }
        return $this->_config;
    }
	
    /**
     * Helper instance getter
     * @return Isiweb_Magestpay_Helper_Data
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('isiweb_magestpay');
			$this->_helper->setConfigObject($this->getConfig());
        }
        return $this->_helper;
    }
	
    /**
     * Check whether payment method can be used
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
		$retval = true;
		if( !parent::isAvailable($quote) || !$this->getConfig()->isMethodAvailable() )
		{
			// Check availability in admin settings
			$this->getHelper()->log('Payment method not available. Check your admin settings or SOAP extension enabled.');
			$retval = false;
		} elseif (!$this->getConfig()->isCurrencySupported($quote->getQuoteCurrencyCode()))
		{ 
			// Check availability related to supported currencies
			$this->getHelper()->log('Payment method not available. Unsupported currency code ' . $quote->getQuoteCurrencyCode());
			$retval = false;
		}
		
		return $retval;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfig()->$field;
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }
	
    /**
     * Return Order place redirect url
     * This is the pointer to the controller action
	 * for this payment method
	 *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('isiweb_magestpay/gestpay/redirect', array('_secure' => Mage::app()->getStore()->isCurrentlySecure()));
    }
	
    /**
     * Return Order payment redirect url
     * This is the pointer to the URL of the
	 * payment gatewy
	 *
     * @return string
     */
    public function getOrderPaymentRedirectUrl()
    {
        $_protocol = Isiweb_Magestpay_Model_Config::GESTPAY_PROTOCOL;
		$_host	   = (filter_var($this->getConfig()->EnableTest, FILTER_VALIDATE_BOOLEAN) ? Isiweb_Magestpay_Model_Config::GESTPAY_HOST_TEST : Isiweb_Magestpay_Model_Config::GESTPAY_HOST);
		$_path	   = Isiweb_Magestpay_Model_Config::GESTPAY_PATH;
		return "{$_protocol}://{$_host}{$_path}";
    }

    /**
     * Return Order Encryption/Decription service url
     * This is the pointer to the URL of the
	 * payment gatewy
	 *
     * @return string
     */
    public function getPaymentServiceUrl()
    {
        $_protocol = Isiweb_Magestpay_Model_Config::GESTPAY_PROTOCOL;
		$_host	   = Isiweb_Magestpay_Model_Config::GESTPAY_WSDL_HOST;
		$_path	   = Isiweb_Magestpay_Model_Config::GESTPAY_WSDL_PATH;
		return "{$_protocol}://{$_host}{$_path}";
    }
	
    /**
     * Checks weather or not order is ready for checkout
     *
     * @return bool
     */
	 public function canCheckout()
	 {
		$this->getSession()->clear();
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		if ($order->getId() && $order->getState() == Mage_Sales_Model_Order::STATE_NEW ) 
		{
			$this->getSession()->setOrder($order);
			return true;
		}
		return false;
	 }
	
    /**
     * Return form field array
     *
     * @return array
     */
    public function getCheckoutFormFields()
    {
		$order 				= $this->getSession()->getOrder();
		$transaction_data 	= array(
			'shopLogin'			=> $this->getConfig()->MerchantId,
			'shopTransactionId'	=> $order->getIncrementId(),
			'uicCode'			=> $this->getConfig()->getUicCodeFromCurrency($order->getOrderCurrencyCode()),
			'amount'			=> round($order->getBaseGrandTotal(), 2)
		);
		
		// Set language if enabled
		if($this->getConfig()->Language != '0')
		{
			$transaction_data['languageId'] = $this->getConfig()->Language;
		}
		
		// Debug log
		$this->getHelper()->log('Sending transaction data for cryptation:');
		$this->getHelper()->log($transaction_data);
		
		// Generate SOAP request to Webservice with "Encrypt" method
		$soapClient = new Zend_Soap_Client($this->getPaymentServiceUrl(), array( 'compression' => SOAP_COMPRESSION_ACCEPT, 'soap_version' => SOAP_1_2,));
		$response   = $soapClient->Encrypt($transaction_data);
		$result     = $this->xmlResultToArray($response->EncryptResult->any);
		
		// Parse validity of transaction
		if($result['TransactionResult'] == Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_OK)
		{
			$this->getHelper()->log('Returned crypted transaction string:');
			$this->getHelper()->log($result['CryptDecryptString']);
			
		} else {
		
			$this->getHelper()->log('Exception occurred:' . $result['ErrorCode'] . ' : ' . $result['ErrorDescription']);
			Mage::throwException($result['ErrorCode'] . ' : ' . $result['ErrorDescription']);
		}
		
		// Prepare output fields
		$fields = array(
			'a'	=>	$this->getConfig()->MerchantId,
			'b' =>	$result['CryptDecryptString']
		);
		
		return $fields;

    }

	/**
     * Sets the parameters received from result action controller
     *
     * @return array
     */
    public function setCallBackTransaction($a, $b)	
	{
		
		$transaction_data = array(
			'shopLogin'		=> $a,
			'CryptedString'	=> $b
		);
		
		// Debug log
		$this->getHelper()->log('Result parameters :');
		$this->getHelper()->log($transaction_data);
		$this->getHelper()->log('Sending data for decrypt ...');

		// Generate SOAP request to Webservice with "Decrypt" method
		$soapClient = new Zend_Soap_Client($this->getPaymentServiceUrl(), array( 'compression' => SOAP_COMPRESSION_ACCEPT, 'soap_version' => SOAP_1_2,));
		$response   = $soapClient->Decrypt($transaction_data);
		$result     = $this->xmlResultToArray($response->DecryptResult->any);
		
		$this->getHelper()->log('Received response :');
		$this->getHelper()->log($result);
		$this->getSession()->setCallBackTransaction($result);
		
		return $this;
	}
	
	/**
     * Process the transaction received by callback
     *
     * @return array
     */
    public function getCallBackOutcome()	
	{
		$result      = false;
		$transaction = $this->getSession()->getCallBackTransaction();

		
		if($transaction['TransactionResult'] == Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_OK
		   || $transaction['TransactionResult'] == Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_PENDING)
		{
			$result = true;
		}

		return $result;
		
	}	
	
	/**
     * Sets the parameters received from server2server action controller
     *
     * @return array
     */
    public function setServerTransaction($a, $b)	
	{
		$transaction_data = array(
			'shopLogin'		=> $a,
			'CryptedString'	=> $b
		);
		
		// Debug log
		$this->getHelper()->log('S2s parameters :');
		$this->getHelper()->log($transaction_data);
		$this->getHelper()->log('Sending data for crypt ...');

		// Generate SOAP request to Webservice with "Decrypt" method
		$soapClient = new Zend_Soap_Client($this->getPaymentServiceUrl(), array( 'compression' => SOAP_COMPRESSION_ACCEPT, 'soap_version' => SOAP_1_2,));
		$response   = $soapClient->Decrypt($transaction_data);
		$result     = $this->xmlResultToArray($response->DecryptResult->any);
		
		$this->getHelper()->log('Received response :');
		$this->getHelper()->log($result);
		$result = array_merge($result, $transaction_data);
		$this->getSession()->setServerTransaction($result);
		
		return $this;
	}
	
	/**
     * Process the transaction received by callback
     *
     * @return array
     */
    public function getServerOutcome()	
	{
		$result      = false;
		$transaction = $this->getSession()->getServerTransaction();
		$order       = Mage::getModel('sales/order')->loadByIncrementId($transaction['ShopTransactionID']);
		$alertCode   = isset($transaction['AlertCode']) ? $transaction['AlertCode'] : '';
		$alertDesc   = isset($transaction['AlertDescription']) ? $transaction['AlertDescription'] : '';
		
		if(!$order->getId())
		{
			// It seems like we do not have such order
			$this->getHelper()->log('Processed response for unknown order #' . $transaction['ShopTransactionID']);
		} 
		else  
		{
			try 
			{
				switch($transaction['TransactionResult'])
				{
					case Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_PENDING :
						
						$this->getHelper()->log('Recording pending payment for order #'.$transaction['ShopTransactionID']);
						
						// Register Payment 
						if($alertCode != '')
						{
							$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'Pending payment', true);
						} 
						else 
						{
							$msg  = "Please check : [{$alertCode}] : {$alertDesc}";
							$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage_Sales_Model_Order::STATUS_FRAUD, $msg);
						}
						
						$order->save();
						
						// Notify customer
						if(!$order->getEmailSent())
						{
							$order->sendNewOrderEmail()
								  ->addStatusHistoryComment('Order confirmation email sent')
								  ->setIsCustomerNotified(true)
								  ->save();
						}
						$result = true;
						break;
						
					case Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_OK :

						$this->getHelper()->log('Recording successful payment for order #'.$transaction['ShopTransactionID']);
						
						$msg  = ' Buyer : ' . $transaction['Buyer']['BuyerName'];
						$msg .= ' Email : ' . $transaction['Buyer']['BuyerEmail'];
						$msg .= ' Auth Code : ' . $transaction['AuthorizationCode'];
					
						// Register Payment and generate invoice
						$payment = $order->getPayment();
						$payment->setTransactionId($transaction['BankTransactionID'])
								->setCurrencyCode($order->getOrderCurrencyCode())
								->setPreparedMessage($msg)
								->setIsTransactionClosed(true)
								->registerCaptureNotification(
									$transaction['Amount'],
									false);
						$order->save();
						
						// Notify customer
						if(!$order->getEmailSent())
						{
							$order->sendNewOrderEmail()
								  ->addStatusHistoryComment('Order confirmation email sent')
								  ->setIsCustomerNotified(true)
								  ->save();
						}
						$result = true;
						break;
						
					case Isiweb_Magestpay_Model_Config::TRANSACTION_RESULT_KO :
					
						$this->getHelper()->log('Recording failed payment for order #'.$transaction['ShopTransactionID']);
					
						$msg  = 'Payment failed. Error code : ';
						$msg .= ' [' . $transaction['ErrorCode'] . ']';
						$msg .= ' ' . $transaction['ErrorDescription'];
					
						// Register Payment Failure if order not cancelled yet
						if($order->canCancel())
						{
							$order->registerCancellation($msg, false)->save();
						} 
						else
						{
							$order->addStatusHistoryComment($msg)
								  ->setIsCustomerNotified(true)
								  ->save();
						}
						break;
				}
			} 
			catch (Exception $e)
			{
				$msg  = 'Exception on S2S transaction : ';
				$msg .= $e->getMessage();
				$order->addStatusHistoryComment($msg)
				      ->setIsCustomerNotified(false)
					  ->save();
				$this->getHelper()->log($msg);
				
			}
		}
		return $result;
		
	}	

	/**
     * Transform xml flow received from soap query 
     * and cleans up empty fields
     * @return array
     */
	public function xmlResultToArray($xml)
	{
		$result = json_decode(json_encode((array) simplexml_load_string($xml)),1);
		foreach($result as $key => $value)
		{
			if(empty($result[$key]))
			{ 
				unset($result[$key]); 
			}
		}
		return $result;
	}
	
	
}
