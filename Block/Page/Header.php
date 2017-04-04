<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Page_Header
 */
class Divante_OpenLoyalty_Block_Page_Header extends Mage_Page_Block_Html_Header
{
    /**
     * @return Divante_OpenLoyalty_Helper_Config
     */
    public function getLoyaltyHelper()
    {
        /** @var Divante_OpenLoyalty_Helper_Config $helper */
        $helper = $this->helper('divante_openloyalty/config');

        return $helper;
    }

    /**
     * @return string
     */
    public function getProgramName()
    {
        return $this->getLoyaltyHelper()->getLoyaltyProgramLabel();
    }

    /**
     * @return string
     */
    public function getCustomerLevel()
    {
        return Mage::getSingleton('customer/session')->getCustomer()->getCustomerDiscountLevel();
    }

    /**
     * @return string
     */
    public function getCustomerPoints()
    {
        return Mage::getSingleton('customer/session')->getCustomer()->getCustomerStatusInLoyaltyProgram()['points'] ?: 0;
    }
}
