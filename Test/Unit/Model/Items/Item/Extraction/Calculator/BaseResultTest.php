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
use Klarna\Orderlines\Model\ItemGenerator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\BaseResult;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\BaseResult
 */
#[AllowMockObjectsWithoutExpectations]
class BaseResultTest extends TestCase
{
    /**
     * @var BaseResult
     */
    private BaseResult $model;
    /**
     * @var DataConverter|MockObject
     */
    private DataConverter $dataConverter;
    /**
     * @var Container
     */
    private Container $container;

    public function testGetFromContainerReturningSku(): void
    {
        $expected = 'my_sku';
        $this->container->method('getSku')
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['reference']);
    }

    public function testGetFromContainerReturningName(): void
    {
        $expected = 'my_name';
        $this->container->method('getName')
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['name']);
    }

    public function testGetFromContainerReturningQuantity(): void
    {
        $expected = 7;
        $this->container->method('getQty')
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['quantity']);
    }

    public function testGetFromContainerReturningVirtualType(): void
    {
        $this->container->method('isProductTypeVirtualOrDownloadable')
            ->willReturn(true);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals(ItemGenerator::ITEM_TYPE_VIRTUAL, $result['type']);
    }

    public function testGetFromContainerReturningPhysicalType(): void
    {
        $this->container->method('isProductTypeVirtualOrDownloadable')
            ->willReturn(false);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals(ItemGenerator::ITEM_TYPE_PHYSICAL, $result['type']);
    }

    public function testGetFromContainerReturningDiscountAmountOfZero(): void
    {
        $result = $this->model->getFromContainer($this->container);
        static::assertEquals(0, $result['discount_rate']);
    }

    public function testGetFromContainerReturningTaxRateOfZero(): void
    {
        $result = $this->model->getFromContainer($this->container);
        static::assertEquals(0, $result['tax_rate']);
    }

    public function testGetFromContainerReturningTotalTaxAmountOfZero(): void
    {
        $result = $this->model->getFromContainer($this->container);
        static::assertEquals(0, $result['total_tax_amount']);
    }

    public function testGetFromContainerReturningTotalDiscountAmount(): void
    {
        $expected = (float) 700;
        $this->container->method('getDiscountAmount')
            ->willReturn((float) 7);
        $this->dataConverter->method('toApiFloat')
            ->with(7)
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['total_discount_amount']);
    }

    public function testGetFromContainerNoProductUrlSinceNotGiven(): void
    {
        $result = $this->model->getFromContainer($this->container);
        static::assertTrue(!isset($result['product_url']));
    }

    public function testGetFromContainerReturningProductUrl(): void
    {
        $expected = 'my_url';
        $this->container->method('getProductUrl')
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['product_url']);
    }

    public function testGetFromContainerNoImageUrlSinceNotGiven(): void
    {
        $result = $this->model->getFromContainer($this->container);
        static::assertTrue(!isset($result['image_url']));
    }

    public function testGetFromContainerReturningImageUrl(): void
    {
        $expected = 'my_url';
        $this->container->method('getImageUrl')
            ->willReturn($expected);

        $result = $this->model->getFromContainer($this->container);
        static::assertEquals($expected, $result['image_url']);
    }

    protected function setUp(): void
    {
        $this->dataConverter = $this->createMock(DataConverter::class);
        $this->model = new BaseResult($this->dataConverter);
        $this->container = $this->createStub(Container::class);
    }
}
