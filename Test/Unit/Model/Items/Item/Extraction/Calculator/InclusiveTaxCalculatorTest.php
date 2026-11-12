<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\BaseResult;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\InclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\InclusiveTaxCalculator
 */
class InclusiveTaxCalculatorTest extends TestCase
{
    /**
     * @var InclusiveTaxCalculator
     */
    private InclusiveTaxCalculator $model;
    /**
     * @var DataConverter|MockObject
     */
    private DataConverter $dataConverter;
    /**
     * @var Config|MockObject
     */
    private Config $taxConfig;
    /**
     * @var Container
     */
    private Container $container;

    public function testGetOrderLineItemReturnsCorrectTaxRate(): void
    {
        $expected = (float) 30;
        $this->container->method('getTaxPercent')
            ->willReturn($expected);
        $this->dataConverter
            ->method('toApiFloat')
            ->willReturn($expected);

        $result = $this->model->getOrderLineItem($this->container);
        static::assertEquals($expected, $result['tax_rate']);
    }

    public function testGetOrderLineItemTaxUsedAfterDiscountReturnsCorrectTotalTaxAmount(): void
    {
        $this->taxConfig->method('applyTaxAfterDiscount')
            ->willReturn(true);

        $this->container->method('getTaxPercent')
            ->willReturn((float) 30);
        $this->container->method('getRowTotalIncludedTax')
            ->willReturn((float) 20);
        $this->container->method('getDiscountAmount')
            ->willReturn((float) 15);
        $this->container->method('getTaxAmount')
            ->willReturn((float) 7);
        $this->dataConverter->method('toApiFloat')
            ->willReturnCallback(fn($float) =>
                match ($float) {
                    (float) 30 => (float) 3000,
                    (float) 5 => (float) 500,
                    (float) 20 => (float) 200,
                    (float) 7 => (float) 700,
                });

        $result = $this->model->getOrderLineItem($this->container);
        static::assertEquals((float) 700, $result['total_tax_amount']);
    }

    public function testGetOrderLineItemTaxUsedBeforeDiscountReturnsCorrectTotalTaxAmount(): void
    {
        $this->taxConfig->method('applyTaxAfterDiscount')
            ->willReturn(false);

        $this->container->method('getTaxPercent')
            ->willReturn((float) 30);
        $this->container->method('getRowTotalIncludedTax')
            ->willReturn((float) 20);
        $this->container->method('getDiscountAmount')
            ->willReturn((float) 15);
        $this->container->method('getTaxAmount')
            ->willReturn((float) 7);
        $this->dataConverter->method('toApiFloat')
            ->willReturnCallback(fn($float) =>
                match ($float) {
                    (float) 30 => (float) 3000,
                    (float) 5 => (float) 500,
                    (float) 20 => (float) 200,
                    (float) 7 => (float) 700,
                });

        $result = $this->model->getOrderLineItem($this->container);
        static::assertEquals((float) 115, $result['total_tax_amount']);
    }

    protected function setUp(): void
    {
        $baseResult = $this->createStub(BaseResult::class);
        $baseResult->method('getFromContainer')
            ->willReturn(['quantity' => 1]);
        $this->dataConverter = $this->createStub(DataConverter::class);
        $this->taxConfig = $this->createStub(Config::class);
        $this->model = new InclusiveTaxCalculator($baseResult, $this->dataConverter, $this->taxConfig);
        $this->container = $this->createStub(Container::class);
    }
}
