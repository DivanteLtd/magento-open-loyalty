<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Observer_Sales
 */
class Divante_OpenLoyalty_Model_Observer_Sales
{
    /**
     * Send after placed order to Open Loyalty if customer is registered in OL or wants to be
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendOrder($observer)
    {
        /** @var Divante_OpenLoyalty_Model_Order $order */
        $order = $observer
            ->getEvent()
            ->getOrder();

        if ($order->getId()) {
            $order->registerOrderInOpenLoyalty();
        }
    }

    /**
     * @return void
     */
    public function cleanLoyaltyFlag()
    {
        Mage::getSingleton('checkout/session')->unsetData(Divante_OpenLoyalty_Helper_Config::LOYALTY_IN_USE);
        Mage::getSingleton('checkout/session')->unsetData(Divante_OpenLoyalty_Model_Observer_Checkout::ATTACH_LOYALTY_CARD_SESSION_KEY);
    }

    /**
     * Recollect quote totals after including open loyalty discount
     *
     * @param Varien_Event_Observer $observer
     */
    public function recollectTotals($observer)
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $observer
            ->getEvent()
            ->getQuote();

        if ($quote->getId()) {
            $quote->collectLoyaltyDiscount();
        }
    }

    /**
     * @return void
     */
    public function cleanCustomerStatusCache()
    {
        Mage::getSingleton('customer/session')->getCustomer()->cleanCustomerStatusCache();
    }
}
