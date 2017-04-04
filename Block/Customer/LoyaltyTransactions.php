<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Customer_LoyaltyTransactions
 */
class Divante_OpenLoyalty_Block_Customer_LoyaltyTransactions extends Divante_OpenLoyalty_Block_Customer_Base
{
    /**
     * @var array
     */
    private $customerTransactions = [];

    /**
     * @return array
     */
    public function getCustomerTransactions()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if (empty($this->customerTransactions) && $customer->checkIfRegisteredInLoyaltyPoint()) {
            $requestModel = Mage::getSingleton('divante_openloyalty/request_transaction');
            $transactions = $requestModel->getCustomerTransactions($customer->getOpenLoyaltyId());
            $this->customerTransactions = $transactions['transactions'] ?: [];
        }

        return $this->customerTransactions;
    }

    /**
     * @param array  $transaction
     * @param string $key
     *
     * @return string
     */
    public function getTransactionData($transaction, $key)
    {
        return $transaction[$key] ?: '';
    }
}
