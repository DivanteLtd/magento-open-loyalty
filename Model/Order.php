<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Override order model
 *
 * Class Divante_OpenLoyalty_Model_Order
 *
 * @method $this setLoyaltyTransactionId(string $value)
 * @method string getLoyaltyTransactionId()
 */
class Divante_OpenLoyalty_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * @var Divante_OpenLoyalty_Model_Customer
     */
    protected $purchaser;

    /**
     * Return register in Open Loyalty flag
     *
     * @return int
     */
    public function getRegisterInLoyalty()
    {
        return (int) $this->getData('register_in_loyalty');
    }

    /**
     * Return loyalty discount in percentage as float
     *
     * @return float|null
     */
    public function getLoyaltyDiscount()
    {
        if (is_null($this->getData('loyalty_discount'))) {
            return null;
        }

        /** @var float $discount */
        $discount = (float) $this->getData('loyalty_discount');

        return $discount;
    }

    /**
     * Return bool if order has loyalty discount
     * @return bool
     */
    public function isLoyaltyDiscount()
    {
        return ($this->getLoyaltyDiscount() > 0);
    }

    /**
     * Register after placed order to Open Loyalty if customer is registered in OL or wants to be
     */
    public function registerOrderInOpenLoyalty()
    {
        if (!$this->doSendToOpenLoyalty()) {
            return $this;
        }

        /** @var Divante_OpenLoyalty_Model_Request_Transaction $requestModel */
        $requestModel = Mage::getModel('divante_openloyalty/request_transaction');

        /** @var array|false $response */
        $response = $requestModel->sendPlacedOrder($this);

        if (!empty($response)) {
            $this->setLoyaltyData($response);
        }

        return $this;
    }

    /**
     * Return purchaser model loaded from db
     *
     * @return Divante_OpenLoyalty_Model_Customer
     */
    public function getPurchaser()
    {
        if (isset($this->purchaser)) {
            return $this->purchaser;
        }

        $this->purchaser = Mage::getModel('customer/customer')
            ->load($this->getCustomerId());

        return $this->purchaser;
    }

    /**
     * Return bool if send placed order to Open Loyalty or not
     *
     * @return bool
     */
    protected function doSendToOpenLoyalty()
    {
        /** @var Divante_OpenLoyalty_Model_Customer $purchaser */
        $purchaser = $this->getPurchaser();

        if ($purchaser->getId()
            && $purchaser->checkIfRegisteredInLoyaltyPoint()
            && !$this->getLoyaltyTransactionId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Save returned loyalty saved transaction data in order model
     *
     * @param array $response
     */
    protected function setLoyaltyData($response)
    {
        if (isset($response['transactionId'])) {
            $this
                ->setLoyaltyTransactionId($response['transactionId'])
                ->save();
        }
    }
}
