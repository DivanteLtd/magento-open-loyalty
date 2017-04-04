<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

require_once(Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php');

/**
 * Class Divante_OpenLoyalty_CartController
 */
class Divante_OpenLoyalty_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Param's name for removing coupon or discount
     *
     * @var string
     */
    const REMOVE_PARAM = 'remove';

    /**
     * Initialize coupon and delete Open Loyalty discount
     */
    public function couponPostAction()
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $this->_getQuote();

        $this->resetLoyaltyInUse();

        /** @var Mage_Core_Helper_Data $helperCore */
        $helperCore = Mage::helper('core');
        
        /**
         * No reason continue with empty shopping cart
         */
        if (!$quote->getItemsCount()
            || !$this->_validateFormKey()) {
            $this->_goBack();

            return;
        }

        /** @var string $couponCode */
        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        
        if ($this->getRequest()->getParam(self::REMOVE_PARAM) == 1) {
            $couponCode = '';
        }
        
        $oldCouponCode = $quote->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            
            return;
        }

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->unsetLoyaltyDiscount()
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $quote->getCouponCode()) {
                    $this
                        ->_getSession()
                        ->addSuccess(
                        $this->__(
                            'Coupon code "%s" was applied.',
                            $helperCore->htmlEscape($couponCode)
                        )
                    );
                } else {
                    $this
                        ->_getSession()
                        ->addError(
                        $this->__(
                            'Coupon code "%s" is not valid.',
                            $helperCore->htmlEscape($couponCode)
                        )
                    );
                }
            } else {
                $this
                    ->_getSession()
                    ->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this
                ->_getSession()
                ->addError($e->getMessage());
        } catch (Exception $e) {
            $this
                ->_getSession()
                ->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

    /**
     * @return void|null
     */
    public function loyaltyDiscountPostAction()
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $this->_getQuote();

        /** @var Divante_OpenLoyalty_Helper_Config $helperLoyalty */
        $helperLoyalty = Mage::helper('divante_openloyalty/config');

        $quote->unsetRegisterInLoyalty();
        $this->resetLoyaltyInUse();

        /**
         * No reason continue with empty shopping cart
         */
        if (!$quote->getItemsCount()
            || !$this->_validateFormKey()
            || !$helperLoyalty->isEnabled()) {
            $this->unsetDiscountAndBack($quote);

            return;
        }

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        /** @var Divante_OpenLoyalty_Model_Customer $loggedCustomer */
        $loggedCustomer = $customerSession->getCustomer();

        if ($this->getRequest()->getParam(self::REMOVE_PARAM) == 1) {
            $this->unsetDiscountAndBack($quote);
            $this
                ->_getSession()
                ->addSuccess(
                    $helperLoyalty->__('Loyalty discount was removed.')
                );

            return;
        }

        try {
            $discount = $helperLoyalty->getInitialProgramDiscount();

            if ($customerSession->isLoggedIn() && $loggedCustomer->checkIfRegisteredInLoyaltyPoint()) {
                /** @var float $discount */
                $discount = $loggedCustomer->getOpenLoyaltyDiscount();
            }

            if (is_null($discount)) {
                $this->unsetDiscountAndBack($quote);
                $this
                    ->_getSession()
                    ->addError(
                        $helperLoyalty->__('You don\'t have the loyalty discount right now.')
                    );

                return;
            }

            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setLoyaltyDiscount($discount)
                ->collectTotals()
                ->save();

            Mage::getSingleton('checkout/session')->setData(Divante_OpenLoyalty_Helper_Config::LOYALTY_IN_USE, true);
            $this
                ->_getSession()
                ->addSuccess(
                    $helperLoyalty->__('Successfully applied your loyalty discount.')
                );

        } catch (Exception $e) {
            $this
                ->_getSession()
                ->addError($helperLoyalty->__('Cannot apply the loyalty discount right now.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

    /**
     * Unset Open loyalty discount from quote and set URL header back
     *
     * @param Divante_OpenLoyalty_Model_Quote $quote
     *
     * @throws Mage_Exception
     */
    protected function unsetDiscountAndBack(Divante_OpenLoyalty_Model_Quote $quote)
    {
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote
            ->unsetLoyaltyDiscount()
            ->collectTotals()
            ->save();
        $this->_goBack();
    }

    /**
     * @return void
     */
    protected function resetLoyaltyInUse()
    {
        Mage::getSingleton('checkout/session')->unsetData(Divante_OpenLoyalty_Helper_Config::LOYALTY_IN_USE);
    }
}
