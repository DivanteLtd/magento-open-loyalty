<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_PopupController
 */
class Divante_OpenLoyalty_PopupController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return void
     */
    public function showAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return \Zend_Controller_Response_Abstract
     */
    public function buyCampaignAction()
    {
        $campaignId = filter_var($this->getRequest()->getParam('campaignId'), FILTER_SANITIZE_SPECIAL_CHARS);
        $responseBody = 'error';

        if(!empty($campaignId)) {
            $campaignRequest = Mage::getModel('divante_openloyalty/request_campaign');
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getOpenLoyaltyId();
            $response = $campaignRequest->buyCustomerCampaign($customerId, $campaignId);

            if($response != null) {
                $responseBody = isset($response['coupon']['code']) ? $response['coupon']['code'] : '';

                $this->cleanCustomerStatusCache();
            }
        }

        return $this->getResponse()->setBody($responseBody);
    }

    /**
     * @return \Zend_Controller_Response_Abstract
     */
    public function redeemedRewardsAction()
    {
        $block = $this->getLayout()
            ->createBlock('divante_openloyalty/popup')
            ->setTemplate('divante/openloyalty/popup/boughtCodes.phtml');
        $responseBody = $block->toHtml();

        return $this->getResponse()->setBody($responseBody);
    }

    /**
     * @return void
     */
    private function cleanCustomerStatusCache()
    {
        Mage::getSingleton('customer/session')->getCustomer()->cleanCustomerStatusCache();
    }

    /**
     * @return void
     */
    public function referafriendAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
