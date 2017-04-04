<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$staticBlock = [
    'title' => 'Open loyalty registered user banner',
    'identifier' => Divante_OpenLoyalty_Helper_Config::REGISTERED_USER_BANNER_STATIC_BLOCK,
    'content' => 'Registered user banner',
    'is_active' => 1,
    'stores' => [0],
];

Mage::getModel('cms/block')->setData($staticBlock)->save();

$staticBlock = [
    'title' => 'Open loyalty unregistered user block',
    'identifier' => Divante_OpenLoyalty_Helper_Config::UNREGISTERED_USER_STATIC_BLOCK,
    'content' => 'Unregistered user banner',
    'is_active' => 1,
    'stores' => [0],
];

Mage::getModel('cms/block')->setData($staticBlock)->save();
