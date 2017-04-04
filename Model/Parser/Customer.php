<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Parser_Customer
 */
class Divante_OpenLoyalty_Model_Parser_Customer extends Divante_OpenLoyalty_Model_Parser_Base
{
    /**
     * Customer's request parameters main key in array
     *
     * @var string
     */
    const CUSTOMER_MAIN_KEY = 'customer';

    /**
     * @param Divante_OpenLoyalty_Model_Customer $customer
     *
     * @return array|null
     */
    public function getCustomerRegisterRequestParameters(Divante_OpenLoyalty_Model_Customer $customer)
    {
        if (!$this->validateCustomer($customer)) {
            return null;
        }

        if ($customer->getDefaultBillingAddress() instanceof Mage_Customer_Model_Address) {
            /** @var Mage_Customer_Model_Address $address */
            $address = clone $customer->getDefaultBillingAddress();
        } else {
            /** @var Mage_Customer_Model_Address $address */
            $address = Mage::getModel('customer/address');
        }

        /** @var Divante_OpenLoyalty_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /** Copy address data from quote if customer has no billing address: */
        if ($quote instanceof Divante_OpenLoyalty_Model_Quote
            && $quote->getId()
            && $quote->doCustomerRegisterInLoyalty()
        ) {
            /** @var Mage_Sales_Model_Quote_Address $billing */
            $billing = $quote->getBillingAddress();

            if ($billing instanceof Mage_Sales_Model_Quote_Address) {
                $address->addData($billing->getData());
            }
        }

        /** @var array $parameters */
        $parameters = [
            self::CUSTOMER_MAIN_KEY => [
                'firstName'         => ucfirst($this->getCustomerData($customer, $address, 'firstname')),
                'lastName'          => ucfirst($this->getCustomerData($customer, $address, 'lastname')),
                'gender'            => $this->getGender($customer->getGender()),
                'email'             => trim($customer->getEmail()),
                'birthDate'         => $customer->getDob(),
                'phone'             => $address->getTelephone(),
                'loyaltyCardNumber' => $customer->getLoyaltyCardNumber(),
                'agreement1'        => true,
                'agreement2'        => $this->getIsCustomerSubscribed($customer),
            ]
        ];

        if ($referralEmail = $this->getReferralCustomerEmail()) {
            $parameters[self::CUSTOMER_MAIN_KEY]['referral_customer_email'] = $referralEmail;
        }

        /** @var array $parsedAddress */
        $parsedAddress = $this->getAddress($address);

        if ($parsedAddress['city'] !== '') {
            $parameters[self::CUSTOMER_MAIN_KEY]['address'] = $parsedAddress;
        }

        $this->getCompany($parameters[self::CUSTOMER_MAIN_KEY], $address, $customer);

        return $parameters;
    }

    /**
     * @param Divante_OpenLoyalty_Model_Customer $customer
     *
     * @return bool
     */
    protected function validateCustomer(Divante_OpenLoyalty_Model_Customer $customer)
    {
        if (!Zend_Validate::is($customer->getEmail(), 'EmailAddress')) {
            return false;
        }

        return true;
    }

    /**
     * @param string $gender
     *
     * @return string
     */
    protected function getGender($gender = '')
    {
        switch ($gender) {
            case '1':
                $gender = 'male';
                break;
            case '2':
                $gender = 'female';
                break;
        }

        return strtolower($gender);
    }

    /**
     * @param array                              $parameters
     * @param Mage_Customer_Model_Address        $address
     * @param Divante_OpenLoyalty_Model_Customer $customer
     */
    protected function getCompany(
        array &$parameters,
        Mage_Customer_Model_Address $address,
        Divante_OpenLoyalty_Model_Customer $customer
    ) {
        if ($address->getCompany()) {
            /** @var string $nip */
            $nip = ($address->getVatId() != '')
                ? $address->getVatId()
                : $customer->getTaxvat();

            $parameters['company'] = [
                'name' => ucfirst($address->getCompany()),
                'nip'  => $nip
            ];
        }
    }

    /**
     * @param Divante_OpenLoyalty_Model_Customer $customer
     * @param Mage_Customer_Model_Address        $address
     * @param string                             $data
     *
     * @return string
     */
    protected function getCustomerData(
        Divante_OpenLoyalty_Model_Customer $customer,
        Mage_Customer_Model_Address $address,
        $data = ''
    ) {
        if (!$data) {
            return '';
        }

        return $address->getData($data) ?: $customer->getData($data);
    }


    /**
     * @param Divante_OpenLoyalty_Model_Customer $customer
     *
     * @return bool
     */
    private function getIsCustomerSubscribed(Divante_OpenLoyalty_Model_Customer $customer)
    {
        $isSubscribed = Mage::app()->getRequest()->getParam('is_subscribed');

        if (!empty($isSubscribed)) {
            return boolval($isSubscribed);
        }

        $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());

        return ($subscriberModel->isSubscribed() ? true : false);
    }

    /**
     * @return string
     */
    private function getReferralCustomerEmail()
    {
        $referralEmail = Mage::app()->getRequest()->getParam($this->getConfigHelper()->getReferralCustomerEmailInputName());
        if(!empty($referralEmail)) {
            return $referralEmail;
        }

        return "";
    }
}
