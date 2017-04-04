<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Abstract
 */
class Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var Divante_OpenLoyalty_Helper_Connection
     */
    protected $connectionHelper = null;

    /**
     * @var  Divante_OpenLoyalty_Helper_Log
     */
    protected $logHelper = null;

    /**
     * @return Divante_OpenLoyalty_Helper_Connection
     */
    protected function getConnectionHelper()
    {
        if ($this->connectionHelper === null) {
            $this->connectionHelper = Mage::helper('divante_openloyalty/connection');
        }

        return $this->connectionHelper;
    }

    /**
     * @return Divante_OpenLoyalty_Helper_Log
     */
    protected function getLogHelper()
    {
        if ($this->logHelper === null) {
            $this->logHelper = Mage::helper('divante_openloyalty/log');
        }

        return $this->logHelper;
    }
}
