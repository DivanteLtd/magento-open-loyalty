<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Observer_Checkout
 */
class Divante_OpenLoyalty_Model_Observer_Checkout
{
    const ATTACH_LOYALTY_CARD_SESSION_KEY = 'have_loyalty_card';
    const CREATE_LOYALTY_ACCOUNT_SESSION_KEY = 'create_loyalty_account';
    const LOYALTY_CARD_NUMBER_SESSION_KEY = 'loyalty_card_number';

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function setRegisterInCheckoutFlag($observer)
    {
        Mage::getSingleton('checkout/session')->unsetData(self::ATTACH_LOYALTY_CARD_SESSION_KEY);
        Mage::getSingleton('checkout/session')->unsetData(self::CREATE_LOYALTY_ACCOUNT_SESSION_KEY);
        Mage::getSingleton('checkout/session')->unsetData(self::LOYALTY_CARD_NUMBER_SESSION_KEY);

        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if (!$this->checkLoyaltyCardNumber()) {
            return $this->returnErrorResponse($observer, 'Specified card does not exist.');
        }

        if (!$this->checkEmailInLoyaltyProgram()) {
            return $this->returnErrorResponse($observer,
                'There is customer registered in loyalty program with same email, please enter loyalty card number.');
        }

        if ($quote->getId()) {
            $quote
                ->setRegisterInLoyalty()
                ->save();
        }
    }

    /**
     * @param        $observer
     * @param string $message
     *
     * @return Zend_Controller_Response_Abstract
     */
    protected function returnErrorResponse($observer, $message = 'Error')
    {
        /** @var Divante_Bronpl_Checkout_OnepageController $controller */
        $controller = $observer->getControllerAction();
        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $response = [
            'error'   => -1,
            'message' => Mage::helper('divante_openloyalty')->__($message)
        ];

        return $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * @return bool
     */
    private function checkLoyaltyCardNumber()
    {
        $requestParams = Mage::app()->getRequest()->getParams();
        $configHelper = Mage::helper('divante_openloyalty/config');

        if (
            isset($requestParams[$configHelper->getHaveLoyaltyCardInputName()])
            && $requestParams[$configHelper->getHaveLoyaltyCardInputName()]
            && isset($requestParams[$configHelper->getLoyaltyCardInputName()])
            && $requestParams[$configHelper->getLoyaltyCardInputName()]
        ) {
            $loyaltyCardNumber = $requestParams[$configHelper->getLoyaltyCardInputName()];
            $response = Mage::getModel('divante_openloyalty/request_customer')
                ->getCustomerByLoyaltyCard($loyaltyCardNumber);

            if (empty($response)) {
                return false;
            }

            Mage::getSingleton('checkout/session')->setData(self::LOYALTY_CARD_NUMBER_SESSION_KEY, $loyaltyCardNumber);
            Mage::getSingleton('checkout/session')->setData(self::ATTACH_LOYALTY_CARD_SESSION_KEY, true);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkEmailInLoyaltyProgram()
    {
        $requestParams = Mage::app()->getRequest()->getParams();
        $configHelper = Mage::helper('divante_openloyalty/config');

        if (
            isset($requestParams[$configHelper->getRegisterInLoyaltyInputName()])
            && $requestParams[$configHelper->getRegisterInLoyaltyInputName()]
            && !$requestParams[$configHelper->getHaveLoyaltyCardInputName()]
            && $customerMail = $this->getCustomerEmail()
        ) {
            $response = Mage::getModel('divante_openloyalty/request_customer')
                ->getCustomerByLoyaltyEmail($customerMail);

            if (!empty($response)) {
                return false;
            }

            Mage::getSingleton('checkout/session')->setData(self::CREATE_LOYALTY_ACCOUNT_SESSION_KEY, true);
        }

        return true;
    }

    /**
     * @return void
     */
    public function cleanPointsCache()
    {
        Mage::getSingleton('checkout/session')->unsetData(Divante_OpenLoyalty_Helper_Quote::PREDICTED_POINTS_CACHE_KEY);
    }

    /**
     * @return string
     */
    private function getCustomerEmail()
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        }

        return isset($requestParams['billing']['email']) ? $requestParams['billing']['email'] : '';
    }
}
