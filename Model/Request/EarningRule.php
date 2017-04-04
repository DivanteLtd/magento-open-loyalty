<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_EarningRule
 */
class Divante_OpenLoyalty_Model_Request_EarningRule extends Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var string
     */
    const GET_EARNING_RULES_RESOURCE = 'api/earningRule';

    /**
     * @param bool $onlyActive
     *
     * @return array
     */
    public function getEarningRules($onlyActive = true)
    {
        $response = [];

        try {
            /** @var array $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::GET_EARNING_RULES_RESOURCE, Zend_Http_Client::GET)
                ->sendRequest();
            $response = $this->getEarningRuleParameters($response);

            if(!empty($response) && $onlyActive) {
                $response = $this->getOnlyActiveEarningRules($response);
            }
        } catch (Exception $e) {
            $this->getLogHelper()->logException($e);
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @return array
     */
    protected function getEarningRuleParameters($response)
    {
        if (empty($response)) {
            return [];
        }

        if (!isset($response['earningRules'])
            || empty($response['earningRules'])
        ) {
            return [];
        }

        return $response['earningRules'];
    }

    /**
     * @param $response
     *
     * @return array
     */
    protected function getOnlyActiveEarningRules($response)
    {
        return array_filter($response, function($rule) {
            return ($rule['active'] === true);
        });
    }
}
