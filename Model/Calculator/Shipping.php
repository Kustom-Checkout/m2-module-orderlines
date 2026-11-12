<?php
/**
 * Copyright © Kustom AB (Originally developed by Klarna Bank AB)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Model\Calculator;

use Klarna\Orderlines\Model\Container\DataHolder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Tax\Model\Calculation;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

/**
 * @api
 */
class Shipping
{
    /**
     * @var OrderTaxManagementInterface
     */
    private ?OrderTaxManagementInterface $orderTaxManagement = null;

    /**
     * @param OrderTaxManagementInterface|null $orderTaxManagement
     * @codeCoverageIgnore
     */
    public function __construct(?OrderTaxManagementInterface $orderTaxManagement = null)
    {
        $this->orderTaxManagement = $orderTaxManagement;
    }

    /**
     * Getting back the tax rate
     *
     * @param DataHolder $dataHolder
     * @return float
     */
    public function getTaxRate(DataHolder $dataHolder): float
    {
        return $this->getTaxRateByAddress($dataHolder->getShippingAddress());
    }

    /**
     * Calculating and getting back the tax rate by shipping costs
     *
     * @param AddressModelInterface $address
     * @param float $shippingAmount
     * @return float
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function getTaxRateByShippingCosts(AddressModelInterface $address, float $shippingAmount): float
    {
        return $this->getTaxRateByAddress($address);
    }

    /**
     * Getting back the shipping tax rate Magento applied to the order
     *
     * Uses the shipping tax rates Magento stored when the order was placed (sales_order_tax_item). The order
     * totals only serve as a fallback for orders without stored shipping tax rates, because a rate worked out
     * from rounded amounts can be off (e.g. 25.06% instead of 25%).
     *
     * @param OrderInterface $order
     * @return float
     */
    public function getTaxRateByOrder(OrderInterface $order): float
    {
        $baseShippingAmount = (float) $order->getBaseShippingAmount();
        $baseShippingTaxAmount = (float) $order->getBaseShippingTaxAmount();
        if ($baseShippingAmount <= 0 || $baseShippingTaxAmount <= 0) {
            return 0.0;
        }

        $storedTaxRate = $this->getStoredShippingTaxRate($order);
        if ($storedTaxRate !== null) {
            return $storedTaxRate;
        }

        return round($baseShippingTaxAmount / $baseShippingAmount * 100, 2);
    }

    /**
     * Getting back the shipping tax rate Magento stored when the order was placed (sales_order_tax_item)
     *
     * Magento stores one row per applied tax rate, with rates of a later priority already compounded, so the sum
     * of the rows is the rate Magento applied to the shipping.
     *
     * @param OrderInterface $order
     * @return float|null null when Magento has no shipping tax rate stored for the order
     */
    private function getStoredShippingTaxRate(OrderInterface $order): ?float
    {
        try {
            $taxDetails = $this->getOrderTaxManagement()->getOrderTaxDetails((int) $order->getEntityId());
        } catch (\Exception $e) {
            return null;
        }
        if ($taxDetails === null) {
            return null;
        }

        $taxRate = null;
        foreach ($taxDetails->getItems() ?? [] as $taxItem) {
            if ($taxItem->getType() !== CommonTaxCollector::ITEM_TYPE_SHIPPING) {
                continue;
            }

            foreach ($taxItem->getAppliedTaxes() ?? [] as $appliedTax) {
                $taxRate = (float) $taxRate + (float) $appliedTax->getPercent();
            }
        }

        return $taxRate > 0 ? $taxRate : null;
    }

    /**
     * Getting back the order tax management, loading it on first use
     *
     * Covers subclasses with their own constructor that don't call the parent constructor.
     *
     * @return OrderTaxManagementInterface
     */
    private function getOrderTaxManagement(): OrderTaxManagementInterface
    {
        $this->orderTaxManagement ??= ObjectManager::getInstance()->get(OrderTaxManagementInterface::class);

        return $this->orderTaxManagement;
    }

    /**
     * Getting back the tax rate by address
     *
     * @param AddressModelInterface $address
     * @return float
     */
    private function getTaxRateByAddress(AddressModelInterface $address): float
    {
        $baseShippingAmount = (float) $address->getBaseShippingAmount();
        if (empty($baseShippingAmount)) {
            return (float) 0;
        }

        $singleTaxRate = $this->getUsedSingleTaxRate($address);
        if ($singleTaxRate > 0) {
            return $singleTaxRate;
        }

        return $address->getBaseShippingTaxAmount() /($baseShippingAmount) * 100;
    }

    /**
     * Getting back the tax rate if just one was applied else 0
     *
     * @param AddressModelInterface $address
     * @return float
     */
    private function getUsedSingleTaxRate(AddressModelInterface $address): float
    {
        $addressData = $address->getData();
        if (isset($addressData['items_applied_taxes']) && isset($addressData['items_applied_taxes']['shipping'])) {
            $shipping = $addressData['items_applied_taxes']['shipping'];

            if (count($shipping) === 1) {
                return (float) $shipping[0]['percent'];
            }
        }

        return (float) 0;
    }
}
