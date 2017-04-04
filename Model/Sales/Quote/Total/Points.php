<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Sales_Quote_Total_Points
 */
class Divante_OpenLoyalty_Model_Sales_Quote_Total_Points extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * @var string
     */
    protected $_code = 'divante_openloyalty_expected';

    /**
     * @param \Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (
            $address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_BILLING
            && $this->canShowPointsToCurrentUser()
        ) {
            $points = Mage::helper('divante_openloyalty/quote')->getPredictedPointsForQuote($address->getQuote());
            $address->addTotal(
                [
                    'code'  => $this->getCode(),
                    'title' => 'points',
                    'value' => $points
                ]
            );
        }

        return $this;
    }

    /**
     * Show loyalty points totals only when customer is already in loyalty program, or join to loyalty is checked in cart
     *
     * @return bool
     */
    private function canShowPointsToCurrentUser()
    {
        return (
            Mage::helper('divante_openloyalty/customer')->isCustomerRegisteredInProgram()
            || Mage::helper('divante_openloyalty/config')->getLoyaltyInUse()
        );
    }
}
