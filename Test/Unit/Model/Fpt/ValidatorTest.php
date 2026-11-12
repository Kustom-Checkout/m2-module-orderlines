<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Fpt;

use Magento\Store\Api\Data\StoreInterface;
use Klarna\Orderlines\Model\Fpt\Validator;
use Klarna\Orderlines\Model\ProductTypeChecker;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var StoreInterface
     */
    private $store;
    /**
     * @var Creditmemo
     */
    private $data;
    /**
     * @var ProductTypeChecker
     */
    private $productTypeChecker;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var InvoiceItem
     */
    private $invoiceItem;
    /**
     * @var CreditMemoItem
     */
    private $creditMemoItem;
    /**
     * @var OrderItem
     */
    private $item;
    /**
     * @var OrderItemInterface
     */
    private $order;

    public function testIsFptUsableTrue(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('value');
        $this->scopeConfig->method('isSetFlag')
            ->willReturn(true);
        static::assertTrue($this->validator->isFptUsable($this->store));
    }

    public function testIsFptUsableFlagNotSet(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->willReturn(false);
        static::assertFalse($this->validator->isFptUsable($this->store));
    }

    public function testIsFptUsableGetValueReturnsNull(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->willReturn(true);
        $this->scopeConfig->method('getValue')
            ->willReturn('');
        static::assertFalse($this->validator->isFptUsable($this->store));
    }

    public function testIsValidOrderItemIsNotCorrectInstance(): void
    {
        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->item));
    }

    public function testIsValidOrderItemParentItemNull(): void
    {
        $this->creditMemoItem->method('getOrderItem')
            ->willReturn($this->item);
        $this->item->method('getParentItem')
            ->willReturn(null);

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemGetParentItemByIDReturnsNull(): void
    {
        $this->creditMemoItem->method('getOrderItem')
            ->willReturn($this->item);
        $this->item->method('getParentItem')
            ->willReturn($this->order);
        $this->item->method('getParentItemId')
            ->willReturn(null);

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsNonBundleProduct(): void
    {
        $this->creditInvoiceItemValidHelper();

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(true);

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsBundledProductWithFixedPriceType(): void
    {
        $this->creditInvoiceItemValidHelper();

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithFixedPriceType')
            ->willReturn(true);

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsBundledProductWithDynamicPriceType(): void
    {
        $this->creditInvoiceItemValidHelper();

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithFixedPriceType')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithDynamicPriceType')
            ->willReturn(true);

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsBundledProductWithDynamicPriceTypeParentItem(): void
    {
        $this->creditInvoiceItemValidHelper();

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithFixedPriceType')
            ->willReturn(false);
        $dynamicChecks = [false, true];
        $this->checkerMock()->expects(static::exactly(2))
            ->method('isBundledProductWithDynamicPriceType')
            ->willReturnCallback(function () use (&$dynamicChecks): bool {
                return array_shift($dynamicChecks);
            });

        static::assertFalse($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsValidCreditMemo(): void
    {
        $this->creditInvoiceItemValidHelper();

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithFixedPriceType')
            ->willReturn(false);

        static::assertTrue($this->validator->isValidOrderItem($this->data, $this->creditMemoItem));
    }

    public function testIsValidOrderItemIsValidInvoice(): void
    {
        $this->invoiceItem->method('getOrderItem')
            ->willReturn($this->item);
        $this->item->method('getParentItem')
            ->willReturn($this->order);
        $this->item->method('getParentItemId')
            ->willReturn('parent_item_id');
        $this->data->method('getItemById')
            ->willReturn($this->invoiceItem);

        $this->checkerMock()->expects(static::once())
            ->method('isNonBundleProduct')
            ->willReturn(false);
        $this->checkerMock()->expects(static::once())
            ->method('isBundledProductWithFixedPriceType')
            ->willReturn(false);

        static::assertTrue($this->validator->isValidOrderItem($this->data, $this->invoiceItem));
    }

    /**
     * Swaps the product type checker for a mock (only the tests that set expectations on it use one)
     *
     * @return ProductTypeChecker|MockObject
     */
    private function checkerMock()
    {
        if (!$this->productTypeChecker instanceof MockObject) {
            $this->productTypeChecker = $this->createMock(ProductTypeChecker::class);
            $this->validator = new Validator($this->productTypeChecker, $this->scopeConfig);
        }

        return $this->productTypeChecker;
    }

    private function creditInvoiceItemValidHelper(): void
    {
        $this->creditMemoItem->method('getOrderItem')
            ->willReturn($this->item);
        $this->item->method('getParentItem')
            ->willReturn($this->order);
        $this->item->method('getParentItemId')
            ->willReturn('parent_item_id');
        $this->data->method('getItemById')
            ->willReturn($this->creditMemoItem);
    }

    protected function setUp(): void
    {
        $this->productTypeChecker = $this->createStub(ProductTypeChecker::class);
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->validator = new Validator($this->productTypeChecker, $this->scopeConfig);

        $this->store = $this->createStub(StoreInterface::class);
        $this->order = $this->createStub(OrderItemInterface::class);

        // Real class with a real getItemById() (not part of ExtensibleDataInterface itself)
        $this->data = $this->createStub(Creditmemo::class);
        $this->creditMemoItem = $this->createStub(CreditMemoItem::class);
        $this->invoiceItem = $this->createStub(InvoiceItem::class);
        $this->item = $this->createStub(OrderItem::class);
    }
}
