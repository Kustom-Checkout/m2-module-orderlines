<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction;

use Klarna\Orderlines\Model\Items\Item\Extraction\ItemValidator;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product\Type;
use Magento\Bundle\Model\Product\Price;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\ItemValidator
 */
class ItemValidatorTest extends TestCase
{
    /**
     * @var ItemValidator
     */
    private ItemValidator $model;
    /**
     * @var Item
     */
    private Item $magentoQuoteItem;
    /**
     * @var DataObject
     */
    private DataObject $product;

    public function testIsBundledProductWithDynamicPriceTypeNotBundledAndNotDynamicImpliesReturningFalse(): void
    {
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn('simple');
        $this->product->setData('price_type', 'fixed');

        static::assertFalse($this->model->isBundledProductWithDynamicPriceType($this->magentoQuoteItem));
    }

    public function testIsBundledProductWithDynamicPriceTypeBundledAndNotDynamicImpliesReturningFalse(): void
    {
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', 'fixed');

        static::assertFalse($this->model->isBundledProductWithDynamicPriceType($this->magentoQuoteItem));
    }

    public function testIsBundledProductWithDynamicPriceTypeNotBundledButDynamicImpliesReturningFalse(): void
    {
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn('simple');
        $this->product->setData('price_type', Price::PRICE_TYPE_DYNAMIC);

        static::assertFalse($this->model->isBundledProductWithDynamicPriceType($this->magentoQuoteItem));
    }

    public function testIsBundledProductWithDynamicPriceTypeBundledAndDynamicImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', (string) Price::PRICE_TYPE_DYNAMIC);

        static::assertTrue($this->model->isBundledProductWithDynamicPriceType($this->magentoQuoteItem));
    }

    public function testIsBundledProductWithDynamicPriceTypeBundledAndDynamicButGivenAsIntegerImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', 0);

        static::assertTrue($this->model->isBundledProductWithDynamicPriceType($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductNoParentItemImpliesReturningFalse(): void
    {
        static::assertFalse($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductParentNoBundleAndPriceTypeNotFixedImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getParentItem')
            ->willReturn($this->magentoQuoteItem);
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn('simple');
        $this->product->setData('price_type', 'dynamic');

        static::assertTrue($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductParentBundleAndPriceTypeNotFixedImpliesReturningFalse(): void
    {
        $this->magentoQuoteItem->method('getParentItem')
            ->willReturn($this->magentoQuoteItem);
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', 'dynamic');

        static::assertFalse($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductParentNoBundleButPriceTypeFixedImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getParentItem')
            ->willReturn($this->magentoQuoteItem);
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn('simple');
        $this->product->setData('price_type', Price::PRICE_TYPE_FIXED);

        static::assertTrue($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductParentBundleAndPriceTypeFixedImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getParentItem')
            ->willReturn($this->magentoQuoteItem);
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', (string) Price::PRICE_TYPE_FIXED);

        static::assertTrue($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    public function testHasInvalidParentProductParentBundleAndPriceTypeFixedAsIntValueImpliesReturningTrue(): void
    {
        $this->magentoQuoteItem->method('getParentItem')
            ->willReturn($this->magentoQuoteItem);
        $this->magentoQuoteItem->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->product->setData('price_type', 1);

        static::assertTrue($this->model->hasInvalidParentProduct($this->magentoQuoteItem));
    }

    protected function setUp(): void
    {
        $this->model = new ItemValidator();

        // getPriceType() is a magic getter: a real DataObject answers it from its data
        $this->product = new DataObject();

        $this->magentoQuoteItem = $this->createStub(Item::class);
        $this->magentoQuoteItem->method('getProduct')
            ->willReturn($this->product);
    }
}
