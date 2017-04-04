<?php
/**
 * @package   Divante\OpenLoyalty
 * @author    Oleksandr Yeremenko <oyeremenko@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

/**
 * Rewrite model quote
 * Class Divante_OpenLoyalty_Model_Quote
 *
 * @method Divante_OpenLoyalty_Model_Quote setCouponCode(string $value)
 */
class Divante_OpenLoyalty_Model_Quote extends Mage_Sales_Model_Quote
{
    /**
     * @var Divante_OpenLoyalty_Helper_Connection
     */
    protected $loyaltyHelper;

    /**
     * @var string Open Loyalty discount name display in totals
     */
    protected $discountName;

    /**
     * @var float Loyalty discount amount in percentage
     */
    protected $loyaltyDiscount;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->loyaltyHelper   = Mage::helper('divante_openloyalty/connection');
        $this->discountName    = $this->loyaltyHelper->getDiscountName();
        $this->loyaltyDiscount = $this->getLoyaltyDiscount();
    }

    /**
     * Return register in Open Loyalty flag
     *
     * @return int
     */
    public function getRegisterInLoyalty()
    {
        return (int) $this->getData('register_in_loyalty');
    }

    /**
     * Return true if register customer in loyalty must be done on the checkout
     *
     * @return bool
     */
    public function doCustomerRegisterInLoyalty()
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();

        /** @var string $module */
        $module = $request->getModuleName();

        return ($module == 'checkout' && $this->getRegisterInLoyalty());
    }

    /**
     * Set register in open loyalty flag depends on request param sends
     *
     * @return Divante_OpenLoyalty_Model_Quote
     */
    public function setRegisterInLoyalty()
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();

        /** @var int $setFlag */
        $setFlag = ($request->has($this->loyaltyHelper->getRegisterInLoyaltyInputName()))
            ? 1
            : 0;

        if (!$this->loyaltyHelper->isEnabled()) {
            $setFlag = 0;
        }

        $this->setData('register_in_loyalty', $setFlag);
     
        return $this;
    }

    /**
     * @return void
     */
    public function unsetRegisterInLoyalty()
    {
        $this->unsetData('register_in_loyalty');
    }

    /**
     * Return loyalty discount in percentage as float
     *
     * @return float
     */
    public function getLoyaltyDiscount()
    {
        /** @var float $discount */
        $discount = (float) $this->getData('loyalty_discount');

        return $discount;
    }

    /**
     * Set Open Loyalty discount as percentage and delete coupon code
     *
     * @param string|float $discount
     *
     * @return Divante_OpenLoyalty_Model_Quote
     */
    public function setLoyaltyDiscount($discount = null)
    {
        $discount = $this
            ->loyaltyHelper
            ->convertStringToFloat($discount);

        if (!$this->loyaltyHelper->isEnabled()) {
            $discount = null;
        }

        $this->setData('loyalty_discount', $discount);

        if ($discount > 0) {
            $this->unsetCouponCode();
        }

        return $this;
    }

    /**
     * Delete Open Loyalty Discount
     *
     * @return $this
     */
    public function unsetLoyaltyDiscount()
    {
        $this->setData('loyalty_discount', null);

        return $this;
    }

    /**
     * Clear coupon code
     *
     * @return $this
     */
    public function unsetCouponCode()
    {
        $this->setCouponCode('');

        return $this;
    }

    /**
     * Rewrite Collect totals including loyalty discount
     *
     * @return Divante_OpenLoyalty_Model_Quote
     */
    public function collectLoyaltyDiscount()
    {
        if (!$this->loyaltyHelper->isEnabled()) {
            $this->unsetLoyaltyDiscount();

            return $this;
        }

        $this->loyaltyDiscount = $this->getLoyaltyDiscount();

        if (!$this->loyaltyDiscount
            || !$this->loyaltyHelper->isEnabled()) {
            return $this;
        } else {
            $this->unsetCouponCode();
        }

        $this->setSubtotal(0);
        $this->setBaseSubtotal(0);
        $this->setSubtotalWithDiscount(0);
        $this->setBaseSubtotalWithDiscount(0);
        $this->setGrandTotal(0);
        $this->setBaseGrandTotal(0);

        /** @var string $canAddItems */
        $canAddItems = $this->isVirtual() ? ('billing') : ('shipping');

        /** @var Mage_Sales_Model_Quote_Address $address */
        foreach ($this->getAllAddresses() as $address) {
            $this->collectAddress($address, $canAddItems);
        }

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($this->getAllItems() as $item) {
            $this->collectItem($item);
        }

        return $this;
    }

    /**
     * Collect totals in quote address
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string                         $canAddItems
     *
     * @throws Exception
     */
    protected function collectAddress(
        Mage_Sales_Model_Quote_Address $address,
        $canAddItems
    ) {
        $address->setSubtotal(0);
        $address->setBaseSubtotal(0);
        $address->setGrandTotal(0);
        $address->setBaseGrandTotal(0);

        $address->collectTotals();

        $this->setSubtotal((float) $this->getSubtotal() + $address->getSubtotal());
        $this->setBaseSubtotal((float) $this->getBaseSubtotal() + $address->getBaseSubtotal());
        $this->setSubtotalWithDiscount((float) $this->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount());
        $this->setBaseSubtotalWithDiscount(
            (float) $this->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
        );
        $this->setGrandTotal((float) $this->getGrandTotal() + $address->getGrandTotal());
        $this->setBaseGrandTotal((float) $this->getBaseGrandTotal() + $address->getBaseGrandTotal());
        $this->save();

        $this
            ->setGrandTotal($this->calculateDiscountAmount($this->getBaseSubtotal()))
            ->setBaseGrandTotal($this->calculateDiscountAmount($this->getBaseSubtotal()))
            ->setSubtotalWithDiscount($this->calculateDiscountAmount($this->getBaseSubtotal()))
            ->setBaseSubtotalWithDiscount($this->calculateDiscountAmount($this->getBaseSubtotal()))
            ->save();

        /** Please notice discount is calculating from subtotal with tax without shipping amount: */
        if ($address->getAddressType() == $canAddItems) {
            $address->setSubtotalWithDiscount($this->calculateDiscountAmount($address->getSubtotalWithDiscount()));
            $address->setGrandTotal(
                $this->calculateDiscountAmount($address->getSubtotalInclTax())
                + $address->getShippingInclTax()
            );
            $address->setBaseSubtotalWithDiscount(
                $this->calculateDiscountAmount($address->getBaseSubtotalWithDiscount())
            );
            $address->setBaseGrandTotal(
                $this->calculateDiscountAmount($address->getBaseSubtotalTotalInclTax())
                + $address->getBaseShippingInclTax()
            );

            if ($address->getDiscountDescription()) {
                $address->setDiscountAmount(-($this->getPriceDiscount($address->getSubtotalInclTax())));
                $address->setDiscountDescription(
                    $address->getDiscountDescription()
                    . ', '
                    . $this->loyaltyHelper->__($this->discountName)
                );
                $address->setBaseDiscountAmount(-($this->getPriceDiscount($address->getBaseSubtotalTotalInclTax())));
            } else {
                $address->setDiscountAmount(-($this->getPriceDiscount($address->getSubtotalInclTax())));
                $address->setDiscountDescription($this->loyaltyHelper->__($this->discountName));
                $address->setBaseDiscountAmount(-($this->getPriceDiscount($address->getBaseSubtotalTotalInclTax())));
            }

            $address->save();
        }
    }

    /**
     * Set additional data in quote item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     */
    protected function collectItem(Mage_Sales_Model_Quote_Item $item)
    {
        $item->setDiscountAmount(
            $item->getDiscountAmount() + $this->getPriceDiscount($item->getRowTotalInclTax())
        );
        $item
            ->setBaseDiscountAmount(
                $item->getBaseDiscountAmount() + $this->getPriceDiscount($item->getBaseRowTotalInclTax())
            )
            ->save();
    }

    /**
     * Return given price reduced by percentage loyalty discount
     *
     * @param float $price
     *
     * @return float
     */
    protected function calculateDiscountAmount($price = 0.0)
    {
        /** @var float $discount */
        $discount = $this->getPriceDiscount($price);

        return (($price - $discount) < 0)
            ? 0
            : $price - $discount;
    }

    /**
     * Return price discount amount from percentage loyalty discount
     *
     * @param float $price
     *
     * @return float
     */
    protected function getPriceDiscount($price = 0.0)
    {
        /** @var float $price */
        $price = $this
            ->loyaltyHelper
            ->convertStringToFloat($price);

        /** @var float $discount */
        $discount = $price * ($this->loyaltyDiscount * 0.01);

        return $discount;
    }

    /**
     * @return bool
     */
    public function isAllowedGuestCheckout()
    {
        return parent::isAllowedGuestCheckout() && !$this->loyaltyHelper->getLoyaltyInUse();
    }
}
