<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

use Divante_OpenLoyalty_Helper_Config as Config;

/**
 * Class Divante_OpenLoyalty_Model_Cron
 */
class Divante_OpenLoyalty_Model_Cron
{
    /**
     * @throws \Divante_OpenLoyalty_Exception_Exception
     */
    public function updateDefaultDiscount()
    {
        $requestModel = Mage::getModel('divante_openloyalty/request_level');
        $levels = $requestModel->getLevels();

        usort($levels, function ($a, $b) {
            return $a['conditionValue'] - $b['conditionValue'];
        });

        $lowerLevel = reset($levels);

        if (!isset($lowerLevel['reward']) || !isset($lowerLevel['reward']["value"]) || !isset($lowerLevel['name'])) {
            throw new Divante_OpenLoyalty_Exception_Exception("Wrong level data!");
        }

        $reward = $lowerLevel["reward"]["value"] * 100;
        Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_DEFAULT_LEVEL_NAME, $lowerLevel['name']);
        Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_DEFAULT_LEVEL_DISCOUNT, $reward);
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }
}
