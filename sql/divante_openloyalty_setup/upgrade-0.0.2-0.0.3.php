<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/** Change store config for default sending emails */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$configValuesMap = [
    Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_TEMPLATE => 'customer_create_account_email_template',
    Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE                => 'sales_email_order_template',
    Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE          => 'sales_email_order_guest_template',
];

foreach ($configValuesMap as $configPath => $configValue) {
    $installer->setConfigData($configPath, $configValue);
}
