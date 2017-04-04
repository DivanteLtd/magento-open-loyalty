<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Adminhtml_System_Config_SimpleText
 */
class Divante_OpenLoyalty_Block_Adminhtml_System_Config_SimpleText
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<tr><td class="label">%s</td><td class="value">%s</td></tr>',
            $element->getLabel(),
            $element->getEscapedValue()
        );
    }
}
