<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Model\Items\Shipping;

use Klarna\Orderlines\Model\Container\DataHolder;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @internal
 */
class PostPurchaseCalculator extends CalculatorAbstract
{
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
     * Getting back the shipping tax rate from the order totals (the order address does not hold shipping amounts)
     *
     * @param float $unitPrice
     * @param OrderInterface $order
     * @return float
     */
    private function calculateTaxRate(float $unitPrice, OrderInterface $order): float
    {
        $baseShippingAmount = (float) $order->getBaseShippingAmount();
        if ($unitPrice <= 0 || $baseShippingAmount <= 0) {
            return 0.0;
        }

        return round((float) $order->getBaseShippingTaxAmount() / $baseShippingAmount * 100, 2);
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
