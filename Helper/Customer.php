<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Helper_Customer
 */
class Divante_OpenLoyalty_Helper_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isCustomerRegisteredInProgram()
    {
        $customer = Mage::getSingleton('customer/session');
        if ($customer->isLoggedIn() && $customer->getCustomer()->checkIfRegisteredInLoyaltyPoint()) {
            return true;
        }

        return false;
    }
}
