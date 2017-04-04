<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Customer_LoyaltyInfo
 */
class Divante_OpenLoyalty_Block_Customer_LoyaltyInfo extends Divante_OpenLoyalty_Block_Customer_Base
{
    /**
     * @return array
     */
    public function getBirthDate()
    {
        $date = $this->getLoyaltyData('birthDate');
        $dateArray = ['day' => '', 'month' => '', 'year' => ''];
        if (!empty($date)) {
            $dateArray = date_parse($date);
        }

        return $dateArray;
    }

    /**
     * @return string
     */
    public function getOLOYPanelUrl()
    {
        return $this->getLoyaltyHelper()->getUrlToCustomerPanel();
    }
}
