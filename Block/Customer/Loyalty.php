<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Customer_Loyalty
 */
class Divante_OpenLoyalty_Block_Customer_Loyalty extends Divante_OpenLoyalty_Block_Customer_Base
{
    /**
     * @param string $discountSuffix Suffix for discount amount
     *
     * @return array
     */
    public function getCustomerLoyaltyData($discountSuffix = '%')
    {
        $return = [];

        /** @var Divante_OpenLoyalty_Model_Customer $customer */
        $customer = $this->getCustomer();

        if (!$customer->checkIfRegisteredInLoyaltyPoint()) {
            return $return;
        }

        /** @var string $discount */
        $discount = sprintf(
            "%s%s",
            number_format($customer->getOpenLoyaltyDiscount(), 2),
            $discountSuffix
        );

        $return = [
            'id' => $customer->getOpenLoyaltyId(),
            'loyaltyCardNumber' => $customer->getLoyaltyCardNumber(),
            'discount' => $discount,
            'loyalty_level_id' => $customer->getOpenLoyaltyLevelId(),
        ];

        return $return;
    }

    /**
     * @return string
     */
    public function getJoinProgramPostUrl()
    {
        return Mage::getUrl('loyaltyprogram/account/joinloyaltyPost');
    }

    /**
     * @return string
     */
    public function getReferralEmailValue()
    {
        if($this->getRequest()->getParam('referral')) {
            return base64_decode($this->getRequest()->getParam('referral'));
        }

        return "";
    }
}
