<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Checkout_Onepage
 */
class Divante_OpenLoyalty_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    /**
     * Name of checkout method param request
     *
     * @var string
     */
    const CHECKOUT_METHOD_PARAM = 'checkout_method';

    /**
     * Check if customer is registering in loyalty program
     *
     * @return bool
     */
    public function isCustomerRegisterInLoyalty()
    {
        /** @var Divante_OpenLoyalty_Model_Customer $customer */
        $customer = $this->getCustomer();

        return (
            $this->isCustomerLoggedIn()
            && $customer->checkIfRegisteredInLoyaltyPoint()
        );
    }

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
     * Return bool if guest wants to register in loyalty om checkout
     *
     * @return bool
     */
    public function isSetRegisterToLoyalty()
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $this->getQuote();

        return (bool) $quote->getRegisterInLoyalty();
    }


    /**
     * If customer is already registered in loyalty or cannot register in loyalty - no show checkbox
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canShowBlock()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * If customer is already registered in loyalty or cannot register in loyalty - no show checkbox
     *
     * @return bool
     */
    protected function canShowBlock()
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $this->getQuote();

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $this->getRequest();

        /** @var string $methodPost */
        $methodPost = ($request->has(self::CHECKOUT_METHOD_PARAM))
            ? $request->getParam(self::CHECKOUT_METHOD_PARAM)
            : $quote->getCheckoutMethod();

        if ($this->isCustomerRegisterInLoyalty()
            || !$this->getLoyaltyHelper()->isEnabled()
            || (!$this->isCustomerLoggedIn() && $methodPost == $quote::CHECKOUT_METHOD_GUEST)
        ) {
            return false;
        }

        return true;
    }
}
