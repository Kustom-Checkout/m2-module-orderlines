<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model;

use Klarna\Orderlines\Model\ProductTypeChecker;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Price;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\ProductTypeChecker
 */
#[AllowMockObjectsWithoutExpectations]
class ProductTypeCheckerTest extends TestCase
{
    /**
     * @var ProductTypeChecker
     */
    private ProductTypeChecker $productTypeChecker;
    /**
     * @var Item
     */
    private Item $item;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var string|null
     */
    private ?string $priceType = null;

    public function testIsBundledProductWithDynamicPriceTypeIsBundledAndDynamicReturnsTrue(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->priceType = (string) Price::PRICE_TYPE_DYNAMIC;
        static::assertTrue($this->productTypeChecker->isBundledProductWithDynamicPriceType($this->item));
    }

    public function testIsBundledProductWithDynamicPriceTypeIsBundledAndFixedReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->priceType = (string) Price::PRICE_TYPE_FIXED;
        static::assertFalse($this->productTypeChecker->isBundledProductWithDynamicPriceType($this->item));
    }

    public function testIsBundledProductWithDynamicPriceTypeIsNotBundledAndDynamicReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->priceType = (string) Price::PRICE_TYPE_DYNAMIC;
        static::assertFalse($this->productTypeChecker->isBundledProductWithDynamicPriceType($this->item));
    }

    public function testIsBundledProductWithDynamicPriceTypeIsNotBundledAndFixedReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->priceType = (string) Price::PRICE_TYPE_FIXED;
        static::assertFalse($this->productTypeChecker->isBundledProductWithDynamicPriceType($this->item));
    }

    public function testIsBundledProductWithFixedPriceTypeIsBundledAndFixedReturnsTrue(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->priceType = (string) Price::PRICE_TYPE_FIXED;
        static::assertTrue($this->productTypeChecker->isBundledProductWithFixedPriceType($this->item));
    }

    #[Test]
    public function isBundledProductWithFixedPriceTypeIsBundledAndDynamicReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->priceType = (string) Price::PRICE_TYPE_DYNAMIC;
        static::assertFalse($this->productTypeChecker->isBundledProductWithFixedPriceType($this->item));
    }

    #[Test]
    public function isBundledProductWithFixedPriceTypeIsNotBundledAndFixedReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->priceType = (string) Price::PRICE_TYPE_FIXED;
        static::assertFalse($this->productTypeChecker->isBundledProductWithFixedPriceType($this->item));
    }

    #[Test]
    public function isBundledProductWithFixedPriceTypeIsNotBundledAndDynamicReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->priceType = (string) Price::PRICE_TYPE_DYNAMIC;
        static::assertFalse($this->productTypeChecker->isBundledProductWithFixedPriceType($this->item));
    }

    public function testIsNonBundleProductProductIsNonBundledReturnsTrue(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        static::assertTrue($this->productTypeChecker->isNonBundleProduct($this->item));
    }

    public function testIsNonBundleProductProductIsBundledReturnsFalse(): void
    {
        $this->item->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        static::assertFalse($this->productTypeChecker->isNonBundleProduct($this->item));
    }

    public function testIsFixedPriceTypePriceTypeIsFixedReturnsTrue(): void
    {
        $this->priceType = (string) Price::PRICE_TYPE_FIXED;
        static::assertTrue($this->productTypeChecker->isFixedPriceType($this->item));
    }

    public function testIsFixedPriceTypePriceTypeIsNotFixedReturnsFalse(): void
    {
        $this->priceType = (string) Price::PRICE_TYPE_DYNAMIC;
        static::assertFalse($this->productTypeChecker->isFixedPriceType($this->item));
    }

    protected function setUp(): void
    {
        $this->productTypeChecker = new ProductTypeChecker();

        $this->item = $this->createStub(Item::class);
        // getPriceType() is a magic getter: stub the real getData() it reads from
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        $this->product->method('getData')
            ->willReturnCallback(fn($key = '') => $key === 'price_type' ? $this->priceType : null);
        $this->item->method('getProduct')
            ->willReturn($this->product);
    }
}
