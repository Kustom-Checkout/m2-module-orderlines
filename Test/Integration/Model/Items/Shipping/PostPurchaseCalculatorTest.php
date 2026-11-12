<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Integration\Model\Items\Shipping;

use Klarna\Orderlines\Model\Calculator\Shipping;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Shipping\PostPurchaseCalculator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Shipping order line of a capture or refund, built with Magento's real order tax storage
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PostPurchaseCalculatorTest extends TestCase
{
    private const ORDER_ID = 990001;

    /**
     * Untyped on purpose: Magento 2.4.5 - 2.4.7 set test properties to null after each test, which fails for
     * non-nullable typed properties.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function testCaptureOfShippingSendsTheRateMagentoStoredInsteadOfTheRoundedRatio(): void
    {
        // 1.00 / 3.99 = 25.06%: the line must carry the 25% Magento applied
        $this->storeShippingTax([['SE 25%', 25.0, 1.0]]);

        $calculator = $this->calculate($this->createOrder(3.99, 1.0), 4.99);

        static::assertEquals(499, $calculator->getTotalAmount());
        static::assertEquals(2500, $calculator->getTaxRate());
        static::assertEquals(100, $calculator->getTaxAmount());
    }

    public function testCaptureWithoutShippingSendsAnEmptyShippingLine(): void
    {
        $this->storeShippingTax([['SE 25%', 25.0, 1.0]]);

        $calculator = $this->calculate($this->createOrder(3.99, 1.0), 0.0);

        static::assertEquals(0, $calculator->getTotalAmount());
        static::assertEquals(0, $calculator->getTaxRate());
        static::assertEquals(0, $calculator->getTaxAmount());
    }

    public function testPartialShippingRefundKeepsTheRateAndProRatesTheTax(): void
    {
        $this->storeShippingTax([['SE 25%', 25.0, 7.0]]);

        $calculator = $this->calculate($this->createOrder(28.0, 7.0), 17.5);

        static::assertEquals(1750, $calculator->getTotalAmount());
        static::assertEquals(2500, $calculator->getTaxRate());
        static::assertEquals(350, $calculator->getTaxAmount());
    }

    public function testRatesOfSeveralShippingTaxesAreAddedUp(): void
    {
        $this->storeShippingTax([['STATE 5%', 5.0, 0.5], ['CITY 7%', 7.0, 0.7]]);

        static::assertEquals(12.0, $this->getShippingCalculator()->getTaxRateByOrder($this->createOrder(10.0, 1.2)));
    }

    public function testTaxStoredForProductsIsIgnored(): void
    {
        $this->storeTax('product', [['REDUCED 12%', 12.0, 1.2]]);
        $this->storeShippingTax([['SE 25%', 25.0, 1.0]]);

        static::assertEquals(25.0, $this->getShippingCalculator()->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testSubclassThatSkipsTheParentConstructorStillReadsTheStoredRate(): void
    {
        $this->storeShippingTax([['SE 25%', 25.0, 1.0]]);
        // Calculator\Shipping had no constructor before 3.2.0, so merchant subclasses may not call it
        $calculator = new class extends Shipping {
            public function __construct()
            {
            }
        };

        static::assertEquals(25.0, $calculator->getTaxRateByOrder($this->createOrder(3.99, 1.0)));
    }

    public function testOrderWithoutStoredShippingTaxFallsBackToTheOrderTotals(): void
    {
        $calculator = $this->calculate($this->createOrder(3.99, 1.0), 4.99);

        static::assertEquals(2506, $calculator->getTaxRate());
        static::assertEquals(100, $calculator->getTaxAmount());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(ResourceConnection::class);
    }

    /**
     * Building the shipping order line for an invoice or credit memo billing the given shipping incl. tax
     *
     * @param Order $order
     * @param float $shippingInclTax
     * @return PostPurchaseCalculator
     */
    private function calculate(Order $order, float $shippingInclTax): PostPurchaseCalculator
    {
        $dataHolder = $this->objectManager->create(DataHolder::class);
        $dataHolder->setBaseShippingInclTax($shippingInclTax);

        $calculator = $this->objectManager->create(PostPurchaseCalculator::class);
        $calculator->calculate($dataHolder, $order);

        return $calculator;
    }

    /**
     * Getting the shipping calculator as Magento's DI builds it
     *
     * @return Shipping
     */
    private function getShippingCalculator(): Shipping
    {
        return $this->objectManager->create(Shipping::class);
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
        $order = $this->objectManager->create(Order::class);
        $order->setEntityId(self::ORDER_ID)
            ->setShippingDescription('Flat Rate - Fixed')
            ->setShippingMethod('flatrate_flatrate')
            ->setBaseShippingAmount($shippingAmount)
            ->setBaseShippingTaxAmount($shippingTaxAmount)
            ->setBaseShippingInclTax($shippingAmount + $shippingTaxAmount);

        return $order;
    }

    /**
     * Storing shipping tax rows the way Magento does at order placement
     *
     * @param array $taxes list of [code, percent, base amount]
     */
    private function storeShippingTax(array $taxes): void
    {
        $this->storeTax('shipping', $taxes);
    }

    /**
     * Storing tax rows in sales_order_tax and sales_order_tax_item for the test order
     *
     * @param string $taxableItemType
     * @param array $taxes list of [code, percent, base amount]
     */
    private function storeTax(string $taxableItemType, array $taxes): void
    {
        $connection = $this->resource->getConnection('sales');
        foreach ($taxes as [$code, $percent, $amount]) {
            $connection->insert(
                $this->resource->getTableName('sales_order_tax', 'sales'),
                [
                    'order_id' => self::ORDER_ID,
                    'code' => $code,
                    'title' => $code,
                    'percent' => $percent,
                    'amount' => $amount,
                    'base_amount' => $amount,
                    'base_real_amount' => $amount,
                    'priority' => 0,
                    'position' => 0,
                    'process' => 0,
                ]
            );
            $connection->insert(
                $this->resource->getTableName('sales_order_tax_item', 'sales'),
                [
                    'tax_id' => (int) $connection->lastInsertId(),
                    'item_id' => null,
                    'tax_percent' => $percent,
                    'amount' => $amount,
                    'base_amount' => $amount,
                    'real_amount' => $amount,
                    'real_base_amount' => $amount,
                    'associated_item_id' => null,
                    'taxable_item_type' => $taxableItemType,
                ]
            );
        }
    }
}
