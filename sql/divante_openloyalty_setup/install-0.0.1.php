<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Return array with default attribute options
 *
 * @return array
 */
function addDefaultOptions()
{
    return [
        'backend'        => '',
        'input'          => 'text',
        'source'         => '',
        'visible'        => true,
        'required'       => false,
        'default'        => '',
        'frontend'       => '',
        'unique'         => false,
        'frontend_class' => 'disabled',
        'global'         => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined'   => false,
    ];
}

/** @var Mage_Customer_Model_Resource_Setup $installer */
$installer = new Mage_Customer_Model_Resource_Setup('core_setup');
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('customer');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

/** Add customer's attributes for Open Loyalty */
/** @var array $attributes Array of customer new attributes and theirs options */
$attributes = [
    'open_loyalty_id'       => [
        'type'  => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'label' => 'Open Loyalty Id',
        'note'  => 'Open Loyalty Id',
    ],
    'open_loyalty_discount' => [
        'type'  => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'label' => 'Open Loyalty discount',
        'note'  => 'Open Loyalty discount amount in %',
    ],
    'open_loyalty_date'     => [
        'type'  => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'label' => 'Open Loyalty register date',
        'note'  => 'Date of register customer in loyalty point',
    ],
    'open_loyalty_password' => [
        'type'  => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'label' => 'Open Loyalty password',
        'note'  => 'Customer password from Open Loyalty',
    ],
];

try {
    $used_in_forms[] = 'adminhtml_customer';

    /**
     * @var string $attribute
     * @var array  $options
     */
    foreach ($attributes as $attribute => $options) {
        $installer->removeAttribute('customer', $attribute);

        $installer->addAttribute(
            'customer',
            $attribute,
            array_merge($options, addDefaultOptions())
        );

        $installer->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $attributeGroupId,
            $attribute,
            '999'  //sort_order
        );

        /** @var Mage_Eav_Model_Entity_Attribute_Abstract $attributeModel */
        $attributeModel = Mage::getSingleton("eav/config")
                              ->getAttribute("customer", $attribute);

        $attributeModel
            ->setData('used_in_forms', $used_in_forms)
            ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
