<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Parser_Base
 */
class Divante_OpenLoyalty_Model_Parser_Base
{
    /**
     * Default string sending as API parameter
     *
     * @var string
     */
    const DEFAULT_NO_DATA = 'No data';

    /**
     * @param Varien_Object $address
     *
     * @return array
     */
    protected function getAddress(Varien_Object $address)
    {
        $nrHouse = $address->getStreet2() ?: $address->getNo1();
        $nrFlat = $address->getStreet3() ?: $address->getNo2();

        $parameters = [
            'postal'   => $address->getPostcode(),
            'city'     => ucfirst($address->getCity()),
            'country'  => strtoupper($address->getCountryId()) ?: 'PL',
            'street'   => ucfirst($address->getStreet1()),
            'address1' => $nrHouse,
            'address2' => $nrFlat,
            'province' => ucfirst($address->getRegion())
        ];

        return $parameters;
    }


    /**
     * @param string $data
     *
     * @return string
     */
    public function fillData($data = '')
    {
        return $data ?: self::DEFAULT_NO_DATA;
    }

    /**
     * @return Divante_OpenLoyalty_Helper_Config
     */
    public function getConfigHelper()
    {
        /** @var Divante_OpenLoyalty_Helper_Config $helper */
        $helper = $this->helper('divante_openloyalty/config');

        return $helper;
    }
}
