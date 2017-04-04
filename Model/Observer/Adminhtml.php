<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

use Divante_OpenLoyalty_Helper_Config as Config;

/**
 * Class Divante_OpenLoyalty_Model_Observer_Adminhtml
 */
class Divante_OpenLoyalty_Model_Observer_Adminhtml
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @return void
     */
    public function afterConfigSave()
    {
        Mage::getModel('divante_openloyalty/cron')->updateDefaultDiscount();
        $this->getDefaultTierAssignType();
        $this->getCurrencyUnits();
        $this->getPosId();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    /**
     * @return void
     */
    private function getDefaultTierAssignType()
    {
        $settings = $this->getProgramSettings();

        if (isset($settings['tierAssignType'])) {
            Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_TIER_TYPE, $settings['tierAssignType']);
        }
    }

    /**
     * @return array
     */
    private function getProgramSettings()
    {
        if (empty($this->settings)) {
            $this->settings = Mage::getModel('divante_openloyalty/request_settings')->getSettings();
        }

        return $this->settings;
    }

    /**
     * @return void
     */
    private function getCurrencyUnits()
    {
        $settings = $this->getProgramSettings();

        if(isset($settings['programPointsSingular'])) {
            Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_CURRENCY_SINGULAR, $settings['programPointsSingular']);
        }

        if(isset($settings['programPointsPlural'])) {
            Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_CURRENCY_PLURAL, $settings['programPointsPlural']);
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function getPosId()
    {
        $posIdentifier = Mage::helper('divante_openloyalty/config')->getPosIdentifier();

        if(!empty($posIdentifier)) {
            $pos = Mage::getModel('divante_openloyalty/request_pos')->getPosByIdentifier($posIdentifier);

            if(!isset($pos['posId'])) {
                throw new Exception(sprintf("Pos with %s identifier doesn't exist!", $posIdentifier));
            }

            Mage::getConfig()->saveConfig(Config::XML_CONFIG_PATH_POS_ID, $pos['posId']);
        }
    }

    /**
     * Set renderer model for customer's open loyalty Id field
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCustomerLoyaltyField($observer)
    {
        /** @var Mage_Adminhtml_Block_Abstract $block */
        $block = $observer
            ->getEvent()
            ->getBlock();

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Form) {
            return;
        }

        if ($block->getType() === 'adminhtml/customer_edit_tab_account') {
            /** @var Varien_Data_Form $form */
            $form = $block->getForm();

            if (!$form instanceof Varien_Data_Form) {
                return;
            }

            /** @var Varien_Data_Form $fieldset */
            $fieldset = $form->getElement('base_fieldset');
            $label = Mage::helper('divante_openloyalty')->__('Loyalty discount');
            $fieldset->addField(
                'open_loyalty_discount',
                'text',
                [
                    'name'     => 'open_loyalty_discount',
                    'label'    => $label,
                    'title'    => $label,
                    'disabled' => true,
                    'class'    => 'disabled',
                    'value'    => $this->getCustomerDiscount()
                ],
                'open_loyalty_id'
            );

            /** @var Varien_Data_Form_Element_Text $loyaltyIdField */
            $loyaltyIdField = $form->getElement('open_loyalty_id');

            if ($loyaltyIdField instanceof Varien_Data_Form_Element_Abstract) {
                /** @var Divante_OpenLoyalty_Block_Adminhtml_Customer_Renderer_Loyalty $renderer */
                $renderer = Mage::getBlockSingleton('divante_openloyalty/adminhtml_customer_renderer_loyalty');
                $loyaltyIdField->setRenderer($renderer);
            }

            $this->disableLoyaltyFields($form);
        };
    }

    /**
     * Disable Open Loyalty fields in adminhtml
     *
     * @param Varien_Data_Form $form
     */
    protected function disableLoyaltyFields(Varien_Data_Form $form)
    {
        $fields = ['open_loyalty_id'];

        /** @var string $field */
        foreach ($fields as $field) {
            /** @var Varien_Data_Form_Element_Abstract $element */
            $element = $form->getElement($field);

            if ($element instanceof Varien_Data_Form_Element_Abstract) {
                $element->setDisabled('disabled');
            }
        }
    }

    /**
     * @return string
     */
    private function getCustomerDiscount()
    {
        $customerId = Mage::app()->getRequest()->getParam('id');

        return Mage::getModel('customer/customer')->load($customerId)->getCustomerDiscountLevel();
    }
}
