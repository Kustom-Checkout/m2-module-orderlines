<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction\ShippingAttributes\Entities;

use Klarna\AdminSettings\Model\Configurations\ShippingOptions;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Dimensions;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Dimensions
 */
class DimensionsTest extends TestCase
{
    /**
     * @var Dimensions
     */
    private Dimensions $model;
    /**
     * @var ShippingOptions
     */
    private ShippingOptions $shippingOptions;
    /**
     * @var Product
     */
    private Product $product;

    public function testGetUnitIsCmButNoValueImpliesReturningZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');

        $result = $this->model->get($this->product);
        static::assertEquals(0, $result['height']);
        static::assertEquals(0, $result['width']);
        static::assertEquals(0, $result['length']);
    }

    public function testGetUnitIsCmAndHaveValuesImpliesReturningNonZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('height');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('width');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('length');

        $this->product->method('getData')
            ->willReturnMap([
                ['height', null, 10],
                ['width', null, 20],
                ['length', null, 30],
            ]);

        $result = $this->model->get($this->product);
        static::assertEquals(100, $result['height']);
        static::assertEquals(200, $result['width']);
        static::assertEquals(300, $result['length']);
    }

    public function testGetUnitIsInchButNoValueImpliesReturningZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('inch');

        $result = $this->model->get($this->product);
        static::assertEquals(0, $result['height']);
        static::assertEquals(0, $result['width']);
        static::assertEquals(0, $result['length']);
    }

    public function testGetUnitIsInchAndHaveValuesImpliesReturningNonZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('inch');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('height');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('width');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('length');

        $this->product->method('getData')
            ->willReturnMap([
                ['height', null, 10],
                ['width', null, 20],
                ['length', null, 30],
            ]);

        $result = $this->model->get($this->product);
        static::assertEquals(254, $result['height']);
        static::assertEquals(508, $result['width']);
        static::assertEquals(762, $result['length']);
    }

    public function testGetUnitIsMmButNoValueImpliesReturningZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('mm');

        $result = $this->model->get($this->product);
        static::assertEquals(0, $result['height']);
        static::assertEquals(0, $result['width']);
        static::assertEquals(0, $result['length']);
    }

    public function testGetUnitIsMmAndHaveValuesImpliesReturningNonZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('mm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('height');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('width');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('length');

        $this->product->method('getData')
            ->willReturnMap([
                ['height', null, 10],
                ['width', null, 20],
                ['length', null, 30],
            ]);

        $result = $this->model->get($this->product);
        static::assertEquals(10, $result['height']);
        static::assertEquals(20, $result['width']);
        static::assertEquals(30, $result['length']);
    }

    public function testGetUnitIsUnknownButNoValueImpliesReturningZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('__');

        $result = $this->model->get($this->product);
        static::assertEquals(0, $result['height']);
        static::assertEquals(0, $result['width']);
        static::assertEquals(0, $result['length']);
    }

    public function testGetUnitIsUnknownAndHaveValuesImpliesReturningNonZeroValueForAllDimensions(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('__');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('height');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('width');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('length');

        $this->product->method('getData')
            ->willReturnMap([
                ['height', null, 10],
                ['width', null, 20],
                ['length', null, 30],
            ]);

        $result = $this->model->get($this->product);
        static::assertEquals(10, $result['height']);
        static::assertEquals(20, $result['width']);
        static::assertEquals(30, $result['length']);
    }

    protected function setUp(): void
    {
        $this->shippingOptions = $this->createStub(ShippingOptions::class);
        $this->model = new Dimensions($this->shippingOptions);

        $store = $this->createStub(Store::class);
        $this->product = $this->createStub(Product::class);
        $this->product->method('getStore')
            ->willReturn($store);
    }
}
