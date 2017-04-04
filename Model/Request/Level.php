<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Level
 */
class Divante_OpenLoyalty_Model_Request_Level extends Divante_OpenLoyalty_Model_Request_Abstract
{
    const GET_LEVELS_RESOURCE = 'api/level';

    /**
     * @return array
     */
    public function getLevels()
    {
        $response = [];

        try {
            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::GET_LEVELS_RESOURCE, Zend_Http_Client::GET)
                ->sendRequest();
            $response = $this->getLevelsFromResponse($response);
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
    protected function getLevelsFromResponse($response)
    {
        if (empty($response) || !isset($response["levels"])) {
            return [];
        }

        return $response["levels"];
    }
}
