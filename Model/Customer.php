<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

use Divante_OpenLoyalty_Model_Observer_Checkout as Observer_Checkout;

/**
 * Class Divante_OpenLoyalty_Model_Customer
 *
 * @method void setOpenLoyaltyId(string $value)
 * @method string getOpenLoyaltyId()
 */
class Divante_OpenLoyalty_Model_Customer extends Mage_Customer_Model_Customer
{
    /**
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    const CACHE_LIFETIME = 3600;

    /**
     * @var string
     */
    const CUSTOMER_STATUS_CACHE_KEY = 'customer_oloy_status_';

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $request;

    /**
     * @var Divante_OpenLoyalty_Model_Request_Customer
     */
    protected $requestModel;

    /**
     * @var Divante_OpenLoyalty_Helper_Config
     */
    protected $loyaltyConfigHelper;

    /**
     * @var string
     */
    private $loyaltyProgramLabel;

    /**
     * @var string
     */
    public function _construct()
    {
        parent::_construct();

        $this->loyaltyConfigHelper = Mage::helper('divante_openloyalty/config');
        $this->loyaltyProgramLabel = $this->loyaltyConfigHelper->getLoyaltyProgramLabel();
    }

    /**
     * Return Open Loyalty Request model
     *
     * @return Divante_OpenLoyalty_Model_Request_Customer
     */
    protected function getRequestModel()
    {
        if (isset($this->requestModel)) {
            return $this->requestModel;
        }

        $this->requestModel = Mage::getSingleton('divante_openloyalty/request_customer');

        return $this->requestModel;
    }

    /**
     * Register customer account in Open Loyalty point
     *
     * @return bool
     */
    protected function registerInOpenLoyalty()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');

        if ($this->checkIfRegisteredInLoyaltyPoint()) {
            $session->addNotice($this->loyaltyConfigHelper->__('You are already registered in loyalty point.'));

            return false;
        }

        list($haveLoyaltyCard, $loyaltyCardNumber) = $this->getLoyaltyRegisterParams();

        if (!empty($haveLoyaltyCard) && !empty($loyaltyCardNumber)) {
            return $this->attachExistingLoyaltyAccountToCustomer($loyaltyCardNumber);
        }

