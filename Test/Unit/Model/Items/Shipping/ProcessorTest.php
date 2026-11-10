<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Shipping;

use Klarna\Base\Model\Quote\Address\Country;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator;
use Klarna\Orderlines\Model\Items\Shipping\PrePurchaseCalculator;
use Klarna\Orderlines\Model\Items\Shipping\Processor;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Shipping\Processor
 */
#[AllowMockObjectsWithoutExpectations]
class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    private Processor $processor;
    /**
     * @var PrePurchaseCalculator
     */
    private PrePurchaseCalculator $prePurchaseCalculator;
    /**
     * @var PostPurchaseCalculator
     */
    private PostPurchaseCalculator $postPurchaseCalculator;
    /**
     * @var Country
     */
    private Country $country;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Order
     */
    private Order $order;
    /**
     * @var Parameter
     */
    private Parameter $parameter;
    /**
     * @var Quote
     */
    private Quote $magentoQuote;

    public function testProcessPrePurchaseSettingUnitPrice(): void
    {
        $expected = 7;
        $this->prePurchaseCalculator->method('getUnitPrice')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingUnitPrice')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingTaxRate(): void
    {
        $expected = 7;
        $this->prePurchaseCalculator->method('getTaxRate')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTaxRate')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingTotalAmount(): void
    {
        $expected = 7;
        $this->prePurchaseCalculator->method('getTotalAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTotalAmount')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingTaxAmount(): void
    {
        $expected = 7;
        $this->prePurchaseCalculator->method('getTaxAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTaxAmount')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingDiscountAmount(): void
    {
        $expected = 7;
        $this->prePurchaseCalculator->method('getDiscountAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingDiscountAmount')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingTitle(): void
    {
        $expected = 'test';
        $this->prePurchaseCalculator->method('getTitle')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTitle')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseSettingReference(): void
    {
        $expected = 'test';
        $this->prePurchaseCalculator->method('getReference')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingReference')
            ->with($expected);
        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseCalculatingIncludingTax(): void
    {
        $this->country->method('isUsCountry')
            ->willReturn(false);
        $this->prePurchaseCalculator->expects(static::once())
            ->method('calculateIncludedTaxData');

        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPrePurchaseCalculatingWithSeparatedTaxLine(): void
    {
        $this->country->method('isUsCountry')
            ->willReturn(true);
        $this->prePurchaseCalculator->expects(static::once())
            ->method('calculateSeparateTaxLineData');

        $this->processor->processPrePurchase($this->dataHolder, $this->parameter, $this->magentoQuote);
    }

    public function testProcessPostPurchaseSettingUnitPrice(): void
    {
        $expected = 7;
        $this->postPurchaseCalculator->method('getUnitPrice')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingUnitPrice')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingTaxRate(): void
    {
        $expected = 7;
        $this->postPurchaseCalculator->method('getTaxRate')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTaxRate')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingTotalAmount(): void
    {
        $expected = 7;
        $this->postPurchaseCalculator->method('getTotalAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTotalAmount')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingTaxAmount(): void
    {
        $expected = 7;
        $this->postPurchaseCalculator->method('getTaxAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTaxAmount')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingDiscountAmount(): void
    {
        $expected = 7;
        $this->postPurchaseCalculator->method('getDiscountAmount')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingDiscountAmount')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingTitle(): void
    {
        $expected = 'test';
        $this->postPurchaseCalculator->method('getTitle')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingTitle')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    public function testProcessPostPurchaseSettingReference(): void
    {
        $expected = 'test';
        $this->postPurchaseCalculator->method('getReference')
            ->willReturn($expected);
        $this->parameter->expects(static::once())
            ->method('setShippingReference')
            ->with($expected);
        $this->processor->processPostPurchase($this->dataHolder, $this->parameter, $this->order);
    }

    protected function setUp(): void
    {
        $this->prePurchaseCalculator = $this->createMock(PrePurchaseCalculator::class);
        $this->postPurchaseCalculator = $this->createMock(PostPurchaseCalculator::class);
        $this->country = $this->createStub(Country::class);
        $this->processor = new Processor(
            $this->prePurchaseCalculator,
            $this->postPurchaseCalculator,
            $this->country
        );

        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->order = $this->createStub(Order::class);
        $this->parameter = $this->createMock(Parameter::class);

        $this->magentoQuote = $this->createStub(Quote::class);
    }
}
