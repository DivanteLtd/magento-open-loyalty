<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_AccountController
 */
class Divante_OpenLoyalty_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Checking if user is logged in or not
     * If not logged in then redirect to customer login
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);

            // adding message in customer login page
            Mage::getSingleton('core/session')
                ->addError(Mage::helper('divante_openloyalty')->__('Please sign in or create a new account'));
        }
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Register logged customer in Loyalty Point action
     */
    public function registerLoyaltyAction()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = $this->_getSession();

        /** @var Divante_OpenLoyalty_Helper_Config $loyaltyHelper */
        $loyaltyHelper = Mage::helper('divante_openloyalty/config');

        if (!$session->isLoggedIn()
            || !$this->_validateFormKey()
            || !$this->getRequest()->has($loyaltyHelper->getRegisterInLoyaltyInputName())
        ) {
            $this->_redirectReferer(Mage::getUrl('*/*/index'));

            return;
        }

        /** @var Divante_OpenLoyalty_Model_Customer $customer */
        $customer = $session->getCustomer();

        /** In before save method is register customer call */
        $customer->save();

        $this->_redirectReferer(Mage::getUrl('*/*/index'));
    }

    /**
     * @return void
     */
    public function joinloyaltyAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($this->__('Join to %s', Mage::helper('divante_openloyalty/config')->getLoyaltyProgramLabel()));
        $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function joinloyaltyPostAction()
    {
        if(!$this->_validateFormKey()) {
            return $this->_redirect('/');
        }

        $loyaltyConfigHelper = Mage::helper('divante_openloyalty/config');
        $cardNumber = $this->getRequest()->getParam($loyaltyConfigHelper->getLoyaltyCardInputName());
        $cardOwner = $this->getRequest()->getParam($loyaltyConfigHelper->getConfirmCardOwnerInputName());

        if($cardOwner == 1 &&
            !empty($cardNumber)
        ) {
            try {
                /** @var Divante_OpenLoyalty_Model_Customer $customer */
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $attached = $customer->attachExistingLoyaltyAccountToCustomer($cardNumber);
                $customer->save();

                if($attached){
                    return $this->_redirect('loyaltyprogram/account');
                }
            } catch (Exception $e) {
                Mage::helper('divante_openloyalty/log')->logException($e);

                Mage::getSingleton('customer/session')
                    ->addError(
                        Mage::helper('divante_openloyalty')->__('Error during attaching your account with %s.', $loyaltyConfigHelper->getLoyaltyProgramLabel()
                        )
                    );
            }
        } else {
            Mage::getSingleton('customer/session')
                ->addError(Mage::helper('divante_openloyalty')->__('Enter card number and confirm that You are card owner'));
        }

        $this->_redirectReferer();
    }

    /**
     * @return void
     */
    public function transactionsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function pointsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function rewardsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
}
