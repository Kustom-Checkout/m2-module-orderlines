<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction\ShippingAttributes;

use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\EntityManager;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Dimensions;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Tags;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Weight;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\EntityManager
 */
class EntityManagerTest extends TestCase
{
    /**
     * @var EntityManager
     */
    private EntityManager $model;
    /**
     * @var Dimensions
     */
    private Dimensions $dimensions;
    /**
     * @var Tags
     */
    private Tags $tags;
    /**
     * @var Weight
     */
    private Weight $weight;
    /**
     * @var Product
     */
    private Product $product;

    public function testAttachToItemReturningDimensionsValue(): void
    {
        $expected = ['abc' => 'def'];
        $this->dimensions->method('get')
            ->willReturn($expected);

        $result = $this->model->attachToItem([], $this->product);
        static::assertEquals($expected, $result['shipping_attributes']['dimensions']);
    }

    public function testAttachToItemReturningTagsValue(): void
    {
        $expected = ['abc' => 'def'];
        $this->tags->method('get')
            ->willReturn($expected);

        $result = $this->model->attachToItem([], $this->product);
        static::assertEquals($expected, $result['shipping_attributes']['tags']);
    }

    public function testAttachToItemReturningWeightValue(): void
    {
        $expected = (float) 7;
        $this->weight->method('get')
            ->willReturn($expected);

        $result = $this->model->attachToItem([], $this->product);
        static::assertEquals($expected, $result['shipping_attributes']['weight']);
    }

    protected function setUp(): void
    {
        $this->dimensions = $this->createStub(Dimensions::class);
        $this->tags = $this->createStub(Tags::class);
        $this->weight = $this->createStub(Weight::class);
        $this->model = new EntityManager($this->dimensions, $this->tags, $this->weight);
        $this->product = $this->createStub(Product::class);
    }
}
