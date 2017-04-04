<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Adminhtml_CustomerController
 */
class Divante_OpenLoyalty_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return \Mage_Adminhtml_Controller_Action
     */
    public function unregisterAction()
    {
        if (!$this->_validateSecretKey()) {
            return $this->_redirectReferer();
        }

        $customerId = intval($this->getRequest()->getParam('customerId'));
        if (is_int($customerId) && $customerId != 0) {
            try {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                $customer->unregisterFromLoyaltyProgram();
                $customer->save();

                $this->_getSession()->addSuccess('User has been unregistered from loyalty program.');
            } catch (Exception $e) {
                $this->_getSession()->addError('Error occurred!');
            }
        }

        return $this->_redirectReferer();
    }
}
