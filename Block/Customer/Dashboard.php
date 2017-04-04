<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Customer_Dashboard
 */
class Divante_OpenLoyalty_Block_Customer_Dashboard extends Divante_OpenLoyalty_Block_Customer_Base
{
    /**
     * @return string
     */
    public function getFormattedValueToNextLevel()
    {
        $formattedValue = '';
        switch ($this->getLoyaltyHelper()->getTierType()) {
            case "points":
                $value = $this->getCustomerStatusValue('pointsToNextLevel');
                $formattedValue = $this->getLoyaltyHelper()->__('%s points', $value);
                break;
            case "transactions":
                $value = $this->getCustomerStatusValue('transactionsAmountToNextLevel');
                $formattedValue = Mage::helper('core')->formatCurrency($value);
                break;
        }

        return $formattedValue;
    }

    /**
     * @return bool
     */
    public function canReachNextLevel()
    {
        return !empty($this->getCustomerStatusValue('nextLevel'));
    }
}
