<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/** @var Mage_Sales_Model_Resource_Setup $installer */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup();

/**
 * Add 'loyalty_discount' and 'register_in_loyalty' attributes for quote and order entities
 */

/** @var array $attributes */
$attributes = [
    'loyalty_discount'       => Varien_Db_Ddl_Table::TYPE_FLOAT,
    'register_in_loyalty'    => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'loyalty_transaction_id' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
];

$entities = [
    'quote',
    'order',
];

$options = [
    'visible'      => true,
    'required'     => false,
    'user_defined' => false,
];

foreach ($entities as $entity) {
    /**
     * @var string $attribute
     * @var string $type
     */
    foreach ($attributes as $attribute => $type) {
        $options['type'] = $type;
        $installer->addAttribute($entity, $attribute, $options);
    }
}

$installer->endSetup();
