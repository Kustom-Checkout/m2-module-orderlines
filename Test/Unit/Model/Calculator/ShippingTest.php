<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Calculator;

use Klarna\Orderlines\Model\Calculator\Shipping;
use Klarna\Orderlines\Model\Container\DataHolder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Calculator\Shipping
 */
#[AllowMockObjectsWithoutExpectations]
class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    private Shipping $model;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var OrderTaxManagementInterface&\PHPUnit\Framework\MockObject\Stub
     */
    private $orderTaxManagement;
    /**
     * @var Address
     */
    private Address $shippingAddress;
    /**
     * @var array
     */
    private array $addressData = [];

    public function testGetTaxRateEmptySingleTaxRateEntryAndShippingAmountIsZeroFromTypeIntImpliesReturningZero(): void
    {
        $this->setAddressData('shipping_amount', 0);
        static::assertEquals(0, $this->model->getTaxRate($this->dataHolder));
    }

    public function testGetTaxRateEmptySingleTaxRateEntryAndShippingAmountIsZeroFromTypeStringImpliesReturningZero(): void
    {
        $this->setAddressData('shipping_amount', '0');
        static::assertEquals(0, $this->model->getTaxRate($this->dataHolder));
    }

    public function testGetTaxRateEmptySingleTaxRateEntryAndShippingAmountIsZeroWithDecimalValuesFromTypeStringImpliesReturningZero(): void
    {
        $this->setAddressData('shipping_amount', '0.00000');
        static::assertEquals(0, $this->model->getTaxRate($this->dataHolder));
    }

    public function testGetTaxRateEmptySingleTaxRateEntryAndReturningCalculatedValue(): void
    {
        $this->setShippingAmounts(1.0, 0.19);
        static::assertEqualsWithDelta(19, $this->model->getTaxRate($this->dataHolder), 0.0001);
    }

    public function testGetTaxRateByShippingCostsEmptySingleTaxRateEntryAndShippingCostsAreZero(): void
    {
        static::assertEquals(0, $this->model->getTaxRateByShippingCosts($this->shippingAddress, 0));
    }

    public function testGetTaxRateItemsAppliedTaxesKeySetButNotOtherKeysImpliesCalculatingTaxRate(): void
    {
        $this->setAddressData('items_applied_taxes', []);
        $this->setShippingAmounts(1.0, 0.19);
        static::assertEqualsWithDelta(19, $this->model->getTaxRate($this->dataHolder), 0.0001);
    }

    public function testGetTaxRateItemsAppliedTaxesKeySetButNotShippingKeyImpliesCalculatingTaxRate(): void
    {
        $this->setAddressData('items_applied_taxes', ['a' => [['percent' => 27], ['percent' => 26]]]);
        $this->setShippingAmounts(1.0, 0.19);
        static::assertEqualsWithDelta(19, $this->model->getTaxRate($this->dataHolder), 0.0001);
    }

    public function testGetTaxRateItemsAppliedTaxesAndShippingKeyButSeveralRatesUsedImpliesCalculatingTaxRate(): void
    {
        $this->setAddressData(
            'items_applied_taxes',
            ['shipping' => [['percent' => 27], ['percent' => 26]]]
        );
        $this->setShippingAmounts(1.0, 0.19);
        static::assertEqualsWithDelta(19, $this->model->getTaxRate($this->dataHolder), 0.0001);
    }

    public function testGetTaxRateItemsAppliedTaxesAndShippingKeyAndJustOneRateUsedImpliesReturningStoredTaxRate(): void
    {
        $this->setAddressData('items_applied_taxes', ['shipping' => [['percent' => 27]]]);
        $this->setShippingAmounts(1.0, 0.19);
        static::assertEquals(27, $this->model->getTaxRate($this->dataHolder));
    }

    public function testGetTaxRateByOrderUsesShippingTaxRateStoredByMagentoInsteadOfRoundedAmounts(): void
    {
        // 1.00 / 3.99 = 25.06%: the rate must not be worked out from the rounded amounts
        $this->mockStoredTaxItems([['shipping', [25.0]]]);
        static::assertEquals(25.0, $this->model->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testGetTaxRateByOrderUsesStoredDecimalShippingTaxRate(): void
    {
        $this->mockStoredTaxItems([['shipping', [25.5]]]);
        static::assertEquals(25.5, $this->model->getTaxRateByOrder($this->createOrder(3.99, 1.02)));
    }

    public function testGetTaxRateByOrderAddsUpAllShippingTaxRatesStoredByMagento(): void
    {
        $this->mockStoredTaxItems([['shipping', [5.0, 7.0]]]);
        static::assertEquals(12.0, $this->model->getTaxRateByOrder($this->createOrder(10.0, 1.2)));
    }

    public function testGetTaxRateByOrderIgnoresTaxRatesStoredForOtherItems(): void
    {
        $this->mockStoredTaxItems([['product', [12.0]], ['shipping', [25.0]]]);
        static::assertEquals(25.0, $this->model->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testGetTaxRateByOrderUsesOrderTotalsWhenMagentoStoredNoShippingTaxRate(): void
    {
        $this->mockStoredTaxItems([['product', [12.0]]]);
        static::assertEquals(25.06, $this->model->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testGetTaxRateByOrderUsesOrderTotalsWhenStoredTaxDetailsCannotBeRead(): void
    {
        $this->orderTaxManagement
            ->method('getOrderTaxDetails')
            ->willThrowException(new NoSuchEntityException());
        static::assertEquals(25.06, $this->model->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testGetTaxRateByOrderWithoutShippingTaxReturnsZero(): void
    {
        $this->mockStoredTaxItems([['shipping', [25.0]]]);
        static::assertEquals(0.0, $this->model->getTaxRateByOrder($this->createOrder(10.0, 0.0)));
    }

    public function testGetTaxRateByOrderWithoutShippingReturnsZero(): void
    {
        $this->mockStoredTaxItems([['shipping', [25.0]]]);
        static::assertEquals(0.0, $this->model->getTaxRateByOrder($this->createOrder(0.0, 0.0)));
    }

    protected function setUp(): void
    {
        $this->orderTaxManagement = $this->createStub(OrderTaxManagementInterface::class);
        $this->model = new Shipping($this->orderTaxManagement);

        $this->dataHolder = $this->createStub(DataHolder::class);
        // Quote address whose getData() (and so its magic getters) reads the data set in each test
        $this->shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        $this->shippingAddress->method('getData')
            ->willReturnCallback(
                fn($key = '', $index = null) => $key === '' ? $this->addressData : ($this->addressData[$key] ?? null)
            );
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
    }

    /**
     * Setting a value on the quote address
     *
     * @param string $key
     * @param mixed $value
     */
    private function setAddressData(string $key, $value): void
    {
        $this->addressData[$key] = $value;
    }

    /**
     * Setting the base shipping amounts on the quote address
     *
     * @param float $shippingAmount
     * @param float $shippingTaxAmount
     */
    private function setShippingAmounts(float $shippingAmount, float $shippingTaxAmount): void
    {
        $this->setAddressData('base_shipping_amount', $shippingAmount);
        $this->setAddressData('base_shipping_tax_amount', $shippingTaxAmount);
    }

    /**
     * Creating an order with the given base shipping amounts
     *
     * @param float $shippingAmount
     * @param float $shippingTaxAmount
     * @return Order
     */
    private function createOrder(float $shippingAmount, float $shippingTaxAmount): Order
    {
        $order = $this->createStub(Order::class);
        $order->method('getEntityId')
            ->willReturn(1);
        $order->method('getBaseShippingAmount')
            ->willReturn($shippingAmount);
        $order->method('getBaseShippingTaxAmount')
            ->willReturn($shippingTaxAmount);

        return $order;
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
                $appliedTax = $this->createStub(OrderTaxDetailsAppliedTaxInterface::class);
                $appliedTax->method('getPercent')
                    ->willReturn($percent);
                $appliedTaxes[] = $appliedTax;
            }

            $taxItem = $this->createStub(OrderTaxDetailsItemInterface::class);
            $taxItem->method('getType')
                ->willReturn($type);
            $taxItem->method('getAppliedTaxes')
                ->willReturn($appliedTaxes);
            $taxItems[] = $taxItem;
        }

        $taxDetails = $this->createStub(OrderTaxDetailsInterface::class);
        $taxDetails->method('getItems')
            ->willReturn($taxItems);
        $this->orderTaxManagement
            ->method('getOrderTaxDetails')
            ->willReturn($taxDetails);
    }
}