        return $this->registerNewAccount();
    }

    /**
     * @return array
     */
    protected function getLoyaltyRegisterParams()
    {
        $request = $this->getRequest();
        if ($request->getModuleName() == "checkout") {
            $session = Mage::getSingleton('checkout/session');
            $haveLoyaltyCard = $session->getData(Observer_Checkout::ATTACH_LOYALTY_CARD_SESSION_KEY);
            $loyaltyCardNumber = $session->getData(Observer_Checkout::LOYALTY_CARD_NUMBER_SESSION_KEY);
        } else {
            //Default behaviour for customer register form
            $haveLoyaltyCard = $request->getParam($this->loyaltyConfigHelper->getHaveLoyaltyCardInputName());
            $loyaltyCardNumber = $request->getParam($this->loyaltyConfigHelper->getLoyaltyCardInputName());
        }

        return [$haveLoyaltyCard, $loyaltyCardNumber];
    }

    /**
     * @param string $loyaltyCardNumber
     *
     * @return bool
     */
    public function attachExistingLoyaltyAccountToCustomer($loyaltyCardNumber)
    {
        $requestModel = $this->getRequestModel();
        $session = Mage::getSingleton('customer/session');

        /** @var array|false $response */
        $response
            = $requestModel->getCustomerByLoyaltyCard($loyaltyCardNumber);

        if (!empty($response)
            && isset($response['customerId'])
        ) {
            $customerData = $response;

            if (is_array($customerData)) {
                $response = array_merge($response, $customerData);
            }

            $this->setLoyaltyData($response);

            $session
                ->addSuccess($this->loyaltyConfigHelper->__('You have successfully attached your account with %s.',
                    $this->loyaltyProgramLabel));

            return true;
        } else {
            $this->setLoyaltyData();

            $session
                ->addError($this->loyaltyConfigHelper->__('Error during attaching your account with %s.',
                    $this->loyaltyProgramLabel));

            return false;
        }
    }

    /**
     * @return bool
     */
    protected function registerNewAccount()
    {
        $requestModel = $this->getRequestModel();
        $session = Mage::getSingleton('customer/session');

        /** @var array|false $response */
        $response = $requestModel->registerCustomer($this);

        if (!empty($response)
            && isset($response['customerId'])
        ) {
            /** @var array $customerData */
            $customerData = $requestModel->getCustomer($response['customerId']);

            if (is_array($customerData)) {
                $response = array_merge($response, $customerData);
            }

            $this->setLoyaltyData($response);

            $session
                ->addSuccess($this->loyaltyConfigHelper->__('You have successfully registered account in %s.',
                    $this->loyaltyProgramLabel));

            return true;
        } else {
            $this->setLoyaltyData();

            $session
                ->addError($this->loyaltyConfigHelper->__('Error during registering the customer\'s account in %s.',
                    $this->loyaltyProgramLabel));

            return false;
        }
    }

    /**
     * Check if current customer is already has Open Loyalty Id
     *
     * @return bool
     */
    public function checkIfRegisteredInLoyaltyPoint()
    {
        return (!empty($this->getOpenLoyaltyId()));
    }

    /**
     * Register customer in OL before save customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function _beforeSave()
    {
        if ($this->doRegisterInLoyaltyPoint() && $this->checkLoyaltyCardNumber()
            && $this->checkCustomerEmailInProgram() && $this->checkCustomerPhoneInProgram()
        ) {
            $this->registerInOpenLoyalty();
        }

        return parent::_beforeSave();
    }

    /**
     * Return bool if guest wants register in Open Loyalty
     *
     * @return bool
     */
    protected function doRegisterInLoyaltyPoint()
    {
        $request = $this->getRequest();


        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /** @var bool $hasParam */
        $hasParam = $request->has($this->loyaltyConfigHelper->getRegisterInLoyaltyInputName());

        /** @var bool $isAdmin */
        $isAdmin = Mage::app()
            ->getStore()
            ->isAdmin();

        return (!$this->checkIfRegisteredInLoyaltyPoint()
            && ($hasParam
                || ($quote->doCustomerRegisterInLoyalty() && !$isAdmin)
            ));
    }

    /**
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function checkLoyaltyCardNumber()
    {
        $cardNumber = $this->getRequest()->getParam($this->loyaltyConfigHelper->getLoyaltyCardInputName());
        $haveCardNumber = $this->getRequest()->getParam($this->loyaltyConfigHelper->getHaveLoyaltyCardInputName());

        if (!empty($cardNumber) && $haveCardNumber) {
            $customer = $this->getRequestModel()->getCustomerByLoyaltyCard($cardNumber);
            if (empty($customer)) {
                throw new Mage_Core_Exception($this->loyaltyConfigHelper->__('Specified card does not exist.'));
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function checkCustomerEmailInProgram()
    {
        $email = $this->getRequest()->getParam('email');
        $cardNumber = $this->getRequest()->getParam($this->loyaltyConfigHelper->getLoyaltyCardInputName());
        $haveCardNumber = $this->getRequest()->getParam($this->loyaltyConfigHelper->getHaveLoyaltyCardInputName());

        if (!empty($email) && !$haveCardNumber && empty($cardNumber)) {
            $customer = $this->getRequestModel()->getCustomerByLoyaltyEmail($email);
            if (!empty($customer)) {
                throw new Mage_Core_Exception(
                    $this->loyaltyConfigHelper->__('There is customer registered in loyalty program with same email, please enter loyalty card number.')
                );
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function checkCustomerPhoneInProgram()
    {
        $phone = $this->getRequest()->getParam('phone');

        if (!empty($phone)) {
            $customer = $this->getRequestModel()->getCustomerByPhone($phone);
            if (!empty($customer)) {
                throw new Mage_Core_Exception(
                    $this->loyaltyConfigHelper->__('User with such data already exists in %s.',$this->loyaltyProgramLabel)
                );
            }
        }

        return true;
    }

    /**
     * Set loyalty customer's data
     *
     * @param array|null $response
     *
     * @return $this
     */
    protected function setLoyaltyData($response = null)
    {
        $id = (isset($response['customerId']))
            ? $response['customerId']
            : '';

        $this->setOpenLoyaltyId($id);

        return $this;
    }

    /**
     * Return customer's Open Loyalty discount in percentage
     *
     * @return float
     */
    public function getOpenLoyaltyDiscount()
    {
        if (!$this->checkIfRegisteredInLoyaltyPoint()) {
            $this->setOpenLoyaltyDiscount(null);
        }

        $status = $this->getCustomerStatusInLoyaltyProgram();
        if (isset($status['level'])) {
            return floatval($status['level']);
        }

        return 0.0;
    }

    /**
     * Update customer's data in Open Loyalty
     *
     * @return Divante_OpenLoyalty_Model_Customer
     */
    public function updateOpenLoyalty()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');

        if (!$this->checkIfRegisteredInLoyaltyPoint()) {
            $session
                ->addError($this->loyaltyConfigHelper->__('Error - you are not registered in loyalty program.'));

            return $this;
        }

        /** @var array|false $response */
        $response = $this
            ->getRequestModel()
            ->updateCustomer($this);

        if (!empty($response)) {
            $session
                ->addSuccess($this->loyaltyConfigHelper->__('You have successfully updated your data in loyalty point.'));

            return $this;
        } else {
            $session
                ->addError($this->loyaltyConfigHelper->__('Error during updating data in loyalty point.'));

            return $this;
        }
    }

    /**
     * @return array
     */
    public function getCustomerStatusInLoyaltyProgram()
    {
        $cache = Mage::app()->getCache();
        $customerStatus = $cache->load($this->getCustomerStatusCacheKey(), false, true);
        if (is_string($customerStatus)) {
            $customerStatus = unserialize($customerStatus);
        }

        if (!is_array($customerStatus)) {
            $response = Mage::getModel('divante_openloyalty/request_customer')
                ->getCustomerStatus($this->getOpenLoyaltyId());
            if (!empty($response) && is_array($response)) {
                $customerStatus = $response;
                $cache->save(serialize($customerStatus), $this->getCustomerStatusCacheKey(), [], self::CACHE_LIFETIME);
            } else {
                $customerStatus = [];
            }
        }

        return $customerStatus;
    }

    public function cleanCustomerStatusCache()
    {
        Mage::app()->getCache()->clean('all', [$this->getCustomerStatusCacheKey()]);
    }

    /**
     * @return string
     */
    public function getCustomerDiscountLevel()
    {
        $status = $this->getCustomerStatusInLoyaltyProgram();
        if (isset($status['level']) && isset($status['levelName'])) {
            return sprintf("%s -%d%%", $status['levelName'], $status['level']);
        }

        return '';
    }

    /**
     * @return string
     */
    private function getCustomerStatusCacheKey()
    {
        return self::CUSTOMER_STATUS_CACHE_KEY . $this->getId();
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest()
    {
        if (empty($this->request)) {
            $this->request = Mage::app()->getRequest();
        }

        return $this->request;
    }

    public function unregisterFromLoyaltyProgram()
    {
        $this->setOpenLoyaltyId('');
        $cache = Mage::app()->getCache();
        $cache->remove($this->getCustomerStatusCacheKey());
    }

    /**
     * @return string
     */
    public function getLoyaltyCardNumber()
    {
        $cardNumber = "";

        if (!empty($this->getOpenLoyaltyId())) {
            $customerData = Mage::getModel('divante_openloyalty/request_customer')
                ->getCustomer($this->getOpenLoyaltyId());
            $cardNumber = $customerData['loyaltyCardNumber'] ?: '';
        }

        return $cardNumber;
    }
}
