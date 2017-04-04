<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Catalog_Product_LoyaltyMessage
 */
class Divante_OpenLoyalty_Block_Catalog_Product_LoyaltyMessage extends Mage_Catalog_Block_Product_Abstract
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
     * Return message for encourage customer for register in loyalty program
     *
     * @return string
     */
    public function getLoyaltyMessage()
    {
        return $this
            ->getLoyaltyHelper()
            ->getFrontendConfig()['product_message'];
    }

    /**
     * Return bool if register in loyalty point is enabled
     *
     * @return bool
     */
    protected function isLoyaltyEnabled()
    {
        return $this->getLoyaltyHelper()->isEnabled();
    }

    /**
     * No show if loyalty program is disabled or if message is empty
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isLoyaltyEnabled() || !$this->getLoyaltyMessage()) {
            return '';
        }

        return parent::_toHtml();
    }
}
