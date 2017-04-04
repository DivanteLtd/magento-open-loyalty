<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Customer
 */
class Divante_OpenLoyalty_Model_Request_Customer extends Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var string
     */
    const REGISTER_CUSTOMER_RESOURCE = 'api/customer/register';

    /**
     * @var string
     */
    const GET_CUSTOMER_RESOURCE = 'api/customer';

    /**
     * @param Divante_OpenLoyalty_Model_Customer $customer
     *
     * @return array
     */
    public function registerCustomer(Divante_OpenLoyalty_Model_Customer $customer)
    {
        $response = [];

        try {
            $customerParser = Mage::getModel('divante_openloyalty/parser_customer');
            $customerRequestParams = $customerParser->getCustomerRegisterRequestParameters($customer);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::REGISTER_CUSTOMER_RESOURCE)
                ->setParameters($customerRequestParams)
                ->sendRequest();
            $response = $this->getRegisterResponseParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }


    /**
     * @param string $customerId
     *
     * @return array
     */
    public function getCustomer($customerId = '')
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf("%s/%s", rtrim(self::GET_CUSTOMER_RESOURCE, '/'), $customerId);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param string $loyaltyCardNumber
     *
     * @return array|false
     */
    public function getCustomerByLoyaltyCard($loyaltyCardNumber = '')
    {
        return $this->getCustomer($loyaltyCardNumber);
    }

    /**
     * @param string $customerId
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public function updateCustomerValue($customerId, $key, $value)
    {
        $response = [];
        $customerData = $this->getCustomer($customerId);

        if (!empty($customerData)) {
            $updatedCustomerData = [];
            $requiredFields = ["firstName", "lastName", "email", "agreement1"];
            foreach ($requiredFields as $field) {
                $updatedCustomerData[$field] = $customerData[$field];
            }

            $updatedCustomerData[$key] = $value;

            try {
                /** @var array|false $response */
                $response = $this
                    ->getConnectionHelper()
                    ->setAdapter(self::GET_CUSTOMER_RESOURCE . '/' . $customerId, Zend_Http_Client::PUT)
                    ->setParameters(["customer" => $updatedCustomerData])
                    ->sendRequest();

                $response = $this->getRegisterResponseParameters($response);
            } catch (Exception $e) {
                $this->getLogHelper()->logException($e);
            }
        }

        return $response;
    }

    /**
     * @param string $email
     *
     * @return array|false
     */
    public function getCustomerByLoyaltyEmail($email = '')
    {
        $response = [];

        try {
            if (!$email) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf("%s?email=%s", rtrim(self::GET_CUSTOMER_RESOURCE, '/'), $email);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();

            $response = $this->getCustomerParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param string $phone
     *
     * @return array|false
     */
    public function getCustomerByPhone($phone = '')
    {
        $response = [];

        try {
            if (!$phone) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf("%s?phone=%s", rtrim(self::GET_CUSTOMER_RESOURCE, '/'), $phone);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();

            $response = $this->getCustomerParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param string $customerId
     *
     * @return array|false
     */
    public function getCustomerStatus($customerId = '')
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf("%s/%s/status", rtrim(self::GET_CUSTOMER_RESOURCE, '/'), $customerId);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param array|false $response
     *
     * @return array
     */
    protected function getRegisterResponseParameters($response)
    {
        if (empty($response)) {
            return [];
        }

        if (!isset($response['customerId'])
            || empty($response['customerId'])
        ) {
            return [];
        }

        return $response;
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function getCustomerParameters($response)
    {
        if (empty($response) || !isset($response["customers"])) {
            return [];
        }

        return reset($response["customers"]);
    }
}
