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

/**
 * Payment method form base block
 */
class Isiweb_Magestpay_Block_Form extends Mage_Payment_Block_Form
{

    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Block construction. Set block template.
     */
    protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('payment/form/magestpay.phtml');
	}

    /**
     * Get instructions text from config
     *
     * @return string
     */
	public function getInstructions()
	{
		if(is_null($this->_instructions))
		{
			$this->_instructions = $this->getMethod()->getConfig()->Instructions;
		}
		return $this->_instructions;
	}
	
}
