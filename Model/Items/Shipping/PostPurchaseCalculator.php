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
use Klarna\Orderlines\Model\Calculator\Shipping as ShippingCalculator;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @internal
 */
class PostPurchaseCalculator extends CalculatorAbstract
{
    /**
     * @var ShippingCalculator
     */
    private ShippingCalculator $calculator;

    /**
     * @param DataConverter $dataConverter
     * @param ShippingCalculator $calculator
     * @codeCoverageIgnore
     */
    public function __construct(
        DataConverter $dataConverter,
        ShippingCalculator $calculator
    ) {
        parent::__construct($dataConverter);
        $this->calculator = $calculator;
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
     * Getting back the shipping tax rate Magento applied to the order, or 0 when no shipping is billed
     *
     * @param float $unitPrice
     * @param OrderInterface $order
     * @return float
     */
    private function calculateTaxRate(float $unitPrice, OrderInterface $order): float
    {
        if ($unitPrice <= 0) {
            return 0.0;
        }

        return $this->calculator->getTaxRateByOrder($order);
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
