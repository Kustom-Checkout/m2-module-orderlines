<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Calculator\ItemShippingAttributes;

use Klarna\AdminSettings\Model\Configurations\ShippingOptions;
use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Dimensions;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Dimensions
 */
class DimensionsTest extends TestCase
{
    /**
     * @var Dimensions
     */
    private Dimensions $dimensions;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;
    /**
     * @var ShippingOptions&\PHPUnit\Framework\MockObject\Stub
     */
    private $shippingOptions;

    public function testGetUnknownUnitGivenAndUnknownAttributeGivenImpliesUsingDefaultCalculatorValueAndReturningZeroHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['height']);
    }

    public function testGetUnknownUnitGivenAndUnknownAttributeGivenImpliesUsingDefaultCalculatorValueAndReturningZeroWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['width']);
    }

    public function testGetUnknownUnitGivenAndUnknownAttributeGivenImpliesUsingDefaultCalculatorValueAndReturningZeroLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['length']);
    }

    public function testGetKnownUnitGivenAndUnknownAttributeGivenImpliesUsingTargetCalculatorValueAndReturningZeroHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['height']);
    }

    public function testGetKnownUnitGivenAndUnknownAttributeGivenImpliesUsingTargetCalculatorValueAndReturningZeroWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['width']);
    }

    public function testGetKnownUnitGivenAndUnknownAttributeGivenImpliesUsingTargetCalculatorValueAndReturningZeroLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => null,
                    'ccc' => null,
                    'ddd' => null
                });

        static::assertEquals(0, $this->dimensions->get($this->product)['length']);
    }

    public function testGetCmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(50, $this->dimensions->get($this->product)['height']);
    }

    public function testGetCmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(70, $this->dimensions->get($this->product)['width']);
    }

    public function testGetCmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('cm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(90, $this->dimensions->get($this->product)['length']);
    }

    public function testGetInchUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('inch');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(127, $this->dimensions->get($this->product)['height']);
    }

    public function testGetInchUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('inch');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(178, $this->dimensions->get($this->product)['width']);
    }

    public function testGetInchUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('inch');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(229, $this->dimensions->get($this->product)['length']);
    }

    public function testGetMmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('mm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(5, $this->dimensions->get($this->product)['height']);
    }

    public function testGetMmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('mm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(7, $this->dimensions->get($this->product)['width']);
    }

    public function testGetMmUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('mm');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(9, $this->dimensions->get($this->product)['length']);
    }

    public function testGetUnknownUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingHeightValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(5, $this->dimensions->get($this->product)['height']);
    }

    public function testGetUnknownUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingWidthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(7, $this->dimensions->get($this->product)['width']);
    }

    public function testGetUnknownUnitGivenKnownAttributeGivenImpliesUsingTargetCalculatorForCalculatingLengthValue(): void
    {
        $this->shippingOptions->method('getProductSizeUnit')
            ->willReturn('aaa');
        $this->shippingOptions->method('getProductHeightAttribute')
            ->willReturn('bbb');
        $this->shippingOptions->method('getProductWidthAttribute')
            ->willReturn('ccc');
        $this->shippingOptions->method('getProductLengthAttribute')
            ->willReturn('ddd');
        $this->product->method('getData')
            ->willReturnCallback(fn($data) =>
                match ($data) {
                    'bbb' => 5,
                    'ccc' => 7,
                    'ddd' => 9,
                });

        static::assertEquals(9, $this->dimensions->get($this->product)['length']);
    }

    protected function setUp(): void
    {
        $this->shippingOptions = $this->createStub(ShippingOptions::class);
        $this->dimensions = new Dimensions($this->shippingOptions);
        $this->product = $this->createStub(Product::class);
        $this->store = $this->createStub(StoreInterface::class);

        $this->product->method('getStore')
            ->willReturn($this->store);
    }
}
