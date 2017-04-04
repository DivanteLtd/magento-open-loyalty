<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Observer_Customer
 */
class Divante_OpenLoyalty_Model_Observer_Customer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function addLoyaltyTabs(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getLayout()->getBlock('customer_account_navigation');
        $loyaltyHelper = Mage::helper('divante_openloyalty/config');
        if (!empty($block) && $loyaltyHelper->isEnabled()) {
            if (!Mage::getSingleton('customer/session')->getCustomer()->checkIfRegisteredInLoyaltyPoint()) {
                $block->addLink(
                    "join_loyalty",
                    'loyaltyprogram/account/joinloyalty',
                    $loyaltyHelper->__("Join to %s", $loyaltyHelper->getLoyaltyProgramLabel())
                );
            } else {
                $block->addLink(
                    "loyalty_club",
                    '#',
                    sprintf("<span class='loyaltyMenuMain open-modal'>%s</span>", $loyaltyHelper->getLoyaltyProgramLabel())
                );
                $block->addLink(
                    "loyalty_rewards",
                    'loyaltyprogram/account/rewards',
                    sprintf("<span class='loyaltyMenuSub'>%s</span>", $loyaltyHelper->__("My rewards"))
                );
                $block->addLink(
                    "loyalty_points",
                    'loyaltyprogram/account/points',
                    sprintf("<span class='loyaltyMenuSub'>%s</span>", $loyaltyHelper->__("My points"))
                );
                $block->addLink(
                    "loyalty_transactions",
                    'loyaltyprogram/account/transactions',
                    sprintf("<span class='loyaltyMenuSub'>%s</span>", $loyaltyHelper->__("My transactions"))
                );
                $block->addLink(
                    "loyalty_info",
                    'loyaltyprogram/account',
                    sprintf("<span class='loyaltyMenuSub'>%s</span>", $loyaltyHelper->__("My loyalty profile"))
                );
                $block->addLink(
                    "loyalty_terms",
                    ltrim($loyaltyHelper->getLoyaltyProgramTermsUrl(false), '/'),
                    sprintf("<span class='loyaltyMenuSub'>%s</span>", $loyaltyHelper->__("Loyalty program terms"))
                );
            }

            $block->setActive(substr(Mage::app()->getRequest()->getPathInfo(), 1));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function newsletterSubscriberSave(Varien_Event_Observer $observer)
    {
        if(Mage::helper('divante_openloyalty/customer')->isCustomerRegisteredInProgram()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $loyaltyId = $customer->getOpenLoyaltyId();
            Mage::getModel('divante_openloyalty/request_customer')
                ->updateCustomerValue($loyaltyId, 'agreement2', boolval(Mage::app()->getRequest()->getParam('is_subscribed')));
            $customer->cleanCustomerStatusCache();
        }
    }
}
