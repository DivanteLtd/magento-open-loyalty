<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Resource_Setup
 */
class Divante_OpenLoyalty_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Return array with default attribute options
     *
     * @return array
     */
    public function addDefaultOptions()
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
            'user_defined'   => false
        ];
    }
}
