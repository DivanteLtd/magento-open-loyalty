<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Block_Adminhtml_Customer_Renderer_Loyalty
 */
class Divante_OpenLoyalty_Block_Adminhtml_Customer_Renderer_Loyalty
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render HTML for register in OL field
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var string $value */
        $value = $element->getEscapedValue();

        if ($value !== '') {
            return $this->registeredCustomerHtml($element);
        }

        return '';
    }

    /**
     * HTML for registered customer
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function registeredCustomerHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var Divante_OpenLoyalty_Helper_Config $loyaltyHelper */
        $loyaltyHelper = Mage::helper('divante_openloyalty/config');

        $unsubscribeUrl = Mage::helper('adminhtml')->getUrl('loyaltyprogram/customer/unregister', array('customerId' => Mage::app()->getRequest()->getParam('id')));
        /** @var Mage_Adminhtml_Block_Widget_Button $button */
        $button = $this
            ->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData([
                'id'    => 'unregister_loyalty_customer',
                'label' => $loyaltyHelper->__('Unregister from OL'),
                'title' => $loyaltyHelper->__('Unregister the account from loyalty program'),
                'class' => 'save',
                'element_name' => $loyaltyHelper->getUnregisterFromLoyaltyInputName(),
                'onclick' => 'confirmSetLocation(\'' . $loyaltyHelper->__('Proceed?') . '\',\''.$unsubscribeUrl.'\');'
            ]);

        $html  = '<tr>';
        $html .= '<td class="label">' . $element->getLabelHtml() . '</td>';
        $html .= '<td class="value">' . $element->getElementHtml() . '</td>';
        $html .= '</tr>' . "\n";
        $html .= '<tr>';
        $html .= '<td class="label"><label>' . $button->toHtml() . '</label></td>';
        $html .= '<td class="value">&nbsp;</td>';
        $html .= '</tr>' . "\n";

        return $html;
    }
}
