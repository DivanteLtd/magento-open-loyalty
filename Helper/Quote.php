<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Divante_OpenLoyalty_Helper_Quote
 */
class Divante_OpenLoyalty_Helper_Quote extends Mage_Core_Helper_Abstract
{
    const PREDICTED_POINTS_CACHE_KEY = 'predicted_points';

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param bool                   $cache
     *
     * @return int
     */
    public function getPredictedPointsForQuote(Mage_Sales_Model_Quote $quote, $cache = true)
    {
        $points = Mage::getSingleton('checkout/session')->getData(self::PREDICTED_POINTS_CACHE_KEY);

        if ($points != null && $cache) {
            return $points;
        }

        try {
            $points = Mage::getModel('divante_openloyalty/request_transaction')->simulateQuotePoints($quote);

            if ($points > 0) {
                Mage::getSingleton('checkout/session')->setData(self::PREDICTED_POINTS_CACHE_KEY, $points);
            }

            return $points;
        } catch (Exception $e) {
            Mage::helper('divante_openloyalty/log')->logException($e);
        }

        return 0;
    }
}
