<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Helper_Config
 */
class Divante_OpenLoyalty_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH_API        = 'divante_openloyalty/api';
    const XML_CONFIG_PATH_TOKEN      = 'divante_openloyalty/api/token';
    const XML_CONFIG_PATH_FRONTEND   = 'divante_openloyalty/frontend';
    const XML_CONFIG_PATH_DEFAULT_LEVEL_NAME = 'divante_openloyalty/program_settings/default_level_name';
    const XML_CONFIG_PATH_DEFAULT_LEVEL_DISCOUNT = 'divante_openloyalty/program_settings/default_level_discount';
    const XML_CONFIG_PATH_TIER_TYPE = 'divante_openloyalty/program_settings/tier_assign_type';
    const XML_CONFIG_PATH_CURRENCY_SINGULAR = 'divante_openloyalty/program_settings/currency_singular';
    const XML_CONFIG_PATH_CURRENCY_PLURAL = 'divante_openloyalty/program_settings/currency_plural';
    const XML_CONFIG_PATH_POS_ID = 'divante_openloyalty/program_settings/pos_id';

    /** Form input name for register customer to Open Loyalty */
    const REGISTER_IN_OL_PARAM = 'register_in_open_loyalty';

    const LOYALTY_CARD_INPUT_NAME = 'loyalty_card_number';
    const HAVE_LOYALTY_CARD_INPUT_NAME = 'have_loyalty_card_number';
    const CONFIRM_CARD_OWNER_INPUT_NAME = 'card_owner';
    const REFERRAL_CUSTOMER_EMAIL_INPUT = 'referral_customer_email';

    /** Form input name for unregister customer from Open Loyalty */
    const UNREGISTER_IN_OL_PARAM = 'unregister_from_loyalty';

    /** Name of discount display in totals */
    const DISCOUNT_NAME = 'Loyalty program discount';

    /** Name of currently customer's loyalty card number offline input */
    const LOYALTY_ID_KEY = 'loyaltyCardNumber';

    const EXCEPTION_CLASS = 'Divante_OpenLoyalty_Exception';

    /** REST resource for getting token for Open Loyalty Point */
    const GET_TOKEN_RESOURCE = 'api/admin/login_check';

    const REGISTERED_USER_BANNER_STATIC_BLOCK = 'openloyalty_registered_banner';
    const UNREGISTERED_USER_STATIC_BLOCK = 'openloyalty_unregistered_info';
    const FAQ_STATIC_BLOCK = 'openloyalty_faq';
    const LOYALTY_IN_USE = 'loyalty_in_use';

    const DEBUG_LOG_FILE = 'openloyalty_debug.log';

    /** @var array */
    protected $configApi;

    /** @var array */
    protected $configFrontend;

    /** @var Mage_Core_Model_Store  */
    protected $store;

    /** @var string JWT token for API call  */
    protected $token;

    /**
     * Divante_OpenLoyalty_Helper_Connection constructor.
     */
    public function __construct()
    {
        $this->store          = Mage::app()->getStore();
        $this->configApi      = Mage::getStoreConfig(self::XML_CONFIG_PATH_API, $this->store);
        $this->configFrontend = Mage::getStoreConfig(self::XML_CONFIG_PATH_FRONTEND, $this->store);
    }

    /**
     * Return array of Open Loyalty frontend config
     *
     * @return array
     */
    public function getFrontendConfig()
    {
        return $this->configFrontend;
    }

    /**
     * Return name of register in loyalty point checkbox input
     *
     * @return string
     */
    public function getRegisterInLoyaltyInputName()
    {
        return self::REGISTER_IN_OL_PARAM;
    }

    /**
     * Return name of unregister from loyalty point checkbox input
     *
     * @return string
     */
    public function getUnregisterFromLoyaltyInputName()
    {
        return self::UNREGISTER_IN_OL_PARAM;
    }

    /**
     * Return name of registered loyalty card number input
     *
     * @return string
     */
    public function getLoyaltyCardNumberInputName()
    {
        return self::LOYALTY_ID_KEY;
    }

    /**
     * @param bool $fullPath
     *
     * @return string
     */
    public function getLoyaltyProgramTermsUrl($fullPath = true)
    {
        if ($fullPath) {
            return Mage::getUrl(ltrim($this->getFrontendConfig()['terms_url'], '/')) ?: '';
        }

        return $this->getFrontendConfig()['terms_url'];
    }

    /**
     * @return string
     */
    public function getLoyaltyProgramInfoUrl()
    {
        return Mage::getUrl(ltrim($this->getFrontendConfig()['program_info_url'], '/')) ?: '';
    }

    /**
     * @return string
     */
    public function getLoyaltyProgramLabel()
    {
        return $this->getFrontendConfig()['label'] ?: '';
    }

    /**
     * Return bool if register in Open Loyalty is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        /** @var bool $enabled */
        $enabled = (bool) $this->configFrontend['enabled'];
        
        /** @var string $url */
        $url = $this->configApi['url'];
        
        return ($enabled && $url != '');
    }

    /**
     * Convert given value to float
     *
     * @param string $value
     *
     * @return float
     */
    public function convertStringToFloat($value = '')
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace("/[^0-9\.]/", "", $value);

        return (float) $value;
    }

    /**
     * Return Open Loyalty discount name display in totals
     *
     * @return string
     */
    public function getDiscountName()
    {
        return self::DISCOUNT_NAME;
    }

    /**
     * Save to db currently and to model API token
     *
     * @param string $token
     */
    public function saveToken($token = '')
    {
        Mage::getConfig()
            ->saveConfig(self::XML_CONFIG_PATH_TOKEN, $token);
        $this->store->resetConfig();
        $this->token = $token;
    }

    /**
     * Get currently API token from db or from model
     *
     * @return string
     */
    public function getToken()
    {
        if (!isset($this->token)) {
            $this->token = Mage::getStoreConfig(self::XML_CONFIG_PATH_TOKEN, $this->store);
        }

        return $this->token;
    }

    /**
     * @return string
     */
    public function getLoyaltyCardInputName()
    {
        return self::LOYALTY_CARD_INPUT_NAME;
    }

    /**
     * @return string
     */
    public function getHaveLoyaltyCardInputName()
    {
        return self::HAVE_LOYALTY_CARD_INPUT_NAME;
    }

    /**
     * @return string
     */
    public function getConfirmCardOwnerInputName()
    {
        return self::CONFIRM_CARD_OWNER_INPUT_NAME;
    }

    /**
     * @return string
     */
    public function getProgramContactMail()
    {
        return $this->getFrontendConfig()['program_support_email'] ?: '';
    }

    /**
     * @return string
     */
    public function getUrlToCustomerPanel()
    {
        return $this->getFrontendConfig()['program_customer_panel'] ?: '';
    }

    /**
     * @return float
     */
    public function getInitialProgramDiscount()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_DEFAULT_LEVEL_DISCOUNT);
    }

    /**
     * @return bool
     */
    public function getLoyaltyInUse()
    {
        return boolval(Mage::getSingleton('checkout/session')->getData(self::LOYALTY_IN_USE));
    }

    /**
     * @return string
     */
    public function getTierType()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_TIER_TYPE);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public function getTranslatedCurrencyUnit($value = 0)
    {
        if($value == 1) {
            return $this->__(Mage::getStoreConfig(self::XML_CONFIG_PATH_CURRENCY_SINGULAR));
        }

        return $this->__(Mage::getStoreConfig(self::XML_CONFIG_PATH_CURRENCY_PLURAL));
    }

    /**
     * @return string
     */
    public function getPosIdentifier()
    {
        return $this->configApi['pos_identifier'] ?: '';
    }

    /**
     * @return string
     */
    public function getPosId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_POS_ID);
    }

    /**
     * @return bool
     */
    public function isDebugLogEnabled()
    {
        return boolval($this->configApi['debug_log']);
    }

    /**
     * @return string
     */
    public function getReferralCustomerEmailInputName()
    {
        return self::REFERRAL_CUSTOMER_EMAIL_INPUT;
    }
}
