<?php
/**
 * Copyright © Kustom AB (Originally developed by Klarna Bank AB)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Shipping;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Calculator\Shipping as ShippingCalculator;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator
 */
#[AllowMockObjectsWithoutExpectations]
class PostPurchaseCalculatorTest extends TestCase
{
    /**
     * @var PostPurchaseCalculator
     */
    private PostPurchaseCalculator $calculator;
    /**
     * @var ShippingCalculator
     */
    private ShippingCalculator $shippingCalculator;
    /**
     * @var DataConverter
     */
    private DataConverter $dataConverter;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Order
     */
    private Order $order;

    public function testCalculateSettingUnitPrice(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(1250, $this->calculator->getUnitPrice());
    }

    public function testCalculateSettingTaxRate(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(2500, $this->calculator->getTaxRate());
    }

    public function testCalculateSettingTotalAmount(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(1250, $this->calculator->getTotalAmount());
    }

    public function testCalculateSettingTaxAmountFromBaseCurrency(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(250, $this->calculator->getTaxAmount());
    }

    public function testCalculateShippingAlreadyInvoicedHasNoTaxAndNoRate(): void
    {
        $dataHolder = $this->createDataHolder(0.0);
        $this->shippingCalculator->expects(static::never())
            ->method('getTaxRateByOrder');

        $this->calculator->calculate($dataHolder, $this->order);
        static::assertEquals(0, $this->calculator->getTotalAmount());
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
    }

    public function testCalculatePartialShippingRefundUsesProportionalTax(): void
    {
        $this->calculator->calculate($this->createDataHolder(5.0), $this->order);
        static::assertEquals(500, $this->calculator->getTotalAmount());
        static::assertEquals(100, $this->calculator->getTaxAmount());
        static::assertEquals(2500, $this->calculator->getTaxRate());
    }

    public function testCalculateOrderWithoutShippingTaxHasNoTax(): void
    {
        $order = $this->createStub(Order::class);
        $order->method('getBaseShippingInclTax')
            ->willReturn(10.0);
        $order->method('getBaseShippingTaxAmount')
            ->willReturn(0.0);

        $this->calculator->calculate($this->createDataHolder(10.0), $order);
        static::assertEquals(0, $this->calculator->getTaxAmount());
    }

    public function testCalculateTakesTaxRateOfTheOrderFromShippingCalculator(): void
    {
        $this->shippingCalculator->expects(static::once())
            ->method('getTaxRateByOrder')
            ->with($this->order);

        $this->calculator->calculate($this->dataHolder, $this->order);
    }

    public function testCalculateSettingTitle(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals('Shipping & Handling (my_shipping_description)', $this->calculator->getTitle());
    }

    public function testCalculateSettingEmptyReference(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals('', $this->calculator->getReference());
    }

    public function testCalculateSettingNotEmptyReference(): void
    {
        $expected = 'my_shipping_method';
        $this->order->method('getShippingMethod')
            ->willReturn($expected);
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals($expected, $this->calculator->getReference());
    }

    public function testCalculateSettingDiscountAmount(): void
    {
        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(0, $this->calculator->getDiscountAmount());
    }

    protected function setUp(): void
    {
        $this->shippingCalculator = $this->createMock(ShippingCalculator::class);
        $this->dataConverter = $this->createStub(DataConverter::class);
        $this->calculator = new PostPurchaseCalculator($this->dataConverter, $this->shippingCalculator);

        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->order = $this->createStub(Order::class);

        $this->dataHolder->method('getBaseShippingInclTax')
            ->willReturn(12.5);
        $this->order->method('getBaseShippingAmount')
            ->willReturn(10.0);
        $this->order->method('getBaseShippingInclTax')
            ->willReturn(12.5);
        $this->order->method('getBaseShippingTaxAmount')
            ->willReturn(2.5);
        // Order currency value; must not be used for the base currency order line
        $this->order->method('getShippingTaxAmount')
            ->willReturn(30.0);
        $this->order->method('getShippingDescription')
            ->willReturn('my_shipping_description');
        $this->shippingCalculator
            ->method('getTaxRateByOrder')
            ->willReturn(25.0);
        $this->dataConverter
            ->method('toApiFloat')
            ->willReturnCallback(fn($float) => (int) round($float * 100));
    }

    /**
     * Creating a data holder with the shipping amount of the current invoice or credit memo
     *
     * @param float $shippingInclTax
     * @return DataHolder
     */
    private function createDataHolder(float $shippingInclTax): DataHolder
    {
        $dataHolder = $this->createStub(DataHolder::class);
        $dataHolder->method('getBaseShippingInclTax')
            ->willReturn($shippingInclTax);

        return $dataHolder;
    }
}
