<?php

/**
 * Class Divante_OpenLoyalty_Model_Request_Pos
 */
class Divante_OpenLoyalty_Model_Request_Pos extends Divante_OpenLoyalty_Model_Request_Abstract
{
    const GET_POS_BY_IDENTIFIER_RESOURCE = 'api/pos/identifier/';

    /**
     * @param string $identifier
     *
     * @return array
     */
    public function getPosByIdentifier($identifier)
    {
        $response = [];

        try {
            if (!$identifier) {
                return $response;
            }

            /** @var string $resource */
            $resource = sprintf(self::GET_POS_BY_IDENTIFIER_RESOURCE . $identifier);

            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter($resource, Zend_Http_Client::GET)
                ->sendRequest();

            $response = $this->getPosParameters($response);
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
    private function getPosParameters($response)
    {
        if (empty($response) || !isset($response["posId"])) {
            return [];
        }

        return $response;
    }
}
