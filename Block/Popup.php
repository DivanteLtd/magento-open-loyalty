<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Popup
 */
class Divante_OpenLoyalty_Block_Popup extends Divante_OpenLoyalty_Block_Customer_Dashboard
{
    /**
     * @var array
     */
    private $customerData;

    /**
     * @var array
     */
    private $transactionsData;

    /**
     * @return bool
     */
    public function customerRegisteredInProgram()
    {
        $customer = $this->getCustomer();

        return $customer->checkIfRegisteredInLoyaltyPoint();
    }

    /**
     * @return array
     */
    public function getCustomerData()
    {
        if(empty($this->customerData)) {
            $requestModel = Mage::getModel('divante_openloyalty/request_customer');
            $this->customerData = $requestModel->getCustomer($this->getCustomer()->getOpenLoyaltyId());
        }

        return $this->customerData;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getCustomerDataValue($key = '')
    {
        return Mage::helper('core')->escapeHtml($this->getCustomerData()[$key]) ?: '';
    }

    /**
     * @return array
     */
    public function getTransactions()
    {
        if(empty($this->transactionsData)) {
            $requestModel = Mage::getModel('divante_openloyalty/request_transaction');
            $transactions = $requestModel->getCustomerTransactions($this->getCustomer()->getOpenLoyaltyId());
            $this->transactionsData = $transactions['transactions'] ?: [];
        }

        return $this->transactionsData;
    }

    /**
     * @param array  $transaction
     * @param string $key
     *
     * @return string
     */
    public function getTransactionData($transaction, $key = '')
    {
        return isset($transaction[$key]) ? Mage::helper('core')->escapeHtml($transaction[$key]) : '';
    }

    /**
     * @return array|false
     */
    public function getBoughtCapmagins()
    {
        return Mage::getModel('divante_openloyalty/request_campaign')
            ->getCustomerBoughtCampaigns($this->getCustomer()->getOpenLoyaltyId());
    }

    /**
     * @return string
     */
    public function getCustomerPanelBoughtUrl()
    {
        return $this->getLoyaltyHelper()->getUrlToCustomerPanel() . "customer/panel/bought";
    }

    /**
     * @param string $campaignId
     *
     * @return string
     */
    public function getBuyRewardUrl($campaignId)
    {
        return Mage::getUrl('loyaltyprogram/popup/buyCampaign', ['campaignId' => $campaignId]);
    }
}
