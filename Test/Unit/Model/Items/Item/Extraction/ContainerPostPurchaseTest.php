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
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\BaseResult;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\ExclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\InclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\Store;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Amounts of order items on a capture (invoice) or refund (credit memo) for only part of the ordered quantity
 *
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Container
 */
class ContainerPostPurchaseTest extends TestCase
{
    /**
     * @var Container
     */
    private Container $model;
    /**
     * @var Store
     */
    private Store $store;
    /**
     * @var Product
     */
    private Product $product;

    public function testSetValuesInvoicingOneOfTwoItemsUsesTheAmountsOfTheInvoiceItem(): void
    {
        // 2 x 49.00 incl 25% tax ordered, 1 invoiced
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 39.20,
            'current_invoice_refund_item_base_tax_amount' => 9.80,
            'current_invoice_refund_item_base_discount_amount' => 0.0,
            'current_invoice_refund_item_base_discount_tax_compensation_amount' => 0.0,
        ]);

        $this->model->setValues($item, $this->store);

        static::assertEquals(1, $this->model->getQty());
        static::assertEquals(39.20, $this->model->getRowTotal());
        static::assertEquals(49.00, $this->model->getRowTotalIncludedTax());
        static::assertEquals(9.80, $this->model->getTaxAmount());
        static::assertEquals(0.0, $this->model->getDiscountAmount());
    }

    public function testSetValuesInvoicingPartOfADiscountedItemUsesTheDiscountAndTaxOfTheInvoiceItem(): void
    {
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 39.20,
            'current_invoice_refund_item_base_tax_amount' => 8.82,
            'current_invoice_refund_item_base_discount_amount' => 4.90,
            'current_invoice_refund_item_base_discount_tax_compensation_amount' => 0.98,
        ]);

        $this->model->setValues($item, $this->store);

        static::assertEquals(39.20, $this->model->getRowTotal());
        static::assertEquals(49.00, $this->model->getRowTotalIncludedTax());
        static::assertEquals(8.82, $this->model->getTaxAmount());
        static::assertEquals(4.90, $this->model->getDiscountAmount());
    }

    public function testSetValuesInvoiceItemWithoutTaxAndDiscountDoesNotUseTheAmountsOfTheOrderItem(): void
    {
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 49.00,
            'current_invoice_refund_item_base_tax_amount' => null,
            'current_invoice_refund_item_base_discount_amount' => null,
            'current_invoice_refund_item_base_discount_tax_compensation_amount' => 0.0,
        ]);

        $this->model->setValues($item, $this->store);

        static::assertEquals(0.0, $this->model->getTaxAmount());
        static::assertEquals(0.0, $this->model->getDiscountAmount());
        static::assertEquals(49.00, $this->model->getRowTotalIncludedTax());
    }

    public function testInclusiveTaxOrderLineOfOneOfTwoInvoicedItems(): void
    {
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 39.20,
            'current_invoice_refund_item_base_tax_amount' => 9.80,
            'current_invoice_refund_item_base_discount_amount' => 0.0,
            'current_invoice_refund_item_base_discount_tax_compensation_amount' => 0.0,
        ]);
        $this->model->setValues($item, $this->store);

        $orderLine = $this->createInclusiveTaxCalculator(true)->getOrderLineItem($this->model);

        static::assertEquals(1, $orderLine['quantity']);
        static::assertEquals(4900, $orderLine['unit_price']);
        static::assertEquals(4900, $orderLine['total_amount']);
        static::assertEquals(980, $orderLine['total_tax_amount']);
        static::assertEquals(2500, $orderLine['tax_rate']);
    }

    public function testInclusiveTaxOrderLineOfOneOfTwoRefundedDiscountedItems(): void
    {
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 39.20,
            'current_invoice_refund_item_base_tax_amount' => 8.82,
            'current_invoice_refund_item_base_discount_amount' => 4.90,
            'current_invoice_refund_item_base_discount_tax_compensation_amount' => 0.98,
        ]);
        $this->model->setValues($item, $this->store);

        $orderLine = $this->createInclusiveTaxCalculator(false)->getOrderLineItem($this->model);

        static::assertEquals(4900, $orderLine['unit_price']);
        static::assertEquals(4410, $orderLine['total_amount']);
        static::assertEquals(882, $orderLine['total_tax_amount']);
    }

    public function testExclusiveTaxOrderLineOfOneOfTwoInvoicedItems(): void
    {
        $item = $this->createOrderItem([
            'current_invoice_refund_item_qty' => 1,
            'current_invoice_refund_item_base_row_total' => 39.20,
            'current_invoice_refund_item_base_tax_amount' => 9.80,
            'current_invoice_refund_item_base_discount_amount' => 0.0,
        ]);
        $this->model->setValues($item, $this->store);

        $baseResult = $this->mockFactory->create(BaseResult::class);
        $baseResult->method('getFromContainer')
            ->willReturn(['quantity' => $this->model->getQty()]);
        $orderLine = (new ExclusiveTaxCalculator($baseResult, $this->createDataConverter()))
            ->getOrderLineItem($this->model);

        static::assertEquals(3920, $orderLine['unit_price']);
        static::assertEquals(3920, $orderLine['total_amount']);
    }

    /**
     * Creating the inclusive tax calculator with the base result giving back the container qty
     *
     * @param bool $applyTaxAfterDiscount
     * @return InclusiveTaxCalculator
     */
    private function createInclusiveTaxCalculator(bool $applyTaxAfterDiscount): InclusiveTaxCalculator
    {
        $baseResult = $this->mockFactory->create(BaseResult::class);
        $baseResult->method('getFromContainer')
            ->willReturn(['quantity' => $this->model->getQty()]);
        $taxConfig = $this->mockFactory->create(TaxConfig::class);
        $taxConfig->method('applyTaxAfterDiscount')
            ->willReturn($applyTaxAfterDiscount);

        return new InclusiveTaxCalculator($baseResult, $this->createDataConverter(), $taxConfig);
    }

    /**
     * Creating a data converter converting amounts to minor units
     *
     * @return DataConverter
     */
    private function createDataConverter(): DataConverter
    {
        $dataConverter = $this->mockFactory->create(DataConverter::class);
        $dataConverter->method('toApiFloat')
            ->willReturnCallback(fn($float) => (int) round($float * 100));

        return $dataConverter;
    }

    public function testSetValuesWithoutInvoiceOrCreditMemoUsesTheAmountsOfTheOrderItem(): void
    {
        $item = $this->createOrderItem([]);

        $this->model->setValues($item, $this->store);

        static::assertEquals(2, $this->model->getQty());
        static::assertEquals(78.40, $this->model->getRowTotal());
        static::assertEquals(78.40 + 19.60 + 1.96, $this->model->getRowTotalIncludedTax());
        static::assertEquals(19.60, $this->model->getTaxAmount());
        static::assertEquals(9.80, $this->model->getDiscountAmount());
    }

    /**
     * Creating an order item of 2 x 49.00 incl 25% tax with the given invoice or credit memo item values
     *
     * @param array $documentItemData
     * @return OrderItem
     */
    private function createOrderItem(array $documentItemData): OrderItem
    {
        $item = $this->mockFactory->create(OrderItem::class, ['getProduct']);
        $item->method('getProduct')
            ->willReturn($this->product);
        $item->setData(array_merge(
            [
                'product_type' => 'simple',
                'qty_ordered' => 2,
                'tax_percent' => 25.0,
                'base_price' => 39.20,
                'base_row_total' => 78.40,
                'base_tax_amount' => 19.60,
                'base_discount_amount' => 9.80,
                'discount_tax_compensation_amount' => 1.96,
                'base_discount_tax_compensation_amount' => 1.96,
            ],
            $documentItemData
        ));

        return $item;
    }

    protected function setUp(): void
    {
        $this->model = parent::setUpMocks(Container::class);
        $this->store = $this->mockFactory->create(Store::class);
        $this->product = $this->mockFactory->create(Product::class);
    }
}
