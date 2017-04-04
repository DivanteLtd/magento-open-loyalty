<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Order_Email_Items
 */
class Divante_OpenLoyalty_Block_Order_Email_Items extends Mage_Sales_Block_Order_Email_Items
{
    /**
     * Return show loyalty discount or not
     *
     * @return bool
     */
    public function isLoyaltyDiscount()
    {
        /** @var Divante_OpenLoyalty_Model_Order $order */
        $order = $this->getOrder();

        return $order->isLoyaltyDiscount();
    }

    /**
     * Get formatted order's loyalty discount as percentage
     *
     * @param string $discountSuffix Suffix for discount amount
     *
     * @return string
     */
    public function getFormattedLoyaltyDiscount($discountSuffix = '%')
    {
        /** @var Divante_OpenLoyalty_Model_Order $order */
        $order = $this->getOrder();

        /** @var string $discount */
        $discount = sprintf(
            "%s%s",
            number_format($order->getLoyaltyDiscount(), 2),
            $discountSuffix
        );

        return $discount;
    }

    /**
     * @return bool
     */
    public function hasGift()
    {
        /** @var Divante_OpenLoyalty_Model_Order $order */
        $order = $this->getOrder();

        /** @var bool $isAvailable */
        $isAvailable = $this->helper('giftmessage/message')
            ->isMessagesAvailable('order', $order, $order->getStore());

        return ($isAvailable && $order->getGiftMessageId());
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
}
