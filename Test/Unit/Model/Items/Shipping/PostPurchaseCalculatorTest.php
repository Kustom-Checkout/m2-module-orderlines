<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Orderlines\Test\Unit\Model\Items\Shipping;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator
 */
class PostPurchaseCalculatorTest extends TestCase
{
    /**
     * @var PostPurchaseCalculator
     */
    private PostPurchaseCalculator $calculator;
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
        $dataHolder = $this->mockFactory->create(DataHolder::class);
        $dataHolder->method('getBaseShippingInclTax')
            ->willReturn(0.0);

        $this->calculator->calculate($dataHolder, $this->order);
        static::assertEquals(0, $this->calculator->getTotalAmount());
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
    }

    public function testCalculatePartialShippingRefundUsesProportionalTax(): void
    {
        $dataHolder = $this->mockFactory->create(DataHolder::class);
        $dataHolder->method('getBaseShippingInclTax')
            ->willReturn(5.0);

        $this->calculator->calculate($dataHolder, $this->order);
        static::assertEquals(500, $this->calculator->getTotalAmount());
        static::assertEquals(100, $this->calculator->getTaxAmount());
        static::assertEquals(2500, $this->calculator->getTaxRate());
    }

    public function testCalculateOrderWithoutShippingTax(): void
    {
        $order = $this->mockFactory->create(Order::class);
        $order->method('getBaseShippingAmount')
            ->willReturn(10.0);
        $order->method('getBaseShippingInclTax')
            ->willReturn(10.0);
        $order->method('getBaseShippingTaxAmount')
            ->willReturn(0.0);

        $this->calculator->calculate($this->dataHolder, $order);
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
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
        $this->calculator = parent::setUpMocks(PostPurchaseCalculator::class);

        $this->dataHolder = $this->mockFactory->create(DataHolder::class);
        $this->order = $this->mockFactory->create(Order::class);

        $address = $this->mockFactory->create(Address::class);
        $this->dataHolder->setShippingAddress($address);

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
        $this->dependencyMocks['dataConverter']
            ->method('toApiFloat')
            ->willReturnCallback(fn($float) => (int) round($float * 100));
    }
}