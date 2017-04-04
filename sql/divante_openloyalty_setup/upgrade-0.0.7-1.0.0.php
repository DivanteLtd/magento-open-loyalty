<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

$installer    = new Mage_Customer_Model_Resource_Setup('core_setup');
$entityTypeId = $installer->getEntityTypeId('customer');

$installer->removeAttribute($entityTypeId, 'open_loyalty_discount');
$installer->removeAttribute($entityTypeId, 'open_loyalty_date');
$installer->removeAttribute($entityTypeId, 'open_loyalty_password');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number');
$installer->removeAttribute($entityTypeId, 'open_loyalty_level_id');
