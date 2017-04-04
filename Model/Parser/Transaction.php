<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Class Divante_OpenLoyalty_Model_Parser_Transaction
 */
class Divante_OpenLoyalty_Model_Parser_Transaction extends Divante_OpenLoyalty_Model_Parser_Base
{
    /**
     * @var string
     */
    const MANUFACTURER_ATTRIBUTE_CODE = 'manufacturer';

    /**
     * @var string
     */
    const TRANSACTION_MAIN_KEY = 'transaction';

    /**
     * @param Divante_OpenLoyalty_Model_Order $order
     *
     * @return array|false
     */
    public function convertOrderToRequestParameters(Divante_OpenLoyalty_Model_Order $order)
    {
        if (!$this->validateOrder($order)) {
            return null;
        }

        /** @var Divante_OpenLoyalty_Model_Customer $customer Customer model loaded from db */
        $customer = $order->getPurchaser();

        /** @var Mage_Sales_Model_Order_Address|false $billing */
        $billing = $order->getBillingAddress();

        if (!$billing instanceof Mage_Sales_Model_Order_Address) {
            $billing = Mage::getModel('sales/order_address');
        } else {
            $customer = clone $customer;
            $customer->addData($billing->getData());
        }

        /** @var array $parameters */
        $parameters = [
            self::TRANSACTION_MAIN_KEY => [
                'transactionData' => [
                    'documentType'   => 'sell',
                    'documentNumber' => $order->getIncrementId(),
                    'purchasePlace'  => $this->getConfigHelper()->getPosIdentifier(),
                    'purchaseDate'   => $order->getCreatedAt(),
                ],
                'customerData'    => [
                    'name'              => sprintf(
                        "%s %s",
                        $customer->getFirstname(),
                        $customer->getLastname()
                    ),
                    'email'             => $order->getCustomerEmail(),
                    'phone'             => $billing->getTelephone(),
                    'loyaltyCardNumber' => $customer->getLoyaltyCardNumber(),
                    'nip'               => $this->parseOrderNip($order, $billing),
                    'address'           => $this->getAddress($billing)
                ],
                'pos'             => $this->getConfigHelper()->getPosId()
            ]
        ];

        $this->parseOrderItems($parameters[self::TRANSACTION_MAIN_KEY], $order);
        $this->parseShipping($parameters[self::TRANSACTION_MAIN_KEY], $order);

        return $parameters;
    }

    /**
     * @param Divante_OpenLoyalty_Model_Order $order
     *
     * @return bool
     */
    public function validateOrder(Divante_OpenLoyalty_Model_Order $order)
    {
        if (!Zend_Validate::is($order->getCustomerEmail(), 'EmailAddress')) {
            return false;
        }

        return true;
    }

    /**
     * @param Divante_OpenLoyalty_Model_Order $order
     * @param Mage_Sales_Model_Order_Address  $address
     *
     * @return string
     */
    public function parseOrderNip(
        Divante_OpenLoyalty_Model_Order $order,
        Mage_Sales_Model_Order_Address $address
    ) {
        /** @var string $nip */
        $nip = ($order->getCustomerTaxvat() != '')
            ? $order->getCustomerTaxvat()
            : $address->getVatId();

        return $nip;
    }

    /**
     * @param array                           $parameters
     * @param Divante_OpenLoyalty_Model_Order $order
     */
    public function parseOrderItems(&$parameters, Divante_OpenLoyalty_Model_Order $order)
    {
        $items = [];
        $productLabelsParser = Mage::getModel('divante_openloyalty/parser_productLabels');
        $priceHelper = $this->getPriceHelper();

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getItemsCollection() as $item) {
            if (!$this->parentItemIsBundle($item) && ($item->getProductType() === 'bundle' || !empty($item->getParentItem()))) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = $item->getProduct();

            /** @var array $catIds */
            $catIds = $product->getCategoryIds();

            /** @var string $categoryName First category name */
            $categoryName = '';

            if (!empty($catIds)
                && isset($catIds[0])
            ) {
                /** @var Mage_Catalog_Model_Category $category */
                $category = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($catIds[0]);
                $categoryName = $category->getName();
            }

            /** @var string $manufacturer */
            $manufacturer = $product->getAttributeText(self::MANUFACTURER_ATTRIBUTE_CODE);

            $items[] = [
                'sku'        => [
                    'code' => $item->getSku()
                ],
                'name'       => $item->getName(),
                'quantity'   => $item->getQtyOrdered(),
                'grossValue' => $priceHelper->subtractFloat($item->getRowTotalInclTax(), $item->getDiscountAmount()),
                'category'   => $this->fillData(ucfirst($categoryName)),
                'maker'      => $this->fillData(ucfirst($manufacturer)),
                'labels'     => $productLabelsParser->getProductLabels($product)
            ];
        }

