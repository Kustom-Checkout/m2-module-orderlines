<?php
/**
 * Copyright © Kustom AB (Originally developed by Klarna Bank AB)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Model\Items\Shipping;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Container\DataHolder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

/**
 * @internal
 */
class PostPurchaseCalculator extends CalculatorAbstract
{
    /**
     * @var OrderTaxManagementInterface
     */
    private OrderTaxManagementInterface $orderTaxManagement;

    /**
     * @param DataConverter $dataConverter
     * @param OrderTaxManagementInterface $orderTaxManagement
     * @codeCoverageIgnore
     */
    public function __construct(DataConverter $dataConverter, OrderTaxManagementInterface $orderTaxManagement)
    {
        parent::__construct($dataConverter);
        $this->orderTaxManagement = $orderTaxManagement;
    }

    /**
     * Calculating the values
     *
     * @param DataHolder $dataHolder
     * @param OrderInterface $order
     */
    public function calculate(DataHolder $dataHolder, OrderInterface $order): void
    {
        $this->reset();

        $unitPrice = $dataHolder->getBaseShippingInclTax();
        $reference = $order->getShippingMethod();
        if ($reference === null) {
            $reference = '';
        }

        $unitPriceConverted = $this->dataConverter->toApiFloat($unitPrice);
        $this->reset()
            ->setUnitPrice($unitPriceConverted)
            ->setTaxRate($this->dataConverter->toApiFloat($this->calculateTaxRate($unitPrice, $order)))
            ->setTotalAmount($unitPriceConverted)
            ->setTaxAmount($this->dataConverter->toApiFloat($this->calculateTaxAmount($unitPrice, $order)))
            ->setTitle(__('Shipping & Handling (' . $order->getShippingDescription() . ')')->getText())
            ->setReference($reference);

        $this->setDiscountAmount(0);
    }

    /**
     * Getting back the shipping tax rate Magento applied to the order
     *
     * The order totals only serve as a fallback for orders without stored shipping tax rates, because a rate
     * worked out from rounded amounts can be off (e.g. 25.06% instead of 25%).
     *
     * @param float $unitPrice
     * @param OrderInterface $order
     * @return float
     */
    private function calculateTaxRate(float $unitPrice, OrderInterface $order): float
    {
        $baseShippingAmount = (float) $order->getBaseShippingAmount();
        $baseShippingTaxAmount = (float) $order->getBaseShippingTaxAmount();
        if ($unitPrice <= 0 || $baseShippingAmount <= 0 || $baseShippingTaxAmount <= 0) {
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
            $taxDetails = $this->orderTaxManagement->getOrderTaxDetails((int) $order->getEntityId());
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
     * Getting back the base shipping tax of the part of the shipping billed by the current invoice or credit memo
     *
     * @param float $unitPrice
     * @param OrderInterface $order
     * @return float
     */
    private function calculateTaxAmount(float $unitPrice, OrderInterface $order): float
    {
        $baseShippingInclTax = (float) $order->getBaseShippingInclTax();
        if ($unitPrice <= 0 || $baseShippingInclTax <= 0) {
            return 0.0;
        }

        $baseShippingTaxAmount = (float) $order->getBaseShippingTaxAmount();
        if ($unitPrice >= $baseShippingInclTax) {
            return $baseShippingTaxAmount;
        }

        return round($baseShippingTaxAmount * $unitPrice / $baseShippingInclTax, 2);
    }
}
