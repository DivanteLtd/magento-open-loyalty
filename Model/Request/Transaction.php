<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Transaction
 */
class Divante_OpenLoyalty_Model_Request_Transaction extends Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var string
     */
    const GET_CUSTOMER_TRANSACTIONS_RESOURCE = 'api/customer/transaction';

    /**
     * @var string
     */
    const SEND_ORDER_RESOURCE = 'api/transaction';

    /**
     * @var string
     */
    const GET_TRANSACTION_SIMULATE_RESOURCE = 'api/transaction/simulate';

    /**
     * @param $customerId
     *
     * @return array
     */
    public function getCustomerTransactions($customerId)
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf("%s?customerId=%s", rtrim(self::GET_CUSTOMER_TRANSACTIONS_RESOURCE, '/'), $customerId);

            /** @var array $response */
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
     * @param Divante_OpenLoyalty_Model_Order $order
     *
     * @return bool
     */
    public function sendPlacedOrder(Divante_OpenLoyalty_Model_Order $order)
    {
        try {
            $transactionParser = Mage::getModel('divante_openloyalty/parser_transaction');
            $orderData = $transactionParser->convertOrderToRequestParameters($order);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::SEND_ORDER_RESOURCE)
                ->setParameters($orderData)
                ->sendRequest();

            return $this->placedOrderResponseParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);

            return false;
        }
    }

    /**
     * @param Divante_OpenLoyalty_Model_Quote $quote
     *
     * @return int
     */
    public function simulateQuotePoints(Divante_OpenLoyalty_Model_Quote $quote)
    {
        $points = 0;

        try {
            $transactionParser = Mage::getModel('divante_openloyalty/parser_transaction');
            $orderData = $transactionParser->convertQuoteToRequestParameters($quote);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::GET_TRANSACTION_SIMULATE_RESOURCE)
                ->setParameters($orderData)
                ->sendRequest();

            $points = $this->simulateQuoteResponseParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $points;
    }


    /**
     * @param array|false $response
     *
     * @return array|false
     */
    protected function placedOrderResponseParameters($response)
    {
        if (empty($response)) {
            return false;
        }

        if (!isset($response['transactionId'])) {
            return false;
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @return int
     */
    protected function simulateQuoteResponseParameters($response)
    {
        $points = 0;

        if(isset($response['points'])) {
            $points = $response['points'];
        }

        return $points;
    }
}
