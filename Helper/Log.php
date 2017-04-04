<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Helper_Log
 */
class Divante_OpenLoyalty_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    const LOG_FILE = "openloyalty.log";

    /**
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, self::LOG_FILE);
    }

    /**
     * @param Exception $e
     * @param int $level
     */
    public function logException(Exception $e, $level = Zend_Log::ERR)
    {
        Mage::log($e->__toString(), $level, self::LOG_FILE);
    }
}
