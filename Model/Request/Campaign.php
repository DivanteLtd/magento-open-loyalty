<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Campaign
 */
class Divante_OpenLoyalty_Model_Request_Campaign extends Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var string
     */
    const GET_CUSTOMER_CAMPAIGNS_RESOURCE = 'api/admin/customer/%s/campaign/available?page=1&perPage=1000';

    /**
     * @var string
     */
    const GET_CUSTOMER_CAMPAIGNS_BOUGHT_RESOURCE = 'api/admin/customer/%s/campaign/bought?includeDetails=true';

    /**
     * @var string
     */
    const BUY_CUSTOMER_CAMPAIGN_RESOURCE = 'api/admin/customer/%s/campaign/%s/buy';

    /**
     * @param string $customerId
     *
     * @return array|false
     */
    public function getCustomerCampaigns($customerId)
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf(self::GET_CUSTOMER_CAMPAIGNS_RESOURCE, $customerId);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();

            $response = $this->campaignsResponseParameters($response);
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
    public function getCustomerBoughtCampaigns($customerId)
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf(self::GET_CUSTOMER_CAMPAIGNS_BOUGHT_RESOURCE, $customerId);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();

            $response = $this->campaignsResponseParameters($response);
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param string $customerId
     * @param string $campaignId
     *
     * @return array|false
     */
    public function buyCustomerCampaign($customerId, $campaignId)
    {
        $response = [];

        try {
            if (!$customerId) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf(self::BUY_CUSTOMER_CAMPAIGN_RESOURCE, $customerId, $campaignId);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource)
                ->sendRequest();
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param array $response
     *
     * @return array
     */
    private function campaignsResponseParameters($response)
    {
        if (empty($response)) {
            return [];
        }

        if (!isset($response['campaigns'])
            || empty($response['campaigns'])
        ) {
            return [];
        }

        return $response['campaigns'];
    }
}
