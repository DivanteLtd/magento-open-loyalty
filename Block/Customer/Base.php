<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Customer_Base
 */
class Divante_OpenLoyalty_Block_Customer_Base extends Mage_Core_Block_Template
{
    /**
     * @var array
     */
    protected $loyaltyData = [];

    /**
     * @var  Divante_OpenLoyalty_Model_Customer
     */
    protected $customer;

    /**
     * @var  array
     */
    protected $customerStatus = [];

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
     * @return Divante_OpenLoyalty_Model_Customer|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (empty($this->customer)) {
            /** @var Divante_OpenLoyalty_Model_Customer $customer */
            $this->customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        return $this->customer;
    }

    /**
     * @return bool
     */
    public function isCustomerRegisteredInProgram()
    {
        $customer = $this->getCustomer();

        return $customer->checkIfRegisteredInLoyaltyPoint();
    }

    /**
     * @return array
     */
    public function getCustomerDataFromOpenLoyalty()
    {
        $customer = $this->getCustomer();

        if (empty($this->loyaltyData) && $customer->checkIfRegisteredInLoyaltyPoint()) {
            $requestModel = Mage::getSingleton('divante_openloyalty/request_customer');
            $this->loyaltyData = $requestModel->getCustomer($customer->getOpenLoyaltyId());
        }

        return $this->loyaltyData;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getLoyaltyData($key)
    {
        return isset($this->getCustomerDataFromOpenLoyalty()[$key]) ? Mage::helper('core')->escapeHtml($this->getCustomerDataFromOpenLoyalty()[$key]) : '';
    }

    /**
     * @return array
     */
    public function getCustomerStatus()
    {
        if (empty($this->customerStatus)) {
            $customer = $this->getCustomer();
            $this->customerStatus = $customer->getCustomerStatusInLoyaltyProgram();
        }

        return $this->customerStatus;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getCustomerStatusValue($key = '')
    {
        return isset($this->getCustomerStatus()[$key]) ? Mage::helper('core')->escapeHtml($this->getCustomerStatus()[$key]) : '';
    }

    /**
     * @return array
     */
    public function getEarningRules()
    {
        return Mage::getModel('divante_openloyalty/request_earningRule')->getEarningRules();
    }

    /**
     * @return array
     */
    public function getCustomerCampaigns()
    {
        $customer = $this->getCustomer()->getOpenLoyaltyId();

        return Mage::getModel('divante_openloyalty/request_campaign')->getCustomerCampaigns($customer);
    }
}
