<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction\ShippingAttributes\Entities;

use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Tags;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Tags
 */
class TagsTest extends TestCase
{
    /**
     * @var Tags
     */
    private Tags $model;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var Collection
     */
    private Collection $collection;
    /**
     * @var DataObject
     */
    private DataObject $item;

    public function testGetProductCollectionIsNullImpliesReturningEmptyArray(): void
    {
        $this->product->method('getCategoryCollection')
            ->willReturn(null);

        static::assertEquals([], $this->model->get($this->product));
    }

    public function testGetCollectionHasNoItemsImpliesReturningEmptyArray(): void
    {
        $this->product->method('getCategoryCollection')
            ->willReturn($this->collection);
        $this->collection->method('getItems')
            ->willReturn([]);
        static::assertEquals([], $this->model->get($this->product));
    }

    public function testGetCollectionHasItemsImpliesReturningArrayWithItems(): void
    {
        $expected = 'my_item_name';
        $this->item->setData('name', $expected);
        $this->collection->method('getItems')
            ->willReturn([$this->item]);
        $this->product->method('getCategoryCollection')
            ->willReturn($this->collection);

        static::assertEquals([$expected], $this->model->get($this->product));
    }

    protected function setUp(): void
    {
        $this->model = new Tags();

        $this->product = $this->createStub(Product::class);
        $this->collection = $this->createStub(Collection::class);
        $this->item = new DataObject();
    }
}
