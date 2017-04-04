<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Helper_Connection
 */
class Divante_OpenLoyalty_Helper_Connection extends Divante_OpenLoyalty_Helper_Config
{    
    /** @var Zend_Http_Client  */
    protected $adapter;

    /**
     * @var array
     */
    protected $params;

    /**
     * Set Http Client Adapter with Open Loyalty settings
     *
     * @param string $resource Name of REST resource call
     * @param bool   $authorization If true - send header token authorization based on JWT token
     * @param string $method Request method
     *
     * @return Divante_OpenLoyalty_Helper_Connection
     *
     * @throws Divante_OpenLoyalty_Exception_Exception
     * @throws Mage_Core_Exception
     */
    public function setAdapter($resource = '', $method = Zend_Http_Client::POST, $authorization = true)
    {
        $this->adapter = new Zend_Http_Client();

        try {
            /** @var string|null $url */
            $url = $this->configApi['url'];

            if (!$this->isEnabled()) {
                Mage::throwException($this->__('Loyalty point is currently disabled.'));
            }

            if (!$resource) {
                Mage::throwException($this->__('No resource given.'));
            }
            
            if (!$url) {
                Mage::throwException($this->__('No API URL set.'));
            }

            $url = sprintf(
                "%s/%s",
                rtrim($url, '/'),
                $resource
            );

            $this->adapter
                ->resetParameters(true)
                ->setUri($url)
                ->setMethod($method);

            if ($authorization) {
                $this->setAdapterCredentials($this->adapter);
            }
        } catch (Exception $e) {
            throw Mage::exception(
                self::EXCEPTION_CLASS,
                $e->getMessage()
            );
        }

        return $this;
    }

    /**
     * Send request and return parsed response from API call as array
     *
     * @param bool $jsonDecode if set tu true - return json decode response
     *
     * @return array|null
     *
     * @throws Divante_OpenLoyalty_Exception_Exception
     * @throws Mage_Core_Exception
     * @throws Zend_Json_Exception
     */
    public function sendRequest($jsonDecode = true)
    {
        $response = null;

        try {
            if (!isset($this->adapter)) {
                Mage::throwException($this->__('Please set adapter first'));
            }

            /** @var Zend_Http_Response $response */
            $response = $this
                ->adapter
                ->request();

            if($this->isDebugLogEnabled()) {
                Mage::log("REQUEST: " . $this->adapter->getLastRequest(), Zend_Log::DEBUG, self::DEBUG_LOG_FILE);
                Mage::log("REQUEST PARAMS: " . json_encode($this->params), Zend_Log::DEBUG, self::DEBUG_LOG_FILE);
                Mage::log("RESPONSE: " . $this->adapter->getLastResponse(), Zend_Log::DEBUG, self::DEBUG_LOG_FILE);
            }

            if (!$this->validateResponse($response)) {
                /** @var Zend_Http_Client $adapter */
                $adapter = clone $this->adapter;

                $this->setAdapterToken();
                $this->setAdapterCredentials($adapter);

                $response = $adapter->request();

                if (!$this->validateResponse($response)) {
                    Mage::throwException($this->__('Code 401 - bad credentials'));
                }
            }
        } catch (Exception $e) {
            if ($response != null) {
                throw Mage::exception(
                    self::EXCEPTION_CLASS,
                    sprintf("%s:\n %s", $e->getMessage(), trim(preg_replace('/\s+/', '', $response->getBody())))
                );
            } else {
                throw Mage::exception(
                    self::EXCEPTION_CLASS,
                    sprintf('Service temporarily unavailable. Reason: %s', $e->getMessage())
                );
            }
        }

        /** @var string|array $body */
        $body = $response->getBody();

        $result = ($jsonDecode)
            ? Zend_Json::decode($body)
            : $body;

        return $result;
    }

    /**
     * Set given parameters to Http Client adapter
     *
     * @param array $params
     * @param bool  $getParams If set true - GET parameters set instead POST
     * @param bool  $notNull If set true
     * - sending parameters cannot be null
     *
     * @return Divante_OpenLoyalty_Helper_Connection
     */
    public function setParameters($params = [], $getParams = false, $notNull = true)
    {
        if (empty($params) && $notNull) {
            Mage::throwException($this->__('Error occured - wrong parameters'));
        }

        if (!isset($this->adapter)) {
            return $this;
        }

        if ($getParams) {
            $this
                ->adapter
                ->setParameterGet($params);
        } else {
            $this
                ->adapter
                ->setParameterPost($params);
        }

        $this->params = $params;

        return $this;
    }

    /**
     * Set request adapter credentials (JWT token) via HEADER
     *
     * @param Zend_Http_Client $adapter
     *
     * @return Zend_Http_Client
     */
    protected function setAdapterCredentials(Zend_Http_Client $adapter)
    {
        /** @var string $token */
        $token = $this->getToken();

        if (!$token) {
            return $adapter;
        }

        $adapter->setHeaders('Authorization', 'Bearer '. $token);
        
        return $adapter;
    }

    /**
     * Validate requested response
     *
     * @param Zend_Http_Response $response
     *
     * @return bool
     */
    protected function validateResponse(Zend_Http_Response $response)
    {
        /** @var int $code */
        $code = $response->getStatus();

        if ($code !== 200
            && $code !== 401
        ) {
            $error = $response->getStatus() . ' - ' . $this->__('Bad request');
            Mage::throwException($error);
        }

        /** @var array $body */
        $body = Zend_Json::decode($response->getBody());

        if (empty($body)) {
            return false;
        }

        if (isset($body['code'])
            && $body['code'] == 401) {
            return false;
        }

        return true;
    }

    /**
     * Save to db Renew received token
     */
    protected function setAdapterToken()
    {
        $this->setAdapter(self::GET_TOKEN_RESOURCE, Zend_Http_Client::POST, false);
        $this->adapter->setParameterPost('_username', $this->configApi['username']);
        $this->adapter->setParameterPost('_password', $this->configApi['password']);

        /** @var Zend_Http_Response $response */
        $response = $this
            ->adapter
            ->request();

        if (!$this->validateResponse($response)) {
            Mage::throwException($this->__('Code 401 - bad credentials'));
        } else {
            /** @var array $body */
            $body = Zend_Json::decode($response->getBody());
            $token = (isset($body['token']))
                ? $body['token']
                : '';

            $this->saveToken($token);
        }
    }
}