        $parameters['items'] = $items;
    }

    /**
     * @param array $parameters
     * @param Divante_OpenLoyalty_Model_Order $order
     */
    private function parseShipping(&$parameters, Divante_OpenLoyalty_Model_Order $order)
    {
        if($order->getIsNotVirtual()) {
            $parameters['items'][] = [
                'sku' => [
                    'code' => $order->getShippingMethod(false)
                ],
                'name' => $order->getShippingDescription(),
                'grossValue' => $order->getShippingAmount(),
                'category' => 'shipping',
                'quantity' => 1
            ];
        }
    }

    /**
     * @param Divante_OpenLoyalty_Model_Quote $quote
     *
     * @return array
     */
    public function convertQuoteToRequestParameters(Divante_OpenLoyalty_Model_Quote $quote)
    {
        $quoteItems = $quote->getItemsCollection();
        $items = $parameters = [];
        $productLabelsParser = Mage::getModel('divante_openloyalty/parser_productLabels');
        $priceHelper = $this->getPriceHelper();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quoteItems as $item) {
            if (!$this->parentItemIsBundle($item) && ($item->getProductType() === 'bundle' || !empty($item->getParentItem()))) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = $item->getProduct();

            /** @var array $catIds */
            $catIds = $product->getCategoryIds();

            /** @var string $categoryName First category name */
            $categoryName = '';

            if (!empty($catIds)
                && isset($catIds[0])
            ) {
                /** @var Mage_Catalog_Model_Category $category */
                $category = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($catIds[0]);
                $categoryName = $category->getName();
            }

            /** @var string $manufacturer */
            $manufacturer = $product->getAttributeText(self::MANUFACTURER_ATTRIBUTE_CODE);

            $items[] = [
                'sku'        => [
                    'code' => $item->getSku()
                ],
                'name'       => $item->getName(),
                'quantity'   => $item->getQty(),
                'grossValue' => $priceHelper->subtractFloat(floatval($item->getRowTotalInclTax()),floatval($item->getDiscountAmount())),
                'category'   => $this->fillData(ucfirst($categoryName)),
                'maker'      => $this->fillData(ucfirst($manufacturer)),
                'labels'     => $productLabelsParser->getProductLabels($product)
            ];
        }

        $parameters[self::TRANSACTION_MAIN_KEY]['items'] = $items;
        $parameters[self::TRANSACTION_MAIN_KEY]['purchaseDate'] = $quote->getUpdatedAt();
        $parameters[self::TRANSACTION_MAIN_KEY]['items'][] = $this->getQuoteShipping($quote);

        return $parameters;
    }

    /**
     * @return Divante_OpenLoyalty_Helper_Price
     */
    private function getPriceHelper()
    {
        /** @var Divante_OpenLoyalty_Helper_Price $helper */
        $helper = Mage::helper('divante_openloyalty/price');

        return $helper;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item
     *
     * @return bool
     */
    private function parentItemIsBundle($item)
    {
        if ($item->getProductType() === 'simple' &&
            $item->getParentItem() &&
            $item->getParentItem()->getProductType() === 'bundle'
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param Divante_OpenLoyalty_Model_Quote $quote
     *
     * @return array
     */
    private function getQuoteShipping($quote)
    {
        if($quote->getShippingAddress()->getShippingMethod()) {
            return [
                'sku' => [
                    'code' => $quote->getShippingAddress()->getShippingMethod()
                ],
                'name' => $quote->getShippingAddress()->getShippingDescription(),
                'grossValue' => $quote->getShippingAddress()->getShippingAmount(),
                'category' => 'shipping',
                'quantity' => 1
            ];
        }

        return [];
    }
}
