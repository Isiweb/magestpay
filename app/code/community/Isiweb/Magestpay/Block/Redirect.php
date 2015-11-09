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

class Isiweb_Magestpay_Block_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $model = Mage::getModel('isiweb_magestpay/gestpay');
        $form = new Varien_Data_Form();
        $form->setAction($model->getOrderPaymentRedirectUrl())
             ->setId('gestpay_checkout')
             ->setName('gestpay_checkout')
             ->setMethod('GET')
             ->setUseContainer(true);
        foreach ($model->getCheckoutFormFields() as $key => $value) {
            $form->addField($key, 'hidden', array('name'=>$key, 'value'=>$value));
        }
        $idSuffix = Mage::helper('core')->uniqHash();
        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $this->__('Click here if you are not redirected within 10 seconds...'),
        ));
        $id = "submit_to_gestpay_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);
        $html = '<html><body>';
        $html.= $this->__('You will be redirected in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("gestpay_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
