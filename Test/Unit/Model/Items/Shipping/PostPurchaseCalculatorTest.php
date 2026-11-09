<?php
/**
 * Copyright © Kustom AB (Originally developed by Klarna Bank AB)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Shipping;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;

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
        $order = $this->createOrder(10.0, 0.0, 10.0);
        $this->mockStoredTaxItems([['shipping', [25.0]]]);

        $this->calculator->calculate($this->dataHolder, $order);
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
    }

    public function testCalculateUsesShippingTaxRateStoredByMagentoInsteadOfRoundedAmounts(): void
    {
        // 1.00 / 3.99 = 25.06%: the rate must not be worked out from the rounded amounts
        $order = $this->createOrder(3.99, 1.0, 4.99);
        $dataHolder = $this->createDataHolder(4.99);
        $this->mockStoredTaxItems([['shipping', [25.0]]]);

        $this->calculator->calculate($dataHolder, $order);
        static::assertEquals(2500, $this->calculator->getTaxRate());
        static::assertEquals(100, $this->calculator->getTaxAmount());
    }

    public function testCalculateUsesStoredDecimalShippingTaxRate(): void
    {
        $order = $this->createOrder(3.99, 1.02, 5.01);
        $dataHolder = $this->createDataHolder(5.01);
        $this->mockStoredTaxItems([['shipping', [25.5]]]);

        $this->calculator->calculate($dataHolder, $order);
        static::assertEquals(2550, $this->calculator->getTaxRate());
    }

    public function testCalculateAddsUpAllShippingTaxRatesStoredByMagento(): void
    {
        $this->mockStoredTaxItems([['shipping', [5.0, 7.0]]]);

        $this->calculator->calculate($this->dataHolder, $this->order);
        static::assertEquals(1200, $this->calculator->getTaxRate());
    }

    public function testCalculateIgnoresTaxRatesStoredForOtherItems(): void
    {
        $order = $this->createOrder(3.99, 1.0, 4.99);
        $dataHolder = $this->createDataHolder(4.99);
        $this->mockStoredTaxItems([['product', [12.0]], ['shipping', [25.0]]]);

        $this->calculator->calculate($dataHolder, $order);
        static::assertEquals(2500, $this->calculator->getTaxRate());
    }

    public function testCalculateUsesOrderTotalsWhenMagentoStoredNoShippingTaxRate(): void
    {
        $order = $this->createOrder(3.99, 1.0, 4.99);
        $dataHolder = $this->createDataHolder(4.99);
        $this->mockStoredTaxItems([['product', [12.0]]]);

        $this->calculator->calculate($dataHolder, $order);
        static::assertEquals(2506, $this->calculator->getTaxRate());
    }

    public function testCalculateUsesOrderTotalsWhenStoredTaxDetailsCannotBeRead(): void
    {
        $order = $this->createOrder(3.99, 1.0, 4.99);
        $dataHolder = $this->createDataHolder(4.99);
        $this->dependencyMocks['orderTaxManagement']
            ->method('getOrderTaxDetails')
            ->willThrowException(new NoSuchEntityException());

        $this->calculator->calculate($dataHolder, $order);
        static::assertEquals(2506, $this->calculator->getTaxRate());
        static::assertEquals(100, $this->calculator->getTaxAmount());
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

    /**
     * Creating an order with the given base shipping amounts
     *
     * @param float $shippingAmount
     * @param float $shippingTaxAmount
     * @param float $shippingInclTax
     * @return Order
     */
    private function createOrder(float $shippingAmount, float $shippingTaxAmount, float $shippingInclTax): Order
    {
        $order = $this->mockFactory->create(Order::class);
        $order->method('getEntityId')
            ->willReturn(1);
        $order->method('getBaseShippingAmount')
            ->willReturn($shippingAmount);
        $order->method('getBaseShippingTaxAmount')
            ->willReturn($shippingTaxAmount);
        $order->method('getBaseShippingInclTax')
            ->willReturn($shippingInclTax);

        return $order;
    }

    /**
     * Creating a data holder with the shipping amount of the current invoice or credit memo
     *
     * @param float $shippingInclTax
     * @return DataHolder
     */
    private function createDataHolder(float $shippingInclTax): DataHolder
    {
        $dataHolder = $this->mockFactory->create(DataHolder::class);
        $dataHolder->method('getBaseShippingInclTax')
            ->willReturn($shippingInclTax);

        return $dataHolder;
    }

    /**
     * Mocking the order tax details Magento stored at order placement (sales_order_tax_item)
     *
     * @param array $items list of [type, [percent, ...]]
     */
    private function mockStoredTaxItems(array $items): void
    {
        $taxItems = [];
        foreach ($items as [$type, $percents]) {
            $appliedTaxes = [];
            foreach ($percents as $percent) {
                $appliedTax = $this->mockFactory->create(OrderTaxDetailsAppliedTaxInterface::class);
                $appliedTax->method('getPercent')
                    ->willReturn($percent);
                $appliedTaxes[] = $appliedTax;
            }

            $taxItem = $this->mockFactory->create(OrderTaxDetailsItemInterface::class);
            $taxItem->method('getType')
                ->willReturn($type);
            $taxItem->method('getAppliedTaxes')
                ->willReturn($appliedTaxes);
            $taxItems[] = $taxItem;
        }

        $taxDetails = $this->mockFactory->create(OrderTaxDetailsInterface::class);
        $taxDetails->method('getItems')
            ->willReturn($taxItems);
        $this->dependencyMocks['orderTaxManagement']
            ->method('getOrderTaxDetails')
            ->willReturn($taxDetails);
    }
}
