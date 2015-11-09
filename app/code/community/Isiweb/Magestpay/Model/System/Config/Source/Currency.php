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

class Isiweb_Magestpay_Model_System_Config_Source_Currency
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>'USD Dollari Usa'),
            array('value' => 2, 'label'=>'GBP Sterlina Gran Bretagna'),
            array('value' => 3, 'label'=>'CHF Franco Svizzero'),
            array('value' => 7, 'label'=>'DKK Corone Danesi'),
            array('value' => 8, 'label'=>'NOK Corona Norvegese'),
            array('value' => 9, 'label'=>'SEK Corona Svedese'),
            array('value' => 12, 'label'=>'CAD Dollari Canadesi'),
            array('value' => 18, 'label'=>'ITL Lira Italiana'),
            array('value' => 71, 'label'=>'JPY Yen Giapponese'),
            array('value' => 103, 'label'=>'HKD Dollaro Hong Kong'),
            array('value' => 234, 'label'=>'BRL Real'),
            array('value' => 242, 'label'=>'EUR Euro'),
        );
    }

}