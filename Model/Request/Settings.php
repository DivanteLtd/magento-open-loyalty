<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Request_Settings
 */
class Divante_OpenLoyalty_Model_Request_Settings extends Divante_OpenLoyalty_Model_Request_Abstract
{
    /**
     * @var string
     */
    const GET_SETTINGS_RESOURCE = 'api/settings';

    /**
     * @return array
     */
    public function getSettings()
    {
        $response = [];

        try {
            /** @var array|false $response */
            $response = $this
                ->getConnectionHelper()
                ->setAdapter(self::GET_SETTINGS_RESOURCE, Zend_Http_Client::GET)
                ->sendRequest();
            $response = $this->getSettingsFromResponse($response);
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
    protected function getSettingsFromResponse($response)
    {
        if (empty($response) || !isset($response["settings"])) {
            return [];
        }

        return $response["settings"];
    }
}
