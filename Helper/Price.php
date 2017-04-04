<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Helper_Price
 */
class Divante_OpenLoyalty_Helper_Price extends Mage_Core_Helper_Abstract
{
    /**
     * @param float $leftOperand
     * @param float $rightOperand
     * @param int   $precision
     *
     * @return float
     */
    public function subtractFloat($leftOperand, $rightOperand, $precision = 2)
    {
        if (extension_loaded('bcmath')) {
            return floatval(bcsub($leftOperand, $rightOperand, $precision));
        }

        return floatval(number_format($leftOperand - $rightOperand, $precision, ".", ""));
    }
}
