<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Override block model of coupon code in cart
 * 
 * Class Divante_OpenLoyalty_Block_Cart_Coupon
 */
class Divante_OpenLoyalty_Block_Cart_Coupon extends Mage_Checkout_Block_Cart_Coupon
{
    /**
     * Return bool if customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        
        return $session->isLoggedIn();
    }

    /**
     * Return customer's loyalty discount as percentage
     *
     * @param string $discountSuffix Suffix for discount amount
     * @param int    $precision
     *
     * @return float
     */
    public function getCustomerLoyaltyDiscount($discountSuffix = '%', $precision = 2)
    {
        if ($this->isCustomerRegisterInLoyalty()) {
            /** @var string $discount */
            $discount = $this->getCustomerDiscountAmount();
        } else {
            $discount = $this->getLoyaltyHelper()->getInitialProgramDiscount();
        }

        $discount = sprintf("%s%s", number_format($discount, $precision), $discountSuffix);

        return $discount;
    }

    /**
     * Return amount of customer's loyalty discount
     *
     * @return float|null
     */
    public function getCustomerDiscountAmount()
    {
        /** @var Divante_OpenLoyalty_Model_Customer $customer */
        $customer = $this->getCustomer();

        return $customer->getOpenLoyaltyDiscount() ?: 0;
    }

    /**
     * Get message of customer's discount
     *
     * @return string
     */
    public function getDiscountLabel()
    {
        if ($this->getCustomerDiscountAmount()) {
            $msg = 'You can use loyalty discount';
        } else {
            $msg = 'You\'ve got loyalty discount';
        }

        return $this->getLoyaltyHelper()->__($msg);
    }

    /**
     * Check if customer is registering in loyalty program
     *
     * @return bool
     */
    public function isCustomerRegisterInLoyalty()
    {
        /** @var Divante_OpenLoyalty_Model_Customer $customer */
        $customer = $this->getCustomer();

        /** @var bool $isEnabled */
        $isEnabled = $this->getLoyaltyHelper()->isEnabled();

        return (
            $this->isLoggedIn()
            && $isEnabled
            && $customer->checkIfRegisteredInLoyaltyPoint());
    }

    /**
     * Return loyalty discount amount in used
     *
     * @return float
     */
    public function getLoyaltyDiscountInUse()
    {
        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = $this->getQuote();

        return $quote->getLoyaltyDiscount();
    }

    /**
     * Return loyalty discount amount in used formatted as percentage
     *
     * @param string $discountSuffix Suffix for discount amount
     *
     * @return string
     */
    public function getLoyaltyDiscountInUseFormatted($discountSuffix = '%')
    {
        /** @var string $discount */
        $discount = sprintf(
            "%s%s",
            number_format($this->getLoyaltyDiscountInUse(), 2),
            $discountSuffix
        );

        return $discount;
    }

    /**
     * Return Open Loyalty post form Url
     *
     * @return string
     */
    public function getDiscountPostUrl()
    {
        return $this->getUrl('loyalty_checkout/cart/loyaltyDiscountPost');
    }

    /**
     * Return set coupon code form post Url
     *
     * @return string
     */
    public function getCouponPostUrl()
    {
        return $this->getUrl('loyalty_checkout/cart/couponPost');
    }

    /**
     * Return popup message for encourage guest for log in
     *
     * @return string
     */
    public function getPopupMessage()
    {
        return $this
            ->getLoyaltyHelper()
            ->getFrontendConfig()['popup_message'];
    }

    /**
     * Check if coupon is applied
     *
     * @return int
     */
    public function isCouponApply()
    {
        return strlen($this->getCouponCode());
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
     * Return bool if register in loyalty point is enabled
     *
     * @return bool
     */
    public function isLoyaltyEnabled()
    {
        return $this->getLoyaltyHelper()->isEnabled();
    }
}
