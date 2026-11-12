<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model;

use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\ItemGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\ItemGenerator
 */
class ItemGeneratorTest extends TestCase
{
    /**
     * @var ItemGenerator
     */
    private ItemGenerator $model;
    /**
     * @var Parameter
     */
    private Parameter $parameter;

    public function testGetDiscountItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getDiscountReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getDiscountTitle')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getDiscountUnitPrice')
            ->willReturn($unitPrice);

        $taxRate = 12.34;
        $this->parameter->method('getDiscountTaxRate')
            ->willReturn($taxRate);

        $totalAmount = 110.0;
        $this->parameter->method('getDiscountTotalAmount')
            ->willReturn($totalAmount);

        $totalTaxAmount = 120.0;
        $this->parameter->method('getDiscountTaxAmount')
            ->willReturn($totalTaxAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_DISCOUNT,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'total_tax_amount' => $totalTaxAmount
        ];

        $result = $this->model->getDiscountItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetCustomerBalanceItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getCustomerbalanceReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getCustomerbalanceTitle')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getCustomerbalanceUnitPrice')
            ->willReturn($unitPrice);

        $taxRate = 12.34;
        $this->parameter->method('getCustomerbalanceTaxRate')
            ->willReturn($taxRate);

        $totalAmount = 110.0;
        $this->parameter->method('getCustomerbalanceTotalAmount')
            ->willReturn($totalAmount);

        $totalTaxAmount = 120.0;
        $this->parameter->method('getCustomerbalanceTaxAmount')
            ->willReturn($totalTaxAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_CUSTOMERBALANCE,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'total_tax_amount' => $totalTaxAmount
        ];

        $result = $this->model->getCustomerBalanceItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetGiftCardItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getGiftCardAccountReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getGiftCardAccountTitle')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getGiftCardAccountUnitPrice')
            ->willReturn($unitPrice);

        $taxRate = 12.34;
        $this->parameter->method('getGiftCardAccountTaxRate')
            ->willReturn($taxRate);

        $totalAmount = 110.0;
        $this->parameter->method('getGiftCardAccountTotalAmount')
            ->willReturn($totalAmount);

        $totalTaxAmount = 120.0;
        $this->parameter->method('getGiftCardAccountTaxAmount')
            ->willReturn($totalTaxAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_GIFTCARD,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'total_tax_amount' => $totalTaxAmount
        ];

        $result = $this->model->getGiftCartItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetRewardItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getRewardReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getRewardTitle')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getRewardUnitPrice')
            ->willReturn($unitPrice);

        $taxRate = 12.34;
        $this->parameter->method('getRewardTaxRate')
            ->willReturn($taxRate);

        $totalAmount = 110.0;
        $this->parameter->method('getRewardTotalAmount')
            ->willReturn($totalAmount);

        $totalTaxAmount = 120.0;
        $this->parameter->method('getRewardTaxAmount')
            ->willReturn($totalTaxAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_REWARD,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'total_tax_amount' => $totalTaxAmount
        ];

        $result = $this->model->getRewardItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetShippingItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getShippingReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getShippingTitle')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getShippingUnitPrice')
            ->willReturn($unitPrice);

        $taxRate = 12.34;
        $this->parameter->method('getShippingTaxRate')
            ->willReturn($taxRate);

        $totalAmount = 110.0;
        $this->parameter->method('getShippingTotalAmount')
            ->willReturn($totalAmount);

        $totalTaxAmount = 120.0;
        $this->parameter->method('getShippingTaxAmount')
            ->willReturn($totalTaxAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_SHIPPING,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total_amount' => $totalAmount,
            'total_tax_amount' => $totalTaxAmount,
            'total_discount_amount' => 0.0
        ];

        $result = $this->model->getShippingItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetSurchargeItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $reference = 'my_reference';
        $this->parameter->method('getSurchargeReference')
            ->willReturn($reference);

        $name = 'my_name';
        $this->parameter->method('getSurchargeName')
            ->willReturn($name);

        $unitPrice = 100.0;
        $this->parameter->method('getSurchargeUnitPrice')
            ->willReturn($unitPrice);

        $totalAmount = 110.0;
        $this->parameter->method('getSurchargeTotalAmount')
            ->willReturn($totalAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_SURCHARGE,
            'reference' => $reference,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => 0.0,
            'total_amount' => $totalAmount,
            'total_tax_amount' => 0.0
        ];

        $result = $this->model->getSurchargeItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    public function testGetTaxItemRowReturnsFilledArray(): void
    {
        $quantity = 1;

        $unitPrice = 100.0;
        $this->parameter->method('getTaxUnitPrice')
            ->willReturn($unitPrice);

        $totalAmount = 110.0;
        $this->parameter->method('getTaxTotalAmount')
            ->willReturn($totalAmount);

        $expected = [
            'type' => ItemGenerator::ITEM_TYPE_TAX,
            'reference' => __('Sales Tax')->getText(),
            'name' => __('Sales Tax')->getText(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => 0.0,
            'total_amount' => $totalAmount,
            'total_tax_amount' => 0.0
        ];

        $result = $this->model->getTaxItemRow($this->parameter, $quantity);
        static::assertSame($expected, $result);
    }

    protected function setUp(): void
    {
        $this->model = new ItemGenerator();
        $this->parameter = $this->createStub(Parameter::class);
    }
}
